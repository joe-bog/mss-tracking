<?php
include 'auth_check.php';
include 'db.php';

$template_id = intval($_POST['template_id']);
$number = intval($_POST['step_number']);
$desc = $conn->real_escape_string($_POST['step_description']);

$conn->query("
    INSERT INTO project_template_steps (template_id, step_number, step_description)
    VALUES ($template_id, $number, '$desc')
");

header("Location: edit_template.php?template_id=$template_id");
exit;
