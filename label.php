<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_GET['project_id'])) {
    die("Project ID required.");
}

$project_id = intval($_GET['project_id']);

$project = $conn->query("
    SELECT * FROM projects WHERE project_id = $project_id
")->fetch_assoc();

$raw_barcode = 
    "PROJ-" . $project['project_id'] . "-" . 
    $project['customer_code'] . "-" . 
    $project['template_name'] . "-" .
    $project['style'] . "-" .
    $project['color'];

// Remove unsupported characters
$barcode_text = preg_replace("/[^A-Za-z0-9\-]/", "", $raw_barcode);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Project Label</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

<h2>Project Label</h2>

<p><strong>Template:</strong> <?php echo $project['template_name']; ?></p>
<p><strong>Customer:</strong> <?php echo $project['customer_code']; ?></p>
<p><strong>Style:</strong> <?php echo $project['style']; ?></p>
<p><strong>Color:</strong> <?php echo $project['color']; ?></p>
<p><strong>Quantity:</strong> <?php echo $project['requested_qty']; ?></p>

<p><strong>Barcode:</strong></p>
<img src="barcode.php?code=<?php echo urlencode($barcode_text); ?>">

<br><br>
<a href="start_project.php">Start Another Project</a>
<br>
<a href="index.php">Home</a>

</body>
</html>
