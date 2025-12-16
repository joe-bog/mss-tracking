<?php
include 'auth_check.php';
include 'db.php';

/*
|--------------------------------------------------------------------------
| 1. Validate scan
|--------------------------------------------------------------------------
*/
if (!isset($_POST['barcode']) || trim($_POST['barcode']) === '') {
    die("No barcode scanned.");
}

$barcode = trim($_POST['barcode']);

/*
|--------------------------------------------------------------------------
| 2. Extract project_id from barcode
|--------------------------------------------------------------------------
*/
if (!preg_match('/^PROJECT-(\d+)$/', $barcode, $matches)) {
    die("Invalid barcode format.");
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
    die("Project not found or already completed.");
}

$template_id = (int)$project['template_id'];

/*
|--------------------------------------------------------------------------
| 4. Find next uncompleted step
|--------------------------------------------------------------------------
*/
$stepQuery = $conn->query("
    SELECT pts.step_number, pts.step_description
    FROM project_template_steps pts
    LEFT JOIN project_steps ps
        ON ps.project_id = $project_id
       AND ps.step_number = pts.step_number
    WHERE pts.template_id = $template_id
      AND ps.step_id IS NULL
    ORDER BY pts.step_number ASC
    LIMIT 1
");

$step = $stepQuery->fetch_assoc();

/*
|--------------------------------------------------------------------------
| 5. If no steps left → complete project
|--------------------------------------------------------------------------
*/
if (!$step) {
    $conn->query("
        UPDATE projects
        SET date_completed = NOW()
        WHERE project_id = $project_id
    ");

    header("Location: scan.php?complete=1");
    exit;
}

/*
|--------------------------------------------------------------------------
| 6. Insert project step
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    INSERT INTO project_steps
    (project_id, template_id, template_name, step_number, step_description, updated_by, updated_date)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "iisisi",
    $project_id,
    $template_id,
    $project['template_name'],
    $step['step_number'],
    $step['step_description'],
    $_SESSION['user_id']
);

$exists = $conn->query("
    SELECT step_id
    FROM project_steps
    WHERE project_id = $project_id
      AND step_number = {$step['step_number']}
    LIMIT 1
");

if ($exists->num_rows > 0) {
    header("Location: scan.php?duplicate=1");
    exit;
}

/*
|--------------------------------------------------------------------------
| 7. Redirect back to scan
|--------------------------------------------------------------------------
*/
header("Location: scan.php?success=1&step=" . urlencode($step['step_description']));
exit;
