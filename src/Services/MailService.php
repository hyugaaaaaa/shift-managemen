<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $pdo;

    public function __construct()
    {
        // config.php で定義されている getPDO() を利用して接続を取得
        // これにより環境変数の差異問題を回避し、接続を共有できる
        if (function_exists('getPDO')) {
            $this->pdo = getPDO();
        } else {
            // 他のコンテキストで呼ばれた場合などのフォールバック (基本的にはありえないはず)
            error_log("MailService Error: getPDO function not found.");
            $this->pdo = null;
        }
    }

    /**
     * メールを送信キューに追加する（非同期送信）
     * @param string $to 送信先アドレス
     * @param string $subject 件名
     * @param string $body 本文
     * @return bool キュー追加成功ならtrue
     */
    public function queue(string $to, string $subject, string $body): bool
    {
        if (!$this->pdo) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO mail_queue (to_email, subject, body, status, created_at) VALUES (:to, :subject, :body, 'pending', NOW())");
            $stmt->execute([
                ':to' => $to,
                ':subject' => $subject,
                ':body' => $body
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("Mail Queue Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * キューに溜まったメールを送信処理する
     * @return int 送信した件数
     */
    public function processQueue(): int
    {
        if (!$this->pdo) {
            return 0;
        }

        // 未送信のメールを取得（古い順）
        // 一度の実行で処理する件数を制限する（例: 10件）
        $stmt = $this->pdo->prepare("SELECT * FROM mail_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 10");
        $stmt->execute();
        $emails = $stmt->fetchAll();

        $count = 0;
        foreach ($emails as $email) {
            $success = $this->send($email['to_email'], $email['subject'], $email['body']);
            
            if ($success) {
                $updateStmt = $this->pdo->prepare("UPDATE mail_queue SET status = 'sent', sent_at = NOW() WHERE id = :id");
                $updateStmt->execute([':id' => $email['id']]);
                $count++;
            } else {
                $updateStmt = $this->pdo->prepare("UPDATE mail_queue SET status = 'failed', error_message = 'Failed to send' WHERE id = :id");
                $updateStmt->execute([':id' => $email['id']]);
            }
        }

        return $count;
    }

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
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
