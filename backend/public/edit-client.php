<?php

use App\Controllers\ClientController;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID klijenta mora biti prosleđen.']);
    exit;
}

$controller = new ClientController();
$response = $controller->updateClient($data['id'], $data);

echo json_encode($response);
