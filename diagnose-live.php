<?php
/**
 * Live Site Diagnostic
 * Shows the current fatal error without breaking wp-admin.
 * Upload and visit: https://www.myembdesigns.com/diagnose-live.php
 * DELETE AFTER USE!
 */

header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check PHP version
echo "=== Server Info ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Check if WooCommerce files exist and are readable
$wc_file = __DIR__ . '/wp-content/plugins/woocommerce/includes/class-wc-product-simple.php';
echo "=== WooCommerce Check ===\n";
if (file_exists($wc_file)) {
    echo "File exists: YES\n";
    echo "File size: " . filesize($wc_file) . " bytes\n";
    $lines = file($wc_file);
    echo "Line 30-40:\n";
    for ($i = 29; $i < min(40, count($lines)); $i++) {
        echo "  " . ($i+1) . ": " . $lines[$i];
    }
} else {
    echo "File exists: NO - WooCommerce is missing or corrupted!\n";
}

// Check for ProductType enum
echo "\n=== ProductType Check ===\n";
if (enum_exists('Automattic\WooCommerce\Enums\ProductType')) {
    echo "ProductType enum: EXISTS\n";
} else {
    echo "ProductType enum: MISSING\n";
}

// Check if MU plugin shim was loaded
$mu_plugin = __DIR__ . '/wp-content/mu-plugins/wc-9-compat.php';
echo "MU plugin exists: " . (file_exists($mu_plugin) ? 'YES' : 'NO') . "\n";

// Try loading WordPress and catching the error
echo "\n=== WordPress Load Test ===\n";
try {
    define('WP_USE_THEMES', false);
    require_once __DIR__ . '/wp-load.php';
    echo "WordPress loaded: SUCCESS\n";
    echo "WC Version: " . (defined('WC_VERSION') ? WC_VERSION : 'NOT DEFINED') . "\n";
    
    // Try creating a simple product
    if (class_exists('WC_Product_Simple')) {
        echo "WC_Product_Simple class: EXISTS\n";
        $product = new WC_Product_Simple();
        echo "Product created: SUCCESS (type=" . $product->get_type() . ")\n";
    } else {
        echo "WC_Product_Simple class: MISSING\n";
    }
} catch (Throwable $e) {
    echo "FATAL ERROR:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Done ===\n";
echo "⚠️ DELETE THIS FILE NOW!\n";
