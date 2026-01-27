<?php
include 'auth_check.php';
include 'db.php';

// Must receive template_id from previous page
if (!isset($_GET['template_id'])) {
    die("Template ID is required.");
}

$template_id = intval($_GET['template_id']);

// Fetch template info
$template = $conn->query("SELECT template_name FROM project_templates WHERE template_id = $template_id")->fetch_assoc();

if (!$template) {
    die("Template not found.");
}

// Fetch existing steps
$steps = $conn->query("
    SELECT step_number, step_description 
    FROM project_template_steps 
    WHERE template_id = $template_id 
    ORDER BY step_number ASC
");

// Determine next step number
$next_step_number = $steps->num_rows + 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Template Steps</title>
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
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header .subtitle {
            color: #667eea;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .header .user-info {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .steps-list {
            list-style: none;
            padding: 0;
            counter-reset: step-counter;
        }
        
        .step-item {
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s;
            counter-increment: step-counter;
        }
        
        .step-item:hover {
            background: #e8f4f8;
            transform: translateX(4px);
        }
        
        .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .step-number::before {
            content: counter(step-counter);
        }
        
        .step-description {
            color: #2c3e50;
            font-weight: 500;
            font-size: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 12px;
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
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input:hover:not(:read-only) {
            border-color: #b8c2cc;
        }
        
        .form-group input:read-only {
            background: #f8f9fa;
            color: #7f8c8d;
            cursor: not-allowed;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .nav-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.2s;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .nav-link:hover {
            background: #f8f9fa;
            transform: translateX(-4px);
        }
        
        .helper-text {
            color: #7f8c8d;
            font-size: 13px;
            margin-top: 6px;
        }
        
        .next-step-badge {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .container {
                max-width: 100%;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .card {
                padding: 20px;
            }
            
            .nav-links {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔢 Add Template Steps</h1>
            <p class="subtitle">Template: <?php echo htmlspecialchars($template['template_name']); ?></p>
            <p class="user-info">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        
        <div class="card">
            <h2>📋 Existing Steps</h2>
            
            <?php if ($steps->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>No steps added yet. Create your first step below!</p>
                </div>
            <?php else: ?>
                <ul class="steps-list">
                    <?php while ($row = $steps->fetch_assoc()): ?>
                        <li class="step-item">
                            <div class="step-number"></div>
                            <div class="step-description">
                                <?php echo htmlspecialchars($row['step_description']); ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>➕ Add New Step</h2>
            
            <form method="POST" action="process_add_step.php">
                <input type="hidden" name="template_id" value="<?php echo $template_id; ?>">
                
                <div class="form-group">
                    <label>
                        Step Number
                        <span class="next-step-badge">Next: <?php echo $next_step_number; ?></span>
                    </label>
                    <input type="number" name="step_number" value="<?php echo $next_step_number; ?>" readonly>
                    <p class="helper-text">Step number is automatically assigned</p>
                </div>
                
                <div class="form-group">
                    <label>Step Description</label>
                    <input type="text" name="step_description" placeholder="e.g., Cut Material, Assembly, Quality Check" required>
                    <p class="helper-text">Describe what happens in this production step</p>
                </div>
                
                <button type="submit" class="btn">✓ Add Step</button>
            </form>
        </div>
        
        <div class="nav-links">
            <a href="add_template.php" class="nav-link">
                ← Back to Templates
            </a>
            <a href="index.php" class="nav-link">
                🏠 Home
            </a>
        </div>
    </div>
</body>
</html>