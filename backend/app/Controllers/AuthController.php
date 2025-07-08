<?php

namespace App\Controllers;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register($name, $email, $password, $company_id = 1) {
        if ($this->userModel->findByEmail($email)) {
            return ['status' => 'error', 'message' => 'Email je već registrovan.'];
        }

        $this->userModel->create($name, $email, $password, $company_id);
        return ['status' => 'success', 'message' => 'Uspešna registracija.'];
    }

   public function login($email, $password) {
    $user = $this->userModel->findByEmail($email);
    if (!$user || !password_verify($password, $user['password'])) {
        return [
            'status' => 'error',
            'message' => 'Neispravan email ili lozinka.',
            'twofa' => false
        ];
    }

    if ($user['twofa_verified'] == 1) {
        if (!empty($user['last_login'])) {
            $lastLogin = new \DateTime($user['last_login']);
            $now = new \DateTime();
            $diff = $now->getTimestamp() - $lastLogin->getTimestamp();

            if ($diff > 2 * 24 * 3600) {
                $this->userModel->reset2FAStatus($user['id']);
            } else {
                $this->userModel->updateLastLogin($user['id'], $now->format('Y-m-d H:i:s'));
                return [
                    'status' => 'success',
                    'user_id' => $user['id'],
                    'token' => $this->generateJWT($user['id']),
                    '2fa_verified' => true,
                    'twofa' => false // Ne šalje se novi kod
                ];
            }
        }
    }

    // Generiši novi 2FA kod i pokušaj slanje
    $twofa_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = (new \DateTime('+10 minutes'))->format('Y-m-d H:i:s');

    $this->userModel->update2FACode($user['id'], $twofa_code, $expiry);
    $emailSent = $this->sendTwoFactorCode($user['email'], $twofa_code);

    if (!$emailSent) {
        // Loguj grešku lokalno (opciono)
        error_log("[2FA EMAIL ERROR] Neuspešno slanje za korisnika ID {$user['id']}, email: {$user['email']}");

        return [
            'status' => 'error',
            'message' => 'Kod nije mogao biti poslat na email. Pokušajte ponovo ili kontaktirajte podršku.',
            'twofa' => false
        ];
    }

    return [
        'status' => 'success',
        'message' => 'Kod za potvrdu je poslat na email.',
        'user_id' => $user['id'],
        '2fa_verified' => false,
        'twofa' => true
    ];
   }


    public function verify2FA($userId, $code) {
        $user = $this->userModel->findById($userId);
        if (!$user || $user['twofa_code'] !== $code) {
            return ['status' => 'error', 'message' => 'Kod nije ispravan.'];
        }

        $now = new \DateTime();
        $expiry = new \DateTime($user['twofa_expiry']);
        if ($now > $expiry) {
            return ['status' => 'error', 'message' => 'Kod je istekao.'];
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $this->userModel->verify2FA($userId, $ip);

        return [
            'status' => 'success',
            'message' => 'Uspešno ste se prijavili.',
            'user_id' => $userId,
            'token' => $this->generateJWT($userId)
        ];
    }

public function sendResetToken($email) {
    $user = $this->userModel->findByEmail($email);
    if (!$user) {
        return ['status' => 'error', 'message' => 'Korisnik ne postoji.'];
    }

    $token = bin2hex(random_bytes(32));
    $expiry = (new \DateTime('+12 hour'))->format('Y-m-d H:i:s');
    $this->userModel->saveResetToken($user['id'], $token, $expiry);

    // Dozvoljeni frontend domeni
    $origins = explode(',', getenv('ALLOWED_ORIGINS') ?: '');

    // Logika: ako je HTTP_ORIGIN definisan, koristi ga ako postoji u listi
    $frontendUrl = $origins[0]; // podrazumevani

    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $origins)) {
        $frontendUrl = $_SERVER['HTTP_ORIGIN'];
    }

    $resetLink = rtrim($frontendUrl, '/') . '/reset-password?token=' . $token;

    $emailSent = $this->sendResetEmail($email, $resetLink);

    return $emailSent
        ? ['status' => 'success', 'message' => 'Link za reset lozinke je poslat.']
        : ['status' => 'error', 'message' => 'Greška prilikom slanja emaila.'];
}


    public function submitNewPassword($token, $newPassword) {
        $user = $this->userModel->findByResetToken($token);
        if (!$user) {
            return ['status' => 'error', 'message' => 'Neispravan token.'];
        }

        $now = new \DateTime();
        $expiry = new \DateTime($user['reset_expiry']);
        if ($now > $expiry) {
            return ['status' => 'error', 'message' => 'Token je istekao.'];
        }

        $this->userModel->updatePassword($user['id'], $newPassword);
        return ['status' => 'success', 'message' => 'Lozinka je uspešno resetovana.'];
    }

    private function generateJWT($userId) {
        $payload = [
            'user_id' => $userId,
            'exp' => time() + (2 * 24 * 3600)
        ];
        return JWT::encode($payload, getenv('JWT_SECRET'), 'HS256');
    }

    private function sendTwoFactorCode($toEmail, $code) {
        return $this->sendEmail(
            $toEmail,
            'Vaš 2FA kod za prijavu',
            "Vaš kod za potvrdu je: <strong>$code</strong><br>Važi 10 minuta."
        );
    }

    private function sendResetEmail($toEmail, $link) {
        return $this->sendEmail(
            $toEmail,
            'Reset lozinke',
            "Kliknite na sledeći link da resetujete lozinku:<br><a href='$link'>$link</a><br>Link važi 1 sat."
        );
    }

    private function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER');
            $mail->Password = getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = getenv('SMTP_PORT');
            $mail->setFrom(getenv('SMTP_FROM'), getenv('SMTP_FROM_NAME'));
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            return $mail->send();
        } catch (Exception $e) {
            error_log('Email error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
