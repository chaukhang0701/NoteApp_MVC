<?php
// File: controllers/AuthController.php

// Ở đầu AuthController.php thêm:
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User(getDB());
    }

    // ================= REGISTER =================
    public function register(): void {
        $email            = trim($_POST['email'] ?? '');
        $display_name     = trim($_POST['display_name'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($email === '' || $display_name === '' || $password === '') {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
            redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email không hợp lệ!";
            redirect('/register');
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
            redirect('/register');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
            redirect('/register');
        }

        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = "Email này đã được sử dụng!";
            redirect('/register');
        }

        $activationToken = bin2hex(random_bytes(32));
        $hashedPassword  = password_hash($password, PASSWORD_BCRYPT);

        $userId = $this->userModel->create(
            $email,
            $display_name,
            $hashedPassword,
            $activationToken
        );

        if ($userId) {
            $_SESSION['user_id']      = $userId;
            $_SESSION['display_name'] = $display_name;
            $_SESSION['is_activated'] = 0;

            $this->sendActivationEmail($email, $activationToken);
            redirect('/notes');
        }

        $_SESSION['error'] = "Hệ thống đang bận, vui lòng thử lại!";
        redirect('/register');
    }
    

    // ================= LOGIN =================
    public function login(): void {
        // DEBUG TẠM - XÓA SAU KHI FIX
        error_log("LOGIN called, BASE_URL=" . BASE_URL);
        error_log("POST data: " . print_r($_POST, true));
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
            redirect('/login');
        }

        $user = $this->userModel->getByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Email hoặc mật khẩu không chính xác!";
            redirect('/login');
        }

        $_SESSION['user_id']      = $user['id'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['is_activated'] = $user['is_activated'];

        redirect('/notes');
    }

    // ================= LOGOUT =================
    public function logout(): void {
        session_unset();
        session_destroy();
        redirect('/login');
    }

    // ================= ACTIVATE =================
    public function activate(): void {
        $token = $_GET['token'] ?? '';

        if ($token === '') {
            redirect('/notes');
        }

        $success = $this->userModel->activateByToken($token);

        if ($success) {
            $_SESSION['is_activated'] = 1;
            $_SESSION['success'] = "Tài khoản đã được kích hoạt thành công!";
        } else {
            $_SESSION['error'] = "Link kích hoạt không hợp lệ hoặc đã hết hạn!";
        }

        redirect('/notes');
    }

    // ================= FORGOT PASSWORD =================
    public function forgotPassword(): void {
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Email không hợp lệ!']);
            exit();
        }

        $user = $this->userModel->getByEmail($email);

        if ($user) {
            $otp     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $this->userModel->saveResetToken($email, $otp, $expires);
            $this->sendOtpEmail($email, $otp);
        }

        echo json_encode(['status' => 'success', 'message' => 'Mã OTP đã được gửi nếu email tồn tại!']);
    }

    // ================= VERIFY OTP =================
    public function verifyOtp(): void {
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');
        $otp   = trim($_POST['otp'] ?? '');

        if ($email === '' || $otp === '') {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin!']);
            exit();
        }

        $reset = $this->userModel->getResetTokenByEmail($email);

        if (!$reset) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy yêu cầu đặt lại mật khẩu!']);
            exit();
        }

        if (strtotime($reset['expires_at']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Mã OTP đã hết hạn! Vui lòng gửi lại.']);
            exit();
        }

        if ($reset['token'] !== $otp) {
            echo json_encode(['status' => 'error', 'message' => 'Mã OTP không đúng!']);
            exit();
        }

        echo json_encode(['status' => 'success', 'token' => $reset['token']]);
    }

    // ================= RESET PASSWORD =================
    public function resetPassword(): void {
        $token            = $_POST['token'] ?? '';
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
            redirect('/reset-password?token=' . $token);
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
            redirect('/reset-password?token=' . $token);
        }

        $reset = $this->userModel->getResetToken($token);

        if (!$reset || strtotime($reset['expires_at']) < time()) {
            $_SESSION['error'] = "Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!";
            redirect('/forgot-password');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->userModel->updatePassword($reset['email'], $hashedPassword);
        $this->userModel->deleteResetToken($token);

        $_SESSION['success'] = "Đặt lại mật khẩu thành công! Vui lòng đăng nhập.";
        redirect('/login');
    }

    // ================= HELPERS =================
    

    // ===== Thay sendActivationEmail() =====
    private function sendActivationEmail(
        string $email,
        string $token
    ): void {
        $link = "http://" . $_SERVER['HTTP_HOST'] . BASE_URL . "/activate?token=$token";
        $subject = "Kích hoạt tài khoản NoteApp";
        $body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;'>
                <h2 style='color:#667eea;'>Chào mừng đến với NoteApp!</h2>
                <p>Click vào nút bên dưới để kích hoạt tài khoản:</p>
                <a href='$link'
                style='display:inline-block;padding:12px 24px;
                        background:#667eea;color:white;
                        border-radius:8px;text-decoration:none;
                        font-weight:bold;'>
                    Kích hoạt tài khoản
                </a>
                <p style='color:#999;font-size:13px;margin-top:20px;'>
                    Nếu bạn không đăng ký, hãy bỏ qua email này.
                </p>
            </div>";

        sendMail($email, $email, $subject, $body);
    }

    // ===== Thay sendOtpEmail() =====
    private function sendOtpEmail(
        string $email,
        string $otp
    ): void {
        $subject = "Mã OTP đặt lại mật khẩu NoteApp";
        $body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;'>
                <h2 style='color:#667eea;'>Đặt lại mật khẩu</h2>
                <p>Mã OTP của bạn là:</p>
                <div style='font-size:2rem;font-weight:bold;
                            letter-spacing:8px;color:#667eea;
                            padding:16px;background:#f5f5f5;
                            border-radius:8px;text-align:center;'>
                    $otp
                </div>
                <p style='color:#999;font-size:13px;margin-top:20px;'>
                    Mã có hiệu lực trong <b>5 phút</b>.<br>
                    Nếu bạn không yêu cầu, hãy bỏ qua email này.
                </p>
            </div>";

        sendMail($email, $email, $subject, $body);
    }
    private function sendResetEmail(string $email, string $token): void {
        $base    = 'http://' . $_SERVER['HTTP_HOST'] . BASE_URL;
        $link    = $base . '/reset-password?token=' . $token;
        $subject = "Đặt lại mật khẩu NoteApp";
        $message = "Click vào link sau để đặt lại mật khẩu (hết hạn sau 1 giờ): $link";
        $headers = "From: no-reply@noteapp.com";
        mail($email, $subject, $message, $headers);
    }
}