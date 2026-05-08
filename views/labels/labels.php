<?php
// File: views/labels/labels.php
// $labels được truyền từ LabelController::index()
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container mt-4 fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">
                        <i class="fa-solid fa-tags me-2 text-primary"></i> Quản lý nhãn
                    </h4>
                    <small class="text-muted">Tạo và quản lý nhãn để phân loại ghi chú</small>
                </div>
                <a href="<?= BASE_URL ?>/notes" class="btn btn-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            <!-- Form tạo nhãn mới (Tiêu chí 18) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-plus me-2 text-success"></i> Tạo nhãn mới
                    </h6>
                    <div class="d-flex gap-2">
                        <input
                            type="text"
                            id="new-label-input"
                            class="form-control"
                            placeholder="Tên nhãn..."
                            maxlength="50"
                            autocomplete="off"
                        >
                        <button class="btn btn-primary px-4" onclick="createLabel()">
                            <i class="fa-solid fa-plus me-1"></i> Tạo
                        </button>
                    </div>
                    <div id="create-error" class="text-danger small mt-2" style="display:none;"></div>
                </div>
            </div>

            <!-- Danh sách nhãn (Tiêu chí 18) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-list me-2 text-primary"></i>
                        Danh sách nhãn
                        <span class="badge bg-secondary ms-2" id="label-count">
                            <?= count($labels) ?>
                        </span>
                    </h6>
                </div>

                <div class="card-body p-4">

                    <!-- Empty state -->
                    <div id="empty-state"
                         class="text-center py-4 text-muted <?= !empty($labels) ? 'd-none' : '' ?>">
                        <i class="fa-solid fa-tags fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Chưa có nhãn nào.</p>
                        <small>Tạo nhãn đầu tiên của bạn!</small>
                    </div>

                    <!-- Label list -->
                    <ul class="list-group list-group-flush" id="label-list">
                        <?php foreach ($labels as $label): ?>
                        <li class="list-group-item px-0 py-3 label-item"
                            data-id="<?= $label['id'] ?>">
                            <div class="d-flex justify-content-between align-items-center">

                                <!-- View mode -->
                                <div class="d-flex align-items-center gap-2 label-view flex-grow-1">
                                    <i class="fa-solid fa-tag text-primary"></i>
                                    <span class="label-name fw-semibold">
                                        <?= htmlspecialchars($label['name']) ?>
                                    </span>
                                    <span class="badge bg-light text-muted border">
                                        <?= $label['note_count'] ?> ghi chú
                                    </span>
                                </div>

                                <!-- Edit mode (ẩn mặc định) -->
                                <div class="label-edit flex-grow-1 d-none">
                                    <div class="d-flex gap-2">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm label-edit-input"
                                            value="<?= htmlspecialchars($label['name']) ?>"
                                            maxlength="50"
                                        >
                                        <button class="btn btn-sm btn-success"
                                                onclick="saveLabel(<?= $label['id'] ?>, this)">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light"
                                                onclick="cancelEdit(<?= $label['id'] ?>)">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="text-danger small mt-1 edit-error" style="display:none;"></div>
                                </div>

                                <!-- Action buttons -->
                                <div class="label-actions ms-3 d-flex gap-2 label-view">
                                    <button class="btn btn-sm btn-outline-secondary"
                                            onclick="startEdit(<?= $label['id'] ?>)"
                                            title="Đổi tên">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteLabel(<?= $label['id'] ?>)"
                                            title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>

                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    
// ===== TẠO NHÃN MỚI (Tiêu chí 18) =====
function createLabel() {
    const input    = document.getElementById('new-label-input');
    const errorDiv = document.getElementById('create-error');
    const name     = input.value.trim();

    errorDiv.style.display = 'none';

    if (!name) {
        showCreateError('Tên nhãn không được để trống!');
        return;
    }

    if (name.length > 50) {
        showCreateError('Tên nhãn không được quá 50 ký tự!');
        return;
    }

    fetch(`${BASE_URL}/labels/store`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `name=${encodeURIComponent(name)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            input.value = '';
            addLabelToList(data.data.id, data.data.name);
            updateLabelCount(1);
        } else {
            showCreateError(data.message || 'Không thể tạo nhãn!');
        }
    })
    .catch(() => showCreateError('Lỗi kết nối!'));
}

// Thêm nhãn mới vào danh sách (không reload trang)
function addLabelToList(id, name) {
    const list      = document.getElementById('label-list');
    const emptyState = document.getElementById('empty-state');
    emptyState.classList.add('d-none');

    const li = document.createElement('li');
    li.className    = 'list-group-item px-0 py-3 label-item';
    li.dataset.id   = id;
    li.innerHTML    = `
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 label-view flex-grow-1">
                <i class="fa-solid fa-tag text-primary"></i>
                <span class="label-name fw-semibold">${escapeHtml(name)}</span>
                <span class="badge bg-light text-muted border">0 ghi chú</span>
            </div>
            <div class="label-edit flex-grow-1 d-none">
                <div class="d-flex gap-2">
                    <input type="text"
                           class="form-control form-control-sm label-edit-input"
                           value="${escapeHtml(name)}"
                           maxlength="50">
                    <button class="btn btn-sm btn-success"
                            onclick="saveLabel(${id}, this)">
                        <i class="fa-solid fa-check"></i>
                    </button>
                    <button class="btn btn-sm btn-light"
                            onclick="cancelEdit(${id})">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                <div class="text-danger small mt-1 edit-error" style="display:none;"></div>
            </div>
            <div class="label-actions ms-3 d-flex gap-2 label-view">
                <button class="btn btn-sm btn-outline-secondary"
                        onclick="startEdit(${id})" title="Đổi tên">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger"
                        onclick="deleteLabel(${id})" title="Xóa">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;

    list.appendChild(li);
}

// ===== ĐỔI TÊN NHÃN (Tiêu chí 18) =====
function startEdit(id) {
    const item = document.querySelector(`.label-item[data-id="${id}"]`);
    item.querySelectorAll('.label-view').forEach(el => el.classList.add('d-none'));
    item.querySelector('.label-edit').classList.remove('d-none');

    // Focus vào input
    const input = item.querySelector('.label-edit-input');
    input.focus();
    input.select();

    // Enter để lưu
    input.onkeydown = (e) => {
        if (e.key === 'Enter') saveLabel(id);
        if (e.key === 'Escape') cancelEdit(id);
    };
}

function cancelEdit(id) {
    const item = document.querySelector(`.label-item[data-id="${id}"]`);
    item.querySelectorAll('.label-view').forEach(el => el.classList.remove('d-none'));
    item.querySelector('.label-edit').classList.add('d-none');

    // Reset input về tên cũ
    const currentName = item.querySelector('.label-name').textContent.trim();
    item.querySelector('.label-edit-input').value = currentName;
}

function saveLabel(id) {
    const item     = document.querySelector(`.label-item[data-id="${id}"]`);
    const input    = item.querySelector('.label-edit-input');
    const errorDiv = item.querySelector('.edit-error');
    const name     = input.value.trim();

    errorDiv.style.display = 'none';

    if (!name) {
        errorDiv.textContent   = 'Tên nhãn không được để trống!';
        errorDiv.style.display = 'block';
        return;
    }

    fetch(`${BASE_URL}/labels/${id}/update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `name=${encodeURIComponent(name)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Cập nhật tên hiển thị (Tiêu chí 18 - tự động cập nhật)
            item.querySelector('.label-name').textContent = data.data.name;
            item.querySelector('.label-edit-input').value = data.data.name;
            cancelEdit(id);
        } else {
            errorDiv.textContent   = data.message || 'Không thể cập nhật!';
            errorDiv.style.display = 'block';
        }
    })
    .catch(() => {
        errorDiv.textContent   = 'Lỗi kết nối!';
        errorDiv.style.display = 'block';
    });
}

// ===== XÓA NHÃN (Tiêu chí 18) =====
// Lưu ý: xóa nhãn KHÔNG ảnh hưởng đến note (đúng yêu cầu đề)
function deleteLabel(id) {
    if (!confirm('Xóa nhãn này?\n\nCác ghi chú có nhãn này sẽ không bị ảnh hưởng.')) return;

    fetch(`${BASE_URL}/labels/${id}/delete`, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.querySelector(`.label-item[data-id="${id}"]`)?.remove();
            updateLabelCount(-1);

            // Hiện empty state nếu không còn nhãn
            const remaining = document.querySelectorAll('.label-item').length;
            if (remaining === 0) {
                document.getElementById('empty-state').classList.remove('d-none');
            }
        } else {
            alert(data.message || 'Không thể xóa nhãn!');
        }
    })
    .catch(() => alert('Lỗi kết nối!'));
}

// ===== HELPERS =====
function updateLabelCount(delta) {
    const badge   = document.getElementById('label-count');
    const current = parseInt(badge.textContent) || 0;
    badge.textContent = Math.max(0, current + delta);
}

function showCreateError(msg) {
    const div         = document.getElementById('create-error');
    div.textContent   = msg;
    div.style.display = 'block';
}

function escapeHtml(text) {
    const div       = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Enter để tạo nhãn
document.getElementById('new-label-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') createLabel();
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>