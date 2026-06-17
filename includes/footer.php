</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <section class="footer-contact">
            <h3>Liên hệ</h3>
            <p>TMH Perfume - Nước hoa chính hãng cao cấp.</p>
            <p>Địa chỉ: Số 8, Bùi Xuân Phái, Hà Nội</p>
            <p>Điện thoại: <a href="tel:0355152212">0355 152 212</a></p>
            <p>Email: <a href="mailto:buiiducthangg@gmail.com">buiiducthangg@gmail.com</a></p>

            <div class="footer-map-card">
                <h4>Bản đồ vệ tinh - Đại học Hòa Bình</h4>
                <div class="footer-map-wrap">
                    <iframe
                        title="Bản đồ vệ tinh Đại học Hòa Bình"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://maps.google.com/maps?q=%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20H%C3%B2a%20B%C3%ACnh%20H%C3%A0%20N%E1%BB%99i&t=k&z=16&output=embed">
                    </iframe>
                </div>
                <p><a href="https://maps.google.com/?q=%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20H%C3%B2a%20B%C3%ACnh%20H%C3%A0%20N%E1%BB%99i" target="_blank" rel="noopener noreferrer">Mở bản đồ lớn</a></p>
            </div>
        </section>

        <section class="footer-link-column">
            <h3>Liên kết hữu ích</h3>
            <ul class="footer-links">
                <li><a href="<?= url('privacy-policy.php') ?>">Chính sách bảo mật</a></li>
                <li><a href="<?= url('terms.php') ?>">Điều khoản sử dụng</a></li>
                <li><a href="<?= url('sitemap.php') ?>">Sơ đồ website</a></li>
                <li><a href="<?= url('faq.php') ?>">Câu hỏi thường gặp</a></li>
            </ul>
        </section>

        <section class="footer-link-column">
            <h3>Mạng xã hội</h3>
            <ul class="footer-links">
                <li><a href="https://facebook.com" target="_blank" rel="noopener noreferrer">Facebook</a></li>
                <li><a href="https://instagram.com" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                <li><a href="https://tiktok.com" target="_blank" rel="noopener noreferrer">TikTok</a></li>
            </ul>
            <p>Đối tác vận chuyển: Giao Hàng Nhanh, Viettel Post, J&T Express.</p>
            <p>Chứng nhận: Cam kết hàng chính hãng, kiểm tra trước khi nhận.</p>
        </section>

        <section>
            <h3>Đăng ký nhận tin</h3>
            <p>Nhận ưu đãi mới nhất và mã giảm giá mỗi tuần.</p>
            <form class="newsletter-form" action="<?= url('subscribe.php') ?>" method="post">
                <input type="email" name="email" placeholder="Nhập email của bạn" required>
                <button class="btn full" type="submit">Đăng ký</button>
            </form>
        </section>
    </div>

    <div class="container footer-bottom">
        <p>Copyright © <?= date('Y') ?> TMH Perfume. All rights reserved.</p>
    </div>
</footer>

<div class="live-chat-widget">
    <button id="chatToggle" class="chat-toggle" type="button">Hỗ trợ trực tuyến</button>
    <div id="chatPanel" class="chat-panel">
        <h4>Tư vấn nhanh</h4>
        <p>Bạn cần hỗ trợ chọn mùi hương hoặc đơn hàng?</p>
            <a class="btn full" href="<?= url('lien-he.php') ?>">Gửi yêu cầu tư vấn</a>
        <a class="btn light full" href="https://m.me" target="_blank" rel="noopener noreferrer">Chat Facebook</a>
        <a class="btn light full" href="https://zalo.me" target="_blank" rel="noopener noreferrer">Chat Zalo</a>
    </div>
</div>

<script src="<?= BASE_URL === '/' ? '/assets/app.js' : BASE_URL . '/assets/app.js' ?>"></script>
</body>
</html>
