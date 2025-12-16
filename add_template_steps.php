<?php
include 'auth_check.php';
include 'db.php';

// Must receive template_id from previous page
if (!isset($_GET['template_id'])) {
    die("Template ID is required.");
}

$template_id = intval($_GET['template_id']);

// Fetch template info
$template = $conn->query("SELECT template_name FROM project_templates WHERE template_id = $template_id")->fetch_assoc();

if (!$template) {
    die("Template not found.");
}

// Fetch existing steps
$steps = $conn->query("
    SELECT step_number, step_description 
    FROM project_template_steps 
    WHERE template_id = $template_id 
    ORDER BY step_number ASC
");

// Determine next step number
$next_step_number = $steps->num_rows + 1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Template Steps</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

<h2>Add Steps for Template: <?php echo $template['template_name']; ?></h2>
<p>Logged in as: <?php echo $_SESSION['user_name']; ?></p>

<h3>Existing Steps:</h3>

<?php if ($steps->num_rows === 0): ?>
    <p>No steps added yet.</p>
<?php else: ?>
    <ol>
    <?php while ($row = $steps->fetch_assoc()): ?>
        <li><?php echo $row['step_description']; ?></li>
    <?php endwhile; ?>
    </ol>
<?php endif; ?>

<hr>

<h3>Add New Step</h3>
<form method="POST" action="process_add_step.php">
    
    <input type="hidden" name="template_id" value="<?php echo $template_id; ?>">

    <label>Step Number:</label><br>
    <input type="number" name="step_number" value="<?php echo $next_step_number; ?>" readonly><br><br>

    <label>Step Description:</label><br>
    <input type="text" name="step_description" required><br><br>

    <button type="submit">Add Step</button>
</form>

<br>
<a href="add_template.php">Back to Templates</a><br>
<a href="index.php">Home</a>

</body>
</html>
