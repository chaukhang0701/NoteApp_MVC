<?php
// File: views/auth/forgot-password.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - NoteApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
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
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
        }
        .step.active   { background: #667eea; color: white; }
        .step.done     { background: #28a745; color: white; }
        .step.inactive { background: #e0e0e0; color: #999; }
        .step-line {
            width: 40px;
            height: 2px;
            background: #e0e0e0;
            align-self: center;
        }
        .step-line.done { background: #28a745; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card auth-card">
                <div class="card-body p-4 p-md-5">

                    <!-- Brand -->
                    <div class="text-center mb-3">
                        <div class="auth-brand">
                            <i class="fa-solid fa-book-open me-2"></i>NoteApp
                        </div>
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

                    <!-- Step indicator -->
                    <div class="step-indicator">
                        <div class="step active" id="step1-dot">1</div>
                        <div class="step-line" id="line1"></div>
                        <div class="step inactive" id="step2-dot">2</div>
                        <div class="step-line" id="line2"></div>
                        <div class="step inactive" id="step3-dot">3</div>
                    </div>

                    <!-- ===== STEP 1: Nhập email ===== -->
                    <div id="step1">
                        <h5 class="text-center fw-bold mb-1">Quên mật khẩu?</h5>
                        <p class="text-center text-muted small mb-4">
                            Nhập email của bạn, chúng tôi sẽ gửi mã OTP để đặt lại mật khẩu.
                        </p>

                        <form id="forgotForm" action="/forgot-password" method="POST" novalidate>
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

                            <button type="submit"
                                    id="btnSend"
                                    class="btn btn-auth btn-lg w-100 text-white mb-3">
                                <i class="fa-solid fa-paper-plane me-2"></i>
                                <span id="btnSendText">Gửi mã OTP</span>
                            </button>
                        </form>
                    </div>

                    <!-- ===== STEP 2: Nhập OTP (hiển thị bằng JS sau khi gửi thành công) ===== -->
                    <div id="step2" style="display:none;">
                        <h5 class="text-center fw-bold mb-1">Nhập mã OTP</h5>
                        <p class="text-center text-muted small mb-4">
                            Mã OTP đã được gửi đến email của bạn. Mã có hiệu lực trong <span id="countdown" class="fw-bold text-danger">60</span> giây.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fa-solid fa-key me-1 text-muted"></i> Mã OTP
                            </label>
                            <div class="d-flex gap-2 justify-content-center">
                                <!-- 6 ô nhập OTP -->
                                <input type="text" class="form-control form-control-lg text-center otp-input"
                                       maxlength="1" style="width:45px; font-size:1.2rem;" data-index="0">
                                <input type="text" class="form-control form-control-lg text-center otp-input"
                                       maxlength="1" style="width:45px; font-size:1.2rem;" data-index="1">
                                <input type="text" class="form-control form-control-lg text-center otp-input"
                                       maxlength="1" style="width:45px; font-size:1.2rem;" data-index="2">
                                <input type="text" class="form-control form-control-lg text-center otp-input"
                                       maxlength="1" style="width:45px; font-size:1.2rem;" data-index="3">
                                <input type="text" class="form-control form-control-lg text-center otp-input"
                                       maxlength="1" style="width:45px; font-size:1.2rem;" data-index="4">
                                <input type="text" class="form-control form-control-lg text-center otp-input"
                                       maxlength="1" style="width:45px; font-size:1.2rem;" data-index="5">
                            </div>
                        </div>

                        <button type="button"
                                id="btnVerifyOtp"
                                class="btn btn-auth btn-lg w-100 text-white mb-3">
                            <i class="fa-solid fa-check me-2"></i> Xác nhận OTP
                        </button>

                        <div class="text-center">
                            <button type="button"
                                    id="btnResend"
                                    class="btn btn-link text-muted small"
                                    disabled>
                                Gửi lại mã OTP
                            </button>
                        </div>
                    </div>

                    <!-- ===== STEP 3: Nhập mật khẩu mới ===== -->
                    <div id="step3" style="display:none;">
                        <h5 class="text-center fw-bold mb-1">Đặt mật khẩu mới</h5>
                        <p class="text-center text-muted small mb-4">
                            Nhập mật khẩu mới cho tài khoản của bạn.
                        </p>

                        <form id="resetForm" action="/reset-password" method="POST" novalidate>
                            <!-- Token ẩn -->
                            <input type="hidden" id="resetToken" name="token" value="">

                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-semibold">
                                    <i class="fa-solid fa-lock me-1 text-muted"></i> Mật khẩu mới
                                </label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control form-control-lg"
                                        id="new_password"
                                        name="password"
                                        placeholder="Ít nhất 6 ký tự"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            id="toggleNew">
                                        <i class="fa-solid fa-eye" id="eyeNew"></i>
                                    </button>
                                </div>
                            </div>

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
                                        placeholder="Nhập lại mật khẩu mới"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            id="toggleConfirm">
                                        <i class="fa-solid fa-eye" id="eyeConfirm"></i>
                                    </button>
                                </div>
                                <div id="matchMsg" class="form-text mt-1" style="display:none;"></div>
                            </div>

                            <button type="submit"
                                    id="btnReset"
                                    class="btn btn-auth btn-lg w-100 text-white">
                                <i class="fa-solid fa-key me-2"></i>
                                <span id="btnResetText">Đặt lại mật khẩu</span>
                            </button>
                        </form>
                    </div>

                    <hr class="my-3">

                    <div class="text-center">
                        <a href="/login" class="text-decoration-none text-muted small">
                            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại đăng nhập
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== STEP MANAGEMENT =====
function goToStep(step) {
    document.getElementById('step1').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('step2').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('step3').style.display = step === 3 ? 'block' : 'none';

    // Cập nhật step indicator
    for (let i = 1; i <= 3; i++) {
        const dot  = document.getElementById(`step${i}-dot`);
        if (i < step) {
            dot.className = 'step done';
            dot.innerHTML = '<i class="fa-solid fa-check"></i>';
        } else if (i === step) {
            dot.className = 'step active';
            dot.textContent = i;
        } else {
            dot.className = 'step inactive';
            dot.textContent = i;
        }
    }

    // Cập nhật line
    for (let i = 1; i <= 2; i++) {
        const line = document.getElementById(`line${i}`);
        line.className = i < step ? 'step-line done' : 'step-line';
    }
}

// ===== STEP 1: GỬI OTP =====
let userEmail = '';

document.getElementById('forgotForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Xử lý bằng JS, không reload trang

    const email   = document.getElementById('email').value.trim();
    const btnText = document.getElementById('btnSendText');
    const btn     = document.getElementById('btnSend');

    if (!email) {
        alert('Vui lòng nhập email!');
        return;
    }

    btn.disabled     = true;
    btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';

    fetch('/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}`
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled      = false;
        btnText.innerHTML = 'Gửi mã OTP';

        if (data.status === 'success') {
            userEmail = email;
            goToStep(2);
            startCountdown();
        } else {
            showError(data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(() => {
        btn.disabled      = false;
        btnText.innerHTML = 'Gửi mã OTP';
        showError('Lỗi kết nối, vui lòng thử lại!');
    });
});

// ===== OTP INPUT: Auto focus next =====
document.querySelectorAll('.otp-input').forEach((input, index, inputs) => {
    input.addEventListener('input', (e) => {
        // Chỉ nhận số
        e.target.value = e.target.value.replace(/[^0-9]/g, '');

        if (e.target.value && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        // Backspace → focus ô trước
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            inputs[index - 1].focus();
        }
    });

    // Paste toàn bộ OTP vào 6 ô
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
        [...paste].slice(0, 6).forEach((char, i) => {
            if (inputs[i]) inputs[i].value = char;
        });
        inputs[Math.min(paste.length, 5)].focus();
    });
});

// ===== STEP 2: XÁC NHẬN OTP =====
document.getElementById('btnVerifyOtp').addEventListener('click', () => {
    const otp = [...document.querySelectorAll('.otp-input')]
                    .map(i => i.value)
                    .join('');

    if (otp.length !== 6) {
        alert('Vui lòng nhập đủ 6 chữ số OTP!');
        return;
    }

    const btn = document.getElementById('btnVerifyOtp');
    btn.disabled    = true;
    btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xác nhận...';

    fetch('/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(userEmail)}&otp=${encodeURIComponent(otp)}`
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-check me-2"></i> Xác nhận OTP';

        if (data.status === 'success') {
            // Lưu token vào hidden input để dùng ở step 3
            document.getElementById('resetToken').value = data.token;
            goToStep(3);
        } else {
            showError(data.message || 'Mã OTP không đúng!');
        }
    })
    .catch(() => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-check me-2"></i> Xác nhận OTP';
        showError('Lỗi kết nối!');
    });
});

// ===== COUNTDOWN & RESEND =====
let countdownTimer = null;

function startCountdown() {
    let seconds   = 60;
    const display = document.getElementById('countdown');
    const resend  = document.getElementById('btnResend');
    resend.disabled = true;

    clearInterval(countdownTimer);

    countdownTimer = setInterval(() => {
        seconds--;
        display.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(countdownTimer);
            resend.disabled     = false;
            display.textContent = '0';
        }
    }, 1000);
}

document.getElementById('btnResend').addEventListener('click', () => {
    // Reset OTP inputs
    document.querySelectorAll('.otp-input').forEach(i => i.value = '');
    document.querySelectorAll('.otp-input')[0].focus();

    fetch('/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(userEmail)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            startCountdown();
        }
    });
});

// ===== STEP 3: SHOW/HIDE PASSWORD =====
function setupToggle(btnId, inputId, iconId) {
    document.getElementById(btnId).addEventListener('click', () => {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        const isPass = input.type === 'password';
        input.type  = isPass ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
}
setupToggle('toggleNew', 'new_password', 'eyeNew');
setupToggle('toggleConfirm', 'confirm_password', 'eyeConfirm');

// Match password
const newPass     = document.getElementById('new_password');
const confirmPass = document.getElementById('confirm_password');
const matchMsg    = document.getElementById('matchMsg');

function checkMatch() {
    if (!confirmPass.value) {
        matchMsg.style.display = 'none';
        return;
    }
    matchMsg.style.display = 'block';
    if (newPass.value === confirmPass.value) {
        matchMsg.innerHTML = '<i class="fa-solid fa-check me-1"></i> Mật khẩu trùng khớp';
        matchMsg.className = 'form-text mt-1 text-success';
    } else {
        matchMsg.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Mật khẩu không khớp!';
        matchMsg.className = 'form-text mt-1 text-danger';
    }
}
newPass.addEventListener('input', checkMatch);
confirmPass.addEventListener('input', checkMatch);

// Submit reset form
document.getElementById('resetForm').addEventListener('submit', function(e) {
    const pw  = newPass.value;
    const cpw = confirmPass.value;

    if (pw !== cpw) {
        e.preventDefault();
        alert('Mật khẩu không khớp!');
        return;
    }

    if (pw.length < 6) {
        e.preventDefault();
        alert('Mật khẩu phải có ít nhất 6 ký tự!');
        return;
    }

    const btn     = document.getElementById('btnReset');
    const btnText = document.getElementById('btnResetText');
    btn.disabled  = true;
    btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
});

// ===== HELPER: Show error =====
function showError(msg) {
    // Xóa alert cũ
    const old = document.getElementById('dynamicAlert');
    if (old) old.remove();

    const alert = document.createElement('div');
    alert.id        = 'dynamicAlert';
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        <i class="fa-solid fa-circle-xmark me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Chèn vào trước step hiện tại
    const card = document.querySelector('.card-body');
    card.insertBefore(alert, card.children[3]);
}
</script>
</body>
</html>