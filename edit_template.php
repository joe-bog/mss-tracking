<?php
include 'auth_check.php';
include 'db.php';

/* --------------------------------
   INLINE ADD FIELD HANDLER
---------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {

    $template_id = intval($_POST['template_id']);
    $field_name  = trim($_POST['field_name']);

    if ($template_id <= 0 || $field_name === '') {
        die("Invalid field data.");
    }

    $stmt = $conn->prepare("
        INSERT INTO project_template_fields (template_id, field_name)
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $template_id, $field_name);
    $stmt->execute();

    header("Location: edit_template.php?template_id=$template_id");
    exit;
}

/* --------------------------------
   CUSTOMER → TEMPLATE SELECTION
---------------------------------*/

// STEP 1: Choose customer
if (!isset($_GET['customer_code']) && !isset($_GET['template_id'])) {

    $customers = $conn->query("
        SELECT customer_code, customer_name
        FROM customers
        ORDER BY customer_name
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
            <label>Select Customer</label>
            <select name="customer_code" required>
                <option value="">-- Choose Customer --</option>
                <?php while ($c = $customers->fetch_assoc()): ?>
                    <option value="<?= $c['customer_code'] ?>">
                        <?= htmlspecialchars($c['customer_name']) ?>
                        (<?= $c['customer_code'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="submit" value="Next">
        </form>
    </div>

    <a href="index.php">Home</a>
    </body>
    </html>
    <?php
    exit;
}

// STEP 2: Choose template for selected customer
if (!isset($_GET['template_id'])) {

    $customer_code = $conn->real_escape_string($_GET['customer_code']);

    $templates = $conn->query("
        SELECT template_id, template_name
        FROM project_templates
        WHERE customer_code = '$customer_code'
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
            <input type="hidden" name="customer_code"
                   value="<?= htmlspecialchars($customer_code) ?>">

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
                    <?= htmlspecialchars($c['customer_name']) ?>
                    (<?= $c['customer_code'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <input type="submit" value="Save Template">
    </form>
</div>

<!-- WORKFLOW STEPS -->
<h3>Workflow Steps</h3>
<div class="container">
<?php while ($s = $steps->fetch_assoc()): ?>
    <form method="post" action="update_template_step.php">
        <input type="hidden" name="step_id" value="<?= $s['step_id'] ?>">
        <input type="hidden" name="template_id" value="<?= $template_id ?>">

        <label>Step #</label>
        <input type="number" name="step_number" value="<?= $s['step_number'] ?>" required>

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
</div>

<!-- ADD STEP -->
<h4>Add New Step</h4>
<div class="container">
    <form method="post" action="add_template_step.php">
        <input type="hidden" name="template_id" value="<?= $template_id ?>">
        <input type="number" name="step_number" required>
        <input type="text" name="step_description" required>
        <input type="submit" value="Add Step">
    </form>
</div>

<!-- TEMPLATE FIELDS -->
<h3>Template Fields</h3>
<div class="container">
<?php while ($f = $fields->fetch_assoc()): ?>
    <div style="margin-bottom:10px;">
        <strong><?= htmlspecialchars($f['field_name']) ?></strong>
        —
        <a href="edit_field_options.php?field_id=<?= $f['field_id'] ?>">
            Edit Options
        </a>
    </div>
<?php endwhile; ?>
</div>

<!-- ADD FIELD (INLINE) -->
<h4>Add New Field</h4>
<div class="container">
    <form method="post">
        <input type="hidden" name="template_id" value="<?= $template_id ?>">
        <input type="hidden" name="add_field" value="1">

        <label>Field Name</label>
        <input type="text" name="field_name" required>

        <input type="submit" value="Add Field">
    </form>
</div>

<a href="index.php">Home</a>

</body>
</html>
