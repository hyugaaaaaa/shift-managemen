<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    echo "Starting schema update for company_user_id...\n";

    // 1. Add column if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'company_user_id'");
    if (!$stmt->fetch()) {
        echo "Adding 'company_user_id' column to 'users'...\n";
        $sql = "ALTER TABLE users ADD COLUMN company_user_id INT UNSIGNED DEFAULT NULL AFTER user_id";
        $pdo->exec($sql);
        echo "Column added.\n";
    } else {
        echo "'company_user_id' column already exists.\n";
    }

    // 2. Backfill existing data
    echo "Backfilling company_user_id for existing users...\n";
    
    // Get all companies
    $stmt = $pdo->query("SELECT company_id FROM companies");
    $companies = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Also include users with NULL company_id (if any, though shouldn't exist in strict multi-tenant)
    // or assuming all users belong to a company.
    
    foreach ($companies as $companyId) {
        // Get users for this company ordered by created_at (or user_id)
        // Only update those who have NULL company_user_id
        $stmtUsers = $pdo->prepare("SELECT user_id FROM users WHERE company_id = ? AND company_user_id IS NULL ORDER BY user_id ASC");
        $stmtUsers->execute([$companyId]);
        $users = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($users) > 0) {
            echo "Processing Company ID $companyId: " . count($users) . " users to update.\n";
            
            // Get current max company_user_id to start from (in case some are already filled)
            $stmtMax = $pdo->prepare("SELECT MAX(company_user_id) FROM users WHERE company_id = ?");
            $stmtMax->execute([$companyId]);
            $currentMax = $stmtMax->fetchColumn();
            $nextId = ($currentMax) ? $currentMax + 1 : 1;
            
            $stmtUpdate = $pdo->prepare("UPDATE users SET company_user_id = ? WHERE user_id = ?");
            
            foreach ($users as $userId) {
                $stmtUpdate->execute([$nextId, $userId]);
                $nextId++;
            }
        }
    }
    
    // Also handle users with company_id = 0 or NULL if necessary (maybe admin/owner?)
    // Owners usually have user_type='owner', maybe they don't need company_user_id or they are separate.
    // Based on requirement, this is mostly for 'employees' (part-time). 
    // But let's check if we should do it for all.
    // "新しく登録した会社にはaという名前のアルバイトは..." suggests part-time staff.
    // Owners might share the same counter or separate. Usually owners are just system users.
    // Let's assume this is primarily for 'part-time' users, but filling it for all in company is safer.
    
    echo "Backfill completed.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
