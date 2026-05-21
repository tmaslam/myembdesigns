<?php
/**
 * Chunked Database Import Script
 * Upload this + myembdesigns_emb_READY.sql to your server root, visit in browser.
 */

set_time_limit(0);
ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db_host = 'localhost';
$db_user = 'myembdesigns_emb';
$db_pass = '9;4P#9oCZQm1';
$db_name = 'myembdesigns_emb';
$sql_file = 'myembdesigns_emb_READY.sql';
$chunk_size = 4096; // bytes to read at a time
$max_queries_per_request = 500; // queries to run before showing progress

header('Content-Type: text/plain');
echo "=== Chunked Database Import ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

if (!file_exists($sql_file)) {
    die("ERROR: SQL file not found: $sql_file\nUpload it to the same folder as this script.\n");
}

$file_size = filesize($sql_file);
echo "SQL file: $sql_file\n";
echo "Size: " . round($file_size / (1024*1024), 1) . " MB\n\n";

// Connect to database
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}
echo "Connected to database: $db_name\n\n";

// Check for progress file
$progress_file = 'import-progress.txt';
$offset = 0;
if (file_exists($progress_file)) {
    $offset = (int) file_get_contents($progress_file);
    echo "Resuming from byte offset: " . number_format($offset) . "\n";
}

$handle = fopen($sql_file, 'r');
if (!$handle) {
    die("ERROR: Cannot open SQL file\n");
}

// Seek to offset
if ($offset > 0) {
    fseek($handle, $offset);
}

$query_buffer = '';
$queries_executed = 0;
$total_queries = 0;
$in_string = false;
$string_char = '';
$start_time = microtime(true);

while (!feof($handle)) {
    $chunk = fread($handle, $chunk_size);
    $query_buffer .= $chunk;
    
    // Process complete queries in buffer
    $len = strlen($query_buffer);
    $query_start = 0;
    
    for ($i = 0; $i < $len; $i++) {
        $char = $query_buffer[$i];
        
        // Handle string literals
        if (!$in_string && ($char === '"' || $char === "'" || $char === '`')) {
            $in_string = true;
            $string_char = $char;
        } elseif ($in_string && $char === $string_char) {
            // Check if escaped
            $backslashes = 0;
            $j = $i - 1;
            while ($j >= 0 && $query_buffer[$j] === '\\') {
                $backslashes++;
                $j--;
            }
            if ($backslashes % 2 === 0) {
                $in_string = false;
            }
        }
        
        // Find end of query (semicolon not in string)
        if (!$in_string && $char === ';') {
            $query = substr($query_buffer, $query_start, $i - $query_start + 1);
            $query = trim($query);
            
            if (!empty($query)) {
                if (!$mysqli->query($query)) {
                    $error = $mysqli->error;
                    // Skip common non-fatal errors
                    if (strpos($error, 'Duplicate entry') === false && 
                        strpos($error, 'already exists') === false) {
                        echo "ERROR at query $total_queries: $error\n";
                        echo "Query: " . substr($query, 0, 200) . "...\n\n";
                    }
                }
                $queries_executed++;
                $total_queries++;
            }
            
            $query_start = $i + 1;
            
            // Save progress every N queries
            if ($queries_executed >= $max_queries_per_request) {
                $current_offset = $offset + $query_start;
                file_put_contents($progress_file, $current_offset);
                
                $percent = round(($current_offset / $file_size) * 100, 1);
                $elapsed = microtime(true) - $start_time;
                $rate = $current_offset / max($elapsed, 0.001);
                $remaining = ($file_size - $current_offset) / max($rate, 1);
                
                echo "Progress: {$percent}% | Queries: " . number_format($total_queries) . " | Offset: " . number_format($current_offset) . "\n";
                
                // Auto-refresh for next batch
                echo "\nReloading for next batch...\n";
                echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
                
                fclose($handle);
                $mysqli->close();
                exit;
            }
        }
    }
    
    // Keep incomplete query in buffer
    $query_buffer = substr($query_buffer, $query_start);
    $offset += $chunk_size;
}

// Execute any remaining query
$query = trim($query_buffer);
if (!empty($query)) {
    if (!$mysqli->query($query)) {
        echo "ERROR on final query: " . $mysqli->error . "\n";
    } else {
        $total_queries++;
    }
}

fclose($handle);

// Clean up progress file
if (file_exists($progress_file)) {
    unlink($progress_file);
}

$elapsed = round(microtime(true) - $start_time, 1);
echo "\n✅ Import complete!\n";
echo "Total queries executed: " . number_format($total_queries) . "\n";
echo "Time elapsed: {$elapsed}s\n";
echo "\nIMPORTANT: Delete this script and the SQL file immediately!\n";

$mysqli->close();
