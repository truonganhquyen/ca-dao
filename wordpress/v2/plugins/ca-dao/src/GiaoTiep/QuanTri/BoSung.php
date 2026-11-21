<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Bổ sung** trong wp-admin (D-103; làm lại D-121, docs/42 mục 6): làm bài đã đăng đầy đủ hơn, không chỉ tiêu. Một
 * dòng tiến độ lên Google ở đầu, rồi năm tab (tham số `xem`): **Thiếu Ý nghĩa** (bài đọc nhiều chưa có, nút Bắt đầu) ·
 * **Cần sửa** (máy rà theo quy tắc viết, Ý nghĩa dài) · **Gợi ý chủ đề, vùng** · **Việc khác** (danh sách ưu tiên: đối
 * chiếu sách, từ khó, nghĩa tục ngữ, câu đố, cờ) · **Nháp Ý nghĩa theo lô**. Đọc từ bảng tóm tắt bài; nút "Bắt đầu" mở
 * bài đầu ở trang Sửa tác phẩm kèm lối "Bài tiếp theo". Chỉ đọc và dẫn đường; máy không viết nội dung, không tự gắn.
 */
final class BoSung {

	public const TRANG = 'phongdao-bo-sung';

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::TRANG );
	}

	/** Năm tab của màn (khoá tham số `xem` => tên). */
	public const TAB = array(
		'y_nghia' => 'Thiếu Ý nghĩa',
		'can_sua' => 'Cần sửa',
		'goi_y'   => 'Gợi ý chủ đề, vùng',
		'tu_dien' => 'Gợi ý Từ điển',
		'khac'    => 'Việc khác',
		'nhap'    => 'Nháp Ý nghĩa theo lô',
	);

	/** Địa chỉ một tab (tab đầu không cần tham số). */
	public static function url_tab( string $tab ): string {
		return 'y_nghia' === $tab || ! isset( self::TAB[ $tab ] ) ? self::url() : add_query_arg( array( 'page' => self::TRANG, 'xem' => $tab ), admin_url( 'admin.php' ) );
	}

	/**
	 * Bài đọc nhiều 7 ngày qua mà chưa có Ý nghĩa (tối đa $so bài, đọc nhiều trước).
	 *
	 * @return int[]
	 */
	public static function doc_nhieu_thieu( int $so = 10 ): array {
		global $wpdb;
		\Phongdao\DichVu\TomTat\TomTat::xong();
		// Đọc từ bảng tóm tắt bài (lượt đọc 7 ngày tính sẵn): một truy vấn, không đọc từng bài
		return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . \Phongdao\DichVu\TomTat\TomTat::bang() . " WHERE trang_thai = 'publish' AND doc_7 > 0 AND thieu LIKE %s ORDER BY doc_7 DESC, post_id ASC LIMIT %d", '%,y_nghia,%', $so ) ) ?: array() ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng của plugin
	}

	/**
	 * Danh sách ưu tiên: [tên, số, đường dẫn danh sách, đường dẫn bắt đầu] — "bắt đầu" mở bài đầu tiên của danh sách ở trang
	 * sửa, giữ lối về đúng danh sách đã lọc để sang bài kế.
	 *
	 * @return array<int,array{0:string,1:int,2:string,3:string}>
	 */
	public static function uu_tien(): array {
		$ra = array();
		foreach (
			array(
				array( 'Bài đã đăng chưa có Ý nghĩa', array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia', 'xep' => 'doc' ) ),
				array( 'Tục ngữ thiếu nghĩa đen / nghĩa bóng', array( 'tt' => 'publish', 'muc' => 'thieu_nghia_tn', 'xep' => 'doc' ) ),
				array( 'Câu đố thiếu đáp án', array( 'tt' => 'publish', 'muc' => 'thieu_dap_an', 'xep' => 'doc' ) ),
				array( 'Câu đố thiếu giải thích đáp án', array( 'tt' => 'publish', 'muc' => 'thieu_giai_thich', 'xep' => 'doc' ) ),
				array( 'Thiếu từ khó', array( 'tt' => 'publish', 'muc' => 'thieu_tu_kho', 'xep' => 'doc' ) ),
				array( 'Thiếu đối chiếu sách', array( 'tt' => 'publish', 'muc' => 'thieu_doi_chieu', 'xep' => 'doc' ) ),
				array( 'Bài có cờ nghi cần xem lại', array( 'muc' => 'co_co' ) ),
			) as [ $ten, $loc ]
		) {
			$so   = DanhSachTacPham::dem_loc( $loc );
			$ds   = DanhSachTacPham::url_loc( $loc );
			$dau  = $so ? (int) ( DanhSachTacPham::ids_loc( $loc, 1 )[0] ?? 0 ) : 0;
			$ra[] = array( $ten, $so, $ds, $dau ? SuaTacPham::url( $dau, $ds ) : $ds );
		}
		$goi_y = Viec::dem( Viec::CHO, 'goi_y' );
		$ra[]  = array( 'Gợi ý chủ đề, vùng của máy chờ duyệt', $goi_y, self::url_goi_y(), self::url_goi_y() );
		return $ra;
	}

	/** Ý nghĩa dài hơn số chữ (tiếng) này thì xếp vào danh sách "nên rút gọn" (D-114; docs/27: Ý nghĩa 2–3 câu ngắn). */
	public const Y_NGHIA_DAI = 80;

	/** Số chữ (tiếng) của một đoạn: đếm theo khoảng trắng sau khi bỏ thẻ định dạng. */
	public static function so_chu( string $chu ): int {
		return count( preg_split( '/\s+/u', trim( wp_strip_all_tags( $chu ) ), -1, PREG_SPLIT_NO_EMPTY ) ?: array() );
	}

	/**
	 * Bài đã đăng có Ý nghĩa dài hơn Y_NGHIA_DAI chữ, dài nhất trước (D-114). Câu đố không xét: phần đó là "Giải thích đáp
	 * án", được phép dài hơn. Một truy vấn (lọc thô theo độ dài giá trị đã lưu) rồi đếm chữ bằng PHP; máy chỉ đếm, không sửa.
	 *
	 * @return array<int,int> ID bài => số chữ
	 */
	public static function y_nghia_dai(): array {
		global $wpdb;
		$hang = $wpdb->get_results( $wpdb->prepare( "SELECT m.post_id, m.meta_value FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE m.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish' AND LENGTH(m.meta_value) > %d", \Phongdao\MienDuLieu\Phan::khoa( 'y_nghia' ), \Phongdao\HaTang\KhaiBao::TAC_PHAM, 2 * self::Y_NGHIA_DAI ) ) ?: array();
		$cd   = get_term_by( 'slug', 'cau-do', \Phongdao\HaTang\KhaiBao::THE_LOAI );
		$bo   = $cd ? array_flip( array_map( 'intval', (array) get_objects_in_term( $cd->term_id, \Phongdao\HaTang\KhaiBao::THE_LOAI ) ) ) : array();
		$ra   = array();
		foreach ( $hang as $h ) {
			$v  = maybe_unserialize( $h->meta_value );
			$so = is_array( $v ) ? self::so_chu( (string) ( $v['chu'] ?? '' ) ) : 0;
			if ( $so > self::Y_NGHIA_DAI && ! isset( $bo[ (int) $h->post_id ] ) ) {
				$ra[ (int) $h->post_id ] = $so;
			}
		}
		arsort( $ra );
		return $ra;
	}

	/** Mở phần Ý nghĩa của bài ở trang Sửa tác phẩm, lối về là danh sách "Ý nghĩa dài" của Bổ sung. */
	public static function url_sua_y_nghia( int $id ): string {
		return add_query_arg( 'phan', 'y_nghia', SuaTacPham::url( $id, self::url_tab( 'can_sua' ) . '#y-nghia-dai' ) ) . '#phan-y_nghia';
	}

	/** Khối "Ý nghĩa dài" của màn Bổ sung: tối đa 50 bài, dài nhất trước. */
	private static function khoi_y_nghia_dai( array $dai ): string {
		$o = '';
		foreach ( array_slice( $dai, 0, 50, true ) as $id => $so ) {
			$o .= '<li><a href="' . esc_url( self::url_sua_y_nghia( $id ) ) . '">' . esc_html( get_the_title( $id ) ?: 'Bài #' . $id ) . '</a> · ' . esc_html( number_format_i18n( $so ) ) . ' chữ</li>';
		}
		return '<h2 id="y-nghia-dai">Ý nghĩa dài, nên rút còn 2–3 câu</h2>'
			. '<p class="description">Ý nghĩa trên trang đọc nên ngắn (2–3 câu) để người đọc hiểu ngay. Phần kể chuyện, giải thích dài có thể chuyển sang Từ khó hoặc Bối cảnh. Máy chỉ đếm chữ (hơn ' . self::Y_NGHIA_DAI . ' chữ), không sửa nội dung. Câu đố không xét.</p>'
			. ( $o ? '<ol class="phongdao-nen-lam">' . $o . '</ol>' . ( count( $dai ) > 50 ? '<p class="description">Còn ' . esc_html( number_format_i18n( count( $dai ) - 50 ) ) . ' bài nữa; rút gọn các bài trên rồi danh sách tự cập nhật.</p>' : '' ) : '<p>Không có Ý nghĩa nào dài hơn ' . self::Y_NGHIA_DAI . ' chữ.</p>' );
	}

	public const HANH_DONG = 'phongdao_bo_sung_goi_y';

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan_goi_y' ) );
		add_action( 'admin_post_' . self::BO_QUA, array( self::class, 'nhan_bo_qua' ) );
		add_action( 'admin_post_' . self::NOI_LAI, array( self::class, 'nhan_noi_lai' ) );
		add_action( 'admin_post_' . self::TU_DIEN, array( self::class, 'nhan_tu_dien' ) );
		add_action( 'admin_post_' . self::TU_DIEN_TAT, array( self::class, 'nhan_tu_dien_tat' ) );
	}

	public const TU_DIEN     = 'phongdao_bo_sung_tu_dien';
	public const TU_DIEN_TAT = 'phongdao_bo_sung_tu_dien_tat';

	/** Số chữ hiện ở bảng tổng quan (chữ có nhiều câu chưa nối trước); tìm một chữ cụ thể bằng ô tìm. */
	private const TU_DIEN_HIEN = 100;

	public static function url_tu_dien( string $khoa = '' ): string {
		return add_query_arg( array_filter( array( 'page' => self::TRANG, 'xem' => 'tu_dien', 'chu' => $khoa ) ), admin_url( 'admin.php' ) );
	}

	/** Nghĩa một mục từ, cắt ngắn cho một dòng. */
	private static function nghia_ngan( \WP_Term $t, int $toi_da = 90 ): string {
		$g = trim( wp_strip_all_tags( (string) $t->description ) );
		return mb_strlen( $g ) > $toi_da ? rtrim( mb_substr( $g, 0, $toi_da - 1 ) ) . '…' : $g;
	}

	/** Nút tắt / bật lại gợi ý một chữ. */
	private static function nut_tat( string $khoa, bool $tat, string $ve ): string {
		return '<form method="post" class="phongdao-td-tat" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::TU_DIEN_TAT . '"><input type="hidden" name="chu" value="' . esc_attr( $khoa ) . '"><input type="hidden" name="tat" value="' . ( $tat ? '1' : '0' ) . '"><input type="hidden" name="ve" value="' . esc_attr( $ve ) . '">' . wp_nonce_field( self::TU_DIEN_TAT, '_wpnonce', true, false )
			. '<button type="submit" class="button">' . ( $tat ? 'Tắt gợi ý chữ này' : 'Bật lại gợi ý' ) . '</button></form>';
	}

	/**
	 * Tab "Gợi ý Từ điển" (D-126): mỗi chữ trong Từ điển một dòng — số nghĩa, số bài đã nối, số câu có trọn chữ mà chưa nối.
	 * Chữ có nhiều câu nhất trước; chữ rất thông dụng và chữ đã tắt gợi ý xếp riêng ở cuối.
	 */
	private static function khoi_tu_dien(): string {
		// phpcs:ignore WordPress.Security.NonceVerification -- chỉ lọc danh sách
		$q    = \Phongdao\DichVu\TuDien\GoiYNoi::khoa( sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ) );
		$tq   = \Phongdao\DichVu\TuDien\GoiYNoi::tong_quan();
		$tat  = array_flip( \Phongdao\DichVu\TuDien\GoiYNoi::da_tat() );
		$ds   = array_filter( $tq, static fn( array $d, string $k ): bool => $d['goi_y'] > 0 && ( '' === $q || str_contains( $k, $q ) ), ARRAY_FILTER_USE_BOTH );
		uasort( $ds, static fn( array $a, array $b ): int => $b['goi_y'] <=> $a['goi_y'] ?: strcmp( $a['ten'], $b['ten'] ) );
		$chinh = array();
		$nhieu = array();
		$da_tat = array();
		foreach ( $ds as $k => $d ) {
			if ( isset( $tat[ $k ] ) ) {
				$da_tat[ $k ] = $d;
			} elseif ( $d['goi_y'] > \Phongdao\DichVu\TuDien\GoiYNoi::THONG_DUNG ) {
				$nhieu[ $k ] = $d;
			} else {
				$chinh[ $k ] = $d;
			}
		}
		$bang = static function ( array $ds, string $chu_thich ): string {
			$hang = '';
			foreach ( $ds as $k => $d ) {
				$hang .= '<tr><th scope="row"><a href="' . esc_url( self::url_tu_dien( (string) $k ) ) . '">' . esc_html( $d['ten'] ) . '</a></th><td>' . esc_html( number_format_i18n( $d['nghia'] ) ) . '</td><td>' . esc_html( number_format_i18n( $d['noi'] ) ) . '</td><td><a class="button" href="' . esc_url( self::url_tu_dien( (string) $k ) ) . '" aria-label="' . esc_attr( 'Duyệt ' . $d['goi_y'] . ' câu có chữ ' . $d['ten'] ) . '">' . esc_html( number_format_i18n( $d['goi_y'] ) ) . ' câu</a></td></tr>';
			}
			return '<table class="widefat striped phongdao-td-bang"><caption class="screen-reader-text">' . esc_html( $chu_thich ) . '</caption><thead><tr><th scope="col">Chữ</th><th scope="col">Nghĩa trong Từ điển</th><th scope="col">Bài đã nối</th><th scope="col">Câu chưa nối</th></tr></thead><tbody>' . $hang . '</tbody></table>';
		};
		$tim = '<form method="get" class="phongdao-td-tim" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '"><input type="hidden" name="xem" value="tu_dien"><label for="phongdao-td-q">Tìm chữ</label> <input type="search" id="phongdao-td-q" name="q" value="' . esc_attr( $q ) . '"> <button type="submit" class="button">Tìm</button>' . ( '' === $q ? '' : ' <a href="' . esc_url( self::url_tu_dien() ) . '">Bỏ tìm</a>' ) . '</form>';
		$o   = '<h2 id="tu-dien">Gợi ý nối Từ điển</h2><p class="description">Máy tìm câu có trọn chữ, đúng dấu của một mục từ mà chưa nối với nghĩa nào của chữ đó. Bạn chọn nghĩa cho từng câu, hoặc "Không nối". Máy không tự gắn. Bài đã nối mà chưa có từ khó riêng sẽ hiện nghĩa trong Từ điển, có nhãn "Theo Từ điển".</p>' . $tim;
		if ( ! $chinh && ! $nhieu && ! $da_tat ) {
			return $o . '<p>' . ( '' === $q ? 'Không còn câu nào chờ nối.' : 'Không có chữ nào khớp.' ) . '</p>';
		}
		$o .= $chinh ? $bang( array_slice( $chinh, 0, self::TU_DIEN_HIEN, true ), 'Chữ có câu chưa nối' ) . ( count( $chinh ) > self::TU_DIEN_HIEN ? '<p class="description">Còn ' . esc_html( number_format_i18n( count( $chinh ) - self::TU_DIEN_HIEN ) ) . ' chữ ít câu hơn; dùng ô Tìm chữ để mở.</p>' : '' ) : '';
		$o .= $nhieu ? '<details class="phongdao-td-phu"><summary>Chữ rất thông dụng (' . esc_html( number_format_i18n( count( $nhieu ) ) ) . ')</summary><p class="description">Mỗi chữ có hơn ' . esc_html( number_format_i18n( \Phongdao\DichVu\TuDien\GoiYNoi::THONG_DUNG ) ) . ' câu; phần lớn không mang nghĩa trong Từ điển.</p>' . $bang( $nhieu, 'Chữ rất thông dụng' ) . '</details>' : '';
		$o .= $da_tat ? '<details class="phongdao-td-phu"><summary>Đã tắt gợi ý (' . esc_html( number_format_i18n( count( $da_tat ) ) ) . ')</summary>' . $bang( $da_tat, 'Chữ đã tắt gợi ý' ) . '</details>' : '';
		return $o;
	}

	/**
	 * Duyệt một chữ: các nghĩa trong Từ điển, rồi mỗi câu có chữ một hàng chọn (một nghĩa · Không nối · Chưa chọn). Chưa chọn
	 * thì không lưu gì. "Chọn cả nhóm" đặt mọi câu đang Chưa chọn vào một nghĩa (cần JavaScript); sửa câu ngoại lệ rồi Lưu.
	 */
	private static function hien_tu_dien( string $khoa ): string {
		$cac = \Phongdao\DichVu\TuDien\GoiYNoi::theo_chu()[ $khoa ] ?? array();
		$ve  = self::url_tu_dien( $khoa );
		$lui = '<p><a href="' . esc_url( self::url_tu_dien() ) . '">← Bổ sung: gợi ý Từ điển</a></p>';
		if ( ! $cac ) {
			return '<div class="wrap phongdao-ban phongdao-bo-sung">' . $lui . '<h1>Không có chữ này trong Từ điển</h1></div>';
		}
		$ten  = (string) $cac[0]->name;
		$tat  = in_array( $khoa, \Phongdao\DichVu\TuDien\GoiYNoi::da_tat(), true );
		$cau  = \Phongdao\DichVu\TuDien\GoiYNoi::cau( $khoa );
		$them = admin_url( 'edit-tags.php?taxonomy=muc_tu&post_type=' . \Phongdao\HaTang\KhaiBao::TAC_PHAM );
		$nghia = '';
		foreach ( $cac as $t ) {
			$sua    = get_edit_term_link( $t, 'muc_tu' );
			$nghia .= '<li><strong>' . esc_html( $t->name ) . '</strong> ' . esc_html( self::nghia_ngan( $t, 160 ) ?: '(chưa có giải thích)' ) . ' <span class="description">· ' . esc_html( number_format_i18n( (int) $t->count ) ) . ' bài đã nối</span>' . ( $sua ? ' · <a href="' . esc_url( $sua ) . '">Sửa nghĩa</a>' : '' ) . '</li>';
		}
		$dau = '<h1>Chữ “' . esc_html( $ten ) . '”</h1><p>' . esc_html( number_format_i18n( $cau['tong'] ) ) . ' câu có trọn chữ này chưa nối' . ( $cau['tong'] > count( $cau['ds'] ) ? '; hiện ' . number_format_i18n( count( $cau['ds'] ) ) . ' câu đọc nhiều trước' : '' ) . '.</p>'
			. '<h2>Nghĩa trong Từ điển</h2><ol class="phongdao-td-nghia">' . $nghia . '</ol>'
			. '<p class="phongdao-td-hang"><a href="' . esc_url( $them ) . '">＋ Thêm nghĩa mới</a> <span class="description">(câu mang nghĩa chưa có: bạn viết nghĩa ở Từ điển rồi quay lại đây)</span></p>'
			. self::nut_tat( $khoa, ! $tat, $ve );
		if ( ! $cau['ds'] ) {
			return '<div class="wrap phongdao-ban phongdao-bo-sung">' . $lui . $dau . TrangLo::lay_bao() . '<p role="status">Không còn câu nào chờ nối cho chữ này.</p></div>';
		}
		$hang = '';
		foreach ( $cau['ds'] as $c ) {
			$b   = (int) $c['bai'];
			$ten_o = 'chon[' . $b . ']';
			$chon = '<label><input type="radio" name="' . esc_attr( $ten_o ) . '" value="" checked> Chưa chọn</label>';
			foreach ( $cac as $t ) {
				$chon .= '<label><input type="radio" name="' . esc_attr( $ten_o ) . '" value="' . (int) $t->term_id . '" data-nghia="' . (int) $t->term_id . '"> ' . esc_html( self::nghia_ngan( $t, 40 ) ?: $t->name ) . '</label>';
			}
			$chon .= '<label><input type="radio" name="' . esc_attr( $ten_o ) . '" value="0"> Không nối</label>';
			$hang .= '<li><div class="phongdao-td-cau"><p>' . implode( '<br>', array_slice( $c['dong'], 0, 3 ) ) . '</p><p class="description"><a href="' . esc_url( SuaTacPham::url( $b, $ve ) ) . '">' . esc_html( $c['tieu_de'] ) . '</a>' . ( '' === $c['the_loai'] ? '' : ' · ' . esc_html( $c['the_loai'] ) ) . '</p></div>'
				. '<fieldset class="phongdao-td-chon"><legend class="screen-reader-text">' . esc_html( 'Nghĩa của “' . $ten . '” trong ' . $c['tieu_de'] ) . '</legend>' . $chon . '</fieldset></li>';
		}
		$ca_nhom = '';
		foreach ( $cac as $t ) {
			$ca_nhom .= '<button type="button" class="button" data-ca-nhom="' . (int) $t->term_id . '" data-chi-js hidden>' . esc_html( 'Chọn cả nhóm: ' . ( self::nghia_ngan( $t, 30 ) ?: $t->name ) ) . '</button> ';
		}
		$form = '<form method="post" class="phongdao-td-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::TU_DIEN . '"><input type="hidden" name="chu" value="' . esc_attr( $khoa ) . '">' . wp_nonce_field( self::TU_DIEN, '_wpnonce', true, false )
			. '<ul class="phongdao-bs-ds phongdao-td-ds">' . $hang . '</ul>'
			. '<p class="phongdao-bs-nut"><button type="submit" class="button button-primary">Lưu các lựa chọn</button> ' . $ca_nhom . '<span class="description" data-td-dem aria-live="polite"></span></p>'
			. '<p class="description">Chỉ câu đã chọn được lưu; Chưa chọn thì giữ nguyên. Nối một nghĩa ghi vào Lịch sử của bài (khôi phục được); "Không nối" làm câu đó không hiện lại ở đây.</p></form>';
		return '<div class="wrap phongdao-ban phongdao-bo-sung">' . $lui . $dau . TrangLo::lay_bao() . $form . '</div>';
	}

	/** Lưu lựa chọn nối Từ điển: kiểm quyền, nonce; mỗi câu được kiểm lại lúc ghi. */
	public static function nhan_tu_dien(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền nối Từ điển.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::TU_DIEN );
		$khoa = \Phongdao\DichVu\TuDien\GoiYNoi::khoa( sanitize_text_field( wp_unslash( (string) ( $_POST['chu'] ?? '' ) ) ) );
		$chon = array();
		foreach ( (array) wp_unslash( $_POST['chon'] ?? array() ) as $bai => $muc ) {
			if ( is_scalar( $muc ) && '' !== (string) $muc && absint( $bai ) ) {
				$chon[ absint( $bai ) ] = absint( $muc );
			}
		}
		$kq  = \Phongdao\DichVu\TuDien\GoiYNoi::luu( $khoa, $chon, get_current_user_id() );
		$chu = $chon
			? 'Đã nối ' . $kq['noi'] . ' câu' . ( $kq['khong'] ? ', ' . $kq['khong'] . ' câu không nối' : '' ) . '.' . ( $kq['loi'] ? ' ' . $kq['loi'] . ' câu chưa lưu được (bài vừa đổi hoặc đã nối ở nơi khác).' : '' )
			: 'Chưa chọn câu nào.';
		set_transient( 'phongdao_lo_bao_' . get_current_user_id(), array( 'ok' => ! $kq['loi'], 'chu' => $chu ), 300 );
		wp_safe_redirect( self::url_tu_dien( $khoa ) );
		exit;
	}

	/** Tắt / bật lại gợi ý một chữ. */
	public static function nhan_tu_dien_tat(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền đổi gợi ý.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::TU_DIEN_TAT );
		$khoa = \Phongdao\DichVu\TuDien\GoiYNoi::khoa( sanitize_text_field( wp_unslash( (string) ( $_POST['chu'] ?? '' ) ) ) );
		$tat  = '1' === ( $_POST['tat'] ?? '' );
		if ( '' !== $khoa ) {
			\Phongdao\DichVu\TuDien\GoiYNoi::tat( $khoa, $tat );
		}
		set_transient( 'phongdao_lo_bao_' . get_current_user_id(), array( 'ok' => true, 'chu' => $tat ? 'Đã tắt gợi ý chữ này; xem lại ở cuối tab Gợi ý Từ điển.' : 'Đã bật lại gợi ý chữ này.' ), 300 );
		wp_safe_redirect( self::url_tu_dien( $khoa ) );
		exit;
	}

	public const NOI_LAI = 'phongdao_bo_sung_noi_lai';

	/** Số từ khó mất nối hiện tối đa trên một lần mở (bấm Nối lại xong, danh sách tự ngắn lại). */
	private const NOI_LAI_HIEN = 200;

	/**
	 * Khối "Từ khó mất nối Từ điển" (D-125) ở tab Cần sửa: từ khó từng nối mục từ nhưng mất số nối khi sửa lời giải (lỗi
	 * trước bản 1.0.88). Mỗi dòng đánh dấu sẵn; người bấm "Nối lại" mới ghi, qua Lịch sử. Không có dòng thì không hiện.
	 *
	 * @param list<array{bai:int,i:int,tu:string,muc:int}> $ds
	 */
	private static function khoi_noi_lai( array $ds ): string {
		if ( ! $ds ) {
			return '';
		}
		$li = '';
		foreach ( array_slice( $ds, 0, self::NOI_LAI_HIEN ) as $d ) {
			$t    = get_term( $d['muc'], 'muc_tu' );
			$co   = $t instanceof \WP_Term;
			$giai = $co ? trim( wp_strip_all_tags( (string) $t->description ) ) : '';
			$giai = mb_strlen( $giai ) > 90 ? rtrim( mb_substr( $giai, 0, 89 ) ) . '…' : $giai;
			$ma   = $d['bai'] . ':' . $d['i'];
			$o    = 'noi-lai-' . $d['bai'] . '-' . $d['i'];
			$li  .= '<li><input type="checkbox" name="chon[]" value="' . esc_attr( $ma ) . '" id="' . esc_attr( $o ) . '" checked> <label for="' . esc_attr( $o ) . '"><strong>«' . esc_html( $d['tu'] ) . '»</strong> trong <a href="' . esc_url( SuaTacPham::url( $d['bai'], self::url_tab( 'can_sua' ) . '#noi-lai' ) ) . '">' . esc_html( get_the_title( $d['bai'] ) ?: 'Bài #' . $d['bai'] ) . '</a>'
				. ' → mục từ ' . ( $co ? '<a href="' . esc_url( (string) get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>' : '#' . (int) $d['muc'] ) . ( '' === $giai ? '' : ' <span class="description">' . esc_html( $giai ) . '</span>' ) . '</label></li>';
		}
		return '<h2 id="noi-lai">Từ khó mất nối Từ điển</h2>'
			. '<p class="description">Các từ khó này từng nối với mục từ trong Từ điển (bài vẫn gắn mục từ cùng chữ), nhưng mất nối khi lời giải được sửa trước bản sửa lỗi. Bấm "Nối lại" để nối đúng mục từ đó; lời giải và nguồn giữ nguyên, Lịch sử ghi lại. Bỏ dấu dòng nào chưa chắc.</p>'
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="phongdao-noi-lai"><input type="hidden" name="action" value="' . self::NOI_LAI . '">' . wp_nonce_field( self::NOI_LAI, '_wpnonce', true, false )
			. '<ul class="phongdao-bs-ds">' . $li . '</ul>'
			. ( count( $ds ) > self::NOI_LAI_HIEN ? '<p class="description">Còn ' . esc_html( number_format_i18n( count( $ds ) - self::NOI_LAI_HIEN ) ) . ' từ nữa; nối các dòng trên rồi danh sách tự cập nhật.</p>' : '' )
			. '<p class="phongdao-bs-nut"><button type="submit" class="button button-primary">Nối lại các từ đã chọn</button></p></form>';
	}

	/** "Nối lại": kiểm quyền, nonce; ghi số mục từ cho đúng các từ đã chọn (điều kiện kiểm lại lúc ghi). */
	public static function nhan_noi_lai(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền sửa từ khó.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::NOI_LAI );
		$chon = array_values( array_filter( array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['chon'] ?? array() ) ), static fn( string $c ): bool => current_user_can( 'edit_post', (int) strtok( $c, ':' ) ) ) );
		$kq   = \Phongdao\DichVu\TuDien\NoiLai::noi( $chon, get_current_user_id() );
		$chu  = $chon ? 'Đã nối lại ' . $kq['muc'] . ' từ khó ở ' . $kq['bai'] . ' bài.' . ( $kq['loi'] ? ' ' . $kq['loi'] . ' bài chưa lưu được, thử lại.' : '' ) : 'Chưa chọn từ nào.';
		set_transient( 'phongdao_lo_bao_' . get_current_user_id(), array( 'ok' => ! $kq['loi'], 'chu' => $chu ), 300 );
		wp_safe_redirect( self::url_tab( 'can_sua' ) . '#noi-lai' );
		exit;
	}

	public const BO_QUA = 'phongdao_bo_sung_bo_qua';

	/** Số bài hiện ở mỗi nhóm "Cần sửa". */
	private const CAN_SUA_HIEN = 20;

	/**
	 * Khối "Cần sửa" (D-119): máy rà nội dung đã đăng theo quy tắc viết và xếp theo loại lỗi. Mỗi bài: tên dẫn tới đúng phần ở
	 * màn Sửa tác phẩm, một đoạn trích, nút "Bỏ qua". Máy chỉ đánh dấu, không sửa chữ.
	 */
	private static function khoi_can_sua( array $ds ): string {
		$o = '';
		foreach ( \Phongdao\MienDuLieu\KiemTrinhBay::NHOM as $ma => [ $ten, $phan ] ) {
			$ids = $ds[ $ma ] ?? array();
			if ( ! $ids ) {
				continue;
			}
			$li = '';
			foreach ( array_slice( $ids, 0, self::CAN_SUA_HIEN ) as $id ) {
				$den = 'vung' === $phan ? add_query_arg( 'phan_loai', 'vung', SuaTacPham::url( $id, self::url_tab( 'can_sua' ) . '#can-sua' ) ) . '#phan-loai-vung' : add_query_arg( 'phan', $phan, SuaTacPham::url( $id, self::url_tab( 'can_sua' ) . '#can-sua' ) ) . '#' . ( 'loi' === $phan ? 'loi-doi-chieu' : 'phan-' . $phan );
				$li .= '<li><a href="' . esc_url( $den ) . '">' . esc_html( get_the_title( $id ) ?: 'Bài #' . $id ) . '</a> <span class="description">' . esc_html( \Phongdao\DichVu\CanSua\CanSua::trich( $id, $ma ) ) . '</span>'
					. ' <form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="phongdao-bo-qua"><input type="hidden" name="action" value="' . self::BO_QUA . '"><input type="hidden" name="id" value="' . (int) $id . '"><input type="hidden" name="ma" value="' . esc_attr( $ma ) . '">'
					. wp_nonce_field( self::BO_QUA . '_' . $id . '_' . $ma, '_wpnonce', true, false ) . '<button type="submit" class="button button-small">Bỏ qua</button></form></li>';
			}
			$o .= '<details class="phongdao-can-sua-nhom" id="can-sua-' . esc_attr( $ma ) . '"><summary>' . esc_html( $ten . ' · ' . number_format_i18n( count( $ids ) ) . ' bài' ) . '</summary><ol class="phongdao-nen-lam">' . $li . '</ol>'
				. ( count( $ids ) > self::CAN_SUA_HIEN ? '<p class="description">' . esc_html( 'Còn ' . number_format_i18n( count( $ids ) - self::CAN_SUA_HIEN ) . ' bài nữa; sửa hoặc bỏ qua các bài trên rồi danh sách tự cập nhật.' ) . '</p>' : '' ) . '</details>';
		}
		return '<h2 id="can-sua">Cần sửa</h2>'
			. '<p class="description">Máy rà nội dung đã đăng theo quy tắc viết (Ý nghĩa 2–3 câu, khoảng 30–60 chữ, bắt đầu bằng nghĩa, có dấu câu cuối, không nêu tác giả; từ khó gọn; lời không lỗi gõ) và xếp theo loại lỗi. Máy chỉ đánh dấu: bạn sửa ở màn Sửa tác phẩm, hoặc bấm "Bỏ qua" nếu chỗ đó đúng như ý. Lời chỉ sửa theo nguồn.</p>'
			. ( '' === $o ? '<p>Chưa thấy lỗi trình bày nào.</p>' : $o );
	}

	/** "Bỏ qua" một lỗi trình bày của một bài: kiểm quyền, nonce; không đổi nội dung bài. */
	public static function nhan_bo_qua(): void {
		$id = absint( $_POST['id'] ?? 0 );
		$ma = sanitize_key( (string) ( $_POST['ma'] ?? '' ) );
		if ( ! current_user_can( 'cadao_soan' ) || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Bạn không có quyền sửa bài này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::BO_QUA . '_' . $id . '_' . $ma );
		\Phongdao\DichVu\CanSua\CanSua::bo_qua( $id, $ma );
		wp_safe_redirect( self::url_tab( 'can_sua' ) . '#can-sua-' . $ma );
		exit;
	}

	public static function url_goi_y( array $them = array() ): string {
		return add_query_arg( array( 'page' => self::TRANG, 'xem' => 'goi_y' ) + $them, admin_url( 'admin.php' ) ) . ( $them ? '' : '#goi-y' );
	}

	/**
	 * Gợi ý chủ đề, vùng của máy đang chờ (Chờ, Để sau), gom theo nhóm (một chủ đề / một vùng). Máy không tự gắn: mỗi gợi ý
	 * là một đề xuất trên bài, người đồng ý hay bỏ.
	 *
	 * @return array<string,array{ten:string,ids:int[],bai:int[]}> khoá "loai:ma"
	 */
	public static function nhom_goi_y(): array {
		global $wpdb;
		$ra = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Viec::bang() . " WHERE loai = %s AND trang_thai IN ('cho','de_sau') ORDER BY id", \Phongdao\DichVu\GoiY\GoiY::LOAI ), ARRAY_A ) ?: array() as $h ) {
			$v = Viec::doc( $h );
			$k = (string) ( $v['noi_dung']['loai'] ?? '' ) . ':' . (string) ( $v['noi_dung']['ma'] ?? '' );
			$ra[ $k ] ??= array( 'ten' => \Phongdao\DichVu\GoiY\GoiY::ten( (array) $v['noi_dung'] ), 'ids' => array(), 'bai' => array() );
			$ra[ $k ]['ids'][] = (int) $v['id'];
			$ra[ $k ]['bai'][] = (int) $v['doi_tuong_id'];
		}
		uasort( $ra, static fn( array $a, array $b ): int => count( $b['ids'] ) <=> count( $a['ids'] ) ?: strcmp( $a['ten'], $b['ten'] ) );
		return $ra;
	}

	/** Thẻ gợi ý đang chờ theo mã (đọc theo lô). @return array<int,array> */
	private static function the_goi_y( array $ids ): array {
		$ra = array();
		foreach ( $ids as $id ) {
			$v = Viec::lay( (int) $id );
			if ( $v && \Phongdao\DichVu\GoiY\GoiY::LOAI === $v['loai'] && in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ) {
				$ra[ (int) $id ] = $v;
			}
		}
		return $ra;
	}

	/**
	 * Form duyệt gợi ý: mỗi dòng một gợi ý (đánh dấu sẵn; bỏ dấu ngoại lệ), hai nút "Đồng ý các mục đã chọn" (người có
	 * quyền duyệt) và "Bỏ các mục đã chọn". Mỗi lần bấm ghi thành một lô: xem lại kết quả từng bài.
	 *
	 * @param array<int,array> $the thẻ gợi ý
	 * @param bool $hien_bai mỗi dòng hiện tên bài và hai dòng lời (duyệt theo nhóm); false = hiện tên gợi ý (theo bài)
	 */
	private static function form_goi_y( array $the, bool $hien_bai, string $ve ): string {
		$dong = '';
		foreach ( $the as $id => $v ) {
			$bai  = (int) $v['doi_tuong_id'];
			$loi  = array_slice( (array) ( \Phongdao\KhoLuu\PhanBai::lay( $bai, 'loi' )['dong'] ?? array() ), 0, 2 );
			$chu  = $hien_bai
				? '<strong><a href="' . esc_url( SuaTacPham::url( $bai ) ) . '">' . esc_html( (string) get_post_field( 'post_title', $bai ) ) . '</a></strong><span class="phongdao-bs-loi">' . esc_html( implode( ' / ', $loi ) ) . '</span>'
				: '<strong>' . esc_html( \Phongdao\DichVu\GoiY\GoiY::ten( (array) $v['noi_dung'] ) ) . '</strong>';
			$ly   = (string) ( $v['noi_dung']['ly_do'] ?? '' );
			$dong .= '<li><input type="checkbox" name="ids[]" value="' . (int) $id . '" id="bs-gy-' . (int) $id . '" checked><label for="bs-gy-' . (int) $id . '">' . $chu . ( '' === $ly ? '' : '<span class="description">Máy dựa vào: ' . esc_html( $ly ) . '</span>' ) . '</label></li>';
		}
		$duyet = current_user_can( 'cadao_duyet' );
		return '<form method="post" class="phongdao-bs-goi-y" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::HANH_DONG . '"><input type="hidden" name="ve" value="' . esc_attr( $ve ) . '">' . wp_nonce_field( self::HANH_DONG, '_wpnonce', true, false )
			. '<ul class="phongdao-bs-ds">' . $dong . '</ul>'
			. '<p class="phongdao-bs-nut">' . ( $duyet ? '<button type="submit" name="lam" value="dong_y" class="button button-primary">Đồng ý các mục đã chọn</button> ' : '' )
			. '<button type="submit" name="lam" value="bo" class="button">Bỏ các mục đã chọn</button></p>'
			. '<p class="description">Đánh dấu sẵn mọi mục; bỏ dấu mục không đúng rồi bấm. Máy không tự gắn. Kết quả ghi thành một lô (xem lại từng bài ở Lịch sử thao tác hàng loạt); gắn nhầm thì bỏ ở phần Phân loại của trang sửa bài.</p></form>';
	}

	/**
	 * Đồng ý / bỏ các gợi ý đã chọn: một lô (Đồng ý gợi ý cần quyền duyệt; Bỏ đề xuất), áp dụng ngay; còn lượt thì sang màn
	 * lô tự chạy tiếp. Về lại đúng màn Bổ sung kèm lời báo và lối xem kết quả từng bài.
	 */
	public static function nhan_goi_y(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền duyệt gợi ý.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::HANH_DONG );
		$ids      = array_map( 'absint', (array) wp_unslash( $_POST['ids'] ?? array() ) );
		$thao_tac = 'dong_y' === ( $_POST['lam'] ?? '' ) ? 'goi_y_dong_y' : 'viec_bo';
		$ve       = esc_url_raw( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		$ve       = str_starts_with( $ve, admin_url( 'admin.php?page=' . self::TRANG ) ) ? $ve : self::url_goi_y();
		$nguoi    = get_current_user_id();
		$kq       = \Phongdao\DichVu\XuLyLo\Lo::tao( $thao_tac, $ids, 'chon', array(), $nguoi );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_lo_bao_' . $nguoi, array( 'ok' => false, 'chu' => $kq['loi'], 'lo' => 0 ), MINUTE_IN_SECONDS );
			wp_safe_redirect( $ve );
			exit;
		}
		$ad = \Phongdao\DichVu\XuLyLo\Lo::ap_dung( $kq['lo'], $nguoi, current_user_can( 'cadao_duyet' ) );
		if ( $ad['ok'] && $ad['con'] > 0 ) {
			wp_safe_redirect( TrangLo::url( (int) $kq['lo'], array( 'tu_chay' => '1' ) ) );
			exit;
		}
		$dem = $ad['dem'] ?? array();
		set_transient( 'phongdao_lo_bao_' . $nguoi, array( 'ok' => (bool) $ad['ok'], 'chu' => $ad['ok'] ? ( 'goi_y_dong_y' === $thao_tac ? 'Đã đồng ý ' : 'Đã bỏ ' ) . (int) ( $dem['thanh_cong'] ?? 0 ) . ' gợi ý' . ( ! empty( $dem['bo_qua'] ) || ! empty( $dem['xung_dot'] ) ? '; ' . ( (int) ( $dem['bo_qua'] ?? 0 ) + (int) ( $dem['xung_dot'] ?? 0 ) ) . ' mục bỏ qua' : '' ) . '.' : $ad['loi'], 'lo' => (int) $kq['lo'] ), MINUTE_IN_SECONDS );
		wp_safe_redirect( $ve );
		exit;
	}

	/** Khối "Gợi ý chủ đề, vùng của máy": nhóm (một chủ đề / vùng) kèm số, bài có nhiều gợi ý nhất. */
	private static function khoi_goi_y(): string {
		$nhom = self::nhom_goi_y();
		if ( ! $nhom ) {
			return '<h2 id="goi-y">Gợi ý chủ đề, vùng của máy</h2><p>Không có gợi ý nào đang chờ.</p>';
		}
		$o   = '';
		$bai = array();
		foreach ( $nhom as $k => $n ) {
			$o .= '<li><a class="phongdao-chip" href="' . esc_url( self::url_goi_y( array( 'nhom' => $k ) ) ) . '">' . esc_html( $n['ten'] ) . ' <span class="phongdao-chip-so">' . esc_html( number_format_i18n( count( $n['ids'] ) ) ) . '</span></a></li>';
			foreach ( $n['bai'] as $b ) {
				$bai[ $b ] = ( $bai[ $b ] ?? 0 ) + 1;
			}
		}
		arsort( $bai );
		$ds_bai = '';
		foreach ( array_slice( $bai, 0, 15, true ) as $b => $so ) {
			$ds_bai .= '<li><a href="' . esc_url( self::url_goi_y( array( 'bai' => $b ) ) ) . '">' . esc_html( (string) get_post_field( 'post_title', $b ) ) . '</a> · ' . esc_html( number_format_i18n( $so ) ) . ' gợi ý</li>';
		}
		return '<h2 id="goi-y">Gợi ý chủ đề, vùng của máy</h2>'
			. '<p class="description">Duyệt theo nhóm: chọn một chủ đề hay vùng, lướt danh sách bài, bỏ dấu ngoại lệ, bấm Đồng ý. Hoặc duyệt theo bài: mọi gợi ý của một bài trên một trang.</p>'
			. '<h3>Theo nhóm</h3><ul class="phongdao-bs-nhom">' . $o . '</ul><h3>Theo bài (nhiều gợi ý nhất)</h3><ul class="phongdao-nen-lam">' . $ds_bai . '</ul>';
	}

	/** Màn duyệt một nhóm (một chủ đề / vùng) hoặc mọi gợi ý của một bài. */
	private static function hien_duyet( string $nhom, int $bai ): string {
		$ve = self::url_goi_y( $nhom ? array( 'nhom' => $nhom ) : array( 'bai' => $bai ) );
		if ( '' !== $nhom ) {
			$n   = self::nhom_goi_y()[ $nhom ] ?? null;
			$ten = $n ? $n['ten'] : 'Nhóm gợi ý';
			$the = $n ? self::the_goi_y( $n['ids'] ) : array();
			// Bài đọc nhiều tuần này lên trước
			$doc = array();
			foreach ( $the as $id => $v ) {
				$doc[ $id ] = (int) ( \Phongdao\DichVu\TomTat\TomTat::lay( (int) $v['doi_tuong_id'] )['doc_7'] ?? 0 );
			}
			uksort( $the, static fn( int $a, int $b ): int => $doc[ $b ] <=> $doc[ $a ] ?: $a <=> $b );
			$dau = '<h1>Gợi ý: ' . esc_html( $ten ) . '</h1><p>' . esc_html( number_format_i18n( count( $the ) ) ) . ' bài máy gợi ý gắn ' . esc_html( mb_strtolower( $ten ) ) . '.</p>';
		} else {
			$the = array();
			foreach ( self::nhom_goi_y() as $n ) {
				foreach ( $n['ids'] as $i => $id ) {
					if ( $bai === $n['bai'][ $i ] ) {
						$the[ $id ] = true;
					}
				}
			}
			$the = self::the_goi_y( array_keys( $the ) );
			$loi = (array) ( \Phongdao\KhoLuu\PhanBai::lay( $bai, 'loi' )['dong'] ?? array() );
			$dau = '<h1>Gợi ý cho: ' . esc_html( (string) get_post_field( 'post_title', $bai ) ) . '</h1><blockquote class="phongdao-bs-loi-bai">' . implode( '<br>', array_map( 'esc_html', array_slice( $loi, 0, 8 ) ) ) . '</blockquote>';
		}
		return '<div class="wrap phongdao-ban phongdao-bo-sung"><p><a href="' . esc_url( self::url_goi_y() ) . '">← Bổ sung: gợi ý chủ đề, vùng</a></p>' . $dau . TrangLo::lay_bao()
			. ( $the ? self::form_goi_y( $the, '' !== $nhom, $ve ) : '<p role="status">Không còn gợi ý nào đang chờ ở đây.</p>' ) . '</div>';
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để chọn màn
		$nhom = sanitize_text_field( wp_unslash( (string) ( $_GET['nhom'] ?? '' ) ) );
		$bai  = absint( $_GET['bai'] ?? 0 );
		// phpcs:enable
		$chu_td = sanitize_text_field( wp_unslash( (string) ( $_GET['chu'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ chọn màn
		if ( '' !== $chu_td ) {
			return self::hien_tu_dien( \Phongdao\DichVu\TuDien\GoiYNoi::khoa( $chu_td ) );
		}
		if ( '' !== $nhom || $bai ) {
			return self::hien_duyet( preg_match( '/^(chu_de|vung):[a-z0-9_-]+$/', $nhom ) ? $nhom : '', $bai );
		}
		// Xem trước một tệp nháp Ý nghĩa vừa nạp (D-117)
		$nap = sanitize_key( wp_unslash( (string) ( $_GET['nap'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc mã phiên xem trước của chính người dùng
		if ( '' !== $nap ) {
			return NapNhap::xem_truoc( $nap );
		}
		$xem  = sanitize_key( wp_unslash( (string) ( $_GET['xem'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ chọn tab
		$xem  = isset( self::TAB[ $xem ] ) ? $xem : 'y_nghia';
		$cs   = \Phongdao\DichVu\CanSua\CanSua::ds();
		$dai  = self::y_nghia_dai();
		$thieu = DanhSachTacPham::dem_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) );
		$noi  = \Phongdao\DichVu\TuDien\NoiLai::ds();
		$dem  = array( 'y_nghia' => $thieu, 'can_sua' => \Phongdao\DichVu\CanSua\CanSua::so_bai( $cs ) + count( $dai ) + count( $noi ), 'goi_y' => Viec::dem( Viec::CHO, 'goi_y' ), 'tu_dien' => null, 'khac' => null, 'nhap' => null );
		$tab  = '';
		foreach ( self::TAB as $ma => $ten ) {
			$tab .= '<a class="phongdao-chip" href="' . esc_url( self::url_tab( $ma ) ) . '"' . ( $ma === $xem ? ' aria-current="page"' : '' ) . '>' . esc_html( $ten ) . ( null === $dem[ $ma ] ? '' : ' <span class="phongdao-chip-so">' . esc_html( number_format_i18n( (int) $dem[ $ma ] ) ) . '</span>' ) . '</a>';
		}
		// D-117: chỉ bài có phần riêng lên Google — tiến độ một dòng
		$lap  = count( \Phongdao\GiaoTiep\Seo\Module::ds_lap() );
		$tong = (int) wp_count_posts( \Phongdao\HaTang\KhaiBao::TAC_PHAM )->publish;
		$tien = '<section class="pq-khoi phongdao-bs-google" aria-label="Tiến độ lên Google"><p><strong>' . esc_html( number_format_i18n( $lap ) . ' / ' . number_format_i18n( $tong ) ) . '</strong> bài có phần thư viện tự viết (lên Google). Mỗi Ý nghĩa bạn viết thêm một bài; bài chỉ có lời vẫn đọc được.</p>'
			. '<progress class="pq-tien-do" max="' . (int) max( 1, $tong ) . '" value="' . (int) min( $lap, max( 1, $tong ) ) . '" aria-label="' . esc_attr( $lap . ' trên ' . $tong . ' bài lên Google' ) . '">' . (int) $lap . ' / ' . (int) $tong . '</progress></section>';
		$than = match ( $xem ) {
			'can_sua' => self::khoi_noi_lai( $noi ) . self::khoi_can_sua( $cs ) . self::khoi_y_nghia_dai( $dai ),
			'goi_y'   => self::khoi_goi_y(),
			'tu_dien' => self::khoi_tu_dien(),
			'khac'    => self::khoi_uu_tien(),
			'nhap'    => NapNhap::khoi(),
			default   => self::khoi_thieu( $thieu ),
		};
		return '<div class="wrap pq-man phongdao-ban phongdao-bo-sung"><div class="pq-dau"><div class="pq-dau-chu"><h1>Bổ sung</h1><p>Làm bài đã đăng đầy đủ hơn. Bài đọc nhiều trước; làm xong một bài, lưu và sang bài kế. Không có chỉ tiêu phải điền kín cả kho.</p></div></div><hr class="wp-header-end">'
			. TrangLo::lay_bao() . $tien . '<nav class="phongdao-hv-tab" aria-label="Việc bổ sung">' . $tab . '</nav>'
			. '<div class="phongdao-bs-than">' . $than . '</div>'
			. '<p class="description">Máy chỉ đếm và xếp thứ tự; không viết nội dung, không tự gắn chủ đề, vùng hay Từ điển.</p></div>';
	}

	/** Tab "Thiếu Ý nghĩa": bài đọc nhiều tuần này chưa có, nút Bắt đầu (bài đầu, đọc nhiều trước) và lối mở cả danh sách. */
	private static function khoi_thieu( int $thieu ): string {
		$o = '';
		foreach ( self::doc_nhieu_thieu() as $id ) {
			$o .= '<li><a href="' . esc_url( SuaTacPham::url( $id ) ) . '">' . esc_html( get_the_title( $id ) ?: 'Bài #' . $id ) . '</a></li>';
		}
		$loc = array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia', 'xep' => 'doc' );
		$ds  = DanhSachTacPham::url_loc( $loc );
		$dau = $thieu ? (int) ( DanhSachTacPham::ids_loc( $loc, 1 )[0] ?? 0 ) : 0;
		return '<p class="phongdao-bs-bat-dau">' . ( $dau ? '<a class="button button-primary" href="' . esc_url( SuaTacPham::url( $dau, $ds ) ) . '">Bắt đầu viết Ý nghĩa →</a> ' : '' )
			. '<a href="' . esc_url( $ds ) . '">Xem cả danh sách (' . esc_html( number_format_i18n( $thieu ) ) . ' bài, đọc nhiều trước)</a></p>'
			. '<h2>Bài đọc nhiều tuần này chưa có Ý nghĩa</h2>'
			. ( $o ? '<ol class="phongdao-nen-lam">' . $o . '</ol>' : '<p>Tuần này chưa có bài đọc nhiều nào thiếu Ý nghĩa.</p>' );
	}

	/** Tab "Việc khác": những việc không có tab riêng (nghĩa tục ngữ, câu đố, từ khó, đối chiếu sách, cờ), mỗi việc một số và nút Bắt đầu. */
	private static function khoi_uu_tien(): string {
		// Bỏ dòng đầu (Thiếu Ý nghĩa) và dòng cuối (Gợi ý chủ đề, vùng): đã có tab riêng
		$ut = array_slice( self::uu_tien(), 1, -1 );
		$hang = '';
		foreach ( $ut as [ $ten, $so, $ds, $dau ] ) {
			$hang .= '<tr><th scope="row">' . esc_html( $ten ) . '</th><td>' . ( $so ? '<a href="' . esc_url( $ds ) . '">' . esc_html( number_format_i18n( $so ) ) . '</a>' : '0' ) . '</td>'
				. '<td>' . ( $so ? '<a class="button" href="' . esc_url( $dau ) . '" aria-label="' . esc_attr( 'Bắt đầu: ' . $ten ) . '">Bắt đầu</a>' : '—' ) . '</td></tr>';
		}
		return '<h2>Danh sách ưu tiên</h2><p class="description">Mỗi việc mở bài đầu ở trang sửa, kèm lối "Bài tiếp theo" (bài đọc nhiều trước). Thiếu Ý nghĩa, Cần sửa, Gợi ý chủ đề và vùng ở tab riêng.</p><table class="widefat striped phongdao-bs-uu-tien"><caption class="screen-reader-text">Việc bổ sung theo loại</caption><thead><tr><th scope="col">Việc</th><th scope="col">Số bài</th><th scope="col">Làm</th></tr></thead><tbody>' . $hang . '</tbody></table>';
	}
}
