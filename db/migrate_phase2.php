<?php
require_once __DIR__ . '/../config.php';

echo "Starting Phase 2 Migration...\n";

try {
    $pdo = getPDO();

    // 1. Usersテーブルの拡張 (emailカラムの追加)
    echo "Checking users table for email column...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($stmt->rowCount() == 0) {
        // emailカラムが存在しない場合のみ追加（ユニーク制約付き）
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE AFTER username");
        echo "Added email column to users table.\n";
    } else {
        echo "email column already exists.\n";
    }

    // 2. スキル管理機能用テーブルの作成
    echo "Creating skills tables...\n";
    // スキルマスタテーブル
    $pdo->exec("CREATE TABLE IF NOT EXISTS skills (
        skill_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        skill_name VARCHAR(100) NOT NULL UNIQUE, -- スキル名は重複不可
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (skill_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ユーザーとスキルの関連付けテーブル（多対多）
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_skills (
        user_id INT UNSIGNED NOT NULL,
        skill_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (user_id, skill_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE, -- ユーザー削除時に連動して削除
        FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE -- スキル削除時に連動して削除
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. シフト交換機能用テーブルの作成
    echo "Creating shift_exchanges table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS shift_exchanges (
        exchange_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        requester_user_id INT UNSIGNED NOT NULL, -- 交換申請者
        target_shift_id INT UNSIGNED NOT NULL,   -- 交換対象のシフトID
        requested_user_id INT UNSIGNED DEFAULT NULL, -- 交換相手（指名する場合）
        status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending', -- ステータス
        reason TEXT, -- 申請理由
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (exchange_id),
        FOREIGN KEY (requester_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (target_shift_id) REFERENCES shifts_scheduled(schedule_id) ON DELETE CASCADE,
        FOREIGN KEY (requested_user_id) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. 操作ログ用テーブルの作成
    echo "Creating operation_logs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS operation_logs (
        log_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED DEFAULT NULL, -- 操作したユーザー（未ログイン時はNULL）
        action VARCHAR(100) NOT NULL,      -- 操作内容
        target_id INT UNSIGNED DEFAULT NULL, -- 操作対象のID
        details TEXT,                      -- 詳細情報
        ip_address VARCHAR(45),            -- 接続元IPアドレス
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (log_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. パスワードリセット用テーブルの作成
    echo "Creating password_resets table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,       -- リセット用トークン
        expires_at DATETIME NOT NULL,      -- 有効期限
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (email),
        INDEX (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Phase 2 Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
