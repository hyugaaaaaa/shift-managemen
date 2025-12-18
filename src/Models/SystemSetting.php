<?php

namespace App\Models;

use PDO;

class SystemSetting
{
    /**
     * システム設定値を取得する
     * @param PDO $pdo
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(PDO $pdo, string $key, $default = null, ?int $company_id = null)
    {
        if ($company_id === null) {
            // fallback for global settings or error (should be handled by caller)
            // ここではマイグレーション時のデフォルト(0)または引数なしの場合を考慮して単純に検索
            // しかしマルチテナントなので基本は必須
             $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1"); // 危険だが、既存コード互換のため
             $stmt->execute([$key]);
        } else {
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? AND company_id = ?");
            $stmt->execute([$key, $company_id]);
        }
        
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    /**
     * システム設定値を保存する
     * @param PDO $pdo
     * @param string $key
     * @param string $value
     * @param string $description
     * @param int $company_id
     */
    public static function save(PDO $pdo, string $key, string $value, string $description, int $company_id)
    {
        // 既存の設定があるか確認
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE company_id = ? AND setting_key = ?");
        $stmt->execute([$company_id, $key]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE company_id = ? AND setting_key = ?");
            $stmt->execute([$value, $company_id, $key]);
        } else {
            // ディスクリプションはDBカラムにないかもしれないので確認が必要だが、
            // スキーマを見ると setting_key, setting_value, company_id の3つ。
            // schema.sql:
            // CREATE TABLE `system_settings` ( `company_id` INT, `setting_key` VARCHAR, `setting_value` VARCHAR, PRIMARY KEY... )
            // descriptionカラムは存在しないようなので無視あるいは将来用。
            // 引数 description は一応受け取るが使わない。
            
            $stmt = $pdo->prepare("INSERT INTO system_settings (company_id, setting_key, setting_value) VALUES (?, ?, ?)");
            $stmt->execute([$company_id, $key, $value]);
        }
    }
}
