<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    
    // Get staff1 user_id
    $stmt = $pdo->prepare("SELECT user_id, company_id FROM users WHERE username = 'staff1' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        die("User 'staff1' not found. Please run seed.php first.");
    }
    
    $user_id = $user['user_id'];
    $company_id = $user['company_id'];
    
    // Create attendance records for last month and this month
    $dates = [
        date('Y-m-d', strtotime('-1 month +1 day')), // 1 day work
        date('Y-m-d', strtotime('-1 month +2 days')),
        date('Y-m-d', strtotime('-1 month +5 days')),
        date('Y-m-d', strtotime('yesterday')),
    ];
    
    echo "Creating attendance records for user $user_id (Company: $company_id)...\n";
    
    $stmt = $pdo->prepare("INSERT INTO attendance_records (user_id, date, clock_in_time, clock_out_time, status, is_approved) VALUES (?, ?, ?, ?, 'present', 1) ON DUPLICATE KEY UPDATE clock_in_time = VALUES(clock_in_time)");
    
    foreach ($dates as $date) {
        // 9:00 - 18:00 (8h work + 1h break implicit in logic?) 
        // Logic calculates simply end - start. Let's say 10:00 - 19:00 (9h)
        $start = "$date 10:00:00";
        $end = "$date 19:00:00";
        
        $stmt->execute([$user_id, $date, $start, $end]);
        echo "Inserted record for $date\n";
    }
    
    echo "Done.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
