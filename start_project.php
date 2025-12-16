<?php
include 'auth_check.php';
include 'db.php';

// Load all customers
$customers = $conn->query("SELECT customer_code, customer_name FROM customers ORDER BY customer_name ASC");

// Load all templates
$templates = $conn->query("SELECT template_id, template_name, customer_code FROM project_templates ORDER BY template_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Start Project</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Start New Project</h2>
<p>Logged in as: <?php echo $_SESSION['user_name']; ?></p>

<form method="POST" action="process_start_project.php">

    <!-- CUSTOMER -->
    <label>Customer:</label><br>
    <select name="customer_code" required>
        <option value="">-- Select Customer --</option>
        <?php while ($c = $customers->fetch_assoc()): ?>
            <option value="<?php echo $c['customer_code']; ?>">
                <?php echo $c['customer_name'] . " (" . $c['customer_code'] . ")"; ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- TEMPLATE -->
    <label>Project Template:</label><br>
    <select name="template_id" required onchange="loadFields(this.value)">
        <option value="">-- Select Template --</option>
        <?php while ($t = $templates->fetch_assoc()): ?>
            <option value="<?php echo $t['template_id']; ?>">
                <?php echo $t['template_name']; ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- Dynamic fields load via AJAX -->
    <div id="dynamic_fields"></div>

    <!-- QUANTITY -->
    <label>Quantity Required:</label><br>
    <input type="number" name="requested_qty" min="1" required><br><br>

    <button type="submit">Start Project</button>

</form>

<script>
function loadFields(templateId) {
    if (!templateId) {
        document.getElementById("dynamic_fields").innerHTML = "";
        return;
    }

    // Load fields via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "load_fields.php?template_id=" + templateId, true);
    xhr.onload = function() {
        document.getElementById("dynamic_fields").innerHTML = this.responseText;
    };
    xhr.send();
}
</script>

<br>
<a href="index.php">Home</a>

</body>
</html>
