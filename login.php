<?php
session_start();
include 'db.php';

// Fetch users for dropdown
$result = $conn->query("SELECT user_id, first_name, last_name FROM users ORDER BY first_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - MSS Tracking</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>MSS Tracking Login</h2>

    <form method="POST" action="authenticate.php">
        <label>Select User:</label>
        <select name="user_id" required>
            <option value="">-- Select Your Name --</option>
            <?php while($row = $result->fetch_assoc()): ?>
                <option value="<?php echo $row['user_id']; ?>">
                    <?php echo $row['first_name'] . " " . $row['last_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
