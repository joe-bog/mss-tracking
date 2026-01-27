<?php
include 'auth_check.php';
include 'db.php';

// Must receive field_id
if (!isset($_GET['field_id'])) {
    die("Field ID is required.");
}

$field_id = intval($_GET['field_id']);

// Fetch field info
$field = $conn->query("
    SELECT field_name, template_id 
    FROM project_template_fields 
    WHERE field_id = $field_id
")->fetch_assoc();

if (!$field) {
    die("Field not found.");
}

$template_id = $field['template_id'];

// Fetch template info
$template = $conn->query("
    SELECT template_name 
    FROM project_templates 
    WHERE template_id = $template_id
")->fetch_assoc();

// Load existing options
$options = $conn->query("
    SELECT option_id, option_name
    FROM project_template_field_options
    WHERE field_id = $field_id
    ORDER BY option_id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Field Options</title>
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
        
        .options-list {
            list-style: none;
            padding: 0;
        }
        
        .options-list li {
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .options-list li::before {
            content: '✓';
            color: #27ae60;
            font-weight: bold;
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
            
            .nav-links {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Add Field Options</h1>
            <p class="subtitle">Field: <?php echo htmlspecialchars($field['field_name']); ?></p>
            <p class="subtitle" style="color: #95a5a6; font-size: 16px;">Template: <?php echo htmlspecialchars($template['template_name']); ?></p>
            <p class="user-info">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        
        <div class="card">
            <h2>📋 Existing Options</h2>
            
            <?php if ($options->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>No options added yet.</p>
                </div>
            <?php else: ?>
                <ul class="options-list">
                    <?php while ($row = $options->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['option_name']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>➕ Add New Option</h2>
            
            <form method="POST" action="process_add_option.php">
                <input type="hidden" name="field_id" value="<?php echo $field_id; ?>">
                
                <div class="form-group">
                    <label>Option Name</label>
                    <input type="text" name="option_name" placeholder='e.g., 7", Chestnut' required>
                    <p class="helper-text">Enter a value for this field option</p>
                </div>
                
                <button type="submit" class="btn">✓ Add Option</button>
            </form>
        </div>
        
        <div class="nav-links">
            <a href="add_template_field.php?template_id=<?php echo $template_id; ?>" class="nav-link">
                ← Back to Template Fields
            </a>
            <a href="index.php" class="nav-link">
                🏠 Home
            </a>
        </div>
    </div>
</body>
</html>