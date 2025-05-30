<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class User {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $email, $password, $company_id = 1) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            INSERT INTO user (name, email, password, company_id) 
            VALUES (:name, :email, :password, :company_id)
        ");
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashed,
            'company_id' => $company_id
        ]);
    }

    public function update2FACode($id, $code, $expiry) {
        $stmt = $this->conn->prepare("
            UPDATE user 
            SET twofa_code = :code, twofa_expiry = :expiry, twofa_verified = 0 
            WHERE id = :id
        ");
        return $stmt->execute([
            'code' => $code,
            'expiry' => $expiry,
            'id' => $id
        ]);
    }

    public function verify2FA($id, $ip) {
        $stmt = $this->conn->prepare("
            UPDATE user 
            SET twofa_verified = 1, last_login = NOW(), last_ip = :ip 
            WHERE id = :id
        ");
        return $stmt->execute([
            'ip' => $ip,
            'id' => $id
        ]);
    }

    public function reset2FAStatus($id) {
        $stmt = $this->conn->prepare("UPDATE user SET twofa_verified = 0 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function updateLastLogin($id, $datetime) {
        $stmt = $this->conn->prepare("UPDATE user SET last_login = :now WHERE id = :id");
        return $stmt->execute([
            'now' => $datetime,
            'id' => $id
        ]);
    }

    public function saveResetToken($id, $token, $expiry) {
        $stmt = $this->conn->prepare("
            UPDATE user 
            SET reset_token = :token, reset_expiry = :expiry 
            WHERE id = :id
        ");
        return $stmt->execute([
            'token' => $token,
            'expiry' => $expiry,
            'id' => $id
        ]);
    }

    public function findByResetToken($token) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE reset_token = :token");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            UPDATE user 
            SET password = :pass, reset_token = NULL, reset_expiry = NULL 
            WHERE id = :id
        ");
        return $stmt->execute([
            'pass' => $hashed,
            'id' => $id
        ]);
    }
}
