<?php
require_once __DIR__ . '/constants.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Return JSON if called from an API endpoint
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['PHP_SELF'] ?? '', '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
            exit;
        }
        die('<h3 style="color:red;font-family:sans-serif">Database connection failed. Check your XAMPP MySQL server and config/constants.php credentials.</h3>');
    }
    return $pdo;
}
