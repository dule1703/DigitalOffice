<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

use App\Controllers\AuthController;

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'], $data['code'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nedostaju podaci.']);
    exit;
}

$auth = new AuthController();
echo json_encode($auth->verify2FA($data['user_id'], $data['code']));
