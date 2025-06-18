<?php

use App\Controllers\OfferController;


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

header('Content-Type: application/json');

if (!isset($_GET['term'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nedostaje termin za pretragu.']);
    exit;
}

$controller = new OfferController();
$response = $controller->searchOffers($_GET['term']);

echo json_encode($response);
