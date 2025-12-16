<?php
require_once __DIR__ . '/../config.php';

echo "Seeding database...\n";

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // 1. Create Sample Company
    $company_code = 'SAMPLE01';
    $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE company_code = ?");
    $stmt->execute([$company_code]);
    $company_id = $stmt->fetchColumn();

    if ($company_id) {
        echo "Company 'SAMPLE01' already exists (ID: $company_id). Skipping company creation.\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code, representative_name, address, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Sample Corp', $company_code, 'Sample Owner', 'Tokyo, Japan', '03-1234-5678']);
        $company_id = $pdo->lastInsertId();
        echo "Created Company 'Sample Corp' (ID: $company_id, Code: $company_code)\n";
    }

    // 2. Create Owner
    $owner_username = 'owner';
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND company_id = ?");
    $stmt->execute([$owner_username, $company_id]);
    if ($stmt->fetch()) {
        echo "Owner 'owner' already exists. Skipping.\n";
    } else {
        $password = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (company_id, username, email, password_hash, user_type, is_agreed_terms, agreed_terms_at) VALUES (?, ?, ?, ?, 'owner', 1, NOW())");
        $stmt->execute([$company_id, $owner_username, 'owner@sample.com', $password]);
        echo "Created Owner User: 'owner' / 'password'\n";
    }

    // 3. Create Staff
    $staff_list = [
        ['name' => 'staff1', 'email' => 'staff1@sample.com'],
        ['name' => 'staff2', 'email' => 'staff2@sample.com'],
        ['name' => 'tanaka', 'email' => 'tanaka@sample.com']
    ];

    foreach ($staff_list as $staff) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND company_id = ?");
        $stmt->execute([$staff['name'], $company_id]);
        if ($stmt->fetch()) {
            echo "Staff '{$staff['name']}' already exists. Skipping.\n";
        } else {
            // Lock and generate ID logic (simplified copy of register_staff.php logic)
            $stmtLock = $pdo->prepare("SELECT company_id FROM companies WHERE company_id = ? FOR UPDATE");
            $stmtLock->execute([$company_id]);
            
            $stmtMax = $pdo->prepare("SELECT MAX(company_user_id) FROM users WHERE company_id = ?");
            $stmtMax->execute([$company_id]);
            $next_id = ($stmtMax->fetchColumn() ?: 0) + 1;

            $password = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (company_id, buffer_company_user_id, username, email, password_hash, user_type, is_agreed_terms, agreed_terms_at) VALUES (?, ?, ?, ?, ?, 'part-time', 1, NOW())");
            
            // Note: column name is company_user_id not buffer_company_user_id, fixing in next line
             $stmt = $pdo->prepare("INSERT INTO users (company_id, company_user_id, username, email, password_hash, user_type, is_agreed_terms, agreed_terms_at) VALUES (?, ?, ?, ?, ?, 'part-time', 1, NOW())");
            $stmt->execute([$company_id, $next_id, $staff['name'], $staff['email'], $password]);
            echo "Created Staff: '{$staff['name']}' / 'password' (Employee ID: $next_id)\n";
        }
    }

    $pdo->commit();
    echo "Seeding completed successfully.\n";
    echo "\n=== LOGIN INFO ===\n";
    echo "Company Code: SAMPLE01\n";
    echo "Owner: owner / password\n";
    echo "Staff: staff1 / password\n";
    echo "==================\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
