<?php
require_once 'functions.php';
require_login();

$taskId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if ($taskId > 0) {
    $task = get_task($pdo, $taskId, $userId);
    if ($task) {
        $newStatus = $task['status'] === 'done' ? 'pending' : 'done';
        $stmt = $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$newStatus, $taskId, $userId]);
    }
}

redirect('index.php');
