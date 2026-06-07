<?php
require_once 'functions.php';
require_login();

$taskId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if ($taskId > 0) {
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$taskId, $userId]);
}

redirect('index.php');
