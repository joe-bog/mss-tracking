<?php
include 'auth_check.php';
include 'db.php';

/* -------------------------------
   TEMPLATE SELECTION
--------------------------------*/
if (!isset($_GET['template_id'])) {

    $templates = $conn->query("
        SELECT template_id, template_name 
        FROM project_templates 
        ORDER BY template_name
    ");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Edit Template</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>

    <h2>Edit Project Template</h2>

    <div class="container">
        <form method="get">
            <label>Select Template</label>
            <select name="template_id" required>
                <option value="">-- Choose Template --</option>
                <?php while ($t = $templates->fetch_assoc()): ?>
                    <option value="<?= $t['template_id'] ?>">
                        <?= htmlspecialchars($t['template_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="submit" value="Edit Template">
        </form>
    </div>

    <a href="index.php">Home</a>
    </body>
    </html>
    <?php
    exit;
}

/* -------------------------------
   LOAD TEMPLATE DATA
--------------------------------*/
$template_id = intval($_GET['template_id']);

$template = $conn->query("
    SELECT * FROM project_templates 
    WHERE template_id = $template_id
")->fetch_assoc();

$customers = $conn->query("
    SELECT customer_code, customer_name 
    FROM customers 
    ORDER BY customer_name
");

$steps = $conn->query("
    SELECT * FROM project_template_steps
    WHERE template_id = $template_id
    ORDER BY step_number
");

$fields = $conn->query("
    SELECT * FROM project_template_fields
    WHERE template_id = $template_id
    ORDER BY field_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Template</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Edit Template: <?= htmlspecialchars($template['template_name']) ?></h2>

<?php if (isset($_GET['created'])): ?>
    <div class="success">
        Template created successfully. You can now edit steps and fields.
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <div class="success">
        Template updated successfully.
    </div>
<?php endif; ?>

<!-- TEMPLATE META -->
<div class="container">
    <form method="post" action="update_template.php">
        <input type="hidden" name="template_id" value="<?= $template_id ?>">

        <label>Template Name</label>
        <input type="text" name="template_name"
               value="<?= htmlspecialchars($template['template_name']) ?>" required>

        <label>Customer</label>
        <select name="customer_code" required>
            <?php while ($c = $customers->fetch_assoc()): ?>
                <option value="<?= $c['customer_code'] ?>"
                    <?= $c['customer_code'] === $template['customer_code'] ? 'selected' : '' ?>>
                    <?= $c['customer_name'] ?> (<?= $c['customer_code'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <input type="submit" value="Save Template">
    </form>
</div>

<!-- STEPS -->
<h3>Workflow Steps</h3>

<div class="container">

<?php if ($steps->num_rows === 0): ?>
    <p>No steps have been added yet.</p>
<?php else: ?>
    <?php while ($s = $steps->fetch_assoc()): ?>
        <form method="post" action="update_template_step.php">
            <input type="hidden" name="step_id" value="<?= $s['step_id'] ?>">
            <input type="hidden" name="template_id" value="<?= $template_id ?>">

            <label>Step #</label>
            <input type="number" name="step_number"
                   value="<?= $s['step_number'] ?>" required>

            <label>Description</label>
            <input type="text" name="step_description"
                   value="<?= htmlspecialchars($s['step_description']) ?>" required>

            <button type="submit">Save Step</button>
        </form>

        <form method="post" action="delete_template_step.php"
              onsubmit="return confirm('Delete this step?');">
            <input type="hidden" name="step_id" value="<?= $s['step_id'] ?>">
            <input type="hidden" name="template_id" value="<?= $template_id ?>">
            <button style="background:#c00;">Delete Step</button>
        </form>
        <hr>
    <?php endwhile; ?>
<?php endif; ?>

</div>

<!-- ADD STEP -->
<h4>Add New Step</h4>
<div class="container">
    <form method="post" action="add_template_step.php">
        <input type="hidden" name="template_id" value="<?= $template_id ?>">

        <label>Step Number</label>
        <input type="number" name="step_number"
               placeholder="e.g. 1" required>

        <label>Step Description</label>
        <input type="text" name="step_description"
               placeholder="e.g. Cutting, Routing, Sanding"
               required>

        <input type="submit" value="Add Step">
    </form>
</div>

<!-- FIELDS -->
<h3>Template Fields</h3>
<div class="container">
<?php if ($fields->num_rows === 0): ?>
    <p>No fields defined yet.</p>
<?php else: ?>
    <?php while ($f = $fields->fetch_assoc()): ?>
        <p>
            <strong><?= htmlspecialchars($f['field_name']) ?></strong>
            —
            <a href="edit_field_options.php?field_id=<?= $f['field_id'] ?>">
                Edit Options
            </a>
        </p>
    <?php endwhile; ?>
<?php endif; ?>
</div>

<a href="index.php">Home</a>

</body>
</html>
