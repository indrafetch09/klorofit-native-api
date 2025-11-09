<?php
session_start();

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Koneksi ke database
require_once __DIR__ . '../../config/koneksi.php';

// Set header response JSON
header('Content-Type: application/json');
$conn = getConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

