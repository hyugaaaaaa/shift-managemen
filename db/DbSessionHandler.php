<?php

class DbSessionHandler implements SessionHandlerInterface {
    private $pdo;
    private $table;

    public function __construct($pdo, $table = 'sessions') {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function open(string $path, string $name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string|false {
        try {
            $stmt = $this->pdo->prepare("SELECT data FROM {$this->table} WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['data'];
            }
            return '';
        } catch (PDOException $e) {
            // エラー時は空文字を返して新しいセッションとして扱う
            error_log("Session read error: " . $e->getMessage());
            return '';
        }
    }

    public function write(string $id, string $data): bool {
        try {
            $timestamp = time();
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (id, data, timestamp) 
                VALUES (:id, :data, :timestamp)
                ON DUPLICATE KEY UPDATE data = :data, timestamp = :timestamp
            ");
            return $stmt->execute([
                ':id' => $id,
                ':data' => $data,
                ':timestamp' => $timestamp
            ]);
        } catch (PDOException $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }

    public function destroy(string $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false {
        try {
            $old = time() - $max_lifetime;
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE timestamp < :old");
            $result = $stmt->execute([':old' => $old]);
            return $result ? $stmt->rowCount() : false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
