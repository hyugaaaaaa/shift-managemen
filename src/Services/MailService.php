<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    /**
     * メールを送信する
     * @param string $to 送信先アドレス
     * @param string $subject 件名
     * @param string $body 本文
     * @return bool 送信成功ならtrue
     */
    public function send(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            // 文字コード設定
            $mail->CharSet = 'UTF-8';
            
            // SMTP設定
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            // 送信元・送信先
            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($to);

            // コンテンツ
            $mail->isHTML(false); // テキストメール
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // エラーログを残すなりする
            // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
