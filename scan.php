<?php
include 'auth_check.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scan Project</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Scan License Plate / Project Label</h2>
<p>Logged in as: <?php echo $_SESSION['user_name']; ?></p>

<form method="post" action="update_step.php">
    <input type="text"
           name="barcode"
           autofocus
           inputmode="none"
           placeholder="Scan barcode"
           required>
</form>

<br>
<a href="index.php">Home</a>

</body>
</html>
