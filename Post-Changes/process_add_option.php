<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $field_id = intval($_POST['field_id']);
    $option_name = trim($_POST['option_name']);

    $stmt = $conn->prepare("
        INSERT INTO project_template_field_options (field_id, option_name)
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $field_id, $option_name);

    if ($stmt->execute()) {
        // Redirect back to the options page
        header("Location: add_field_options.php?field_id=" . $field_id);
        exit;
    } else {
        echo "<p>Error adding option: " . $stmt->error . "</p>";
        echo "<a href='add_field_options.php?field_id=$field_id'>Try Again</a>";
    }

} else {
    header("Location: index.php");
    exit;
}
?>
