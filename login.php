<?php
session_start();
include 'db.php';

// 1. HANDLE NEW USER SIGNUP (Logic runs if the "Add User" form is submitted)
$message = "";
$message_type = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    // Basic sanitization
    $first = $conn->real_escape_string($_POST['new_first_name']);
    $last = $conn->real_escape_string($_POST['new_last_name']);

    if(!empty($first) && !empty($last)){
        $sql = "INSERT INTO users (first_name, last_name) VALUES ('$first', '$last')";
        
        if($conn->query($sql) === TRUE){
            $message = "User '$first $last' added successfully! Please log in below.";
            $message_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "error";
        }
    }
}

// 2. FETCH USERS (Runs after the insert, so the new user is included)
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 500px;
            width: 100%;
        }
        
        .logo-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-header h1 {
            color: white;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .logo-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
        }
        
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
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-group select {
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }
        
        .form-group input:focus,
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
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            padding: 12px 24px;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-top: 15px;
        }
        
        .btn-secondary:hover {
            background: #f8f9fa;
        }
        
        #signup-form {
            display: none;
            animation: slideDown 0.3s ease;
        }
        
        #signup-form.show {
            display: block;
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #e0e6ed;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            color: #7f8c8d;
            font-size: 14px;
            position: relative;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .logo-header h1 {
                font-size: 28px;
            }
            
            .card {
                padding: 30px 25px;
            }
        }
    </style>
    <script>
        function toggleSignup() {
            var form = document.getElementById("signup-form");
            var btn = document.getElementById("toggle-btn");
            
            if (form.classList.contains("show")) {
                form.classList.remove("show");
                btn.textContent = "Create New User";
            } else {
                form.classList.add("show");
                btn.textContent = "Cancel";
            }
        }
    </script>
</head>
<body>
    <div class="login-container">
        <div class="logo-header">
            <h1>🏭 MSS Tracking</h1>
            <p>Production Management System</p>
        </div>
        
        <div class="card">
            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <h2>Welcome Back</h2>
            
            <form method="POST" action="authenticate.php">
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
                
                <button type="submit" class="btn-primary">Login →</button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <button id="toggle-btn" class="btn-secondary" onclick="toggleSignup()">
                👤 Create New User
            </button>
            
            <div id="signup-form">
                <div class="divider" style="margin-top: 30px;">
                    <span>New User Registration</span>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="new_first_name" required placeholder="John">
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="new_last_name" required placeholder="Doe">
                    </div>
                    
                    <button type="submit" name="add_user" class="btn-primary">
                        ✓ Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>