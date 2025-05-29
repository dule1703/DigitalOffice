<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PDO;
use App\Core\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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

    // Ako je korisnik već 2FA-verifikovan, generiši token i loguj ga direktno
   if ($user['twofa_verified'] == 1) {
    // Proveri da li je prethodni token istekao (više od 1 sat)
    if (!empty($user['last_login'])) {
        $lastLogin = new \DateTime($user['last_login']);
        $now = new \DateTime();
        $diffInSeconds = $now->getTimestamp() - $lastLogin->getTimestamp();

        if ($diffInSeconds > 3600) {
            // Token je istekao, resetuj 2FA i idi na generisanje novog koda
            $this->resetTwoFactorStatus($user['id']);
        } else {
            // Token je još važeći → osveži last_login i generiši novi token
            $this->conn->prepare("
                UPDATE user SET last_login = :now WHERE id = :id
            ")->execute([
                'now' => $now->format('Y-m-d H:i:s'),
                'id' => $user['id']
            ]);

            $token = $this->generateJWT($user['id']);

            return [
                'status' => 'success',
                'user_id' => $user['id'],
                'token' => $token,
                '2fa_verified' => true
            ];
        }
    }
  }
    // Generiši novi 2FA kod
    $twofa_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = (new \DateTime('+10 minutes'))->format('Y-m-d H:i:s');

    // Ažuriraj kod i resetuj 2FA status
    $update = $this->conn->prepare("
        UPDATE user 
        SET twofa_code = :code, twofa_expiry = :expiry, twofa_verified = 0 
        WHERE id = :id
    ");
    $update->execute([
        'code' => $twofa_code,
        'expiry' => $expiry,
        'id' => $user['id']
    ]);

    // Pošalji kod na mejl
    $emailSent = $this->sendTwoFactorCode($user['email'], $twofa_code);

    return [
        'status' => 'success',
        'message' => 'Kod za potvrdu je poslat na email.',
        'user_id' => $user['id'],
        '2fa_verified' => false,
        'twofa_sent' => $emailSent
    ];
}

    public function resetTwoFactorStatus($userId) {
        $stmt = $this->conn->prepare("UPDATE user SET twofa_verified = 0 WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }

    private function generateJWT($userId) {
    $payload = [
        'user_id' => $userId,
        'exp' => time() + 3600 // 1h
    ];

    return \Firebase\JWT\JWT::encode($payload, getenv('JWT_SECRET'), 'HS256');
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
            
            if ($mail->send()) {
                error_log("✅ Mail je uspešno poslat na " . $toEmail);
            } else {
                error_log("❌ Slanje mejla nije uspelo: " . $mail->ErrorInfo);
            }

            return true;
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public function verifyTwoFactorCode($userId, $code) {
    $stmt = $this->conn->prepare("SELECT email, twofa_code, twofa_expiry FROM user WHERE id = :id");
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

    // ✅ Kod je ispravan, update 2FA status
    $ip = $_SERVER['REMOTE_ADDR'];
    $this->conn->prepare("
        UPDATE user 
        SET twofa_verified = 1, last_login = NOW(), last_ip = :ip 
        WHERE id = :id
    ")->execute([
        'id' => $userId,
        'ip' => $ip
    ]);

    // ✅ JWT token payload
    $baseUrl = getenv('APP_URL') ?: 'http://localhost';

    $payload = [
        'iss' => $baseUrl,
        'aud' => $baseUrl,
        'iat' => time(),
        'exp' => time() + 3600, // Za test – promeni na 3600 u produkciji
        'user_id' => $userId,
        'email' => $user['email']
    ];

    $jwtSecret = getenv('JWT_SECRET');
    $token = \Firebase\JWT\JWT::encode($payload, $jwtSecret, 'HS256');

    return [
        'status' => 'success',
        'message' => 'Uspešno ste se prijavili.',
        'user_id' => $userId,
        'token' => $token
    ];
}


   public function sendResetToken($email) {
    try {
        // 1. Provera da li korisnik postoji
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['status' => 'error', 'message' => 'Korisnik ne postoji.'];
        }

        // 2. Generisanje tokena i isteka
        $token = bin2hex(random_bytes(32));
        $expiry = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');

        // 3. Čuvanje u bazu
        $update = $this->conn->prepare("
            UPDATE user 
            SET reset_token = :token, reset_expiry = :expiry 
            WHERE id = :id
        ");
        $update->execute([
            'token' => $token,
            'expiry' => $expiry,
            'id' => $user['id']
        ]);

        error_log("Token za reset postavljen korisniku ID {$user['id']}: $token (istice $expiry)");

        // 4. Generisanje linka na osnovu APP_URL iz .env
        $baseUrl = getenv('APP_URL') ?: 'http://localhost';
        $resetLink = rtrim($baseUrl, '/') . '/reset-password?token=' . $token;

        error_log("Reset link: $resetLink");

        // 5. Slanje mejla
        $emailSent = $this->sendResetEmail($email, $resetLink);

        if (!$emailSent) {
            error_log("Greška: Slanje mejla za reset nije uspelo korisniku $email.");
            return ['status' => 'error', 'message' => 'Došlo je do greške prilikom slanja emaila.'];
        }

        return ['status' => 'success', 'message' => 'Link za reset lozinke je poslat.'];

    } catch (\Exception $e) {
        error_log("Greška u sendResetToken(): " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Greška na serveru. Pokušajte kasnije.'];
    }
}


    private function sendResetEmail($toEmail, $link) {
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
            $mail->Subject = 'Reset lozinke';
            $mail->Body    = "Kliknite na sledeći link da resetujete lozinku: <br><a href='$link'>$link</a><br>Link važi 1 sat.";
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }


}
