<?php

declare(strict_types=1);

define('PUBLIC_PATH', dirname(__DIR__));

require dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Logger;
use App\Services\Mpesa\CallbackHandler;

// Public, unauthenticated by design — Safaricom posts here directly and
// cannot send a CSRF token. Hardened instead via strict payload validation
// and idempotent processing keyed on CheckoutRequestID (see CallbackHandler).
$rawBody = file_get_contents('php://input') ?: '';
Logger::info('M-Pesa callback received: ' . $rawBody);

try {
    CallbackHandler::handleWebhook($rawBody);
} catch (\Throwable $e) {
    Logger::error('M-Pesa callback processing error: ' . $e->getMessage());
    // Still acknowledge with 200 below — Safaricom will retry on non-200,
    // and retrying won't help an application-level error like this.
}

// Safaricom requires a 200 + this exact JSON shape to consider the callback delivered.
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
