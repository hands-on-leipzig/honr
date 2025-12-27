<?php
/**
 * Extract email HTML from Laravel log file
 * 
 * Usage: php extract-email-from-log.php [log-file-path] [output-dir]
 * Example: php extract-email-from-log.php storage/logs/laravel.log storage/emails
 */

$logFile = $argv[1] ?? 'storage/logs/laravel.log';
$outputDir = $argv[2] ?? 'storage/emails';

if (!file_exists($logFile)) {
    die("Log file not found: $logFile\n");
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$logContent = file_get_contents($logFile);
$emailCount = 0;

// Pattern to match email HTML in Laravel log
// Laravel logs emails with "Message-ID" header
preg_match_all('/Message-ID:.*?<\/html>/s', $logContent, $matches);

foreach ($matches[0] as $index => $emailContent) {
    // Extract subject if available
    preg_match('/Subject: (.+)/', $emailContent, $subjectMatch);
    $subject = $subjectMatch[1] ?? "email-{$emailCount}";
    
    // Clean up subject for filename
    $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $subject);
    $filename = substr($filename, 0, 50);
    
    // Extract HTML body (between <html> tags)
    preg_match('/<html[^>]*>.*?<\/html>/s', $emailContent, $htmlMatch);
    
    if (!empty($htmlMatch[0])) {
        $html = $htmlMatch[0];
        $outputFile = $outputDir . '/' . $filename . '-' . time() . '-' . $emailCount . '.html';
        file_put_contents($outputFile, $html);
        echo "Extracted: $outputFile\n";
        $emailCount++;
    }
}

if ($emailCount === 0) {
    echo "No emails found in log. Make sure you've sent some test emails.\n";
} else {
    echo "\nExtracted $emailCount email(s) to $outputDir/\n";
    echo "You can now:\n";
    echo "1. Open the HTML files in a browser\n";
    echo "2. Copy the content and paste into Outlook\n";
    echo "3. Or forward the HTML file as an attachment\n";
}

