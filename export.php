<?php
require_once 'functions.php';
require_login();

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
if ($statusFilter !== 'pending' && $statusFilter !== 'done') {
    $statusFilter = null;
}

$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
if ($categoryFilter !== 'Work' && $categoryFilter !== 'Personal' && $categoryFilter !== 'Study' && $categoryFilter !== 'Other') {
    $categoryFilter = null;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
if ($sort !== 'default' && $sort !== 'due_asc' && $sort !== 'due_desc' && $sort !== 'priority') {
    $sort = 'default';
}

$userId = $_SESSION['user_id'];
$tasks = get_tasks($pdo, $userId, $statusFilter, $search, $categoryFilter, $sort);

$filename = 'tasks_export_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
if ($output === false) {
    exit;
}

// UTF-8 BOM for Excel compatibility
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['Title', 'Description', 'Due Date', 'Priority', 'Category', 'Status', 'Created At']);
foreach ($tasks as $task) {
    fputcsv($output, [
        $task['title'],
        $task['description'],
        $task['due_date'],
        ucfirst($task['priority']),
        $task['category'],
        ucfirst($task['status']),
        $task['created_at'],
    ]);
}
fclose($output);
exit;
