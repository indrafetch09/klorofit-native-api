<?php
// Fungsi global untuk koneksi database
function getConnection() {
    static $conn;
    if ($conn === null) {
        $host = 'localhost';
        $dbname = 'db_nutrition_app';
        $username = 'root';
        $password = '';

        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die(json_encode(['message' => 'Connection failed: ' . $e->getMessage()]));
        }
    }
    return $conn;
}
