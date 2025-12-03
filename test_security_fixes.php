<?php
require_once 'functions.php';

echo "Testing h() function...\n";
$input = '<script>alert("XSS")</script>';
$expected = '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';
$result = h($input);

if ($result === $expected) {
    echo "[PASS] h() function works correctly.\n";
} else {
    echo "[FAIL] h() function failed.\n";
    echo "Expected: $expected\n";
    echo "Got: $result\n";
}

echo "\nChecking syntax of modified files...\n";
$files = [
    'owner/user_edit.php',
    'owner/manage_requests.php',
    'functions.php'
];

foreach ($files as $file) {
    $output = [];
    $return_var = 0;
    exec("c:\\xampp\\php\\php.exe -l $file", $output, $return_var);
    if ($return_var === 0) {
        file_put_contents('test_result.txt', "[PASS] Syntax check for $file: OK\n", FILE_APPEND);
    } else {
        file_put_contents('test_result.txt', "[FAIL] Syntax check for $file: ERROR\n" . implode("\n", $output) . "\n", FILE_APPEND);
    }
}
file_put_contents('test_result.txt', "ALL TESTS COMPLETED\n", FILE_APPEND);

