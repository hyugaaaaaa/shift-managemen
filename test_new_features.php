<?php
$files = [
    'owner/announcements.php',
    'views/owner/announcements_view.php',
    'profile.php',
    'views/profile_view.php',
    'dashboard.php',
    'views/dashboard_view.php',
    'template.php',
    'functions.php'
];

$has_error = false;
$results = [];

foreach ($files as $file) {
    $output = [];
    $return_var = 0;
    exec("c:\\xampp\\php\\php.exe -l " . __DIR__ . "\\$file", $output, $return_var);
    
    if ($return_var === 0) {
        $results[] = "[PASS] $file";
    } else {
        $has_error = true;
        $results[] = "[FAIL] $file";
        $results = array_merge($results, $output);
    }
}

file_put_contents('test_new_features_result.txt', implode("\n", $results));

if ($has_error) {
    echo "Errors found. Check test_new_features_result.txt\n";
    exit(1);
} else {
    echo "All syntax checks passed.\n";
    exit(0);
}
