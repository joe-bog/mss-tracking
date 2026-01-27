<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_POST['project_id'])) {
    die("Project ID required.");
}

$project_id = intval($_POST['project_id']);

// Only allow delete if project is completed
$check = $conn->query("
    SELECT *
    FROM projects
    WHERE project_id = $project_id
      AND date_completed IS NOT NULL
");

if ($check->num_rows === 0) {
    die("Cannot delete active project.");
}

$project = $check->fetch_assoc();

// Archive to completed_projects table
$stmt = $conn->prepare("
    INSERT INTO completed_projects 
    (project_id, template_id, template_name, customer_code, final_chip_qty, style, color, created_by, date_completed)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iississis",
    $project['project_id'],
    $project['template_id'],
    $project['template_name'],
    $project['customer_code'],
    $project['final_chip_qty'],
    $project['style'],
    $project['color'],
    $project['created_by'],
    $project['date_completed']
);

if (!$stmt->execute()) {
    die("Error archiving project: " . $stmt->error);
}

// Delete step history first
$conn->query("DELETE FROM project_steps WHERE project_id = $project_id");

// Delete project
if (!$conn->query("DELETE FROM projects WHERE project_id = $project_id")) {
    die("Error deleting project: " . $conn->error);
}

header("Location: view_projects.php?archived=1");
exit;
?>
