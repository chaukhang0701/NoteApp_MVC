<?php
// File: views/notes/editor.php
$isEdit      = !empty($note);
$noteId      = $note['id'] ?? '';
$noteTitle   = $note['title'] ?? '';
$noteContent = $note['content'] ?? '';
$isPinned    = $note['is_pinned'] ?? false;
$hasPassword = !empty($note['note_password']);

require_once __DIR__ . '/../layout/header.php';
?>

<div class="container mt-3 fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            <!-- TOOLBAR -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                <a href="<?= BASE_URL ?>/notes" class="btn btn-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>

                <span id="save-status" class="text-muted small">
                    <i class="fa-solid fa-cloud me-1"></i>
                    <span id="save-text">Đã đồng bộ</span>
                </span>

                <?php if ($isEdit): ?>
                <div class="d-flex align-items-center gap-2">
                    <div id="ws-status"></div>
                    <div id="collab-users" class="d-flex flex-wrap gap-1"></div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm <?= $isPinned ? 'btn-warning' : 'btn-outline-warning' ?>"
                            id="btn-pin"
                            onclick="togglePin()"
                            title="<?= $isPinned ? 'Bỏ ghim' : 'Ghim' ?>">
                        <i class="fa-solid fa-thumbtack"></i>
                    </button>
                    <button class="btn btn-sm <?= $hasPassword ? 'btn-danger' : 'btn-outline-danger' ?>"
                            id="btn-password"
                            onclick="showPasswordModal()"
                            title="<?= $hasPassword ? 'Đổi/tắt mật khẩu' : 'Đặt mật khẩu' ?>">
                        <i class="fa-solid fa-lock"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info"
                            onclick="showShareModal()"
                            title="Chia sẻ">
                        <i class="fa-solid fa-share-nodes"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="confirmDelete(<?= $noteId ?>)"
                            title="Xóa">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
                <?php endif; ?>

            </div>

            <!-- NOTE CARD -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">

                    <input type="hidden" id="note_id" value="<?= $noteId ?>">

                    <input type="text"
                           id="note_title"
                           class="form-control border-0 fs-3 fw-bold mb-3 px-0"
                           placeholder="Tiêu đề..."
                           value="<?= htmlspecialchars($noteTitle) ?>"
                           autocomplete="off">

                    <hr class="my-2">

                    <textarea id="note_content"
                              class="form-control border-0 px-0"
                              rows="12"
                              placeholder="Bắt đầu viết..."
                              style="resize:none;"
                    ><?= htmlspecialchars($noteContent) ?></textarea>

                    <hr class="my-3">

                    <!-- IMAGES -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="fw-semibold text-muted">
                                <i class="fa-solid fa-image me-1"></i> Ảnh đính kèm
                            </small>
                            <?php if ($isEdit): ?>
                            <label for="image-upload"
                                   class="btn btn-sm btn-outline-secondary"
                                   style="cursor:pointer;">
                                <i class="fa-solid fa-plus me-1"></i> Thêm ảnh
                                <input type="file"
                                       id="image-upload"
                                       accept="image/*"
                                       multiple
                                       style="display:none;">
                            </label>
                            <?php endif; ?>
                        </div>

                        <div id="images-container" class="d-flex flex-wrap gap-2">
                            <?php if ($isEdit && !empty($noteImages)): ?>
                                <?php foreach ($noteImages as $img): ?>
                                <div class="position-relative image-item"
                                     data-id="<?= $img['id'] ?>">
                                    <img src="<?= htmlspecialchars($img['path']) ?>"
                                         class="rounded"
                                         style="width:80px;height:80px;object-fit:cover;"
                                         alt="Ảnh đính kèm">
                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0"
                                            style="width:18px;height:18px;font-size:10px;line-height:1;"
                                            onclick="deleteImage(<?= $img['id'] ?>)">
                                        ×
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- LABELS -->
                    <div>
                        <small class="fw-semibold text-muted">
                            <i class="fa-solid fa-tags me-1"></i> Nhãn
                        </small>
                        <div id="label-list" class="d-flex flex-wrap gap-2 mt-2">
                            <?php
                            $labels     = $labels     ?? [];
                            $noteLabels = $noteLabels ?? [];
                            ?>
                            <?php if (!empty($labels)): ?>
                                <?php foreach ($labels as $label):
                                    $isAttached = in_array(
                                        $label['id'],
                                        array_column($noteLabels, 'id')
                                    );
                                ?>
                                <button type="button"
                                        class="btn btn-sm label-btn <?= $isAttached ? 'btn-primary' : 'btn-outline-secondary' ?>"
                                        data-label-id="<?= $label['id'] ?>"
                                        data-attached="<?= $isAttached ? '1' : '0' ?>"
                                        onclick="toggleLabel(this)">
                                    <i class="fa-solid fa-tag me-1"></i>
                                    <?= htmlspecialchars($label['name']) ?>
                                </button>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <small class="text-muted">
                                    Chưa có nhãn. <a href="<?= BASE_URL ?>/labels">Tạo nhãn mới</a>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL: PASSWORD -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="passwordModalTitle">
                    <i class="fa-solid fa-lock me-2"></i> Đặt mật khẩu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="password-form-content"></div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: SHARE -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-share-nodes me-2"></i> Chia sẻ ghi chú
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-3">
                    <input type="email"
                           id="share-email"
                           class="form-control"
                           placeholder="Email người nhận...">
                    <select id="share-permission" class="form-select" style="width:130px;">
                        <option value="read">Chỉ đọc</option>
                        <option value="edit">Chỉnh sửa</option>
                    </select>
                    <button class="btn btn-primary" onclick="shareNote()">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
                <div id="share-list">
                    <small class="text-muted fw-semibold">ĐÃ CHIA SẺ VỚI</small>
                    <div id="share-list-content" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const NOTE_ID     = <?= $noteId ? (int)$noteId : 'null' ?>;
let autoSaveTimer = null;
let hasPassword   = <?= $hasPassword ? 'true' : 'false' ?>;

// ===== AUTO-SAVE =====
function triggerAutoSave() {
    clearTimeout(autoSaveTimer);
    setSaveStatus('saving');
    autoSaveTimer = setTimeout(autoSave, 1000);
}

function autoSave() {
    const id      = document.getElementById('note_id').value;
    const title   = document.getElementById('note_title').value.trim();
    const content = document.getElementById('note_content').value.trim();

    fetch(`${BASE_URL}/notes/autosave`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `id=${encodeURIComponent(id)}&title=${encodeURIComponent(title || 'Ghi chú không tên')}&content=${encodeURIComponent(content)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            if (!id && data.id) {
                document.getElementById('note_id').value = data.id;
                history.replaceState(null, '', `${BASE_URL}/notes/${data.id}`);
            }
            setSaveStatus('saved', data.time);
        } else {
            setSaveStatus('error');
        }
    })
    .catch(() => setSaveStatus('error'));
}

function setSaveStatus(status, time = '') {
    const el     = document.getElementById('save-status');
    const states = {
        saving : { icon: 'fa-spinner fa-spin', color: 'text-warning', label: 'Đang lưu...' },
        saved  : { icon: 'fa-cloud-arrow-up',  color: 'text-success', label: `Đã lưu lúc ${time}` },
        error  : { icon: 'fa-cloud-slash',     color: 'text-danger',  label: 'Lỗi lưu!' }
    };
    const s      = states[status];
    el.className = `small ${s.color}`;
    el.innerHTML = `<i class="fa-solid ${s.icon} me-1"></i><span>${s.label}</span>`;
}

document.getElementById('note_title').addEventListener('input', triggerAutoSave);
document.getElementById('note_content').addEventListener('input', triggerAutoSave);

// ===== PIN =====
function togglePin() {
    if (!NOTE_ID) return;
    fetch(`${BASE_URL}/notes/${NOTE_ID}/pin`, { method: 'POST' })
        .then(res => res.json())
        .then(data => { if (data.status === 'success') location.reload(); });
}

// ===== LABEL =====
function toggleLabel(btn) {
    if (!NOTE_ID) {
        alert('Vui lòng lưu ghi chú trước khi gắn nhãn!');
        return;
    }

    const labelId  = btn.dataset.labelId;
    const attached = btn.dataset.attached === '1';
    const url      = attached
        ? `${BASE_URL}/notes/${NOTE_ID}/labels/detach`
        : `${BASE_URL}/notes/${NOTE_ID}/labels/attach`;

    fetch(url, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `note_id=${NOTE_ID}&label_id=${labelId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            btn.dataset.attached = attached ? '0' : '1';
            btn.className = `btn btn-sm label-btn ${attached ? 'btn-outline-secondary' : 'btn-primary'}`;
        }
    });
}

// ===== IMAGES =====
document.getElementById('image-upload')?.addEventListener('change', function() {
    if (!NOTE_ID) {
        alert('Vui lòng lưu ghi chú trước khi thêm ảnh!');
        return;
    }

    Array.from(this.files).forEach(file => {
        const formData = new FormData();
        formData.append('image', file);

        fetch(`${BASE_URL}/notes/${NOTE_ID}/upload-image`, {
            method : 'POST',
            body   : formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') addImagePreview(data.id, data.url);
            else alert(data.message || 'Upload thất bại!');
        });
    });

    this.value = '';
});

function addImagePreview(imgId, url) {
    const container = document.getElementById('images-container');
    const div       = document.createElement('div');
    div.className   = 'position-relative image-item';
    div.dataset.id  = imgId;
    div.innerHTML   = `
        <img src="${url}" class="rounded" style="width:80px;height:80px;object-fit:cover;">
        <button class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0"
                style="width:18px;height:18px;font-size:10px;line-height:1;"
                onclick="deleteImage(${imgId})">×</button>`;
    container.appendChild(div);
}

function deleteImage(imgId) {
    if (!confirm('Xóa ảnh này?')) return;

    fetch(`${BASE_URL}/notes/${NOTE_ID}/delete-image`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `image_id=${imgId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.querySelector(`.image-item[data-id="${imgId}"]`)?.remove();
        }
    });
}

// ===== DELETE =====
function confirmDelete(id) {
    if (!confirm('Bạn có chắc muốn xóa ghi chú này?')) return;

    fetch(`${BASE_URL}/notes/${id}/delete`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') window.location.href = `${BASE_URL}/notes`;
        else alert('Không thể xóa ghi chú!');
    });
}

// ===== PASSWORD MODAL =====
function showPasswordModal() {
    const content = document.getElementById('password-form-content');
    const title   = document.getElementById('passwordModalTitle');

    if (!hasPassword) {
        title.innerHTML   = '<i class="fa-solid fa-lock me-2"></i> Đặt mật khẩu';
        content.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Mật khẩu mới</label>
                <input type="password" id="pw1" class="form-control"
                       placeholder="Nhập mật khẩu..." autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Xác nhận mật khẩu</label>
                <input type="password" id="pw2" class="form-control"
                       placeholder="Nhập lại..." autocomplete="new-password">
            </div>
            <button class="btn btn-danger w-100" id="btn-enable"
                    onclick="submitPassword('enable')">
                <i class="fa-solid fa-lock me-1"></i> Bật bảo vệ
            </button>`;

    } else {
        title.innerHTML   = '<i class="fa-solid fa-lock me-2"></i> Quản lý mật khẩu';
        content.innerHTML = `
            <div class="mb-3">
                <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                <div class="input-group">
                    <input type="password" id="pw-current" class="form-control"
                           placeholder="Nhập mật khẩu hiện tại" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePwField('pw-current', 'eye-current')">
                        <i class="fa-solid fa-eye" id="eye-current"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Mật khẩu mới</label>
                <div class="input-group">
                    <input type="password" id="pw1" class="form-control"
                           placeholder="Nhập mật khẩu mới" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePwField('pw1', 'eye-pw1')">
                        <i class="fa-solid fa-eye" id="eye-pw1"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                <div class="input-group">
                    <input type="password" id="pw2" class="form-control"
                           placeholder="Nhập lại mật khẩu mới" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePwField('pw2', 'eye-pw2')">
                        <i class="fa-solid fa-eye" id="eye-pw2"></i>
                    </button>
                </div>
                <div id="pw-match-msg" class="form-text mt-1" style="display:none;"></div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-warning flex-grow-1" id="btn-change"
                        onclick="submitPassword('change')">
                    <i class="fa-solid fa-key me-1"></i> Đổi mật khẩu
                </button>
                <button class="btn btn-outline-danger" id="btn-disable"
                        onclick="submitPassword('disable')">
                    <i class="fa-solid fa-lock-open me-1"></i> Tắt bảo vệ
                </button>
            </div>
            <div class="mt-2 text-muted small text-center">
                <i class="fa-solid fa-circle-info me-1"></i>
                Tắt bảo vệ chỉ cần nhập mật khẩu hiện tại
            </div>`;
    }

    const modalEl = document.getElementById('passwordModal');
    const modal   = bootstrap.Modal.getOrCreateInstance(modalEl);

    modalEl.addEventListener('shown.bs.modal', function() {
        document.querySelectorAll('#passwordModal input[type="password"]')
            .forEach(input => { input.value = ''; });
        document.getElementById('pw1')?.addEventListener('input', checkPwMatch);
        document.getElementById('pw2')?.addEventListener('input', checkPwMatch);
    }, { once: true });

    modal.show();
}

function submitPassword(action) {
    const currentEl = document.getElementById('pw-current');
    const pw1El     = document.getElementById('pw1');
    const pw2El     = document.getElementById('pw2');

    const current = currentEl ? currentEl.value.trim() : '';
    const pw1     = pw1El     ? pw1El.value.trim()     : '';
    const pw2     = pw2El     ? pw2El.value.trim()     : '';

    if (action === 'disable') {
        if (!current) {
            alert('Vui lòng nhập mật khẩu hiện tại!');
            return;
        }

        const btn = document.getElementById('btn-disable');
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';
        }

        fetch(`${BASE_URL}/notes/${NOTE_ID}/password`, {
            method  : 'POST',
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
            body    : `action=disable&current_password=${encodeURIComponent(current)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                bootstrap.Modal.getInstance(
                    document.getElementById('passwordModal')
                ).hide();
                alert(data.message || 'Đã tắt bảo vệ mật khẩu!');
                location.reload();
            } else {
                if (btn) {
                    btn.disabled  = false;
                    btn.innerHTML = '<i class="fa-solid fa-lock-open me-1"></i> Tắt bảo vệ';
                }
                alert(data.message || 'Mật khẩu không đúng!');
            }
        })
        .catch(() => {
            if (btn) {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-lock-open me-1"></i> Tắt bảo vệ';
            }
            alert('Lỗi kết nối!');
        });
        return;
    }

    if (action === 'enable') {
        if (!pw1 || !pw2)      { alert('Vui lòng điền đầy đủ mật khẩu!'); return; }
        if (pw1 !== pw2)       { alert('Mật khẩu không khớp!');           return; }
        if (pw1.length < 4)    { alert('Mật khẩu phải có ít nhất 4 ký tự!'); return; }
    }

    if (action === 'change') {
        if (!current)          { alert('Vui lòng nhập mật khẩu hiện tại!'); return; }
        if (!pw1 || !pw2)      { alert('Vui lòng nhập mật khẩu mới!');      return; }
        if (pw1 !== pw2)       { alert('Mật khẩu mới không khớp!');         return; }
        if (pw1.length < 4)    { alert('Mật khẩu mới phải có ít nhất 4 ký tự!'); return; }
    }

    let body = `action=${action}&password=${encodeURIComponent(pw1)}&confirm_password=${encodeURIComponent(pw2)}`;
    if (action === 'change') body += `&current_password=${encodeURIComponent(current)}`;

    fetch(`${BASE_URL}/notes/${NOTE_ID}/password`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            bootstrap.Modal.getInstance(
                document.getElementById('passwordModal')
            ).hide();
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(() => alert('Lỗi kết nối!'));
}

function checkPwMatch() {
    const pw1 = document.getElementById('pw1')?.value;
    const pw2 = document.getElementById('pw2')?.value;
    const msg = document.getElementById('pw-match-msg');
    if (!msg || !pw2) return;
    msg.style.display = 'block';
    if (pw1 === pw2) {
        msg.innerHTML = '<i class="fa-solid fa-check me-1"></i> Trùng khớp';
        msg.className = 'form-text mt-1 text-success';
    } else {
        msg.innerHTML = '<i class="fa-solid fa-times me-1"></i> Không khớp';
        msg.className = 'form-text mt-1 text-danger';
    }
}

function togglePwField(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (!input || !icon) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

// ===== SHARE MODAL =====
function showShareModal() {
    if (!NOTE_ID) return;
    const modal = new bootstrap.Modal(document.getElementById('shareModal'));
    modal.show();
    loadShareList();
}

function loadShareList() {
    fetch(`${BASE_URL}/notes/${NOTE_ID}/share-list`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('share-list-content');
            if (!data.data || data.data.length === 0) {
                container.innerHTML = '<p class="text-muted small">Chưa chia sẻ với ai.</p>';
                return;
            }
            container.innerHTML = data.data.map(share => `
                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                    <div>
                        <div class="fw-semibold small">${share.recipient_name}</div>
                        <small class="text-muted">${share.recipient_email}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select form-select-sm" style="width:110px;"
                                onchange="updatePermission(${share.id}, this.value)">
                            <option value="read" ${share.permission === 'read' ? 'selected' : ''}>Chỉ đọc</option>
                            <option value="edit" ${share.permission === 'edit' ? 'selected' : ''}>Chỉnh sửa</option>
                        </select>
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="revokeShare(${share.id})">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>`
            ).join('');
        });
}

function shareNote() {
    const email      = document.getElementById('share-email').value.trim();
    const permission = document.getElementById('share-permission').value;
    if (!email) { alert('Vui lòng nhập email!'); return; }

    fetch(`${BASE_URL}/notes/${NOTE_ID}/share`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `email=${encodeURIComponent(email)}&permission=${permission}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('share-email').value = '';
            loadShareList();
        } else {
            alert(data.message || 'Không thể chia sẻ!');
        }
    });
}

function updatePermission(shareId, permission) {
    fetch(`${BASE_URL}/shares/${shareId}/update-permission`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `permission=${permission}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') alert('Không thể cập nhật quyền!');
    });
}

function revokeShare(shareId) {
    if (!confirm('Thu hồi quyền truy cập?')) return;
    fetch(`${BASE_URL}/shares/${shareId}/revoke`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') loadShareList();
            else alert('Không thể thu hồi!');
        });
}

// ===== WEBSOCKET REALTIME =====
<?php if ($isEdit && isset($note)): ?>
const WS_NOTE_ID   = <?= (int)$noteId ?>;
const WS_USER_ID   = <?= (int)$_SESSION['user_id'] ?>;
const WS_USER_NAME = "<?= htmlspecialchars($_SESSION['display_name']) ?>";

let ws             = null;
let wsReconnectTimer = null;
let isRemoteEdit   = false;

function connectWebSocket() {
    ws = new WebSocket('ws://localhost:8080');

    ws.onopen = () => {
        clearInterval(wsReconnectTimer);
        ws.send(JSON.stringify({
            type      : 'join',
            note_id   : WS_NOTE_ID,
            user_id   : WS_USER_ID,
            user_name : WS_USER_NAME
        }));
        showWsStatus('connected');
    };

    ws.onmessage = (event) => {
        handleWsMessage(JSON.parse(event.data));
    };

    ws.onclose = () => {
        showWsStatus('disconnected');
        wsReconnectTimer = setTimeout(connectWebSocket, 3000);
    };

    ws.onerror = () => showWsStatus('error');
}

function handleWsMessage(data) {
    switch (data.type) {
        case 'room_info':
            updateUserList(data.users);
            break;
        case 'user_joined':
            updateUserList(data.users);
            showToast(`${data.user_name} đã tham gia chỉnh sửa`, 'info');
            break;
        case 'user_left':
            updateUserList(data.users);
            showToast(`${data.user_name} đã rời khỏi`, 'warning');
            break;
        case 'edit':
            isRemoteEdit = true;
            if (data.field === 'title') {
                const el  = document.getElementById('note_title');
                const pos = el.selectionStart;
                el.value  = data.value;
                el.setSelectionRange(pos, pos);
            } else if (data.field === 'content') {
                const el  = document.getElementById('note_content');
                const pos = el.selectionStart;
                el.value  = data.value;
                el.setSelectionRange(pos, pos);
            }
            isRemoteEdit = false;
            showToast('Đang được chỉnh sửa bởi người khác...', 'info');
            break;
    }
}

function sendWsEdit(field, value) {
    if (!ws || ws.readyState !== WebSocket.OPEN || isRemoteEdit) return;
    ws.send(JSON.stringify({
        type    : 'edit',
        note_id : WS_NOTE_ID,
        user_id : WS_USER_ID,
        field,
        value
    }));
}

function updateUserList(users) {
    const container = document.getElementById('collab-users');
    if (!container) return;
    container.innerHTML = (users || []).map(u => `
        <span class="badge bg-success me-1" title="${u.user_name}">
            <i class="fa-solid fa-circle me-1" style="font-size:8px;"></i>
            ${u.user_name}
        </span>`
    ).join('');
}

function showWsStatus(status) {
    const indicator = document.getElementById('ws-status');
    if (!indicator) return;
    const states = {
        connected    : { color: 'text-success', icon: 'fa-circle',       text: 'Realtime' },
        disconnected : { color: 'text-warning', icon: 'fa-circle-pause', text: 'Mất kết nối' },
        error        : { color: 'text-danger',  icon: 'fa-circle-xmark', text: 'Lỗi' }
    };
    const s = states[status];
    indicator.innerHTML = `
        <i class="fa-solid ${s.icon} ${s.color} me-1" style="font-size:10px;"></i>
        <span class="${s.color}" style="font-size:12px;">${s.text}</span>`;
}

document.getElementById('note_title').addEventListener('input', function() {
    if (!isRemoteEdit) sendWsEdit('title', this.value);
});

document.getElementById('note_content').addEventListener('input', function() {
    if (!isRemoteEdit) sendWsEdit('content', this.value);
});

setInterval(() => {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ type: 'ping' }));
    }
}, 30000);

connectWebSocket();
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>