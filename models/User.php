<?php
// File: models/User.php

class User {
    private PDO $conn;
    private string $table = "users";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // ================= AUTH =================

    // Kiểm tra email đã tồn tại chưa
    public function emailExists(string $email): bool {
        $stmt = $this->conn->prepare(
            "SELECT id FROM {$this->table} WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    // Tạo tài khoản mới — trả về id để tự động login (tiêu chí 1)
    public function create(
        string $email,
        string $display_name,
        string $hashedPassword,
        string $activationToken
    ): int|false {
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} 
                (email, display_name, password, activation_token, is_activated)
            VALUES (?, ?, ?, ?, 0)
        ");

        $success = $stmt->execute([
            $email,
            $display_name,
            $hashedPassword,
            $activationToken
        ]);

        return $success ? (int)$this->conn->lastInsertId() : false;
    }

    // Lấy user theo email
    public function getByEmail(string $email): array|false {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Lấy user theo id
    public function getById(int $id): array|false {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ================= ACTIVATION (Tiêu chí 2) =================

    // Kích hoạt tài khoản bằng token
    public function activateByToken(string $token): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET is_activated = 1, activation_token = NULL
            WHERE activation_token = ? AND is_activated = 0
        ");
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    // ================= PASSWORD RESET (Tiêu chí 4) =================

    // Lưu token reset password vào bảng password_resets
    public function saveResetToken(
        string $email,
        string $token,
        string $expires
    ): bool {
        // Xóa token cũ nếu có
        $this->deleteResetTokenByEmail($email);

        $stmt = $this->conn->prepare("
            INSERT INTO password_resets (email, token, expires_at)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$email, $token, $expires]);
    }

    // Lấy thông tin token reset
    public function getResetToken(string $token): array|false {
        $stmt = $this->conn->prepare("
            SELECT * FROM password_resets
            WHERE token = ? LIMIT 1
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    // Xóa token reset sau khi dùng
    public function deleteResetToken(string $token): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM password_resets WHERE token = ?"
        );
        return $stmt->execute([$token]);
    }

    // Xóa token reset theo email
    private function deleteResetTokenByEmail(string $email): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM password_resets WHERE email = ?"
        );
        return $stmt->execute([$email]);
    }

    // Cập nhật mật khẩu mới
    public function updatePassword(string $email, string $hashedPassword): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET password = ?
            WHERE email = ?
        ");
        return $stmt->execute([$hashedPassword, $email]);
    }

    // ================= PROFILE (Tiêu chí 5, 6, 7) =================

    // Cập nhật thông tin profile
    public function updateProfile(
        int $id,
        string $display_name,
        string $email
    ): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET display_name = ?, email = ?
            WHERE id = ?
        ");
        return $stmt->execute([$display_name, $email, $id]);
    }

    // Cập nhật avatar
    public function updateAvatar(int $id, string $avatarPath): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET avatar = ?
            WHERE id = ?
        ");
        return $stmt->execute([$avatarPath, $id]);
    }

    // Đổi mật khẩu (tiêu chí 7)
    public function changePassword(int $id, string $hashedPassword): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET password = ?
            WHERE id = ?
        ");
        return $stmt->execute([$hashedPassword, $id]);
    }

    // ================= PREFERENCES (Tiêu chí 8) =================

    // Cập nhật preferences (font size, màu, dark/light mode)
    public function updatePreferences(int $id, array $preferences): bool {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET preferences = ?
            WHERE id = ?
        ");
        return $stmt->execute([json_encode($preferences), $id]);
    }

    // Lấy preferences
    public function getPreferences(int $id): array {
        $user = $this->getById($id);
        if (!$user || empty($user['preferences'])) {
            // Trả về giá trị mặc định
            return [
                'font_size'  => 'medium',
                'note_color' => '#ffffff',
                'theme'      => 'light'
            ];
        }
        return json_decode($user['preferences'], true);
    }
    // Lấy reset token theo email (dùng cho verify OTP)
    public function getResetTokenByEmail(string $email): array|false {
        $stmt = $this->conn->prepare("
            SELECT * FROM password_resets
            WHERE email = ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}