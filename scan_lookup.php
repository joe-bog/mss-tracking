<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: scan.php");
    exit;
}

$barcode = trim($_POST['barcode']);

if ($barcode === "") {
    die("Invalid barcode.");
}

// ------------------------------
// Parse barcode format:
// PROJ-{id}-{cust}-{template}-{style}-{color}
// ------------------------------
$parts = explode("-", $barcode);

if (count($parts) < 3 || $parts[0] !== "PROJ") {
    die("Unrecognized barcode format.");
}

$project_id = intval($parts[1]);

// Fetch project
$project = $conn->query("
    SELECT * FROM projects WHERE project_id = $project_id
")->fetch_assoc();

if (!$project) {
    die("Project not found.");
}

// Determine current step
$current_step = $conn->query("
    SELECT pts.step_id, pts.step_number, pts.step_description,
           ps.completed_qty
    FROM project_template_steps pts
    LEFT JOIN project_steps ps 
        ON (ps.project_id = $project_id AND ps.step_id = pts.step_id)
    WHERE pts.template_id = {$project['template_id']}
    ORDER BY pts.step_number ASC
")->fetch_all(MYSQLI_ASSOC);

$next_step = null;

foreach ($current_step as $step) {
    if ($step['completed_qty'] == 0 || $step['completed_qty'] === null) {
        $next_step = $step;
        break;
    }
}

// If all steps done:
if (!$next_step) {
    die("This project has already completed all steps.");
}

// Redirect to update page
header("Location: update_step.php?project_id={$project_id}&step_id={$next_step['step_id']}");
exit;
?>
