<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_POST['project_id'])) {
    die("Project ID required.");
}

$project_id = intval($_POST['project_id']);

// Only allow delete if project is completed
$check = $conn->query("
    SELECT project_id
    FROM projects
    WHERE project_id = $project_id
      AND date_completed IS NOT NULL
");

if ($check->num_rows === 0) {
    die("Cannot delete active project.");
}

// Delete step history first
$conn->query("DELETE FROM project_steps WHERE project_id = $project_id");

// Delete project
$conn->query("DELETE FROM projects WHERE project_id = $project_id");

header("Location: view_projects.php");
exit;
