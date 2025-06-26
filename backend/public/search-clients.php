<?php

use App\Controllers\ClientController;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['query'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nedostaje parametar za pretragu.']);
    exit;
}

$controller = new ClientController();
$response = $controller->searchClients($input['query']);

echo json_encode($response);
