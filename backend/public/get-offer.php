<?php

use App\Controllers\OfferController;
use App\Core\cors;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';


header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nedostaje ID.']);
    exit;
}

$controller = new OfferController();
$response = $controller->getOfferById($_GET['id']);

echo json_encode($response);
