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
}
