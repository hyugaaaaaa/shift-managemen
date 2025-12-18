<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // 1. Company A
    $companyCodeA = 'SAMPLE_A';
    $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE company_code = ?");
    $stmt->execute([$companyCodeA]);
    $companyA = $stmt->fetch();

    if (!$companyA) {
        $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code, representative_name, address, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Sample Corp A', $companyCodeA, 'Tarou Yamada', 'Tokyo, Shibuya', '03-1111-2222']);
        $companyIdA = $pdo->lastInsertId();
        echo "Created Company A (ID: $companyIdA)\n";
    } else {
        $companyIdA = $companyA['company_id'];
        echo "Company A already exists (ID: $companyIdA)\n";
    }

    // 2. Company B
    $companyCodeB = 'SAMPLE_B';
    $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE company_code = ?");
    $stmt->execute([$companyCodeB]);
    $companyB = $stmt->fetch();

    if (!$companyB) {
        $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code, representative_name, address, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Sample Corp B', $companyCodeB, 'Hanako Suzuki', 'Osaka, Umeda', '06-3333-4444']);
        $companyIdB = $pdo->lastInsertId();
        echo "Created Company B (ID: $companyIdB)\n";
    } else {
        $companyIdB = $companyB['company_id'];
        echo "Company B already exists (ID: $companyIdB)\n";
    }

    // Function to add user
    function addUser($pdo, $companyId, $username, $password, $userType, $companyUserId) {
        // Check if username exists in company
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE company_id = ? AND username = ?");
        $stmt->execute([$companyId, $username]);
        if ($stmt->fetch()) {
            echo "User $username already exists in company $companyId\n";
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        // Assuming email is optional or can be null/dummy. Leaving it null for simplicity unless required.
        // But unique_email_company allows NULLs in MySQL (usually). 
        // Let's create dummy emails.
        $email = $username . '@example.com';

        $stmt = $pdo->prepare("INSERT INTO users (company_id, company_user_id, username, email, password_hash, user_type, hourly_rate, transportation_expense) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$companyId, $companyUserId, $username, $email, $passwordHash, $userType, 1200.00, 500.00]);
        echo "Created User $username in Company $companyId (Type: $userType)\n";
    }

    // Users for Company A
    addUser($pdo, $companyIdA, 'owner_a', 'password123', 'owner', 1);
    addUser($pdo, $companyIdA, 'user_a_1', 'password123', 'part-time', 2);
    addUser($pdo, $companyIdA, 'user_a_2', 'password123', 'part-time', 3);

    // Users for Company B
    addUser($pdo, $companyIdB, 'owner_b', 'password123', 'owner', 1);
    addUser($pdo, $companyIdB, 'user_b_1', 'password123', 'part-time', 2);
    addUser($pdo, $companyIdB, 'user_b_2', 'password123', 'part-time', 3);

    $pdo->commit();
    echo "Sample data insertion completed successfully.\n";

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
