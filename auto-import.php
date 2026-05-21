<?php
/**
 * Auto-Import SQL Chunks
 * Upload this file + all part_*.sql chunks to your server, then visit in browser.
 * Each chunk is imported via AJAX with a 3-second cooldown between requests.
 * Can resume if interrupted.
 * 
 * SECURITY: DELETE THIS FILE IMMEDIATELY AFTER IMPORT COMPLETES!
 */

// Database credentials (same as wp-config.php)
$db_host = 'localhost';
$db_user = 'myembdesigns_emb';
$db_pass = '9;4P#9oCZQm1';
$db_name = 'myembdesigns_emb';

$chunks_dir = __DIR__ . '/sql_chunks';
$progress_file = __DIR__ . '/import-progress.json';
$log_file = __DIR__ . '/import-log.txt';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'status') {
        $progress = file_exists($progress_file) ? json_decode(file_get_contents($progress_file), true) : ['last_chunk' => 0, 'errors' => []];
        $chunks = glob($chunks_dir . '/part_*.sql');
        sort($chunks);
        echo json_encode([
            'total_chunks' => count($chunks),
            'last_chunk' => $progress['last_chunk'] ?? 0,
            'next_chunk' => ($progress['last_chunk'] ?? 0) + 1,
            'percent' => count($chunks) > 0 ? round((($progress['last_chunk'] ?? 0) / count($chunks)) * 100, 1) : 0,
            'complete' => ($progress['last_chunk'] ?? 0) >= count($chunks) && count($chunks) > 0,
            'errors' => $progress['errors'] ?? []
        ]);
        exit;
    }
    
    if ($_GET['action'] === 'import' && isset($_GET['chunk'])) {
        $chunk_num = (int) $_GET['chunk'];
        $chunk_file = sprintf('%s/part_%03d.sql', $chunks_dir, $chunk_num);
        
        if (!file_exists($chunk_file)) {
            echo json_encode(['success' => false, 'error' => "Chunk file not found: part_$chunk_num.sql"]);
            exit;
        }
        
        $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($mysqli->connect_error) {
            echo json_encode(['success' => false, 'error' => 'DB connect failed: ' . $mysqli->connect_error]);
            exit;
        }
        
        $sql = file_get_contents($chunk_file);
        $queries = 0;
        $errors = [];
        
        // Split into individual queries
        $lines = explode("\n", $sql);
        $buffer = '';
        $in_string = false;
        $string_char = '';
        
        foreach ($lines as $line) {
            $len = strlen($line);
            for ($i = 0; $i < $len; $i++) {
                $char = $line[$i];
                
                if (!$in_string && ($char === '"' || $char === "'" || $char === '`')) {
                    $in_string = true;
                    $string_char = $char;
                } elseif ($in_string && $char === $string_char) {
                    $backslashes = 0;
                    $j = strlen($buffer) - 1;
                    while ($j >= 0 && $buffer[$j] === '\\') {
                        $backslashes++;
                        $j--;
                    }
                    if ($backslashes % 2 === 0) {
                        $in_string = false;
                    }
                }
                
                $buffer .= $char;
                
                if (!$in_string && $char === ';') {
                    $query = trim($buffer);
                    if (!empty($query)) {
                        if (!$mysqli->query($query)) {
                            $err = $mysqli->error;
                            if (strpos($err, 'Duplicate entry') === false && 
                                strpos($err, 'already exists') === false) {
                                $errors[] = substr($err, 0, 100);
                            }
                        }
                        $queries++;
                    }
                    $buffer = '';
                }
            }
            $buffer .= "\n";
        }
        
        // Execute remaining
        $query = trim($buffer);
        if (!empty($query)) {
            if (!$mysqli->query($query)) {
                $err = $mysqli->error;
                if (strpos($err, 'Duplicate entry') === false && 
                    strpos($err, 'already exists') === false) {
                    $errors[] = substr($err, 0, 100);
                }
            }
            $queries++;
        }
        
        $mysqli->close();
        
        // Save progress
        $progress = file_exists($progress_file) ? json_decode(file_get_contents($progress_file), true) : ['last_chunk' => 0, 'errors' => []];
        $progress['last_chunk'] = $chunk_num;
        if (!empty($errors)) {
            $progress['errors'] = array_merge($progress['errors'] ?? [], array_slice($errors, 0, 5));
        }
        file_put_contents($progress_file, json_encode($progress));
        
        // Log
        $log = date('Y-m-d H:i:s') . " | Chunk $chunk_num | Queries: $queries | Errors: " . count($errors) . "\n";
        file_put_contents($log_file, $log, FILE_APPEND);
        
        echo json_encode([
            'success' => true,
            'chunk' => $chunk_num,
            'queries' => $queries,
            'errors' => array_slice($errors, 0, 3),
            'has_errors' => !empty($errors)
        ]);
        exit;
    }
    
    if ($_GET['action'] === 'reset') {
        if (file_exists($progress_file)) unlink($progress_file);
        if (file_exists($log_file)) unlink($log_file);
        echo json_encode(['success' => true, 'message' => 'Progress reset']);
        exit;
    }
}

$chunks = glob($chunks_dir . '/part_*.sql');
sort($chunks);
$total_chunks = count($chunks);
$progress = file_exists($progress_file) ? json_decode(file_get_contents($progress_file), true) : ['last_chunk' => 0, 'errors' => []];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auto-Import SQL Chunks</title>
    <style>
        * { box-sizing: border-box; font-family: system-ui, sans-serif; }
        body { max-width: 700px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .box { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #333; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #856404; }
        .danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .progress-bar { width: 100%; height: 30px; background: #e9ecef; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #28a745, #34ce57); width: 0%; transition: width 0.5s ease; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 14px; }
        .btn { padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; margin-right: 10px; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .log { background: #212529; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; max-height: 300px; overflow-y: auto; margin-top: 20px; }
        .log .success { color: #7ee787; }
        .log .error { color: #f85149; }
        .log .info-line { color: #79c0ff; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-box .number { font-size: 28px; font-weight: bold; color: #007bff; }
        .stat-box .label { font-size: 12px; color: #666; margin-top: 5px; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🗄️ Auto-Import SQL Chunks</h1>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> This file contains database credentials and allows SQL execution.<br>
            <strong>DELETE this file immediately after the import completes!</strong>
        </div>
        
        <?php if ($total_chunks === 0): ?>
            <div class="danger">
                <strong>❌ No chunk files found!</strong><br>
                Please upload all <code>part_*.sql</code> files to the <code>sql_chunks/</code> folder first.
            </div>
        <?php else: ?>
            <div class="info">
                Found <strong><?php echo $total_chunks; ?></strong> chunk files in <code>sql_chunks/</code><br>
                Last imported: <strong id="last-chunk-display"><?php echo $progress['last_chunk'] ?? 0; ?></strong> / <?php echo $total_chunks; ?>
            </div>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="number" id="stat-total"><?php echo $total_chunks; ?></div>
                    <div class="label">Total Chunks</div>
                </div>
                <div class="stat-box">
                    <div class="number" id="stat-done"><?php echo $progress['last_chunk'] ?? 0; ?></div>
                    <div class="label">Completed</div>
                </div>
                <div class="stat-box">
                    <div class="number" id="stat-remaining"><?php echo $total_chunks - ($progress['last_chunk'] ?? 0); ?></div>
                    <div class="label">Remaining</div>
                </div>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill">0%</div>
            </div>
            
            <div>
                <button class="btn btn-primary" id="btn-start" onclick="startImport()">▶️ Start Import</button>
                <button class="btn btn-danger" id="btn-stop" onclick="stopImport()" disabled>⏹️ Stop</button>
                <button class="btn btn-danger" onclick="resetProgress()" style="float:right;">🔄 Reset</button>
            </div>
            
            <div class="log" id="log">
                <div class="info-line">Ready to import. Click "Start Import" to begin.</div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        let isRunning = false;
        let shouldStop = false;
        let delay = 3000; // 3 seconds between chunks
        
        function log(msg, type = 'info') {
            const logEl = document.getElementById('log');
            const div = document.createElement('div');
            div.className = type;
            div.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
            logEl.appendChild(div);
            logEl.scrollTop = logEl.scrollHeight;
        }
        
        function updateProgress(done, total) {
            const percent = total > 0 ? Math.round((done / total) * 100) : 0;
            document.getElementById('progress-fill').style.width = percent + '%';
            document.getElementById('progress-fill').textContent = percent + '%';
            document.getElementById('stat-done').textContent = done;
            document.getElementById('stat-remaining').textContent = total - done;
            document.getElementById('last-chunk-display').textContent = done;
        }
        
        async function importChunk(chunkNum) {
            try {
                log('Importing chunk ' + chunkNum + '...', 'info-line');
                const response = await fetch('?action=import&chunk=' + chunkNum);
                const data = await response.json();
                
                if (data.success) {
                    log('✅ Chunk ' + chunkNum + ' done (' + data.queries + ' queries)', 'success');
                    if (data.has_errors) {
                        data.errors.forEach(e => log('⚠️ ' + e, 'error'));
                    }
                    return true;
                } else {
                    log('❌ Chunk ' + chunkNum + ' failed: ' + data.error, 'error');
                    return false;
                }
            } catch (e) {
                log('❌ Network error on chunk ' + chunkNum + ': ' + e.message, 'error');
                return false;
            }
        }
        
        async function startImport() {
            if (isRunning) return;
            isRunning = true;
            shouldStop = false;
            document.getElementById('btn-start').disabled = true;
            document.getElementById('btn-stop').disabled = false;
            
            log('=== Starting import ===', 'info-line');
            
            // Get status first
            const statusRes = await fetch('?action=status');
            const status = await statusRes.json();
            
            if (status.complete) {
                log('Import already complete!', 'success');
                updateProgress(status.total_chunks, status.total_chunks);
                isRunning = false;
                document.getElementById('btn-start').disabled = false;
                document.getElementById('btn-stop').disabled = true;
                return;
            }
            
            let current = status.next_chunk;
            const total = status.total_chunks;
            
            while (current <= total && !shouldStop) {
                const success = await importChunk(current);
                updateProgress(current, total);
                
                if (!success) {
                    log('Waiting 5s before retry...', 'info-line');
                    await new Promise(r => setTimeout(r, 5000));
                    // Retry same chunk
                    continue;
                }
                
                current++;
                
                if (current <= total && !shouldStop) {
                    log('Cooling down for ' + (delay/1000) + 's...', 'info-line');
                    await new Promise(r => setTimeout(r, delay));
                }
            }
            
            if (shouldStop) {
                log('=== Import stopped by user ===', 'info-line');
            } else {
                log('=== Import complete! ===', 'success');
                log('DELETE THIS FILE NOW FOR SECURITY!', 'error');
            }
            
            isRunning = false;
            document.getElementById('btn-start').disabled = false;
            document.getElementById('btn-stop').disabled = true;
        }
        
        function stopImport() {
            shouldStop = true;
            log('Stopping after current chunk...', 'info-line');
            document.getElementById('btn-stop').disabled = true;
        }
        
        async function resetProgress() {
            if (!confirm('Reset progress? This will start from chunk 1 again.')) return;
            await fetch('?action=reset');
            location.reload();
        }
        
        // Update progress on load
        fetch('?action=status').then(r => r.json()).then(s => {
            updateProgress(s.last_chunk, s.total_chunks);
        });
    </script>
</body>
</html>
