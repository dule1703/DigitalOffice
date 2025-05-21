<?php
require_once __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['token'], $data['new_password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Token i nova lozinka su obavezni.']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT id, reset_expiry FROM user WHERE reset_token = :token");
$stmt->execute(['token' => $data['token']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Neispravan token.']);
    exit;
}

$now = new \DateTime();
$expiry = new \DateTime($user['reset_expiry']);

if ($now > $expiry) {
    echo json_encode(['status' => 'error', 'message' => 'Token je istekao.']);
    exit;
}

// Ažuriraj lozinku i očisti token
$hashed = password_hash($data['new_password'], PASSWORD_DEFAULT);

$update = $conn->prepare("
    UPDATE user 
    SET password = :pass, reset_token = NULL, reset_expiry = NULL 
    WHERE id = :id
");
$update->execute([
    'pass' => $hashed,
    'id' => $user['id']
]);

echo json_encode(['status' => 'success', 'message' => 'Lozinka je uspešno resetovana.']);
