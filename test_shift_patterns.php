<?php
require_once __DIR__ . '/config.php';
session_start();

// Mock session for owner
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'owner';

echo "Starting Shift Pattern Verification...\n";

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // 1. Create Template
    $stmt = $pdo->prepare("INSERT INTO shift_templates (template_name, start_time, end_time) VALUES (?, ?, ?)");
    $stmt->execute(['Test Pattern A', '09:00:00', '18:00:00']);
    $tpl_id = $pdo->lastInsertId();
    echo "Created template ID: $tpl_id\n";

    // 2. Verify Template Exists
    $stmt = $pdo->query("SELECT * FROM shift_templates WHERE template_id = $tpl_id");
    $tpl = $stmt->fetch();
    if ($tpl && $tpl['template_name'] === 'Test Pattern A') {
        echo "PASS: Template created successfully.\n";
    } else {
        echo "FAIL: Template creation failed.\n";
    }

    // 3. Mock session for Part-time user to check submit page
    $_SESSION['user_id'] = 2; // Assuming ID 2 exists or doesn't matter for this check
    $_SESSION['user_type'] = 'part-time';
    
    // Capture output of submit_shift.php (we need to suppress headers)
    ob_start();
    // We can't include submit_shift.php directly because it has logic that might redirect or exit.
    // Instead, we'll just check if the DB query works in the context of the file.
    // But we can check if the view file contains the new HTML.
    
    $view_content = file_get_contents(__DIR__ . '/views/parttime/submit_shift_view.php');
    if (strpos($view_content, 'id="shift_template"') !== false && strpos($view_content, 'data-start') !== false) {
        echo "PASS: View file contains dropdown and data attributes.\n";
    } else {
        echo "FAIL: View file missing expected HTML.\n";
    }

    // Clean up
    $pdo->rollBack();
    echo "Test finished. Rolled back changes.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
