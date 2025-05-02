<?php
require_once __DIR__ . '/../app/Core/Database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    echo json_encode(["status" => "success", "message" => "Uspešno povezano na bazu"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
