# 📘 NoteApp — Ứng dụng Quản lý Ghi chú

> Đồ án môn Lập trình và ứng dụng Web (503073) — Semester II/2024-2025

---

## 👥 Thông tin nhóm

| Họ tên | MSSV | Email |
|--------|------|-------|
| [Lê Văn Quý] | [52400153] | [52400153@student.tdtu.edu.vn] |
| [Huỳnh Nhật Khang] | [52400130] | [52400130@student.tdtu.edu.vn] |
| [Trần Thanh Huy] | [52400123] | [52400123@student.tdtu.edu.vn] |

---

## 🔗 Links

- 🌐 **Live Demo:** [[link hosting](https://noteapp-tdtu.free.nf/)]
- 🎬 **Video Demo:** [link YouTube : https://www.youtube.com/watch?v=b5wZPkCayNw]

> ⚠️ Nếu hosting không truy cập được, vui lòng chạy LOCAL theo hướng dẫn bên dưới.

---

## ⚙️ Công nghệ

- **Backend:** PHP 8.x (MVC, không framework)
- **Frontend:** Bootstrap 5.3, Font Awesome 6.4, JavaScript ES6+
- **Database:** MySQL — **Server:** XAMPP
- **Thư viện:** PHPMailer (Gmail SMTP), Ratchet (WebSocket)

---

## 🚀 Hướng dẫn chạy LOCAL

### Bước 1 — Yêu cầu
```
- XAMPP (PHP 8.x + MySQL)
- Trình duyệt Chrome/Edge/Firefox
```

### Bước 2 — Copy project
```
Giải nén vào: C:\xampp\htdocs\Final_Project\
```

> ✅ Thư mục `vendor/` đã có sẵn trong project, **không cần cài Composer**.

### Bước 3 — Tạo Database
```
1. XAMPP → Start Apache + MySQL
2. Vào: localhost/phpmyadmin
3. Tạo database: note_management (utf8mb4_unicode_ci)
4. Chọn database vừa tạo → tab Import → chọn file: database/schema.sql → Go
```

### Bước 4 — Cấu hình Database
Mở `config/database.php`, kiểm tra:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'note_management');
define('DB_USER', 'root');
define('DB_PASS', '');        // XAMPP mặc định không có password
define('BASE_URL', '/Final_Project/public');
```

### Bước 5 — Cấu hình Gmail
```
1. Mở config/mail.php
2. Điền thông tin Gmail vào
```
```php
define('MAIL_HOST',         'smtp.gmail.com');
define('MAIL_PORT',         587);
define('MAIL_USERNAME',     'your_gmail@gmail.com');
define('MAIL_PASSWORD',     'your_app_password_16_chars');
define('MAIL_FROM_ADDRESS', 'your_gmail@gmail.com');
define('MAIL_FROM_NAME',    'NoteApp System');
```
> 💡 Tạo App Password: Google Account → Security → 2-Step Verification → App passwords

### Bước 6 — Truy cập
```
localhost/Final_Project/public
```

---

## ⚡ Chạy WebSocket — Tiêu chí 24

> ⚠️ Chỉ chạy được trên **LOCAL**, không hỗ trợ hosting miễn phí.

```bash
# Mở CMD tại thư mục project
cd C:\xampp\htdocs\Final_Project
php websocket/server.php

# Giữ cửa sổ CMD mở — WebSocket tại: ws://localhost:8080
```

Test:
```
1. Mở 2 tab cùng vào 1 note có quyền edit
2. Gõ ở tab 1 → tab 2 cập nhật realtime
```

---

## 🔑 Tài khoản test

| Email | Mật khẩu |
|-------|----------|
| admin@noteapp.com | password |
| user@noteapp.com | password |

---

## 📋 Tính năng (28 tiêu chí)

| # | Tính năng | # | Tính năng |
|---|-----------|---|-----------|
| 1 | ✅ Đăng ký | 15 | ✅ Ảnh đính kèm |
| 2 | ✅ Kích hoạt email (Gmail) | 16 | ✅ Pin ghi chú |
| 3 | ✅ Đăng nhập / Đăng xuất | 17 | ✅ Live Search |
| 4 | ✅ Quên mật khẩu (OTP Gmail) | 18 | ✅ Quản lý nhãn |
| 5 | ✅ Xem hồ sơ | 19 | ✅ Gắn nhãn vào ghi chú |
| 6 | ✅ Cập nhật hồ sơ + Avatar | 20 | ✅ Filter theo nhãn |
| 7 | ✅ Đổi mật khẩu | 21 | ✅ Đặt mật khẩu ghi chú |
| 8 | ✅ Preferences | 22 | ✅ Đổi/tắt mật khẩu ghi chú |
| 9 | ✅ Grid view | 23 | ✅ Chia sẻ ghi chú |
| 10 | ✅ List view | 24 | ✅ Realtime (Local only) |
| 11 | ✅ Tạo ghi chú | 25 | ✅ UI/UX |
| 12 | ✅ Chỉnh sửa ghi chú | 26 | ✅ Responsive |
| 13 | ✅ Xóa ghi chú | 27 | ✅ PWA Offline |
| 14 | ✅ Auto-save | 28 | ✅ Deploy hosting |

---

## ⚠️ Lưu ý quan trọng

| Vấn đề | Giải thích |
|--------|-----------|
| Tiêu chí 24 không chạy trên hosting | WebSocket cần server riêng — chỉ demo được trên LOCAL |
| PHP >= 8.0 | Bật extension: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` |