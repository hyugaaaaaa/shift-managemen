<?php
require_once __DIR__ . '/config.php';

echo "Creating test users...\n";

$pdo = getPDO();

// テストユーザー定義
$users = [
    [
        'username' => 'test_owner',
        'password' => 'password123',
        'type' => 'owner',
        'hourly_rate' => 0,
        'transportation_expense' => 0
    ],
    [
        'username' => 'test_parttime',
        'password' => 'password123',
        'type' => 'part-time',
        'hourly_rate' => 1000,
        'transportation_expense' => 500
    ]
];

foreach ($users as $u) {
    // 既存チェック
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$u['username']]);
    if ($stmt->fetch()) {
        echo "User '{$u['username']}' already exists. Skipping.\n";
        continue;
    }

    // 作成
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, user_type, hourly_rate, transportation_expense) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $u['username'],
        password_hash($u['password'], PASSWORD_DEFAULT),
        $u['type'],
        $u['hourly_rate'],
        $u['transportation_expense']
    ]);
    echo "User '{$u['username']}' created. (Password: {$u['password']})\n";
}

echo "Done.\n";
