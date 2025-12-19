<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_GET['project_id'])) {
    die("Project ID required.");
}

$project_id = (int)$_GET['project_id'];

$project = $conn->query("
    SELECT *
    FROM projects
    WHERE project_id = $project_id
")->fetch_assoc();

if (!$project) {
    die("Project not found.");
}

// ✅ SINGLE SOURCE OF TRUTH
$barcode_text = "PROJECT-" . $project_id;

$display_line = strtoupper(
    $project['customer_code'] . '-' .
    $project['template_name'] . '-' .
    str_replace('"', '', $project['style']) . '-' .
    $project['color'] . '-' .
    $project['requested_qty']
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Label</title>
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
            max-width: 700px;
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
        
        .label-container {
            text-align: center;
            margin: 30px 0;
        }
        
        /* THE LABEL CONTAINER */
        .label {
            border: 3px solid #2c3e50;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            width: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* PROJECT ID (TOP TEXT) */
        .label .project-number {
            margin: 0 0 15px 0;
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* BARCODE IMAGE */
        .label img {
            display: block;
            margin: 0 auto 15px auto;
            max-width: 100%;
            height: auto;
        }
        
        /* FOOTER DETAILS */
        .label .details {
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
            width: 100%;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:active {
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
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            h1, .header, .button-group, .back-link {
                display: none !important;
            }
            .card {
                box-shadow: none;
                padding: 0;
                background: transparent;
            }
            .label {
                box-shadow: none;
                border: 2px solid #000;
                width: 100%;
                max-width: 400px;
            }
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
            
            .label {
                width: 100%;
                max-width: 350px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🏷️ Project Label</h1>
        <p class="user-info">Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>
    </div>
    
    <div class="card">
        <div class="label-container">
            <div class="label">
                <p class="project-number"><?= htmlspecialchars($project_id) ?></p>
                <img src="barcode.php?code=<?= urlencode($barcode_text) ?>" alt="Barcode">
                <p class="details"><?= htmlspecialchars($display_line) ?></p>
            </div>
        </div>
        
        <div class="button-group">
            <button class="btn-primary" onclick="window.print()">🖨️ Print Label</button>
            <a href="start_project.php" class="back-link">← Start Another Project</a>
            <a href="index.php" class="back-link">← Home</a>
        </div>
    </div>
</div>

</body>
</html>
