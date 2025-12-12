<?php
$envPath = __DIR__ . '/.env';
echo "Checking .env at: $envPath\n";

if (file_exists($envPath)) {
    echo "File exists.\n";
    if (is_readable($envPath)) {
        echo "File is readable.\n";
        $content = file_get_contents($envPath);
        echo "Content length: " . strlen($content) . "\n";
        
        // Dump first 10 bytes in Hex
        echo "First 10 bytes (Hex): ";
        for ($i = 0; $i < min(10, strlen($content)); $i++) {
            echo dechex(ord($content[$i])) . " ";
        }
        echo "\n";
        
        echo "Full Content (Masked):\n";
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            // Mask values
            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                echo "$key=********\n";
            } else {
                echo "$line\n";
            }
        }
    } else {
        echo "File is NOT readable.\n";
    }
} else {
    echo "File does NOT exist.\n";
}
