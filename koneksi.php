<?php
// koneksi.php — Konfigurasi koneksi database osmipa_db

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Ganti sesuai username MySQL Anda
define('DB_PASS', '');           // Ganti sesuai password MySQL Anda
define('DB_NAME', 'osmipa_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'status'  => 'error',
        'message' => 'Koneksi database gagal: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');