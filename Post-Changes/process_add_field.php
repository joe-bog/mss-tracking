<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $template_id = intval($_POST['template_id']);
    $field_name = trim($_POST['field_name']);

    $stmt = $conn->prepare("
        INSERT INTO project_template_fields (template_id, field_name)
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $template_id, $field_name);

    if ($stmt->execute()) {
        // Return to field list
        header("Location: add_template_fields.php?template_id=" . $template_id);
        exit;
    } else {
        echo "<p>Error adding field: " . $stmt->error . "</p>";
        echo "<a href='add_template_fields.php?template_id=$template_id'>Try Again</a>";
    }

} else {
    header("Location: index.php");
    exit;
}
?>
