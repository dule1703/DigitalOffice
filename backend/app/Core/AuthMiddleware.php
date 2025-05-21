<?php

namespace App\Core;

require_once __DIR__ . '/../../config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;



class AuthMiddleware
{
    public static function validateToken(): array
    {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token nije prosleđen.']);
            exit;
        }

        $token = trim(str_replace('Bearer', '', $authHeader));
        $secret = getenv('JWT_SECRET');

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (array)$decoded; // možeš koristiti $decoded->uid itd.
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Nevažeći ili istekao token.']);
            exit;
        }
    }
}
