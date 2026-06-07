<?php
require_once 'functions.php';
require_admin();

$currentUserId = $_SESSION['user_id'];
$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$user = get_user_by_id($pdo, $userId);
if (!$user) {
    redirect('admin.php');
}

$error = '';
$message = '';
$username = $user['username'];
$isAdmin = $user['is_admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $isAdmin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1' ? 1 : 0;

    if ($username === '') {
        $error = 'Username is required.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $stmt->execute([$username, $userId]);
        if ($stmt->fetch()) {
            $error = 'That username is already taken.';
        } else {
            if ($userId === $currentUserId) {
                $isAdmin = 1;
            }
            $params = [$username, $isAdmin, $userId];
            $sql = 'UPDATE users SET username = ?, is_admin = ? WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$passwordHash, $userId]);
            }
            $message = 'User updated successfully.';
            log_admin_action($pdo, $currentUserId, 'user', $userId, 'edit_user', 'Username updated to ' . $username . ', is_admin=' . $isAdmin . ($password !== '' ? ', password changed' : ''));
            if ($userId === $currentUserId) {
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = 1;
            }
            $user = get_user_by_id($pdo, $userId);
            $isAdmin = $user['is_admin'];
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Edit User &raquo; Task Manager</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div class="page">
    <div class="top-bar">
        <h1>Edit User</h1>
        <div class="nav-right">
            <a class="button small" href="admin.php">Back to Admin</a>
            <a class="button small logout" href="logout.php">Logout</a>
        </div>
    </div>
    <div class="card">
        <?php if ($message !== ''): ?>
            <div class="alert"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form action="admin_edit_user.php?user_id=<?php echo $userId; ?>" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" />

            <label for="password">Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" />

            <?php if ($userId !== $currentUserId): ?>
                <label for="is_admin">Admin Role</label>
                <select id="is_admin" name="is_admin">
                    <option value="0"<?php echo $isAdmin ? '' : ' selected="selected"'; ?>>User</option>
                    <option value="1"<?php echo $isAdmin ? ' selected="selected"' : ''; ?>>Admin</option>
                </select>
            <?php else: ?>
                <div class="small">Your own admin role cannot be changed here.</div>
            <?php endif; ?>

            <button type="submit">Save User</button>
        </form>
    </div>
</div>
</body>
</html>
