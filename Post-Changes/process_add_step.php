<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $template_id = intval($_POST['template_id']);
    $step_number = intval($_POST['step_number']);
    $step_description = trim($_POST['step_description']);

    $stmt = $conn->prepare("
        INSERT INTO project_template_steps (template_id, step_number, step_description)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iis", $template_id, $step_number, $step_description);

    if ($stmt->execute()) {
        // Redirect back to step form with template_id
        header("Location: add_template_steps.php?template_id=" . $template_id);
        exit;
    } else {
        echo "<p>Error adding step: " . $stmt->error . "</p>";
        echo "<a href='add_template_steps.php?template_id=$template_id'>Try Again</a>";
    }

} else {
    header("Location: index.php");
    exit;
}
?>
