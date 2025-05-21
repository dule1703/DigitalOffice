<?php
require_once __DIR__ . '/../app/Core/AuthService.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\AuthService;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email je obavezan.']);
    exit;
}

$auth = new AuthService();
echo json_encode($auth->sendResetToken($data['email']));
