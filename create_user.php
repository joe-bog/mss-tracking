<?php
session_start();
include 'db.php';

/*
|--------------------------------------------------------------------------
| ADMIN ACCESS ONLY
|--------------------------------------------------------------------------
*/
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    strtolower($_SESSION['user_role']) !== 'admin'
) {
    http_response_code(403);
    echo "403 Forbidden — Admins only.";
    exit;
}

$message = '';
$message_type = '';

/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $role  = trim($_POST['role'] ?? 'user');
    $password = $_POST['password'] ?? '';

    if ($first === '' || $last === '' || $password === '') {
        $message = "All fields are required.";
        $message_type = "error";
    } else {

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (first_name, last_name, role, password)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $first, $last, $role, $hashedPassword);

        if ($stmt->execute()) {
            $message = "User '{$first} {$last}' created successfully.";
            $message_type = "success";
        } else {
            $message = "Database error: " . $stmt->error;
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User (Admin)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            border-radius: 8px;
            border: 2px solid #ddd;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.95;
        }

        .back {
            text-align: center;
            margin-top: 18px;
        }

        .back a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>👤 Create New User</h1>

    <?php if ($message): ?>
        <div class="message <?= htmlspecialchars($message_type) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label>Temporary Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Create User</button>
    </form>

    <div class="back">
        <a href="index.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
