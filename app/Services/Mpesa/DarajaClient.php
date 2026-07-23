<?php

declare(strict_types=1);

namespace App\Services\Mpesa;

use App\Core\Config;
use App\Core\Logger;
use RuntimeException;

/**
 * Low-level Daraja (Safaricom M-Pesa) API client: OAuth token fetch/cache
 * and a generic authenticated request helper. STK push / query business
 * logic lives in the sibling *Service classes, not here.
 */
final class DarajaClient
{
    private const TOKEN_CACHE_PATH = '/storage/cache/mpesa_token.cache';
    private const REQUEST_TIMEOUT_SECONDS = 20;

    public static function baseUrl(): string
    {
        $env = Config::mpesa('env');
        return Config::mpesa('base_urls')[$env] ?? 'https://sandbox.safaricom.co.ke';
    }

    public static function getAccessToken(): string
    {
        $cachePath = dirname(__DIR__, 3) . self::TOKEN_CACHE_PATH;

        if (is_file($cachePath)) {
            $cached = json_decode((string) file_get_contents($cachePath), true);
            if (is_array($cached) && ($cached['expires_at'] ?? 0) > time()) {
                return (string) $cached['token'];
            }
        }

        $consumerKey = Config::mpesa('consumer_key');
        $consumerSecret = Config::mpesa('consumer_secret');

        $ch = curl_init(self::baseUrl() . '/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $consumerKey . ':' . $consumerSecret,
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT_SECONDS,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            Logger::error('Daraja OAuth request failed: ' . $curlError);
            throw new RuntimeException('Could not reach the M-Pesa payment service. Please try again shortly.');
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['access_token'])) {
            Logger::error('Daraja OAuth rejected (HTTP ' . $httpCode . '): ' . $response);
            throw new RuntimeException('M-Pesa payment service authentication failed.');
        }

        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        file_put_contents($cachePath, json_encode([
            'token' => $data['access_token'],
            'expires_at' => time() + $expiresIn - 60, // 60s safety buffer
        ]), LOCK_EX);

        return (string) $data['access_token'];
    }

    /** @return array{status:int, data:array} */
    public static function post(string $endpoint, array $body): array
    {
        $token = self::getAccessToken();

        $ch = curl_init(self::baseUrl() . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT_SECONDS,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            Logger::error('Daraja request to ' . $endpoint . ' failed: ' . $curlError);
            throw new RuntimeException('Could not reach the M-Pesa payment service. Please try again shortly.');
        }

        $data = json_decode($response, true) ?? [];

        return ['status' => $httpCode, 'data' => $data];
    }
}
