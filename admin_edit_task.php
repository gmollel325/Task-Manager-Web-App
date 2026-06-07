<?php
require_once 'functions.php';
require_admin();

$taskId = isset($_GET['task_id']) ? (int) $_GET['task_id'] : 0;
$task = get_task_by_id($pdo, $taskId);
if (!$task) {
    redirect('admin.php');
}

$users = get_all_users($pdo);
$error = '';
$title = $task['title'];
$description = $task['description'];
$due_date = $task['due_date'];
$priority = $task['priority'];
$category = $task['category'];
$status = $task['status'];
$approval_status = isset($task['approval_status']) ? $task['approval_status'] : 'pending';
$assignedUserId = $task['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $due_date = sanitize($_POST['due_date'] ?? '');
    $priority = sanitize($_POST['priority'] ?? 'medium');
    $category = sanitize($_POST['category'] ?? 'Other');
    $status = sanitize($_POST['status'] ?? 'pending');
    $approval_status = sanitize($_POST['approval_status'] ?? 'pending');
    $assignedUserId = isset($_POST['assigned_user_id']) ? (int) $_POST['assigned_user_id'] : $assignedUserId;

    if ($title === '') {
        $error = 'Task title is required.';
    } elseif (!in_array($priority, ['low', 'medium', 'high'], true)) {
        $priority = 'medium';
    } elseif (!in_array($category, ['Work', 'Personal', 'Study', 'Other'], true)) {
        $category = 'Other';
    } elseif (!in_array($status, ['pending', 'done'], true)) {
        $status = 'pending';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute([$assignedUserId]);
        if (!$stmt->fetch()) {
            $error = 'Assigned user does not exist.';
        } else {
            $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ?, category = ?, status = ?, approval_status = ?, user_id = ? WHERE id = ?');
            $stmt->execute([
                $title,
                $description,
                $due_date !== '' ? $due_date : null,
                $priority,
                $category,
                $status,
                $approval_status,
                $assignedUserId,
                $taskId,
            ]);
            log_admin_action($pdo, $_SESSION['user_id'], 'task', $taskId, 'edit_task', 'Updated task details, status=' . $status . ', approval=' . $approval_status . ', assigned_user_id=' . $assignedUserId);
            redirect('admin.php');
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Edit Task &raquo; Task Manager</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div class="page">
    <div class="top-bar">
        <h1>Edit Task</h1>
        <div class="nav-right">
            <a class="button small" href="admin.php">Back to Admin</a>
            <a class="button small logout" href="logout.php">Logout</a>
        </div>
    </div>
    <div class="card">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form action="admin_edit_task.php?task_id=<?php echo $taskId; ?>" method="post">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" />

            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="assigned_user_id">Assigned User</label>
            <select id="assigned_user_id" name="assigned_user_id">
                <?php foreach ($users as $userRow): ?>
                    <option value="<?php echo $userRow['id']; ?>"<?php echo $assignedUserId === (int) $userRow['id'] ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($userRow['username'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="Work"<?php echo $category === 'Work' ? ' selected="selected"' : ''; ?>>Work</option>
                <option value="Personal"<?php echo $category === 'Personal' ? ' selected="selected"' : ''; ?>>Personal</option>
                <option value="Study"<?php echo $category === 'Study' ? ' selected="selected"' : ''; ?>>Study</option>
                <option value="Other"<?php echo $category === 'Other' ? ' selected="selected"' : ''; ?>>Other</option>
            </select>

            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <option value="high"<?php echo $priority === 'high' ? ' selected="selected"' : ''; ?>>High</option>
                <option value="medium"<?php echo $priority === 'medium' ? ' selected="selected"' : ''; ?>>Medium</option>
                <option value="low"<?php echo $priority === 'low' ? ' selected="selected"' : ''; ?>>Low</option>
            </select>

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="pending"<?php echo $status === 'pending' ? ' selected="selected"' : ''; ?>>Pending</option>
                <option value="done"<?php echo $status === 'done' ? ' selected="selected"' : ''; ?>>Done</option>
            </select>

            <label for="approval_status">Approval Status</label>
            <select id="approval_status" name="approval_status">
                <option value="pending"<?php echo $approval_status === 'pending' ? ' selected="selected"' : ''; ?>>Pending Approval</option>
                <option value="approved"<?php echo $approval_status === 'approved' ? ' selected="selected"' : ''; ?>>Approved</option>
                <option value="rejected"<?php echo $approval_status === 'rejected' ? ' selected="selected"' : ''; ?>>Rejected</option>
            </select>

            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($due_date, ENT_QUOTES, 'UTF-8'); ?>" />

            <button type="submit">Update Task</button>
        </form>
    </div>
</div>
</body>
</html>
