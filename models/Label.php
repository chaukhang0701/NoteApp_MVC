<?php
// File: models/Label.php

class Label {
    private PDO $conn;
    private string $table = "labels";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // ================= LABEL MANAGEMENT (Tiêu chí 18) =================

    // Lấy tất cả label của user
    public function getAllByUser(int $user_id): array {
        $stmt = $this->conn->prepare("
            SELECT l.*, COUNT(nl.note_id) as note_count
            FROM {$this->table} l
            LEFT JOIN note_labels nl ON nl.label_id = l.id
            WHERE l.user_id = ?
            GROUP BY l.id
            ORDER BY l.name ASC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    // Lấy label theo id (kiểm tra quyền sở hữu)
    public function getById(int $id, int $user_id): array|false {
        $stmt = $this->conn->prepare("
            SELECT * FROM {$this->table}
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch();
    }

    // Tạo label mới
    public function create(int $user_id, string $name): int|false {
        // Kiểm tra trùng tên trong cùng user
        if ($this->nameExists($user_id, $name)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (user_id, name)
            VALUES (?, ?)
        ");
        $success = $stmt->execute([$user_id, $name]);
        return $success ? (int)$this->conn->lastInsertId() : false;
    }

    // Đổi tên label (tiêu chí 18)
    // Khi đổi tên → tất cả note gắn label đó tự động cập nhật (vì dùng FK)
    public function update(int $id, int $user_id, string $newName): bool {
        // Kiểm tra trùng tên
        if ($this->nameExists($user_id, $newName, $id)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET name = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$newName, $id, $user_id]);
    }

    // Xóa label — note gắn label này KHÔNG bị ảnh hưởng (ON DELETE CASCADE ở note_labels)
    public function delete(int $id, int $user_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->table}
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, $user_id]);
    }

    // Kiểm tra tên label đã tồn tại chưa
    private function nameExists(
        int $user_id,
        string $name,
        ?int $excludeId = null
    ): bool {
        $sql    = "SELECT id FROM {$this->table} WHERE user_id = ? AND name = ?";
        $params = [$user_id, $name];

        if ($excludeId) {
            $sql     .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // ================= NOTE - LABEL (Tiêu chí 19) =================

    // Gắn label vào note
    public function attachToNote(int $note_id, int $label_id): bool {
        $stmt = $this->conn->prepare("
            INSERT IGNORE INTO note_labels (note_id, label_id)
            VALUES (?, ?)
        ");
        return $stmt->execute([$note_id, $label_id]);
    }

    // Gỡ label khỏi note
    public function detachFromNote(int $note_id, int $label_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM note_labels
            WHERE note_id = ? AND label_id = ?
        ");
        return $stmt->execute([$note_id, $label_id]);
    }

    // Xóa toàn bộ label của 1 note (dùng khi update lại labels)
    public function clearNoteLabels(int $note_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM note_labels WHERE note_id = ?
        ");
        return $stmt->execute([$note_id]);
    }

    // Lấy labels của 1 note
    public function getLabelsByNote(int $note_id): array {
        $stmt = $this->conn->prepare("
            SELECT l.*
            FROM {$this->table} l
            INNER JOIN note_labels nl ON nl.label_id = l.id
            WHERE nl.note_id = ?
            ORDER BY l.name ASC
        ");
        $stmt->execute([$note_id]);
        return $stmt->fetchAll();
    }

    // ================= FILTER (Tiêu chí 20) =================

    // Filter note theo label — có user_id để bảo mật
    public function getNotesByLabel(int $label_id, int $user_id): array {
        $stmt = $this->conn->prepare("
            SELECT n.*
            FROM notes n
            INNER JOIN note_labels nl ON nl.note_id = n.id
            WHERE nl.label_id = ? AND n.user_id = ?
            ORDER BY n.is_pinned DESC, n.pinned_at DESC, n.updated_at DESC
        ");
        $stmt->execute([$label_id, $user_id]);
        return $stmt->fetchAll();
    }
}