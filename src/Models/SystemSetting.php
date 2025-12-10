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
    public static function get(PDO $pdo, string $key, $default = null)
    {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }
}
