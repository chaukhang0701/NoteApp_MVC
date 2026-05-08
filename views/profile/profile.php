<?php
// File: views/profile/profile.php
// $user được truyền từ ProfileController::index()
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container mt-4 fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">
                        <i class="fa-solid fa-user me-2 text-primary"></i> Hồ sơ cá nhân
                    </h4>
                    <small class="text-muted">Quản lý thông tin tài khoản của bạn</small>
                </div>
                <a href="<?= BASE_URL ?>/notes" class="btn btn-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            <div class="row g-4">

                <!-- ===== CỘT TRÁI: AVATAR ===== -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center p-4">

                        <!-- Avatar (Tiêu chí 5, 6) -->
                        <div class="position-relative d-inline-block mx-auto mb-3">
                            <div id="avatar-wrapper">
                                <?php if (!empty($user['avatar'])): ?>
                                <img src="<?= htmlspecialchars($user['avatar']) ?>"
                                     id="avatar-preview"
                                     class="rounded-circle shadow"
                                     width="120" height="120"
                                     style="object-fit:cover;"
                                     alt="Avatar">
                                <?php else: ?>
                                <div id="avatar-placeholder"
                                     class="rounded-circle bg-primary d-flex align-items-center
                                            justify-content-center shadow mx-auto"
                                     style="width:120px;height:120px;font-size:2.5rem;color:white;">
                                    <?= strtoupper(mb_substr($user['display_name'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Upload button -->
                            <label for="avatar-upload"
                                   class="btn btn-sm btn-primary rounded-circle position-absolute
                                          bottom-0 end-0 shadow"
                                   style="width:32px;height:32px;padding:0;line-height:32px;cursor:pointer;"
                                   title="Đổi ảnh đại diện">
                                <i class="fa-solid fa-camera" style="font-size:12px;"></i>
                                <input type="file"
                                       id="avatar-upload"
                                       accept="image/*"
                                       style="display:none;">
                            </label>
                        </div>

                        <h5 class="fw-bold mb-1">
                            <?= htmlspecialchars($user['display_name']) ?>
                        </h5>
                        <p class="text-muted small mb-3">
                            <?= htmlspecialchars($user['email']) ?>
                        </p>

                        <!-- Trạng thái kích hoạt (Tiêu chí 2) -->
                        <?php if ($user['is_activated']): ?>
                        <span class="badge bg-success">
                            <i class="fa-solid fa-circle-check me-1"></i> Đã xác minh
                        </span>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark">
                            <i class="fa-solid fa-clock me-1"></i> Chưa xác minh
                        </span>
                        <small class="d-block text-muted mt-2">
                            Kiểm tra email để kích hoạt tài khoản
                        </small>
                        <?php endif; ?>

                        <!-- Upload progress -->
                        <div id="avatar-progress" class="mt-3" style="display:none;">
                            <div class="progress" style="height:4px;">
                                <div class="progress-bar progress-bar-striped
                                            progress-bar-animated w-100"></div>
                            </div>
                            <small class="text-muted">Đang tải ảnh...</small>
                        </div>

                        <div id="avatar-error" class="text-danger small mt-2"
                             style="display:none;"></div>

                        <!-- Ngày tham gia -->
                        <hr>
                        <small class="text-muted">
                            <i class="fa-solid fa-calendar me-1"></i>
                            Tham gia từ
                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                        </small>

                    </div>
                </div>

                <!-- ===== CỘT PHẢI: THÔNG TIN + ĐỔI MẬT KHẨU ===== -->
                <div class="col-md-8">

                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-4 gap-2" id="profileTabs">
                        <li class="nav-item">
                            <button class="nav-link active"
                                    onclick="showTab('info', this)">
                                <i class="fa-solid fa-user me-1"></i> Thông tin
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link"
                                    onclick="showTab('password', this)">
                                <i class="fa-solid fa-lock me-1"></i> Đổi mật khẩu
                            </button>
                        </li>
                    </ul>

                    <!-- ===== TAB: THÔNG TIN (Tiêu chí 5, 6) ===== -->
                    <div id="tab-info">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4">
                                    <i class="fa-solid fa-pen me-2 text-primary"></i>
                                    Chỉnh sửa thông tin
                                </h6>

                                <form action="<?= BASE_URL ?>/profile/update" method="POST" id="profileForm">

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fa-solid fa-user me-1 text-muted"></i>
                                            Tên hiển thị
                                        </label>
                                        <input
                                            type="text"
                                            name="display_name"
                                            class="form-control"
                                            value="<?= htmlspecialchars($user['display_name']) ?>"
                                            required
                                            maxlength="255"
                                        >
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="fa-solid fa-envelope me-1 text-muted"></i>
                                            Email
                                        </label>
                                        <input
                                            type="email"
                                            name="email"
                                            class="form-control"
                                            value="<?= htmlspecialchars($user['email']) ?>"
                                            required
                                        >
                                    </div>

                                    <button type="submit"
                                            id="btnSaveProfile"
                                            class="btn btn-primary w-100">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>
                                        <span id="btnSaveText">Lưu thay đổi</span>
                                    </button>

                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ===== TAB: ĐỔI MẬT KHẨU (Tiêu chí 7) ===== -->
                    <div id="tab-password" style="display:none;">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4">
                                    <i class="fa-solid fa-lock me-2 text-danger"></i>
                                    Đổi mật khẩu
                                </h6>

                                <form action="<?= BASE_URL ?>/profile/change-password"
                                      method="POST"
                                      id="passwordForm">

                                    <!-- Mật khẩu hiện tại -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Mật khẩu hiện tại
                                        </label>
                                        <div class="input-group">
                                            <input type="password"
                                                   name="current_password"
                                                   id="current_password"
                                                   class="form-control"
                                                   placeholder="Nhập mật khẩu hiện tại"
                                                   required>
                                            <button class="btn btn-outline-secondary"
                                                    type="button"
                                                    onclick="togglePw('current_password',
                                                                      'eye-current')">
                                                <i class="fa-solid fa-eye"
                                                   id="eye-current"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Mật khẩu mới -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Mật khẩu mới
                                        </label>
                                        <div class="input-group">
                                            <input type="password"
                                                   name="new_password"
                                                   id="new_password"
                                                   class="form-control"
                                                   placeholder="Ít nhất 6 ký tự"
                                                   required>
                                            <button class="btn btn-outline-secondary"
                                                    type="button"
                                                    onclick="togglePw('new_password',
                                                                      'eye-new')">
                                                <i class="fa-solid fa-eye"
                                                   id="eye-new"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Xác nhận mật khẩu mới -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            Xác nhận mật khẩu mới
                                        </label>
                                        <div class="input-group">
                                            <input type="password"
                                                   name="confirm_password"
                                                   id="confirm_password"
                                                   class="form-control"
                                                   placeholder="Nhập lại mật khẩu mới"
                                                   required>
                                            <button class="btn btn-outline-secondary"
                                                    type="button"
                                                    onclick="togglePw('confirm_password',
                                                                      'eye-confirm')">
                                                <i class="fa-solid fa-eye"
                                                   id="eye-confirm"></i>
                                            </button>
                                        </div>
                                        <div id="pw-match-msg" class="form-text mt-1"
                                             style="display:none;"></div>
                                    </div>

                                    <button type="submit"
                                            id="btnChangePw"
                                            class="btn btn-danger w-100">
                                        <i class="fa-solid fa-key me-1"></i>
                                        <span id="btnChangePwText">Đổi mật khẩu</span>
                                    </button>

                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
// ===== TABS =====
function showTab(tab, btn) {
    document.getElementById('tab-info').style.display      = 'none';
    document.getElementById('tab-password').style.display  = 'none';
    document.getElementById(`tab-${tab}`).style.display    = 'block';

    document.querySelectorAll('#profileTabs .nav-link')
        .forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// ===== UPLOAD AVATAR (Tiêu chí 6) =====
document.getElementById('avatar-upload').addEventListener('change', function() {
    const file      = this.files[0];
    const errorDiv  = document.getElementById('avatar-error');
    const progress  = document.getElementById('avatar-progress');
    errorDiv.style.display = 'none';

    if (!file) return;

    // Validate client-side
    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
        errorDiv.textContent   = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)!';
        errorDiv.style.display = 'block';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        errorDiv.textContent   = 'Ảnh quá lớn (tối đa 2MB)!';
        errorDiv.style.display = 'block';
        return;
    }

    // Preview ngay lập tức
    const reader  = new FileReader();
    reader.onload = (e) => {
        const wrapper = document.getElementById('avatar-wrapper');
        wrapper.innerHTML = `
            <img src="${e.target.result}"
                 id="avatar-preview"
                 class="rounded-circle shadow"
                 width="120" height="120"
                 style="object-fit:cover;"
                 alt="Avatar">`;
    };
    reader.readAsDataURL(file);

    // Upload lên server
    progress.style.display = 'block';
    const formData = new FormData();
    formData.append('avatar', file);

    fetch('${BASE_URL}/profile/avatar', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        progress.style.display = 'none';
        if (data.status === 'success') {
            document.getElementById('avatar-preview').src = data.url;
        } else {
            errorDiv.textContent   = data.message || 'Upload thất bại!';
            errorDiv.style.display = 'block';
        }
    })
    .catch(() => {
        progress.style.display = 'none';
        errorDiv.textContent   = 'Lỗi kết nối!';
        errorDiv.style.display = 'block';
    });

    this.value = '';
});

// ===== LOADING KHI LƯU PROFILE =====
document.getElementById('profileForm').addEventListener('submit', function() {
    const btn  = document.getElementById('btnSaveProfile');
    const text = document.getElementById('btnSaveText');
    btn.disabled  = true;
    text.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';
});

// ===== SHOW/HIDE PASSWORD =====
function togglePw(inputId, iconId) {
    const input  = document.getElementById(inputId);
    const icon   = document.getElementById(iconId);
    const isPass = input.type === 'password';
    input.type   = isPass ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

// ===== REAL-TIME PASSWORD MATCH =====
const newPw     = document.getElementById('new_password');
const confirmPw = document.getElementById('confirm_password');
const matchMsg  = document.getElementById('pw-match-msg');
const btnChange = document.getElementById('btnChangePw');

function checkMatch() {
    if (!confirmPw.value) {
        matchMsg.style.display = 'none';
        return;
    }
    matchMsg.style.display = 'block';
    if (newPw.value === confirmPw.value) {
        matchMsg.innerHTML = '<i class="fa-solid fa-check me-1"></i> Mật khẩu trùng khớp';
        matchMsg.className = 'form-text mt-1 text-success';
        btnChange.disabled = false;
    } else {
        matchMsg.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Mật khẩu không khớp!';
        matchMsg.className = 'form-text mt-1 text-danger';
        btnChange.disabled = true;
    }
}

newPw.addEventListener('input', checkMatch);
confirmPw.addEventListener('input', checkMatch);

// ===== LOADING KHI ĐỔI MẬT KHẨU =====
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const current = document.getElementById('current_password').value;
    const pw      = newPw.value;
    const confirm = confirmPw.value;

    if (!current || !pw || !confirm) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin!');
        return;
    }

    if (pw !== confirm) {
        e.preventDefault();
        alert('Mật khẩu mới không khớp!');
        return;
    }

    if (pw.length < 6) {
        e.preventDefault();
        alert('Mật khẩu mới phải có ít nhất 6 ký tự!');
        return;
    }

    const btn  = document.getElementById('btnChangePw');
    const text = document.getElementById('btnChangePwText');
    btn.disabled  = true;
    text.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>