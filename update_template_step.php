<?php
include 'auth_check.php';
include 'db.php';

$step_id = intval($_POST['step_id']);
$template_id = intval($_POST['template_id']);
$num = intval($_POST['step_number']);
$desc = $conn->real_escape_string($_POST['step_description']);

$conn->query("
    UPDATE project_template_steps
    SET step_number = $num,
        step_description = '$desc'
    WHERE step_id = $step_id
");

header("Location: edit_template.php?template_id=$template_id");
exit;
