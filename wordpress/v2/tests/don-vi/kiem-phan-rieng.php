<?php
/**
 * Phần riêng của tác phẩm (MienDuLieu\PhanRieng, D-117): chỉ bài có phần thư viện tự làm thêm mới lên Google khi mở.
 * Lời và phần máy chép từ nguồn không tính. Dữ liệu tổng hợp.
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\PhanRieng;

$loi = array( 'loi' => array( 'dong' => array( 'Dòng mẫu một', 'Dòng mẫu hai' ), 'nguoi_lam' => 'may_chep' ) );
kt( 'phần riêng: bài chỉ có lời, đáp án, câu hỏi, chú thích / dị bản / nghĩa máy chép → không có phần riêng', ! PhanRieng::co( $loi ) && ! PhanRieng::co( $loi + array( 'dap_an' => array( 'chu' => 'Cái kim' ), 'cau_hoi' => array( 'chu' => 'Là cái gì?' ), 'chu_thich' => array( 'muc' => array( array( 'tu' => 'mẫu', 'loi_giai' => 'Giải chép.', 'nguoi_lam' => 'may_chep' ) ) ), 'di_ban' => array( 'muc' => array( array( 'dong' => array( 'Dị bản chép' ), 'nguoi_lam' => 'may_chep' ) ) ), 'nghia_bong' => array( 'chu' => 'Nghĩa chép Wiktionary', 'nguoi_lam' => 'may_chep' ), 'media' => array( 'muc' => array( array( 'attachment_id' => 1, 'loai' => 'giong_may' ) ) ) ) ) );
kt(
	'phần riêng: Ý nghĩa (kể cả có hỗ trợ AI đã duyệt), nghĩa bóng người viết, bối cảnh có sự kiện, chú thích / dị bản người viết hoặc bạn đọc góp, đối chiếu sách, ảnh, câu SGK — mỗi thứ đều đủ',
	PhanRieng::co( $loi + array( 'y_nghia' => array( 'chu' => 'Nghĩa.', 'nguoi_lam' => 'ai_da_duyet' ) ) )
	&& PhanRieng::co( $loi + array( 'nghia_bong' => array( 'chu' => 'Bóng.', 'nguoi_lam' => 'nguoi_viet' ) ) )
	&& PhanRieng::co( $loi + array( 'boi_canh' => array( 'su_kien' => 'Một sự kiện' ) ) )
	&& PhanRieng::co( $loi + array( 'chu_thich' => array( 'muc' => array( array( 'tu' => 'a', 'loi_giai' => 'chép', 'nguoi_lam' => 'may_chep' ), array( 'tu' => 'b', 'loi_giai' => 'tự viết', 'nguoi_lam' => 'nguoi_viet' ) ) ) ) )
	&& PhanRieng::co( $loi + array( 'di_ban' => array( 'muc' => array( array( 'dong' => array( 'Ở quê tôi' ), 'nguoi_lam' => 'doc_gia_gop' ) ) ) ) )
	&& PhanRieng::co( array( 'loi' => array( 'dong' => array( 'a' ), 'doi_chieu' => array( 'ho_so' => 7, 'trang' => '12' ) ) ) )
	&& PhanRieng::co( $loi + array( 'media' => array( 'muc' => array( array( 'attachment_id' => 3, 'loai' => 'anh' ) ) ) ) )
	&& PhanRieng::co( $loi + array( 'sgk' => array( 'muc' => array( array( 'bo_sach' => 'Mẫu', 'lop' => 6, 'bai' => '1' ) ) ) ) )
);
kt( 'phần riêng: phần có nhưng rỗng chữ không tính; danh sách phần trả theo thứ tự, có đối chiếu sách', ! PhanRieng::co( $loi + array( 'y_nghia' => array( 'chu' => '  ' ), 'boi_canh' => array( 'nhan_vat' => array() ) ) ) && array( 'y_nghia', 'chu_thich', 'doi_chieu' ) === PhanRieng::cac( array( 'loi' => array( 'dong' => array( 'a' ), 'doi_chieu' => array( 'ho_so' => 1 ) ), 'y_nghia' => array( 'chu' => 'x' ), 'chu_thich' => array( 'muc' => array( array( 'tu' => 'a', 'loi_giai' => 'b' ) ) ) ) ) );
