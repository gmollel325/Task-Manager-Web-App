<?php
require_once 'config.php';

function is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function is_admin()
{
    return !empty($_SESSION['is_admin']);
}

function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        redirect('index.php');
    }
}

function get_all_users($pdo)
{
    $stmt = $pdo->prepare('SELECT id, username, is_admin, created_at FROM users ORDER BY username ASC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function log_admin_action($pdo, $adminUserId, $targetType, $targetId, $action, $details = '')
{
    $stmt = $pdo->prepare('INSERT INTO admin_logs (admin_user_id, target_type, target_id, action, details, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$adminUserId, $targetType, $targetId, $action, $details]);
}

function get_admin_logs($pdo, $limit = 20)
{
    $stmt = $pdo->prepare('SELECT l.*, u.username AS admin_username FROM admin_logs l JOIN users u ON l.admin_user_id = u.id ORDER BY l.created_at DESC LIMIT ?');
    $stmt->bindValue(1, (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sanitize($value)
{
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

function get_tasks($pdo, $user_id, $status = null, $search = null, $category = null, $sort = 'default')
{
    $sql = 'SELECT * FROM tasks WHERE user_id = ?';
    $params = [$user_id];

    if ($status === 'pending' || $status === 'done') {
        $sql .= ' AND status = ?';
        $params[] = $status;
    }

    if ($category === 'Work' || $category === 'Personal' || $category === 'Study' || $category === 'Other') {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }

    if ($search !== null && $search !== '') {
        $sql .= ' AND (title LIKE ? OR description LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    switch ($sort) {
        case 'due_asc':
            $sql .= ' ORDER BY due_date IS NULL, due_date ASC, FIELD(status, "pending", "done"), FIELD(priority, "high", "medium", "low"), created_at DESC';
            break;
        case 'due_desc':
            $sql .= ' ORDER BY due_date IS NULL, due_date DESC, FIELD(status, "pending", "done"), FIELD(priority, "high", "medium", "low"), created_at DESC';
            break;
        case 'priority':
            $sql .= ' ORDER BY FIELD(priority, "high", "medium", "low"), FIELD(status, "pending", "done"), due_date IS NULL, due_date ASC, created_at DESC';
            break;
        default:
            if ($status === 'pending' || $status === 'done') {
                $sql .= ' ORDER BY FIELD(priority, "high", "medium", "low"), due_date IS NULL, due_date ASC, created_at DESC';
            } else {
                $sql .= ' ORDER BY FIELD(status, "pending", "done"), FIELD(priority, "high", "medium", "low"), due_date IS NULL, due_date ASC, created_at DESC';
            }
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_task($pdo, $task_id, $user_id)
{
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$task_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_task_by_id($pdo, $task_id)
{
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
    $stmt->execute([$task_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_by_id($pdo, $user_id)
{
    $stmt = $pdo->prepare('SELECT id, username, is_admin, created_at FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function task_count($pdo, $user_id, $status = null)
{
    if ($status === 'pending' || $status === 'done') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = ?');
        $stmt->execute([$user_id, $status]);
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ?');
        $stmt->execute([$user_id]);
    }
    return (int) $stmt->fetchColumn();
}

function category_counts($pdo, $user_id)
{
    $stmt = $pdo->prepare('SELECT category, COUNT(*) AS count FROM tasks WHERE user_id = ? GROUP BY category');
    $stmt->execute([$user_id]);
    $counts = ['Work' => 0, 'Personal' => 0, 'Study' => 0, 'Other' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($counts[$row['category']])) {
            $counts[$row['category']] = (int) $row['count'];
        }
    }
    return $counts;
}
