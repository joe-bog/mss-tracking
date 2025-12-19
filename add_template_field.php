<?php
include 'auth_check.php';
include 'db.php';

if (isset($_POST['template_id'])) {
    $template_id = intval($_POST['template_id']);
} elseif (isset($_GET['template_id'])) {
    $template_id = intval($_GET['template_id']);
} else {
    die("Template ID is required.");
}

// Fetch template info
$template = $conn->query("
    SELECT template_name 
    FROM project_templates 
    WHERE template_id = $template_id
")->fetch_assoc();

if (!$template) {
    die("Template not found.");
}

// Fetch existing fields for this template
$fields = $conn->query("
    SELECT field_id, field_name
    FROM project_template_fields
    WHERE template_id = $template_id
    ORDER BY field_id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Template Fields</title>
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
        
        .fields-list {
            list-style: none;
            padding: 0;
        }
        
        .field-item {
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }
        
        .field-item:hover {
            background: #e8f4f8;
            transform: translateX(4px);
        }
        
        .field-name {
            color: #2c3e50;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .field-name::before {
            content: '📝';
        }
        
        .add-options-btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .add-options-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
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
        
        .form-group input:hover {
            border-color: #b8c2cc;
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
            
            .field-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .add-options-btn {
                width: 100%;
                justify-content: center;
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
            <h1>📋 Add Template Fields</h1>
            <p class="subtitle">Template: <?php echo htmlspecialchars($template['template_name']); ?></p>
            <p class="user-info">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        
        <div class="card">
            <h2>📝 Existing Fields</h2>
            
            <?php if ($fields->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <p>No fields added yet. Create your first field below!</p>
                </div>
            <?php else: ?>
                <ul class="fields-list">
                    <?php while ($row = $fields->fetch_assoc()): ?>
                        <li class="field-item">
                            <span class="field-name">
                                <?php echo htmlspecialchars($row['field_name']); ?>
                            </span>
                            <a href="add_field_options.php?field_id=<?php echo $row['field_id']; ?>" class="add-options-btn">
                                ⚙️ Add Options
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>➕ Add New Field</h2>
            
            <form method="POST" action="process_add_field.php">
                <input type="hidden" name="template_id" value="<?php echo $template_id; ?>">
                
                <div class="form-group">
                    <label>Field Name</label>
                    <input type="text" name="field_name" placeholder="e.g., Style, Color, Size" required>
                    <p class="helper-text">This field will be used when starting new projects</p>
                </div>
                
                <button type="submit" class="btn">✓ Add Field</button>
            </form>
        </div>
        
        <div class="nav-links">
            <a href="add_template_steps.php?template_id=<?php echo $template_id; ?>" class="nav-link">
                ← Back to Steps
            </a>
            <a href="index.php" class="nav-link">
                🏠 Home
            </a>
        </div>
    </div>
</body>
</html>