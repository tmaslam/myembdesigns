<?php
/**
 * Fix missing AUTO_INCREMENT and PRIMARY KEY on all WordPress tables.
 * Run this after a broken import.
 * DELETE AFTER USE!
 */

$host = 'localhost';
$user = 'myembdesigns_emb';
$pass = '9;4P#9oCZQm1';
$db   = 'myembdesigns_emb';

set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain');
echo "=== Fix AUTO_INCREMENT & PRIMARY KEY ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}

echo "Connected to database: $db\n\n";

// WordPress core tables that need ID auto_increment
$core_tables = [
    'wp_posts'              => ['id_col' => 'ID',              'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_postmeta'           => ['id_col' => 'meta_id',         'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_comments'           => ['id_col' => 'comment_ID',      'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_commentmeta'        => ['id_col' => 'meta_id',         'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_terms'              => ['id_col' => 'term_id',         'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_term_taxonomy'      => ['id_col' => 'term_taxonomy_id','type' => 'bigint(20) unsigned NOT NULL'],
    'wp_term_relationships' => ['id_col' => null,              'type' => null], // composite PK
    'wp_users'              => ['id_col' => 'ID',              'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_usermeta'           => ['id_col' => 'umeta_id',        'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_links'              => ['id_col' => 'link_id',         'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_options'            => ['id_col' => 'option_id',       'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_actionscheduler_actions'    => ['id_col' => 'action_id',   'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_actionscheduler_claims'     => ['id_col' => 'claim_id',    'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_actionscheduler_groups'     => ['id_col' => 'group_id',    'type' => 'bigint(20) unsigned NOT NULL'],
    'wp_actionscheduler_logs'       => ['id_col' => 'log_id',      'type' => 'bigint(20) unsigned NOT NULL'],
];

$fixed = 0;
$skipped = 0;
$errors = 0;

// Get all tables in database
$result = $mysqli->query("SHOW TABLES");
$all_tables = [];
while ($row = $result->fetch_array()) {
    $all_tables[] = $row[0];
}
$result->free();

foreach ($all_tables as $table) {
    echo "Checking: $table\n";
    
    // Get column info
    $cols = $mysqli->query("SHOW COLUMNS FROM `$table`");
    if (!$cols) {
        echo "  ❌ Error reading columns: " . $mysqli->error . "\n";
        $errors++;
        continue;
    }
    
    $has_ai = false;
    $pk_col = null;
    $pk_type = 'bigint(20) unsigned NOT NULL';
    
    while ($col = $cols->fetch_assoc()) {
        if (strpos($col['Extra'], 'auto_increment') !== false) {
            $has_ai = true;
        }
        if ($col['Key'] === 'PRI') {
            $pk_col = $col['Field'];
            $pk_type = $col['Type'] . ' NOT NULL';
        }
    }
    $cols->free();
    
    if ($has_ai) {
        echo "  ✅ AUTO_INCREMENT already present\n";
        $skipped++;
        continue;
    }
    
    // Determine the ID column
    $id_col = null;
    if (isset($core_tables[$table])) {
        $id_col = $core_tables[$table]['id_col'];
    } else {
        // Try to guess: look for a column ending in _id or named id
        $cols2 = $mysqli->query("SHOW COLUMNS FROM `$table`");
        while ($col = $cols2->fetch_assoc()) {
            if ($col['Field'] === 'id' || $col['Field'] === 'ID' || 
                preg_match('/_id$/', $col['Field'])) {
                $id_col = $col['Field'];
                $pk_type = $col['Type'] . ' NOT NULL';
                break;
            }
        }
        $cols2->free();
    }
    
    if (!$id_col) {
        echo "  ⚠️ Cannot determine ID column, skipping\n";
        $skipped++;
        continue;
    }
    
    echo "  ID column: $id_col ($pk_type)\n";
    
    // Check if there's data
    $count_res = $mysqli->query("SELECT MAX(`$id_col`) as max_id FROM `$table`");
    $max_id = 0;
    if ($count_res) {
        $row = $count_res->fetch_assoc();
        $max_id = (int) $row['max_id'];
        $count_res->free();
    }
    $next_ai = $max_id + 1;
    echo "  Max ID: $max_id -> Next AI: $next_ai\n";
    
    // Fix: Add PRIMARY KEY if missing, then AUTO_INCREMENT
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
    
    if (!$pk_col) {
        echo "  → Adding PRIMARY KEY on `$id_col`...\n";
        $alter_pk = "ALTER TABLE `$table` ADD PRIMARY KEY (`$id_col`)";
        if (!$mysqli->query($alter_pk)) {
            echo "  ❌ PK failed: " . $mysqli->error . "\n";
            $errors++;
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
            continue;
        }
        echo "  ✅ PK added\n";
    }
    
    echo "  → Adding AUTO_INCREMENT...\n";
    $alter_ai = "ALTER TABLE `$table` MODIFY `$id_col` $pk_type AUTO_INCREMENT, AUTO_INCREMENT=$next_ai";
    if ($mysqli->query($alter_ai)) {
        echo "  ✅ AUTO_INCREMENT added ($next_ai)\n";
        $fixed++;
    } else {
        echo "  ❌ AI failed: " . $mysqli->error . "\n";
        $errors++;
    }
    
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "\n";
}

echo "=== Summary ===\n";
echo "Fixed:    $fixed\n";
echo "Skipped:  $skipped\n";
echo "Errors:   $errors\n";
echo "\n✅ Done!\n";
echo "⚠️ DELETE THIS FILE NOW!\n";

$mysqli->close();
