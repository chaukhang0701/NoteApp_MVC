<?php
// File: views/layout/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$preferences = $_SESSION['preferences'] ?? [
    'font_size'  => 'medium',
    'note_color' => '#ffffff',
    'theme'      => 'light'
];
$theme    = $preferences['theme'] ?? 'light';
$fontSize = $preferences['font_size'] ?? 'medium';

$fontSizeMap = [
    'small'  => '13px',
    'medium' => '15px',
    'large'  => '18px'
];
$fontSizePx = $fontSizeMap[$fontSize] ?? '15px';

$isLoggedIn  = isset($_SESSION['user_id']);
$isActivated = $_SESSION['is_activated'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteApp</title>

    <?php // ✅ Đã xóa thẻ base href vì gây xung đột VirtualHost ?>

    <!-- PWA -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#212529">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ✅ Dùng BASE_URL cho assets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <style>
        body { font-size: <?= $fontSizePx ?>; }
        [data-theme="dark"] body { background-color: #1a1a2e !important; color: #e0e0e0; }
        [data-theme="dark"] .navbar { background-color: #16213e !important; }
        [data-theme="dark"] .card { background-color: #16213e; border-color: #0f3460; color: #e0e0e0; }
        [data-theme="dark"] .sidebar { background-color: #16213e !important; }
    </style>
    <!-- ✅ Truyền BASE_URL từ PHP sang JS -->
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body class="bg-light">

<?php if ($isLoggedIn): ?>

<!-- NAVBAR (đã đăng nhập) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-4">

        <!-- ✅ Brand -->
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/notes">
            <i class="fa-solid fa-book-open me-1"></i> NoteApp
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            <!-- Live search -->
            <div class="mx-auto" style="width: 35%;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-secondary border-0">
                        <i class="fa-solid fa-magnifying-glass text-white"></i>
                    </span>
                    <!-- ✅ Thêm readonly rồi xóa bằng JS -->
                    <input
                        type="text"
                        id="live-search"
                        class="form-control border-0"
                        placeholder="Tìm kiếm ghi chú..."
                        autocomplete="off"
                        readonly
                    >
                </div>
            </div>

            <!-- Nav right -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <!-- Toggle view -->
                <li class="nav-item d-none d-lg-block">
                    <div class="btn-group btn-group-sm" id="view-toggle">
                        <button class="btn btn-outline-light active" id="btn-grid" title="Grid view">
                            <i class="fa-solid fa-grip"></i>
                        </button>
                        <button class="btn btn-outline-light" id="btn-list" title="List view">
                            <i class="fa-solid fa-list"></i>
                        </button>
                    </div>
                </li>

                <!-- Dark mode toggle -->
                <li class="nav-item">
                    <button class="btn btn-sm btn-outline-light" id="theme-toggle" title="Đổi giao diện">
                        <i class="fa-solid <?= $theme === 'dark' ? 'fa-sun' : 'fa-moon' ?>"></i>
                    </button>
                </li>

                <!-- ✅ Shared notes -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/shared" title="Ghi chú được chia sẻ">
                        <i class="fa-solid fa-share-nodes"></i>
                    </a>
                </li>

                <!-- Avatar + dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       href="#" role="button" data-bs-toggle="dropdown">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?= htmlspecialchars($_SESSION['avatar']) ?>"
                                 class="rounded-circle"
                                 width="30" height="30"
                                 style="object-fit:cover;"
                                 alt="Avatar">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                 style="width:30px;height:30px;font-size:13px;">
                                <i class="fa-solid fa-user text-white"></i>
                            </div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($_SESSION['display_name'] ?? '') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <!-- ✅ Tất cả href dùng BASE_URL -->
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/profile">
                                <i class="fa-solid fa-user me-2"></i> Hồ sơ
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/labels">
                                <i class="fa-solid fa-tags me-2"></i> Quản lý nhãn
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/preferences">
                                <i class="fa-solid fa-gear me-2"></i> Cài đặt
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- Thông báo chưa kích hoạt -->
<?php if (!$isActivated): ?>
<div class="alert alert-warning alert-dismissible rounded-0 mb-0 text-center" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    Tài khoản của bạn chưa được xác minh. Vui lòng kiểm tra email để kích hoạt tài khoản.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php else: ?>

<!-- NAVBAR (chưa đăng nhập) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- ✅ Brand -->
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/">
            <i class="fa-solid fa-book-open me-1"></i> NoteApp
        </a>
        <div>
            <!-- ✅ Login / Register links -->
            <a href="<?= BASE_URL ?>/login" class="btn btn-outline-light me-2">Đăng nhập</a>
            <a href="<?= BASE_URL ?>/register" class="btn btn-info text-white">Đăng ký</a>
        </div>
    </div>
</nav>

<?php endif; ?>

<!-- Flash messages -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show rounded-0 mb-0" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>
    <?= htmlspecialchars($_SESSION['success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show rounded-0 mb-0" role="alert">
    <i class="fa-solid fa-circle-xmark me-2"></i>
    <?= htmlspecialchars($_SESSION['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Wrapper chính -->
<div class="main-wrapper">