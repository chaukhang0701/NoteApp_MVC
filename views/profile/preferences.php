<?php
// File: views/profile/preferences.php
// Dữ liệu từ ProfileController::preferences()
// $preferences = ['font_size' => ..., 'note_color' => ..., 'theme' => ...]

require_once __DIR__ . '/../layout/header.php';

$fontSize  = $preferences['font_size']  ?? 'medium';
$noteColor = $preferences['note_color'] ?? '#ffffff';
$theme     = $preferences['theme']      ?? 'light';
?>

<div class="container py-4" style="max-width: 680px;">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/notes" class="text-decoration-none">
                    <i class="fa-solid fa-house me-1"></i>Ghi chú
                </a>
            </li>
            <li class="breadcrumb-item active">Cài đặt giao diện</li>
        </ol>
    </nav>

    <h4 class="fw-bold mb-4">
        <i class="fa-solid fa-gear me-2 text-primary"></i>Cài đặt giao diện
    </h4>

    <!-- ===== CARD: THEME ===== -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid fa-circle-half-stroke me-2 text-muted"></i>Giao diện
            </h6>

            <div class="row g-3">
                <!-- Light -->
                <div class="col-6">
                    <label class="w-100" style="cursor:pointer;">
                        <input type="radio" name="theme" value="light"
                               class="d-none theme-radio"
                               <?= $theme === 'light' ? 'checked' : '' ?>>
                        <div class="theme-option border rounded-3 p-3 text-center
                                    <?= $theme === 'light' ? 'border-primary border-2 bg-primary bg-opacity-10' : '' ?>"
                             style="transition: all .2s;">
                            <div class="rounded-2 mb-2 mx-auto d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:#f8f9fa;border:1px solid #dee2e6;">
                                <i class="fa-solid fa-sun text-warning fs-5"></i>
                            </div>
                            <div class="fw-semibold small">Sáng</div>
                        </div>
                    </label>
                </div>

                <!-- Dark -->
                <div class="col-6">
                    <label class="w-100" style="cursor:pointer;">
                        <input type="radio" name="theme" value="dark"
                               class="d-none theme-radio"
                               <?= $theme === 'dark' ? 'checked' : '' ?>>
                        <div class="theme-option border rounded-3 p-3 text-center
                                    <?= $theme === 'dark' ? 'border-primary border-2 bg-primary bg-opacity-10' : '' ?>"
                             style="transition: all .2s;">
                            <div class="rounded-2 mb-2 mx-auto d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:#1a1a2e;border:1px solid #444;">
                                <i class="fa-solid fa-moon text-info fs-5"></i>
                            </div>
                            <div class="fw-semibold small">Tối</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== CARD: FONT SIZE ===== -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid fa-text-height me-2 text-muted"></i>Cỡ chữ
            </h6>

            <div class="d-flex gap-3">
                <?php foreach (['small' => ['label' => 'Nhỏ', 'size' => '13px'],
                                'medium'=> ['label' => 'Vừa', 'size' => '15px'],
                                'large' => ['label' => 'Lớn', 'size' => '18px']] as $val => $info): ?>
                <label class="flex-grow-1" style="cursor:pointer;">
                    <input type="radio" name="font_size" value="<?= $val ?>"
                           class="d-none fontsize-radio"
                           <?= $fontSize === $val ? 'checked' : '' ?>>
                    <div class="font-option border rounded-3 p-3 text-center
                                <?= $fontSize === $val ? 'border-primary border-2 bg-primary bg-opacity-10' : '' ?>"
                         style="transition: all .2s;">
                        <div class="fw-bold mb-1" style="font-size:<?= $info['size'] ?>;">Aa</div>
                        <small class="text-muted"><?= $info['label'] ?></small>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===== CARD: NOTE COLOR ===== -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid fa-palette me-2 text-muted"></i>Màu nền ghi chú mặc định
            </h6>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Preset colors -->
                <?php $presets = [
                    '#ffffff' => 'Trắng',
                    '#fef9c3' => 'Vàng nhạt',
                    '#dcfce7' => 'Xanh lá',
                    '#dbeafe' => 'Xanh dương',
                    '#fce7f3' => 'Hồng',
                    '#f3e8ff' => 'Tím nhạt',
                    '#ffedd5' => 'Cam nhạt',
                    '#f1f5f9' => 'Xám nhạt',
                ]; ?>

                <?php foreach ($presets as $hex => $name): ?>
                <label style="cursor:pointer;" title="<?= $name ?>">
                    <input type="radio" name="note_color" value="<?= $hex ?>"
                           class="d-none color-radio"
                           <?= $noteColor === $hex ? 'checked' : '' ?>>
                    <div class="color-swatch rounded-circle border
                                <?= $noteColor === $hex ? 'border-primary border-3' : 'border-secondary' ?>"
                         style="width:32px;height:32px;background:<?= $hex ?>;
                                transition:transform .15s, box-shadow .15s;
                                <?= $noteColor === $hex ? 'box-shadow:0 0 0 3px rgba(13,110,253,.3);' : '' ?>">
                    </div>
                </label>
                <?php endforeach; ?>

                <!-- Custom color picker -->
                <label style="cursor:pointer;" title="Màu tùy chỉnh">
                    <input type="color" id="custom-color" value="<?= $noteColor ?>"
                           style="width:32px;height:32px;padding:2px;border-radius:50%;
                                  border:2px solid #adb5bd;cursor:pointer;">
                </label>
            </div>

            <!-- Preview -->
            <div class="mt-3 p-3 rounded-3 border"
                 id="color-preview"
                 style="background:<?= $noteColor ?>; transition: background .3s;">
                <div class="fw-semibold small text-muted mb-1">Xem trước</div>
                <div class="fw-bold">Tiêu đề ghi chú</div>
                <div class="small text-muted">Nội dung ghi chú sẽ hiển thị như thế này...</div>
            </div>
        </div>
    </div>

    <!-- ===== BUTTONS ===== -->
    <div class="d-flex gap-2 justify-content-end">
        <a href="<?= BASE_URL ?>/notes" class="btn btn-light px-4">
            Hủy
        </a>
        <button class="btn btn-primary px-4" id="btn-save" onclick="savePreferences()">
            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu cài đặt
        </button>
    </div>

    <!-- Toast thông báo -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
        <div id="save-toast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fa-solid fa-circle-check me-2"></i> Đã lưu cài đặt!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

</div>

<script>
//const BASE_URL = '<?= BASE_URL ?>';

// ===== Highlight lựa chọn khi click =====
function bindRadioHighlight(radioClass, optionClass) {
    document.querySelectorAll('.' + radioClass).forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.' + optionClass).forEach(el => {
                el.classList.remove('border-primary', 'border-2', 'bg-primary', 'bg-opacity-10');
            });
            this.closest('label').querySelector('.' + optionClass)
                .classList.add('border-primary', 'border-2', 'bg-primary', 'bg-opacity-10');
        });
    });
}

bindRadioHighlight('theme-radio',    'theme-option');
bindRadioHighlight('fontsize-radio', 'font-option');

// ===== Màu note =====
document.querySelectorAll('.color-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        // Reset tất cả swatches
        document.querySelectorAll('.color-swatch').forEach(el => {
            el.classList.remove('border-primary', 'border-3');
            el.classList.add('border-secondary');
            el.style.boxShadow = '';
        });
        // Highlight swatch được chọn
        const swatch = this.closest('label').querySelector('.color-swatch');
        swatch.classList.add('border-primary', 'border-3');
        swatch.classList.remove('border-secondary');
        swatch.style.boxShadow = '0 0 0 3px rgba(13,110,253,.3)';
        // Cập nhật preview
        document.getElementById('color-preview').style.background = this.value;
        document.getElementById('custom-color').value = this.value;
    });
});

// Custom color picker
document.getElementById('custom-color').addEventListener('input', function() {
    // Bỏ chọn tất cả preset
    document.querySelectorAll('.color-radio').forEach(r => r.checked = false);
    document.querySelectorAll('.color-swatch').forEach(el => {
        el.classList.remove('border-primary', 'border-3');
        el.classList.add('border-secondary');
        el.style.boxShadow = '';
    });
    document.getElementById('color-preview').style.background = this.value;
});

// ===== SAVE =====
function savePreferences() {
    const theme     = document.querySelector('.theme-radio:checked')?.value    ?? 'light';
    const font_size = document.querySelector('.fontsize-radio:checked')?.value ?? 'medium';

    // Màu: preset hoặc custom
    const colorRadio  = document.querySelector('.color-radio:checked');
    const note_color  = colorRadio
        ? colorRadio.value
        : document.getElementById('custom-color').value;

    const btn = document.getElementById('btn-save');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

    fetch(`${BASE_URL}/preferences/update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `theme=${theme}&font_size=${font_size}&note_color=${encodeURIComponent(note_color)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Áp dụng theme ngay lập tức
            document.documentElement.setAttribute('data-theme', theme);

            // Hiện toast
            const toast = new bootstrap.Toast(document.getElementById('save-toast'));
            toast.show();

            // Reload sau 1 giây để áp dụng font size
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.message || 'Không thể lưu cài đặt!');
        }
    })
    .catch(() => alert('Lỗi kết nối!'))
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu cài đặt';
    });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>