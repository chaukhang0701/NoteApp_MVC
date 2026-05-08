<?php
// File: models/Share.php

class Share {
    private PDO $conn;
    private string $table = "shares";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // ================= CHIA SẺ NOTE (Tiêu chí 23) =================

    // Tạo share mới
    public function create(
        int $note_id,
        int $owner_id,
        int $recipient_id,
        string $permission = 'read'
    ): int|false {
        // Kiểm tra đã share cho người này chưa
        if ($this->exists($note_id, $recipient_id)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (note_id, owner_id, recipient_id, permission)
            VALUES (?, ?, ?, ?)
        ");
        $success = $stmt->execute([$note_id, $owner_id, $recipient_id, $permission]);
        return $success ? (int)$this->conn->lastInsertId() : false;
    }

    // Kiểm tra đã share chưa
    public function exists(int $note_id, int $recipient_id): bool {
        $stmt = $this->conn->prepare("
            SELECT id FROM {$this->table}
            WHERE note_id = ? AND recipient_id = ?
            LIMIT 1
        ");
        $stmt->execute([$note_id, $recipient_id]);
        return $stmt->rowCount() > 0;
    }

    // Lấy share theo id
    public function getById(int $id): array|false {
        $stmt = $this->conn->prepare("
            SELECT s.*, 
                   u.email as recipient_email,
                   u.display_name as recipient_name,
                   u.avatar as recipient_avatar,
                   n.title as note_title
            FROM {$this->table} s
            INNER JOIN users u ON u.id = s.recipient_id
            INNER JOIN notes n ON n.id = s.note_id
            WHERE s.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Lấy danh sách người được share 1 note (cho owner xem)
    public function getSharesByNote(int $note_id, int $owner_id): array {
        $stmt = $this->conn->prepare("
            SELECT s.*,
                   u.email as recipient_email,
                   u.display_name as recipient_name,
                   u.avatar as recipient_avatar
            FROM {$this->table} s
            INNER JOIN users u ON u.id = s.recipient_id
            WHERE s.note_id = ? AND s.owner_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$note_id, $owner_id]);
        return $stmt->fetchAll();
    }

    // Lấy danh sách note được share cho 1 user (cho recipient xem)
    public function getSharedWithUser(int $recipient_id): array {
        $stmt = $this->conn->prepare("
            SELECT s.*,
                   n.title as note_title,
                   n.content as note_content,
                   n.updated_at as note_updated_at,
                   u.email as owner_email,
                   u.display_name as owner_name,
                   u.avatar as owner_avatar
            FROM {$this->table} s
            INNER JOIN notes n ON n.id = s.note_id
            INNER JOIN users u ON u.id = s.owner_id
            WHERE s.recipient_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$recipient_id]);
        return $stmt->fetchAll();
    }

    // Cập nhật quyền share (tiêu chí 23)
    public function updatePermission(
        int $id,
        int $owner_id,
        string $permission
    ): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET permission = ?
            WHERE id = ? AND owner_id = ?
        ");
        return $stmt->execute([$permission, $id, $owner_id]);
    }

    // Thu hồi quyền share (tiêu chí 23)
    public function revoke(int $id, int $owner_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->table}
            WHERE id = ? AND owner_id = ?
        ");
        return $stmt->execute([$id, $owner_id]);
    }

    // Thu hồi toàn bộ share của 1 note
    public function revokeAll(int $note_id, int $owner_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->table}
            WHERE note_id = ? AND owner_id = ?
        ");
        return $stmt->execute([$note_id, $owner_id]);
    }

    // Kiểm tra user có quyền truy cập note không
    public function getPermission(
        int $note_id,
        int $user_id
    ): string|false {
        $stmt = $this->conn->prepare("
            SELECT permission FROM {$this->table}
            WHERE note_id = ? AND recipient_id = ?
            LIMIT 1
        ");
        $stmt->execute([$note_id, $user_id]);
        $row = $stmt->fetch();
        return $row ? $row['permission'] : false;
    }
    public function getOwnerId(int $note_id): int {
        $stmt = $this->conn->prepare("
            SELECT owner_id FROM {$this->table}
            WHERE note_id = ? LIMIT 1
        ");
        $stmt->execute([$note_id]);
        $row = $stmt->fetch();
        return $row ? (int)$row['owner_id'] : 0;
    }
}