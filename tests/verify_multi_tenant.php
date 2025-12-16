<?php
// 検証用スクリプト
// 実行方法: php tests/verify_multi_tenant.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../src/Services/ShiftService.php';

use App\Services\ShiftService;

function testLog($msg, $status = 'INFO') {
    $line = "[$status] $msg" . PHP_EOL;
    echo $line;
    file_put_contents(__DIR__ . '/verify_error.log', $line, FILE_APPEND);
}

function assertTest($condition, $msg) {
    if ($condition) {
        testLog($msg, 'PASS');
    } else {
        testLog($msg, 'FAIL');
        exit(1);
    }
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    testLog("Starting Multi-Tenant Verification...");

    // 1. Company Creation Test
    $compCodeA = generate_company_code();
    $compCodeB = generate_company_code();
    assertTest($compCodeA !== $compCodeB, "Generated unique codes");

    $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code) VALUES (?, ?)");
    $stmt->execute(['Test Company A', $compCodeA]);
    $compIdA = $pdo->lastInsertId();

    $stmt->execute(['Test Company B', $compCodeB]);
    $compIdB = $pdo->lastInsertId();

    assertTest($compIdA > 0 && $compIdB > 0, "Created instances for Company A ($compIdA) and B ($compIdB)");

    // 2. User Creation Test
    // Owner A
    $emailA = 'owner@example.com'; // Same email base for test? No, let's use unique for owners just in case
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_type, company_id) VALUES (?, ?, 'hash', 'owner', ?)");
    $stmt->execute(['ownerA', 'ownerA@test.com', $compIdA]);
    $ownerIdA = $pdo->lastInsertId();

    // Owner B
    $stmt->execute(['ownerB', 'ownerB@test.com', $compIdB]);
    $ownerIdB = $pdo->lastInsertId();

    // Same Email User (Staff) Test
    $commonEmail = 'staff@common.com';
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_type, company_id) VALUES (?, ?, 'hash', 'part-time', ?)");
    
    // Staff A
    $stmt->execute(['staffA', $commonEmail, $compIdA]);
    $staffIdA = $pdo->lastInsertId();
    
    // Staff B
    $stmt->execute(['staffB', $commonEmail, $compIdB]);
    $staffIdB = $pdo->lastInsertId();

    assertTest($staffIdA !== $staffIdB, "Created users with same email in different companies ($staffIdA, $staffIdB)");

    // 3. Shift Data Separation Test
    $date = date('Y-m-d');
    $service = new ShiftService();

    // Add Shift for Staff A
    $stmt = $pdo->prepare("INSERT INTO shifts_scheduled (user_id, shift_date, start_time, end_time) VALUES (?, ?, '09:00:00', '18:00:00')");
    $stmt->execute([$staffIdA, $date]);

    // Add Shift for Staff B
    $stmt->execute([$staffIdB, $date]);

    // Verify Data Access
    // Get records for Company A
    // Note: getMergedWorkRecords signature: ($pdo, $start, $end, $target_user, $company_id)
    $recordsA = $service->getMergedWorkRecords($pdo, $date, $date, null, $compIdA);
    $recordsB = $service->getMergedWorkRecords($pdo, $date, $date, null, $compIdB);

    // Check Company A results
    $foundStaffA_in_A = isset($recordsA[$staffIdA]);
    $foundStaffB_in_A = isset($recordsA[$staffIdB]);
    
    assertTest($foundStaffA_in_A, "Company A sees Staff A's shift");
    assertTest(!$foundStaffB_in_A, "Company A does NOT see Staff B's shift");

    // Check Company B results
    $foundStaffA_in_B = isset($recordsB[$staffIdA]);
    $foundStaffB_in_B = isset($recordsB[$staffIdB]);

    assertTest(!$foundStaffA_in_B, "Company B does NOT see Staff A's shift");
    assertTest($foundStaffB_in_B, "Company B sees Staff B's shift");

    // 4. Password Reset Schema Check
    // forgot_password.php logic check
    testLog("Checking password_resets insert...", "INFO");
    $reset_token = bin2hex(random_bytes(32));
    // Try insert without expires_at (as in forgot_password.php)
    // If table has expires_at NOT NULL, this will fail
    try {
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, company_id) VALUES (?, ?, ?)");
        $stmt->execute(['test@example.com', $reset_token, $compIdA]);
        assertTest(true, "Password reset insert successful (schema matches implementation)");
    } catch (Exception $e) {
        // If fails, we need to fix forgot_password.php
        testLog("Password reset insert failed: " . $e->getMessage(), "WARN");
        throw $e;
    }

    testLog("All verification tests passed!");

    // Clean up (Rollback)
    $pdo->rollBack();
    testLog("Database changes rolled back.");

} catch (Exception $e) {
    testLog("Exception: " . $e->getMessage() . " at file " . $e->getFile() . " line " . $e->getLine(), 'ERROR');
    testLog("Trace: " . $e->getTraceAsString(), 'ERROR');
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    exit(1);
}
