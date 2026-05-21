<?php
/**
 * Split SQL into exactly 5 chunks (~25MB each) for manual import.
 */

$inputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_READY.sql';
$outputDir = 'C:\Users\tmasl\Downloads\sql_chunks_5';
$numChunks = 5;

if (!file_exists($inputFile)) {
    die("ERROR: Input file not found: $inputFile\n");
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

foreach (glob("$outputDir/*.sql") as $old) {
    unlink($old);
}

$totalSize = filesize($inputFile);
$targetSize = ceil($totalSize / $numChunks);

echo "Total size: " . round($totalSize / (1024*1024), 1) . " MB\n";
echo "Target per chunk: ~" . round($targetSize / (1024*1024), 1) . " MB\n\n";

$handle = fopen($inputFile, 'r');
$chunkNum = 1;
$currentBuffer = '';
$currentSize = 0;

$header = "SET FOREIGN_KEY_CHECKS = 0;\nSET AUTOCOMMIT = 0;\nSTART TRANSACTION;\n\n";
$footer = "\n\nCOMMIT;\nSET FOREIGN_KEY_CHECKS = 1;\n";

while (!feof($handle) && $chunkNum <= $numChunks) {
    $readSize = min(8192, $targetSize - $currentSize);
    if ($readSize <= 0) $readSize = 8192;
    
    $chunk = fread($handle, $readSize);
    $currentBuffer .= $chunk;
    $currentSize += strlen($chunk);
    
    // If we've reached target size and are at end of a statement, write chunk
    if ($currentSize >= $targetSize && $chunkNum < $numChunks) {
        $lastSemicolon = strrpos($currentBuffer, ";\n");
        if ($lastSemicolon !== false) {
            $toWrite = substr($currentBuffer, 0, $lastSemicolon + 1);
            $currentBuffer = substr($currentBuffer, $lastSemicolon + 1);
            
            $filename = "$outputDir/part_" . sprintf("%02d", $chunkNum) . ".sql";
            file_put_contents($filename, $header . $toWrite . $footer);
            $size = round(strlen($toWrite) / (1024*1024), 1);
            echo "Created: $filename ({$size} MB)\n";
            
            $chunkNum++;
            $currentSize = strlen($currentBuffer);
        }
    }
}

// Write final chunk
if (!empty($currentBuffer)) {
    $filename = "$outputDir/part_" . sprintf("%02d", $chunkNum) . ".sql";
    file_put_contents($filename, $header . $currentBuffer . $footer);
    $size = round(strlen($currentBuffer) / (1024*1024), 1);
    echo "Created: $filename ({$size} MB)\n";
}

fclose($handle);

echo "\n✅ Done! Created $chunkNum chunks in:\n  $outputDir\n";
echo "\nImport order: part_01.sql → part_02.sql → part_03.sql → part_04.sql → part_05.sql\n";
