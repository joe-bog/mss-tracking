 <?php
session_start();
include 'db.php';

/*
|--------------------------------------------------------------------------
| If already logged in, go to appropriate dashboard
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin') {
        header("Location: index.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}

/*
|--------------------------------------------------------------------------
| Build login messages from query params
|--------------------------------------------------------------------------
*/
$message = "";
$message_type = "";

if (isset($_GET['err'])) {
    if ($_GET['err'] === 'missing') {
        $message = "Please select your name.";
        $message_type = "error";
    } elseif ($_GET['err'] === 'invalid') {
        $message = "Invalid user selection.";
        $message_type = "error";
    } elseif ($_GET['err'] === 'user') {
        $message = "User not found.";
        $message_type = "error";
    } else {
        $message = "Login failed. Please try again.";
        $message_type = "error";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $message = "You’ve been logged out.";
    $message_type = "success";
}

/*
|--------------------------------------------------------------------------
| Fetch users for dropdown
|--------------------------------------------------------------------------
*/
$result = $conn->query("SELECT user_id, first_name, last_name FROM users ORDER BY first_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MSS Tracking</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container { max-width: 500px; width: 100%; }

        .logo-header { text-align: center; margin-bottom: 30px; }

        .logo-header h1 {
            color: white;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .logo-header p { color: rgba(255, 255, 255, 0.9); font-size: 16px; }

        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
        }

        .message {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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

        .form-group { margin-bottom: 25px; }

        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: inherit;
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }

        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active { transform: translateY(0); }

        .helper {
            margin-top: 14px;
            font-size: 13px;
            color: #7f8c8d;
            text-align: center;
        }

        @media (max-width: 768px) {
            body { padding: 15px; }
            .logo-header h1 { font-size: 28px; }
            .card { padding: 30px 25px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-header">
            <h1>🏭 MSS Tracking</h1>
            <p>Production Management System</p>
        </div>

        <div class="card">
            <?php if ($message): ?>
                <div class="message <?= htmlspecialchars($message_type) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <h2>Welcome Back</h2>

            <form method="POST" action="authenticate.php" autocomplete="off">
                <div class="form-group">
                    <label>Select Your Name</label>
                    <select name="user_id" required>
                        <option value="">-- Select Your Name --</option>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['user_id']) ?>">
                                <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Enter →</button>

                <div class="helper">
                    If you don’t have access, contact an admin to create your user.
                </div>
            </form>
        </div>
    </div>
</body>
</html>
