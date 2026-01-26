<?php
include 'auth_check.php';

// Redirect admins to the full dashboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSS Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            width: 100%;
            max-width: 900px;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .welcome-text {
            color: #7f8c8d;
            font-size: 20px;
            font-weight: 500;
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(231, 76, 60, 0.4);
        }
        
        .main-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .action-card {
            background: white;
            border-radius: 20px;
            padding: 50px 30px;
            text-align: center;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border: 4px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .action-card:hover::before {
            opacity: 1;
        }
        
        .action-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border-color: #667eea;
        }
        
        .action-icon {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
            position: relative;
            z-index: 1;
        }
        
        .action-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .action-description {
            font-size: 16px;
            color: #7f8c8d;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
                align-items: flex-start;
            }
            
            .container {
                margin-top: 20px;
            }
            
            .header {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .welcome-text {
                font-size: 16px;
            }
            
            .logout-btn {
                position: static;
                display: inline-block;
                margin-top: 15px;
                font-size: 14px;
                padding: 10px 20px;
            }
            
            .main-actions {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .action-card {
                padding: 40px 25px;
            }
            
            .action-icon {
                font-size: 64px;
            }
            
            .action-title {
                font-size: 22px;
            }
            
            .action-description {
                font-size: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .action-icon {
                font-size: 56px;
            }
            
            .action-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
            <h1>MSS Tracking</h1>
            <p class="welcome-text">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>
        
        <div class="main-actions">
            <a href="start_project.php" class="action-card">
                <span class="action-icon">🏷️</span>
                <div class="action-title">Start Project</div>
                <div class="action-description">Create a new project</div>
            </a>
            
            <a href="scan.php" class="action-card">
                <span class="action-icon">📱</span>
                <div class="action-title">Scan Label</div>
                <div class="action-description">Update project progress</div>
            </a>
            
            <a href="view_projects.php" class="action-card">
                <span class="action-icon">📊</span>
                <div class="action-title">View Projects</div>
                <div class="action-description">See all projects</div>
            </a>
            
            <a href="view_labels.php" class="action-card">
                <span class="action-icon">🔍</span>
                <div class="action-title">View Labels</div>
                <div class="action-description">Search labels</div>
            </a>
        </div>
    </div>
</body>
</html>