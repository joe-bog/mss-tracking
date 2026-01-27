<?php
include 'auth_check.php';
include 'db.php';

/*
|--------------------------------------------------------------------------
| DASHBOARD OVERVIEW (by Template -> by Color -> by Step)
|--------------------------------------------------------------------------
| - Only OPEN projects (date_completed IS NULL)
| - Rows: template steps
| - Columns: colors with open projects for that template
| - Cell values: "Need at this step" in CHIP quantities
| - Completed row: chips completed at the last step (ready for assembly)
*/

/* -------------------------------
   1) Fetch all OPEN projects
--------------------------------*/
$openProjectsRes = $conn->query("
    SELECT project_id, template_id, template_name, color, final_chip_qty
    FROM projects
    WHERE date_completed IS NULL
");

$projects = [];
$templateNames = [];
$projectIds = [];

while ($row = $openProjectsRes->fetch_assoc()) {
    $pid = (int)$row['project_id'];
    $tid = (int)$row['template_id'];

    $projects[$pid] = [
        'project_id'     => $pid,
        'template_id'    => $tid,
        'template_name'  => $row['template_name'],
        'color'          => $row['color'],
        'expected'       => (int)$row['final_chip_qty'],
    ];

    $templateNames[$tid] = $row['template_name'];
    $projectIds[] = $pid;
}

/* If no open projects, we can show empty state early */
$hasOpenProjects = count($projects) > 0;

/* -------------------------------
   2) Fetch steps for templates that appear in open projects
--------------------------------*/
$templateSteps = []; // [template_id][step_number] => step_description

if ($hasOpenProjects) {
    $templateIds = array_values(array_unique(array_map(fn($p) => $p['template_id'], $projects)));
    $in = implode(',', array_fill(0, count($templateIds), '?'));

    $stmtSteps = $conn->prepare("
        SELECT template_id, step_number, step_description
        FROM project_template_steps
        WHERE template_id IN ($in)
        ORDER BY template_id ASC, step_number ASC
    ");

    $types = str_repeat('i', count($templateIds));
    $stmtSteps->bind_param($types, ...$templateIds);
    $stmtSteps->execute();
    $resSteps = $stmtSteps->get_result();

    while ($s = $resSteps->fetch_assoc()) {
        $tid = (int)$s['template_id'];
        $sn  = (int)$s['step_number'];
        $templateSteps[$tid][$sn] = $s['step_description'];
    }
}

/* -------------------------------
   3) Fetch cumulative completed qty by project & step
--------------------------------*/
$completedByProjectStep = []; // [project_id][step_number] => completed_qty

if ($hasOpenProjects) {
    $inP = implode(',', array_fill(0, count($projectIds), '?'));

    $stmtPS = $conn->prepare("
        SELECT project_id, step_number, COALESCE(SUM(updated_qty), 0) AS completed_qty
        FROM project_steps
        WHERE project_id IN ($inP)
        GROUP BY project_id, step_number
    ");

    $typesP = str_repeat('i', count($projectIds));
    $stmtPS->bind_param($typesP, ...$projectIds);
    $stmtPS->execute();
    $resPS = $stmtPS->get_result();

    while ($r = $resPS->fetch_assoc()) {
        $pid = (int)$r['project_id'];
        $sn  = (int)$r['step_number'];
        $completedByProjectStep[$pid][$sn] = (int)$r['completed_qty'];
    }
}

/* -------------------------------
   4) Aggregate "Need at each step" by template + color
--------------------------------*/
$colorsByTemplate = [];       // [template_id] => [color1, color2, ...]
$needAgg = [];                // [template_id][color][step_number] => need_qty
$completedAgg = [];           // [template_id][color] => completed_qty_at_last_step
$pendingAgg = [];             // [template_id][color] => total_pending_qty

if ($hasOpenProjects) {
    foreach ($projects as $pid => $p) {
        $tid = $p['template_id'];
        $color = $p['color'];
        $expected = max(0, (int)$p['expected']);

        if (!isset($templateSteps[$tid]) || count($templateSteps[$tid]) === 0) {
            // If template has no defined steps, skip safely
            continue;
        }

        $colorsByTemplate[$tid][$color] = true;

        $stepNumbers = array_keys($templateSteps[$tid]);
        sort($stepNumbers);
        $lastStepNumber = end($stepNumbers);

        // Build cumulative completion per step (clamped to [0, expected])
        $cum = [];
        foreach ($stepNumbers as $sn) {
            $c = $completedByProjectStep[$pid][$sn] ?? 0;
            $c = max(0, min($expected, (int)$c));
            $cum[$sn] = $c;
        }

        // Need at step = prevCum - currentCum (for step>1), and expected - cum(first) for step1
        $prevCum = null;
        foreach ($stepNumbers as $idx => $sn) {
            $currentCum = $cum[$sn];

            if ($idx === 0) {
                $need = $expected - $currentCum;
            } else {
                $need = ($prevCum ?? 0) - $currentCum;
            }

            $need = max(0, (int)$need);

            // Aggregate only for displayed step rows (all steps)
            $needAgg[$tid][$color][$sn] = ($needAgg[$tid][$color][$sn] ?? 0) + $need;

            $prevCum = $currentCum;
        }

        // Completed chips = cumulative at last step
        $completedAtEnd = $cum[$lastStepNumber] ?? 0;
        $completedAgg[$tid][$color] = ($completedAgg[$tid][$color] ?? 0) + $completedAtEnd;

        // Pending = expected - completedAtEnd
        $pending = max(0, $expected - $completedAtEnd);
        $pendingAgg[$tid][$color] = ($pendingAgg[$tid][$color] ?? 0) + $pending;
    }

    // Convert color sets to sorted arrays
    foreach ($colorsByTemplate as $tid => $set) {
        $colors = array_keys($set);
        sort($colors);
        $colorsByTemplate[$tid] = $colors;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Overview</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background: #f5f7fa;
      padding: 20px;
      min-height: 100vh;
    }
    .container { max-width: 1600px; margin: 0 auto; }

    .header {
      background: white;
      padding: 25px 30px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 18px;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    .header h1 { color: #2c3e50; font-size: 28px; display: flex; align-items: center; gap: 12px; }
    .header .user-info { color: #7f8c8d; font-size: 14px; margin-top: 6px; }
    .note {
      background: #fff;
      border-left: 4px solid #667eea;
      padding: 14px 16px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      color: #2c3e50;
      font-size: 13px;
      max-width: 720px;
      line-height: 1.4;
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 18px;
    }

    .card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .card-header {
      padding: 16px 18px;
      border-bottom: 1px solid #ecf0f1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
    }

    .card-title {
      font-size: 16px;
      font-weight: 800;
      color: #2c3e50;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .table-wrapper { width: 100%; overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; min-width: 900px; }
    thead { background: #f8f9fa; }
    th {
      padding: 14px 12px;
      text-align: left;
      font-weight: 700;
      color: #2c3e50;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e0e6ed;
      vertical-align: bottom;
      white-space: nowrap;
    }
    td {
      padding: 12px 12px;
      border-bottom: 1px solid #ecf0f1;
      color: #2c3e50;
      font-size: 14px;
      white-space: nowrap;
    }
    tbody tr:hover { background: #f8f9fa; }

    .step-col { width: 260px; white-space: normal; }
    .step-badge {
      display: inline-block;
      padding: 6px 10px;
      background: #e8f4f8;
      color: #667eea;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 700;
      white-space: normal;
    }

    .color-head {
      display: flex;
      flex-direction: column;
      gap: 6px;
      line-height: 1.1;
    }
    .color-name { font-size: 16px; font-weight: 900; text-transform: none; }
    .pending {
      font-size: 12px;
      font-weight: 700;
      color: #2c3e50;
      opacity: 0.9;
      text-transform: none;
      letter-spacing: 0;
    }

    .num {
      font-weight: 800;
      font-variant-numeric: tabular-nums;
    }

    .row-strong td { border-top: 3px solid #2c3e50; }
    .completed-label {
      font-weight: 900;
      font-size: 14px;
      color: #2c3e50;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #7f8c8d;
    }
    .empty-state-icon { font-size: 64px; margin-bottom: 16px; opacity: 0.5; }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #667eea;
      text-decoration: none;
      font-weight: 700;
      padding: 12px 18px;
      border-radius: 10px;
      transition: all 0.2s;
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-top: 18px;
    }
    .back-link:hover { background: #f8f9fa; transform: translateX(-4px); }

    @media (max-width: 900px) {
      table { min-width: 720px; }
      .step-col { width: 220px; }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>📊 Dashboard Overview</h1>
        <p class="user-info">Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>
      </div>

    </div>

    <?php if (!$hasOpenProjects): ?>
      <div class="card">
        <div class="empty-state">
          <div class="empty-state-icon">📦</div>
          <p style="font-size: 18px; font-weight: 800; margin-bottom: 8px;">No Open Projects</p>
          <p>When projects are in progress, this page will show totals by template, color, and step.</p>
        </div>
      </div>
    <?php else: ?>

      <div class="grid">
        <?php foreach ($templateSteps as $tid => $stepsMap): ?>
          <?php
            $colors = $colorsByTemplate[$tid] ?? [];
            if (count($colors) === 0) continue;

            $stepNumbers = array_keys($stepsMap);
            sort($stepNumbers);
            $templateLabel = htmlspecialchars($templateNames[$tid] ?? ('Template #' . $tid));
          ?>

          <div class="card">
            <div class="card-header">
              <div class="card-title">🧩 <?= $templateLabel; ?> — Step Totals by Color</div>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th class="step-col">Step</th>
                    <?php foreach ($colors as $color): ?>
                      <?php $pending = $pendingAgg[$tid][$color] ?? 0; ?>
                      <th>
                        <div class="color-head">
                          <div class="color-name"><?= htmlspecialchars($color); ?></div>
                          <div class="pending">Total pending count: <span class="num"><?= (int)$pending; ?></span></div>
                        </div>
                      </th>
                    <?php endforeach; ?>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($stepNumbers as $sn): ?>
                    <tr>
                      <td class="step-col">
                        <span class="step-badge"><?= htmlspecialchars($stepsMap[$sn]); ?></span>
                      </td>

                      <?php foreach ($colors as $color): ?>
                        <?php $val = $needAgg[$tid][$color][$sn] ?? 0; ?>
                        <td><span class="num"><?= (int)$val; ?></span></td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endforeach; ?>

                  <tr class="row-strong">
                    <td class="step-col">
                      <span class="completed-label">Completed Chips (Ready for assembly)</span>
                    </td>
                    <?php foreach ($colors as $color): ?>
                      <?php $done = $completedAgg[$tid][$color] ?? 0; ?>
                      <td><span class="num"><?= (int)$done; ?></span></td>
                    <?php endforeach; ?>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        <?php endforeach; ?>
      </div>

    <?php endif; ?>

    <a href="index.php" class="back-link">← Back to Home</a>
  </div>
</body>
</html>
