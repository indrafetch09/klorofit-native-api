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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && isset($_GET['q'])) {
    $keyword = '%' . $_GET['q'] . '%';

    try {
        $query = "SELECT * FROM nutrition_library WHERE name LIKE :keyword";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'massage' => 'Data not found']);
            exit();
        }
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $results]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// Proses food tracking (GET, POST, DELETE)
switch ($method) {
    case 'GET':
        $query = "SELECT ft.*, nl.name, nl.calories FROM food_tracking ft 
                  JOIN nutrition_library nl ON ft.food_id = nl.id 
                  WHERE ft.user_id = :user_id ORDER BY ft.consumed_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($data)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'massage' => 'Data Kosong']);
            exit();
        }
        
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['food_id'], $data['quantity'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Incomplete data']);
            exit();
        }

        $query = "INSERT INTO food_tracking (user_id, food_id, quantity, consumed_at) VALUES (:user_id, :food_id, :quantity, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':food_id' => $data['food_id'],
            ':quantity' => $data['quantity']
        ]);
        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Food tracked successfully']);
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $query = "DELETE FROM food_tracking WHERE id = :id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Food data deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
