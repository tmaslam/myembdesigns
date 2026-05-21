<?php
/**
 * Split SQL into tiny chunks (~100KB) for overloaded servers.
 */

$inputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_READY.sql';
$outputDir = 'C:\Users\tmasl\Downloads\sql_chunks_small';
$maxChunkSize = 100 * 1024; // 100 KB per chunk

if (!file_exists($inputFile)) {
    die("ERROR: Input file not found: $inputFile\n");
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

foreach (glob("$outputDir/part_*.sql") as $old) {
    unlink($old);
}

$partNum = 1;
$currentSize = 0;
$currentBuffer = '';

$header = "SET FOREIGN_KEY_CHECKS = 0;\nSET AUTOCOMMIT = 0;\nSTART TRANSACTION;\n\n";
$footer = "\n\nCOMMIT;\nSET FOREIGN_KEY_CHECKS = 1;\n";

$handle = fopen($inputFile, 'r');

function writeChunk($buffer, $partNum, $outputDir, $header, $footer) {
    $filename = sprintf("%s/part_%03d.sql", $outputDir, $partNum);
    file_put_contents($filename, $header . $buffer . $footer);
    return $filename;
}

while (!feof($handle)) {
    $chunk = fread($handle, $maxChunkSize);
    $currentBuffer .= $chunk;
    $currentSize += strlen($chunk);
    
    // Find last complete query in buffer
    $lastSemicolon = strrpos($currentBuffer, ";\n");
    if ($lastSemicolon !== false) {
        $toWrite = substr($currentBuffer, 0, $lastSemicolon + 1);
        $currentBuffer = substr($currentBuffer, $lastSemicolon + 1);
        
        $filename = writeChunk($toWrite, $partNum, $outputDir, $header, $footer);
        $size = round(strlen($toWrite) / 1024, 1);
        echo "Created: $filename ({$size} KB)\n";
        
        $partNum++;
        $currentSize = strlen($currentBuffer);
    }
}

// Write final chunk
if (!empty($currentBuffer)) {
    $filename = writeChunk($currentBuffer, $partNum, $outputDir, $header, $footer);
    $size = round(strlen($currentBuffer) / 1024, 1);
    echo "Created: $filename ({$size} KB)\n";
}

fclose($handle);

echo "\n✅ Done! Created $partNum small chunks in:\n  $outputDir\n";
