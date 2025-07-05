<?php

use App\Controllers\ModelController;
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

header('Content-Type: applicaton/json');

$controller = new ModelController();

$response = $controller->getAllModels();

echo json_encode($response);

