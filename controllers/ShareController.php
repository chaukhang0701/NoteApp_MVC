<?php
// File: controllers/ShareController.php

// Ở đầu ShareController.php thêm:
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../models/Share.php';
require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../models/User.php';

class ShareController {

    private Share $shareModel;
    private Note $noteModel;
    private User $userModel;

    public function __construct() {
        $db = getDB();
        $this->shareModel = new Share($db);
        $this->noteModel  = new Note($db);
        $this->userModel  = new User($db);
    }

    // ================= DANH SÁCH NOTE ĐƯỢC SHARE (Tiêu chí 23) =================
    public function index(): void {
        $user_id     = $_SESSION['user_id'];
        $sharedNotes = $this->shareModel->getSharedWithUser($user_id);

        require __DIR__ . '/../views/shared/shared.php';
    }

    // ================= CHIA SẺ NOTE (Tiêu chí 23) =================
    public function share(int $note_id): void {
        header('Content-Type: application/json');

        $owner_id   = $_SESSION['user_id'];
        $email      = trim($_POST['email'] ?? '');
        $permission = $_POST['permission'] ?? 'read';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Email không hợp lệ!']);
            exit();
        }

        if (!in_array($permission, ['read', 'edit'])) {
            echo json_encode(['status' => 'error', 'message' => 'Quyền không hợp lệ!']);
            exit();
        }

        $note = $this->noteModel->getNoteById($note_id, $owner_id);
        if (!$note) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ghi chú!']);
            exit();
        }

        $recipient = $this->userModel->getByEmail($email);
        if (!$recipient) {
            echo json_encode(['status' => 'error', 'message' => 'Email này chưa đăng ký tài khoản!']);
            exit();
        }

        if ($recipient['id'] === $owner_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không thể chia sẻ cho chính mình!']);
            exit();
        }

        $share_id = $this->shareModel->create(
            $note_id,
            $owner_id,
            $recipient['id'],
            $permission
        );

        if ($share_id) {
            $this->sendShareNotification(
                $email,
                $recipient['display_name'],
                $note['title'],
                $_SESSION['display_name'],
                $permission
            );

            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'share_id'        => $share_id,
                    'recipient_email' => $email,
                    'recipient_name'  => $recipient['display_name'],
                    'permission'      => $permission,
                    'shared_at'       => date('d/m/Y H:i')
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ghi chú đã được chia sẻ với người này rồi!']);
        }
    }

    // ================= CẬP NHẬT QUYỀN (Tiêu chí 23) =================
    public function updatePermission(int $share_id): void {
        header('Content-Type: application/json');

        $owner_id   = $_SESSION['user_id'];
        $permission = $_POST['permission'] ?? '';

        if (!in_array($permission, ['read', 'edit'])) {
            echo json_encode(['status' => 'error', 'message' => 'Quyền không hợp lệ!']);
            exit();
        }

        if ($this->shareModel->updatePermission($share_id, $owner_id, $permission)) {
            echo json_encode(['status' => 'success', 'data' => ['permission' => $permission]]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể cập nhật quyền!']);
        }
    }

    // ================= THU HỒI QUYỀN (Tiêu chí 23) =================
    public function revoke(int $share_id): void {
        header('Content-Type: application/json');

        $owner_id = $_SESSION['user_id'];

        $share = $this->shareModel->getById($share_id);
        if (!$share || $share['owner_id'] !== $owner_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông tin chia sẻ!']);
            exit();
        }

        if ($this->shareModel->revoke($share_id, $owner_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể thu hồi quyền!']);
        }
    }

    // ================= XEM DANH SÁCH NGƯỜI ĐƯỢC SHARE (Tiêu chí 23) =================
    public function getShareList(int $note_id): void {
        header('Content-Type: application/json');

        $owner_id = $_SESSION['user_id'];

        $note = $this->noteModel->getNoteById($note_id, $owner_id);
        if (!$note) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ghi chú!']);
            exit();
        }

        $shares = $this->shareModel->getSharesByNote($note_id, $owner_id);
        echo json_encode(['status' => 'success', 'data' => $shares]);
    }

    // ================= XEM NOTE ĐƯỢC SHARE (read-only) =================
    // ================= XEM NOTE ĐƯỢC SHARE (read-only) =================
    public function viewShared(int $note_id): void {
        $user_id    = $_SESSION['user_id'];
        $db         = getDB(); // ✅ Khai báo $db trướcS

        $permission = $this->shareModel->getPermission($note_id, $user_id);

        if (!$permission) {
            header('Location: /shared'); // ✅ Dùng header thay vì redirect()
            exit();
        }

        $owner_id   = $this->shareModel->getOwnerId($note_id);
        $note       = $this->noteModel->getNoteById($note_id, $owner_id);
        $noteImages = $this->noteModel->getImages($note_id);
        $noteLabels = $this->noteModel->getLabels($note_id); // ✅ Thêm dòng này
        $labels     = (new Label($db))->getAllByUser($user_id); // ✅ Dùng $db
        $readOnly   = true;

        require __DIR__ . '/../views/notes/editor.php';
    }

    // ================= EDIT NOTE ĐƯỢC SHARE (edit permission) =================
    public function editShared(int $note_id): void {
        $user_id    = $_SESSION['user_id'];
        $db         = getDB(); // ✅ Khai báo $db trước

        $permission = $this->shareModel->getPermission($note_id, $user_id);

        if ($permission !== 'edit') {
            header('Location: /shared'); // ✅ Dùng header thay vì redirect()
            exit();
        }

        $owner_id   = $this->shareModel->getOwnerId($note_id);
        $note       = $this->noteModel->getNoteById($note_id, $owner_id);
        $noteImages = $this->noteModel->getImages($note_id);
        $noteLabels = $this->noteModel->getLabels($note_id);
        $labels     = (new Label($db))->getAllByUser($user_id); // ✅ Chỉ 1 lần
        $readOnly   = false;

        require __DIR__ . '/../views/notes/editor.php';
    }

    // ================= HELPER: Gửi email thông báo =================
        // Thay sendShareNotification()
    private function sendShareNotification(
        string $recipientEmail,
        string $recipientName,
        string $noteTitle,
        string $ownerName,
        string $permission
    ): void {
        $permissionText = $permission === 'edit'
            ? 'chỉnh sửa'
            : 'chỉ đọc';
        $link    = "http://" . $_SERVER['HTTP_HOST'] . "/shared";
        $subject = "$ownerName đã chia sẻ ghi chú với bạn";
        $body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;'>
                <h2 style='color:#667eea;'>Ghi chú được chia sẻ</h2>
                <p>
                    <b>$ownerName</b> đã chia sẻ ghi chú
                    <b>\"$noteTitle\"</b> với bạn.
                </p>
                <p>
                    Quyền truy cập:
                    <span style='color:#667eea;font-weight:bold;'>
                        $permissionText
                    </span>
                </p>
                <a href='$link'
                style='display:inline-block;padding:12px 24px;
                        background:#667eea;color:white;
                        border-radius:8px;text-decoration:none;
                        font-weight:bold;'>
                    Xem ghi chú
                </a>
                <p style='color:#999;font-size:13px;margin-top:20px;'>
                    Đăng nhập vào NoteApp để xem chi tiết.
                </p>
            </div>";

        sendMail($recipientEmail, $recipientName, $subject, $body);
    }
}