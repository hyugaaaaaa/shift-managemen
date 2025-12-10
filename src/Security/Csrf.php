<?php

namespace App\Security;

class Csrf
{
    /**
     * CSRFトークンを生成する
     * @return string
     */
    public static function generate(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * CSRFトークンを検証する
     * @param string $token
     * @return bool
     */
    public static function validate(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
