<?php
include 'auth_check.php';
include 'db.php';

// Load customers for dropdown
$customers = $conn->query("SELECT customer_code, customer_name FROM customers ORDER BY customer_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Project Template</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

<h2>Add Project Template</h2>
<p>Logged in as: <?php echo $_SESSION['user_name']; ?></p>

<form method="POST" action="process_add_template.php">
    
    <label>Template Name (Ex: Fandeck):</label><br>
    <input type="text" name="template_name" required><br><br>

    <label>Select Customer:</label><br>
    <select name="customer_code" required>
        <option value="">-- Select Customer --</option>
        <?php while ($row = $customers->fetch_assoc()): ?>
            <option value="<?php echo $row['customer_code']; ?>">
                <?php echo $row['customer_name'] . " (" . $row['customer_code'] . ")"; ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>
    <button type="submit">Create Template</button>
</form>

<br>
<a href="index.php">Back to Home</a>

</body>
</html>
