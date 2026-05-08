<?php
// File: controllers/LabelController.php

require_once __DIR__ . '/../models/Label.php';

class LabelController {

    private Label $labelModel;

    public function __construct() {
        $this->labelModel = new Label(getDB());
    }

    // ================= HIỂN THỊ DANH SÁCH LABEL (Tiêu chí 18) =================
    public function index(): void {
        $user_id = $_SESSION['user_id'];
        $labels  = $this->labelModel->getAllByUser($user_id);

        require __DIR__ . '/../views/labels/labels.php';
    }

    // ================= TẠO LABEL MỚI (Tiêu chí 18) =================
    public function store(): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];
        $name    = trim($_POST['name'] ?? '');

        // Validate
        if ($name === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Tên nhãn không được để trống!'
            ]);
            exit();
        }

        if (mb_strlen($name) > 50) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Tên nhãn không được quá 50 ký tự!'
            ]);
            exit();
        }

        $id = $this->labelModel->create($user_id, $name);

        if ($id) {
            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'id'   => $id,
                    'name' => $name
                ]
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Tên nhãn đã tồn tại hoặc không thể tạo!'
            ]);
        }
    }

    // ================= ĐỔI TÊN LABEL (Tiêu chí 18) =================
    public function update(int $id): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];
        $name    = trim($_POST['name'] ?? '');

        // Validate
        if ($name === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Tên nhãn không được để trống!'
            ]);
            exit();
        }

        if (mb_strlen($name) > 50) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Tên nhãn không được quá 50 ký tự!'
            ]);
            exit();
        }

        // Kiểm tra label có thuộc user không
        $label = $this->labelModel->getById($id, $user_id);
        if (!$label) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không tìm thấy nhãn!'
            ]);
            exit();
        }

        if ($this->labelModel->update($id, $user_id, $name)) {
            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'id'   => $id,
                    'name' => $name
                ]
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Tên nhãn đã tồn tại hoặc không thể cập nhật!'
            ]);
        }
    }

    // ================= XÓA LABEL (Tiêu chí 18) =================
    // Lưu ý: xóa label KHÔNG ảnh hưởng đến note (đúng yêu cầu đề)
    public function delete(int $id): void {
        header('Content-Type: application/json');

        $user_id = $_SESSION['user_id'];

        // Kiểm tra label có thuộc user không
        $label = $this->labelModel->getById($id, $user_id);
        if (!$label) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không tìm thấy nhãn!'
            ]);
            exit();
        }

        if ($this->labelModel->delete($id, $user_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không thể xóa nhãn!'
            ]);
        }
    }

    // ================= GẮN LABEL VÀO NOTE (Tiêu chí 19) =================
    public function attachToNote(): void {
        header('Content-Type: application/json');

        $user_id  = $_SESSION['user_id'];
        $note_id  = (int)($_POST['note_id'] ?? 0);
        $label_id = (int)($_POST['label_id'] ?? 0);

        if (!$note_id || !$label_id) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Thiếu thông tin!'
            ]);
            exit();
        }

        // Kiểm tra label có thuộc user không
        $label = $this->labelModel->getById($label_id, $user_id);
        if (!$label) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không tìm thấy nhãn!'
            ]);
            exit();
        }

        if ($this->labelModel->attachToNote($note_id, $label_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không thể gắn nhãn!'
            ]);
        }
    }

    // ================= GỠ LABEL KHỎI NOTE (Tiêu chí 19) =================
    public function detachFromNote(): void {
        header('Content-Type: application/json');

        $user_id  = $_SESSION['user_id'];
        $note_id  = (int)($_POST['note_id'] ?? 0);
        $label_id = (int)($_POST['label_id'] ?? 0);

        if (!$note_id || !$label_id) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Thiếu thông tin!'
            ]);
            exit();
        }

        if ($this->labelModel->detachFromNote($note_id, $label_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Không thể gỡ nhãn!'
            ]);
        }
    }

    // ================= LẤY LABEL CỦA 1 NOTE (Tiêu chí 19) =================
    public function getNoteLabels(int $note_id): void {
        header('Content-Type: application/json');

        $labels = $this->labelModel->getLabelsByNote($note_id);
        echo json_encode([
            'status' => 'success',
            'data'   => $labels
        ]);
    }
}