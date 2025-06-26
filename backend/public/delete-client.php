<?php

use App\Controllers\ClientController;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

header('Content-Type: application/json');

// Čitaj JSON telo zahteva
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID nije prosleđen.']);
    exit;
}

$controller = new ClientController();
$response = $controller->deleteClient($id);

echo json_encode($response);
