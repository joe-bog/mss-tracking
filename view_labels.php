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
        requested_qty,
        created_at
    FROM projects
    WHERE date_completed IS NULL
    ORDER BY project_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View & Print Labels</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        .label-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
        }

        .label-card {
            border: 1px solid #ccc;
            padding: 15px;
            background: #fff;
            text-align: center;
            cursor: pointer;
        }

        .label-card:hover {
            background: #f9f9f9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .barcode {
            margin: 10px 0;
        }

        .barcode-text {
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 6px;
        }

        .print-btn {
            margin-top: 10px;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .label-card, .label-card * {
                visibility: visible;
            }
            .label-card {
                page-break-after: always;
                border: none;
            }
        }
    </style>
</head>
<body>

<h2>Active Project Labels</h2>
<p>Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>

<?php if ($projects->num_rows === 0): ?>
    <p>No active projects.</p>
<?php else: ?>

<button onclick="printAllLabels()">🖨️ Print All Labels</button>
<br><br>

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
        $p['requested_qty'];
?>

<div class="label-card">

    <strong>Project #<?= str_pad($p['project_id'], 4, "0", STR_PAD_LEFT); ?></strong><br>
    <small><?= date("m/d/Y H:i", strtotime($p['created_at'])); ?></small>

    <div class="barcode">
        <img src="barcode.php?code=<?= urlencode($barcode_code); ?>">
    </div>

    <div class="barcode-text">
        <?= htmlspecialchars($bottom_text); ?>
    </div>


</div>

<?php endwhile; ?>

</div>

<?php endif; ?>

<br>
<a href="index.php">Home</a>

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

// Event listener for clicking label cards
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.label-card').forEach(card => {
        card.addEventListener('click', function() {
            printSingle(this);
        });
    });
});
</script>

</body>
</html>
