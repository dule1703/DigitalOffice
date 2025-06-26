<?php

use App\Controllers\ClientController;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nedostaje ID.']);
    exit;
}

$controller = new ClientController();
$response = $controller->getClientById($_GET['id']);

echo json_encode($response);