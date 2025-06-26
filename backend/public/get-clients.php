<?php

use App\Controllers\ClientController;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';


header('Content-Type: application/json');

$controller = new ClientController();
$response = $controller->getAllClients();

echo json_encode($response);
