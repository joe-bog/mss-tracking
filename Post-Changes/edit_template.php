<?php
include 'auth_check.php';
include 'db.php';

/* --------------------------------
   INLINE ADD FIELD HANDLER
---------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {

    $template_id = intval($_POST['template_id']);
    $field_name  = trim($_POST['field_name']);

    if ($template_id <= 0 || $field_name === '') {
        die("Invalid field data.");
    }

    $stmt = $conn->prepare("
        INSERT INTO project_template_fields (template_id, field_name)
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $template_id, $field_name);
    $stmt->execute();

    header("Location: edit_template.php?template_id=$template_id");
    exit;
}

/* --------------------------------
   CUSTOMER → TEMPLATE SELECTION
---------------------------------*/

// STEP 1: Choose customer
if (!isset($_GET['customer_code']) && !isset($_GET['template_id'])) {

    $customers = $conn->query("
        SELECT customer_code, customer_name
        FROM customers
        ORDER BY customer_name
    ");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Template - Select Customer</title>
        <link rel="stylesheet" href="styles.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: #f5f7fa;
                padding: 20px;
                min-height: 100vh;
            }
            .container { max-width: 600px; margin: 0 auto; }
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
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .card {
                background: white;
                border-radius: 12px;
                padding: 30px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                margin-bottom: 20px;
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
            .form-group select {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e0e6ed;
                border-radius: 8px;
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
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>✏️ Edit Template - Step 1</h1>
            </div>
            
            <div class="card">
                <form method="get">
                    <div class="form-group">
                        <label>Select Customer</label>
                        <select name="customer_code" required>
                            <option value="">-- Choose Customer --</option>
                            <?php while ($c = $customers->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($c['customer_code']) ?>">
                                    <?= htmlspecialchars($c['customer_name']) ?>
                                    (<?= htmlspecialchars($c['customer_code']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Next →</button>
                </form>
            </div>
            
            <a href="index.php" class="back-link">← Home</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// STEP 2: Choose template for selected customer
if (!isset($_GET['template_id'])) {

    $customer_code = $conn->real_escape_string($_GET['customer_code']);

    $templates = $conn->query("
        SELECT template_id, template_name
        FROM project_templates
        WHERE customer_code = '$customer_code'
        ORDER BY template_name
    ");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Template - Select Template</title>
        <link rel="stylesheet" href="styles.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: #f5f7fa;
                padding: 20px;
                min-height: 100vh;
            }
            .container { max-width: 600px; margin: 0 auto; }
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
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .card {
                background: white;
                border-radius: 12px;
                padding: 30px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                margin-bottom: 20px;
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
            .form-group select {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e0e6ed;
                border-radius: 8px;
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
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>✏️ Edit Template - Step 2</h1>
            </div>
            
            <div class="card">
                <form method="get">
                    <input type="hidden" name="customer_code" value="<?= htmlspecialchars($customer_code) ?>">
                    
                    <div class="form-group">
                        <label>Select Template</label>
                        <select name="template_id" required>
                            <option value="">-- Choose Template --</option>
                            <?php while ($t = $templates->fetch_assoc()): ?>
                                <option value="<?= $t['template_id'] ?>">
                                    <?= htmlspecialchars($t['template_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Edit Template →</button>
                </form>
            </div>
            
            <a href="index.php" class="back-link">← Home</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* -------------------------------
   LOAD TEMPLATE DATA
--------------------------------*/
$template_id = intval($_GET['template_id']);

$template = $conn->query("
    SELECT * FROM project_templates
    WHERE template_id = $template_id
")->fetch_assoc();

$customers = $conn->query("
    SELECT customer_code, customer_name
    FROM customers
    ORDER BY customer_name
");

$steps = $conn->query("
    SELECT * FROM project_template_steps
    WHERE template_id = $template_id
    ORDER BY step_number
");

$fields = $conn->query("
    SELECT * FROM project_template_fields
    WHERE template_id = $template_id
    ORDER BY field_name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template</title>
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
        
        .container-main {
            max-width: 1000px;
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
            display: flex;
            align-items: center;
            gap: 12px;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
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
            padding: 12px 16px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
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
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .step-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .step-item-header {
            display: flex;
            gap: 15px;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .step-item-header .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .step-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .field-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .field-name {
            color: #2c3e50;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .field-name::before {
            content: '📝';
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
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
        
        @media (max-width: 768px) {
            .step-item-header {
                flex-direction: column;
            }
            
            .step-actions {
                flex-direction: column;
            }
            
            .field-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container-main">
        <div class="header">
            <h1>✏️ Edit Template: <?= htmlspecialchars($template['template_name']) ?></h1>
        </div>
        
        <!-- TEMPLATE META -->
        <div class="card">
            <h2>📋 Template Information</h2>
            <form method="post" action="update_template.php">
                <input type="hidden" name="template_id" value="<?= $template_id ?>">
                
                <div class="form-group">
                    <label>Template Name</label>
                    <input type="text" name="template_name"
                           value="<?= htmlspecialchars($template['template_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_code" required>
                        <?php while ($c = $customers->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($c['customer_code']) ?>"
                                <?= $c['customer_code'] === $template['customer_code'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['customer_name']) ?>
                                (<?= htmlspecialchars($c['customer_code']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">✓ Save Template</button>
            </form>
        </div>
        
        <!-- WORKFLOW STEPS -->
        <div class="card">
            <h2>🔢 Workflow Steps</h2>
            
            <?php while ($s = $steps->fetch_assoc()): ?>
                <div class="step-item">
                    <form method="post" action="update_template_step.php">
                        <input type="hidden" name="step_id" value="<?= $s['step_id'] ?>">
                        <input type="hidden" name="template_id" value="<?= $template_id ?>">
                        
                        <div class="step-item-header">
                            <div class="form-group" style="max-width: 120px;">
                                <label>Step #</label>
                                <input type="number" name="step_number" value="<?= $s['step_number'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="step_description"
                                       value="<?= htmlspecialchars($s['step_description']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="step-actions">
                            <button type="submit" class="btn-primary">Save Step</button>
                        </div>
                    </form>
                    
                    <form method="post" action="delete_template_step.php"
                          onsubmit="return confirm('Delete this step?');" style="display: inline;">
                        <input type="hidden" name="step_id" value="<?= $s['step_id'] ?>">
                        <input type="hidden" name="template_id" value="<?= $template_id ?>">
                        <button type="submit" class="btn-delete">🗑️ Delete Step</button>
                    </form>
                </div>
            <?php endwhile; ?>
            
            <!-- ADD STEP -->
            <h3 style="color: #2c3e50; font-size: 18px; margin: 25px 0 15px 0;">➕ Add New Step</h3>
            <form method="post" action="add_template_step.php">
                <input type="hidden" name="template_id" value="<?= $template_id ?>">
                
                <div style="display: flex; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="max-width: 120px; margin-bottom: 0;">
                        <label>Step #</label>
                        <input type="number" name="step_number" required>
                    </div>
                    
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Description</label>
                        <input type="text" name="step_description" placeholder="e.g., Cut Material" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">Add Step</button>
                </div>
            </form>
        </div>
        
        <!-- TEMPLATE FIELDS -->
        <div class="card">
            <h2>📝 Template Fields</h2>
            
            <?php if ($fields->num_rows === 0): ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">No fields added yet.</p>
            <?php else: ?>
                <?php while ($f = $fields->fetch_assoc()): ?>
                    <div class="field-item">
                        <span class="field-name">
                            <?= htmlspecialchars($f['field_name']) ?>
                        </span>
                        <a href="edit_field_options.php?field_id=<?= $f['field_id'] ?>" class="btn-secondary">
                            ⚙️ Edit Options
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
            
            <!-- ADD FIELD (INLINE) -->
            <h3 style="color: #2c3e50; font-size: 18px; margin: 25px 0 15px 0;">➕ Add New Field</h3>
            <form method="post">
                <input type="hidden" name="template_id" value="<?= $template_id ?>">
                <input type="hidden" name="add_field" value="1">
                
                <div style="display: flex; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Field Name</label>
                        <input type="text" name="field_name" placeholder="e.g., Style, Color" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">Add Field</button>
                </div>
            </form>
        </div>
        
        <a href="index.php" class="back-link">← Home</a>
    </div>
</body>
</html>