<?php

namespace App\Models;

use PDO;
use App\Core\Database;
use Exception;

class Offer {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        if (!$this->conn) {
            throw new Exception("Neuspešna konekcija na bazu podataka.");
        }
    }

    public function getAllOffers(int $limit = 10, int $offset = 0): array {
        try {
            if ($limit < 1 || $offset < 0) {
                return ['status' => 'error', 'message' => 'Nevalidni parametri za limit ili offset.'];
            }

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
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'data' => $offers,
                'message' => empty($offers) ? 'Nema ponuda za prikaz.' : 'Ponude uspešno preuzete.'
            ];
        } catch (Exception $e) {
            error_log("Greška u getAllOffers: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Greška pri preuzimanju ponuda.'];
        }
    }

    public function getOfferById(string $id): array {
        try {
            if (empty($id)) {
                return ['status' => 'error', 'message' => 'ID ponude je obavezan.'];
            }

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

    public function searchOffers(string $term, int $limit = 10, int $offset = 0): array {
        try {
            if (empty($term) || strlen($term) < 2) {
                return ['status' => 'error', 'message' => 'Pojam za pretragu mora imati najmanje 2 karaktera.'];
            }
            if ($limit < 1 || $offset < 0) {
                return ['status' => 'error', 'message' => 'Nevalidni parametri za limit ili offset.'];
            }

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
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'data' => $offers,
                'message' => empty($offers) ? 'Nema rezultata za zadatu pretragu.' : 'Rezultati pretrage uspešno preuzeti.'
            ];
        } catch (Exception $e) {
            error_log("Greška u searchOffers: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Greška pri pretrazi ponuda.'];
        }
    }

    public function createOffer(array $data): array {
        try {
            if (!isset($data['total_without_vat'], $data['client_id']) || !is_numeric($data['total_without_vat']) || empty($data['client_id'])) {
                return ['status' => 'error', 'message' => 'Nevalidni podaci: total_without_vat i client_id su obavezni.'];
            }

            $vat_percentage = 18;
            $vat = round($data['total_without_vat'] * $vat_percentage / 100);
            $total = $data['total_without_vat'] + $vat;
            $year = date('Y');

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count 
                FROM document 
                WHERE document_type_id = '003' AND YEAR(date) = :year
            ");
            $stmt->execute(['year' => $year]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)$row['count'] + 1;
            $number = str_pad($count, 3, '0', STR_PAD_LEFT) . '/' . $year;

            $sql = "
                INSERT INTO document (
                    id, number, date, service_date, total_without_vat, vat_percentage, vat, total, note, client_id, document_type_id
                ) VALUES (
                    :id, :number, :date, :service_date, :total_without_vat, :vat_percentage, :vat, :total, :note, :client_id, :document_type_id
                )
            ";

            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([
                'id' => $this->generateUUID(),
                'number' => $number,
                'date' => date('Y-m-d H:i:s'),
                'service_date' => date('Y-m-d H:i:s'),
                'total_without_vat' => $data['total_without_vat'],
                'vat_percentage' => $vat_percentage,
                'vat' => $vat,
                'total' => $total,
                'note' => isset($data['note']) ? $data['note'] : '',
                'client_id' => $data['client_id'],
                'document_type_id' => '003'
            ]);

            if ($success) {
                $this->conn->commit();
                return ['status' => 'success', 'message' => 'Ponuda je uspešno kreirana.', 'data' => ['id' => $this->generateUUID()]];
            } else {
                $this->conn->rollBack();
                return ['status' => 'error', 'message' => 'Neuspešno kreiranje ponude.'];
            }
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Greška u createOffer: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Greška pri kreiranju ponude.'];
        }
    }

    private function generateUUID(): string {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function updateOffer(string $id, array $data): array {
        try {
            if (empty($id)) {
                return ['status' => 'error', 'message' => 'ID ponude je obavezan.'];
            }
            if (!isset($data['total_without_vat'], $data['client_id']) || !is_numeric($data['total_without_vat']) || empty($data['client_id'])) {
                return ['status' => 'error', 'message' => 'Nevalidni podaci: total_without_vat i client_id su obavezni.'];
            }

            $vat_percentage = 18;
            $vat = round($data['total_without_vat'] * $vat_percentage / 100);
            $total = $data['total_without_vat'] + $vat;

            $this->conn->beginTransaction();

            $sql = "
                UPDATE document 
                SET      
                    service_date = :service_date,
                    total_without_vat = :total_without_vat,
                    vat_percentage = :vat_percentage,
                    vat = :vat,
                    total = :total,
                    note = :note,
                    client_id = :client_id
                WHERE id = :id
            ";

            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([
                'service_date' => date('Y-m-d H:i:s'),
                'total_without_vat' => $data['total_without_vat'],
                'vat_percentage' => $vat_percentage,
                'vat' => $vat,
                'total' => $total,
                'note' => isset($data['note']) ? $data['note'] : '',
                'client_id' => $data['client_id'],
                'id' => $id
            ]);

            if ($success && $stmt->rowCount() > 0) {
                $this->conn->commit();
                return ['status' => 'success', 'message' => 'Ponuda je uspešno ažurirana.'];
            } else {
                $this->conn->rollBack();
                return ['status' => 'warning', 'message' => 'Nema promenjenih podataka ili ID ne postoji.'];
            }
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Greška u updateOffer: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Greška pri ažuriranju ponude.'];
        }
    }

    public function deleteOffer(string $id): array {
        try {
            if (empty($id)) {
                return ['status' => 'error', 'message' => 'ID ponude je obavezan.'];
            }

            $sql = "DELETE FROM document WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute(['id' => $id]);

            if ($success && $stmt->rowCount() > 0) {
                return ['status' => 'success', 'message' => 'Ponuda je uspešno obrisana.'];
            }
            return ['status' => 'warning', 'message' => 'Ponuda nije pronađena.'];
        } catch (Exception $e) {
            error_log("Greška u deleteOffer: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Greška pri brisanju ponude.'];
        }
    }
}