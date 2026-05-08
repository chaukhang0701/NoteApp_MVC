-- =============================================
-- NoteApp - Database Schema
-- =============================================

-- 1. Bảng người dùng
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    display_name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) NULL,
    preferences JSON NULL,
    is_activated TINYINT(1) DEFAULT 0,
    activation_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Bảng ghi chú
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    is_pinned TINYINT(1) DEFAULT 0,
    pinned_at TIMESTAMP NULL,
    note_password VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Bảng nhãn
CREATE TABLE IF NOT EXISTS labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Bảng pivot: note ↔ label
CREATE TABLE IF NOT EXISTS note_labels (
    note_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (note_id, label_id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
);

-- 5. Bảng ảnh đính kèm
CREATE TABLE IF NOT EXISTS note_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
);

-- 6. Bảng chia sẻ ghi chú
CREATE TABLE IF NOT EXISTS shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    owner_id INT NOT NULL,
    recipient_id INT NOT NULL,
    permission ENUM('read', 'edit') DEFAULT 'read',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. Bảng reset mật khẩu
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- DỮ LIỆU MẪU (DUMMY DATA)
-- =============================================

-- TẠO USER MẪU (Mật khẩu đều là: password)
-- Pass đã được hash bằng bcrypt tương ứng với chữ "password"
INSERT INTO users (email, display_name, password, is_activated) VALUES
('admin@noteapp.com', 'Trưởng Nhóm Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('user@noteapp.com', 'Nguyễn Văn Test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- TẠO NHÃN (LABELS)
INSERT INTO labels (user_id, name) VALUES
(1, 'Công việc'),
(1, 'Quan trọng'),
(1, 'Cá nhân'),
(2, 'Học tập');

-- TẠO GHI CHÚ (NOTES) 
INSERT INTO notes (user_id, title, content, is_pinned, created_at) VALUES
(1, 'Lịch họp đồ án Web', 'Thứ 2 tuần sau nộp bài cho thầy Mạnh. Nhớ kiểm tra kỹ file Zip.', 1, NOW()),
(1, 'Danh sách đi chợ', 'Mua thịt bò, rau cải, trứng gà và sữa.', 0, NOW());

-- Ghi chú có mật khẩu (Đã sửa cột thành note_password)
INSERT INTO notes (user_id, title, content, note_password, created_at) VALUES
(1, 'Tài khoản ngân hàng', 'Số tài khoản: 123456789 - Techcombank (Ghi chú này đã khóa pass)', '123456', NOW());

-- GẮN NHÃN CHO GHI CHÚ
INSERT INTO note_labels (note_id, label_id) VALUES
(1, 1), 
(1, 2);