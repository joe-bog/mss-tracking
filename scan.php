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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Project</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            text-align: center;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .header p {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 8px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .scan-icon {
            font-size: 64px;
            text-align: center;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.05); opacity: 1; }
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            text-align: center;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 18px;
            transition: all 0.2s;
            font-family: 'Courier New', monospace;
            text-align: center;
            letter-spacing: 2px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .project-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e0e6ed;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #7f8c8d;
            font-weight: 600;
            font-size: 14px;
        }
        
        .info-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 16px;
        }
        
        .progress-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .progress-section h3 {
            margin-bottom: 15px;
            font-size: 18px;
            opacity: 0.9;
        }
        
        .progress-numbers {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .progress-bar-container {
            background: rgba(255, 255, 255, 0.2);
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 15px;
        }
        
        .progress-bar-fill {
            background: white;
            height: 100%;
            border-radius: 6px;
            transition: width 0.3s ease;
        }
        
        .remaining-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 20px;
            margin-top: 12px;
            font-size: 14px;
        }
        
        /* Main Action Button */
        .btn-primary {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 16px 28px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        /* Secondary/Camera Button */
        .btn-secondary {
            background: #ffffff;
            color: #2c3e50;
            padding: 14px 28px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-top: 12px;
        }
        
        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.2s;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .camera-panel {
            display: none;
            margin-top: 20px;
            text-align: center;
        }

        .camera-panel.active {
            display: block;
        }

        .camera-preview {
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            border: 2px solid #e0e6ed;
            background: #000;
        }

        .scan-status {
            margin-top: 12px;
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Project Scanning</h1>
            <p>Logged in as: <?= htmlspecialchars($_SESSION['user_name']) ?></p>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <span style="font-size: 24px;">❌</span>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!$step): ?>
            <div class="card">
                <div class="scan-icon">🔍</div>
                <form method="post" id="scan-form">
                    <div class="form-group">
                        <label>Enter or Scan Barcode</label>
                        <input type="text"
                               id="barcode-input"
                               name="barcode"
                               autofocus
                               placeholder="PROJECT-XXXXX"
                               required>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn-primary">
                            Enter / Lookup Project
                        </button>
                        
                        <button type="button" class="btn-secondary" id="start-camera">
                            📷 Start Camera Scan
                        </button>
                    </div>

                    <div class="camera-panel" id="camera-panel">
                        <video id="camera-preview" class="camera-preview" muted playsinline></video>
                        <div class="scan-status" id="scan-status">
                            Align the barcode within the frame to scan.
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if ($step && $project): ?>
            <div class="card">
                <div class="project-info">
                    <div class="info-row">
                        <span class="info-label">Project ID</span>
                        <span class="info-value"><?= htmlspecialchars($barcode) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Current Step</span>
                        <span class="info-value"><?= htmlspecialchars($step['step_description']) ?></span>
                    </div>
                </div>
                
                <div class="progress-section">
                    <h3>Step Progress</h3>
                    <div class="progress-numbers">
                        <?= $completed ?> / <?= $project['requested_qty'] ?>
                    </div>
                    <?php 
                    $progress_percent = $project['requested_qty'] > 0 
                        ? round(($completed / $project['requested_qty']) * 100) 
                        : 0;
                    ?>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= $progress_percent ?>%"></div>
                    </div>
                    <div class="remaining-badge">
                        <?= $remaining ?> Remaining
                    </div>
                </div>
                
                <form method="post" action="update_step.php">
                    <input type="hidden" name="barcode" value="<?= htmlspecialchars($barcode) ?>">
                    <div class="form-group">
                        <label>Enter Quantity Completed</label>
                        <input type="number" name="updated_qty" min="1" max="<?= $remaining ?>" autofocus required style="font-size: 24px; padding: 20px;">
                    </div>
                    <button type="submit" class="btn-primary">✓ Confirm Update</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="text-center">
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js"></script>
    <script>
        const startButton = document.getElementById('start-camera');
        const cameraPanel = document.getElementById('camera-panel');
        const cameraPreview = document.getElementById('camera-preview');
        const scanStatus = document.getElementById('scan-status');
        const barcodeInput = document.getElementById('barcode-input');
        const scanForm = document.getElementById('scan-form');

        if (startButton) {
            let codeReader = null;
            const setStatus = (message) => { if (scanStatus) scanStatus.textContent = message; };

            startButton.addEventListener('click', () => {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setStatus('Camera access is not supported on this device.');
                    return;
                }

                startButton.disabled = true;
                startButton.textContent = 'Scanning...';
                cameraPanel.classList.add('active');
                setStatus('Starting camera...');

                codeReader = new ZXing.BrowserMultiFormatReader();
                codeReader.decodeFromVideoDevice(
                    null,
                    cameraPreview,
                    (result, error) => {
                        if (result) {
                            barcodeInput.value = result.getText();
                            setStatus('Barcode detected. Submitting...');
                            codeReader.reset();
                            scanForm.submit();
                        }
                    }
                ).catch(() => {
                    setStatus('Unable to access camera. Check permissions.');
                    startButton.disabled = false;
                    startButton.textContent = '📷 Start Camera Scan';
                });
            });
        }
    </script>
</body>
</html>