<?php
require_once __DIR__ . '/../config.php';

echo "Migrating tables for multi-tenancy (holidays, shift_templates)...\n";

try {
    $pdo = getPDO();
    
    // 1. Check if column exists helper
    function columnExists($pdo, $table, $column) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return $stmt->fetch() !== false;
    }

    // --- Holidays Table ---
    if (!columnExists($pdo, 'holidays', 'company_id')) {
        echo "Adding company_id to holidays...\n";
        // 1. Truncate to avoid constraint issues during migration (assuming dev/staging env or acceptable data loss for consistency)
        // If production, we would default to a specific company or use a temp table logic.
        $pdo->exec("TRUNCATE TABLE holidays");
        
        // 2. Add column
        $pdo->exec("ALTER TABLE holidays ADD COLUMN company_id INT UNSIGNED NOT NULL AFTER id");
        
        // 3. Drop old unique constraint
        // Check index name usually 'holiday_date'
        $stmt = $pdo->prepare("SHOW INDEX FROM holidays WHERE Key_name = 'holiday_date'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $pdo->exec("ALTER TABLE holidays DROP INDEX holiday_date");
        }
        
        // 4. Add new composite unique constraint (if desired, or just index)
        // Note: holidays.php currently does DELETE then INSERT, so UNIQUE might not be strictly required for logic but good for integrity.
        // But logic is DELETE ... WHERE company_id = ? ...
        // Let's add simple index or unique.
        // UNIQUE on (company_id, holiday_date)
        $pdo->exec("ALTER TABLE holidays ADD UNIQUE KEY `unique_company_date` (`company_id`, `holiday_date`)");
        
        // 5. Add FK
        $pdo->exec("ALTER TABLE holidays ADD CONSTRAINT `fk_holidays_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE");
        
        echo "Holidays table migrated.\n";
    } else {
        echo "Holidays table already has company_id.\n";
    }

    // --- Shift Templates Table ---
    if (!columnExists($pdo, 'shift_templates', 'company_id')) {
        echo "Adding company_id to shift_templates...\n";
        $pdo->exec("TRUNCATE TABLE shift_templates");
        $pdo->exec("ALTER TABLE shift_templates ADD COLUMN company_id INT UNSIGNED NOT NULL AFTER template_id");
        $pdo->exec("ALTER TABLE shift_templates ADD CONSTRAINT `fk_templates_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE");
        echo "Shift Templates table migrated.\n";
    } else {
        echo "Shift Templates table already has company_id.\n";
    }

    // --- Announcements Table (Double Check) ---
    if (!columnExists($pdo, 'announcements', 'company_id')) {
        echo "Adding company_id to announcements...\n";
        $pdo->exec("TRUNCATE TABLE announcements");
        $pdo->exec("ALTER TABLE announcements ADD COLUMN company_id INT UNSIGNED NOT NULL AFTER id");
        $pdo->exec("ALTER TABLE announcements ADD CONSTRAINT `fk_announcements_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE");
        echo "Announcements table migrated.\n";
    } else {
        echo "Announcements table already has company_id.\n";
    }


    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
