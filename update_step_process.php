<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: scan.php");
    exit;
}

$project_id = intval($_POST['project_id']);
$step_id = intval($_POST['step_id']);
$completed_qty = intval($_POST['completed_qty']);

// Fetch project to validate quantities
$project = $conn->query("
    SELECT final_chip_qty 
    FROM projects 
    WHERE project_id = $project_id
")->fetch_assoc();

if (!$project) {
    die("Project not found.");
}

$final_chip_qty = intval($project['final_chip_qty']);

// Validate quantity
if ($completed_qty < 0 || $completed_qty > $final_chip_qty) {
    die("Invalid quantity. Must be between 0 and $final_chip_qty.");
}

// Insert or update
$stmt = $conn->prepare("
    INSERT INTO project_steps (project_id, step_id, completed_qty)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE completed_qty = VALUES(completed_qty)
");

$stmt->bind_param("iii", $project_id, $step_id, $completed_qty);

if ($stmt->execute()) {

    // If the step is completed, redirect back to scan page for next LP
    header("Location: scan.php?success=1");
    exit;

} else {
    die("Database error: " . $stmt->error);
}
?>
