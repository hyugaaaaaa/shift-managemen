-- usersテーブルのカラム変更
-- 既存のNotifyトークンはMessaging APIでは使えないため、カラム名変更と共にデータをクリア（NULL化）するのが適切だが、
-- CHANGE COLUMN だけではデータは残るため、別途 UPDATE で NULL にする。

ALTER TABLE users CHANGE COLUMN line_notify_token line_user_id VARCHAR(255) DEFAULT NULL;
UPDATE users SET line_user_id = NULL;
