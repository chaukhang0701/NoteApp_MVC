// File: public/assets/js/main.js

// =============================================
// NoteApp - Main JavaScript
// =============================================

// ===== TOAST NOTIFICATION =====
function showToast(message, type = 'success') {
    // Xóa toast cũ
    document.querySelectorAll('.noteapp-toast').forEach(t => t.remove());

    const colors = {
        success : { bg: '#28a745', icon: '✅' },
        error   : { bg: '#dc3545', icon: '❌' },
        warning : { bg: '#ffc107', icon: '⚠️' },
        info    : { bg: '#667eea', icon: 'ℹ️' }
    };

    const c     = colors[type] || colors.success;
    const toast = document.createElement('div');
    toast.className = 'noteapp-toast';
    toast.innerHTML = `${c.icon} ${message}`;

    Object.assign(toast.style, {
        position     : 'fixed',
        bottom       : '24px',
        right        : '24px',
        background   : c.bg,
        color        : '#fff',
        padding      : '12px 20px',
        borderRadius : '10px',
        boxShadow    : '0 4px 15px rgba(0,0,0,0.2)',
        fontSize     : '14px',
        fontWeight   : '500',
        opacity      : '0',
        transition   : 'opacity 0.3s, transform 0.3s',
        transform    : 'translateY(10px)',
        zIndex       : '9999',
        maxWidth     : '300px'
    });

    document.body.appendChild(toast);

    // Hiện toast
    requestAnimationFrame(() => {
        toast.style.opacity   = '1';
        toast.style.transform = 'translateY(0)';
    });

    // Ẩn sau 2.5 giây
    setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// ===== DELETE NOTE (Tiêu chí 13) =====
// Đề yêu cầu: phải có confirm dialog trước khi xóa
function confirmDelete(noteId) {
    if (!confirm('Bạn có chắc muốn xóa ghi chú này không?')) return;

    fetch(`/notes/${noteId}/delete`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `id=${noteId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Animation xóa
            const card = document.querySelector(
                `.note-card-wrapper[data-note-id="${noteId}"]`
            );
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity    = '0';
                card.style.transform  = 'scale(0.85)';
                setTimeout(() => {
                    card.remove();
                    checkEmptyState();
                }, 300);
            }
            showToast('Đã xóa ghi chú!');
        } else {
            showToast(data.message || 'Không thể xóa!', 'error');
        }
    })
    .catch(() => showToast('Lỗi kết nối!', 'error'));
}

// Kiểm tra nếu không còn note nào
function checkEmptyState() {
    const grid = document.getElementById('notes-grid');
    const list = document.getElementById('notes-list');
    if (!grid) return;

    const remaining = document.querySelectorAll('.note-card-wrapper').length;
    if (remaining === 0) {
        const empty = `
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-note-sticky fa-4x mb-3 opacity-25"></i>
                <h5>Chưa có ghi chú nào</h5>
                <a href="/notes/create" class="btn btn-primary mt-2">
                    <i class="fa-solid fa-plus me-1"></i> Tạo ngay
                </a>
            </div>`;
        if (grid) grid.innerHTML = empty;
        if (list) list.innerHTML = '';
    }
}

// ===== PIN NOTE (Tiêu chí 16) =====
function togglePin(noteId) {
    fetch(`/notes/${noteId}/pin`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `id=${noteId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Đã cập nhật ghim!');
            // Reload để sắp xếp lại đúng thứ tự
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Không thể ghim!', 'error');
        }
    })
    .catch(() => showToast('Lỗi kết nối!', 'error'));
}

// ===== LIVE SEARCH (Tiêu chí 17) =====
// Delay 300ms đúng yêu cầu đề
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('live-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const keyword = searchInput.value.trim();

        // Delay 300ms
        searchTimeout = setTimeout(() => {
            if (keyword === '') {
                // Nếu xóa hết → reload về danh sách đầy đủ
                location.reload();
                return;
            }
            doSearch(keyword);
        }, 300);
    });
});

function doSearch(keyword) {
    fetch(`/notes/search?q=${encodeURIComponent(keyword)}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            renderNotes(data.data);
        }
    })
    .catch(err => console.error('Search error:', err));
}

// ===== RENDER NOTES =====
function renderNotes(notes) {
    const gridContainer = document.getElementById('notes-grid');
    const listContainer = document.getElementById('notes-list');
    if (!gridContainer && !listContainer) return;

    if (notes.length === 0) {
        const empty = `
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-magnifying-glass fa-3x mb-3 opacity-25"></i>
                <h5>Không tìm thấy ghi chú nào</h5>
                <p class="small">Thử tìm với từ khóa khác</p>
            </div>`;
        if (gridContainer) gridContainer.innerHTML = empty;
        if (listContainer) listContainer.innerHTML = '';
        return;
    }

    let gridHtml = '';
    let listHtml = '';

    notes.forEach(note => {
        const pinIcon   = note.is_pinned
            ? '<i class="fa-solid fa-thumbtack text-warning me-1"></i>' : '';
        const lockIcon  = note.note_password
            ? '<i class="fa-solid fa-lock text-danger me-1"></i>' : '';
        const shareIcon = note.is_shared
            ? '<i class="fa-solid fa-share-nodes text-info me-1"></i>' : '';

        const date      = new Date(note.updated_at)
                            .toLocaleDateString('vi-VN');
        const content   = (note.content || '')
                            .replace(/<[^>]*>/g, '')
                            .substring(0, 100);
        const onclick   = note.note_password
            ? `showPasswordPrompt(${note.id})`
            : `window.location.href='/notes/${note.id}'`;

        // Grid card
        gridHtml += `
        <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm border-0 note-card note-card-wrapper"
                 data-note-id="${note.id}"
                 onclick="${onclick}"
                 style="cursor:pointer;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="fw-bold mb-0 text-truncate">
                            ${escapeHtml(note.title)}
                        </h6>
                        <div class="note-icons ms-2">
                            ${pinIcon}${lockIcon}${shareIcon}
                        </div>
                    </div>
                    <p class="card-text text-muted small">
                        ${escapeHtml(content)}${content.length >= 100 ? '...' : ''}
                    </p>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">${date}</small>
                    <div onclick="event.stopPropagation()">
                        <button class="btn btn-sm btn-outline-warning me-1"
                                onclick="togglePin(${note.id})">
                            <i class="fa-solid fa-thumbtack"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="confirmDelete(${note.id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

        // List item
        listHtml += `
        <div class="list-group-item list-group-item-action note-card-wrapper"
             data-note-id="${note.id}"
             onclick="${onclick}"
             style="cursor:pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 me-3">
                    <div class="fw-bold mb-1">
                        ${pinIcon}${lockIcon}${shareIcon}
                        ${escapeHtml(note.title)}
                    </div>
                    <small class="text-muted">
                        ${escapeHtml(content)}${content.length >= 100 ? '...' : ''}
                    </small>
                </div>
                <div class="d-flex align-items-center gap-2 text-nowrap"
                     onclick="event.stopPropagation()">
                    <small class="text-muted">${date}</small>
                    <button class="btn btn-sm btn-outline-warning"
                            onclick="togglePin(${note.id})">
                        <i class="fa-solid fa-thumbtack"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="confirmDelete(${note.id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });

    if (gridContainer) gridContainer.innerHTML = gridHtml;
    if (listContainer) listContainer.innerHTML = listHtml;
}

// ===== GRID / LIST TOGGLE (Tiêu chí 9, 10) =====
document.addEventListener('DOMContentLoaded', () => {
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');

    // Lấy view đã lưu
    const savedView = localStorage.getItem('notes-view') || 'grid';
    applyView(savedView);

    btnGrid?.addEventListener('click', () => {
        applyView('grid');
        localStorage.setItem('notes-view', 'grid');
    });

    btnList?.addEventListener('click', () => {
        applyView('list');
        localStorage.setItem('notes-view', 'list');
    });
});

function applyView(view) {
    const grids = document.querySelectorAll(
        '#notes-grid, #notes-grid-pinned'
    );
    const lists = document.querySelectorAll(
        '#notes-list, #notes-list-pinned'
    );
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');

    if (view === 'grid') {
        grids.forEach(el => el.classList.remove('d-none'));
        lists.forEach(el => el.classList.add('d-none'));
        btnGrid?.classList.add('active');
        btnList?.classList.remove('active');
    } else {
        grids.forEach(el => el.classList.add('d-none'));
        lists.forEach(el => el.classList.remove('d-none'));
        btnList?.classList.add('active');
        btnGrid?.classList.remove('active');
    }
}

// ===== PASSWORD PROMPT (Tiêu chí 21) =====
function showPasswordPrompt(noteId) {
    document.getElementById('pwPromptModal')?.remove();

    const modalHtml = `
    <div class="modal fade" id="pwPromptModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">
                        <i class="fa-solid fa-lock me-2 text-danger"></i>
                        Ghi chú được bảo vệ
                    </h6>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Nhập mật khẩu để mở ghi chú này.
                    </p>

                    <div class="input-group">
                        <input type="text"
                               id="prompt-pw"
                               class="form-control"
                               placeholder="Mật khẩu ghi chú..."
                               autocomplete="off"
                               readonly
                               style="-webkit-text-security: disc;
                                      font-family: text-security-disc, monospace;">
                        <button class="btn btn-outline-secondary"
                                type="button"
                                onclick="togglePromptPw()">
                            <i class="fa-solid fa-eye" id="prompt-eye"></i>
                        </button>
                    </div>
                    <div id="prompt-error"
                         class="text-danger small mt-2"
                         style="display:none;">
                        <i class="fa-solid fa-circle-xmark me-1"></i>
                        Mật khẩu không đúng!
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-light"
                            data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger"
                            id="prompt-btn"
                            onclick="submitNotePassword(${noteId})">
                        <i class="fa-solid fa-unlock me-1"></i> Mở
                    </button>
                </div>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal = new bootstrap.Modal(
        document.getElementById('pwPromptModal')
    );

    // Khi modal hiện hoàn toàn
    document.getElementById('pwPromptModal')
        .addEventListener('shown.bs.modal', () => {
            const input = document.getElementById('prompt-pw');
            if (input) {
                input.value = '';
                // ✅ Xóa readonly sau khi modal hiện
                setTimeout(() => {
                    input.removeAttribute('readonly');
                    input.value = '';
                    input.focus();
                }, 100);
            }
        });

    modal.show();

    // Enter để submit
    document.getElementById('pwPromptModal')
        .addEventListener('keydown', e => {
            if (e.key === 'Enter') submitNotePassword(noteId);
        });
}

// Sửa lại togglePromptPw để xử lý type=text với -webkit-text-security
function togglePromptPw() {
    const input = document.getElementById('prompt-pw');
    const icon  = document.getElementById('prompt-eye');
    if (!input || !icon) return;

    if (input.style.webkitTextSecurity === 'disc' ||
        input.style.webkitTextSecurity === '') {
        input.style.webkitTextSecurity = 'none';
        input.style.fontFamily = 'inherit';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.style.webkitTextSecurity = 'disc';
        input.style.fontFamily = 'text-security-disc, monospace';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}


function submitNotePassword(noteId) {
    const pw       = document.getElementById('prompt-pw').value;
    const errorDiv = document.getElementById('prompt-error');
    const btn      = document.getElementById('prompt-btn');

    errorDiv.style.display = 'none';

    if (!pw) {
        errorDiv.textContent   = 'Vui lòng nhập mật khẩu!';
        errorDiv.style.display = 'block';
        return;
    }

    btn.disabled   = true;
    btn.innerHTML  =
        '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';

    fetch(`/notes/${noteId}/verify-password`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `password=${encodeURIComponent(pw)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = `/notes/${noteId}`;
        } else {
            btn.disabled  = false;
            btn.innerHTML =
                '<i class="fa-solid fa-unlock me-1"></i> Mở';
            errorDiv.textContent   = 'Mật khẩu không đúng!';
            errorDiv.style.display = 'block';
            document.getElementById('prompt-pw').value = '';
            document.getElementById('prompt-pw').focus();
        }
    })
    .catch(() => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-unlock me-1"></i> Mở';
        errorDiv.textContent   = 'Lỗi kết nối!';
        errorDiv.style.display = 'block';
    });
}

// ===== HELPER =====
function escapeHtml(text) {
    if (!text) return '';
    const div       = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== DARK MODE SYNC =====
// Đồng bộ theme khi load trang
document.addEventListener('DOMContentLoaded', () => {
    const theme = document.documentElement.getAttribute('data-theme');
    const icon  = document.querySelector('#theme-toggle i');
    if (icon && theme === 'dark') {
        icon.className = 'fa-solid fa-sun';
    }
});