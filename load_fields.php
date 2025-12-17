<?php
include 'db.php';

if (!isset($_GET['template_id']) || !is_numeric($_GET['template_id'])) {
    exit;
}

$template_id = intval($_GET['template_id']);

// Get fields for this template
$fields = $conn->prepare("
    SELECT field_id, field_name
    FROM project_template_fields
    WHERE template_id = ?
    ORDER BY field_id
");
$fields->bind_param("i", $template_id);
$fields->execute();
$result = $fields->get_result();

if ($result->num_rows === 0) {
    echo "<p><em>No custom fields for this template.</em></p>";
    exit;
}

while ($field = $result->fetch_assoc()) {

    echo "<label>{$field['field_name']}:</label><br>";

    echo "<select name='field_{$field['field_id']}' required>";
    echo "<option value=''>-- Select {$field['field_name']} --</option>";

    // Get options for this field
    $options = $conn->prepare("
        SELECT option_name
        FROM project_template_field_options
        WHERE field_id = ?
        ORDER BY option_name
    ");
    $options->bind_param("i", $field['field_id']);
    $options->execute();
    $optResult = $options->get_result();

    while ($opt = $optResult->fetch_assoc()) {
        $val = htmlspecialchars($opt['option_name']);
        echo "<option value='{$val}'>{$val}</option>";
    }

    echo "</select><br><br>";
}
