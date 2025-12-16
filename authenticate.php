<?php
session_start();
include 'db.php';

if (!isset($_POST['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_POST['user_id']);

// Get user info
$stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Store user info in session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];

    header("Location: index.php"); // Change this later
    exit;

} else {
    echo "Invalid user.";
}
?>
