<?php
include 'auth_check.php';
include 'db.php';

/* -------------------------------
   FETCH PROJECTS
--------------------------------*/
$projects = $conn->query("
    SELECT 
        p.project_id,
        p.template_id,
        p.template_name,
        p.customer_code,
        p.requested_qty,
        p.style,
        p.color,
        p.created_at,
        p.date_completed
    FROM projects p
    ORDER BY p.project_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Projects</title>
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
            max-width: 1400px;
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
            padding: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
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
        
        .progress-bar {
            width: 100%;
            min-width: 120px;
            background: #ecf0f1;
            border-radius: 20px;
            height: 24px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            font-size: 11px;
            font-weight: 600;
            line-height: 24px;
            text-align: center;
            white-space: nowrap;
            transition: width 0.3s ease;
            border-radius: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-progress {
            background: #fff3cd;
            color: #856404;
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
        
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .delete-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }
        
        .delete-btn:active {
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
        
        .id-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
        }
        
        @media (max-width: 1200px) {
            th, td {
                padding: 12px 8px;
                font-size: 12px;
            }
            
            .progress-bar {
                min-width: 100px;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            th, td {
                padding: 10px 6px;
                font-size: 11px;
            }
            
            .step-badge,
            .status-badge {
                font-size: 10px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 All Projects</h1>
            <p class="user-info">Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        
        <?php if ($projects->num_rows === 0): ?>
            <div class="card">
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No Projects Found</p>
                    <p>Start by creating a new project from the dashboard</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Start Date</th>
                                <th>Template</th>
                                <th>Customer</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Current Step</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $projects->fetch_assoc()): ?>
                                <?php
                                $requested_qty = (int)$p['requested_qty'];
                                $current_step = "Completed";
                                $current_step_number = null;
                                $completed_qty = 0;
                                $percent = 0;

                                if (!$p['date_completed']) {
                                    /* FIND CURRENT STEP */
                                    $stmt = $conn->prepare("
                                        SELECT pts.step_number, pts.step_description
                                        FROM project_template_steps pts
                                        LEFT JOIN (
                                            SELECT step_number, SUM(updated_qty) AS completed_qty
                                            FROM project_steps
                                            WHERE project_id = ?
                                            GROUP BY step_number
                                        ) ps ON ps.step_number = pts.step_number
                                        WHERE pts.template_id = ?
                                          AND COALESCE(ps.completed_qty, 0) < ?
                                        ORDER BY pts.step_number ASC
                                        LIMIT 1
                                    ");

                                    $stmt->bind_param("iii", $p['project_id'], $p['template_id'], $requested_qty);
                                    $stmt->execute();

                                    $row = $stmt->get_result()->fetch_assoc();
                                    if ($row) {
                                        $current_step = $row['step_description'];
                                        $current_step_number = $row['step_number'];
                                        
                                        /* PROGRESS FOR CURRENT STEP ONLY */
                                        $stmt2 = $conn->prepare("
                                            SELECT COALESCE(SUM(updated_qty), 0) AS completed_qty
                                            FROM project_steps
                                            WHERE project_id = ? AND step_number = ?
                                        ");
                                        $stmt2->bind_param("ii", $p['project_id'], $current_step_number);
                                        $stmt2->execute();
                                        $completed_qty = (int)$stmt2->get_result()->fetch_assoc()['completed_qty'];
                                        
                                        $percent = $requested_qty > 0
                                            ? min(100, round(($completed_qty / $requested_qty) * 100))
                                            : 0;
                                    }
                                } else {
                                    // Project is completed
                                    $completed_qty = $requested_qty;
                                    $percent = 100;
                                }
                                ?>
                                
                                <tr>
                                    <td>
                                        <a href="view_single_project.php?project_id=<?= $p['project_id']; ?>" style="color: white; text-decoration: none; margin: 0;">
                                            <span class="id-badge">#<?= $p['project_id']; ?></span>
                                        </a>
                                    </td>
                                    <td><?= date("m/d/Y", strtotime($p['created_at'])); ?></td>
                                    <td><?= htmlspecialchars($p['template_name']); ?></td>
                                    <td><?= htmlspecialchars($p['customer_code']); ?></td>
                                    <td><?= htmlspecialchars($p['style']); ?></td>
                                    <td><?= htmlspecialchars($p['color']); ?></td>
                                    <td>
                                        <span class="step-badge">
                                            <?= htmlspecialchars($current_step); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $percent ?>%;">
                                                <?= $completed_qty ?> / <?= $requested_qty ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($p['date_completed']): ?>
                                            <span class="status-badge status-completed">✓ Completed</span>
                                        <?php else: ?>
                                            <span class="status-badge status-progress">⏳ In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['date_completed']): ?>
                                            <form method="post"
                                                  action="delete_project.php"
                                                  onsubmit="return confirm('Delete this completed project?');"
                                                  style="margin: 0;">
                                                <input type="hidden" name="project_id" value="<?= $p['project_id']; ?>">
                                                <button type="submit" class="delete-btn">🗑️ Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #bdc3c7;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>
</body>
</html>