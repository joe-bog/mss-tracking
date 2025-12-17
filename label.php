<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_GET['project_id'])) {
    die("Project ID required.");
}

$project_id = (int)$_GET['project_id'];

$project = $conn->query("
    SELECT *
    FROM projects
    WHERE project_id = $project_id
")->fetch_assoc();

if (!$project) {
    die("Project not found.");
}

// ✅ SINGLE SOURCE OF TRUTH
$barcode_text = "PROJECT-" . $project_id;


$display_line = strtoupper(
    $project['customer_code'] . '-' .
    $project['template_name'] . '-' .
    str_replace('"', '', $project['style']) . '-' . // Removes quotes from style
    $project['color'] . '-' .
    $project['requested_qty']
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Project Label</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* THE LABEL CONTAINER */
        .label {
            border: 2px solid #000;
            display: inline-flex;      /* Uses flexbox for alignment */
            flex-direction: column;    /* Stacks items vertically */
            align-items: center;       /* Centers items horizontally */
            justify-content: center;   /* Centers items vertically */
            padding: 15px;             /* Space between content and border */
            margin-top: 20px;
            width: 380px;              /* Slightly wider for safety */
            background: white;
        }

        /* PROJECT ID (TOP TEXT) */
        .label .project-number {
            margin: 0 0 10px 0;        /* Removes top margin to move it higher */
            font-size: 20px;
            font-weight: bold;
        }

        /* BARCODE IMAGE */
        .label img {
            display: block;
            margin: 0 auto 15px auto;  /* Centers and adds space below */
            max-width: 90%;            /* Prevents barcode from touching borders */
            height: auto;
        }

        /* FOOTER DETAILS */
        .label .details {
            margin: 0;
            font-size: 14px;
            line-height: 1.3;
            word-wrap: break-word;     /* Ensures long text doesn't break border */
            width: 100%;
        }

        @media print {
            body { background: white; padding: 0; }
            h2, a, button { display: none !important; }
            .label { margin: 0; border: 2px solid #000; }
        }
    </style>
</head>
<body>

<h2>Project Label</h2>

<div class="label">
    
    <p><?= htmlspecialchars($project_id) ?></p>
    <img src="barcode.php?code=<?= urlencode($barcode_text) ?>" alt="Barcode">

    <p><?= htmlspecialchars($display_line) ?></p>
</div>

<br><br>
<button onclick="window.print()">Print Label</button>
<br><br>
<a href="start_project.php">Start Another Project</a>
<a href="index.php">Home</a>

</body>
</html>
