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
$username = $_SESSION['username'];
$tasks = get_tasks($pdo, $userId, $statusFilter, $search, $categoryFilter, $sort);
$totalTasks = task_count($pdo, $userId);
$pendingTasks = task_count($pdo, $userId, 'pending');
$doneTasks = task_count($pdo, $userId, 'done');
$completionPercent = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;
$categoryTotals = category_counts($pdo, $userId);

$baseQuery = [];
if ($search !== '') {
    $baseQuery['search'] = $search;
}
if ($categoryFilter !== null) {
    $baseQuery['category'] = $categoryFilter;
}
if ($sort !== 'default') {
    $baseQuery['sort'] = $sort;
}
if ($statusFilter !== null) {
    $baseQuery['status'] = $statusFilter;
}
$allQuery = http_build_query($baseQuery);
$pendingQuery = http_build_query(array_merge($baseQuery, ['status' => 'pending']));
$doneQuery = http_build_query(array_merge($baseQuery, ['status' => 'done']));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Dashboard &raquo; Task Manager</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div class="page">
    <div class="top-bar">
        <h1>Task Manager</h1>
        <div class="nav-right">
            <span>Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
            <a class="button small" href="add_task.php">Add Task</a>
            <?php if (!empty($_SESSION['is_admin'])): ?>
                <a class="button small" href="admin.php">Admin Panel</a>
            <?php endif; ?>
            <a class="button small" href="export.php<?php echo $allQuery !== '' ? '?' . $allQuery : ''; ?>">Export CSV</a>
            <a class="button small logout" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="summary">
        <a class="summary-card<?php echo $statusFilter === null ? ' active-card' : ''; ?>" href="index.php<?php echo $allQuery !== '' ? '?' . $allQuery : ''; ?>">
            <strong><?php echo $totalTasks; ?></strong>
            <span>Total Tasks</span>
        </a>
        <a class="summary-card<?php echo $statusFilter === 'pending' ? ' active-card' : ''; ?>" href="index.php<?php echo $pendingQuery !== '' ? '?' . $pendingQuery : ''; ?>">
            <strong><?php echo $pendingTasks; ?></strong>
            <span>Pending</span>
        </a>
        <a class="summary-card<?php echo $statusFilter === 'done' ? ' active-card' : ''; ?>" href="index.php<?php echo $doneQuery !== '' ? '?' . $doneQuery : ''; ?>">
            <strong><?php echo $doneTasks; ?></strong>
            <span>Completed</span>
        </a>
        <div class="summary-card progress-card">
            <strong><?php echo $completionPercent; ?>%</strong>
            <span>Completion</span>
            <div class="progress-bar">
                <div class="progress-bar-inner" style="width: <?php echo $completionPercent; ?>%;"></div>
            </div>
        </div>
    </div>

    <div class="category-summary">
        <a class="summary-card<?php echo $categoryFilter === null ? ' active-card' : ''; ?>" href="index.php<?php echo $search !== '' || $statusFilter !== null ? '?' . http_build_query(array_filter(['search' => $search, 'status' => $statusFilter])) : ''; ?>">
            <strong><?php echo $categoryTotals['Work'] + $categoryTotals['Personal'] + $categoryTotals['Study'] + $categoryTotals['Other']; ?></strong>
            <span>All categories</span>
        </a>
        <a class="summary-card<?php echo $categoryFilter === 'Work' ? ' active-card' : ''; ?>" href="index.php?category=Work<?php echo ($search !== '' ? '&search=' . urlencode($search) : '') . ($statusFilter !== null ? '&status=' . urlencode($statusFilter) : ''); ?>">
            <strong><?php echo $categoryTotals['Work']; ?></strong>
            <span>Work</span>
        </a>
        <a class="summary-card<?php echo $categoryFilter === 'Personal' ? ' active-card' : ''; ?>" href="index.php?category=Personal<?php echo ($search !== '' ? '&search=' . urlencode($search) : '') . ($statusFilter !== null ? '&status=' . urlencode($statusFilter) : ''); ?>">
            <strong><?php echo $categoryTotals['Personal']; ?></strong>
            <span>Personal</span>
        </a>
        <a class="summary-card<?php echo $categoryFilter === 'Study' ? ' active-card' : ''; ?>" href="index.php?category=Study<?php echo ($search !== '' ? '&search=' . urlencode($search) : '') . ($statusFilter !== null ? '&status=' . urlencode($statusFilter) : ''); ?>">
            <strong><?php echo $categoryTotals['Study']; ?></strong>
            <span>Study</span>
        </a>
        <a class="summary-card<?php echo $categoryFilter === 'Other' ? ' active-card' : ''; ?>" href="index.php?category=Other<?php echo ($search !== '' ? '&search=' . urlencode($search) : '') . ($statusFilter !== null ? '&status=' . urlencode($statusFilter) : ''); ?>">
            <strong><?php echo $categoryTotals['Other']; ?></strong>
            <span>Other</span>
        </a>
    </div>

    <div class="toolbar">
        <div class="filters">
            <a href="index.php<?php echo $search !== '' || $categoryFilter !== null ? '?' . http_build_query(array_filter(['search' => $search, 'category' => $categoryFilter])) : ''; ?>"<?php echo $statusFilter === null ? ' class="active"' : ''; ?>>All</a>
            <a href="index.php?status=pending<?php echo ($search !== '' ? '&search=' . urlencode($search) : '') . ($categoryFilter !== null ? '&category=' . urlencode($categoryFilter) : ''); ?>"<?php echo $statusFilter === 'pending' ? ' class="active"' : ''; ?>>Pending</a>
            <a href="index.php?status=done<?php echo ($search !== '' ? '&search=' . urlencode($search) : '') . ($categoryFilter !== null ? '&category=' . urlencode($categoryFilter) : ''); ?>"<?php echo $statusFilter === 'done' ? ' class="active"' : ''; ?>>Completed</a>
        </div>
        <div class="search-box">
            <form action="index.php" method="get">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search tasks..." />
                <select name="category">
                    <option value=""<?php echo $categoryFilter === null ? ' selected="selected"' : ''; ?>>All categories</option>
                    <option value="Work"<?php echo $categoryFilter === 'Work' ? ' selected="selected"' : ''; ?>>Work</option>
                    <option value="Personal"<?php echo $categoryFilter === 'Personal' ? ' selected="selected"' : ''; ?>>Personal</option>
                    <option value="Study"<?php echo $categoryFilter === 'Study' ? ' selected="selected"' : ''; ?>>Study</option>
                    <option value="Other"<?php echo $categoryFilter === 'Other' ? ' selected="selected"' : ''; ?>>Other</option>
                </select>
                <select name="sort">
                    <option value="default"<?php echo $sort === 'default' ? ' selected="selected"' : ''; ?>>Sort: default</option>
                    <option value="due_asc"<?php echo $sort === 'due_asc' ? ' selected="selected"' : ''; ?>>Sort: due date ↑</option>
                    <option value="due_desc"<?php echo $sort === 'due_desc' ? ' selected="selected"' : ''; ?>>Sort: due date ↓</option>
                    <option value="priority"<?php echo $sort === 'priority' ? ' selected="selected"' : ''; ?>>Sort: priority</option>
                </select>
                <?php if ($statusFilter !== null): ?>
                    <input type="hidden" name="status" value="<?php echo $statusFilter; ?>" />
                <?php endif; ?>
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <p>No tasks found. Click "Add Task" to create your first task.</p>
        </div>
    <?php else: ?>
        <table class="task-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')); ?></td>
                        <td><?php echo $task['due_date'] ?: '-'; ?></td>
                        <td><?php echo ucfirst($task['priority']); ?></td>
                        <td><?php echo htmlspecialchars($task['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="status-pill <?php echo $task['status']; ?>"><?php echo ucfirst($task['status']); ?></span></td>
                        <td>
                            <a class="button tiny" href="edit_task.php?id=<?php echo $task['id']; ?>">Edit</a>
                            <a class="button tiny" href="mark_task.php?id=<?php echo $task['id']; ?>"><?php echo $task['status'] === 'done' ? 'Mark Pending' : 'Mark Done'; ?></a>
                            <a class="button tiny danger" href="delete_task.php?id=<?php echo $task['id']; ?>" onclick="return confirm('Delete this task?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
