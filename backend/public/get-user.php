<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/cors.php';

use App\Core\AuthMiddleware;

// Vraća dekodirani token ako je ispravan
$userData = AuthMiddleware::validateToken();

// Odgovor – samo testni
echo json_encode([
    'status' => 'success',
    'message' => 'Pristup dozvoljen.',
    'data' => $userData
]);
