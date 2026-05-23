<?php
require_once __DIR__ . '/../classes/Database.php';

try {
    $pdo = Database::getConnection();
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
