-- =========================================================
-- BỔ SUNG CHỨC NĂNG: Liên hệ + mã giảm giá mẫu
-- Chạy SAU các file SQL trước. Chạy lại nhiều lần không bị trùng.
-- =========================================================
USE banhang_dienthoai;

CREATE TABLE IF NOT EXISTS contacts (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    phone      VARCHAR(15),
    subject    VARCHAR(150),
    message    TEXT NOT NULL,
    status     ENUM('new', 'read') NOT NULL DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mã giảm giá mẫu để thử chức năng đặt hàng
INSERT INTO vouchers (code, discount_type, discount_value, min_order_value, quantity, start_date, end_date, status)
SELECT 'GIAM500K', 'fixed', 500000, 5000000, 100, '2026-01-01', '2027-12-31', 'active' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM vouchers WHERE code = 'GIAM500K');

INSERT INTO vouchers (code, discount_type, discount_value, min_order_value, quantity, start_date, end_date, status)
SELECT 'SALE5', 'percent', 5, 0, 100, '2026-01-01', '2027-12-31', 'active' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM vouchers WHERE code = 'SALE5');
