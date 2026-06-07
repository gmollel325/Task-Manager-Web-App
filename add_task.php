<?php
require_once 'functions.php';
require_login();

$userId = $_SESSION['user_id'];
$error = '';
$title = '';
$description = '';
$due_date = '';
$priority = 'medium';
$category = 'Other';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $due_date = sanitize($_POST['due_date'] ?? '');
    $priority = sanitize($_POST['priority'] ?? 'medium');
    $category = sanitize($_POST['category'] ?? 'Other');

    if ($title === '') {
        $error = 'Task title is required.';
    } elseif (!in_array($priority, ['low', 'medium', 'high'], true)) {
        $priority = 'medium';
    } elseif (!in_array($category, ['Work', 'Personal', 'Study', 'Other'], true)) {
        $category = 'Other';
    } else {
        $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, description, due_date, priority, category) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $title, $description, $due_date !== '' ? $due_date : null, $priority, $category]);
        redirect('index.php');
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Add Task &raquo; Task Manager</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript">
    function validateForm() {
        var title = document.getElementById('title').value.trim();
        if (title === '') {
            alert('Please enter a task title.');
            document.getElementById('title').focus();
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
<div class="page">
    <div class="top-bar">
        <h1>Add Task</h1>
        <div class="nav-right">
            <a class="button small" href="index.php">Back to Dashboard</a>
        </div>
    </div>
    <div class="card">
        <?php if ($error !== ''): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="add_task.php" method="post" onsubmit="return validateForm();">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo $title; ?>" maxlength="255" />

            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo $description; ?></textarea>

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

            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo $due_date; ?>" />

            <button type="submit">Save Task</button>
        </form>
    </div>
</div>
</body>
</html>
