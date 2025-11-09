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
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(['message' => 'Email and Password are required']);
    exit();
}

// Ambil data user dari database
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifikasi password
if ($user && password_verify($data['password'], $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    echo json_encode([
        'message' => 'Login successful',
        'user' => [
            'name' => $user['name'],
        ]
    ]);
} else {
    echo json_encode(['message' => 'Invalid email or password']);
}
