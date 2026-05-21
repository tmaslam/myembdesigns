<?php
/**
 * Second pass: Fix remaining JSON-escaped localhost URLs and old demo paths.
 */

$inputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_CLEANED.sql';
$outputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_FINAL.sql';

if (!file_exists($inputFile)) {
    die("ERROR: Input file not found: $inputFile\n");
}

$replacements = [
    // JSON-escaped localhost URLs
    'http:\\/\\/localhost\\/myemb' => 'https:\\/\\/www.myembdesigns.com',
    'https:\\/\\/localhost\\/myemb' => 'https:\\/\\/www.myembdesigns.com',
    
    // Old demo site references
    'http:\\/\\/localhost\\/Edigit\\/' => 'https:\\/\\/www.myembdesigns.com\\/',
    'http:\\/\\/localhost\\/~abbas\\/electro\\/' => 'https:\\/\\/www.myembdesigns.com\\/',
    
    // Any remaining plain localhost/myemb
    'localhost/myemb' => 'www.myembdesigns.com',
    'localhost/Edigit' => 'www.myembdesigns.com',
    'localhost/~abbas/electro' => 'www.myembdesigns.com',
];

$counts = array_fill_keys(array_keys($replacements), 0);
$linesProcessed = 0;

$fin = fopen($inputFile, 'r');
$fout = fopen($outputFile, 'w');

while (($line = fgets($fin)) !== false) {
    $linesProcessed++;
    
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
    
    if ($linesProcessed % 100000 === 0) {
        echo "  Processed " . number_format($linesProcessed) . " lines...\n";
    }
}

fclose($fin);
fclose($fout);

echo "\n✅ Second pass complete! Processed " . number_format($linesProcessed) . " lines.\n\n";

echo "Replacements made:\n";
foreach ($counts as $pattern => $count) {
    if ($count > 0) {
        printf("  %6s x %s\n", number_format($count), substr($pattern, 0, 50));
    }
}

echo "\nFinal file saved to:\n  $outputFile\n";

// Verify remaining localhost references
$remaining = shell_exec('grep -o -i "localhost" "' . $outputFile . '" | wc -l');
echo "\nRemaining 'localhost' references: " . trim($remaining) . "\n";
