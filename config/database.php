<?php
// File: config/database.php

define('DB_HOST',    'localhost');
define('DB_NAME',    'note_management');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ✅ Đổi dòng này tùy môi trường:
// Chưa có VirtualHost → '/Final_Project/public'
// Đã có VirtualHost   → ''
define('BASE_URL', '/Final_Project/public');

function getDB(): PDO {
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $conn = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Lỗi kết nối cơ sở dữ liệu']));
    }

    return $conn;
}