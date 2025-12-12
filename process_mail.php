<?php
// Debug log
$logFile = __DIR__ . '/mail_process.log';
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Process triggered\n", FILE_APPEND);

require_once __DIR__ . '/config.php';

// MailServiceのインスタンス化と実行
use App\Services\MailService;

echo "[" . date('Y-m-d H:i:s') . "] Starting mail queue processing...\n";

$service = new MailService();
$count = $service->processQueue();

echo "[" . date('Y-m-d H:i:s') . "] Processed $count emails.\n";
