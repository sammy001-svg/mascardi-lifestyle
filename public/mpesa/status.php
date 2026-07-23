<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Response;
use App\Models\MpesaTransaction;

$ref = $_GET['ref'] ?? '';

if (!is_string($ref) || $ref === '') {
    Response::json(['status' => 'unknown'], 400);
}

$transaction = MpesaTransaction::findByCheckoutRequestId($ref);

if (!$transaction) {
    Response::json(['status' => 'unknown']);
}

Response::json([
    'status' => $transaction['status'], // initiated | pending | success | failed | cancelled | timeout
    'result_desc' => $transaction['result_desc'],
]);
