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

// Mendapatkan total kalori dari makanan yang user tambahkan
$query = "SELECT nl.name, nl.calories, ft.quantity, (nl.calories * ft.quantity) AS total_calories 
          FROM food_tracking ft 
          JOIN nutrition_library nl ON ft.food_id = nl.id 
          WHERE ft.user_id = :user_id";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kalkulasi total kalori
$total_calories = 0;
foreach ($data as $item) {
    $total_calories += $item['total_calories'];
}

http_response_code(200);
echo json_encode(['status' => 'success', 'total_calories' => $total_calories, 'data' => $data]);
?>
