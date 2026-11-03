-- =========================================================
-- CSDL: BÁN ĐIỆN THOẠI ONLINE
-- Mô tả: Thiết kế database cho website bán điện thoại
--        bằng PHP + MySQL
-- =========================================================

CREATE DATABASE IF NOT EXISTS banhang_dienthoai
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE banhang_dienthoai;

-- =========================================================
-- 1. BẢNG NGƯỜI DÙNG (khách hàng + admin dùng chung)
-- =========================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,      -- lưu mật khẩu đã hash (password_hash)
    phone VARCHAR(15),
    address VARCHAR(255),
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    status ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 2. BẢNG HÃNG ĐIỆN THOẠI
-- =========================================================
CREATE TABLE brands (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(50) NOT NULL UNIQUE,
    logo VARCHAR(255)
) ENGINE=InnoDB;

-- =========================================================
-- 3. BẢNG DANH MỤC SẢN PHẨM
-- =========================================================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    parent_id INT DEFAULT NULL,          -- hỗ trợ danh mục cha - con (VD: Điện thoại > iPhone)
    FOREIGN KEY (parent_id) REFERENCES categories(category_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 4. BẢNG SẢN PHẨM (thông tin chung, chưa gồm giá/tồn kho)
-- =========================================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    brand_id INT,
    category_id INT,
    description TEXT,
    screen VARCHAR(100),                 -- thông số màn hình
    chipset VARCHAR(100),                -- chip xử lý
    battery VARCHAR(50),                 -- dung lượng pin
    camera VARCHAR(150),                 -- thông số camera
    os VARCHAR(50),                      -- hệ điều hành
    status ENUM('available', 'discontinued') DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id)
        ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 5. BẢNG BIẾN THỂ SẢN PHẨM (màu, dung lượng, giá, tồn kho)
--    -> Mỗi điện thoại thường có nhiều phiên bản khác nhau
-- =========================================================
CREATE TABLE product_variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    color VARCHAR(50) NOT NULL,
    storage VARCHAR(20) NOT NULL,        -- VD: 128GB, 256GB
    ram VARCHAR(20),                     -- VD: 6GB, 8GB
    price DECIMAL(12,0) NOT NULL,        -- giá bán (VNĐ)
    original_price DECIMAL(12,0),        -- giá gốc (để hiển thị giảm giá)
    stock_quantity INT NOT NULL DEFAULT 0,
    sku VARCHAR(50) UNIQUE,              -- mã sản phẩm riêng của biến thể
    FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 6. BẢNG ẢNH SẢN PHẨM (1 sản phẩm có nhiều ảnh)
-- =========================================================
CREATE TABLE product_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,       -- ảnh đại diện chính
    FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 7. BẢNG GIỎ HÀNG
-- =========================================================
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    variant_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 8. BẢNG MÃ GIẢM GIÁ (voucher) - tùy chọn, có thể bỏ nếu không cần
-- =========================================================
CREATE TABLE vouchers (
    voucher_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'fixed') NOT NULL,
    discount_value DECIMAL(12,0) NOT NULL,
    min_order_value DECIMAL(12,0) DEFAULT 0,
    quantity INT DEFAULT 0,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'expired') DEFAULT 'active'
) ENGINE=InnoDB;

-- =========================================================
-- 9. BẢNG ĐƠN HÀNG
-- =========================================================
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voucher_id INT DEFAULT NULL,
    receiver_name VARCHAR(100) NOT NULL,
    receiver_phone VARCHAR(15) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    total_amount DECIMAL(12,0) NOT NULL,
    discount_amount DECIMAL(12,0) DEFAULT 0,
    final_amount DECIMAL(12,0) NOT NULL,
    order_status ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled')
        DEFAULT 'pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 10. BẢNG CHI TIẾT ĐƠN HÀNG
-- =========================================================
CREATE TABLE order_details (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    variant_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(12,0) NOT NULL,        -- giá tại thời điểm mua (không dùng giá hiện tại)
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- 11. BẢNG THANH TOÁN
-- =========================================================
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('cod', 'bank_transfer', 'momo', 'vnpay') NOT NULL,
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    paid_at DATETIME DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 12. BẢNG ĐÁNH GIÁ SẢN PHẨM
-- =========================================================
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- DỮ LIỆU MẪU (để test giao diện)
-- =========================================================

INSERT INTO users (full_name, email, password, phone, address, role) VALUES
('Admin', 'admin@shop.com', '$2y$10$examplehashedpassword', '0900000000', 'Hà Nội', 'admin'),
('Nguyễn Văn A', 'vana@gmail.com', '$2y$10$examplehashedpassword', '0912345678', 'Hà Đông, Hà Nội', 'customer');

INSERT INTO brands (brand_name) VALUES
('Apple'), ('Samsung'), ('Xiaomi'), ('OPPO');

INSERT INTO categories (category_name, parent_id) VALUES
('Điện thoại', NULL),
('Phụ kiện', NULL),
('iPhone', 1),
('Samsung Galaxy', 1);

INSERT INTO products (product_name, brand_id, category_id, description, screen, chipset, battery, camera, os) VALUES
('iPhone 16', 1, 3, 'Điện thoại iPhone 16 chính hãng VN/A', '6.1 inch OLED', 'Apple A18', '3561 mAh', '48MP + 12MP', 'iOS 18'),
('Samsung Galaxy S25', 2, 4, 'Flagship Samsung mới nhất', '6.2 inch AMOLED', 'Snapdragon 8 Gen 4', '4000 mAh', '50MP + 12MP + 10MP', 'Android 15');

INSERT INTO product_variants (product_id, color, storage, ram, price, original_price, stock_quantity, sku) VALUES
(1, 'Đen', '128GB', '8GB', 22990000, 24990000, 50, 'IP16-BLK-128'),
(1, 'Trắng', '256GB', '8GB', 26990000, 28990000, 30, 'IP16-WHT-256'),
(2, 'Xanh', '256GB', '12GB', 21990000, 23990000, 40, 'SS25-BLU-256');

INSERT INTO product_images (product_id, image_url, is_main) VALUES
(1, 'images/iphone16_1.jpg', TRUE),
(2, 'images/s25_1.jpg', TRUE);
