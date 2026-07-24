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
    private const MAX_BYTES = 5 * 1024 * 1024; // 5MB

    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function storeImage(array $file, string $subdir, ?int $uploadedBy = null): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && ($file['error'] ?? null) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed. Please try again.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload.');
        }

        if ($file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('Image must be smaller than 5MB.');
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

        $extension = self::ALLOWED[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $uploadsRoot = PUBLIC_PATH . '/uploads';
        $targetDir = $uploadsRoot . '/' . trim($subdir, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Re-encode through GD to strip any non-image payload embedded in the file.
        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png' => imagecreatefrompng($file['tmp_name']),
            'image/webp' => imagecreatefromwebp($file['tmp_name']),
        };

        if ($image === false) {
            throw new \RuntimeException('Could not process the uploaded image.');
        }

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
