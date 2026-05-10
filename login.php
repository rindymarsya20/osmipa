<?php
// login.php — Endpoint autentikasi pengguna

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}

require_once 'koneksi.php';

// Ambil data JSON dari body request
$body  = json_decode(file_get_contents('php://input'), true);
$email = trim($body['email'] ?? '');
$pwd   = $body['pwd']        ?? '';

// ── Validasi ──────────────────────────────────────────────────────────────────
if (!$email || !$pwd) {
    echo json_encode(['status' => 'error', 'message' => 'Masukkan email dan kata sandi']);
    exit;
}

// ── Cari user di database ─────────────────────────────────────────────────────
$stmt = $conn->prepare(
    'SELECT id, nama_depan, nama_belakang, email, password
     FROM users WHERE email = ? LIMIT 1'
);
$stmt->bind_param('s', $email);
$stmt->execute();

$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// ── Verifikasi password ───────────────────────────────────────────────────────
if (!$user || !password_verify($pwd, $user['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email atau kata sandi salah']);
    exit;
}

// ── Login berhasil ────────────────────────────────────────────────────────────
echo json_encode([
    'status'  => 'success',
    'message' => 'Login berhasil',
    'user'    => [
        'id'           => $user['id'],
        'namaDepan'    => $user['nama_depan'],
        'namaBelakang' => $user['nama_belakang'],
        'email'        => $user['email'],
    ]
]);

$conn->close();