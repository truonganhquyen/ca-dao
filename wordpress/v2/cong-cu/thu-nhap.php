<?php
/**
 * Chạy thử nhập dữ liệu trên site thật khi máy chủ còn chạy MVP (docs/29 mục 4) — CHỈ ĐỌC.
 *
 *   wp eval-file <thư mục chứa wordpress/v2>/cong-cu/thu-nhap.php
 *
 * In báo cáo kiểm kê và nhập thử (số lượng, mã bài, lý do; không in lời). Không nạp plugin V2, không đăng ký hook nào:
 * chỉ dùng các lớp đọc / tính của V2 qua bộ tự nạp. Mọi truy vấn nằm trong một giao dịch được huỷ ở cuối, nên kể cả
 * khi có lỗi ngoài ý muốn cũng không có gì được ghi lại. Mỗi lần chạy trên site thật cần người sáng lập đồng ý
 * cho lần đó (AGENTS.md).
 */

defined( 'ABSPATH' ) || exit( "Chạy bằng: wp eval-file cong-cu/thu-nhap.php\n" );

require_once __DIR__ . '/../plugins/ca-dao/src/tu-nap.php';

global $wpdb;
$wpdb->query( 'START TRANSACTION' );
try {
	$dong = array_merge(
		\Phongdao\DichVu\NhapDuLieuCu\KiemKe::in(),
		array( '' ),
		\Phongdao\DichVu\NhapDuLieuCu\NhapThu::in( \Phongdao\DichVu\NhapDuLieuCu\NhapThu::chay() )
	);
	echo implode( "\n", $dong ), "\n";
} finally {
	$wpdb->query( 'ROLLBACK' );
}
