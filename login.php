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

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = !empty($user['is_admin']) ? 1 : 0;
            redirect('index.php');
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Login &raquo; Task Manager</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div class="page">
    <h1>Task Manager</h1>
    <div class="card">
        <h2>Login</h2>
        <?php if ($error !== ''): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" maxlength="100" />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" />

            <button type="submit">Sign In</button>
        </form>
        <p class="small">Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</div>
</body>
</html>
