<?php
session_start();

$DB_HOST = '127.0.0.1';
$DB_PORT = '3307';
$DB_NAME = 'task_manager';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        'mysql:host=' . $DB_HOST . ';port=' . $DB_PORT . ';dbname=' . $DB_NAME . ';charset=utf8mb4',
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Database Error</title></head><body>';
    echo '<h1>Database connection failed</h1>';
    echo '<p>Please check <code>config.php</code> and your MySQL settings.</p>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
    exit;
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}
