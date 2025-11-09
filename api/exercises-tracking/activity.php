<?php
session_start();
// koneksi database
require_once __DIR__ . '../../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            if (isset($_GET['id'])) {
                // Mendapatkan aktivitas berdasarkan ID milik user yang login
                $id = $_GET['id'];
                $query = "SELECT * FROM activities WHERE id = :id AND user_id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->execute([':id' => $id, ':user_id' => $user_id]);
                $activity = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($activity) {
                    echo json_encode(['status' => 'success', 'data' => $activity]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Activity not found']);
                }
            } else {
                // Mendapatkan semua aktivitas user yang login
                $query = "SELECT * FROM activities WHERE user_id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->execute([':user_id' => $user_id]);
                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['status' => 'success', 'data' => $activities]);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'], $data['duration_minutes'], $data['calories_burned'], $data['activity_date'])) {
            echo json_encode(['message' => 'Invalid data provided']);
            exit();
        }

        try {
            $query = "INSERT INTO activities (user_id, name, duration_minutes, calories_burned, activity_date) 
                      VALUES (:user_id, :name, :duration_minutes, :calories_burned, :activity_date)";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':name' => $data['name'],
                ':duration_minutes' => $data['duration_minutes'],
                ':calories_burned' => $data['calories_burned'],
                ':activity_date' => $data['activity_date']
            ]);

            echo json_encode(['message' => 'Activity created successfully']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            echo json_encode(['message' => 'ID not provided']);
            exit();
        }

        $id = $_GET['id'];
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            $query = "UPDATE activities SET name = :name, duration_minutes = :duration_minutes, 
                      calories_burned = :calories_burned, activity_date = :activity_date 
                      WHERE id = :id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':user_id' => $user_id,
                ':name' => $data['name'],
                ':duration_minutes' => $data['duration_minutes'],
                ':calories_burned' => $data['calories_burned'],
                ':activity_date' => $data['activity_date']
            ]);

            echo json_encode(['message' => 'Activity updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(['message' => 'ID not provided']);
            exit();
        }

        $id = $_GET['id'];

        try {
            $query = "DELETE FROM activities WHERE id = :id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id, ':user_id' => $user_id]);

            echo json_encode(['message' => 'Activity deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}
