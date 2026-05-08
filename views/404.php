<?php
// File: views/404.php
if (session_status() === PHP_SESSION_NONE) session_start();
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang | NoteApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            color: rgba(255,255,255,0.2);
            line-height: 1;
            letter-spacing: -5px;
        }
        .error-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            color: white;
            transition: opacity 0.2s, transform 0.2s;
        }
        .btn-home:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }
        .btn-back {
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
        }
        /* Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px);   }
            50%       { transform: translateY(-15px); }
        }
        .float-icon {
            animation: float 3s ease-in-out infinite;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card error-card text-center p-5">

                <!-- Icon -->
                <div class="mb-3">
                    <span class="float-icon" style="font-size: 5rem;">📭</span>
                </div>

                <!-- Error code -->
                <div class="error-code text-muted">404</div>

                <!-- Title -->
                <h2 class="fw-bold mt-2 mb-2">
                    Trang không tồn tại
                </h2>

                <!-- Description -->
                <p class="text-muted mb-4">
                    Trang bạn đang tìm kiếm có thể đã bị xóa,
                    đổi tên hoặc tạm thời không khả dụng.
                </p>

                <!-- Suggestions -->
                <div class="bg-light rounded-3 p-3 mb-4 text-start">
                    <small class="fw-semibold text-muted d-block mb-2">
                        <i class="fa-solid fa-lightbulb me-1 text-warning"></i>
                        Bạn có thể thử:
                    </small>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1">
                            <i class="fa-solid fa-check me-2 text-success"></i>
                            Kiểm tra lại đường dẫn URL
                        </li>
                        <li class="mb-1">
                            <i class="fa-solid fa-check me-2 text-success"></i>
                            Quay về trang chủ
                        </li>
                        <li>
                            <i class="fa-solid fa-check me-2 text-success"></i>
                            Tìm kiếm ghi chú của bạn
                        </li>
                    </ul>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-3 justify-content-center">
                    <button onclick="history.back()"
                            class="btn btn-outline-secondary btn-back">
                        <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                    </button>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/notes" class="btn btn-home">
                        <i class="fa-solid fa-note-sticky me-2"></i>Ghi chú của tôi
                    </a>
                    <?php else: ?>
                    <a href="/" class="btn btn-home">
                        <i class="fa-solid fa-house me-2"></i>Trang chủ
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Brand -->
                <hr class="my-4">
                <div style="color:#667eea; font-weight:800; font-size:1.2rem;">
                    <i class="fa-solid fa-book-open me-1"></i> NoteApp
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>