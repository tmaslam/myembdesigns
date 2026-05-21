<?php
/**
 * Clean WordPress SQL export for live import.
 * - Replaces localhost URLs with live URLs
 * - Fixes local file paths
 * - Adds DROP TABLE IF EXISTS before CREATE TABLE
 */

$inputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb (6).sql';
$outputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_CLEANED.sql';

if (!file_exists($inputFile)) {
    die("ERROR: Input file not found: $inputFile\n");
}

$inputSize = filesize($inputFile);
echo "Reading: $inputFile\n";
echo "Output:  $outputFile\n";
echo "Size:    " . round($inputSize / (1024*1024), 1) . " MB\n\n";

// Replacement rules
$replacements = [
    // Local URLs -> Live URLs
    'http://localhost/myemb' => 'https://www.myembdesigns.com',
    'http://localhost\/myemb' => 'https:\/\/www.myembdesigns.com',
    'https://localhost/myemb' => 'https://www.myembdesigns.com',
    'https://localhost\/myemb' => 'https:\/\/www.myembdesigns.com',
    
    // Local paths (Windows -> Linux)
    'C:/xampp/htdocs/myemb' => '/home/myembdesigns/public_html',
    'C:\\xampp\\htdocs\\myemb' => '/home/myembdesigns/public_html',
    
    // Mixed-case domain fixes
    'MyEmbDesigns.com' => 'myembdesigns.com',
    'Myembdesigns.com' => 'myembdesigns.com',
    'MyembDesigns.com' => 'myembdesigns.com',
    'MYEMBDESIGNS.com' => 'myembdesigns.com',
];

$counts = array_fill_keys(array_keys($replacements), 0);
$linesProcessed = 0;
$tablesSeen = [];

$fin = fopen($inputFile, 'r');
if (!$fin) {
    die("ERROR: Cannot open input file\n");
}

$fout = fopen($outputFile, 'w');
if (!$fout) {
    die("ERROR: Cannot create output file\n");
}

while (($line = fgets($fin)) !== false) {
    $linesProcessed++;
    
    // Add DROP TABLE before CREATE TABLE
    if (preg_match('/CREATE TABLE\s+`(\w+)`/i', $line, $matches)) {
        $tableName = $matches[1];
        if (!in_array($tableName, $tablesSeen)) {
            $tablesSeen[] = $tableName;
            fwrite($fout, "DROP TABLE IF EXISTS `$tableName`;\n");
        }
    }
    
    // Apply replacements
    foreach ($replacements as $search => $replace) {
        if (strpos($line, $search) !== false) {
            $newLine = str_replace($search, $replace, $line);
            if ($newLine !== $line) {
                $counts[$search]++;
                $line = $newLine;
            }
        }
    }
    
    fwrite($fout, $line);
    
    // Progress every 100k lines
    if ($linesProcessed % 100000 === 0) {
        echo "  Processed " . number_format($linesProcessed) . " lines...\n";
    }
}

fclose($fin);
fclose($fout);

$outputSize = filesize($outputFile);

echo "\n✅ Done! Processed " . number_format($linesProcessed) . " lines.\n";
echo "Output size: " . round($outputSize / (1024*1024), 1) . " MB\n\n";

echo "Replacements made:\n";
foreach ($counts as $pattern => $count) {
    if ($count > 0) {
        printf("  %6s x %s\n", number_format($count), substr($pattern, 0, 50));
    }
}

echo "\nTables with DROP TABLE added: " . count($tablesSeen) . "\n";
echo "\nCleaned file saved to:\n  $outputFile\n";
