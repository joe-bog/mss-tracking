










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
        p.date_completed
    FROM projects p
    ORDER BY p.project_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View All Projects</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width:100%;
            border-collapse:collapse;
        }
        th, td {
            padding:10px;
            border:1px solid #ccc;
            text-align:center;
        }
        th {
            background:#eee;
        }
        .progress-bar {
            width:100%;
            background:#eee;
            border-radius:4px;
            height:20px;
            overflow:hidden;
        }
        .progress-fill {
            height:100%;
            background:#4caf50;
            color:white;
            font-size:12px;
            line-height:20px;
            text-align:center;
            white-space:nowrap;
        }
        .delete-btn {
            background:#c00;
            color:#fff;
            border:none;
            padding:6px 10px;
            cursor:pointer;
        }




        .container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 25px;
    overflow-x: hidden; /* prevents background spill */
}

.table-wrapper {
    width: 100%;
    overflow-x: auto; /* allows scroll if needed */
}

table {
    width: 100%;
    table-layout: fixed; /* forces columns to fit */
}

th, td {
    word-wrap: break-word;
    white-space: normal;
    text-align: center;
}
    </style>
</head>
<body>

<h2>All Projects</h2>
<p>Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>

<div class="container">

<?php if ($projects->num_rows === 0): ?>
    <p>No projects found.</p>
<?php else: ?>

<table>
    <tr>
        <th>ID</th>
        <th>Template</th>
        <th>Customer</th>
        <th>Style</th>
        <th>Color</th>
        <th>Current Step</th>
        <th>Progress</th>
        <th>Status</th>
        <th>Delete</th>
    </tr>

<?php while ($p = $projects->fetch_assoc()): ?>

<?php
$requested_qty = (int)$p['requested_qty'];
$current_step = "Completed";
$current_step_number = null;
$completed_qty = 0;
$percent = 0;

if (!$p['date_completed']) {
    /* -------------------------------
       FIND CURRENT STEP
    --------------------------------*/
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

    $stmt->bind_param(
        "iii",
        $p['project_id'],
        $p['template_id'],
        $requested_qty
    );
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $current_step = $row['step_description'];
        $current_step_number = $row['step_number'];
        
        /* -------------------------------
           PROGRESS FOR CURRENT STEP ONLY
        --------------------------------*/
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
    <td><?= $p['project_id']; ?></td>
    <td><?= htmlspecialchars($p['template_name']); ?></td>
    <td><?= htmlspecialchars($p['customer_code']); ?></td>
    <td><?= htmlspecialchars($p['style']); ?></td>
    <td><?= htmlspecialchars($p['color']); ?></td>

    <!-- CURRENT STEP -->
    <td>
        <?= htmlspecialchars($current_step); ?>
    </td>

    <!-- PROGRESS -->
    <td>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $percent ?>%;">
                <?= $completed_qty ?> / <?= $requested_qty ?>
            </div>
        </div>
    </td>

    <!-- STATUS -->
    <td>
        <?php if ($p['date_completed']): ?>
            <strong style="color:green;">Completed</strong>
        <?php else: ?>
            <strong style="color:#cc8800;">In Progress</strong>
        <?php endif; ?>
    </td>

    <!-- DELETE -->
    <td>
        <?php if ($p['date_completed']): ?>
            <form method="post"
                  action="delete_project.php"
                  onsubmit="return confirm('Delete this completed project?');">
                <input type="hidden" name="project_id"
                       value="<?= $p['project_id']; ?>">
                <button class="delete-btn">Delete</button>
            </form>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
</tr>

<?php endwhile; ?>

</table>

<?php endif; ?>

</div>

<br>
<a href="index.php">Home</a>

</body>
</html>