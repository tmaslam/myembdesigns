<?php
/**
 * Action Scheduler Cleanup Script
 * Upload to your live server root, run once, then DELETE it.
 */

define('WP_USE_THEMES', false);
require_once dirname(__FILE__) . '/wp-load.php';

echo "=== Action Scheduler Cleanup ===\n\n";

// Method 1: Clear all pending/failed actions
global $wpdb;

$tables = $wpdb->get_results("SHOW TABLES LIKE '%actionscheduler%'", ARRAY_N);
if (!empty($tables)) {
    echo "Found Action Scheduler tables:\n";
    foreach ($tables as $table) {
        echo " - " . $table[0] . "\n";
    }
    
    // Count problematic actions
    $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_actions WHERE status = 'pending'");
    $failed_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_actions WHERE status = 'failed'");
    $in_progress_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_actions WHERE status = 'in-progress'");
    
    echo "\nCurrent actions:\n";
    echo " - Pending: $pending_count\n";
    echo " - Failed: $failed_count\n";
    echo " - In-progress: $in_progress_count\n";
    
    // Clear failed and orphaned in-progress actions
    if ($failed_count > 0 || $in_progress_count > 0) {
        echo "\n>>> Clearing failed and stuck in-progress actions...\n";
        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE status IN ('failed', 'in-progress')");
        echo "Done!\n";
    }
    
    // Clear orphaned claims
    $claims_deleted = $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_claims WHERE claim_id = 0");
    if ($claims_deleted) {
        echo ">>> Cleared $claims_deleted orphaned claims.\n";
    }
    
    // Clear old logs (older than 30 days)
    $logs_deleted = $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_logs WHERE log_date_gmt < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    if ($logs_deleted) {
        echo ">>> Cleared $logs_deleted old log entries.\n";
    }
    
} else {
    echo "No Action Scheduler tables found.\n";
}

echo "\n=== Cleanup Complete ===\n";
echo "IMPORTANT: Delete this file (fix-action-scheduler.php) immediately after running!\n";
