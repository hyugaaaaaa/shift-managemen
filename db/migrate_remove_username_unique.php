<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    echo "Starting schema update to remove UNIQUE constraint on username...\n";

    // Check if index exists and what its name is.
    // Usually it's 'username' or 'username_UNIQUE'.
    
    // Get index information
    $tableName = 'users';
    $stmt = $pdo->prepare("SHOW INDEX FROM users WHERE Key_name = 'username'");
    $indexExists = $stmt->fetch();

    if ($indexExists) {
        // Check if it is unique
        if ($indexExists['Non_unique'] == 0) {
            echo "Found UNIQUE index 'username'. Dropping and recreating as normal INDEX...\n";
            
            // Drop unique index
            $pdo->exec("ALTER TABLE users DROP INDEX username");
            
            // Add normal index (for performance)
            $pdo->exec("ALTER TABLE users ADD INDEX idx_username (username)");
            
            echo "Done. UNIQUE constraint removed.\n";
        } else {
             echo "Index 'username' exists but is already Non_unique. No changes needed.\n";
        }
    } else {
        // Try checking for other common names if 'username' index not found, 
        // or just try adding index if missing.
        echo "Index 'username' not found. Checking for constraint by column...\n";
        // If strictly defined as constraint, might need different approach, but usually MySQL indexes handle unique.
        
        // Let's just create a normal index if not exists
        echo "Creating normal index on username...\n";
        $pdo->exec("ALTER TABLE users ADD INDEX idx_username (username)");
        echo "Done.\n";
    }

    echo "Schema update completed successfully.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    // If error is "Can't DROP 'username'; check that column/key exists", it means it might be named differently.
    
    // Fallback: Try to identify unique index on username column
    try {
        echo "Attempting fallback to find unique index...\n";
        $stmt = $pdo->query("SHOW INDEX FROM users WHERE Column_name = 'username' AND Non_unique = 0");
        $idx = $stmt->fetch();
        if ($idx) {
            $keyName = $idx['Key_name'];
            echo "Found unique index named '$keyName'. Dropping...\n";
            $pdo->exec("ALTER TABLE users DROP INDEX `$keyName`");
            $pdo->exec("ALTER TABLE users ADD INDEX idx_username (username)");
            echo "Done.\n";
        }
    } catch (Exception $ex) {
        echo "Fallback failed: " . $ex->getMessage() . "\n";
        exit(1);
    }
}
