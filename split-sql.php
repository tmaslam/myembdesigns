<?php
/**
 * Split large SQL file into smaller chunks for reliable import.
 * Produces files like: part_001.sql, part_002.sql, etc.
 */

$inputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_READY.sql';
$outputDir = 'C:\Users\tmasl\Downloads\sql_chunks';
$maxChunkSize = 2 * 1024 * 1024; // 2 MB per chunk

if (!file_exists($inputFile)) {
    die("ERROR: Input file not found: $inputFile\n");
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// Clean old chunks
foreach (glob("$outputDir/part_*.sql") as $old) {
    unlink($old);
}

$partNum = 1;
$currentSize = 0;
$currentBuffer = '';
$totalLines = 0;

$header = "SET FOREIGN_KEY_CHECKS = 0;\nSET AUTOCOMMIT = 0;\nSTART TRANSACTION;\n\n";
$footer = "\n\nCOMMIT;\nSET FOREIGN_KEY_CHECKS = 1;\n";

$handle = fopen($inputFile, 'r');

function writeChunk($buffer, $partNum, $outputDir, $header, $footer) {
    $filename = sprintf("%s/part_%03d.sql", $outputDir, $partNum);
    file_put_contents($filename, $header . $buffer . $footer);
    return $filename;
}

while (($line = fgets($handle)) !== false) {
    $totalLines++;
    $lineSize = strlen($line);
    
    // Start new chunk if current would exceed limit
    if ($currentSize > 0 && $currentSize + $lineSize > $maxChunkSize && preg_match('/;\s*$/', $line)) {
        $filename = writeChunk($currentBuffer, $partNum, $outputDir, $header, $footer);
        echo "Created: $filename (" . round($currentSize / 1024, 1) . " KB)\n";
        
        $partNum++;
        $currentBuffer = '';
        $currentSize = 0;
    }
    
    $currentBuffer .= $line;
    $currentSize += $lineSize;
}

// Write final chunk
if ($currentSize > 0) {
    $filename = writeChunk($currentBuffer, $partNum, $outputDir, $header, $footer);
    echo "Created: $filename (" . round($currentSize / 1024, 1) . " KB)\n";
}

fclose($handle);

echo "\n✅ Done! Created $partNum chunks in:\n  $outputDir\n";
echo "\nImport order: part_001.sql first, then part_002.sql, etc.\n";
