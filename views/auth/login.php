<?php
// File: views/auth/login.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - NoteApp</title>
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
                        <h5 class="text-muted mt-2 fw-normal">Chào mừng trở lại!</h5>
                    </div>

                    <!-- Flash messages -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-xmark me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <!-- Form -->
                    <form id="loginForm" action="<?= BASE_URL ?>/login" method="POST" novalidate>

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

                        <div class="mb-2">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fa-solid fa-lock me-1 text-muted"></i> Mật khẩu
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control form-control-lg"
                                    id="password"
                                    name="password"
                                    placeholder="Nhập mật khẩu"
                                    required
                                    autocomplete="current-password"
                                >
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        id="togglePassword"
                                        title="Ẩn/hiện mật khẩu">
                                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Link quên mật khẩu (tiêu chí 4) -->
                        <div class="text-end mb-4">
                            <a href="<?= BASE_URL ?>/forgot-password" class="text-decoration-none small text-muted">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <button type="submit" id="btnSubmit" class="btn btn-auth btn-lg w-100 text-white mb-3">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>
                            <span id="btnText">Đăng nhập</span>
                        </button>

                    </form>

                    <hr class="my-3">

                    <div class="text-center">
                        <span class="text-muted">Chưa có tài khoản?</span>
                        <a href="<?= BASE_URL ?>/register" class="text-decoration-none fw-bold ms-1">Đăng ký ngay</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ===== SHOW / HIDE PASSWORD =====
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput  = document.getElementById('password');
    const eyeIcon        = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });

    // ===== LOADING KHI SUBMIT =====
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn     = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');

        // Validate cơ bản trước khi submit
        const email    = document.getElementById('email').value.trim();
        const password = passwordInput.value.trim();

        if (!email || !password) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin!');
            return;
        }

        btn.disabled  = true;
        btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    });
</script>
</body>
</html>