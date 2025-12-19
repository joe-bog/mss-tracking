<?php
include 'auth_check.php';
include 'db.php';

/* -------------------------------
   LOAD FIELD
--------------------------------*/
if (!isset($_GET['field_id'])) {
    die("Field ID is required.");
}

$field_id = intval($_GET['field_id']);

// Load field info
$field = $conn->query("
    SELECT f.field_name, f.template_id, t.template_name
    FROM project_template_fields f
    JOIN project_templates t ON f.template_id = t.template_id
    WHERE f.field_id = $field_id
")->fetch_assoc();

if (!$field) {
    die("Field not found.");
}

/* -------------------------------
   ADD OPTION (INLINE)
--------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {

    $option_name = trim($_POST['option_name']);

    if ($option_name === '') {
        die("Option name required.");
    }

    $stmt = $conn->prepare("
        INSERT INTO project_template_field_options (field_id, option_name)
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $field_id, $option_name);
    $stmt->execute();

    header("Location: edit_field_options.php?field_id=$field_id");
    exit;
}

/* -------------------------------
   DELETE OPTION
--------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_option'])) {

    $option_id = intval($_POST['option_id']);

    $stmt = $conn->prepare("
        DELETE FROM project_template_field_options
        WHERE option_id = ?
    ");
    $stmt->bind_param("i", $option_id);
    $stmt->execute();

    header("Location: edit_field_options.php?field_id=$field_id");
    exit;
}

// Load existing options
$options = $conn->query("
    SELECT option_id, option_name
    FROM project_template_field_options
    WHERE field_id = $field_id
    ORDER BY option_name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Field Options</title>
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
        
        .header .template-info {
            color: #95a5a6;
            font-size: 16px;
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
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .option-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.2s;
            gap: 16px;
        }
        
        .option-item:hover {
            background: #e8f4f8;
        }
        
        .option-name {
            color: #2c3e50;
            font-weight: 500;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        
        .option-name::before {
            content: '•';
            color: #667eea;
            font-size: 24px;
            font-weight: bold;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .delete-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }
        
        .delete-btn:active {
            transform: translateY(0);
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
        
        .back-link {
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
        
        .back-link:hover {
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
            
            .option-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .delete-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Field Options</h1>
            <p class="subtitle">Field: <?= htmlspecialchars($field['field_name']) ?></p>
            <p class="template-info">Template: <?= htmlspecialchars($field['template_name']) ?></p>
        </div>
        
        <div class="card">
            <h2>📋 Current Options</h2>
            
            <?php if ($options->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>No options added yet. Create your first option below!</p>
                </div>
            <?php else: ?>
                <div class="options-list">
                    <?php while ($o = $options->fetch_assoc()): ?>
                        <div class="option-item">
                            <span class="option-name">
                                <?= htmlspecialchars($o['option_name']) ?>
                            </span>
                            
                            <form method="post" style="margin: 0;">
                                <input type="hidden" name="option_id" value="<?= $o['option_id'] ?>">
                                <input type="hidden" name="delete_option" value="1">
                                
                                <button type="submit" 
                                        class="delete-btn"
                                        onclick="return confirm('Delete this option?');">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>➕ Add New Option</h2>
            
            <form method="post">
                <input type="hidden" name="add_option" value="1">
                
                <div class="form-group">
                    <label>Option Name</label>
                    <input type="text" 
                           name="option_name" 
                           placeholder='e.g., 7", Blue, Matte' 
                           required>
                    <p class="helper-text">Enter a value for this field option</p>
                </div>
                
                <button type="submit" class="btn">✓ Add Option</button>
            </form>
        </div>
        
        <a href="edit_template.php?template_id=<?= $field['template_id'] ?>" class="back-link">
            ← Back to Edit Template
        </a>
    </div>
</body>
</html>