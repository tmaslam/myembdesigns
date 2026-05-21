<?php
/**
 * Drop all tables in the database to start fresh.
 * Run this BEFORE importing chunks.
 * DELETE AFTER USE!
 */

$db_host = 'localhost';
$db_user = 'myembdesigns_emb';
$db_pass = '9;4P#9oCZQm1';
$db_name = 'myembdesigns_emb';

header('Content-Type: text/plain');
echo "=== Dropping all tables in $db_name ===\n\n";

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}

// Get all tables
$result = $mysqli->query("SHOW TABLES");
if (!$result) {
    die("Error: " . $mysqli->error . "\n");
}

$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}
$result->free();

echo "Found " . count($tables) . " tables to drop.\n\n";

// Disable foreign key checks
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

// Drop each table
$dropped = 0;
foreach ($tables as $table) {
    if ($mysqli->query("DROP TABLE IF EXISTS `$table`")) {
        echo "✅ Dropped: $table\n";
        $dropped++;
    } else {
        echo "❌ Failed: $table - " . $mysqli->error . "\n";
    }
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
$mysqli->close();

echo "\n=== Done! Dropped $dropped tables. ===\n";
echo "Database is now empty. You can start importing chunks.\n";
echo "\n⚠️ DELETE THIS FILE NOW!\n";
