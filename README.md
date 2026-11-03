# PhoneShop - Website bán điện thoại online

Đồ án Web - Trường Đại học Kinh doanh và Công nghệ Hà Nội
Công nghệ: **PHP (PDO) + MySQL**, chạy trên **XAMPP**.

Sinh viên thực hiện: …Trần Mnh Tú…………………… - MSSV: …TH29.18……………

## Chức năng

**Khách hàng:** xem trang chủ, danh mục (lọc theo hãng, mức giá, sắp xếp), tìm kiếm, chi tiết sản phẩm (chọn phiên bản và màu), giỏ hàng, đặt hàng (có mã giảm giá), xem và hủy đơn, liên hệ, đăng ký, đăng nhập.

**Quản trị viên:** thêm, sửa, xóa sản phẩm / phiên bản / hình ảnh; quản lý đơn hàng; khóa, mở khóa khách hàng; xem tin nhắn liên hệ; thống kê.

## Hướng dẫn cài đặt

1. Cài [XAMPP](https://www.apachefriends.org/), mở XAMPP Control Panel và Start **Apache** và **MySQL**.
2. Chép thư mục dự án vào `C:\xampp\htdocs\banhang`.
3. Vào `http://localhost/phpmyadmin`, tab **Nhập (Import)**, nhập lần lượt 4 file SQL theo đúng thứ tự:
   1. `banhang_dienthoai.sql` (tạo database và các bảng)
   2. `them_mau_dien_thoai.sql` (thêm sản phẩm mẫu)
   3. `them_anh_san_pham.sql` (khai báo ảnh sản phẩm)
   4. `them_chuc_nang.sql` (bảng liên hệ và mã giảm giá mẫu)
4. Bỏ ảnh sản phẩm vào thư mục `images/` (tên file xem trong `images/DOC_TOI_DAY.txt`) hoặc tải ảnh lên trong trang Quản trị.
5. Mở `http://localhost/banhang/`.

Cấu hình kết nối CSDL nằm trong `config.php` (mặc định: host `localhost`, user `root`, mật khẩu trống, database `banhang_dienthoai`).

## Tài khoản

- Quản trị viên: `admin@shop.com` (mật khẩu do người chạy tự đặt trong phpMyAdmin, hoặc đăng ký tài khoản rồi đổi cột `role` thành `admin`).
- Mã giảm giá thử: `SALE5` (giảm 5%), `GIAM500K` (giảm 500.000đ cho đơn từ 5 triệu).

## Cấu trúc thư mục

```
banhang/
├── config.php            kết nối CSDL, session, phân quyền
├── index.php             trang chủ
├── category.php          danh mục, lọc, tìm kiếm
├── product.php           chi tiết sản phẩm
├── cart.php              giỏ hàng
├── checkout.php          đặt hàng
├── orders.php            đơn hàng của tôi
├── contact.php           liên hệ
├── login.php, register.php, logout.php
├── style.css
├── includes/             header, footer, hàm dùng chung
├── images/               ảnh sản phẩm
├── admin/                trang quản trị (sản phẩm, đơn hàng, khách hàng, liên hệ)
└── *.sql                 file tạo CSDL và dữ liệu mẫu
```
