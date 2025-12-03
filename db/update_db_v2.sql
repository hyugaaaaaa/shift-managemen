-- お知らせテーブル
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- LINE Notifyトークン用カラム追加
-- 既に存在する場合はエラーになる可能性があるため、存在チェックを入れるのが理想だが、
-- MariaDBのバージョンによっては IF NOT EXISTS が使えない場合があるため、
-- シンプルに ALTER TABLE を実行し、失敗したら無視する運用とする（または手動確認）。
-- ここでは安全策として、プロシージャ等は使わず直接実行する。
ALTER TABLE users ADD COLUMN line_notify_token VARCHAR(255) DEFAULT NULL;
