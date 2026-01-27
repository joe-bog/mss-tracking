<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_GET['project_id'])) {
    die("Project ID required.");
}

$project_id = intval($_GET['project_id']);

// Fetch project details
$stmt = $conn->prepare("
    SELECT * FROM projects WHERE project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    die("Project not found.");
}

// Fetch detailed project steps with all information
$stmt = $conn->prepare("
    SELECT 
        ps.step_id,
        ps.project_id,
        ps.updated_by,
        ps.updated_qty,
        ps.updated_date,
        ps.template_id,
        ps.template_name,
        ps.step_number,
        ps.step_description,
        u.first_name,
        u.last_name
    FROM project_steps ps
    LEFT JOIN users u ON ps.updated_by = u.user_id
    WHERE ps.project_id = ?
    ORDER BY ps.step_number ASC, ps.updated_date ASC
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$steps = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details</title>
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
            max-width: 1200px;
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
        
        h2 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            padding: 12px 0;
            border-bottom: 1px solid #e0e6ed;
            align-items: center;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .detail-value {
            color: #555;
        }
        
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th {
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e0e6ed;
            white-space: nowrap;
        }
        
        td {
            padding: 16px 12px;
            border-bottom: 1px solid #ecf0f1;
            color: #2c3e50;
            font-size: 14px;
        }
        
        tbody tr {
            transition: background 0.2s;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .step-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #e8f4f8;
            color: #667eea;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
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
            body {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .detail-row {
                grid-template-columns: 1fr;
            }
            
            .detail-label {
                margin-bottom: 4px;
            }
            
            th, td {
                padding: 10px 6px;
                font-size: 11px;
            }
            
            .step-badge {
                font-size: 10px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Project #<?= $project['project_id']; ?></h1>
            <p class="user-info">Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        
        <div class="card">
            <h2>Project Details</h2>
            
            <div class="detail-row">
                <div class="detail-label">Project ID:</div>
                <div class="detail-value"><?= $project['project_id']; ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Customer:</div>
                <div class="detail-value"><?= htmlspecialchars($project['customer_code']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Template:</div>
                <div class="detail-value"><?= htmlspecialchars($project['template_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Style:</div>
                <div class="detail-value"><?= htmlspecialchars($project['style']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Color:</div>
                <div class="detail-value"><?= htmlspecialchars($project['color']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Quantity:</div>
                <div class="detail-value"><?= $project['requested_qty']; ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Started:</div>
                <div class="detail-value"><?= date("m/d/Y H:i", strtotime($project['created_at'])); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <?php if ($project['date_completed']): ?>
                        <span style="color: green; font-weight: 600;">✓ Completed</span>
                    <?php else: ?>
                        <span style="color: orange; font-weight: 600;">⏳ In Progress</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Workflow Progress</h2>
            
            <?php if ($steps->num_rows === 0): ?>
                <div class="empty-state">
                    <p>📦 No workflow updates yet for this project.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Step</th>
                                <th>Description</th>
                                <th>Quantity Completed</th>
                                <th>Updated By</th>
                                <th>Updated Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($step = $steps->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="step-badge">
                                            Step <?= $step['step_number']; ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($step['step_description']); ?></td>
                                    <td style="font-weight: 600;"><?= $step['updated_qty']; ?></td>
                                    <td><?= htmlspecialchars($step['first_name'] . ' ' . $step['last_name']); ?></td>
                                    <td><?= date("m/d/Y H:i", strtotime($step['updated_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="view_projects.php" class="back-link">← Back to All Projects</a>
    </div>
</body>
</html>