<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // 1. Get Sample Company ID
    $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE company_code = 'SAMPLE01'");
    $stmt->execute();
    $sample_company_id = $stmt->fetchColumn();

    if (!$sample_company_id) {
        throw new Exception("Sample company 'SAMPLE01' not found. Aborting cleanup.");
    }

    echo "Found Sample Company ID: $sample_company_id\n";

    // 2. Delete from tables without Foreign Keys (or manual cleanup needed)
    
    // announcements
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE company_id != ?");
    $stmt->execute([$sample_company_id]);
    echo "Deleted " . $stmt->rowCount() . " rows from announcements.\n";

    // password_resets
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE company_id != ?");
    $stmt->execute([$sample_company_id]);
    echo "Deleted " . $stmt->rowCount() . " rows from password_resets.\n";

    // 3. Delete from companies (Cascades to users, shifts, etc.)
    $stmt = $pdo->prepare("DELETE FROM companies WHERE company_id != ?");
    $stmt->execute([$sample_company_id]);
    echo "Deleted " . $stmt->rowCount() . " rows from companies (and cascaded tables).\n";

    $pdo->commit();
    echo "Cleanup completed successfully.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
