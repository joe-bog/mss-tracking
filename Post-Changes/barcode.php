<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// ------------------------------------------
// Validate input
// ------------------------------------------
if (!isset($_GET['code']) || trim($_GET['code']) === '') {
    http_response_code(400);
    exit('No barcode data provided.');
}

$code = trim($_GET['code']);

// Optional safety limit
if (strlen($code) > 64) {
    http_response_code(400);
    exit('Barcode data too long.');
}

// ------------------------------------------
// Generate barcode
// ------------------------------------------
$generator = new BarcodeGeneratorPNG();

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

echo $generator->getBarcode(
    $code,
    $generator::TYPE_CODE_128,
    2,   // bar width (px)
    80   // bar height (px)
);
