<?php
/** So trước/sau trong Lịch sử (MienDuLieu\SoSanh, T-170). Dữ liệu tổng hợp. */

use Phongdao\MienDuLieu\SoSanh;

$loi = array( 'dong' => array( 'Dòng mẫu một', 'Dòng mẫu hai' ), 'nguoi_lam' => 'nguoi_viet', 'sua_boi' => 3, 'sua_luc' => '2026-11-09 10:00:00', 'doi_chieu' => array( 'ho_so' => 7, 'trang' => '12' ) );
kt( 'trải lời: mỗi dòng một dòng, bỏ người sửa/giờ sửa, đối chiếu thành một dòng', array( 'Dòng mẫu một', 'Dòng mẫu hai', 'Đối chiếu: Hồ sơ nguồn: 7 · Trang: 12' ) === SoSanh::dong( $loi ), SoSanh::dong( $loi ) );
kt( 'trải ý nghĩa nhiều dòng và dấu hỗ trợ AI', array( 'Câu một.', 'Câu hai.', '(có hỗ trợ AI)' ) === SoSanh::dong( array( 'chu' => "Câu một.\nCâu hai.", 'ai' => true, 'nguoi_lam' => 'ai_da_duyet' ) ) );
$ct = array( 'muc' => array( array( 'tu' => 'chút', 'loi_giai' => 'Một ít.', 'vi_tri' => array( 1 ) ), array( 'loi_giai' => 'Chú thích chung.' ) ) );
kt( 'trải danh sách (từ khó): đánh số mục, nhãn tiếng Việt, bỏ vị trí', array( '1. Từ: chút', '    Lời giải: Một ít.', '2. Lời giải: Chú thích chung.' ) === SoSanh::dong( $ct ), SoSanh::dong( $ct ) );
kt( 'trải phân loại (tên mục) và trạng thái bài', array( 'Lao động', 'Hát ru' ) === SoSanh::dong( array( 'term_ids' => array( 3, 9 ), 'ten' => array( 'Lao động', 'Hát ru' ) ) ) && array( 'Trạng thái: Đã ẩn' ) === SoSanh::dong( array( 'trang_thai' => 'private' ) ) );
kt( 'giá trị trống → không dòng nào', array() === SoSanh::dong( null ) && array() === SoSanh::dong( array() ) );
$k = SoSanh::khac( array( 'a', 'b', 'c', 'd' ), array( 'a', 'x', 'c', 'd', 'e' ) );
kt( 'so dòng: giữ dòng chung, đánh dấu dòng bỏ / dòng thêm đúng thứ tự', array( array( '=', 'a' ), array( '-', 'b' ), array( '+', 'x' ), array( '=', 'c' ), array( '=', 'd' ), array( '+', 'e' ) ) === $k, $k );
kt( 'so dòng: thêm từ trống, gỡ hết', array( array( '+', 'a' ) ) === SoSanh::khac( array(), array( 'a' ) ) && array( array( '-', 'a' ), array( '-', 'b' ) ) === SoSanh::khac( array( 'a', 'b' ), array() ) );
$dai = array_map( static fn( int $i ): string => "Dòng $i", range( 1, 400 ) );
$bd  = microtime( true );
$kd  = SoSanh::khac( $dai, array_merge( array_slice( $dai, 0, 200 ), array( 'Dòng mới' ), array_slice( $dai, 201 ) ) );
kt( 'so bài dài 400 dòng: đúng một dòng bỏ, một dòng thêm, dưới 1 giây', 1 === count( array_filter( $kd, static fn( $o ) => '-' === $o[0] ) ) && 1 === count( array_filter( $kd, static fn( $o ) => '+' === $o[0] ) ) && microtime( true ) - $bd < 1 );
$td = SoSanh::truong_doi( array( 'ten' => 'Sách A', 'chep_chu_thich' => false, 'trang' => '' ), array( 'ten' => 'Sách A', 'chep_chu_thich' => true, 'trang' => '5' ) );
kt( 'hồ sơ / cài đặt: chỉ trường đổi, "cũ → mới", trống ghi (trống)', array( 'Chép chú thích: Không → Có', 'Trang: (trống) → 5' ) === $td, $td );
