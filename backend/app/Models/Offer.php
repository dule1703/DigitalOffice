<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Offer {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    public function getAllOffers() {
        $sql = "
            SELECT 
                d.id,
                d.number,
                d.date,
                d.total_without_vat,
                d.total,
                d.note,
                c.c_name AS client_name
            FROM document d
            JOIN client c ON d.client_id = c.id
            ORDER BY d.date DESC
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOfferById($id) {
        $sql = "
            SELECT 
                d.id,
                d.number,
                d.date,
                d.total_without_vat,
                d.total,
                d.note,
                c.c_name AS client_name
            FROM document d
            JOIN client c ON d.client_id = c.id
            WHERE d.id = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   public function searchOffers($term) {
    $sql = "
        SELECT 
            d.id,
            d.number,
            d.date,
            d.total_without_vat,
            d.total,
            d.note,
            c.c_name AS client_name,
            c.tax_number,
            c.identification_number,
            c.address,
            c.city,
            c.country
        FROM document d
        JOIN client c ON d.client_id = c.id
        WHERE 
            c.c_name LIKE :term OR
            c.tax_number LIKE :term OR
            c.identification_number LIKE :term OR
            c.address LIKE :term OR
            c.city LIKE :term OR
            c.country LIKE :term OR
            d.number LIKE :term OR
            d.date LIKE :term OR
            d.total_without_vat LIKE :term OR
            d.total LIKE :term OR
            d.note LIKE :term
        ORDER BY d.date DESC
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['term' => '%' . $term . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}
