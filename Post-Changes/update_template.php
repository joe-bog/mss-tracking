<?php
include 'auth_check.php';
include 'db.php';

$id = intval($_POST['template_id']);
$name = $conn->real_escape_string($_POST['template_name']);
$customer = $conn->real_escape_string($_POST['customer_code']);

$conn->query("
    UPDATE project_templates
    SET template_name = '$name',
        customer_code = '$customer'
    WHERE template_id = $id
");

header("Location: edit_template.php?template_id=$id&updated=1");
exit;
