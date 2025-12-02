-- MySQL / MariaDB 用スキーマ定義
CREATE DATABASE IF NOT EXISTS shift_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE shift_management;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(255) UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `user_type` ENUM('owner','part-time') NOT NULL DEFAULT 'part-time',
  `hourly_rate` DECIMAL(8,2) NOT NULL DEFAULT 1000.00,
  `transportation_expense` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `payslip_consent` TINYINT(1) NOT NULL DEFAULT 0,
  `payslip_consent_date` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shifts_requested` (
  `request_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `shift_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `request_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  INDEX (`user_id`),
  CONSTRAINT `fk_req_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shifts_scheduled` (
  `schedule_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `shift_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`schedule_id`),
  INDEX (`user_id`),
  CONSTRAINT `fk_sched_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- サンプル初期ユーザー（パスワードは PHP の password_hash() で生成したものに置き換えてください）
-- INSERT INTO users (username,password_hash,user_type) VALUES ('owner1', '$2y$10$...replace_with_hash...', 'owner');

CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 初期設定投入
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES ('closing_day', '15'), ('payment_day', '25');

CREATE TABLE IF NOT EXISTS `attendance_records` (
  `attendance_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `schedule_id` INT UNSIGNED DEFAULT NULL,
  `date` DATE NOT NULL,
  `clock_in_time` DATETIME,
  `clock_out_time` DATETIME,
  `status` ENUM('present', 'absent', 'late', 'early_leave', 'paid_leave') NOT NULL DEFAULT 'present',
  `notes` TEXT,
  `is_approved` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendance_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`schedule_id`) REFERENCES `shifts_scheduled`(`schedule_id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_user_date` (`user_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Phase 2 Migrations
-- Usersテーブル拡張
-- ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE AFTER username;
-- ALTER TABLE users ADD COLUMN payslip_consent TINYINT(1) NOT NULL DEFAULT 0;
-- ALTER TABLE users ADD COLUMN payslip_consent_date DATETIME DEFAULT NULL;
-- ※新規インストールのために users テーブル定義を更新する場合は上記 ALTER ではなく CREATE TABLE を修正すべきだが、
--   ここでは既存の CREATE TABLE を修正しつつ、追加テーブルを定義する。

-- スキル管理
CREATE TABLE IF NOT EXISTS `skills` (
  `skill_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `skill_name` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_skills` (
  `user_id` INT UNSIGNED NOT NULL,
  `skill_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `skill_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`skill_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- シフト交換
CREATE TABLE IF NOT EXISTS `shift_exchanges` (
  `exchange_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `requester_user_id` INT UNSIGNED NOT NULL,
  `target_shift_id` INT UNSIGNED NOT NULL,
  `requested_user_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `reason` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exchange_id`),
  FOREIGN KEY (`requester_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`target_shift_id`) REFERENCES `shifts_scheduled`(`schedule_id`) ON DELETE CASCADE,
  FOREIGN KEY (`requested_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 操作ログ
CREATE TABLE IF NOT EXISTS `operation_logs` (
  `log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `target_id` INT UNSIGNED DEFAULT NULL,
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- パスワードリセット
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`email`),
  INDEX (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
