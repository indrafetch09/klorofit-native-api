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

// Mendapatkan nama user
$queryUser = "SELECT name FROM users WHERE id = :user_id";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmtUser->execute();
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Validasi user ada
if (!$user) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit();
}

// Mendapatkan tanggal hari ini
$date_today = date('Y-m-d');

// Mengambil data konsumsi makanan user hari ini
$query = "SELECT nl.name, nl.calories, nl.protein, nl.carbs, nl.fat, ft.quantity, ft.consumed_at, 
                 (nl.calories * ft.quantity) AS total_calories, 
                 (nl.protein * ft.quantity) AS total_protein, 
                 (nl.carbs * ft.quantity) AS total_carbs, 
                 (nl.fat * ft.quantity) AS total_fat
          FROM food_tracking ft
          JOIN nutrition_library nl ON ft.food_id = nl.id
          WHERE ft.user_id = :user_id AND DATE(ft.consumed_at) = CURRENT_DATE";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kalkulasi total nutrisi
$total_calories = 0;
$total_protein = 0;
$total_carbs = 0;
$total_fat = 0;

foreach ($data as $item) {
    $total_calories += $item['total_calories'];
    $total_protein += $item['total_protein'];
    $total_carbs += $item['total_carbs'];
    $total_fat += $item['total_fat'];
}

// Response JSON
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'name' => $user['name'],
    'date' => $date_today,
    'total_calories' => $total_calories,
    'total_protein' => $total_protein,
    'total_carbs' => $total_carbs,
    'total_fat' => $total_fat,
    'data' => $data
]);
?>
