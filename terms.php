<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
?>
<div class="form-card" style="max-width:900px;">
    <h1>Điều khoản sử dụng</h1>
    <p><strong>Ngày hiệu lực:</strong> <?= date('d/m/Y') ?></p>
    <p>Bằng việc truy cập hoặc đặt hàng tại TMH Perfume, bạn xác nhận đã đọc, hiểu và đồng ý bị ràng buộc bởi các điều khoản dưới đây.</p>

    <h2>1. Căn cứ pháp lý áp dụng</h2>
    <p>Các giao dịch trên website được thực hiện theo pháp luật Việt Nam hiện hành, bao gồm nhưng không giới hạn ở: Bộ luật Dân sự, Luật Bảo vệ quyền lợi người tiêu dùng, Luật Giao dịch điện tử và các văn bản hướng dẫn về thương mại điện tử.</p>

    <h2>2. Điều kiện sử dụng dịch vụ</h2>
    <ul>
        <li>Người dùng cung cấp thông tin chính xác khi đăng ký tài khoản, đặt hàng và thanh toán.</li>
        <li>Không sử dụng website cho mục đích gian lận, phát tán mã độc, gây gián đoạn hệ thống hoặc xâm phạm quyền, lợi ích hợp pháp của tổ chức/cá nhân khác.</li>
        <li>Tự chịu trách nhiệm với hoạt động phát sinh từ tài khoản của mình, bao gồm bảo mật mật khẩu và thiết bị đăng nhập.</li>
    </ul>

    <h2>3. Sản phẩm và thông tin hàng hóa</h2>
    <ul>
        <li>TMH Perfume cam kết minh bạch thông tin cơ bản về sản phẩm: tên hàng hóa, dung tích, giá bán, khuyến mại (nếu có).</li>
        <li>Hình ảnh sản phẩm có thể có sai lệch nhỏ về màu sắc do màn hình/thiết bị hiển thị.</li>
        <li>Trong trường hợp có lỗi hiển thị giá hoặc thông tin do sự cố kỹ thuật, TMH Perfume có quyền từ chối hoặc hủy đơn và thông báo lại cho khách hàng.</li>
    </ul>

    <h2>4. Giao kết hợp đồng điện tử</h2>
    <ul>
        <li>Đơn hàng được xem là đề nghị giao kết từ phía khách hàng.</li>
        <li>Hợp đồng mua bán được xác lập khi TMH Perfume xác nhận đơn hàng thành công (qua website, email, điện thoại hoặc phương thức liên hệ phù hợp).</li>
        <li>TMH Perfume có quyền từ chối xác nhận đơn trong các trường hợp bất khả kháng, nghi ngờ gian lận, hoặc thông tin giao nhận không hợp lệ.</li>
    </ul>

    <h2>5. Giá bán, thanh toán và hóa đơn</h2>
    <ul>
        <li>Giá niêm yết trên website là cơ sở tính tiền tại thời điểm khách đặt hàng thành công, trừ trường hợp có lỗi kỹ thuật rõ ràng.</li>
        <li>Khách hàng thanh toán theo các phương thức được TMH Perfume cung cấp tại bước thanh toán.</li>
        <li>Phí vận chuyển, chiết khấu, khuyến mãi được hiển thị minh bạch trước khi khách xác nhận đặt hàng.</li>
    </ul>

    <h2>6. Giao hàng, kiểm hàng, chuyển rủi ro</h2>
    <ul>
        <li>Thời gian giao hàng mang tính dự kiến và có thể thay đổi vì điều kiện khách quan (thời tiết, vận chuyển, khu vực hạn chế).</li>
        <li>Khách hàng có quyền kiểm tra tình trạng bên ngoài gói hàng tại thời điểm nhận.</li>
        <li>Rủi ro về mất mát/hư hỏng hàng hóa được chuyển cho khách sau khi khách hoặc người được ủy quyền xác nhận đã nhận hàng.</li>
    </ul>

    <h2>7. Đổi trả, hoàn tiền, bảo hành cam kết</h2>
    <ul>
        <li>TMH Perfume tiếp nhận yêu cầu đổi trả theo chính sách công bố trên website và quy định pháp luật bảo vệ người tiêu dùng.</li>
        <li>Điều kiện xử lý đổi trả có thể bao gồm: còn hóa đơn/chứng từ, sản phẩm còn tem niêm phong, chưa qua sử dụng hoặc có lỗi từ nhà sản xuất.</li>
        <li>Thời gian và phương thức hoàn tiền thực hiện theo thỏa thuận cụ thể cho từng trường hợp hợp lệ.</li>
    </ul>

    <h2>8. Bảo vệ dữ liệu cá nhân và quyền riêng tư</h2>
    <ul>
        <li>TMH Perfume thu thập, lưu trữ, xử lý dữ liệu cá nhân ở phạm vi cần thiết để vận hành đơn hàng, chăm sóc khách hàng và tuân thủ nghĩa vụ pháp lý.</li>
        <li>Không bán dữ liệu cá nhân trái phép cho bên thứ ba.</li>
        <li>Khách hàng có quyền yêu cầu cập nhật, chỉnh sửa hoặc xóa thông tin theo quy định pháp luật và chính sách bảo mật của website.</li>
    </ul>

    <h2>9. Quyền sở hữu trí tuệ</h2>
    <ul>
        <li>Toàn bộ nội dung trên website (logo, tên thương mại, hình ảnh, bố cục, văn bản) thuộc quyền sở hữu hoặc quyền sử dụng hợp pháp của TMH Perfume.</li>
        <li>Nghiêm cấm sao chép, chỉnh sửa, phát hành lại nội dung mà không có sự chấp thuận bằng văn bản của TMH Perfume.</li>
    </ul>

    <h2>10. Giới hạn trách nhiệm</h2>
    <ul>
        <li>TMH Perfume không chịu trách nhiệm với thiệt hại gián tiếp, lợi nhuận bị mất hoặc tổn thất phát sinh từ việc sử dụng website không đúng hướng dẫn.</li>
        <li>Trong mọi trường hợp, trách nhiệm bồi thường (nếu có) được giới hạn trong phạm vi giá trị đơn hàng liên quan theo quy định pháp luật.</li>
    </ul>

    <h2>11. Tạm ngừng hoặc chấm dứt cung cấp dịch vụ</h2>
    <p>TMH Perfume có quyền tạm ngừng/chấm dứt quyền truy cập tài khoản hoặc từ chối phục vụ nếu phát hiện hành vi vi phạm điều khoản, gian lận thanh toán, hoặc gây rủi ro cho hệ thống và cộng đồng người dùng.</p>

    <h2>12. Sửa đổi điều khoản</h2>
    <p>TMH Perfume có thể cập nhật điều khoản để phù hợp với thực tiễn vận hành và quy định pháp luật mới. Phiên bản cập nhật có hiệu lực kể từ thời điểm đăng tải trên website.</p>

    <h2>13. Luật áp dụng và giải quyết tranh chấp</h2>
    <ul>
        <li>Mọi tranh chấp phát sinh được ưu tiên giải quyết bằng thương lượng, hòa giải trên tinh thần hợp tác.</li>
        <li>Trường hợp không đạt được thỏa thuận, tranh chấp sẽ được giải quyết tại cơ quan có thẩm quyền theo pháp luật Việt Nam.</li>
    </ul>

    <h2>14. Thông tin liên hệ</h2>
    <p>Nếu cần hỗ trợ về điều khoản, vui lòng liên hệ TMH Perfume qua email hoặc số điện thoại công bố tại footer website.</p>
</div>
<?php include __DIR__ . '/includes/footer.php';
