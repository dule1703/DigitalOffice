<?php

use App\Controllers\ClientController;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$controller = new ClientController();
$response = $controller->createClient($data);

echo json_encode($response);
