<?php
/**
 * Fix AUTO_INCREMENT v3 - Automatically detects ALL tables that need it.
 * Scans entire database, finds ID columns, fixes everything.
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
echo "=== Fix AUTO_INCREMENT v3 - ALL TABLES ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}

echo "Connected to: $db\n\n";
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

// Get ALL tables
$res = $mysqli->query("SHOW TABLES");
$all_tables = [];
while ($r = $res->fetch_array()) {
    $all_tables[] = $r[0];
}
$res->free();

echo "Found " . count($all_tables) . " tables to check.\n\n";

$fixed = 0; $skipped = 0; $errors = 0;

foreach ($all_tables as $table) {
    echo "--- Table: $table ---\n";
    
    // Get CREATE TABLE sql
    $create_res = $mysqli->query("SHOW CREATE TABLE `$table`");
    if (!$create_res) {
        echo "  ❌ Cannot read structure\n\n";
        $errors++;
        continue;
    }
    $create_row = $create_res->fetch_assoc();
    $create_sql = $create_row['Create Table'];
    $create_res->free();
    
    // Check if already has AUTO_INCREMENT
    if (strpos($create_sql, 'AUTO_INCREMENT') !== false) {
        echo "  ✅ Already has AUTO_INCREMENT\n\n";
        $skipped++;
        continue;
    }
    
    // Find the ID column - look for columns that are:
    // 1. Named exactly 'id' or 'ID' or ending with '_id'
    // 2. First column (common for WordPress)
    // 3. Type is bigint/int
    $cols_res = $mysqli->query("SHOW COLUMNS FROM `$table`");
    $id_col = null;
    $id_type = 'bigint(20) unsigned';
    $first_col = null;
    $first_type = null;
    
    while ($col = $cols_res->fetch_assoc()) {
        if ($first_col === null) {
            $first_col = $col['Field'];
            $first_type = $col['Type'];
        }
        
        $field = $col['Field'];
        $type = strtolower($col['Type']);
        
        // Priority matching for ID columns
        if ($field === 'id' || $field === 'ID') {
            $id_col = $field;
            $id_type = $col['Type'];
            break; // Exact match, highest priority
        }
        if (preg_match('/_id$/', $field) && (strpos($type, 'int') !== false || strpos($type, 'bigint') !== false)) {
            $id_col = $field;
            $id_type = $col['Type'];
            // Don't break - keep looking for exact 'id' match
        }
        if (($field === 'term_taxonomy_id' || $field === 'term_id' || $field === 'comment_ID') && $id_col === null) {
            $id_col = $field;
            $id_type = $col['Type'];
        }
    }
    $cols_res->free();
    
    // If no _id column found, check if first column is a number type (might be ID)
    if (!$id_col && $first_col) {
        $ftype = strtolower($first_type);
        if (strpos($ftype, 'int') !== false || strpos($ftype, 'bigint') !== false) {
            $id_col = $first_col;
            $id_type = $first_type;
        }
    }
    
    if (!$id_col) {
        echo "  ⚠️ No ID column found, skipping\n\n";
        $skipped++;
        continue;
    }
    
    echo "  ID column detected: $id_col ($id_type)\n";
    
    // Special handling for wp_options
    if ($table === 'wp_options') {
        echo "  Using wp_options special repair...\n";
        fix_wp_options($mysqli);
        $fixed++;
        continue;
    }
    
    // Check for duplicate IDs
    $dup_res = $mysqli->query("SELECT `$id_col`, COUNT(*) as c FROM `$table` GROUP BY `$id_col` HAVING c > 1 LIMIT 1");
    $has_dups = ($dup_res && $dup_res->num_rows > 0);
    if ($dup_res) $dup_res->free();
    
    if ($has_dups) {
        echo "  Found duplicate $id_col values, removing...\n";
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
    $next_ai = max(1, $max_id + 1);
    echo "  Max ID: $max_id -> Next AI: $next_ai\n";
    
    // Check if PK exists
    $pk_res = $mysqli->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $has_pk = ($pk_res && $pk_res->num_rows > 0);
    if ($pk_res) $pk_res->free();
    
    // Try ALTER first
    if ($has_pk) {
        $alter = "ALTER TABLE `$table` MODIFY `$id_col` $id_type NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=$next_ai";
    } else {
        $alter = "ALTER TABLE `$table` ADD PRIMARY KEY (`$id_col`), MODIFY `$id_col` $id_type NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=$next_ai";
    }
    
    echo "  Trying ALTER TABLE...\n";
    if ($mysqli->query($alter)) {
        echo "  ✅ Fixed with ALTER!\n\n";
        $fixed++;
        continue;
    }
    
    echo "  ❌ ALTER failed: " . $mysqli->error . "\n";
    echo "  Trying table recreation fallback...\n";
    
    // Fallback: recreate table
    $backup_name = "{$table}_backup_" . time();
    
    // Get all data
    $data_res = $mysqli->query("SELECT * FROM `$table`");
    $rows = [];
    $columns = [];
    if ($data_res) {
        while ($r = $data_res->fetch_assoc()) $rows[] = $r;
        $data_res->free();
    }
    
    // Get column names
    $col_res = $mysqli->query("SHOW COLUMNS FROM `$table`");
    while ($c = $col_res->fetch_assoc()) {
        $columns[] = $c['Field'];
    }
    $col_res->free();
    
    // Modify CREATE SQL
    $new_create = preg_replace(
        '/(`' . preg_quote($id_col, '/') . '`\s+' . preg_quote($id_type, '/') . ')(\s+NOT\s+NULL)?/i',
        '$1 NOT NULL AUTO_INCREMENT',
        $create_sql
    );
    
    if (strpos($new_create, 'PRIMARY KEY') === false) {
        $new_create = preg_replace('/\)\s*ENGINE=/', ", PRIMARY KEY (`$id_col`)) ENGINE=", $new_create, 1);
    }
    
    // Backup and recreate
    $mysqli->query("DROP TABLE IF EXISTS `$backup_name`");
    if (!$mysqli->query("RENAME TABLE `$table` TO `$backup_name`")) {
        echo "  ❌ Cannot rename table: " . $mysqli->error . "\n\n";
        $errors++;
        continue;
    }
    
    if (!$mysqli->query($new_create)) {
        echo "  ❌ Cannot recreate table: " . $mysqli->error . "\n";
        $mysqli->query("RENAME TABLE `$backup_name` TO `$table`");
        echo "  Restored original.\n\n";
        $errors++;
        continue;
    }
    
    // Re-insert data
    if (!empty($rows)) {
        $col_list = implode('`, `', $columns);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $stmt = $mysqli->prepare("INSERT INTO `$table` (`$col_list`) VALUES ($placeholders)");
        
        foreach ($rows as $row) {
            $vals = [];
            $types = '';
            foreach ($columns as $col) {
                $v = $row[$col] ?? null;
                $vals[] = $v;
                if (is_null($v)) {
                    $types .= 's';
                } elseif (is_int($v)) {
                    $types .= 'i';
                } elseif (is_float($v)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$vals);
            if (!$stmt->execute()) {
                echo "  ⚠️ Insert error: " . $stmt->error . "\n";
            }
        }
        $stmt->close();
    }
    
    $mysqli->query("ALTER TABLE `$table` AUTO_INCREMENT=$next_ai");
    $mysqli->query("DROP TABLE `$backup_name`");
    
    echo "  ✅ Fixed via recreation!\n\n";
    $fixed++;
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

echo "=== SUMMARY ===\n";
echo "Tables checked: " . count($all_tables) . "\n";
echo "Fixed:          $fixed\n";
echo "Skipped (OK):   $skipped\n";
echo "Errors:         $errors\n";
echo "\n✅ Done!\n";
echo "⚠️ DELETE THIS FILE NOW!\n";

$mysqli->close();

// Helper function for wp_options
function fix_wp_options($mysqli) {
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
    
    $mysqli->query($create);
    $mysqli->query("INSERT IGNORE INTO wp_options_new (option_id, option_name, option_value, autoload)
        SELECT option_id, option_name, option_value, autoload FROM wp_options WHERE option_id > 0");
    $mysqli->query("INSERT INTO wp_options_new (option_name, option_value, autoload)
        SELECT option_name, option_value, autoload FROM wp_options WHERE option_id <= 0");
    $mysqli->query("DROP TABLE wp_options");
    $mysqli->query("RENAME TABLE wp_options_new TO wp_options");
    echo "  ✅ wp_options rebuilt\n\n";
}
