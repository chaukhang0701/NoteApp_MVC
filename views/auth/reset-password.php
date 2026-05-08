<?php
// File: views/auth/reset-password.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/database.php';

// Lấy token từ URL
$token = $_GET['token'] ?? '';

// Nếu không có token → redirect về forgot-password
if (empty($token)) {
    header('Location: ' . BASE_URL . '/forgot-password');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - NoteApp</title>
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
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s;
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
                        <h5 class="text-muted mt-2 fw-normal">Đặt lại mật khẩu</h5>
                        <p class="text-muted small">
                            Nhập mật khẩu mới cho tài khoản của bạn
                        </p>
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
                    <form id="resetForm"
                          action="<?= BASE_URL ?>/reset-password"
                          method="POST"
                          novalidate>

                        <!-- Token ẩn -->
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <!-- Mật khẩu mới -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fa-solid fa-lock me-1 text-muted"></i>
                                Mật khẩu mới
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
                                        onclick="togglePw('password', 'eye1')">
                                    <i class="fa-solid fa-eye" id="eye1"></i>
                                </button>
                            </div>

                            <!-- Password strength -->
                            <div class="mt-2">
                                <div class="progress" style="height:4px;">
                                    <div id="strength-bar"
                                         class="progress-bar strength-bar"
                                         style="width:0%;">
                                    </div>
                                </div>
                                <small id="strength-text" class="text-muted"></small>
                            </div>
                        </div>

                        <!-- Xác nhận mật khẩu -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="fa-solid fa-lock me-1 text-muted"></i>
                                Xác nhận mật khẩu mới
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control form-control-lg"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Nhập lại mật khẩu mới"
                                    required
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        onclick="togglePw('confirm_password', 'eye2')">
                                    <i class="fa-solid fa-eye" id="eye2"></i>
                                </button>
                            </div>

                            <!-- Match message -->
                            <div id="match-msg"
                                 class="form-text mt-1"
                                 style="display:none;">
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                                id="btnReset"
                                class="btn btn-auth btn-lg w-100 text-white mb-3"
                                disabled>
                            <i class="fa-solid fa-key me-2"></i>
                            <span id="btnText">Đặt lại mật khẩu</span>
                        </button>

                    </form>

                    <hr class="my-3">

                    <div class="text-center">
                        <a href="<?= BASE_URL ?>/login"
                           class="text-decoration-none text-muted small">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Quay lại đăng nhập
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const passwordInput = document.getElementById('password');
const confirmInput  = document.getElementById('confirm_password');
const matchMsg      = document.getElementById('match-msg');
const btnReset      = document.getElementById('btnReset');
const strengthBar   = document.getElementById('strength-bar');
const strengthText  = document.getElementById('strength-text');

// ===== SHOW / HIDE PASSWORD =====
function togglePw(inputId, iconId) {
    const input  = document.getElementById(inputId);
    const icon   = document.getElementById(iconId);
    const isPass = input.type === 'password';
    input.type   = isPass ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

// ===== PASSWORD STRENGTH =====
passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    let strength = 0;

    if (val.length >= 6)               strength++;
    if (val.length >= 10)              strength++;
    if (/[A-Z]/.test(val))            strength++;
    if (/[0-9]/.test(val))            strength++;
    if (/[^A-Za-z0-9]/.test(val))    strength++;

    const levels = [
        { width: '0%',   color: '',           text: ''          },
        { width: '25%',  color: 'bg-danger',  text: 'Yếu'       },
        { width: '50%',  color: 'bg-warning', text: 'Trung bình' },
        { width: '75%',  color: 'bg-info',    text: 'Khá'       },
        { width: '100%', color: 'bg-success', text: 'Mạnh'      },
        { width: '100%', color: 'bg-success', text: 'Rất mạnh'  },
    ];

    const level          = levels[strength] || levels[0];
    strengthBar.style.width = level.width;
    strengthBar.className   = `progress-bar strength-bar ${level.color}`;
    strengthText.textContent = level.text;

    checkMatch();
});

// ===== REAL-TIME PASSWORD MATCH =====
confirmInput.addEventListener('input', checkMatch);

function checkMatch() {
    const pw      = passwordInput.value;
    const confirm = confirmInput.value;

    if (!confirm) {
        matchMsg.style.display = 'none';
        btnReset.disabled      = true;
        return;
    }

    matchMsg.style.display = 'block';

    if (pw === confirm && pw.length >= 6) {
        matchMsg.innerHTML = '<i class="fa-solid fa-check me-1"></i> Mật khẩu trùng khớp';
        matchMsg.className = 'form-text mt-1 text-success';
        btnReset.disabled  = false;
    } else if (pw !== confirm) {
        matchMsg.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Mật khẩu không khớp!';
        matchMsg.className = 'form-text mt-1 text-danger';
        btnReset.disabled  = true;
    } else {
        matchMsg.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Mật khẩu phải có ít nhất 6 ký tự!';
        matchMsg.className = 'form-text mt-1 text-warning';
        btnReset.disabled  = true;
    }
}

// ===== SUBMIT =====
document.getElementById('resetForm').addEventListener('submit', function(e) {
    const pw      = passwordInput.value;
    const confirm = confirmInput.value;

    if (pw !== confirm) {
        e.preventDefault();
        alert('Mật khẩu không khớp!');
        return;
    }

    if (pw.length < 6) {
        e.preventDefault();
        alert('Mật khẩu phải có ít nhất 6 ký tự!');
        return;
    }

    // Loading state
    btnReset.disabled        = true;
    document.getElementById('btnText').innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
});
</script>
</body>
</html>