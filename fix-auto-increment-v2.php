<?php
/**
 * Fix missing AUTO_INCREMENT v2 - Handles duplicates, corrupted PKs, and timeouts.
 * DELETE AFTER USE!
 */

$host = 'localhost';
$user = 'myembdesigns_emb';
$pass = '9;4P#9oCZQm1';
$db   = 'myembdesigns_emb';

set_time_limit(600);
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
error_reporting(E_ALL);

header('Content-Type: text/plain');
echo "=== Fix AUTO_INCREMENT v2 ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}

echo "Connected.\n\n";
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

// Tables to fix with their ID columns
$tables = [
    'wp_posts'              => 'ID',
    'wp_postmeta'           => 'meta_id',
    'wp_comments'           => 'comment_ID',
    'wp_commentmeta'        => 'meta_id',
    'wp_terms'              => 'term_id',
    'wp_term_taxonomy'      => 'term_taxonomy_id',
    'wp_users'              => 'ID',
    'wp_usermeta'           => 'umeta_id',
    'wp_links'              => 'link_id',
    'wp_options'            => 'option_id',
    'wp_actionscheduler_actions'    => 'action_id',
    'wp_actionscheduler_claims'     => 'claim_id',
    'wp_actionscheduler_groups'     => 'group_id',
    'wp_actionscheduler_logs'       => 'log_id',
];

$fixed = 0; $errors = 0; $skipped = 0;

foreach ($tables as $table => $id_col) {
    echo "--- $table ---\n";
    
    // Check if table exists
    $exists = $mysqli->query("SHOW TABLES LIKE '$table'");
    if (!$exists || $exists->num_rows === 0) {
        echo "  Table does not exist, skipping\n\n";
        $skipped++;
        continue;
    }
    $exists->free();
    
    // Get current structure using SHOW CREATE TABLE
    $create_res = $mysqli->query("SHOW CREATE TABLE `$table`");
    if (!$create_res) {
        echo "  ❌ Cannot read structure: " . $mysqli->error . "\n\n";
        $errors++;
        continue;
    }
    $create_row = $create_res->fetch_assoc();
    $create_sql = $create_row['Create Table'];
    $create_res->free();
    
    // Check if AUTO_INCREMENT already exists
    if (strpos($create_sql, 'AUTO_INCREMENT') !== false) {
        echo "  ✅ Already has AUTO_INCREMENT\n\n";
        $skipped++;
        continue;
    }
    
    echo "  Missing AUTO_INCREMENT detected\n";
    
    // Special handling for wp_options (often has duplicates/negative IDs)
    if ($table === 'wp_options') {
        echo "  Using special wp_options repair...\n";
        
        // Check for duplicates
        $dup = $mysqli->query("SELECT option_id, COUNT(*) as c FROM wp_options GROUP BY option_id HAVING c > 1");
        $has_dups = ($dup && $dup->num_rows > 0);
        if ($dup) $dup->free();
        
        if ($has_dups || $mysqli->query("SELECT 1 FROM wp_options WHERE option_id <= 0 LIMIT 1")->num_rows > 0) {
            echo "  Found duplicates or invalid IDs, rebuilding table...\n";
            
            $mysqli->query("DROP TABLE IF EXISTS wp_options_new");
            $create = "CREATE TABLE wp_options_new (
              option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              option_name varchar(191) NOT NULL DEFAULT '',
              option_value longtext NOT NULL,
              autoload varchar(20) NOT NULL DEFAULT 'yes',
              PRIMARY KEY (option_id),
              UNIQUE KEY option_name (option_name),
              KEY autoload (autoload)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";
            
            if (!$mysqli->query($create)) {
                echo "  ❌ Create failed: " . $mysqli->error . "\n\n";
                $errors++;
                continue;
            }
            
            // Copy valid rows
            $mysqli->query("INSERT IGNORE INTO wp_options_new (option_id, option_name, option_value, autoload)
                SELECT option_id, option_name, option_value, autoload FROM wp_options WHERE option_id > 0");
            
            // Copy invalid rows with new IDs
            $mysqli->query("INSERT INTO wp_options_new (option_name, option_value, autoload)
                SELECT option_name, option_value, autoload FROM wp_options WHERE option_id <= 0");
            
            $mysqli->query("DROP TABLE wp_options");
            $mysqli->query("RENAME TABLE wp_options_new TO wp_options");
            echo "  ✅ wp_options rebuilt with AUTO_INCREMENT\n\n";
            $fixed++;
            continue;
        }
    }
    
    // For other tables: check for duplicates in ID column
    $dup_check = $mysqli->query("SELECT `$id_col`, COUNT(*) as c FROM `$table` GROUP BY `$id_col` HAVING c > 1 LIMIT 1");
    $has_dups = ($dup_check && $dup_check->num_rows > 0);
    if ($dup_check) $dup_check->free();
    
    if ($has_dups) {
        echo "  Found duplicate IDs, removing duplicates...\n";
        // Keep only the first occurrence of each ID
        $mysqli->query("CREATE TABLE `{$table}_temp` LIKE `$table`");
        $mysqli->query("INSERT INTO `{$table}_temp` SELECT * FROM `$table` GROUP BY `$id_col`");
        $mysqli->query("DROP TABLE `$table`");
        $mysqli->query("RENAME TABLE `{$table}_temp` TO `$table`");
        echo "  Duplicates removed\n";
    }
    
    // Get max ID
    $max_res = $mysqli->query("SELECT MAX(`$id_col`) as max_id FROM `$table`");
    $max_id = 0;
    if ($max_res) {
        $row = $max_res->fetch_assoc();
        $max_id = (int)($row['max_id'] ?? 0);
        $max_res->free();
    }
    $next_ai = $max_id + 1;
    echo "  Max ID: $max_id, Next AI: $next_ai\n";
    
    // Get column type
    $col_res = $mysqli->query("SHOW COLUMNS FROM `$table` WHERE Field = '$id_col'");
    $col_type = 'bigint(20) unsigned';
    if ($col_res && $col_res->num_rows > 0) {
        $col_row = $col_res->fetch_assoc();
        $col_type = $col_row['Type'];
        $col_res->free();
    }
    
    // Check if PRIMARY KEY exists
    $pk_res = $mysqli->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $has_pk = ($pk_res && $pk_res->num_rows > 0);
    if ($pk_res) $pk_res->free();
    
    // Build ALTER statement
    if ($has_pk) {
        $alter = "ALTER TABLE `$table` MODIFY `$id_col` $col_type NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=$next_ai";
    } else {
        $alter = "ALTER TABLE `$table` ADD PRIMARY KEY (`$id_col`), MODIFY `$id_col` $col_type NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=$next_ai";
    }
    
    echo "  Running: $alter\n";
    if ($mysqli->query($alter)) {
        echo "  ✅ Fixed!\n\n";
        $fixed++;
    } else {
        echo "  ❌ Failed: " . $mysqli->error . "\n";
        // Try fallback: recreate table
        echo "  Trying fallback (recreate table)...\n";
        
        // Get current data
        $data = $mysqli->query("SELECT * FROM `$table`");
        $rows = [];
        if ($data) {
            while ($r = $data->fetch_assoc()) $rows[] = $r;
            $data->free();
        }
        
        // Modify CREATE SQL to add AUTO_INCREMENT
        $new_create = preg_replace(
            '/(`' . $id_col . '`\s+' . preg_quote($col_type, '/') . ')(\s+NOT\s+NULL)?/i',
            '$1 NOT NULL AUTO_INCREMENT',
            $create_sql
        );
        
        // Ensure PRIMARY KEY exists
        if (strpos($new_create, 'PRIMARY KEY') === false) {
            $new_create = preg_replace('/\)\s*ENGINE=/', ", PRIMARY KEY (`$id_col`)) ENGINE=", $new_create);
        }
        
        // Drop and recreate
        $mysqli->query("DROP TABLE IF EXISTS `{$table}_backup`");
        $mysqli->query("RENAME TABLE `$table` TO `{$table}_backup`");
        
        if ($mysqli->query($new_create)) {
            // Re-insert data without the ID column to let auto-increment handle it
            if (!empty($rows)) {
                $cols = array_keys($rows[0]);
                $col_list = implode('`, `', $cols);
                $stmt = $mysqli->prepare("INSERT INTO `$table` (`$col_list`) VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")");
                foreach ($rows as $row) {
                    $vals = array_values($row);
                    $types = '';
                    foreach ($vals as $v) {
                        $types .= is_int($v) ? 'i' : (is_double($v) ? 'd' : 's');
                    }
                    $stmt->bind_param($types, ...$vals);
                    $stmt->execute();
                }
                $stmt->close();
            }
            $mysqli->query("DROP TABLE `{$table}_backup`");
            $mysqli->query("ALTER TABLE `$table` AUTO_INCREMENT=$next_ai");
            echo "  ✅ Fixed via table recreation!\n\n";
            $fixed++;
        } else {
            echo "  ❌ Fallback also failed: " . $mysqli->error . "\n";
            // Restore backup
            $mysqli->query("DROP TABLE IF EXISTS `$table`");
            $mysqli->query("RENAME TABLE `{$table}_backup` TO `$table`");
            echo "  Original table restored.\n\n";
            $errors++;
        }
    }
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

echo "=== Summary ===\n";
echo "Fixed:   $fixed\n";
echo "Skipped: $skipped\n";
echo "Errors:  $errors\n";
echo "\n✅ Done!\n";
echo "⚠️ DELETE THIS FILE NOW!\n";

$mysqli->close();
