<?php
/**
 * Auto-Import SQL Chunks v2 - For Overloaded Servers
 * Uses tiny chunks (~100KB) and multi_query for speed.
 * Shows raw server errors when JSON fails.
 * 
 * SECURITY: DELETE THIS FILE IMMEDIATELY AFTER IMPORT!
 */

$db_host = 'localhost';
$db_user = 'myembdesigns_emb';
$db_pass = '9;4P#9oCZQm1';
$db_name = 'myembdesigns_emb';

$chunks_dir = __DIR__ . '/sql_chunks_small';
$progress_file = __DIR__ . '/import-progress-v2.json';
$delay_seconds = 5;
$max_execution = 15; // seconds per chunk

set_time_limit($max_execution + 5);
ini_set('max_execution_time', $max_execution + 5);

// AJAX handler
if (isset($_GET['action'])) {
    // Use text/plain so errors are readable even if PHP crashes
    header('Content-Type: text/plain; charset=utf-8');
    
    if ($_GET['action'] === 'status') {
        $progress = file_exists($progress_file) ? json_decode(file_get_contents($progress_file), true) : ['last_chunk' => 0];
        $chunks = glob($chunks_dir . '/part_*.sql');
        sort($chunks);
        echo "OK\n";
        echo "total=" . count($chunks) . "\n";
        echo "last=" . ($progress['last_chunk'] ?? 0) . "\n";
        echo "next=" . (($progress['last_chunk'] ?? 0) + 1) . "\n";
        echo "percent=" . (count($chunks) > 0 ? round((($progress['last_chunk'] ?? 0) / count($chunks)) * 100, 1) : 0) . "\n";
        echo "done=" . (($progress['last_chunk'] ?? 0) >= count($chunks) ? '1' : '0') . "\n";
        exit;
    }
    
    if ($_GET['action'] === 'import' && isset($_GET['chunk'])) {
        $chunk_num = (int) $_GET['chunk'];
        $chunk_file = sprintf('%s/part_%03d.sql', $chunks_dir, $chunk_num);
        
        if (!file_exists($chunk_file)) {
            echo "ERROR: File not found: part_$chunk_num.sql\n";
            exit;
        }
        
        $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($mysqli->connect_error) {
            echo "ERROR: DB connect failed: " . $mysqli->connect_error . "\n";
            exit;
        }
        
        $sql = file_get_contents($chunk_file);
        
        // Use multi_query for speed
        if ($mysqli->multi_query($sql)) {
            // Flush results
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
            
            // Check for errors in the batch
            if ($mysqli->error) {
                $err = $mysqli->error;
                if (strpos($err, 'Duplicate') === false && strpos($err, 'already exists') === false) {
                    echo "WARN: " . substr($err, 0, 200) . "\n";
                }
            }
        } else {
            echo "ERROR: " . $mysqli->error . "\n";
            $mysqli->close();
            exit;
        }
        
        $mysqli->close();
        
        // Save progress
        $progress = file_exists($progress_file) ? json_decode(file_get_contents($progress_file), true) : ['last_chunk' => 0];
        $progress['last_chunk'] = $chunk_num;
        file_put_contents($progress_file, json_encode($progress));
        
        echo "OK\n";
        echo "chunk=$chunk_num\n";
        echo "size=" . round(filesize($chunk_file) / 1024, 1) . "KB\n";
        exit;
    }
    
    if ($_GET['action'] === 'reset') {
        if (file_exists($progress_file)) unlink($progress_file);
        echo "OK\nreset=1\n";
        exit;
    }
}

$chunks = glob($chunks_dir . '/part_*.sql');
sort($chunks);
$total_chunks = count($chunks);
$progress = file_exists($progress_file) ? json_decode(file_get_contents($progress_file), true) : ['last_chunk' => 0];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auto-Import v2</title>
    <style>
        * { box-sizing: border-box; font-family: system-ui, sans-serif; }
        body { max-width: 700px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .box { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .progress-bar { width: 100%; height: 30px; background: #e9ecef; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #28a745, #34ce57); width: 0%; transition: width 0.5s; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 14px; }
        .btn { padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; margin-right: 10px; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .log { background: #212529; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; margin-top: 20px; white-space: pre-wrap; }
        .log .ok { color: #7ee787; }
        .log .err { color: #f85149; }
        .log .warn { color: #ffa657; }
        .log .info { color: #79c0ff; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-box .num { font-size: 28px; font-weight: bold; color: #007bff; }
        .stat-box .lbl { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🗄️ Auto-Import v2 (Tiny Chunks)</h1>
        
        <div class="warning">
            <strong>⚠️ DELETE THIS FILE AFTER IMPORT!</strong><br>
            Contains database credentials. Each chunk is ~100KB to avoid timeouts.
        </div>
        
        <?php if ($total_chunks === 0): ?>
            <div class="danger">
                <strong>No chunks found!</strong> Upload <code>part_*.sql</code> files to <code>sql_chunks_small/</code>
            </div>
        <?php else: ?>
            <div class="info">
                Found <strong><?php echo $total_chunks; ?></strong> tiny chunks<br>
                Last done: <strong id="last-disp"><?php echo $progress['last_chunk'] ?? 0; ?></strong> / <?php echo $total_chunks; ?>
            </div>
            
            <div class="stats">
                <div class="stat-box"><div class="num" id="st-total"><?php echo $total_chunks; ?></div><div class="lbl">Total</div></div>
                <div class="stat-box"><div class="num" id="st-done"><?php echo $progress['last_chunk'] ?? 0; ?></div><div class="lbl">Done</div></div>
                <div class="stat-box"><div class="num" id="st-left"><?php echo $total_chunks - ($progress['last_chunk'] ?? 0); ?></div><div class="lbl">Left</div></div>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" id="pbar">0%</div>
            </div>
            
            <div>
                <button class="btn btn-primary" id="btn-go" onclick="start()">▶️ Start Import</button>
                <button class="btn btn-danger" id="btn-stop" onclick="stop()" disabled>⏹️ Stop</button>
                <button class="btn btn-danger" onclick="resetAll()" style="float:right;font-size:14px;">🔄 Reset</button>
            </div>
            
            <div class="log" id="log"></div>
        <?php endif; ?>
    </div>
    
    <script>
        let running = false, shouldStop = false;
        const delay = <?php echo $delay_seconds * 1000; ?>;
        
        function out(msg, cls) {
            const el = document.getElementById('log');
            const line = document.createElement('div');
            line.className = cls || 'info';
            line.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
            el.appendChild(line);
            el.scrollTop = el.scrollHeight;
        }
        
        function update(done, total) {
            const pct = total > 0 ? Math.round((done / total) * 100) : 0;
            document.getElementById('pbar').style.width = pct + '%';
            document.getElementById('pbar').textContent = pct + '%';
            document.getElementById('st-done').textContent = done;
            document.getElementById('st-left').textContent = total - done;
            document.getElementById('last-disp').textContent = done;
        }
        
        async function doChunk(n) {
            try {
                const res = await fetch('?action=import&chunk=' + n);
                const text = await res.text();
                
                if (!text.trim().startsWith('OK')) {
                    out('Chunk ' + n + ' server error:\n' + text.substring(0, 500), 'err');
                    return false;
                }
                
                out('✅ Chunk ' + n + ' imported', 'ok');
                return true;
            } catch (e) {
                out('Chunk ' + n + ' network error: ' + e.message, 'err');
                return false;
            }
        }
        
        async function start() {
            if (running) return;
            running = true; shouldStop = false;
            document.getElementById('btn-go').disabled = true;
            document.getElementById('btn-stop').disabled = false;
            out('=== Starting import ===', 'info');
            
            const statusRes = await fetch('?action=status');
            const statusText = await statusRes.text();
            const lines = statusText.split('\n');
            const total = parseInt(lines.find(l => l.startsWith('total='))?.split('=')[1] || 0);
            let current = parseInt(lines.find(l => l.startsWith('next='))?.split('=')[1] || 1);
            
            if (lines.find(l => l.startsWith('done=1'))) {
                out('Already complete!', 'ok');
                update(total, total);
                running = false;
                document.getElementById('btn-go').disabled = false;
                document.getElementById('btn-stop').disabled = true;
                return;
            }
            
            while (current <= total && !shouldStop) {
                const ok = await doChunk(current);
                update(current, total);
                
                if (!ok) {
                    out('Waiting 10s before retry...', 'warn');
                    await new Promise(r => setTimeout(r, 10000));
                    continue;
                }
                
                current++;
                if (current <= total && !shouldStop) {
                    out('Cooling down ' + (delay/1000) + 's...', 'info');
                    await new Promise(r => setTimeout(r, delay));
                }
            }
            
            if (shouldStop) out('Stopped by user', 'warn');
            else { out('=== IMPORT COMPLETE ===', 'ok'); out('DELETE THIS FILE NOW!', 'err'); }
            
            running = false;
            document.getElementById('btn-go').disabled = false;
            document.getElementById('btn-stop').disabled = true;
        }
        
        function stop() { shouldStop = true; out('Stopping...', 'warn'); }
        
        async function resetAll() {
            if (!confirm('Reset and start from chunk 1?')) return;
            await fetch('?action=reset');
            location.reload();
        }
        
        fetch('?action=status').then(r => r.text()).then(t => {
            const total = parseInt(t.split('\n').find(l => l.startsWith('total='))?.split('=')[1] || 0);
            const last = parseInt(t.split('\n').find(l => l.startsWith('last='))?.split('=')[1] || 0);
            update(last, total);
        });
    </script>
</body>
</html>
