<?php
// File: views/shared/shared.php
// $sharedNotes được truyền từ ShareController::index()
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container mt-4 fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">
                        <i class="fa-solid fa-share-nodes me-2 text-info"></i>
                        Ghi chú được chia sẻ
                    </h4>
                    <small class="text-muted">
                        Danh sách ghi chú người khác chia sẻ với bạn
                    </small>
                </div>
                <a href="<?= BASE_URL ?>/notes" class="btn btn-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            <!-- Filter tabs -->
            <ul class="nav nav-pills mb-4 gap-2" id="shareFilter">
                <li class="nav-item">
                    <button class="nav-link active" data-filter="all"
                            onclick="filterShares('all', this)">
                        <i class="fa-solid fa-layer-group me-1"></i>
                        Tất cả
                        <span class="badge bg-white text-dark ms-1">
                            <?= count($sharedNotes) ?>
                        </span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-filter="read"
                            onclick="filterShares('read', this)">
                        <i class="fa-solid fa-eye me-1"></i>
                        Chỉ đọc
                        <span class="badge bg-white text-dark ms-1">
                            <?= count(array_filter($sharedNotes, fn($n) => $n['permission'] === 'read')) ?>
                        </span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-filter="edit"
                            onclick="filterShares('edit', this)">
                        <i class="fa-solid fa-pen me-1"></i>
                        Có thể chỉnh sửa
                        <span class="badge bg-white text-dark ms-1">
                            <?= count(array_filter($sharedNotes, fn($n) => $n['permission'] === 'edit')) ?>
                        </span>
                    </button>
                </li>
            </ul>

            <!-- Empty state -->
            <?php if (empty($sharedNotes)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-share-nodes fa-4x mb-3 opacity-25"></i>
                <h5>Chưa có ghi chú nào được chia sẻ</h5>
                <p class="small">Khi ai đó chia sẻ ghi chú với bạn, chúng sẽ xuất hiện ở đây.</p>
            </div>

            <?php else: ?>

            <!-- Shared notes list -->
            <div id="shared-list">
                <?php foreach ($sharedNotes as $note): ?>
                <div class="card border-0 shadow-sm mb-3 shared-item"
                     data-permission="<?= $note['permission'] ?>">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                            <!-- Note info -->
                            <div class="flex-grow-1">

                                <!-- Permission badge + Owner (Tiêu chí 23) -->
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">

                                    <?php if ($note['permission'] === 'edit'): ?>
                                    <span class="badge bg-success">
                                        <i class="fa-solid fa-pen me-1"></i> Có thể chỉnh sửa
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-info text-white">
                                        <i class="fa-solid fa-eye me-1"></i> Chỉ đọc
                                    </span>
                                    <?php endif; ?>

                                    <small class="text-muted">
                                        <i class="fa-solid fa-user me-1"></i>
                                        Chia sẻ bởi
                                        <strong><?= htmlspecialchars($note['owner_name']) ?></strong>
                                        (<?= htmlspecialchars($note['owner_email']) ?>)
                                    </small>

                                    <small class="text-muted">
                                        <i class="fa-solid fa-clock me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($note['created_at'])) ?>
                                    </small>

                                </div>

                                <!-- Title -->
                                <h5 class="fw-bold mb-2">
                                    <?= htmlspecialchars($note['note_title']) ?>
                                </h5>

                                <!-- Content preview -->
                                <p class="text-muted small mb-0">
                                    <?= htmlspecialchars(
                                        mb_substr(strip_tags($note['note_content'] ?? ''), 0, 150)
                                        . (mb_strlen($note['note_content'] ?? '') > 150 ? '...' : '')
                                    ) ?>
                                </p>

                                <!-- Last updated -->
                                <small class="text-muted mt-2 d-block">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>
                                    Cập nhật lần cuối:
                                    <?= date('d/m/Y H:i', strtotime($note['note_updated_at'])) ?>
                                </small>

                            </div>

                            <!-- Action button -->
                            <div class="d-flex flex-column gap-2 align-items-end">
                                <?php if ($note['permission'] === 'edit'): ?>
                                <a href="<?= BASE_URL ?>/shared/<?= $note['note_id'] ?>/edit"
                                   class="btn btn-success btn-sm">
                                    <i class="fa-solid fa-pen me-1"></i> Chỉnh sửa
                                </a>
                                <?php else: ?>
                                <a href="<?= BASE_URL ?>/shared/<?= $note['note_id'] ?>/view"
                                   class="btn btn-info btn-sm text-white">
                                    <i class="fa-solid fa-eye me-1"></i> Xem
                                </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// ===== FILTER THEO PERMISSION =====
function filterShares(filter, btn) {
    // Cập nhật active tab
    document.querySelectorAll('#shareFilter .nav-link')
        .forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter items
    document.querySelectorAll('.shared-item').forEach(item => {
        const permission = item.dataset.permission;
        if (filter === 'all' || permission === filter) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>