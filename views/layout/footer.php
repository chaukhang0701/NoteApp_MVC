<?php
// File: views/layout/footer.php
?>

</div><!-- /.main-wrapper -->

<footer class="footer mt-auto py-3 bg-dark text-white">
    <div class="container text-center">
        <span class="text-muted">© 2026 NoteApp - Đồ án Web Programming & Applications</span>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Main JS — dùng BASE_URL (đã được khai báo trong header.php) -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

<script>
// ================= DARK/LIGHT MODE TOGGLE (Tiêu chí 8) =================
const themeToggle = document.getElementById('theme-toggle');
const html        = document.documentElement;

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const current  = html.getAttribute('data-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';

        html.setAttribute('data-theme', newTheme);

        // Cập nhật icon
        themeToggle.innerHTML = newTheme === 'dark'
            ? '<i class="fa-solid fa-sun"></i>'
            : '<i class="fa-solid fa-moon"></i>';

        // ✅ Lưu preferences lên server
        fetch(`${BASE_URL}/preferences/update`, {
            method  : 'POST',
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
            body    : `theme=${newTheme}`
        });
    });
}

// ================= GRID / LIST VIEW TOGGLE (Tiêu chí 9, 10) =================
const btnGrid   = document.getElementById('btn-grid');
const btnList   = document.getElementById('btn-list');
const notesGrid = document.getElementById('notes-grid');
const notesList = document.getElementById('notes-list');

// Lấy view đã lưu từ localStorage
const savedView = localStorage.getItem('notes-view') || 'grid';
applyView(savedView);

if (btnGrid) {
    btnGrid.addEventListener('click', () => {
        applyView('grid');
        localStorage.setItem('notes-view', 'grid');
    });
}

if (btnList) {
    btnList.addEventListener('click', () => {
        applyView('list');
        localStorage.setItem('notes-view', 'list');
    });
}

function applyView(view) {
    if (!notesGrid || !notesList) return;

    if (view === 'grid') {
        notesGrid.classList.remove('d-none');
        notesList.classList.add('d-none');
        btnGrid?.classList.add('active');
        btnList?.classList.remove('active');
    } else {
        notesGrid.classList.add('d-none');
        notesList.classList.remove('d-none');
        btnList?.classList.add('active');
        btnGrid?.classList.remove('active');
    }
}

// ================= LIVE SEARCH (Tiêu chí 17) =================
const searchInput = document.getElementById('live-search');
let footerSearchTimeout = null;

if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(footerSearchTimeout);

        // Delay 300ms tránh gọi API liên tục (đúng yêu cầu đề)
        footerSearchTimeout = setTimeout(() => {
            const keyword = searchInput.value.trim();
            doSearch(keyword);
        }, 300);
    });
}

function doSearch(keyword) {
    // ✅ Dùng BASE_URL
    fetch(`${BASE_URL}/notes/search?q=${encodeURIComponent(keyword)}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                renderNotes(data.data);
            }
        })
        .catch(err => console.error('Search error:', err));
}

// Hàm render notes — được gọi từ search và từ list.php
function renderNotes(notes) {
    const gridContainer = document.getElementById('notes-grid');
    const listContainer = document.getElementById('notes-list');
    if (!gridContainer && !listContainer) return;

    if (notes.length === 0) {
        const empty = `
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-magnifying-glass fa-2x mb-3"></i>
                <p>Không tìm thấy ghi chú nào.</p>
            </div>`;
        if (gridContainer) gridContainer.innerHTML = empty;
        if (listContainer) listContainer.innerHTML = empty;
        return;
    }

    let gridHtml = '';
    let listHtml = '';

    notes.forEach(note => {
        const pinIcon   = note.is_pinned
            ? '<i class="fa-solid fa-thumbtack text-warning me-1" title="Đã ghim"></i>'   : '';
        const lockIcon  = note.note_password
            ? '<i class="fa-solid fa-lock text-danger me-1" title="Có mật khẩu"></i>'     : '';
        const shareIcon = note.is_shared
            ? '<i class="fa-solid fa-share-nodes text-info me-1" title="Đã chia sẻ"></i>' : '';

        const updatedAt    = new Date(note.updated_at).toLocaleDateString('vi-VN');
        const shortContent = note.content
            ? note.content.replace(/<[^>]*>/g, '').substring(0, 100) + '...'
            : '';

        // Grid card
        gridHtml += `
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm note-card" style="cursor:pointer;"
                     onclick="openNote(${note.id}, ${note.note_password ? 'true' : 'false'})">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title fw-bold mb-0 text-truncate">
                                ${escapeHtml(note.title)}
                            </h6>
                            <div class="note-icons ms-2 text-nowrap">
                                ${pinIcon}${lockIcon}${shareIcon}
                            </div>
                        </div>
                        <p class="card-text text-muted small">${escapeHtml(shortContent)}</p>
                    </div>
                    <div class="card-footer text-muted small d-flex justify-content-between">
                        <span>${updatedAt}</span>
                        <button class="btn btn-sm btn-link text-danger p-0"
                                onclick="event.stopPropagation(); confirmDelete(${note.id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;

        // List row
        listHtml += `
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                 onclick="openNote(${note.id}, ${note.note_password ? 'true' : 'false'})"
                 style="cursor:pointer;">
                <div class="d-flex align-items-center gap-2">
                    <div>
                        <div class="fw-bold">
                            ${pinIcon}${lockIcon}${shareIcon}
                            ${escapeHtml(note.title)}
                        </div>
                        <small class="text-muted">${escapeHtml(shortContent)}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">${updatedAt}</small>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="event.stopPropagation(); confirmDelete(${note.id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>`;
    });

    if (gridContainer) gridContainer.innerHTML = gridHtml;
    if (listContainer) listContainer.innerHTML = listHtml;
}

// ================= MỞ NOTE (có kiểm tra password) =================
function openNote(id, hasPassword) {
    if (hasPassword) {
        showPasswordPrompt(id);
    } else {
        // ✅ Dùng BASE_URL
        window.location.href = `${BASE_URL}/notes/${id}`;
    }
}

// ================= XÁC THỰC MẬT KHẨU NOTE (Tiêu chí 21) =================
function showPasswordPrompt(noteId) {
    const modalHtml = `
    <div class="modal fade" id="pwPromptModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
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
                        <input type="password"
                               id="prompt-password"
                               class="form-control"
                               placeholder="Mật khẩu..."
                               autofocus>
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
                <div class="modal-footer border-0">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">Hủy</button>
                    <button type="button"
                            class="btn btn-danger"
                            id="prompt-submit"
                            onclick="submitNotePassword(${noteId})">
                        <i class="fa-solid fa-unlock me-1"></i> Mở
                    </button>
                </div>
            </div>
        </div>
    </div>`;

    document.getElementById('pwPromptModal')?.remove();
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal = new bootstrap.Modal(
        document.getElementById('pwPromptModal')
    );
    modal.show();

    document.getElementById('prompt-password')
        .addEventListener('keydown', (e) => {
            if (e.key === 'Enter') submitNotePassword(noteId);
        });
}

function togglePromptPw() {
    const input = document.getElementById('prompt-password');
    const icon  = document.getElementById('prompt-eye');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

function submitNotePassword(noteId) {
    const password = document.getElementById('prompt-password').value;
    const errorDiv = document.getElementById('prompt-error');
    const btn      = document.getElementById('prompt-submit');

    errorDiv.style.display = 'none';

    if (!password) {
        errorDiv.textContent   = 'Vui lòng nhập mật khẩu!';
        errorDiv.style.display = 'block';
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';

    // ✅ Dùng BASE_URL
    fetch(`${BASE_URL}/notes/${noteId}/verify-password`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `password=${encodeURIComponent(password)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // ✅ Dùng BASE_URL
            window.location.href = `${BASE_URL}/notes/${noteId}`;
        } else {
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-unlock me-1"></i> Mở';
            errorDiv.textContent   = 'Mật khẩu không đúng!';
            errorDiv.style.display = 'block';
            document.getElementById('prompt-password').value = '';
            document.getElementById('prompt-password').focus();
        }
    })
    .catch(() => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-unlock me-1"></i> Mở';
        errorDiv.textContent   = 'Lỗi kết nối!';
        errorDiv.style.display = 'block';
    });
}

// ================= XÓA NOTE (Tiêu chí 13) =================
function confirmDelete(noteId) {
    if (!confirm('Bạn có chắc muốn xóa ghi chú này không?')) return;

    // ✅ Dùng BASE_URL
    fetch(`${BASE_URL}/notes/${noteId}/delete`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : `id=${noteId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.querySelectorAll(`[data-note-id="${noteId}"]`)
                .forEach(el => el.remove());
            location.reload();
        } else {
            alert('Không thể xóa ghi chú!');
        }
    });
}

// ================= HELPER =================
function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}
</script>

<!-- PWA Service Worker (Tiêu chí 27) -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // ✅ Dùng BASE_URL
        navigator.serviceWorker
            .register(`${BASE_URL}/sw.js`)
            .then(reg => {
                console.log('[SW] Registered:', reg.scope);
            })
            .catch(err => {
                console.error('[SW] Registration failed:', err);
            });
    });
}

// ===== CHẶN AUTO-FILL TRIỆT ĐỂ =====
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('live-search');
    if (search) {
        setTimeout(() => {
            search.removeAttribute('readonly');
            search.value = '';
        }, 500);
    }
});
</script>

</body>
</html>