<?php
include 'auth_check.php';
include 'db.php';

// Load all customers
$customers = $conn->query("
    SELECT customer_code, customer_name
    FROM customers
    ORDER BY customer_name ASC
");

// Load all templates (we will filter client-side)
$templates = $conn->query("
    SELECT template_id, template_name, customer_code
    FROM project_templates
    ORDER BY template_name ASC
");
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
    <select name="customer_code" id="customer_select" required onchange="filterTemplates()">
        <option value="">-- Select Customer --</option>
        <?php while ($c = $customers->fetch_assoc()): ?>
            <option value="<?= $c['customer_code']; ?>">
                <?= htmlspecialchars($c['customer_name']); ?>
                (<?= $c['customer_code']; ?>)
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- TEMPLATE -->
    <label>Project Template:</label><br>
    <select name="template_id" id="template_select" required onchange="loadFields(this.value)" disabled>
        <option value="">-- Select Template --</option>

        <?php while ($t = $templates->fetch_assoc()): ?>
            <option value="<?= $t['template_id']; ?>"
                    data-customer="<?= $t['customer_code']; ?>"
                    style="display:none;">
                <?= htmlspecialchars($t['template_name']); ?>
            </option>
        <?php endwhile; ?>

    </select><br><br>

    <!-- DYNAMIC FIELDS -->
    <div id="dynamic_fields"></div>

    <!-- QUANTITY -->
    <label>Quantity Required:</label><br>
    <input type="number" name="requested_qty" min="1" required><br><br>

    <button type="submit">Start Project</button>

</form>

<script>
function filterTemplates() {
    const customer = document.getElementById('customer_select').value;
    const templateSelect = document.getElementById('template_select');
    const options = templateSelect.querySelectorAll('option');

    // Reset template selection
    templateSelect.value = '';
    templateSelect.disabled = !customer;

    // Clear dynamic fields
    document.getElementById('dynamic_fields').innerHTML = '';

    options.forEach(opt => {
        if (!opt.value) return; // Skip placeholder

        if (opt.dataset.customer === customer) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
}

function loadFields(templateId) {
    if (!templateId) {
        document.getElementById("dynamic_fields").innerHTML = "";
        return;
    }

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

