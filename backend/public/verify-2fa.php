<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/AuthService.php';
require_once __DIR__ . '/../app/Core/cors.php';

use App\Core\AuthService;

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['user_id'], $data['code'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Nedostaju podaci.']);
        exit;
    }

    $authService = new AuthService();
    $result = $authService->verifyTwoFactorCode($data['user_id'], $data['code']);
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
