<?php
include 'auth_check.php';
include 'db.php';

$project = null;
$step = null;
$completed = null;
$remaining = null;

/* --------------------------------
   HANDLE BARCODE LOOKUP
---------------------------------*/
if (isset($_POST['barcode']) && trim($_POST['barcode']) !== '') {

    $barcode = trim($_POST['barcode']);

    if (preg_match('/^PROJECT-(\d+)$/', $barcode, $matches)) {

        $project_id = (int)$matches[1];

        // Load project
        $stmt = $conn->prepare("
            SELECT *
            FROM projects
            WHERE project_id = ?
              AND date_completed IS NULL
        ");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $project = $stmt->get_result()->fetch_assoc();

        if ($project) {

            // Find current step + progress
            $stmt = $conn->prepare("
                SELECT pts.step_number, pts.step_description,
                       COALESCE(ps.completed_qty, 0) AS completed_qty
                FROM project_template_steps pts
                LEFT JOIN (
                    SELECT step_number, SUM(updated_qty) AS completed_qty
                    FROM project_steps
                    WHERE project_id = ?
                    GROUP BY step_number
                ) ps ON ps.step_number = pts.step_number
                WHERE pts.template_id = ?
                  AND COALESCE(ps.completed_qty, 0) < ?
                ORDER BY pts.step_number ASC
                LIMIT 1
            ");
            $stmt->bind_param(
                "iii",
                $project_id,
                $project['template_id'],
                $project['requested_qty']
            );
            $stmt->execute();
            $step = $stmt->get_result()->fetch_assoc();

            if ($step) {
                $completed = (int)$step['completed_qty'];
                $remaining = $project['requested_qty'] - $completed;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scan Project</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .panel {
            max-width: 500px;
            margin: 20px auto;
            text-align: center;
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: #444; }
    </style>
</head>
<body>

<h2>Scan License Plate</h2>

<!-- SCAN BARCODE -->
<?php if (!$step): ?>
<form method="post" class="panel">
    <label>Scan Barcode</label>
    <input type="text"
           name="barcode"
           autofocus
           inputmode="none"
           placeholder="Scan barcode"
           required>
</form>
<?php endif; ?>

<!-- STEP INFO + QUANTITY -->
<?php if ($step && $project): ?>
<div class="panel">

    <p class="info"><strong>Project:</strong> <?= htmlspecialchars($barcode) ?></p>
    <p class="info"><strong>Current Step:</strong> <?= htmlspecialchars($step['step_description']) ?></p>

    <p class="success">
        Progress: <?= $completed ?> / <?= $project['requested_qty'] ?>
    </p>

    <p class="info">
        Remaining: <?= $remaining ?>
    </p>

    <!-- QUANTITY CONFIRM -->
    <form method="post" action="update_step.php">
        <input type="hidden" name="barcode" value="<?= htmlspecialchars($barcode) ?>">

        <label>Quantity Completed</label>
        <input type="number"
               name="updated_qty"
               min="1"
               max="<?= $remaining ?>"
               required>

        <button type="submit">Confirm Update</button>
    </form>

</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="panel error">
    ❌ <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<div style="text-align:center;">
    <a href="index.php">Home</a>
</div>

</body>
</html>
