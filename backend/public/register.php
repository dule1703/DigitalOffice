<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\AuthService;

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name'], $data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Sva polja su obavezna.']);
        exit;
    }

    $authService = new AuthService();
    $result = $authService->register($data['name'], $data['email'], $data['password']);
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
