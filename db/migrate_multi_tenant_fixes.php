<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    echo "Starting schema update for multi-tenant security fixes...\n";

    // 1. Get default company ID (to assign existing records to)
    $stmt = $pdo->query("SELECT company_id FROM companies ORDER BY company_id ASC LIMIT 1");
    $defaultCompanyId = $stmt->fetchColumn();

    if (!$defaultCompanyId) {
        // If no company exists, creating one or handling error.
        // For migration safety, we might need to create a dummy company if none exists,
        // but verify_multi_tenant.php should have created one.
        echo "Warning: No companies found. Existing data might be lost or migration might fail if strictly enforcing foreign keys immediately.\n";
        // Attempt to create a default company if strict mode is needed, but for now let's assume one exists or we set to 0/1 carefully.
        if (!$defaultCompanyId) $defaultCompanyId = 1; 
    }
    echo "Default Company ID for migration: " . $defaultCompanyId . "\n";

    // 2. Add company_id to holidays
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM holidays LIKE 'company_id'");
    if ($stmt->fetch()) {
        echo "Column 'company_id' already exists in 'holidays'. Skipping.\n";
    } else {
        echo "Adding 'company_id' to 'holidays'...\n";
        // Add column allowing NULL first to fill data, then modify to NOT NULL if needed, 
        // OR add with DEFAULT then drop default.
        // Simpler: Add INT UNSIGNED DEFAULT '{$defaultCompanyId}' (MySQL supports integer defaults)
        
        $sql = "ALTER TABLE holidays ADD COLUMN company_id INT UNSIGNED NOT NULL DEFAULT {$defaultCompanyId} AFTER id";
        $pdo->exec($sql);
        
        // Add Foreign Key
        $pdo->exec("ALTER TABLE holidays ADD CONSTRAINT fk_holidays_company FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE");
        echo "Done.\n";
    }

    // 3. Add company_id to announcements
    $stmt = $pdo->query("SHOW COLUMNS FROM announcements LIKE 'company_id'");
    if ($stmt->fetch()) {
        echo "Column 'company_id' already exists in 'announcements'. Skipping.\n";
    } else {
        echo "Adding 'company_id' to 'announcements'...\n";
        $sql = "ALTER TABLE announcements ADD COLUMN company_id INT UNSIGNED NOT NULL DEFAULT {$defaultCompanyId} AFTER id";
        $pdo->exec($sql);
        
        $pdo->exec("ALTER TABLE announcements ADD CONSTRAINT fk_announcements_company FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE");
        echo "Done.\n";
    }

    // 4. Add company_id to shift_templates
    $stmt = $pdo->query("SHOW COLUMNS FROM shift_templates LIKE 'company_id'");
    if ($stmt->fetch()) {
        echo "Column 'company_id' already exists in 'shift_templates'. Skipping.\n";
    } else {
        echo "Adding 'company_id' to 'shift_templates'...\n";
        $sql = "ALTER TABLE shift_templates ADD COLUMN company_id INT UNSIGNED NOT NULL DEFAULT {$defaultCompanyId} AFTER template_id";
        $pdo->exec($sql);
        
        $pdo->exec("ALTER TABLE shift_templates ADD CONSTRAINT fk_templates_company FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE");
        echo "Done.\n";
    }

    echo "Schema update completed successfully.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
