<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

defined( 'ABSPATH' ) || exit;

/**
 * Kiểm kê dữ liệu người làm trên MVP (docs/29 mục 2) — chỉ đếm, không ghi, không in lời. `wp phongdao kiem-ke` và
 * tệp chạy thử cong-cu/thu-nhap.php dùng chung. Khoá meta / tuỳ chọn lạ (chưa có trong bảng mục 2) được liệt kê
 * riêng để không sót loại dữ liệu nào.
 */
final class KiemKe {

	/** Khoá meta bài MVP đã có đích hoặc lý do không mang (docs/29 mục 2). */
	public const META_BAI = array(
		'_cadao_bam', '_cadao_ho_tro_ai', '_cadao_nhap_hieu_nhanh', '_cadao_nhap_hn_luc', '_cadao_di_ban_them', '_cadao_chu_thich_them',
		'_cadao_url_goc', '_cadao_ngay_lay', '_cadao_nguon_id', '_cadao_ma_goc', '_cadao_goi_y', '_cadao_goi_y_xong', '_cadao_ten_xep',
		'_cadao_chu_dau', '_cadao_gop_vao', '_cadao_ma_site_cu', '_cadao_wd_xet', '_cadao_anh_commons', 'nguon_ten', 'nguon_url',
		'ngay_truy_cap', 'doi_chieu_ten', 'doi_chieu_trang', 'doi_chieu_url', 'hop_khi', '_wp_old_slug', '_edit_last', '_edit_lock',
		'_wp_old_date', '_encloseme', '_pingme', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug',
	);

	/** Khoá term meta MVP đã biết. */
	public const META_THE = array(
		'_cadao_khoa', '_cadao_nguon_ten', '_cadao_nguon_url', '_cadao_chep', '_cadao_wd', '_cadao_wd_xet', '_cadao_anh_rieng',
		'_cadao_anh_ghi_cong', '_cadao_an_anh', '_cadao_mo_tip_dau', '_cadao_cc_so_nguoi', '_cadao_cc_do_tuoi', '_cadao_cc_do_dung', '_cadao_cc_wiki', '_cadao_cc_buoc',
		'_cadao_cc_nguon_ten', '_cadao_cc_nguon_url', '_cadao_cc_giay_phep',
	);

	/** Tuỳ chọn MVP đã biết (tên đầy đủ; tiền tố cadao_trang_ = trang sinh bởi mã). */
	public const TUY_CHON = array(
		'cadao_nguon_tu_dong', 'cadao_bo_chu_de', 'cadao_anh_xa_chu_de', 'cadao_mo_tip_bo', 'cadao_mo_tip_bo_qua', 'cadao_ban_co_biet',
		'cadao_site_cu', 'cadao_turnstile_site', 'cadao_turnstile_bi_mat', 'cadao_tinh_huong_cu', 'cadao_gop_tn', 'cadao_d068',
		'cadao_du_lieu_phien_ban', 'cadao_kho_phien_ban', 'cadao_luot_doc_phien_ban', 'cadao_su_kien_phien_ban', 'cadao_tim_phien_ban',
		'cadao_trung_phien_ban', 'cadao_wd_phien_ban', 'cadao_ten_xep_phien_ban', 'cadao_quy_tac_duong_dan', 'cadao_goi_y_sau',
		'cadao_the_tho_sau', 'cadao_tim_sau', 'cadao_tu_dien_sau', 'cadao_trung_lam_dan', 'cadao_trung_tu_gan', 'cadao_trung_tu_gan_sau',
		'cadao_wiktionary', 'cadao_theme_da_bat',
	);

	/**
	 * @return array{dong:list<array{0:string,1:string,2:int}>, la:array<string,string[]>}
	 *         dong: [nhóm, việc, số]; la: khoá meta bài / term meta / tuỳ chọn chưa có trong bảng kiểm kê
	 */
	public static function dem(): array {
		global $wpdb;
		$p    = $wpdb->posts;
		$pm   = $wpdb->postmeta;
		$bai  = DuLieuCu::LOAI_BAI;
		$q    = static fn( string $sql, ...$v ): int => (int) $wpdb->get_var( $v ? $wpdb->prepare( $sql, ...$v ) : $sql );
		$meta = static fn( string $khoa ): int => $q( "SELECT COUNT(DISTINCT m.post_id) FROM $pm m JOIN $p b ON b.ID = m.post_id WHERE b.post_type = %s AND b.post_status <> 'trash' AND m.meta_key = %s AND m.meta_value <> ''", $bai, $khoa );
		$bang = static fn( string $ten ): string => $wpdb->prefix . $ten;
		$dem_bang = static fn( string $ten, string $dk = '' ): int => DuLieuCu::co_bang( $bang( $ten ) ) ? $q( 'SELECT COUNT(*) FROM ' . $bang( $ten ) . ( $dk ? " WHERE $dk" : '' ) ) : -1;
		$the  = static fn( string $tax ): int => $q( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", $tax );
		$gan  = static fn( string $tax ): int => $q( "SELECT COUNT(*) FROM {$wpdb->term_relationships} r JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = r.term_taxonomy_id JOIN $p b ON b.ID = r.object_id WHERE tt.taxonomy = %s AND b.post_type = %s AND b.post_status <> 'trash'", $tax, $bai );
		// Lọc tiền tố bằng PHP: LIKE với ký tự thoát chạy khác nhau giữa MySQL và SQLite (Playground)
		$tm   = static fn( string $tax, string $tien_to ): int => count(
			array_unique(
				array_column(
					array_filter(
						$wpdb->get_results( $wpdb->prepare( "SELECT m.term_id, m.meta_key FROM {$wpdb->termmeta} m JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = m.term_id WHERE tt.taxonomy = %s AND m.meta_value <> ''", $tax ), ARRAY_A ) ?: array(),
						static fn( $r ) => str_starts_with( (string) $r['meta_key'], $tien_to )
					),
					'term_id'
				)
			)
		);
		$tt   = static fn( string $st ): int => $q( "SELECT COUNT(*) FROM $p WHERE post_type = %s AND post_status = %s", $bai, $st );
		$opt  = static function ( string $ten ): int {
			$v = get_option( $ten, null );
			return null === $v || '' === $v || array() === $v ? 0 : ( is_array( $v ) ? count( $v ) : 1 );
		};

		$sua_tay = 0;
		$khop    = 0;
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT b.ID, b.post_content, m.meta_value AS bam FROM $p b JOIN $pm m ON m.post_id = b.ID AND m.meta_key = '_cadao_bam' WHERE b.post_type = %s AND b.post_status <> 'trash'", $bai ) ) as $r ) {
			md5( (string) $r->post_content ) === (string) $r->bam ? ++$khop : ++$sua_tay;
		}

		$dong = array(
			array( 'Tác phẩm', 'đã đăng', $tt( 'publish' ) ),
			array( 'Tác phẩm', 'bản nháp', $tt( 'draft' ) ),
			array( 'Tác phẩm', 'chờ duyệt (máy tạo)', $tt( 'pending' ) ),
			array( 'Tác phẩm', 'riêng tư / hẹn giờ', $tt( 'private' ) + $tt( 'future' ) ),
			array( 'Tác phẩm', 'trong thùng rác (không mang)', $tt( 'trash' ) ),
			array( 'Lời, dị bản', 'bài có dấu băm, chưa sửa tay', $khop ),
			array( 'Lời, dị bản', 'bài có dấu băm, đã sửa tay', $sua_tay ),
			array( 'Lời, dị bản', 'bài không có dấu băm (nhập tay, site cũ)', $q( "SELECT COUNT(*) FROM $p b WHERE b.post_type = %s AND b.post_status <> 'trash' AND NOT EXISTS (SELECT 1 FROM $pm m WHERE m.post_id = b.ID AND m.meta_key = '_cadao_bam')", $bai ) ),
			array( 'Lời, dị bản', 'bài còn trong khối Đoạn văn (chưa có khối Thơ)', $q( "SELECT COUNT(*) FROM $p WHERE post_type = %s AND post_status <> 'trash' AND post_content NOT LIKE %s", $bai, '%<!-- wp:verse%' ) ),
			array( 'Lời, dị bản', 'dị bản thêm tay (_cadao_di_ban_them)', $meta( '_cadao_di_ban_them' ) ),
			array( 'Chú thích', 'chú thích thêm tay (_cadao_chu_thich_them)', $meta( '_cadao_chu_thich_them' ) ),
			array( 'Kho', 'câu trong kho', $dem_bang( 'cadao_kho' ) ),
			array( 'Kho', 'câu đã thành bài', $dem_bang( 'cadao_kho', "trang_thai = 'da_tao_bai'" ) ),
			array( 'Kho', 'câu đã gộp vào bài', $dem_bang( 'cadao_kho', "trang_thai = 'da_gop'" ) ),
			array( 'Kho', 'câu đã bỏ qua', $dem_bang( 'cadao_kho', "trang_thai = 'bo_qua'" ) ),
			array( 'Ý nghĩa', 'Hiểu nhanh đã lên trang', $q( "SELECT COUNT(*) FROM $p WHERE post_type = %s AND post_status <> 'trash' AND post_excerpt <> ''", $bai ) ),
			array( 'Ý nghĩa', 'có dấu "hỗ trợ AI"', $meta( '_cadao_ho_tro_ai' ) ),
			array( 'Ý nghĩa', 'nháp Hiểu nhanh của máy', $meta( '_cadao_nhap_hieu_nhanh' ) ),
			array( 'Nguồn', 'bài có nguồn người điền', $meta( 'nguon_ten' ) ),
			array( 'Nguồn', 'số tên nguồn khác nhau', $q( "SELECT COUNT(DISTINCT m.meta_value) FROM $pm m JOIN $p b ON b.ID = m.post_id WHERE b.post_type = %s AND b.post_status <> 'trash' AND m.meta_key = 'nguon_ten' AND m.meta_value <> ''", $bai ) ),
			array( 'Nguồn', 'bài có "Lời đã đối chiếu với"', $meta( 'doi_chieu_ten' ) ),
			array( 'Nguồn', 'bài có địa chỉ gốc máy đã lấy', $meta( '_cadao_url_goc' ) ),
			array( 'Tuyển tập', 'tình huống', $the( 'tinh_huong' ) ),
			array( 'Tuyển tập', 'bài gắn tình huống', $gan( 'tinh_huong' ) ),
			array( 'Tuyển tập', 'bài có "Hợp khi…"', $meta( 'hop_khi' ) ),
			array( 'Phân loại', 'thể loại (term / bài gắn)', $the( 'the_loai' ) ),
			array( 'Phân loại', 'bài gắn thể loại', $gan( 'the_loai' ) ),
			array( 'Phân loại', 'chủ đề', $the( 'chu_de' ) ),
			array( 'Phân loại', 'bài gắn chủ đề', $gan( 'chu_de' ) ),
			array( 'Phân loại', 'vùng', $the( 'vung' ) ),
			array( 'Phân loại', 'bài gắn vùng', $gan( 'vung' ) ),
			array( 'Phân loại', 'mô-típ', $the( 'mo_tip' ) ),
			array( 'Phân loại', 'bài gắn mô-típ', $gan( 'mo_tip' ) ),
			array( 'Phân loại', 'thể thơ', $the( 'the_tho' ) ),
			array( 'Phân loại', 'bài gắn thể thơ', $gan( 'the_tho' ) ),
			array( 'Từ điển', 'mục từ', $the( 'tu_kho' ) ),
			array( 'Từ điển', 'bài gắn mục từ', $gan( 'tu_kho' ) ),
			array( 'Từ điển', 'mục từ có nguồn lời giải', $tm( 'tu_kho', '_cadao_nguon_ten' ) ),
			array( 'Từ điển', 'mục từ có Wikidata đã xác nhận', $tm( 'tu_kho', '_cadao_wd' ) ),
			array( 'Từ điển', 'mục từ có ảnh riêng / ẩn ảnh', $tm( 'tu_kho', '_cadao_anh_rieng' ) + $tm( 'tu_kho', '_cadao_an_anh' ) ),
			array( 'Từ điển', 'quyết định Wikidata (bảng xác nhận)', $dem_bang( 'cadao_xac_nhan', "trang_thai <> 'cho'" ) ),
			array( 'Trò chơi', 'trò chơi', $the( 'tro_choi' ) ),
			array( 'Trò chơi', 'trò chơi có cách chơi', $tm( 'tro_choi', '_cadao_cc_' ) ),
			array( 'Trò chơi', 'bài gắn trò chơi', $gan( 'tro_choi' ) ),
			array( 'Bạn có biết', 'trong tuỳ chọn cadao_ban_co_biet', $opt( 'cadao_ban_co_biet' ) ),
			array( 'Cài đặt', 'bộ chủ đề / bảng ánh xạ chủ đề', $opt( 'cadao_bo_chu_de' ) + $opt( 'cadao_anh_xa_chu_de' ) ),
			array( 'Cài đặt', 'mô-típ đã đặt tên / bỏ qua', $opt( 'cadao_mo_tip_bo' ) + $opt( 'cadao_mo_tip_bo_qua' ) ),
			array( 'Cài đặt', 'nguồn tự động', $opt( 'cadao_nguon_tu_dong' ) ),
			array( 'Cài đặt', 'khoá Turnstile đã đặt (không in khoá)', $opt( 'cadao_turnstile_site' ) + $opt( 'cadao_turnstile_bi_mat' ) ),
			array( 'Chống trùng', 'cặp đã quyết (Là bài khác, Để sau…)', $dem_bang( 'cadao_trung', "trang_thai <> 'cho'" ) ),
			array( 'Chống trùng', 'cặp đang chờ', $dem_bang( 'cadao_trung', "trang_thai = 'cho'" ) ),
			array( 'Chống trùng', 'vân tay / cặp âm tiết', max( 0, $dem_bang( 'cadao_van_tay' ) ) + max( 0, $dem_bang( 'cadao_cap_am' ) ) ),
			array( 'Đường dẫn', 'bài có tên cũ (_wp_old_slug)', $meta( '_wp_old_slug' ) ),
			array( 'Đường dẫn', 'bài đã gộp vào bài khác', $meta( '_cadao_gop_vao' ) ),
			array( 'Đường dẫn', 'bài có tên đuôi số (-2, -3…)', count( preg_grep( '/-\d+$/', $wpdb->get_col( $wpdb->prepare( "SELECT post_name FROM $p WHERE post_type = %s AND post_status IN ('publish','draft','private','future')", $bai ) ) ) ) ),
			array( 'Đường dẫn', 'bài từ site cũ', $meta( '_cadao_ma_site_cu' ) ),
			array( 'Người đọc', 'góp ý (gop_y)', $q( "SELECT COUNT(*) FROM $p WHERE post_type = 'gop_y' AND post_status <> 'trash'" ) ),
			array( 'Người đọc', 'cảm nghĩ (bình luận trên bài)', $q( "SELECT COUNT(*) FROM {$wpdb->comments} c JOIN $p b ON b.ID = c.comment_post_ID WHERE b.post_type = %s AND c.comment_approved IN ('0','1')", $bai ) ),
			array( 'Người đọc', 'bài phân tích', $q( "SELECT COUNT(*) FROM $p WHERE post_type = 'phan_tich' AND post_status <> 'trash'" ) ),
			array( 'Đo lường', 'lượt đọc (dòng)', $dem_bang( 'cadao_luot_doc' ) ),
			array( 'Đo lường', 'sự kiện (dòng)', $dem_bang( 'cadao_su_kien' ) ),
			array( 'Đo lường', 'trang giới thiệu theo ngày (dòng)', $dem_bang( 'cadao_gioi_thieu' ) ),
			array( 'Khác', 'trang tĩnh', $q( "SELECT COUNT(*) FROM $p WHERE post_type = 'page' AND post_status IN ('publish','draft','private')" ) ),
			array( 'Khác', 'ảnh, tệp đã tải lên', $q( "SELECT COUNT(*) FROM $p WHERE post_type = 'attachment'" ) ),
			array( 'Khác', 'người dùng', $q( "SELECT COUNT(*) FROM {$wpdb->users}" ) ),
		);

		$la = array(
			'meta bài'  => array_values( array_diff( $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT m.meta_key FROM $pm m JOIN $p b ON b.ID = m.post_id WHERE b.post_type = %s", $bai ) ), self::META_BAI ) ),
			'term meta' => array_values( array_diff( preg_grep( '/^_cadao/', $wpdb->get_col( "SELECT DISTINCT meta_key FROM {$wpdb->termmeta}" ) ), self::META_THE ) ),
			'tuỳ chọn'  => array_values(
				array_filter(
					$wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'cadao%'" ),
					static fn( $t ) => str_starts_with( $t, 'cadao_' ) && ! in_array( $t, self::TUY_CHON, true ) && ! str_starts_with( $t, 'cadao_trang_' ) && ! str_starts_with( $t, 'cadao_url_' )
				)
			),
		);
		return array(
			'dong' => $dong,
			'la'   => $la,
		);
	}

	/** Báo cáo kiểm kê thành các dòng chữ; -1 = bảng không có. */
	public static function in(): array {
		$kk   = self::dem();
		$ra   = array( '== Kiểm kê dữ liệu MVP (không ghi gì) ==' );
		$nhom = '';
		foreach ( $kk['dong'] as [ $n, $viec, $so ] ) {
			if ( $n !== $nhom ) {
				$ra[] = "-- $n --";
				$nhom = $n;
			}
			$ra[] = '  ' . $viec . str_repeat( ' ', max( 1, 56 - mb_strlen( $viec ) ) ) . ( $so < 0 ? 'không có bảng' : (string) $so ); // canh theo ký tự, không theo byte
		}
		$ra[] = '-- Khoá chưa có trong bảng kiểm kê (docs/29 mục 2) --';
		foreach ( $kk['la'] as $loai => $ds ) {
			$ra[] = "  $loai: " . ( $ds ? implode( ', ', $ds ) : 'không có' );
		}
		return $ra;
	}
}
