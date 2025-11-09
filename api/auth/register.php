<?php
session_start();
require_once __DIR__ . '../../config/koneksi.php';

// Cek apakah method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['message' => 'Invalid request method']);
    exit();
}

// Mendapatkan koneksi database
$conn = getConnection();
$data = json_decode(file_get_contents("php://input"), true);

// Validasi input
if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['message' => 'Name, Email, and Password are required']);
    exit();
}

// Cek apakah email sudah digunakan
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $data['email']]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['message' => 'Email is already registered']);
    exit();
}

// Hash password
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

// Simpan user baru ke database
$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
$stmt->execute([
    ':name' => htmlspecialchars($data['name']),
    ':email' => htmlspecialchars($data['email']),
    ':password' => $hashed_password
]);

echo json_encode(['message' => 'Registration successful']);
