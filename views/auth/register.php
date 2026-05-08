<?php
// File: views/auth/register.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - NoteApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .auth-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .auth-brand {
            font-size: 2rem;
            font-weight: 800;
            color: #667eea;
        }
        .btn-auth {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
        }
        .btn-auth:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">

            <div class="card auth-card">
                <div class="card-body p-4 p-md-5">

                    <!-- Brand -->
                    <div class="text-center mb-4">
                        <div class="auth-brand">
                            <i class="fa-solid fa-book-open me-2"></i>NoteApp
                        </div>
                        <h5 class="text-muted mt-2 fw-normal">Tạo tài khoản mới</h5>
                    </div>

                    <!-- Flash error từ server -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-xmark me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Form -->
                    <form id="registerForm" action="<?= BASE_URL ?>/register" method="POST" novalidate>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fa-solid fa-envelope me-1 text-muted"></i> Email
                            </label>
                            <input
                                type="email"
                                class="form-control form-control-lg"
                                id="email"
                                name="email"
                                placeholder="ban@email.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required
                                autocomplete="email"
                            >
                        </div>

                        <!-- Tên hiển thị -->
                        <div class="mb-3">
                            <label for="display_name" class="form-label fw-semibold">
                                <i class="fa-solid fa-user me-1 text-muted"></i> Tên hiển thị
                            </label>
                            <input
                                type="text"
                                class="form-control form-control-lg"
                                id="display_name"
                                name="display_name"
                                placeholder="Tên của bạn"
                                value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>"
                                required
                                autocomplete="name"
                            >
                        </div>

                        <!-- Mật khẩu -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fa-solid fa-lock me-1 text-muted"></i> Mật khẩu
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control form-control-lg"
                                    id="password"
                                    name="password"
                                    placeholder="Ít nhất 6 ký tự"
                                    required
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        id="togglePassword"
                                        title="Ẩn/hiện mật khẩu">
                                    <i class="fa-solid fa-eye" id="eyeIcon1"></i>
                                </button>
                            </div>
                            <!-- Strength indicator -->
                            <div class="mt-1">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar" id="strengthBar"
                                         role="progressbar" style="width: 0%">
                                    </div>
                                </div>
                                <small id="strengthText" class="text-muted"></small>
                            </div>
                        </div>

                        <!-- Xác nhận mật khẩu -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="fa-solid fa-lock me-1 text-muted"></i> Xác nhận mật khẩu
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control form-control-lg"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Nhập lại mật khẩu"
                                    required
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        id="toggleConfirmPassword"
                                        title="Ẩn/hiện mật khẩu">
                                    <i class="fa-solid fa-eye" id="eyeIcon2"></i>
                                </button>
                            </div>
                            <!-- Match message -->
                            <div id="passwordMatchMsg" class="form-text mt-1" style="display:none;"></div>
                        </div>

                        <button type="submit"
                                id="btnSubmit"
                                class="btn btn-auth btn-lg w-100 text-white mb-3">
                            <i class="fa-solid fa-user-plus me-2"></i>
                            <span id="btnText">Đăng ký</span>
                        </button>

                    </form>

                    <hr class="my-3">

                    <div class="text-center">
                        <span class="text-muted">Đã có tài khoản?</span>
                        <a href="<?= BASE_URL ?>/login" class="text-decoration-none fw-bold ms-1">Đăng nhập ngay</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ===== SHOW / HIDE PASSWORD =====
    function setupToggle(btnId, inputId, iconId) {
        document.getElementById(btnId).addEventListener('click', () => {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    setupToggle('togglePassword', 'password', 'eyeIcon1');
    setupToggle('toggleConfirmPassword', 'confirm_password', 'eyeIcon2');

    // ===== PASSWORD STRENGTH =====
    const passwordInput  = document.getElementById('password');
    const strengthBar    = document.getElementById('strengthBar');
    const strengthText   = document.getElementById('strengthText');

    passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        let strength = 0;

        if (val.length >= 6)                        strength++;
        if (val.length >= 10)                       strength++;
        if (/[A-Z]/.test(val))                      strength++;
        if (/[0-9]/.test(val))                      strength++;
        if (/[^A-Za-z0-9]/.test(val))              strength++;

        const levels = [
            { width: '0%',   color: '',          text: '' },
            { width: '25%',  color: 'bg-danger',  text: 'Yếu' },
            { width: '50%',  color: 'bg-warning', text: 'Trung bình' },
            { width: '75%',  color: 'bg-info',    text: 'Khá' },
            { width: '100%', color: 'bg-success',  text: 'Mạnh' },
            { width: '100%', color: 'bg-success',  text: 'Rất mạnh' },
        ];

        const level = levels[strength] || levels[0];
        strengthBar.style.width  = level.width;
        strengthBar.className    = `progress-bar ${level.color}`;
        strengthText.textContent = level.text;

        validatePasswordMatch();
    });

    // ===== REAL-TIME PASSWORD MATCH =====
    const confirmInput   = document.getElementById('confirm_password');
    const matchMsg       = document.getElementById('passwordMatchMsg');
    const btnSubmit      = document.getElementById('btnSubmit');

    function validatePasswordMatch() {
        if (confirmInput.value === '') {
            matchMsg.style.display = 'none';
            return;
        }

        matchMsg.style.display = 'block';

        if (passwordInput.value === confirmInput.value) {
            matchMsg.innerHTML   = '<i class="fa-solid fa-check me-1"></i> Mật khẩu trùng khớp';
            matchMsg.className   = 'form-text mt-1 text-success';
            btnSubmit.disabled   = false;
        } else {
            matchMsg.innerHTML   = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Mật khẩu không khớp!';
            matchMsg.className   = 'form-text mt-1 text-danger';
            btnSubmit.disabled   = true;
        }
    }

    confirmInput.addEventListener('input', validatePasswordMatch);

    // ===== LOADING KHI SUBMIT =====
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const email       = document.getElementById('email').value.trim();
        const displayName = document.getElementById('display_name').value.trim();
        const password    = passwordInput.value;
        const confirm     = confirmInput.value;

        // Validate client-side
        if (!email || !displayName || !password || !confirm) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin!');
            return;
        }

        if (password !== confirm) {
            e.preventDefault();
            alert('Mật khẩu không khớp!');
            return;
        }

        if (password.length < 6) {
            e.preventDefault();
            alert('Mật khẩu phải có ít nhất 6 ký tự!');
            return;
        }

        // Loading state
        btnSubmit.disabled         = true;
        document.getElementById('btnText').innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Đang tạo tài khoản...';
    });
</script>
</body>
</html>