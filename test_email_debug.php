<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

echo "=== Email Sending Test Script ===\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "Configuration Check:\n";
echo "SMTP_HOST: " . (defined('SMTP_HOST') ? SMTP_HOST : '(Not Defined)') . "\n";
echo "SMTP_PORT: " . (defined('SMTP_PORT') ? SMTP_PORT : '(Not Defined)') . "\n";
echo "SMTP_USER: " . (defined('SMTP_USER') ? SMTP_USER : '(Not Defined)') . "\n";
echo "FROM_EMAIL: " . (defined('FROM_EMAIL') ? FROM_EMAIL : '(Not Defined)') . "\n";
echo "FROM_NAME: " . (defined('FROM_NAME') ? FROM_NAME : '(Not Defined)') . "\n";

echo "\nInitializing PHPMailer...\n";

$mail = new PHPMailer(true);

try {
    // サーバー設定
    // 2 = SMTP::DEBUG_SERVER (クライアントとサーバーのメッセージを表示)
    // 3 = SMTP::DEBUG_CONNECTION (接続レベルの詳細も表示)
    // 4 = SMTP::DEBUG_LOWLEVEL (詳細なローレベル通信を表示)
    $mail->SMTPDebug = 3;                      
    $mail->isSMTP();                                            
    $mail->Host       = SMTP_HOST;                     
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = SMTP_USER;                     
    $mail->Password   = SMTP_PASS;                               
    $mail->SMTPSecure = SMTP_SECURE;            
    $mail->Port       = SMTP_PORT;                                    
    $mail->CharSet    = 'UTF-8';

    // 送信元設定
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    
    // 送信先 (テストのため、送信元と同じアドレスに送るか、引数があればそれを使う)
    $to = defined('FROM_EMAIL') ? FROM_EMAIL : 'test@example.com';
    $mail->addAddress($to);

    // コンテンツ
    $mail->isHTML(false);                                  
    $mail->Subject = 'Test Email from Shift Management System';
    $mail->Body    = "This is a test email sent at " . date('Y-m-d H:i:s') . ".\nIf you receive this, the mail settings are correct.";

    echo "Attempting to connect and send to: $to\n";
    echo "--- SMTP LOG START ---\n";
    
    $mail->send();
    
    echo "--- SMTP LOG END ---\n";
    echo "\nResult: Message has been sent successfully!\n";

} catch (Exception $e) {
    echo "--- SMTP LOG END ---\n";
    echo "\nResult: Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
}
