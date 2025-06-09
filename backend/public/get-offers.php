<?php

use App\Controllers\OfferController;
use App\Core\cors;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';


header('Content-Type: application/json');

$controller = new OfferController();
$response = $controller->getAllOffers();

echo json_encode($response);
