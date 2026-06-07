<?php
require_once 'functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$username = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $password === '' || $passwordConfirm === '') {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'That username is already taken.';
        } else {
            $isAdmin = 0;
            $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE is_admin = 1');
            if ((int) $stmt->fetchColumn() === 0) {
                $isAdmin = 1;
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)');
            $stmt->execute([$username, $passwordHash, $isAdmin]);
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Register &raquo; Task Manager</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div class="page">
    <h1>Task Manager</h1>
    <div class="card">
        <h2>Register</h2>
        <?php if ($error !== ''): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="register.php" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" maxlength="100" />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" />

            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" />

            <button type="submit">Create Account</button>
        </form>
        <p class="small">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</div>
</body>
</html>
