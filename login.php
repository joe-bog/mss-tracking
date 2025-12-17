









<?php
session_start();
include 'db.php';

// 1. HANDLE NEW USER SIGNUP (Logic runs if the "Add User" form is submitted)
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    // Basic sanitization
    $first = $conn->real_escape_string($_POST['new_first_name']);
    $last = $conn->real_escape_string($_POST['new_last_name']);

    if(!empty($first) && !empty($last)){
        $sql = "INSERT INTO users (first_name, last_name) VALUES ('$first', '$last')";
        
        if($conn->query($sql) === TRUE){
            $message = "<p class='success'>User '$first $last' added! Please log in below.</p>";
        } else {
            $message = "<p class='error'>Error: " . $conn->error . "</p>";
        }
    }
}

// 2. FETCH USERS (Runs after the insert, so the new user is included)
$result = $conn->query("SELECT user_id, first_name, last_name FROM users ORDER BY first_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - MSS Tracking</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Simple style to hide the signup form initially */
        #signup-form {
            display: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .toggle-btn {
            background: #666; /* Grey button to differentiate from Login */
            margin-top: 10px;
        }
        .toggle-btn:hover {
            background: #555;
        }
    </style>
    <script>
        function toggleSignup() {
            var form = document.getElementById("signup-form");
            var btn = document.getElementById("toggle-btn");
            
            if (form.style.display === "none" || form.style.display === "") {
                form.style.display = "block";
                btn.textContent = "Cancel Signup";
            } else {
                form.style.display = "none";
                btn.textContent = "Create New User";
            }
        }
    </script>
</head>
<body>

    <?php echo $message; ?>

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

    <button id="toggle-btn" class="toggle-btn" onclick="toggleSignup()">Create New User</button>

    <div id="signup-form">
        <h3>Add New User</h3>
        <form method="POST" action="">
            <label>First Name:</label>
            <input type="text" name="new_first_name" required placeholder="John">
            
            <label>Last Name:</label>
            <input type="text" name="new_last_name" required placeholder="Doe">
            
            <button type="submit" name="add_user">Save & Add to List</button>
        </form>
    </div>

</body>
</html>