<?php
include 'auth_check.php';
include 'db.php';

if (isset($_POST['template_id'])) {
    $template_id = intval($_POST['template_id']);
} elseif (isset($_GET['template_id'])) {
    $template_id = intval($_GET['template_id']);
} else {
    die("Template ID is required.");
}

// Fetch template info
$template = $conn->query("
    SELECT template_name 
    FROM project_templates 
    WHERE template_id = $template_id
")->fetch_assoc();

if (!$template) {
    die("Template not found.");
}

// Fetch existing fields for this template
$fields = $conn->query("
    SELECT field_id, field_name
    FROM project_template_fields
    WHERE template_id = $template_id
    ORDER BY field_id ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Template Fields</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

<h2>Add Fields for Template: <?php echo $template['template_name']; ?></h2>
<p>Logged in as: <?php echo $_SESSION['user_name']; ?></p>

<h3>Existing Fields:</h3>

<?php if ($fields->num_rows === 0): ?>
    <p>No fields added yet.</p>
<?php else: ?>
    <ul>
        <?php while ($row = $fields->fetch_assoc()): ?>
            <li>
                <?php echo $row['field_name']; ?>
                — <a href="add_field_options.php?field_id=<?php echo $row['field_id']; ?>">Add Options</a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>

<hr>

<h3>Add New Field</h3>
<form method="POST" action="process_add_field.php">
    
    <input type="hidden" name="template_id" value="<?php echo $template_id; ?>">

    <label>Field Name (ex: Style, Color):</label><br>
    <input type="text" name="field_name" required><br><br>

    <button type="submit">Add Field</button>
</form>

<br>
<a href="add_template_steps.php?template_id=<?php echo $template_id; ?>">Back to Steps</a><br>
<a href="index.php">Home</a>

</body>
</html>
