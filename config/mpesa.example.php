<?php
/**
 * Copy this file to mpesa.php and fill in real Daraja values.
 * mpesa.php is gitignored — never commit real credentials.
 *
 * Sandbox defaults below are Safaricom's published test values — safe to
 * keep during development. Switch 'env' to 'production' and replace every
 * value with your live Paybill/Till credentials before go-live (Phase 5).
 */
return [
    'env' => 'sandbox', // 'sandbox' | 'production'

    'consumer_key' => 'REPLACE_WITH_DARAJA_CONSUMER_KEY',
    'consumer_secret' => 'REPLACE_WITH_DARAJA_CONSUMER_SECRET',

    'shortcode' => '174379', // sandbox test shortcode; replace with live Paybill/Till in production
    'passkey' => 'REPLACE_WITH_DARAJA_PASSKEY', // sandbox passkey is published in Daraja docs

    // Must be a publicly reachable HTTPS URL — Safaricom cannot call localhost.
    // Use an ngrok/staging URL during development, real domain in production.
    'callback_base_url' => 'https://example.com',

    'base_urls' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],
];
