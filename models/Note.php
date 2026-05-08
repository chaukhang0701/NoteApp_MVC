<?php
// File: models/Note.php

class Note {
    private PDO $conn;
    private string $table = "notes";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // ================= AUTO-SAVE (Tiêu chí 14) =================
    public function saveNote(
        ?int $id,
        int $user_id,
        string $title,
        string $content
    ): int|false {

        if (empty($id)) {
            // CREATE
            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table} (user_id, title, content)
                VALUES (?, ?, ?)
            ");
            $success = $stmt->execute([$user_id, $title, $content]);
            return $success ? (int)$this->conn->lastInsertId() : false;

        } else {
            // UPDATE — chỉ update note của chính user
            $stmt = $this->conn->prepare("
                UPDATE {$this->table}
                SET title = ?, content = ?
                WHERE id = ? AND user_id = ?
            ");
            $success = $stmt->execute([$title, $content, $id, $user_id]);
            return $success ? $id : false;
        }
    }

    // ================= GET ALL (Tiêu chí 9, 10, 16, 20) =================
    public function getAllNotes(
        int $user_id,
        ?int $label_id = null,
        ?string $keyword = null
    ): array {

        // Base query — join label nếu cần filter
        $sql = "
            SELECT DISTINCT n.*
            FROM {$this->table} n
        ";

        $params = [];

        // Filter theo label (tiêu chí 20)
        if ($label_id) {
            $sql .= " INNER JOIN note_labels nl ON nl.note_id = n.id
                      AND nl.label_id = ? ";
            $params[] = $label_id;
        }

        $sql .= " WHERE n.user_id = ? ";
        $params[] = $user_id;

        // Search keyword (tiêu chí 17)
        if ($keyword && $keyword !== '') {
            $sql .= " AND (n.title LIKE ? OR n.content LIKE ?) ";
            $like = '%' . $keyword . '%';
            $params[] = $like;
            $params[] = $like;
        }

        // Sort: pinned trước (theo pinned_at), sau đó mới nhất (tiêu chí 16)
        $sql .= " ORDER BY n.is_pinned DESC, n.pinned_at DESC, n.updated_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ================= GET BY ID (Tiêu chí 12, 21) =================
    public function getNoteById(int $id, int $user_id): array|false {
        $stmt = $this->conn->prepare("
            SELECT * FROM {$this->table}
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch();
    }

    // ================= SEARCH (Tiêu chí 17) =================
    public function searchNotes(int $user_id, string $keyword): array {
        $like = '%' . $keyword . '%';
        $stmt = $this->conn->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = ?
            AND (title LIKE ? OR content LIKE ?)
            ORDER BY is_pinned DESC, updated_at DESC
        ");
        $stmt->execute([$user_id, $like, $like]);
        return $stmt->fetchAll();
    }

    // ================= DELETE (Tiêu chí 13) =================
    public function deleteNote(int $id, int $user_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->table}
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, $user_id]);
    }

    // ================= PIN / UNPIN (Tiêu chí 16) =================
    public function togglePin(int $id, int $user_id): bool {
        // Lấy trạng thái hiện tại
        $note = $this->getNoteById($id, $user_id);
        if (!$note) return false;

        $newPinned  = $note['is_pinned'] ? 0 : 1;
        // Lưu thời điểm pin để sort đúng thứ tự
        $pinnedAt   = $newPinned ? date('Y-m-d H:i:s') : null;

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET is_pinned = ?, pinned_at = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$newPinned, $pinnedAt, $id, $user_id]);
    }

    // ================= NOTE PASSWORD (Tiêu chí 21, 22) =================
    public function setNotePassword(int $id, ?string $hashedPassword): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET note_password = ?
            WHERE id = ?
        ");
        return $stmt->execute([$hashedPassword, $id]);
    }

    // ================= IMAGES (Tiêu chí 15) =================
    public function addImage(int $note_id, string $path): bool {
        $stmt = $this->conn->prepare("
            INSERT INTO note_images (note_id, path)
            VALUES (?, ?)
        ");
        return $stmt->execute([$note_id, $path]);
    }

    public function getImages(int $note_id): array {
        $stmt = $this->conn->prepare("
            SELECT * FROM note_images
            WHERE note_id = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$note_id]);
        return $stmt->fetchAll();
    }

    public function deleteImage(int $image_id, int $note_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM note_images
            WHERE id = ? AND note_id = ?
        ");
        return $stmt->execute([$image_id, $note_id]);
    }

    // ================= LABELS (Tiêu chí 19) =================
    public function getLabels(int $note_id): array {
        $stmt = $this->conn->prepare("
            SELECT l.* FROM labels l
            INNER JOIN note_labels nl ON nl.label_id = l.id
            WHERE nl.note_id = ?
        ");
        $stmt->execute([$note_id]);
        return $stmt->fetchAll();
    }

    public function addLabel(int $note_id, int $label_id): bool {
        // Tránh trùng lặp
        $stmt = $this->conn->prepare("
            INSERT IGNORE INTO note_labels (note_id, label_id)
            VALUES (?, ?)
        ");
        return $stmt->execute([$note_id, $label_id]);
    }

    public function removeLabel(int $note_id, int $label_id): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM note_labels
            WHERE note_id = ? AND label_id = ?
        ");
        return $stmt->execute([$note_id, $label_id]);
    }
}