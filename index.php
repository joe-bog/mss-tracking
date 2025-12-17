
<?php
include 'auth_check.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>MSS Tracking Dashboard</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

<h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>
<p>This is your MSS Production Tracking dashboard.</p>

<hr>

<div class="section">
    <h3> Admin: Setup & Configuration</h3>

    <a href="add_customer.php"> Add Customer</a>
    <a href="add_template.php"> Add Project Template</a>

    <!-- When editing an existing template -->
    <a href="edit_template.php"> Edit Template (Steps, Fields, Options)</a>
</div>

<hr>

<div class="section">
    <h3>Production: Start & Track Projects</h3>

    <a href="start_project.php"> Start New Project (Generate Label)</a>
    <a href="scan.php"> Scan Project (Update Steps)</a>
</div>

<hr>

<div class="section">
    <h3>Reports & Status</h3>

    <a href="view_projects.php"> View All Projects</a>
    <a href="view_labels.php"> View Labels</a>
</div>

<hr>

<a class="logout" href="logout.php"> Logout</a>

</body>
</html>
