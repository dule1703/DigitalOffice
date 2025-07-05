<?php

namespace App\Models;

use PDO;
use App\Core\Database;
use Exception;
use App\Helpers\Utils;
use PDOException;

class Model {
    private $conn;

    function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        if(!$this->conn) {
            throw new Exception('Neuspešna konekcija na bazu podataka.');
        }
    }

    function getAllModels () {
        try{
            $sql = "SELECT m.id, m.model_name, mt.engine_id, ep.package_name, ep.id AS sifra_paketa, mt.model_price, ai.accessories_name, a.accessories_price, act.id AS naziv_stavke FROM model m 
INNER JOIN model_type mt ON m.id = mt.model_id
INNER JOIN equipment_package ep ON mt.equip_package_id = ep.id
INNER JOIN accessories a ON mt.id = a.model_type_id
INNER JOIN accessories_items ai ON a.accessories_items_id = ai.id
INNER JOIN accessories_type act ON act.id = a.accessories_type_id;";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "status" => "success",
                "data" => $models,
                "message" => empty($models) ? "Nema podataka za prikaz" : "Uspešno preuzeti modeli"
            ];

        } catch(Exception $e) {
            error_log('Greška u getAllModels: ' . $e->getMessage() );
            return [
                "status" => "error",
                "message" => "Greška pri preuzimanju modela."
            ];
        }
    }

    function getBasicEquip() {
        try {
            $sql = "SELECT be.model_id, be.equip_package_id, be.basic_equip_type_id, be.basic_eq_item_id, 
                           bea.basic_equip_name 
                    FROM basic_equipment be INNER JOIN basic_equipment_items bea ON be.basic_eq_item_id = bea.id;";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $osnovna_oprema = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
               "status" => "success",
               "data" => $osnovna_oprema,
               "message" => empty($osnovna_oprema) ? "Nema podataka za prikaz" : "Uspešno preuzeti podaci o opremi"     
            ];

        } catch (Exception $e) {
            error_log('Greška u getBasicEquip: ' . $e->getMessage());
            return [
                "status" => "error",
                "message" => "Greška pri preuzimanju podataka za Basic equipment."
            ];
        }
    }
}