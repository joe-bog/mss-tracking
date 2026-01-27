<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $template_name = trim($_POST['template_name']);
    $customer_code = trim($_POST['customer_code']);

    $stmt = $conn->prepare("
        INSERT INTO project_templates (template_name, customer_code)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ss", $template_name, $customer_code);

    if ($stmt->execute()) {
        $template_id = $stmt->insert_id;

        // Redirect to edit template page (styled)
        header("Location: edit_template.php?template_id=$template_id&created=1");
        exit;
    } else {
        header("Location: add_template.php?error=1");
        exit;
    }
}
else {
    header("Location: add_template.php");
    exit;
}
