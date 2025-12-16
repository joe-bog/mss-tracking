<?php
include 'auth_check.php';
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Customer</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

<h2>Add New Customer</h2>
<p>Logged in as: <?php echo $_SESSION['user_name']; ?></p>

<form method="POST" action="process_add_customer.php">
    <label>Customer Name:</label><br>
    <input type="text" name="customer_name" required><br><br>

    <label>Customer Code (short code ex: EVL):</label><br>
    <input type="text" name="customer_code" maxlength="10" required><br><br>

    <button type="submit">Add Customer</button>
</form>

<br><br>
<a href="index.php">Back to Home</a>

</body>
</html>
