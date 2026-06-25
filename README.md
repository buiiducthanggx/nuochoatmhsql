# nuochoatmhsql

<p align="center">
  <b>E-commerce Website for Perfume (Nước hoa)</b>
</p>

<p align="center">
  Website bán nước hoa với giao diện hiện đại, thân thiện người dùng và hướng tới trải nghiệm mua sắm trực tuyến tiện lợi.
</p>

---

## 📌 Giới thiệu dự án

`nuochoatmhsql` là một dự án website thương mại điện tử dành cho sản phẩm nước hoa.  
Dự án được xây dựng nhằm mô phỏng một hệ thống bán hàng trực tuyến với đầy đủ các chức năng cơ bản như:

- Xem danh sách sản phẩm
- Xem chi tiết sản phẩm
- Tìm kiếm và lọc sản phẩm
- Quản lý giỏ hàng
- Đặt hàng
- Quản lý đơn hàng
- Theo dõi tiến độ công việc qua Jira Board



---

## 🎯 Mục tiêu của dự án

- Xây dựng một website bán hàng có giao diện đẹp và dễ sử dụng
- Ứng dụng các kiến thức về phát triển web vào thực tế
- Rèn luyện kỹ năng làm việc với giao diện, xử lý dữ liệu và quy trình đặt hàng
- Tạo ra một sản phẩm có tính hoàn thiện để phục vụ báo cáo đồ án

---

## ✨ Tính năng chính

### 1. Giao diện người dùng
- Trang chủ hiển thị sản phẩm nổi bật
- Thiết kế trực quan, hiện đại
- Tối ưu hiển thị trên nhiều kích thước màn hình

### 2. Quản lý sản phẩm
- Danh sách sản phẩm nước hoa
- Thông tin chi tiết: tên, giá, mô tả, hình ảnh
- Tìm kiếm sản phẩm nhanh chóng
- Lọc theo danh mục hoặc tiêu chí phù hợp

### 3. Giỏ hàng và đặt hàng
- Thêm sản phẩm vào giỏ hàng
- Cập nhật số lượng sản phẩm
- Xóa sản phẩm khỏi giỏ hàng
- Thực hiện đặt hàng theo quy trình đơn giản

### 4. Quản lý đơn hàng
- Theo dõi trạng thái đơn hàng
- Hiển thị thông tin đơn hàng đã đặt
- Hỗ trợ kiểm tra lịch sử mua hàng

### 5. Quản lý công việc
- Theo dõi tiến độ thực hiện dự án qua Jira Board
- Phân chia công việc rõ ràng
- Hỗ trợ quản lý nhóm hiệu quả

---

## 🛠️ Công nghệ sử dụng



- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Quản lý dự án:** Jira
- **Công cụ phát triển:** Git, GitHub, VS Code
- **Triển khai:** Docker

---

## 📁 Cấu trúc dự án

```bash
nuochoatmhsql/
├── assets/                # Hình ảnh, CSS, JS, icon và tài nguyên giao diện
├── includes/              # Các file dùng chung, header/footer, kết nối hoặc tiện ích
├── uploads/               # File upload từ người dùng hoặc quản trị
├── add_to_cart.php        # Thêm sản phẩm vào giỏ hàng
├── admin_orders.php       # Trang quản lý đơn hàng cho admin
├── admin_users.php        # Trang quản lý người dùng cho admin
├── auth.php               # Xử lý xác thực và phân quyền
├── blog.php               # Trang blog/tin tức
├── cart.php               # Trang giỏ hàng
├── category.php           # Trang hiển thị sản phẩm theo danh mục
├── checkout.php           # Trang thanh toán
├── config.php             # Cấu hình hệ thống
├── config-production.php  # Cấu hình khi deploy production
├── docker-compose.yml     # Cấu hình Docker Compose
├── faq.php                # Câu hỏi thường gặp
├── gioi-thieu.php         # Trang giới thiệu
├── index.php              # Trang chủ
├── lien-he.php            # Trang liên hệ
├── login.php              # Trang đăng nhập
├── logout.php             # Đăng xuất
├── manage_products.php    # Quản lý sản phẩm
├── my_orders.php          # Danh sách đơn hàng của tôi
├── nuoc-hoa-tmh-sql.sql   # File cơ sở dữ liệu
├── order_detail.php       # Chi tiết đơn hàng
├── place_order.php        # Xử lý đặt hàng
├── product.php            # Trang chi tiết sản phẩm
├── profile.php            # Trang thông tin cá nhân
├── privacy-policy.php     # Chính sách bảo mật
├── register.php           # Trang đăng ký tài khoản
├── remove_cart_item.php   # Xóa sản phẩm khỏi giỏ hàng
├── reset_db.php           # Reset cơ sở dữ liệu
├── sanpham.csv            # Dữ liệu sản phẩm mẫu
├── search_suggest.php     # Gợi ý tìm kiếm
├── sitemap.php            # Tạo sitemap
├── subscribe.php          # Đăng ký nhận tin
├── terms.php              # Điều khoản sử dụng
├── README.md              # Tài liệu dự án
└── ...
```

---

## 🚀 Cách cài đặt và chạy dự án

### 1. Clone repository
```bash
git clone https://github.com/buiiducthanggx/nuochoatmhsql.git
cd nuochoatmhsql
```

### 2. Cài đặt và cấu hình
- Import file cơ sở dữ liệu `nuoc-hoa-tmh-sql.sql` vào MySQL
- Cập nhật thông tin kết nối trong `config.php` hoặc `config-production.php`
- Nếu dùng Docker, kiểm tra lại `docker-compose.yml`

### 3. Chạy dự án
```bash
php -S localhost:8000
```

> Hoặc chạy bằng môi trường web server như XAMPP, Laragon, Docker tùy theo cách triển khai.


## 🔗 Liên kết nhanh

- **Jira Board (WBHNH):** [Mở Jira board](https://nhguyenduong12345.atlassian.net/jira/software/projects/WBHNH/boards/36?atlOrigin=eyJpIjoiYjBhY2RiYTEzZDdiNGUyZmEyYWY1MWJlNGVmOTYxZGEiLCJwIjoiaiJ9)
- **GitHub Repository:** [nuochoatmhsql](https://github.com/buiiducthanggx/nuochoatmhsql)

---

## 👨‍💻 Thành viên / Tác giả

- [buiiducthanggx](https://github.com/buiiducthanggx)
- Tùng Dương
- Việt Hoàng

---

---

## 📌 Phân công công việc

- **Frontend:** Thiết kế giao diện, hiển thị sản phẩm, giỏ hàng, thanh toán
- **Backend:** Xử lý logic đăng nhập, đặt hàng, quản lý dữ liệu
- **Database:** Thiết kế và tối ưu cơ sở dữ liệu
- **Project Management:** Theo dõi tiến độ và phân chia công việc qua Jira
- **Testing:** Kiểm tra lỗi, đánh giá chức năng và trải nghiệm người dùng

---

## 📝 Hướng phát triển trong tương lai

- Bổ sung chức năng đăng nhập/đăng ký nâng cao
- Thêm thanh toán trực tuyến
- Tích hợp đánh giá và bình luận sản phẩm
- Gợi ý sản phẩm thông minh theo hành vi người dùng
- Cải thiện giao diện và tối ưu hiệu năng

---

## 📄 Giấy phép

Dự án được thực hiện phục vụ mục đích học tập và đồ án môn học.
