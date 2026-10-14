<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

if (!APP_DEBUG) {
    ini_set('display_errors', '0');
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die(APP_DEBUG ? 'Database connection failed: ' . $e->getMessage() : 'Service unavailable. Please try again later.');
}