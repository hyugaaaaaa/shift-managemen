<?php
// 同名ユーザー登録・ログイン検証スクリプト
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$pdo = getPDO();
echo "=== Verifying Duplicate Username Support ===\n";

try {
    // 1. テストデータ作成
    $companyCode = 'TESTDUP' . rand(100,999);
    $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code, owner_name, email) VALUES ('DupTest Corp', ?, 'Owner Dup', 'dup@test.com')");
    $stmt->execute([$companyCode]);
    $companyId = $pdo->lastInsertId();

    $commonName = 'test_user_dup_' . rand(1000,9999);
    $pass1 = 'password123';
    $pass2 = 'password456';
    $email1 = 'dup1_' . rand(100,999) . '@test.com';
    $email2 = 'dup2_' . rand(100,999) . '@test.com';

    echo "Creating User 1: $commonName / $pass1\n";
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_type, company_id) VALUES (?, ?, ?, 'part-time', ?)");
    $stmt->execute([$commonName, $email1, password_hash($pass1, PASSWORD_DEFAULT), $companyId]);
    $id1 = $pdo->lastInsertId();

    echo "Creating User 2: $commonName / $pass2 (Same Name, Diff Pass)\n";
    $stmt->execute([$commonName, $email2, password_hash($pass2, PASSWORD_DEFAULT), $companyId]);
    $id2 = $pdo->lastInsertId();

    // 2. ログイン検証 (Mock)
    // User 1 Login
    echo "[Test 1] Login as User 1 ($pass1)\n";
    if (mock_login($pdo, $commonName, $pass1) === $id1) {
        echo "PASS: Authenticated as User 1 correctly.\n";
    } else {
        echo "FAIL: Failed to authenticate as User 1.\n";
        exit(1);
    }

    // User 2 Login
    echo "[Test 2] Login as User 2 ($pass2)\n";
    if (mock_login($pdo, $commonName, $pass2) === $id2) {
        echo "PASS: Authenticated as User 2 correctly.\n";
    } else {
        echo "FAIL: Failed to authenticate as User 2.\n";
        exit(1);
    }

    // Wrong Password
    echo "[Test 3] Wrong Password\n";
    if (mock_login($pdo, $commonName, 'wrongpass') === false) {
        echo "PASS: Authentication failed as expected.\n";
    } else {
        echo "FAIL: Authenticated with wrong password??\n";
        exit(1);
    }
    
    // Cleanup
    $pdo->exec("DELETE FROM users WHERE user_id IN ($id1, $id2)");
    $pdo->exec("DELETE FROM companies WHERE company_id = $companyId");
    echo "=== ALL TESTS PASSED ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

function mock_login($pdo, $username, $password) {
    $stmt = $pdo->prepare('SELECT user_id, username, password_hash, user_type, login_attempts, locked_until, company_id FROM users WHERE username = ? AND is_deleted = 0');
    $stmt->execute([$username]);
    $users = $stmt->fetchAll();
    
    foreach ($users as $u) {
        if (password_verify($password, $u['password_hash'])) {
            return $u['user_id'];
        }
    }
    return false;
}
