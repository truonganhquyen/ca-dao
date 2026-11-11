<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Bổ sung** trong wp-admin (D-103, docs/32 mục 3.5): làm bài đã đăng đầy đủ hơn, không chỉ tiêu. Đọc từ bảng tóm tắt
 * bài. Bài đọc nhiều chưa có Ý nghĩa lên đầu; danh sách ưu tiên (thiếu Ý nghĩa, tục ngữ thiếu nghĩa, câu đố thiếu đáp án
 * rồi thiếu giải thích, thiếu từ khó, thiếu đối chiếu sách, có cờ) xếp bài đọc nhiều trước; nút "Bắt đầu" mở bài đầu ở
 * trang Sửa tác phẩm kèm lối "Bài tiếp theo". Chỉ đọc và dẫn đường; máy không viết nội dung, không tự gắn phân loại.
 */
final class BoSung {

	public const TRANG = 'phongdao-bo-sung';

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::TRANG );
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

	public const HANH_DONG = 'phongdao_bo_sung_goi_y';

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan_goi_y' ) );
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
		if ( '' !== $nhom || $bai ) {
			return self::hien_duyet( preg_match( '/^(chu_de|vung):[a-z0-9_-]+$/', $nhom ) ? $nhom : '', $bai );
		}
		$doc = self::doc_nhieu_thieu();
		$o   = '';
		foreach ( $doc as $id ) {
			$o .= '<li><a href="' . esc_url( SuaTacPham::url( $id ) ) . '">' . esc_html( get_the_title( $id ) ?: 'Bài #' . $id ) . '</a></li>';
		}
		$hang = '';
		foreach ( self::uu_tien() as [ $ten, $so, $ds, $dau ] ) {
			$hang .= '<tr><th scope="row">' . esc_html( $ten ) . '</th><td>' . ( $so ? '<a href="' . esc_url( $ds ) . '">' . esc_html( number_format_i18n( $so ) ) . '</a>' : '0' ) . '</td>'
				. '<td>' . ( $so ? '<a class="button" href="' . esc_url( $dau ) . '" aria-label="' . esc_attr( 'Bắt đầu: ' . $ten ) . '">Bắt đầu</a>' : '—' ) . '</td></tr>';
		}
		return '<div class="wrap phongdao-ban phongdao-bo-sung"><h1>Bổ sung</h1>'
			. '<p class="description">Làm bài đã đăng đầy đủ hơn: Ý nghĩa, từ khó, dị bản, đối chiếu sách. Bài đọc nhiều lên đầu. Không có chỉ tiêu phải điền kín cả kho; viết xong một bài, lưu và sang bài kế.</p>'
			. '<h2>Bài đọc nhiều tuần này chưa có Ý nghĩa</h2>'
			. ( $o ? '<ol class="phongdao-nen-lam">' . $o . '</ol>' : '<p>Tuần này chưa có bài đọc nhiều nào thiếu Ý nghĩa.</p>' )
			. '<h2>Danh sách ưu tiên</h2><table class="widefat striped phongdao-ban-so-lieu"><caption class="screen-reader-text">Việc bổ sung theo loại</caption><thead><tr><th scope="col">Việc</th><th scope="col">Số bài</th><th scope="col">Làm</th></tr></thead><tbody>' . $hang . '</tbody></table>'
			. TrangLo::lay_bao() . self::khoi_goi_y()
			. '<p class="description">Máy chỉ đếm và xếp thứ tự; không viết nội dung, không tự gắn chủ đề hay vùng.</p></div>';
	}
}
