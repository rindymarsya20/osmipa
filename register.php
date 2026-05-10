<?php
// register.php — Endpoint registrasi pengguna baru

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
$body        = json_decode(file_get_contents('php://input'), true);
$namaDepan   = trim($body['namaDepan']   ?? '');
$namaBelakang = trim($body['namaBelakang'] ?? '');
$email       = trim($body['email']       ?? '');
$pwd         = $body['pwd']              ?? '';

// ── Validasi ─────────────────────────────────────────────────────────────────
if (!$namaDepan || !$namaBelakang || !$email || !$pwd) {
    echo json_encode(['status' => 'error', 'message' => 'Semua kolom harus diisi']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid']);
    exit;
}

if (strlen($pwd) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Kata sandi minimal 6 karakter']);
    exit;
}

// ── Cek duplikat email ────────────────────────────────────────────────────────
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar']);
    exit;
}
$stmt->close();

// ── Simpan user baru ──────────────────────────────────────────────────────────
$hashedPwd = password_hash($pwd, PASSWORD_BCRYPT);

$stmt = $conn->prepare(
    'INSERT INTO users (nama_depan, nama_belakang, email, password, created_at)
     VALUES (?, ?, ?, ?, NOW())'
);
$stmt->bind_param('ssss', $namaDepan, $namaBelakang, $email, $hashedPwd);

if ($stmt->execute()) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Akun berhasil dibuat! Silakan masuk.'
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal menyimpan data: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();