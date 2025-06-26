<?php

namespace App\Models;

use PDO;
use App\Core\Database;
use Exception;
use App\Helpers\Utils;
use PDOException;

class Client {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        if(!$this->conn) {
            throw new Exception('Neuspešna konekcija na bazu podataka.');
        }
    }

    public function getAllClients() {
        try {
            $sql = "
                SELECT 
                    id,
                    c_name,
                    tax_number,
                    identification_number,
                    address,
                    zip_code,
                    city,
                    country,
                    datum            
                FROM client                 
                ORDER BY datum DESC             
            ";

            $stmt = $this->conn->prepare($sql);
           
            $stmt->execute();
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'data' => $clients,
                'message' => empty($clients) ? 'Nema ponuda za prikaz.' : 'Ponude uspešno preuzete.'
            ];
        } catch (Exception $e) {
            error_log("Greška u getAllOffers: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Greška pri preuzimanju ponuda.'];
        }
    }

    public function searchClients($term) {
        $query = trim($term);

        if (strlen($query) < 2) {
            return []; // ili možeš vratiti null i obraditi to u kontroleru
        }

        try {
            $sql = "
                SELECT 
                    id,                                       
                    c_name,
                    tax_number,
                    identification_number,
                    address,
                    zip_code,
                    city,
                    country,
                    datum
                FROM client
                WHERE 
                    c_name LIKE ? OR
                    tax_number LIKE ? OR
                    identification_number LIKE ? OR                    
                    address LIKE ? OR
                    zip_code LIKE ? OR
                    city LIKE ? OR
                    country LIKE ?                    
            ";

            $stmt = $this->conn->prepare($sql);
            $like = '%' . $query . '%';
            $params = array_fill(0, 7, $like);

            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            error_log("Greška u searchClients: " . $e->getMessage());
            return []; // ili možeš vratiti null, false, ili strukturu sa 'error' ključem
        }
    }

    public function getClientById(string $id) {
        try {
            if (empty($id)) {
                return ['status' => 'error', 'message' => 'ID ponude je obavezan.'];
            }

            $sql = "
                SELECT 
                    id,                    
                    c_name, 
                    tax_number,
                    identification_number,
                    address,
                    zip_code,
                    city,
                    country
                FROM client               
                WHERE id = :id
                LIMIT 1
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['id' => $id]);
            $offer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($offer === false) {
                return ['status' => 'warning', 'message' => 'Ponuda sa datim ID-jem nije pronađena.'];
            }

            return [
                'status' => 'success',
                'data' => $offer,
                'message' => 'Ponuda uspešno preuzeta.'
            ];
        } catch (Exception $e) {
            error_log("Greška u getOfferById: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Greška pri preuzimanju ponude.'];
        }
    }

public function createClient(array $data): array {
    try {
        $this->conn->beginTransaction();
        $id = Utils::generateUUID();

        $sql = "
            INSERT INTO client (id, c_name, tax_number, identification_number, address, zip_code, city, country, datum) 
            VALUES (:id, :c_name, :tax_number, :identification_number, :address, :zip_code, :city, :country, :datum)
        ";

        // Pretvori prazne stringove u NULL (za nullable polja)
        $data['tax_number'] = !empty(trim($data['tax_number'])) ? $data['tax_number'] : null;
        $data['identification_number'] = !empty(trim($data['identification_number'])) ? $data['identification_number'] : null;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $id,                
            'c_name' => $data['c_name'],
            'tax_number' => $data['tax_number'],
            'identification_number' => $data['identification_number'],
            'address' => $data['address'],
            'zip_code' => $data['zip_code'],
            'city' => $data['city'],
            'country' => $data['country'],
            'datum' => date('Y-m-d H:i:s')
        ]);

        $this->conn->commit(); // Commit tek nakon uspešnog execute
        return [
            'status' => 'success',
            'message' => 'Klijent je uspešno kreiran.',
            'data' => ['id' => $id]
        ];

    } catch (PDOException $e) {
        $this->conn->rollBack();

        // Ako je duplikat – kod 1062
        if ($e->getCode() === '23000' && isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
            return [
                'status' => 'error',
                'message' => 'Klijent sa unetim PIB-om ili JMBG-om već postoji.'
            ];
        }

        error_log("Greška u createClient: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Greška pri kreiranju klijenta.'];
    }
}



    public function updateClient(string $id, array $data): array {
    try {
        if (empty($id)) {
            return ['status' => 'error', 'message' => 'ID klijenta je obavezan.'];
        }

        $data['tax_number'] = !empty(trim($data['tax_number'])) ? $data['tax_number'] : null;
        $data['identification_number'] = !empty(trim($data['identification_number'])) ? $data['identification_number'] : null;

        $this->conn->beginTransaction();

        $sql = "
            UPDATE client 
            SET      
                c_name = :c_name,
                tax_number = :tax_number,
                identification_number = :identification_number,
                address = :address,
                zip_code = :zip_code,
                city = :city,
                country = :country
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);
        $success = $stmt->execute([
            'id' => $id,
            'c_name' => $data['c_name'],
            'tax_number' => $data['tax_number'],
            'identification_number' => $data['identification_number'],
            'address' => $data['address'],
            'zip_code' => $data['zip_code'],
            'city' => $data['city'],
            'country' => $data['country'],
        ]);

        if ($success && $stmt->rowCount() > 0) {
            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Klijent je uspešno ažuriran.'];
        } else {
            $this->conn->rollBack();
            return ['status' => 'warning', 'message' => 'Nema promenjenih podataka ili ID ne postoji.'];
        }
    } catch (PDOException $e) {
        $this->conn->rollBack();

        // 👇 Detekcija UNIQUE constraint violation (PIB/JMBG već postoji)
        if ($e->getCode() === '23000' && isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
            return ['status' => 'error', 'message' => 'Klijent sa unetim PIB-om ili JMBG-om već postoji.'];
        }

        error_log("Greška u updateClient: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Greška pri ažuriranju klijenta.'];
    }
}


    public function deleteClient(string $id): array {
    if (empty($id)) {
        return ['status' => 'error', 'message' => 'ID klijenta je obavezan.'];
    }

    try {
        // Provera da li klijent postoji pre pokušaja brisanja
        $checkSql = "SELECT id FROM client WHERE id = :id";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->execute(['id' => $id]);

        if ($checkStmt->rowCount() === 0) {
            return ['status' => 'warning', 'message' => 'Klijent sa datim ID-jem ne postoji.'];
        }

        // Pokušaj brisanja
        $sql = "DELETE FROM client WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        return ['status' => 'success', 'message' => 'Klijent je uspešno obrisan.'];

    } catch (PDOException $e) {
        error_log("Greška u deleteClient: " . $e->getMessage());

        // Prepoznaje FOREIGN KEY constraint grešku
        if (str_contains($e->getMessage(), 'a foreign key constraint fails')) {
            return [
                'status' => 'error',
                'message' => 'Klijent se ne može obrisati jer je vezan za druge podatke.'
            ];
        }

        return ['status' => 'error', 'message' => 'Greška pri brisanju klijenta.'];
    }
}



}