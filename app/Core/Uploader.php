<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\MediaUpload;

/**
 * Validates and stores an uploaded image: real MIME sniffing (not filename/
 * client Content-Type), size cap, GD re-encode to strip any embedded payload,
 * random filename. Returns the path relative to /public/uploads (e.g.
 * "pillars/ab12cd34.jpg") for storage in the DB, or throws on failure.
 *
 * Every store()/delete() also keeps the media_uploads library index in sync —
 * this is the single choke point every admin upload flow goes through.
 */
final class Uploader
{
    private const MAX_BYTES = 15 * 1024 * 1024; // 15MB

    /** Images larger than this on the long edge are downscaled on upload. */
    private const MAX_DIMENSION = 2400;

    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function storeImage(array $file, string $subdir, ?int $uploadedBy = null): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && ($file['error'] ?? null) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(self::uploadErrorMessage((int) ($file['error'] ?? 0)));
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload.');
        }

        if ($file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('Image must be smaller than 15MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED[$mime])) {
            throw new \RuntimeException('Only JPEG, PNG, or WEBP images are allowed.');
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new \RuntimeException('The uploaded file is not a valid image.');
        }

        // getimagesize only reads the header; the full decode below needs enough
        // memory to hold the uncompressed bitmap. On big photos this is the usual
        // cause of "could not process", so raise the limit for this request first.
        self::ensureMemoryFor($imageInfo);

        $extension = self::ALLOWED[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $uploadsRoot = PUBLIC_PATH . '/uploads';
        $targetDir = $uploadsRoot . '/' . trim($subdir, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Re-encode through GD to strip any non-image payload embedded in the file.
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        };

        if (!$image instanceof \GdImage) {
            throw new \RuntimeException('Could not process the uploaded image. Please try a smaller photo.');
        }

        // Preserve transparency for PNG/WebP through resize + save.
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        // Phone photos carry EXIF orientation; bake it in so they aren't sideways.
        if ($mime === 'image/jpeg') {
            $image = self::applyExifOrientation($image, $file['tmp_name']);
        }

        // Downscale oversized images — smaller files, faster pages, identical on screen.
        $image = self::downscale($image, $mime);

        $targetPath = $targetDir . '/' . $filename;

        $saved = match ($mime) {
            'image/jpeg' => imagejpeg($image, $targetPath, 85),
            'image/png' => imagepng($image, $targetPath, 6),
            'image/webp' => imagewebp($image, $targetPath, 85),
        };
        imagedestroy($image);

        if (!$saved) {
            throw new \RuntimeException('Could not save the uploaded image.');
        }

        $relativePath = trim($subdir, '/') . '/' . $filename;

        MediaUpload::record($relativePath, (string) $file['name'], $mime, (int) $file['size'], $uploadedBy);

        return $relativePath;
    }

    /**
     * Raise memory_limit for this request if it's below what decoding this
     * image will need (≈ width × height × 4 bytes, plus decode buffers).
     */
    private static function ensureMemoryFor(array $imageInfo): void
    {
        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        if ($width < 1 || $height < 1) {
            return;
        }

        $needed = (int) ($width * $height * 7 * 1.6) + 48 * 1024 * 1024;
        $current = self::memoryLimitBytes();
        if ($current !== -1 && $current < $needed) {
            @ini_set('memory_limit', (string) $needed);
        }
    }

    private static function memoryLimitBytes(): int
    {
        $raw = trim((string) ini_get('memory_limit'));
        if ($raw === '' || $raw === '-1') {
            return -1;
        }
        $unit = strtolower($raw[strlen($raw) - 1]);
        $value = (int) $raw;
        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int) $raw,
        };
    }

    private static function downscale(\GdImage $image, string $mime): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longEdge = max($width, $height);
        if ($longEdge <= self::MAX_DIMENSION) {
            return $image;
        }

        $scale = self::MAX_DIMENSION / $longEdge;
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        return $resized;
    }

    private static function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 0);
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };
        if ($rotated instanceof \GdImage) {
            imagedestroy($image);
            return $rotated;
        }
        return $image;
    }

    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That image is too large for the server to accept. Please upload one under 15MB.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
            default => 'Upload failed. Please try again.',
        };
    }

    public static function delete(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $fullPath = PUBLIC_PATH . '/uploads/' . ltrim($relativePath, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
        MediaUpload::deleteByPath($relativePath);
    }
}
