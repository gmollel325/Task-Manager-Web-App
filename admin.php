<?php
require_once 'functions.php';
require_admin();

$currentUserId = $_SESSION['user_id'];
$message = '';
$error = '';

$userSearch = isset($_GET['user_search']) ? trim($_GET['user_search']) : '';
$userRole = isset($_GET['user_role']) ? $_GET['user_role'] : '';
$taskSearch = isset($_GET['task_search']) ? trim($_GET['task_search']) : '';
$taskStatus = isset($_GET['task_status']) ? $_GET['task_status'] : '';
$taskApproval = isset($_GET['task_approval']) ? $_GET['task_approval'] : '';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$targetUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$targetTaskId = isset($_GET['task_id']) ? (int) $_GET['task_id'] : 0;

if ($action === 'toggle_admin' && $targetUserId > 0) {
    if ($targetUserId === $currentUserId) {
        $error = 'You cannot change your own admin status here.';
    } else {
        $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
        $stmt->execute([$targetUserId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $newStatus = $user['is_admin'] ? 0 : 1;
            $stmt = $pdo->prepare('UPDATE users SET is_admin = ? WHERE id = ?');
            $stmt->execute([$newStatus, $targetUserId]);
            $message = $newStatus ? 'User promoted to admin.' : 'User demoted from admin.';
            log_admin_action($pdo, $currentUserId, 'user', $targetUserId, 'toggle_admin', $message);
        }
    }
}

if ($action === 'delete_user' && $targetUserId > 0) {
    if ($targetUserId === $currentUserId) {
        $error = 'You cannot delete your own account from the admin panel.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$targetUserId]);
        $message = 'User deleted successfully.';
        log_admin_action($pdo, $currentUserId, 'user', $targetUserId, 'delete_user', $message);
    }
}

if ($action === 'delete_task' && $targetTaskId > 0) {
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->execute([$targetTaskId]);
    $message = 'Task deleted successfully.';
    log_admin_action($pdo, $currentUserId, 'task', $targetTaskId, 'delete_task', $message);
}


$userParams = [];
$userSql = 'SELECT id, username, is_admin, created_at FROM users WHERE 1=1';
if ($userSearch !== '') {
    $userSql .= ' AND username LIKE ?';
    $userParams[] = '%' . $userSearch . '%';
}
if ($userRole === 'admin') {
    $userSql .= ' AND is_admin = 1';
} elseif ($userRole === 'user') {
    $userSql .= ' AND is_admin = 0';
}
$userSql .= ' ORDER BY username ASC';
$stmt = $pdo->prepare($userSql);
$stmt->execute($userParams);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$taskParams = [];
$taskSql = 'SELECT t.*, u.username FROM tasks t JOIN users u ON t.user_id = u.id WHERE 1=1';
if ($taskSearch !== '') {
    $taskSql .= ' AND (t.title LIKE ? OR t.description LIKE ? OR u.username LIKE ?)';
    $taskParams[] = '%' . $taskSearch . '%';
    $taskParams[] = '%' . $taskSearch . '%';
    $taskParams[] = '%' . $taskSearch . '%';
}
if ($taskStatus === 'pending' || $taskStatus === 'done') {
    $taskSql .= ' AND t.status = ?';
    $taskParams[] = $taskStatus;
}
if ($taskApproval === 'pending' || $taskApproval === 'approved' || $taskApproval === 'rejected') {
    $taskSql .= ' AND t.approval_status = ?';
    $taskParams[] = $taskApproval;
}
$taskSql .= ' ORDER BY t.created_at DESC';
$stmt = $pdo->prepare($taskSql);
$stmt->execute($taskParams);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalUsers = count($users);
$totalTasks = count($tasks);
$pendingTasks = 0;
$doneTasks = 0;
foreach ($tasks as $task) {
    if ($task['status'] === 'pending') {
        $pendingTasks++;
    } elseif ($task['status'] === 'done') {
        $doneTasks++;
    }
}

$adminLogs = get_admin_logs($pdo, 20);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin Panel &raquo; Task Manager</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div class="page">
    <div class="top-bar">
        <h1>Admin Panel</h1>
        <div class="nav-right">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
            <a class="button small" href="index.php">Dashboard</a>
            <a class="button small logout" href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="summary">
        <div class="summary-card active-card">
            <strong><?php echo $totalUsers; ?></strong>
            <span>Registered Users</span>
        </div>
        <div class="summary-card">
            <strong><?php echo $totalTasks; ?></strong>
            <span>Total Tasks</span>
        </div>
        <div class="summary-card">
            <strong><?php echo $pendingTasks; ?></strong>
            <span>Pending Tasks</span>
        </div>
        <div class="summary-card">
            <strong><?php echo $doneTasks; ?></strong>
            <span>Completed Tasks</span>
        </div>
    </div>

    <div class="admin-filters">
        <form action="admin.php" method="get" class="admin-filter-form">
            <div class="filter-group">
                <h3>User filters</h3>
                <input type="text" name="user_search" placeholder="Search users..." value="<?php echo htmlspecialchars($userSearch, ENT_QUOTES, 'UTF-8'); ?>" />
                <select name="user_role">
                    <option value=""<?php echo $userRole === '' ? ' selected="selected"' : ''; ?>>All roles</option>
                    <option value="admin"<?php echo $userRole === 'admin' ? ' selected="selected"' : ''; ?>>Admin</option>
                    <option value="user"<?php echo $userRole === 'user' ? ' selected="selected"' : ''; ?>>User</option>
                </select>
            </div>
            <div class="filter-group">
                <h3>Task filters</h3>
                <input type="text" name="task_search" placeholder="Search tasks or users..." value="<?php echo htmlspecialchars($taskSearch, ENT_QUOTES, 'UTF-8'); ?>" />
                <select name="task_status">
                    <option value=""<?php echo $taskStatus === '' ? ' selected="selected"' : ''; ?>>All statuses</option>
                    <option value="pending"<?php echo $taskStatus === 'pending' ? ' selected="selected"' : ''; ?>>Pending</option>
                    <option value="done"<?php echo $taskStatus === 'done' ? ' selected="selected"' : ''; ?>>Completed</option>
                </select>
                <select name="task_approval">
                    <option value=""<?php echo $taskApproval === '' ? ' selected="selected"' : ''; ?>>All approvals</option>
                    <option value="pending"<?php echo $taskApproval === 'pending' ? ' selected="selected"' : ''; ?>>Approval pending</option>
                    <option value="approved"<?php echo $taskApproval === 'approved' ? ' selected="selected"' : ''; ?>>Approved</option>
                    <option value="rejected"<?php echo $taskApproval === 'rejected' ? ' selected="selected"' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="button small">Apply filters</button>
                <a class="button small" href="admin.php">Reset</a>
            </div>
        </form>
    </div>

    <div class="admin-section">
        <h2>Users</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></td>
                        <td><?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="admin_edit_user.php?user_id=<?php echo $user['id']; ?>">Edit</a>
                            <?php if ($user['id'] !== $currentUserId): ?>
                                |
                                <a href="admin.php?action=toggle_admin&user_id=<?php echo $user['id']; ?>"><?php echo $user['is_admin'] ? 'Demote' : 'Promote'; ?></a>
                                |
                                <a href="admin.php?action=delete_user&user_id=<?php echo $user['id']; ?>" onclick="return confirm('Delete this user and their tasks?');">Delete</a>
                            <?php else: ?>
                                <span class="small">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-section">
        <h2>All Tasks</h2>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th>Due Date</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="8">No tasks available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($task['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($task['priority'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($task['approval_status'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo htmlspecialchars($task['due_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($task['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <a href="admin_edit_task.php?task_id=<?php echo $task['id']; ?>">Edit</a>
                                |
                                <a href="admin.php?action=delete_task&task_id=<?php echo $task['id']; ?>" onclick="return confirm('Delete this task?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-section">
        <h2>Activity Log</h2>
        <table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($adminLogs)): ?>
                    <tr>
                        <td colspan="5">No activity yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($adminLogs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($log['admin_username'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($log['target_type'] . ' #' . $log['target_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($log['details'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
