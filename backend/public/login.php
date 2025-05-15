<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/AuthService.php';

header('Content-Type: application/json');

try {
    // Parsiranje ulaza
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email i lozinka su obavezni.']);
        exit;
    }

    $authService = new AuthService();
    $result = $authService->login($data['email'], $data['password']);
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
