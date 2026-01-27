<?php
include 'auth_check.php';

// If user is not Admin, redirect to user_dashboard.php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: user_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSS Tracking Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }
        
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
        }
        
        .card-header h2 {
            color: #2c3e50;
            font-size: 20px;
            font-weight: 600;
        }
        
        .card-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .card-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .card-link:hover {
            background: #e8f4f8;
            border-color: #3498db;
            transform: translateX(5px);
        }
        
        .card-link::before {
            content: '→';
            margin-right: 10px;
            font-weight: bold;
            color: #3498db;
        }
        
        .setup .card-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .production .card-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .reports .card-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .quick-actions {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .quick-actions h2 {
            margin-bottom: 15px;
            font-size: 22px;
        }
        
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-action-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s;
            display: block;
        }
        
        .quick-action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: white;
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .logout-btn {
                position: static;
                display: block;
                margin-top: 15px;
                width: fit-content;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="logout.php" class="logout-btn">🚪 Logout</a>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p>MSS Production Tracking Dashboard</p>
    </div>
    
    <div class="quick-actions">
        <h2>⚡ Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="start_project.php" class="quick-action-btn">🏷️ Start New Project</a>
            <a href="scan.php" class="quick-action-btn">📱 Scan Project</a>
            <a href="view_projects.php" class="quick-action-btn">📊 View All Projects</a>
            <a href="view_labels.php" class="quick-action-btn">🏷️ View Labels</a>
        </div>
    </div>
    
    <div style="height: 30px;"></div>
    
    <div class="dashboard-grid">
        <div class="card setup">
            <div class="card-header">
                <div class="card-icon">⚙️</div>
                <h2>Setup & Configuration</h2>
            </div>
            <div class="card-links">
                <a href="add_customer.php" class="card-link">Add Customer</a>
                <a href="add_template.php" class="card-link">Add Project Template</a>
                <a href="edit_template.php" class="card-link">Edit Template (Steps, Fields, Options)</a>
            </div>
        </div>
        
        <div class="card production">
            <div class="card-header">
                <div class="card-icon">🏭</div>
                <h2>Production</h2>
            </div>
            <div class="card-links">
                <a href="start_project.php" class="card-link">Start New Project (Generate Label)</a>
                <a href="scan.php" class="card-link">Scan Project (Update Steps)</a>
            </div>
        </div>
        
        <div class="card reports">
            <div class="card-header">
                <div class="card-icon">📈</div>
                <h2>Reports & Status</h2>
            </div>
            <div class="card-links">
                <a href="view_projects.php" class="card-link">View All Projects</a>
                <a href="view_labels.php" class="card-link">View Labels</a>
            </div>
        </div>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
            <div class="card admin">
            <div class="card-header">
                <div class="card-icon">🛡️</div>
                <h2>Admin</h2>
            </div>
            <div class="card-links">
                <a href="create_user.php" class="card-link">Add New User Profile</a>
            </div>
        </div>
<?php endif; ?>
    </div>
</body>
</html>