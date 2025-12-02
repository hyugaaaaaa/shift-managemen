USE shift_management;

-- usersテーブルにアカウントロック用のカラムを追加
-- 既に存在する場合のエラーを回避するためにプロシージャを使用するか、単純に実行してエラーを確認する
-- ここではシンプルにALTERを実行します。エラーが出たら手動で確認します。
ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0;
ALTER TABLE users ADD COLUMN locked_until DATETIME DEFAULT NULL;

-- システム設定テーブルの作成
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- 初期設定値の投入（シフト提出締め切り日：毎月25日）
INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES ('shift_submission_deadline_day', '25');
