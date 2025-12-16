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
        p.date_completed,
        u.first_name AS creator_first,
        u.last_name AS creator_last
    FROM projects p
    LEFT JOIN users u ON p.created_by = u.user_id
    ORDER BY p.project_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View All Projects</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>All Projects</h2>
<p>Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>

<div class="container">

<?php if ($projects->num_rows === 0): ?>
    <p>No projects found.</p>
<?php else: ?>

<table style="width:100%; border-collapse: collapse;">
    <tr style="background:#eee; font-weight:bold;">
        <td>ID</td>
        <td>Template</td>
        <td>Customer</td>
        <td>Style</td>
        <td>Color</td>
        <td>Qty</td>
        <td>Status</td>
        <td>Actions</td>
    </tr>

<?php while ($p = $projects->fetch_assoc()): ?>

<?php
/* -------------------------------
   DETERMINE PROJECT STATUS
--------------------------------*/
$steps = $conn->query("
    SELECT 
        pts.step_number,
        pts.step_description,
        ps.step_id AS completed_step_id
    FROM project_template_steps pts
    LEFT JOIN project_steps ps
        ON ps.project_id = {$p['project_id']}
        AND ps.step_number = pts.step_number
    WHERE pts.template_id = {$p['template_id']}
    ORDER BY pts.step_number ASC
");

$current_status = "Not Started";

while ($s = $steps->fetch_assoc()) {
    if ($s['completed_step_id'] === null) {
        $current_status = $s['step_description'];
        break;
    }
}
?>

<tr>
    <td><?= $p['project_id']; ?></td>
    <td><?= htmlspecialchars($p['template_name']); ?></td>
    <td><?= htmlspecialchars($p['customer_code']); ?></td>
    <td><?= htmlspecialchars($p['style']); ?></td>
    <td><?= htmlspecialchars($p['color']); ?></td>
    <td><?= (int)$p['requested_qty']; ?></td>

    <!-- STATUS -->
    <td>
        <?php if ($current_status === "Complete"): ?>
            <span style="color:green; font-weight:bold;">Complete</span>
        <?php else: ?>
            <span style="color:#cc8800; font-weight:bold;">
                <?= htmlspecialchars($current_status); ?>
            </span>
        <?php endif; ?>
    </td>

    <!-- ACTIONS -->
    <td>
        <a href="project_detail.php?project_id=<?= $p['project_id']; ?>">
            View
        </a>
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
