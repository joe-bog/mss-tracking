<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_name = trim($_POST['customer_name']);
    $customer_code = strtoupper(trim($_POST['customer_code'])); // Always uppercase

    // Prepare SQL
    $stmt = $conn->prepare("
        INSERT INTO customers (customer_name, customer_code)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ss", $customer_name, $customer_code);

    if ($stmt->execute()) {
        echo "<p>Customer added successfully!</p>";
        echo "<a href='add_customer.php'>Add Another</a><br>";
        echo "<a href='index.php'>Back to Home</a>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
        echo "<a href='add_customer.php'>Try Again</a>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: add_customer.php");
    exit;
}
?>
