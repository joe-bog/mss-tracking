<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['user_id'])) {
    header("Location: login.php?err=missing");
    exit;
}

$user_id = (int)$_POST['user_id'];

if ($user_id <= 0 || $password === '') {
    header("Location: login.php?err=missing");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get user info
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT user_id, first_name, last_name, role
    FROM users
    WHERE user_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: login.php?err=user");
    exit;
}

$user = $result->fetch_assoc();

$dbRole = $user['role'] ?? 'user';


/*
|--------------------------------------------------------------------------
| Successful login: store session data
|--------------------------------------------------------------------------
*/
session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$_SESSION['user_role'] = $dbRole;

/*
|--------------------------------------------------------------------------
| Role-based redirect
|--------------------------------------------------------------------------
*/
if ($dbRole === 'Admin') {
    header("Location: index.php");
} else {
    header("Location: user_dashboard.php");
}
exit;
