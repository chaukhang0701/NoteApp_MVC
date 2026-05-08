<?php
// File: controllers/NoteController.php

require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../models/Label.php';

class NoteController {

    private Note $noteModel;

    public function __construct() {
        $this->noteModel = new Note(getDB());
    }

    // ================= HIỂN THỊ DANH SÁCH (Tiêu chí 9, 10, 16, 17, 20) =================
    public function index(): void {
        $user_id      = $_SESSION['user_id'];
        $label_id     = $_GET['label'] ?? null;
        $keyword      = $_GET['q'] ?? null;
    
        $noteModel    = $this->noteModel;
        $labelModel   = new \Label(getDB());
    
        $notes        = $noteModel->getAllNotes($user_id, $label_id, $keyword);
        $labels       = $labelModel->getAllByUser($user_id);
        $currentLabel = $label_id;
    
        $currentLabelName = '';
        if ($label_id) {
            $found = array_filter($labels, fn($l) => $l['id'] == $label_id);
            $currentLabelName = !empty($found) ? reset($found)['name'] : '';
        }
    
        require __DIR__ . '/../views/notes/list.php';
    }

    // ================= MỞ EDITOR TẠO MỚI (Tiêu chí 11) =================
    // ===== CREATE =====
    public function create(): void {
        $user_id = $_SESSION['user_id'];

        $note       = null;
        $noteLabels = [];
        $noteImages = [];
        $labels     = (new Label(getDB()))->getAllByUser($user_id); // ← THÊM

        require __DIR__ . '/../views/notes/editor.php';
    }

    public function edit(int $id): void {
        $user_id = $_SESSION['user_id'];
        $db      = getDB();
    
        $note = $this->noteModel->getNoteById($id, $user_id);
    
        if (!$note) {
            header("Location: " . BASE_URL . "/notes");
            exit();
        }
    
        // Kiểm tra note có password không
        if ($note['note_password'] !== null) {
            $verified = $_SESSION['note_verified'][$id] ?? false;
    
            if (!$verified) {
                // ✅ Redirect về /notes
                // JS ở list.php sẽ tự hiện modal password
                header("Location: " . BASE_URL . "/notes");
                exit();
            }
        }
    
        $labelModel = new Label($db);
        $labels     = $labelModel->getAllByUser($user_id);
        $noteLabels = $this->noteModel->getLabels($id);
        $noteImages = $this->noteModel->getImages($id);
    
        require __DIR__ . '/../views/notes/editor.php';
    }

    // ================= AUTO-SAVE (Tiêu chí 14) =================
    public function autoSave(): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];
        $id      = $_POST['id'] ?? null;
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($title === '') {
            $title = "Ghi chú không tên";
        }

        $saved_id = $this->noteModel->saveNote(
            $id ? (int)$id : null,
            $user_id,
            $title,
            $content
        );

        if ($saved_id) {
            echo json_encode([
                'status' => 'success',
                'id'     => $saved_id,
                'time'   => date('H:i:s')
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không thể lưu ghi chú'
            ]);
        }
    }

    // ================= XÓA NOTE (Tiêu chí 13) =================
    public function delete(int $id): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];

        if ($this->noteModel->deleteNote($id, $user_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không thể xóa ghi chú'
            ]);
        }
    }

    // ================= PIN / UNPIN (Tiêu chí 16) =================
    public function togglePin(int $id): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];

        if ($this->noteModel->togglePin($id, $user_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không thể ghim ghi chú'
            ]);
        }
    }

    // ================= SEARCH LIVE (Tiêu chí 17) =================
    public function search(): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];
        $keyword = trim($_GET['q'] ?? '');

        $notes = $this->noteModel->searchNotes($user_id, $keyword);

        echo json_encode([
            'status' => 'success',
            'data'   => $notes
        ]);
    }

    // ================= UPLOAD ẢNH (Tiêu chí 15) =================
    public function uploadImage(int $id): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];

        $note = $this->noteModel->getNoteById($id, $user_id);
        if (!$note) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ghi chú']);
            exit();
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Không có file ảnh']);
            exit();
        }

        $file     = $_FILES['image'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Chỉ chấp nhận file ảnh']);
            exit();
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'File quá lớn (tối đa 5MB)']);
            exit();
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $ext;
        $savePath = __DIR__ . '/../public/uploads/images/' . $filename;
        // ✅ Dùng BASE_URL để URL ảnh đúng môi trường
        $urlPath  = BASE_URL . '/uploads/images/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $savePath)) {
            $this->noteModel->addImage($id, $urlPath);
            echo json_encode([
                'status' => 'success',
                'url'    => $urlPath
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lưu ảnh thất bại']);
        }
    }

    // ================= ĐẶT MẬT KHẨU NOTE (Tiêu chí 21, 22) =================
    public function setPassword(int $id): void {
        header('Content-Type: application/json');

        $user_id  = $_SESSION['user_id'];
        $action   = $_POST['action'] ?? '';
        $note     = $this->noteModel->getNoteById($id, $user_id);

        if (!$note) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ghi chú']);
            exit();
        }

        if ($action === 'enable') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if ($password !== $confirm || strlen($password) < 4) {
                echo json_encode(['status' => 'error', 'message' => 'Mật khẩu không hợp lệ']);
                exit();
            }

            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $this->noteModel->setNotePassword($id, $hashed);
            echo json_encode(['status' => 'success', 'message' => 'Đã bật bảo vệ mật khẩu']);

        } elseif ($action === 'change') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!password_verify($current, $note['note_password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Mật khẩu hiện tại không đúng']);
                exit();
            }

            if ($new !== $confirm || strlen($new) < 4) {
                echo json_encode(['status' => 'error', 'message' => 'Mật khẩu mới không hợp lệ']);
                exit();
            }

            $hashed = password_hash($new, PASSWORD_BCRYPT);
            $this->noteModel->setNotePassword($id, $hashed);
            echo json_encode(['status' => 'success', 'message' => 'Đã đổi mật khẩu']);

        } elseif ($action === 'disable') {
            $current = $_POST['current_password'] ?? '';

            if (!password_verify($current, $note['note_password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Mật khẩu không đúng']);
                exit();
            }

            $this->noteModel->setNotePassword($id, null);
            unset($_SESSION['note_verified'][$id]);
            echo json_encode(['status' => 'success', 'message' => 'Đã tắt bảo vệ mật khẩu']);
        }
    }

    // ================= XÁC THỰC MẬT KHẨU NOTE (Tiêu chí 21) =================
    public function verifyPassword(int $id): void {
        header('Content-Type: application/json');

        $user_id  = $_SESSION['user_id'];
        $password = $_POST['password'] ?? '';
        $note     = $this->noteModel->getNoteById($id, $user_id);

        if (!$note) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ghi chú']);
            exit();
        }

        if (password_verify($password, $note['note_password'])) {
            $_SESSION['note_verified'][$id] = true;
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Mật khẩu không đúng']);
        }
    }

    // ================= XÓA ẢNH ĐÍNH KÈM (Tiêu chí 15) =================
    public function deleteImage(int $id): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];

        $note = $this->noteModel->getNoteById($id, $user_id);
        if (!$note) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ghi chú']);
            exit();
        }

        $imageUrl = $_POST['image_url'] ?? '';
        
        if (empty($imageUrl)) {
            echo json_encode(['status' => 'error', 'message' => 'Không có thông tin ảnh cần xóa']);
            exit();
        }

        // ✅ Cắt BASE_URL ra trước khi tìm file vật lý
        $relativePath = str_replace(BASE_URL, '', $imageUrl);
        $filePath = __DIR__ . '/../public' . $relativePath;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if ($this->noteModel->deleteImage($id, $imageUrl)) {
            echo json_encode(['status' => 'success', 'message' => 'Đã xóa ảnh thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể xóa thông tin ảnh trong CSDL']);
        }
    }
}