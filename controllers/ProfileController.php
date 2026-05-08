<?php
// File: controllers/ProfileController.php

require_once __DIR__ . '/../models/User.php';

class ProfileController {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User(getDB());
    }

    // ================= XEM PROFILE (Tiêu chí 5) =================
    public function index(): void {
        $user_id = $_SESSION['user_id'];
        $user    = $this->userModel->getById($user_id);

        if (!$user) {
            redirect('/logout');
        }

        require __DIR__ . '/../views/profile/profile.php';
    }

    // ================= CẬP NHẬT PROFILE (Tiêu chí 6) =================
    public function update(): void {
        $user_id      = $_SESSION['user_id'];
        $display_name = trim($_POST['display_name'] ?? '');
        $email        = trim($_POST['email'] ?? '');

        if ($display_name === '' || $email === '') {
            $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin!';
            redirect('/profile');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email không hợp lệ!';
            redirect('/profile');
        }

        $existingUser = $this->userModel->getByEmail($email);
        if ($existingUser && $existingUser['id'] !== $user_id) {
            $_SESSION['error'] = 'Email này đã được sử dụng!';
            redirect('/profile');
        }

        if ($this->userModel->updateProfile($user_id, $display_name, $email)) {
            $_SESSION['display_name'] = $display_name;
            $_SESSION['success']      = 'Cập nhật thông tin thành công!';
        } else {
            $_SESSION['error'] = 'Không thể cập nhật thông tin!';
        }

        redirect('/profile');
    }

    // ================= UPLOAD AVATAR (Tiêu chí 6) =================
    public function updateAvatar(): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Không có file ảnh!']);
            exit();
        }

        $file     = $_FILES['avatar'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)!']);
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'Ảnh quá lớn (tối đa 2MB)!']);
            exit();
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
        $savePath = __DIR__ . '/../public/uploads/avatars/' . $filename;

        // ✅ Dùng BASE_URL để URL ảnh đúng môi trường
        $urlPath  = BASE_URL . '/uploads/avatars/' . $filename;

        // Xóa avatar cũ nếu có
        $user = $this->userModel->getById($user_id);
        if ($user['avatar']) {
            // ✅ Cắt BASE_URL ra trước khi tìm file vật lý
            $oldRelative = str_replace(BASE_URL, '', $user['avatar']);
            $oldPath = __DIR__ . '/../public' . $oldRelative;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        if (move_uploaded_file($file['tmp_name'], $savePath)) {
            $this->userModel->updateAvatar($user_id, $urlPath);
            echo json_encode(['status' => 'success', 'url' => $urlPath]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lưu ảnh thất bại!']);
        }
    }

    // ================= ĐỔI MẬT KHẨU (Tiêu chí 7) =================
    public function changePassword(): void {
        $user_id = $_SESSION['user_id'];
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin!';
            redirect('/profile');
        }

        if ($new !== $confirm) {
            $_SESSION['error'] = 'Mật khẩu mới không khớp!';
            redirect('/profile');
        }

        if (strlen($new) < 6) {
            $_SESSION['error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
            redirect('/profile');
        }

        $user = $this->userModel->getById($user_id);
        if (!password_verify($current, $user['password'])) {
            $_SESSION['error'] = 'Mật khẩu hiện tại không đúng!';
            redirect('/profile');
        }

        $hashed = password_hash($new, PASSWORD_BCRYPT);

        if ($this->userModel->changePassword($user_id, $hashed)) {
            $_SESSION['success'] = 'Đổi mật khẩu thành công!';
        } else {
            $_SESSION['error'] = 'Không thể đổi mật khẩu!';
        }

        redirect('/profile');
    }

    // ================= PREFERENCES (Tiêu chí 8) =================
    public function preferences(): void {
        $user_id     = $_SESSION['user_id'];
        $preferences = $this->userModel->getPreferences($user_id);

        require __DIR__ . '/../views/profile/preferences.php';
    }

    public function updatePreferences(): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];

        $preferences = [
            'font_size'  => $_POST['font_size'] ?? 'medium',
            'note_color' => $_POST['note_color'] ?? '#ffffff',
            'theme'      => $_POST['theme'] ?? 'light'
        ];

        if (!in_array($preferences['font_size'], ['small', 'medium', 'large'])) {
            $preferences['font_size'] = 'medium';
        }

        if (!in_array($preferences['theme'], ['light', 'dark'])) {
            $preferences['theme'] = 'light';
        }

        if ($this->userModel->updatePreferences($user_id, $preferences)) {
            $_SESSION['preferences'] = $preferences;
            echo json_encode(['status' => 'success', 'data' => $preferences]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể cập nhật cài đặt!']);
        }
    }
}