<?php
include 'auth_check.php';
include 'db.php';

// Must receive field_id
if (!isset($_GET['field_id'])) {
    die("Field ID is required.");
}

$field_id = intval($_GET['field_id']);

// Fetch field info
$field = $conn->query("
    SELECT field_name, template_id 
    FROM project_template_fields 
    WHERE field_id = $field_id
")->fetch_assoc();

if (!$field) {
    die("Field not found.");
}

$template_id = $field['template_id'];

// Fetch template info
$template = $conn->query("
    SELECT template_name 
    FROM project_templates 
    WHERE template_id = $template_id
")->fetch_assoc();

// Load existing options
$options = $conn->query("
    SELECT option_id, option_name
    FROM project_template_field_options
    WHERE field_id = $field_id
    ORDER BY option_id ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Field Options</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

<h2>Add Options for Field: <?php echo $field['field_name']; ?></h2>
<h3>Template: <?php echo $template['template_name']; ?></h3>
<p>Logged in as: <?php echo $_SESSION['user_name']; ?></p>

<h3>Existing Options:</h3>

<?php if ($options->num_rows === 0): ?>
    <p>No options added yet.</p>
<?php else: ?>
    <ul>
        <?php while ($row = $options->fetch_assoc()): ?>
            <li><?php echo $row['option_name']; ?></li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>

<hr>

<h3>Add New Option</h3>

<form method="POST" action="process_add_option.php">

    <input type="hidden" name="field_id" value="<?php echo $field_id; ?>">

    <label>Option Name (ex: 7&quot;, Chestnut):</label><br>
    <input type="text" name="option_name" required><br><br>

    <button type="submit">Add Option</button>
</form>

<br>
<a href="add_template_fields.php?template_id=<?php echo $template_id; ?>">Back to Template Fields</a><br>
<a href="index.php">Home</a>

</body>
</html>
