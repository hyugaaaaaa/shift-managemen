<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    if ($sql === false) {
        throw new Exception("Could not read schema.sql");
    }

    // Split SQL by semicolon strictly might fail on triggers/procedures but schema.sql looks simple.
    // However, PDO can execute multiple queries if emulation is enabled (default in some versions).
    // Or we can just try executing the whole block if the driver supports it, or split.
    // Since schema.sql has simple CREATE TABLE statements, splitting by semi-colon is safer for line-by-line reporting,
    // but running as one block is often supported.
    // Let's try exec the whole thing.
    
    // To handle potential multiple statements properly with detailed error reporting, let's split.
    // But basic splitting by ; might break if text contains ;.
    // schema.sql provided seems to have comments and standard structure.
    // Let's just try executing the whole thing first.
    
    $pdo->exec($sql);
    echo "Database initialized from schema.sql\n";

} catch (Exception $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
    exit(1);
}
