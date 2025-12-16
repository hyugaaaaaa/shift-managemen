-- MySQL / MariaDB 用スキーマ定義 (Updated for Multi-Tenant)
CREATE DATABASE IF NOT EXISTS shift_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE shift_management;

-- 企業テーブル (Multi-Tenant Root)
CREATE TABLE IF NOT EXISTS `companies` (
  `company_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(100) NOT NULL,
  `company_code` VARCHAR(20) NOT NULL UNIQUE,
  `representative_name` VARCHAR(100),
  `address` VARCHAR(255),
  `phone_number` VARCHAR(20),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ユーザーテーブル
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `company_user_id` INT UNSIGNED DEFAULT NULL, -- 企業内での連番ID (従業員番号)
  `username` VARCHAR(100) NOT NULL, -- Global UniqueではなくCompany内Unique
  `email` VARCHAR(255), -- NULL許容
  `password_hash` VARCHAR(255) NOT NULL,
  `user_type` ENUM('owner','part-time') NOT NULL DEFAULT 'part-time',
  `hourly_rate` DECIMAL(8,2) NOT NULL DEFAULT 1000.00,
  `transportation_expense` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `payslip_consent` TINYINT(1) NOT NULL DEFAULT 0,
  `payslip_consent_date` DATETIME DEFAULT NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `login_attempts` INT DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `is_agreed_terms` TINYINT(1) NOT NULL DEFAULT 0,
  `agreed_terms_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_company_username` (`company_id`, `username`),
  UNIQUE KEY `unique_email_company` (`email`, `company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- シフト希望
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

-- 確定シフト
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

-- システム設定 (Company Scope)
CREATE TABLE IF NOT EXISTS `system_settings` (
  `company_id` INT UNSIGNED NOT NULL,
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`company_id`, `setting_key`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 勤怠記録
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
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`email`),
  INDEX (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- お知らせ
CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- シフトテンプレート
CREATE TABLE IF NOT EXISTS `shift_templates` (
    `template_id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `template_name` VARCHAR(50) NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 定休日
CREATE TABLE IF NOT EXISTS `holidays` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `holiday_date` DATE NOT NULL,
    `description` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_company_date` (`company_id`, `holiday_date`),
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- メール送信キュー
CREATE TABLE IF NOT EXISTS `mail_queue` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `to_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `status` ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `sent_at` DATETIME DEFAULT NULL,
    `error_message` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
