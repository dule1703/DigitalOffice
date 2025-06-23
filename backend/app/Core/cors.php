<?php
require_once __DIR__ . '/../../config.php';

$origin = trim($_SERVER['HTTP_ORIGIN'] ?? '');
$allowedOriginsRaw = getenv('ALLOWED_ORIGINS') ?: '';
$allowedOrigins = array_map('trim', explode(',', $allowedOriginsRaw));


if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    error_log("CORS headeri postavljeni za: $origin");
} else {
    error_log("CORS blokiran: '$origin' nije dozvoljen");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
