<?php
include 'auth_check.php';
include 'db.php';

/* -------------------------------
   LOAD FIELD
--------------------------------*/
if (!isset($_GET['field_id'])) {
    die("Field ID is required.");
}

$field_id = intval($_GET['field_id']);

// Load field info
$field = $conn->query("
    SELECT f.field_name, f.template_id, t.template_name
    FROM project_template_fields f
    JOIN project_templates t ON f.template_id = t.template_id
    WHERE f.field_id = $field_id
")->fetch_assoc();

if (!$field) {
    die("Field not found.");
}

/* -------------------------------
   ADD OPTION (INLINE)
--------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {

    $option_name = trim($_POST['option_name']);

    if ($option_name === '') {
        die("Option name required.");
    }

    $stmt = $conn->prepare("
        INSERT INTO project_template_field_options (field_id, option_name)
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $field_id, $option_name);
    $stmt->execute();

    header("Location: edit_field_options.php?field_id=$field_id");
    exit;
}

/* -------------------------------
   DELETE OPTION
--------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_option'])) {

    $option_id = intval($_POST['option_id']);

    $stmt = $conn->prepare("
        DELETE FROM project_template_field_options
        WHERE option_id = ?
    ");
    $stmt->bind_param("i", $option_id);
    $stmt->execute();

    header("Location: edit_field_options.php?field_id=$field_id");
    exit;
}

// Load existing options
$options = $conn->query("
    SELECT option_id, option_name
    FROM project_template_field_options
    WHERE field_id = $field_id
    ORDER BY option_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Field Options</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>
    Edit Options for Field:
    <?= htmlspecialchars($field['field_name']) ?>
</h2>

<p>
    <strong>Template:</strong>
    <?= htmlspecialchars($field['template_name']) ?>
</p>

<!-- EXISTING OPTIONS -->
<div class="container">
<?php if ($options->num_rows === 0): ?>
    <p>No options added yet.</p>
<?php else: ?>
    <?php while ($o = $options->fetch_assoc()): ?>
        <form method="post" style="display:flex; gap:10px; align-items:center;">
            <span><?= htmlspecialchars($o['option_name']) ?></span>

            <input type="hidden" name="option_id" value="<?= $o['option_id'] ?>">
            <input type="hidden" name="delete_option" value="1">

            <button type="submit"
                    style="background:#c00;"
                    onclick="return confirm('Delete this option?');">
                Delete
            </button>
        </form>
    <?php endwhile; ?>
<?php endif; ?>
</div>

<!-- ADD OPTION -->
<h4>Add New Option</h4>
<div class="container">
    <form method="post">
        <input type="hidden" name="add_option" value="1">

        <label>Option Name</label>
        <input type="text"
               name="option_name"
               placeholder="e.g. 7&quot;, Blue, Matte"
               required>

        <input type="submit" value="Add Option">
    </form>
</div>

<br>
<a href="edit_template.php?template_id=<?= $field['template_id'] ?>">
    ← Back to Edit Template
</a>

</body>
</html>
