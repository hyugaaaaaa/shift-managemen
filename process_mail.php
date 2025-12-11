<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php'; // 設定ファイルや定数の読み込み順依存がある場合は注意

// .envの読み込み
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// 定数定義が必要な場合（functions.phpやconfig.phpで定義されていない場合）
// ここでは、MailService内でSMTP設定定数を使用しているため、
// それらがどこで定義されているか確認が必要だが、通常はconfig.phpなどで定義される想定。
// 既存のコードベースでは明示的なconfig.phpが見当たらず、functions.phpや各ページでrequireしている可能性がある。
// 一旦、MailService内で定数を使っているが、それらの定義元が不明確なため、
// 必要な定数が定義されているか確認し、未定義ならロードする仕組みが必要。
// grep結果からは見えなかったが、composer.jsonのautoload設定からApp名前空間はsrc以下。

// ここでは、既存のページと同様の環境を再現する必要がある。
// もし config.php があるならそれを require する。
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// MailServiceのインスタンス化と実行
use App\Services\MailService;

echo "[" . date('Y-m-d H:i:s') . "] Starting mail queue processing...\n";

$service = new MailService();
$count = $service->processQueue();

echo "[" . date('Y-m-d H:i:s') . "] Processed $count emails.\n";
