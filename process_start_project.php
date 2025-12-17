<?php
include 'auth_check.php';
include 'db.php';

$customer_code = $_POST['customer_code'];
$template_id   = intval($_POST['template_id']);
$requested_qty = intval($_POST['requested_qty']);
$created_by    = $_SESSION['user_id'];

// Load template fields
$fields = $conn->query("
    SELECT field_id, field_name
    FROM project_template_fields
    WHERE template_id = $template_id
");

$style = null;
$color = null;

while ($f = $fields->fetch_assoc()) {
    $key = 'field_' . $f['field_id'];

    if (!isset($_POST[$key])) {
        die("Missing required field: " . $f['field_name']);
    }

    if (strtolower($f['field_name']) === 'style') {
        $style = $_POST[$key];
    }

    if (strtolower($f['field_name']) === 'color') {
        $color = $_POST[$key];
    }
}

// HARD SAFETY CHECK
if (!$style || !$color) {
    die("Style or Color not selected.");
}

// Insert project
$stmt = $conn->prepare("
    INSERT INTO projects
    (created_by, template_id, template_name, customer_code, requested_qty, style, color)
    SELECT ?, t.template_id, t.template_name, ?, ?, ?, ?
    FROM project_templates t
    WHERE t.template_id = ?
");

$stmt->bind_param(
    "isissi",
    $created_by,
    $customer_code,
    $requested_qty,
    $style,
    $color,
    $template_id
);

$stmt->execute();

$project_id = $stmt->insert_id;

header("Location: label.php?project_id=" . $project_id);
exit;
