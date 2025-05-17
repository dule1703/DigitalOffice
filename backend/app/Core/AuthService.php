<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PDO;
use App\Core\Database;
require_once __DIR__ . '/../../vendor/autoload.php';

class AuthService {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function register($name, $email, $password, $company_id = 1) {
        // Validacija da korisnik ne postoji
       
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            return ['status' => 'error', 'message' => 'Email je već registrovan.'];
        }

        // Upis u bazu
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO user (name, email, password, company_id) VALUES (:name, :email, :password, :company_id)");

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'company_id' => $company_id
        ]);

        return ['status' => 'success', 'message' => 'Uspešna registracija.'];
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM user WHERE email = :email AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['status' => 'error', 'message' => 'Neispravan email ili lozinka.'];
        }

        // Generiši 2FA kod i expiry
        $twofa_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = (new \DateTime('+10 minutes'))->format('Y-m-d H:i:s');

        $update = $this->conn->prepare("UPDATE user SET twofa_code = :code, twofa_expiry = :expiry, twofa_verified = 0 WHERE id = :id");
        $update->execute([
            'code' => $twofa_code,
            'expiry' => $expiry,
            'id' => $user['id']
        ]);

        $emailSent = $this->sendTwoFactorCode($user['email'], $twofa_code);

        return [
            'status' => 'success',
            'message' => 'Kod za potvrdu je poslat na email.',
            'user_id' => $user['id'],
            'twofa_sent' => $emailSent
        ];
    }

    private function sendTwoFactorCode($toEmail, $code) {
        $mail = new PHPMailer(true);

        try {
         
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER');
            $mail->Password   = getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            $mail->Port       = getenv('SMTP_PORT');           
            $mail->setFrom(getenv('SMTP_FROM'), getenv('SMTP_FROM_NAME'));
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Vaš 2FA kod za prijavu';
            $mail->Body    = "Vaš kod za potvrdu je: <strong>$code</strong><br>Važi 10 minuta.";
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public function verifyTwoFactorCode($userId, $code) {
        $stmt = $this->conn->prepare("SELECT twofa_code, twofa_expiry FROM user WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['status' => 'error', 'message' => 'Korisnik ne postoji.'];
        }

        $now = new \DateTime();
        $expiry = new \DateTime($user['twofa_expiry']);

        if ($user['twofa_code'] !== $code) {
            return ['status' => 'error', 'message' => 'Kod nije ispravan.'];
        }

        if ($now > $expiry) {
            return ['status' => 'error', 'message' => 'Kod je istekao.'];
        }

        // Kod je ispravan
        $ip = $_SERVER['REMOTE_ADDR'];
        $this->conn->prepare("
            UPDATE user 
            SET twofa_verified = 1, last_login = NOW(), last_ip = :ip 
            WHERE id = :id
        ")->execute([
            'id' => $userId,
            'ip' => $ip
        ]);

        return ['status' => 'success', 'message' => 'Uspešno ste se prijavili.'];
    }

}
