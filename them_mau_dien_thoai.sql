-- =========================================================
-- THÊM DỮ LIỆU MẪU: 8 mẫu điện thoại mới
-- Chạy SAU KHI đã import banhang_dienthoai.sql
-- CHỈ CHẠY 1 LẦN (chạy lại sẽ bị trùng dữ liệu)
-- Lưu ý: giá và thông số chỉ mang tính minh họa cho đồ án
-- =========================================================
USE banhang_dienthoai;

-- Thêm danh mục con cho Xiaomi và OPPO (cha = 'Điện thoại')
INSERT INTO categories (category_name, parent_id)
VALUES ('Xiaomi', (SELECT x.category_id FROM (SELECT category_id FROM categories WHERE category_name = 'Điện thoại' LIMIT 1) x)),
       ('OPPO',   (SELECT x.category_id FROM (SELECT category_id FROM categories WHERE category_name = 'Điện thoại' LIMIT 1) x));

-- ---------------------------------------------------------
-- SẢN PHẨM
-- ---------------------------------------------------------
INSERT INTO products (product_name, brand_id, category_id, description, screen, chipset, battery, camera, os) VALUES
('iPhone 16 Pro Max',
 (SELECT brand_id FROM brands WHERE brand_name = 'Apple'),
 (SELECT category_id FROM categories WHERE category_name = 'iPhone'),
 'iPhone cao cấp nhất với khung titan, camera tele 5x, hiệu năng mạnh mẽ.',
 '6.9 inch Super Retina XDR OLED', 'Apple A18 Pro', '4685 mAh', '48MP + 48MP + 12MP', 'iOS 18'),

('iPhone 15',
 (SELECT brand_id FROM brands WHERE brand_name = 'Apple'),
 (SELECT category_id FROM categories WHERE category_name = 'iPhone'),
 'iPhone 15 với Dynamic Island, cổng sạc USB-C, camera chính 48MP.',
 '6.1 inch Super Retina XDR OLED', 'Apple A16 Bionic', '3349 mAh', '48MP + 12MP', 'iOS 17'),

('Samsung Galaxy S24 Ultra',
 (SELECT brand_id FROM brands WHERE brand_name = 'Samsung'),
 (SELECT category_id FROM categories WHERE category_name = 'Samsung Galaxy'),
 'Flagship Samsung với bút S Pen, camera 200MP và nhiều tính năng Galaxy AI.',
 '6.8 inch Dynamic AMOLED 2X', 'Snapdragon 8 Gen 3 for Galaxy', '5000 mAh', '200MP + 50MP + 12MP + 10MP', 'Android 14'),

('Samsung Galaxy A55 5G',
 (SELECT brand_id FROM brands WHERE brand_name = 'Samsung'),
 (SELECT category_id FROM categories WHERE category_name = 'Samsung Galaxy'),
 'Tầm trung nổi bật với khung kim loại, màn hình đẹp, pin trâu.',
 '6.6 inch Super AMOLED 120Hz', 'Exynos 1480', '5000 mAh', '50MP + 12MP + 5MP', 'Android 14'),

('Xiaomi 14',
 (SELECT brand_id FROM brands WHERE brand_name = 'Xiaomi'),
 (SELECT category_id FROM categories WHERE category_name = 'Xiaomi'),
 'Flagship nhỏ gọn, camera hợp tác cùng Leica, sạc nhanh 90W.',
 '6.36 inch AMOLED 120Hz', 'Snapdragon 8 Gen 3', '4610 mAh', '50MP + 50MP + 50MP (Leica)', 'Android 14 (HyperOS)'),

('Redmi Note 13 Pro',
 (SELECT brand_id FROM brands WHERE brand_name = 'Xiaomi'),
 (SELECT category_id FROM categories WHERE category_name = 'Xiaomi'),
 'Tầm trung giá tốt, camera 200MP, màn hình AMOLED sắc nét.',
 '6.67 inch AMOLED 120Hz', 'MediaTek Helio G99-Ultra', '5100 mAh', '200MP + 8MP + 2MP', 'Android 13 (MIUI 14)'),

('OPPO Reno11 F 5G',
 (SELECT brand_id FROM brands WHERE brand_name = 'OPPO'),
 (SELECT category_id FROM categories WHERE category_name = 'OPPO'),
 'Thiết kế mỏng nhẹ, chuyên chụp chân dung, sạc nhanh 67W.',
 '6.7 inch AMOLED 120Hz', 'MediaTek Dimensity 7050', '5000 mAh', '64MP + 8MP + 2MP', 'Android 14 (ColorOS 14)'),

('OPPO A60',
 (SELECT brand_id FROM brands WHERE brand_name = 'OPPO'),
 (SELECT category_id FROM categories WHERE category_name = 'OPPO'),
 'Điện thoại phổ thông giá rẻ, pin lớn, bền bỉ cho nhu cầu cơ bản.',
 '6.67 inch LCD 90Hz', 'Snapdragon 680', '5000 mAh', '50MP + 2MP', 'Android 14 (ColorOS 14)');

-- ---------------------------------------------------------
-- PHIÊN BẢN (màu / dung lượng / giá / tồn kho)
-- ---------------------------------------------------------
INSERT INTO product_variants (product_id, color, storage, ram, price, original_price, stock_quantity, sku) VALUES
-- iPhone 16 Pro Max
((SELECT product_id FROM products WHERE product_name = 'iPhone 16 Pro Max'), 'Titan Sa Mạc', '256GB', '8GB', 34990000, 37990000, 25, 'IP16PM-DES-256'),
((SELECT product_id FROM products WHERE product_name = 'iPhone 16 Pro Max'), 'Titan Đen',    '256GB', '8GB', 34990000, 37990000, 30, 'IP16PM-BLK-256'),
((SELECT product_id FROM products WHERE product_name = 'iPhone 16 Pro Max'), 'Titan Đen',    '512GB', '8GB', 40990000, 43990000, 15, 'IP16PM-BLK-512'),
-- iPhone 15
((SELECT product_id FROM products WHERE product_name = 'iPhone 15'), 'Hồng', '128GB', '6GB', 17990000, 19990000, 40, 'IP15-PNK-128'),
((SELECT product_id FROM products WHERE product_name = 'iPhone 15'), 'Đen',  '128GB', '6GB', 17990000, 19990000, 45, 'IP15-BLK-128'),
((SELECT product_id FROM products WHERE product_name = 'iPhone 15'), 'Đen',  '256GB', '6GB', 20990000, 22990000, 20, 'IP15-BLK-256'),
-- Samsung Galaxy S24 Ultra
((SELECT product_id FROM products WHERE product_name = 'Samsung Galaxy S24 Ultra'), 'Xám Titan', '256GB', '12GB', 26990000, 33990000, 20, 'S24U-GRY-256'),
((SELECT product_id FROM products WHERE product_name = 'Samsung Galaxy S24 Ultra'), 'Đen Titan', '256GB', '12GB', 26990000, 33990000, 25, 'S24U-BLK-256'),
((SELECT product_id FROM products WHERE product_name = 'Samsung Galaxy S24 Ultra'), 'Đen Titan', '512GB', '12GB', 30990000, 37990000, 10, 'S24U-BLK-512'),
-- Samsung Galaxy A55 5G
((SELECT product_id FROM products WHERE product_name = 'Samsung Galaxy A55 5G'), 'Xanh Navy', '128GB', '8GB',  9990000, 11990000, 50, 'A55-NVY-128'),
((SELECT product_id FROM products WHERE product_name = 'Samsung Galaxy A55 5G'), 'Tím',       '256GB', '8GB', 11490000, 13490000, 35, 'A55-PUR-256'),
-- Xiaomi 14
((SELECT product_id FROM products WHERE product_name = 'Xiaomi 14'), 'Đen',  '256GB', '12GB', 18990000, 22990000, 20, 'XM14-BLK-256'),
((SELECT product_id FROM products WHERE product_name = 'Xiaomi 14'), 'Xanh', '512GB', '12GB', 21990000, 25990000, 12, 'XM14-GRN-512'),
-- Redmi Note 13 Pro
((SELECT product_id FROM products WHERE product_name = 'Redmi Note 13 Pro'), 'Đen',      '256GB', '8GB', 7490000, 8990000, 60, 'RN13P-BLK-256'),
((SELECT product_id FROM products WHERE product_name = 'Redmi Note 13 Pro'), 'Xanh lam', '256GB', '8GB', 7490000, 8990000, 40, 'RN13P-BLU-256'),
-- OPPO Reno11 F 5G
((SELECT product_id FROM products WHERE product_name = 'OPPO Reno11 F 5G'), 'Xanh Palm', '256GB', '8GB', 8990000, 10490000, 30, 'R11F-GRN-256'),
((SELECT product_id FROM products WHERE product_name = 'OPPO Reno11 F 5G'), 'Cam',       '256GB', '8GB', 8990000, 10490000, 25, 'R11F-ORG-256'),
-- OPPO A60
((SELECT product_id FROM products WHERE product_name = 'OPPO A60'), 'Tím',  '128GB', '8GB', 5490000, 6490000, 70, 'A60-PUR-128'),
((SELECT product_id FROM products WHERE product_name = 'OPPO A60'), 'Đen',  '128GB', '8GB', 5490000, 6490000, 80, 'A60-BLK-128');
