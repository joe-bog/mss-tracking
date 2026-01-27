<?php
include 'auth_check.php';
include 'db.php';

$customer_code = $_POST['customer_code'];
$template_id   = intval($_POST['template_id']);
$plank_qty = intval($_POST['plank_qty']);
$created_by    = $_SESSION['user_id'];

// Load template fields
$fields = $conn->query("
    SELECT field_id, field_name
    FROM project_template_fields
    WHERE template_id = $template_id
");

$style = ' ';
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

// Fetch template name
$template_name_stmt = $conn->prepare("SELECT template_name FROM project_templates WHERE template_id = ?");
$template_name_stmt->bind_param("i", $template_id);
$template_name_stmt->execute();
$template_name_result = $template_name_stmt->get_result();
$template_row = $template_name_result->fetch_assoc();
$template_name = $template_row['template_name'] ?? '';

$final_chip_qty = $plank_qty;

if (trim($template_name) === 'PrintWorks Fandeck') {
    $final_chip_qty *= 56;
}
else if(trim($template_name) === 'Shopworks Fandeck') {
    $final_chip_qty *= 66;
}

// Insert project
$stmt = $conn->prepare("
    INSERT INTO projects
    (created_by, template_id, template_name, customer_code, final_chip_qty, style, color, plank_qty)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iississi",
    $created_by,
    $template_id,
    $template_name,
    $customer_code,
    $final_chip_qty,
    $style,
    $color,
    $plank_qty
);

$stmt->execute();

$project_id = $stmt->insert_id;

header("Location: label.php?project_id=" . $project_id);
exit;
