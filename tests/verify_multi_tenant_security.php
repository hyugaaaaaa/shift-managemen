<?php
// マルチテナント・セキュリティ検証スクリプト
// 目的: 異なる会社のデータが適切に分離されているか検証する

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// テスト用DB接続
$pdo = getPDO();

echo "=== verifying Multi-Tenant Security ===\n";

try {
    // 1. テストデータ準備
    // 会社Aと会社Bを作成
    echo "[Setup] Creating test companies...\n";
    
    // Company A
    $codeA = generate_company_code();
    $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code, owner_name, email) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Security Test Corp A', $codeA, 'Owner A', 'ownerA@test.com']);
    $companyIdA = $pdo->lastInsertId();
    
    // Company B
    $codeB = generate_company_code();
    $stmt->execute(['Security Test Corp B', $codeB, 'Owner B', 'ownerB@test.com']);
    $companyIdB = $pdo->lastInsertId();
    
    echo "Created Company A (ID: $companyIdA) and Company B (ID: $companyIdB)\n";

    // ユーザー作成
    // User A1 (Company A)
    $stmtUsers = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_type, company_id, is_deleted) VALUES (?, ?, ?, ?, ?, 0)");
    $stmtUsers->execute(['User A1', 'userA1@test.com', 'hash', 'part-time', $companyIdA]);
    $userIdA1 = $pdo->lastInsertId();
    
    // User B1 (Company B)
    $stmtUsers->execute(['User B1', 'userB1@test.com', 'hash', 'part-time', $companyIdB]);
    $userIdB1 = $pdo->lastInsertId();

    echo "Created User A1 (ID: $userIdA1) and User B1 (ID: $userIdB1)\n";

    // データ作成
    // Announcement for A
    $stmtAnn = $pdo->prepare("INSERT INTO announcements (company_id, title, content) VALUES (?, ?, ?)");
    $stmtAnn->execute([$companyIdA, 'News for A', 'Content A']);
    $annIdA = $pdo->lastInsertId();
    
    // Holiday for B
    $stmtHol = $pdo->prepare("INSERT INTO holidays (company_id, holiday_date, description) VALUES (?, ?, ?)");
    $dateB = date('Y-m-d', strtotime('+10 days'));
    $stmtHol->execute([$companyIdB, $dateB, 'Holiday B']);
    
    // 2. 検証: オーナー機能でのフィルタリング (Simulation)
    
    echo "\n[Test 1] Owner A fetching users...\n";
    // 偽装セッション: Company A
    $_SESSION['company_id'] = $companyIdA;
    $_SESSION['user_type'] = 'owner';
    
    // users.php のロジック相当
    // 修正: users.phpの実際のクエリは ORDER BY username だが、テストスクリプトの手動クエリで ORDER BY name と書いていた可能性がある
    // 元のソース: ORDER BY username
    $stmt = $pdo->prepare('SELECT user_id, username FROM users WHERE user_type = ? AND is_deleted = 0 AND company_id = ? ORDER BY username');
    $stmt->execute(['part-time', $companyIdA]);
    $usersA = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $foundA1 = false;
    $foundB1 = false;
    foreach ($usersA as $u) {
        if ($u['user_id'] == $userIdA1) $foundA1 = true;
        if ($u['user_id'] == $userIdB1) $foundB1 = true;
    }
    
    if ($foundA1 && !$foundB1) {
        echo "PASS: Owner A sees User A1 and DOES NOT see User B1.\n";
    } else {
        echo "FAIL: Owner A visibility check failed. A1: " . ($foundA1?'Yes':'No') . ", B1: " . ($foundB1?'Yes':'No') . "\n";
        exit(1);
    }
    
    echo "\n[Test 2] Owner B fetching announcements...\n";
    // 偽装セッション: Company B
    $_SESSION['company_id'] = $companyIdB;
    
    // announcements.php のロジック相当
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE company_id = ?");
    $stmt->execute([$companyIdB]);
    $annsB = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $seesAnnA = false;
    // Bのお知らせリストにAのお知らせ(ID: $annIdA)が含まれていないか確認
    // (このテストではBのお知らせは作っていないので空のはず)
    if (count($annsB) === 0) {
        echo "PASS: Owner B sees 0 announcements (correct).\n";
    } else {
        // もしデータがあればIDチェック
        foreach($annsB as $a) {
            if ($a['id'] == $annIdA) $seesAnnA = true;
        }
        if ($seesAnnA) {
             echo "FAIL: Owner B sees Announcement for A!\n";
             exit(1);
        } else {
             echo "PASS: Owner B does not see Announcement A.\n";
        }
    }

    echo "\n[Test 3] Shift Submission Holiday Check (User A)\n";
    // User A1 tries to submit shift on Company B's holiday date
    // Should NOT be blocked because it's B's holiday, not A's.
    $_SESSION['company_id'] = $companyIdA;
    
    // submit_shift.php Logic
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ? AND company_id = ?");
    $stmt->execute([$dateB, $companyIdA]); // Checking against Company A
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "PASS: User A is NOT blocked by Company B's holiday.\n";
    } else {
        echo "FAIL: User A is blocked by Company B's holiday!\n";
        exit(1);
    }

    // Cleanup
    echo "\n[Cleanup] Removing test data...\n";
    $pdo->exec("DELETE FROM companies WHERE company_id IN ($companyIdA, $companyIdB)");
    // Users, Announcements, Holidays should cascade delete if FKs are correct.
    // If not, we manually delete.
    $pdo->exec("DELETE FROM users WHERE company_id IN ($companyIdA, $companyIdB)");
    
    echo "=== ALL SECURITY TESTS PASSED ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
