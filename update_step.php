<?php
include 'auth_check.php';
include 'db.php';

/*
|--------------------------------------------------------------------------
| 1. Validate input
|--------------------------------------------------------------------------
*/
if (
    !isset($_POST['barcode']) || trim($_POST['barcode']) === '' ||
    !isset($_POST['updated_qty']) || intval($_POST['updated_qty']) <= 0
) {
    header("Location: scan.php?error=Missing+barcode+or+quantity");
    exit;
}

$barcode     = trim($_POST['barcode']);
$updated_qty = intval($_POST['updated_qty']);

/*
|--------------------------------------------------------------------------
| 2. Extract project_id from barcode
|--------------------------------------------------------------------------
*/
if (!preg_match('/^PROJECT-(\d+)$/', $barcode, $matches)) {
    header("Location: scan.php?error=Invalid+barcode+format&barcode=" . urlencode($barcode));
    exit;
}

$project_id = (int)$matches[1];

/*
|--------------------------------------------------------------------------
| 3. Load project
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT *
    FROM projects
    WHERE project_id = ?
      AND date_completed IS NULL
");
$stmt->bind_param("i", $project_id);
$stmt->execute();

$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    header("Location: scan.php?error=Project+not+found+or+completed&barcode=" . urlencode($barcode));
    exit;
}

$template_id   = (int)$project['template_id'];
$final_chip_qty = (int)$project['final_chip_qty'];

/*
|--------------------------------------------------------------------------
| 4. Determine CURRENT step based on quantity progress
|--------------------------------------------------------------------------
*/
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
$stmt->bind_param("iii", $project_id, $template_id, $final_chip_qty);
$stmt->execute();

$step = $stmt->get_result()->fetch_assoc();

/*
|--------------------------------------------------------------------------
| 5. If no step found → project complete
|--------------------------------------------------------------------------
*/
if (!$step) {
    $conn->query("
        UPDATE projects
        SET date_completed = NOW()
        WHERE project_id = $project_id
    ");

    header("Location: scan.php?complete=1&barcode=" . urlencode($barcode));
    exit;
}

/*
|--------------------------------------------------------------------------
| 6. Enforce step order (no skipping)
|--------------------------------------------------------------------------
*/
if ($step['step_number'] > 1) {
    $prev_step = $step['step_number'] - 1;

    $prevCheck = $conn->query("
        SELECT COALESCE(SUM(updated_qty), 0) AS qty
        FROM project_steps
        WHERE project_id = $project_id
          AND step_number = $prev_step
    ")->fetch_assoc();

    if ($prevCheck['qty'] <= 0) {
        header("Location: scan.php?error=Previous+step+not+started");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| 7. Validate quantity does not exceed remaining
|--------------------------------------------------------------------------
*/
$completed_so_far = (int)$step['completed_qty'];
$remaining        = $final_chip_qty - $completed_so_far;

if ($updated_qty > $remaining) {
    header("Location: scan.php?error=Quantity+exceeds+remaining+$remaining");
    exit;
}

/*
|--------------------------------------------------------------------------
| 8. Insert step progress (append-only)
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    INSERT INTO project_steps
    (project_id, template_id, template_name, step_number, step_description, updated_qty, updated_by, updated_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "iisisii",
    $project_id,
    $template_id,
    $project['template_name'],
    $step['step_number'],
    $step['step_description'],
    $updated_qty,
    $_SESSION['user_id']
);

$stmt->execute();

/*
|--------------------------------------------------------------------------
| 9. Compute new progress
|--------------------------------------------------------------------------
*/
$new_completed = $completed_so_far + $updated_qty;
$new_remaining = $final_chip_qty - $new_completed;

/*
|--------------------------------------------------------------------------
| 10. Determine next step
|--------------------------------------------------------------------------
*/
$nextStep = null;

if ($new_completed >= $final_chip_qty) {
    $next = $conn->query("
        SELECT step_description
        FROM project_template_steps
        WHERE template_id = $template_id
          AND step_number > {$step['step_number']}
        ORDER BY step_number ASC
        LIMIT 1
    ")->fetch_assoc();

    if ($next) {
        $nextStep = $next['step_description'];
    }
}

/*
|--------------------------------------------------------------------------
| 12. If THIS was the LAST step and it's now complete → close project
|--------------------------------------------------------------------------
*/
if ($new_completed >= $final_chip_qty && !$nextStep) {

    $stmt = $conn->prepare("
        UPDATE projects
        SET date_completed = NOW()
        WHERE project_id = ?
          AND date_completed IS NULL
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();

    header("Location: scan.php?complete=1&barcode=" . urlencode($barcode));
    exit;
}


/*
|--------------------------------------------------------------------------
| 11. Redirect with rich feedback
|--------------------------------------------------------------------------
*/
$url = "scan.php?success=1"
     . "&barcode=" . urlencode($barcode)
     . "&step=" . urlencode($step['step_description'])
     . "&completed=$new_completed"
     . "&remaining=$new_remaining";

if ($nextStep) {
    $url .= "&next_step=" . urlencode($nextStep);
}

header("Location: $url");
exit;


