<?php
// File: views/notes/list.php
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid px-4 mt-4">
    <div class="row">

        <!-- ===== SIDEBAR LABELS ===== -->
        <div class="col-lg-2 col-md-3 d-none d-md-block sidebar">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-3">

                    <h6 class="fw-bold text-muted mb-3">
                        <i class="fa-solid fa-tags me-2"></i>Nhãn
                    </h6>

                    <ul class="list-unstyled mb-0">
                        <!-- ✅ Tất cả notes -->
                        <li class="mb-1">
                            <a href="<?= BASE_URL ?>/notes"
                               class="btn btn-sm w-100 text-start <?= !isset($currentLabel) ? 'btn-primary' : 'btn-light' ?>">
                                <i class="fa-solid fa-note-sticky me-2"></i>Tất cả
                            </a>
                        </li>

                        <!-- ✅ Từng label -->
                        <?php foreach ($labels as $label): ?>
                        <li class="mb-1">
                            <a href="<?= BASE_URL ?>/notes?label=<?= $label['id'] ?>"
                               class="btn btn-sm w-100 text-start <?= (isset($currentLabel) && $currentLabel == $label['id']) ? 'btn-primary' : 'btn-light' ?>">
                                <i class="fa-solid fa-tag me-2"></i>
                                <?= htmlspecialchars($label['name']) ?>
                                <span class="badge bg-secondary ms-1"><?= $label['note_count'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <hr>
                    <!-- ✅ Quản lý nhãn -->
                    <a href="<?= BASE_URL ?>/labels" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa-solid fa-gear me-1"></i> Quản lý nhãn
                    </a>

                </div>
            </div>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="col-lg-10 col-md-9">

            <!-- Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0">
                    <i class="fa-solid fa-note-sticky me-2 text-primary"></i>
                    <?php if (isset($currentLabel) && $currentLabel): ?>
                        Nhãn: <?= htmlspecialchars($currentLabelName ?? '') ?>
                    <?php else: ?>
                        Tất cả ghi chú
                    <?php endif; ?>
                    <span class="badge bg-secondary ms-2"><?= count($notes) ?></span>
                </h5>

                <!-- ✅ Tạo ghi chú -->
                <a href="<?= BASE_URL ?>/notes/create" class="btn btn-primary shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i> Tạo ghi chú
                </a>
            </div>

            <!-- Notes pinned -->
            <?php $pinnedNotes = array_filter($notes, fn($n) => $n['is_pinned']); ?>
            <?php if (!empty($pinnedNotes)): ?>
            <div class="mb-2">
                <small class="text-muted fw-semibold">
                    <i class="fa-solid fa-thumbtack me-1"></i> ĐÃ GHIM
                </small>
            </div>

            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mb-3" id="notes-grid-pinned">
                <?php foreach ($pinnedNotes as $note): ?>
                    <?= renderNoteCard($note) ?>
                <?php endforeach; ?>
            </div>

            <div class="list-group mb-3 d-none" id="notes-list-pinned">
                <?php foreach ($pinnedNotes as $note): ?>
                    <?= renderNoteListItem($note) ?>
                <?php endforeach; ?>
            </div>

            <div class="mb-2 mt-3">
                <small class="text-muted fw-semibold">KHÁC</small>
            </div>
            <?php endif; ?>

            <!-- Notes không ghim -->
            <?php $unpinnedNotes = array_filter($notes, fn($n) => !$n['is_pinned']); ?>

            <?php if (empty($notes)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-note-sticky fa-4x mb-3 opacity-25"></i>
                <h5>Chưa có ghi chú nào</h5>
                <p>Bắt đầu tạo ghi chú đầu tiên của bạn!</p>
                <!-- ✅ -->
                <a href="<?= BASE_URL ?>/notes/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i> Tạo ngay
                </a>
            </div>
            <?php else: ?>

            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3" id="notes-grid">
                <?php foreach ($unpinnedNotes as $note): ?>
                    <?= renderNoteCard($note) ?>
                <?php endforeach; ?>
            </div>

            <div class="list-group d-none" id="notes-list">
                <?php foreach ($unpinnedNotes as $note): ?>
                    <?= renderNoteListItem($note) ?>
                <?php endforeach; ?>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php
// ===== HELPER FUNCTIONS =====
// ✅ Truyền BASE_URL vào helper functions qua global

function renderNoteCard(array $note): string {
    $base    = BASE_URL; // ✅
    $id      = $note['id'];
    $title   = htmlspecialchars($note['title']);
    $content = htmlspecialchars(strip_tags($note['content'] ?? ''));
    $short   = mb_substr($content, 0, 80) . (mb_strlen($content) > 80 ? '...' : '');
    $date    = date('d/m/Y', strtotime($note['updated_at']));
    $hasPass  = !empty($note['note_password']);
    $isShared = !empty($note['is_shared']);
    $isPinned = !empty($note['is_pinned']);

    $icons = '';
    if ($isPinned) $icons .= '<i class="fa-solid fa-thumbtack text-warning me-1" title="Đã ghim"></i>';
    if ($hasPass)  $icons .= '<i class="fa-solid fa-lock text-danger me-1" title="Có mật khẩu"></i>';
    if ($isShared) $icons .= '<i class="fa-solid fa-share-nodes text-info me-1" title="Đã chia sẻ"></i>';

    // ✅ Dùng BASE_URL trong onclick redirect
    $onclick = $hasPass
        ? "showPasswordPrompt($id)"
        : "window.location.href='{$base}/notes/{$id}'";

    return "
    <div class='col note-card-wrapper' data-note-id='$id'>
        <div class='card h-100 shadow-sm border-0 note-card'
             onclick=\"$onclick\" style='cursor:pointer;'>
            <div class='card-body p-3'>
                <div class='d-flex justify-content-between align-items-start mb-2'>
                    <h6 class='card-title fw-bold mb-0 text-truncate flex-grow-1'>$title</h6>
                    <div class='ms-2 text-nowrap'>$icons</div>
                </div>
                <p class='card-text text-muted small mb-0'>$short</p>
            </div>
            <div class='card-footer bg-transparent border-0 d-flex justify-content-between align-items-center pt-0 pb-2 px-3'>
                <small class='text-muted'>$date</small>
                <div class='d-flex gap-1' onclick='event.stopPropagation()'>
                    <button class='btn btn-sm btn-outline-warning'
                            onclick='togglePin($id)' title='Ghim/bỏ ghim'>
                        <i class='fa-solid fa-thumbtack'></i>
                    </button>
                    <button class='btn btn-sm btn-outline-danger'
                            onclick='confirmDelete($id)' title='Xóa'>
                        <i class='fa-solid fa-trash'></i>
                    </button>
                </div>
            </div>
        </div>
    </div>";
}

function renderNoteListItem(array $note): string {
    $base    = BASE_URL; // ✅
    $id      = $note['id'];
    $title   = htmlspecialchars($note['title']);
    $content = htmlspecialchars(strip_tags($note['content'] ?? ''));
    $short   = mb_substr($content, 0, 100) . (mb_strlen($content) > 100 ? '...' : '');
    $date    = date('d/m/Y H:i', strtotime($note['updated_at']));
    $hasPass  = !empty($note['note_password']);
    $isShared = !empty($note['is_shared']);
    $isPinned = !empty($note['is_pinned']);

    $icons = '';
    if ($isPinned) $icons .= '<i class="fa-solid fa-thumbtack text-warning me-1"></i>';
    if ($hasPass)  $icons .= '<i class="fa-solid fa-lock text-danger me-1"></i>';
    if ($isShared) $icons .= '<i class="fa-solid fa-share-nodes text-info me-1"></i>';

    // ✅ Dùng BASE_URL trong onclick redirect
    $onclick = $hasPass
        ? "showPasswordPrompt($id)"
        : "window.location.href='{$base}/notes/{$id}'";

    return "
    <div class='list-group-item list-group-item-action note-card-wrapper'
         data-note-id='$id'
         onclick=\"$onclick\"
         style='cursor:pointer;'>
        <div class='d-flex justify-content-between align-items-center'>
            <div class='flex-grow-1 me-3'>
                <div class='fw-bold mb-1'>$icons $title</div>
                <small class='text-muted'>$short</small>
            </div>
            <div class='d-flex align-items-center gap-2 text-nowrap' onclick='event.stopPropagation()'>
                <small class='text-muted'>$date</small>
                <button class='btn btn-sm btn-outline-warning' onclick='togglePin($id)'>
                    <i class='fa-solid fa-thumbtack'></i>
                </button>
                <button class='btn btn-sm btn-outline-danger' onclick='confirmDelete($id)'>
                    <i class='fa-solid fa-trash'></i>
                </button>
            </div>
        </div>
    </div>";
}
?>

<script>

// ===== TOGGLE PIN =====
function togglePin(id) {
    fetch(`${BASE_URL}/notes/${id}/pin`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') location.reload();
        else alert('Không thể ghim ghi chú!');
    });
}

// ===== CONFIRM DELETE =====
function confirmDelete(id) {
    if (!confirm('Bạn có chắc muốn xóa ghi chú này?')) return;
    fetch(`${BASE_URL}/notes/${id}/delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') location.reload();
        else alert('Không thể xóa ghi chú!');
    });
}

// ===== PASSWORD PROMPT =====
function showPasswordPrompt(id) {
    window.location.href = `${BASE_URL}/notes/${id}`;
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>