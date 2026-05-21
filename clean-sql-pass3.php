<?php
/**
 * Third pass: Fix remaining double-backslash-escaped localhost URLs.
 * Uses chr() to avoid ALL escaping confusion.
 */

$inputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_FINAL.sql';
$outputFile = 'C:\Users\tmasl\Downloads\myembdesigns_emb_READY.sql';

if (!file_exists($inputFile)) {
    die("ERROR: Input file not found: $inputFile\n");
}

// Build patterns with exact hex bytes
$bs = chr(0x5c);  // backslash \
$sl = chr(0x2f);  // slash /

// http:\/\/localhost\/myemb  (two backslashes + slash)
$oldHttp  = 'http:'  . $bs.$bs.$sl . $bs.$bs.$sl . 'localhost' . $bs.$bs.$sl . 'myemb';
$oldHttps = 'https:' . $bs.$bs.$sl . $bs.$bs.$sl . 'localhost' . $bs.$bs.$sl . 'myemb';

// http:\/\/localhost\/~abbas\/electro
$oldAbbas = 'http:'  . $bs.$bs.$sl . $bs.$bs.$sl . 'localhost' . $bs.$bs.$sl . '~abbas' . $bs.$bs.$sl . 'electro';

// http:\/\/localhost\/Edigit
$oldEdigit = 'http:'  . $bs.$bs.$sl . $bs.$bs.$sl . 'localhost' . $bs.$bs.$sl . 'Edigit';

// https:\/\/www.myembdesigns.com\/  (replacement)
$newUrl = 'https:' . $bs.$bs.$sl . $bs.$bs.$sl . 'www.myembdesigns.com' . $bs.$bs.$sl;

$replacements = [
    $oldHttp    => $newUrl,
    $oldHttps   => $newUrl,
    $oldAbbas   => $newUrl,
    $oldEdigit  => $newUrl,
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

echo "\n✅ Third pass complete! Processed " . number_format($linesProcessed) . " lines.\n\n";

echo "Replacements made:\n";
$total = 0;
foreach ($counts as $pattern => $count) {
    if ($count > 0) {
        printf("  %6s x %s\n", number_format($count), substr($pattern, 0, 50));
        $total += $count;
    }
}
echo "\nTotal replacements: " . number_format($total) . "\n";

// Verify remaining localhost references
$remaining = shell_exec('grep -o -i "localhost" "' . $outputFile . '" | wc -l');
echo "Remaining 'localhost' references: " . trim($remaining) . "\n";

echo "\nFinal ready-to-import file:\n  $outputFile\n";
