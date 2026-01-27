<?php
include 'auth_check.php';
include 'db.php';

/*
|--------------------------------------------------------------------------
| Fetch ACTIVE projects only
|--------------------------------------------------------------------------
*/
$projects = $conn->query("
    SELECT
        project_id,
        template_name,
        customer_code,
        style,
        color,
        final_chip_qty,
        created_at
    FROM projects
    WHERE date_completed IS NULL
    ORDER BY project_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View & Print Labels</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header-left h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-left .user-info {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .print-all-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 60px 20px;
            text-align: center;
            color: #7f8c8d;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .label-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .label-card {
            border: 2px solid #e0e6ed;
            padding: 0.25in;
            background: #fff;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 400px;
        }
        
        .label-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .label-card:hover::after {
            content: '🖨️ Click to Print';
            position: absolute;
            top: 10px;
            right: 10px;
            background: #667eea;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .label-header {
            margin-bottom: 12px;
        }
        
        .project-id {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        
        .project-date {
            font-size: 12px;
            color: #7f8c8d;
            margin: 4px 0 0 0;
        }
        
        .barcode-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
        }
        
        .barcode {
            margin: 10px 0;
            text-align: center;
        }
        
        .barcode img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        
        .barcode-text {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            word-wrap: break-word;
            width: 100%;
            margin-top: 8px;
            line-height: 1.3;
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
        
        .back-link:hover {
            background: #f8f9fa;
            transform: translateX(-4px);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            body * {
                visibility: hidden;
            }
            
            .label-grid,
            .label-grid * {
                visibility: visible;
            }
            
            .label-grid {
                display: block;
            }
            
            .label-card {
                page-break-after: always;
                display: block;
                visibility: visible;
                margin-bottom: 20px;
                padding: 15px;
                break-inside: avoid;
                box-shadow: none;
            }
            
            .label-card * {
                visibility: visible;
            }
            
            .label-card::after {
                display: none !important;
            }
            .label-header {
                margin-bottom: 10px;
                page-break-after: avoid;
            }
            
            .barcode-section {
                page-break-after: avoid;
            }
            
            .back-link,
            .print-all-btn,
            .header {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header-left h1 {
                font-size: 24px;
            }
            
            .header-content {
                flex-direction: column;
                align-items: stretch;
            }
            
            .print-all-btn {
                width: 100%;
                justify-content: center;
            }
            
            .label-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <h1>🏷️ Active Project Labels</h1>
                    <p class="user-info">Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>
                </div>
                
                <?php if ($projects->num_rows > 0): ?>
                    <button onclick="printAllLabels()" class="print-all-btn">
                        🖨️ Print All Labels
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($projects->num_rows === 0): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No Active Projects</p>
                <p>Create a new project to generate labels</p>
            </div>
        <?php else: ?>
            <div class="label-grid">
                <?php while ($p = $projects->fetch_assoc()): ?>
                    <?php
                        // Build barcode + display text
                        $barcode_code = "PROJECT-" . $p['project_id'];

                        $bottom_text =
                            $p['customer_code'] . "-" .
                            strtoupper(str_replace(" ", "", $p['template_name'])) . "-" .
                            $p['style'] . "-" .
                            strtoupper($p['color']) . "-" .
                            $p['final_chip_qty'];
                    ?>
                    
                    <div class="label-card" onclick="printSingle(this)">
                        <div class="label-header">
                            <div class="project-id">
                                Project #<?= str_pad($p['project_id'], 4, "0", STR_PAD_LEFT); ?>
                            </div>
                            <div class="project-date">
                                <?= date("m/d/Y H:i", strtotime($p['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="barcode-section">
                            <div class="barcode">
                                <img src="barcode.php?code=<?= urlencode($barcode_code); ?>" alt="Barcode">
                            </div>
                            
                            <div class="barcode-text">
                                <?= htmlspecialchars($bottom_text); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>

    <script>
    function printAllLabels() {
        window.print();
    }

    function printSingle(card) {
        const original = document.body.innerHTML;
        const printContent = card.outerHTML;

        document.body.innerHTML = printContent;
        
        // Wait for images to load before printing
        const images = document.querySelectorAll('img');
        let imagesLoaded = 0;
        
        if (images.length === 0) {
            window.print();
        } else {
            images.forEach(img => {
                img.onload = function() {
                    imagesLoaded++;
                    if (imagesLoaded === images.length) {
                        window.print();
                    }
                };
                img.onerror = function() {
                    imagesLoaded++;
                    if (imagesLoaded === images.length) {
                        window.print();
                    }
                };
            });
        }
        
        window.addEventListener('afterprint', function() {
            document.body.innerHTML = original;
            location.reload();
        });
    }
    </script>
</body>
</html>