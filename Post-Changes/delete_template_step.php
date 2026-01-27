<?php
include 'auth_check.php';
include 'db.php';

$step_id = intval($_POST['step_id']);
$template_id = intval($_POST['template_id']);

$conn->query("DELETE FROM project_template_steps WHERE step_id = $step_id");

header("Location: edit_template.php?template_id=$template_id");
exit;
