<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\LuuPhan\DuongDan;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\DichVu\TomTat\TomTat;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Danh sách Tác phẩm trong wp-admin (T-167, T-187; đợt 3 docs/32 mục 3.6). Đọc từ bảng tóm tắt bài (`TomTat`): lọc, đếm,
 * xếp không phải đọc post meta từng bài. Tab trạng thái kèm số (Tất cả · Đã đăng · Nháp · Đã ẩn · Thùng rác), nút lọc
 * nhanh "Thiếu ý nghĩa", "Có cờ", bộ lọc còn lại gập trong "Thêm bộ lọc"; mỗi hàng Sửa · Xem · Ẩn. Điện thoại: mỗi bài
 * một thẻ ba dòng (tên; trạng thái và thể loại; còn thiếu, cờ).
 */
final class DanhSachTacPham {

	public const TRANG = 'phongdao-tac-pham';
	public const TACH = 'phongdao_tach_duong_dan';
	private const MOI_TRANG = 30;
	private const TRANG_THAI = array(
		'tat_ca'  => 'Tất cả',
		'publish' => 'Đã đăng',
		'draft'   => 'Bản nháp',
		'pending' => 'Chờ',
		'private' => 'Đã ẩn',
		'future'  => 'Đã hẹn',
		'trash'   => 'Thùng rác',
	);
	/** Tab trạng thái trên đầu danh sách (Chờ, Đã hẹn vẫn lọc được qua đường dẫn). */
	private const TAB = array( 'tat_ca', 'publish', 'draft', 'private', 'trash' );
	/** "Tất cả" không gồm thùng rác (như WordPress). */
	private const KHONG_RAC = array( 'publish', 'draft', 'pending', 'private', 'future' );
	/** Mục "còn thiếu, có cờ" => [chữ, điều kiện trên bảng tóm tắt]. */
	private const MUC = array(
		'tat_ca'           => 'Không lọc',
		'thieu_y_nghia'    => 'Thiếu ý nghĩa',
		'thieu_nghia_tn'   => 'Tục ngữ thiếu nghĩa đen / nghĩa bóng',
		'thieu_dap_an'     => 'Câu đố thiếu đáp án',
		'thieu_giai_thich' => 'Câu đố thiếu giải thích đáp án',
		'thieu_tu_kho'     => 'Thiếu từ khó',
		'thieu_doi_chieu'  => 'Thiếu đối chiếu sách',
		'thieu_loi'        => 'Thiếu lời',
		'co_co'            => 'Có cờ nghi',
	);
	private const XEP = array(
		''    => 'Sửa gần nhất trước',
		'doc' => 'Đọc nhiều tuần này trước',
	);

	/** @return array<string,string|int> Bộ lọc đã kiểm; URL và SQL chỉ nhận các giá trị này. */
	private static function loc( ?array $vao = null ): array {
		$vao   = $vao ?? wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$nguon = sanitize_text_field( (string) ( $vao['nguon'] ?? 'tat_ca' ) );
		$ds_nguon = NguonTuDong::tat_ca();
		if ( ! in_array( $nguon, array( 'tat_ca', 'may', 'nguoi', 'khong_may' ), true ) ) {
			$id = str_starts_with( $nguon, 'may:' ) ? substr( $nguon, 4 ) : '';
			$nguon = isset( $ds_nguon[ $id ] ) && preg_match( '/^[a-z0-9_-]+$/', $id ) ? 'may:' . $id : 'tat_ca';
		}
		$tt  = sanitize_key( (string) ( $vao['tt'] ?? 'tat_ca' ) );
		$muc = sanitize_key( (string) ( $vao['muc'] ?? 'tat_ca' ) );
		$xep = sanitize_key( (string) ( $vao['xep'] ?? '' ) );
		return array(
			'q'        => sanitize_text_field( (string) ( $vao['q'] ?? '' ) ),
			'tt'       => isset( self::TRANG_THAI[ $tt ] ) ? $tt : 'tat_ca',
			'the_loai' => sanitize_title( (string) ( $vao['the_loai'] ?? '' ) ),
			'chu_de'   => sanitize_title( (string) ( $vao['chu_de'] ?? '' ) ),
			'muc'      => isset( self::MUC[ $muc ] ) ? $muc : 'tat_ca',
			'nguon'    => $nguon,
			'xep'      => isset( self::XEP[ $xep ] ) ? $xep : '',
			'paged'    => max( 1, (int) ( $vao['paged'] ?? 1 ) ),
		);
	}

	/**
	 * Phần FROM … WHERE của bộ lọc trên bảng bài + bảng tóm tắt (bí danh p, t). Bài chưa có dòng tóm tắt vẫn hiện khi không
	 * lọc theo phần thiếu / nguồn (LEFT JOIN).
	 */
	private static function tu( array $loc ): string {
		global $wpdb;
		TomTat::xong(); // bài vừa đổi trong lượt này: ghi dòng trước khi lọc
		$tt = 'tat_ca' === $loc['tt'] ? self::KHONG_RAC : array( (string) $loc['tt'] );
		$dk = array(
			$wpdb->prepare( 'p.post_type = %s', KhaiBao::TAC_PHAM ),
			'p.post_status IN (' . implode( ',', array_map( static fn( string $s ): string => "'" . esc_sql( $s ) . "'", $tt ) ) . ')',
		);
		if ( '' !== $loc['q'] ) {
			$dk[] = ctype_digit( (string) $loc['q'] )
				? $wpdb->prepare( 'p.ID = %d', (int) $loc['q'] )
				: $wpdb->prepare( '(p.post_title LIKE %s OR p.post_content LIKE %s)', '%' . $wpdb->esc_like( (string) $loc['q'] ) . '%', '%' . $wpdb->esc_like( (string) $loc['q'] ) . '%' );
		}
		if ( '' !== $loc['the_loai'] ) {
			$dk[] = self::co_term( KhaiBao::THE_LOAI, (string) $loc['the_loai'] );
		}
		if ( '' !== $loc['chu_de'] ) {
			$dk[] = self::co_term( 'chu_de', (string) $loc['chu_de'] );
		}
		$thieu = array( 'thieu_y_nghia' => 'y_nghia', 'thieu_nghia_tn' => 'nghia_tn', 'thieu_dap_an' => 'dap_an', 'thieu_giai_thich' => 'giai_thich', 'thieu_tu_kho' => 'tu_kho', 'thieu_doi_chieu' => 'doi_chieu', 'thieu_loi' => 'loi' )[ $loc['muc'] ] ?? '';
		if ( '' !== $thieu ) {
			$dk[] = $wpdb->prepare( 't.thieu LIKE %s', '%,' . $thieu . ',%' );
		} elseif ( 'co_co' === $loc['muc'] ) {
			$dk[] = 't.co = 1';
		}
		if ( 'may' === $loc['nguon'] ) {
			$dk[] = "t.nguon_may <> ''";
		} elseif ( str_starts_with( (string) $loc['nguon'], 'may:' ) ) {
			$dk[] = $wpdb->prepare( 't.nguon_may = %s', substr( (string) $loc['nguon'], 4 ) );
		} elseif ( 'nguoi' === $loc['nguon'] ) {
			$dk[] = 't.nguon_nguoi = 1';
		} elseif ( 'khong_may' === $loc['nguon'] ) {
			$dk[] = "(t.post_id IS NULL OR t.nguon_may = '')";
		}
		return " FROM {$wpdb->posts} p LEFT JOIN " . TomTat::bang() . ' t ON t.post_id = p.ID WHERE ' . implode( ' AND ', $dk );
	}

	/** Điều kiện "bài có mục phân loại này" (theo slug). */
	private static function co_term( string $tax, string $slug ): string {
		global $wpdb;
		return $wpdb->prepare( "EXISTS (SELECT 1 FROM {$wpdb->term_relationships} tr JOIN {$wpdb->term_taxonomy} tx ON tx.term_taxonomy_id = tr.term_taxonomy_id JOIN {$wpdb->terms} te ON te.term_id = tx.term_id WHERE tr.object_id = p.ID AND tx.taxonomy = %s AND te.slug = %s)", $tax, $slug );
	}

	private static function xep( array $loc ): string {
		return 'doc' === $loc['xep'] ? ' ORDER BY COALESCE(t.doc_7, 0) DESC, p.post_modified DESC, p.ID DESC' : ' ORDER BY p.post_modified DESC, p.ID DESC';
	}

	/** Số tác phẩm khớp một bộ lọc (Hôm nay, Bổ sung), không phụ thuộc phân trang. */
	public static function dem_loc( array $vao ): int {
		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT COUNT(*)' . self::tu( self::loc( $vao ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- điều kiện đã prepare từng phần
	}

	/** Đường dẫn tới danh sách đã lọc (bộ lọc qua cùng bước kiểm như form). */
	public static function url_loc( array $vao ): string {
		return self::url( self::loc( $vao ) );
	}

	/**
	 * Mọi ID tác phẩm khớp bộ lọc (phạm vi "mọi bài khớp bộ lọc" của một lô), cùng điều kiện và thứ tự với danh sách; lấy
	 * tối đa $toi_da + 1 để biết có vượt giới hạn.
	 *
	 * @param array<string,string> $vao bộ lọc đã gửi (q, tt, the_loai, chu_de, muc, nguon, xep)
	 * @return int[]
	 */
	public static function ids_loc( array $vao, int $toi_da ): array {
		global $wpdb;
		$loc = self::loc( $vao );
		return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( 'SELECT p.ID' . self::tu( $loc ) . self::xep( $loc ) . ' LIMIT %d', $toi_da + 1 ) ) ?: array() ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/** @return array{tong:int,ids:int[]} một trang của danh sách */
	private static function tim( array $loc ): array {
		global $wpdb;
		$tu   = self::tu( $loc );
		$tong = (int) $wpdb->get_var( 'SELECT COUNT(*)' . $tu ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$ids  = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( 'SELECT p.ID' . $tu . self::xep( $loc ) . ' LIMIT %d OFFSET %d', self::MOI_TRANG, ( (int) $loc['paged'] - 1 ) * self::MOI_TRANG ) ) ?: array() ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array( 'tong' => $tong, 'ids' => $ids );
	}

	/** Dòng tóm tắt của đúng các bài trên trang (một truy vấn). @return array<int,array> */
	private static function tom_trang( array $ids ): array {
		global $wpdb;
		if ( ! $ids ) {
			return array();
		}
		$ra = array();
		foreach ( $wpdb->get_results( 'SELECT * FROM ' . TomTat::bang() . ' WHERE post_id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')', ARRAY_A ) ?: array() as $d ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- mã đã ép số
			$ra[ (int) $d['post_id'] ] = $d;
		}
		// Bài chưa có dòng (ghi thẳng vào CSDL, bảng đang dựng dở): hẹn dựng lại theo lô, không tính từng bài ngay lúc mở trang
		if ( array_diff( $ids, array_keys( $ra ) ) && (int) get_option( TomTat::SAU, -1 ) < 0 ) {
			TomTat::bat_dau_dung_lai();
		}
		return $ra;
	}

	/** Số bài theo từng tab trạng thái (đếm của WordPress, không phụ thuộc bảng tóm tắt). @return array<string,int> */
	private static function dem_tab(): array {
		$dem = (array) wp_count_posts( KhaiBao::TAC_PHAM );
		$ra  = array( 'tat_ca' => 0 );
		foreach ( self::KHONG_RAC as $tt ) {
			$ra['tat_ca'] += (int) ( $dem[ $tt ] ?? 0 );
		}
		foreach ( array( 'publish', 'draft', 'private', 'trash' ) as $tt ) {
			$ra[ $tt ] = (int) ( $dem[ $tt ] ?? 0 );
		}
		return $ra;
	}

	/** Đếm việc mở của đúng 30 bài trên trang bằng một truy vấn. */
	private static function viec_trang( array $ids ): array {
		global $wpdb;
		if ( ! $ids ) {
			return array();
		}
		$dau = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql = $wpdb->prepare( 'SELECT doi_tuong_id, COUNT(*) AS so, MIN(id) AS dau FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'post' AND trang_thai IN ('cho','de_sau') AND doi_tuong_id IN ($dau) GROUP BY doi_tuong_id", $ids );
		$ra = array();
		foreach ( $wpdb->get_results( $sql, ARRAY_A ) ?: array() as $hang ) {
			$ra[ (int) $hang['doi_tuong_id'] ] = array( 'so' => (int) $hang['so'], 'dau' => (int) $hang['dau'] );
		}
		return $ra;
	}

	private static function url( array $loc, int $trang = 1 ): string {
		return add_query_arg( array_filter( array( 'page' => self::TRANG, 'q' => $loc['q'], 'tt' => $loc['tt'], 'the_loai' => $loc['the_loai'], 'chu_de' => $loc['chu_de'], 'muc' => $loc['muc'], 'nguon' => $loc['nguon'], 'xep' => $loc['xep'] ?? '', 'paged' => $trang ), static fn( $v ) => '' !== $v ), admin_url( 'admin.php' ) );
	}

	private static function chon( string $ten, string $nhan, array $ds, string $gia_tri ): string {
		$ra = '<div class="phongdao-tp-truong"><label for="phongdao-' . esc_attr( $ten ) . '">' . esc_html( $nhan ) . '</label><select id="phongdao-' . esc_attr( $ten ) . '" name="' . esc_attr( $ten ) . '">';
		foreach ( $ds as $ma => $ten_hien ) {
			$ra .= '<option value="' . esc_attr( (string) $ma ) . '"' . selected( (string) $ma, $gia_tri, false ) . '>' . esc_html( (string) $ten_hien ) . '</option>';
		}
		return $ra . '</select></div>';
	}

	private static function tu_nguon( array $ds ): array {
		$ra = array( 'tat_ca' => 'Mọi nguồn', 'may' => 'Có nguồn tự động', 'nguoi' => 'Có nguồn người điền', 'khong_may' => 'Không từ nguồn tự động' );
		foreach ( $ds as $id => $n ) {
			if ( preg_match( '/^[a-z0-9_-]+$/', (string) $id ) ) {
				$ra[ 'may:' . $id ] = NguonTuDong::ten( (string) $id );
			}
		}
		return $ra;
	}

	/** Chữ "còn thiếu" của một dòng tóm tắt (tối đa ba mục, còn lại ghi "+N"). */
	private static function chu_thieu( ?array $tt ): string {
		if ( null === $tt ) {
			return 'đang tính';
		}
		$ds = array_map( static fn( string $m ): string => TomTat::THIEU[ $m ] ?? $m, TomTat::thieu( $tt ) );
		if ( ! $ds ) {
			return 'Đủ phần chính';
		}
		return implode( ', ', array_slice( $ds, 0, 3 ) ) . ( count( $ds ) > 3 ? ' +' . ( count( $ds ) - 3 ) : '' );
	}

	/**
	 * Một hàng: tên (mở trang sửa) kèm Sửa · Xem · Ẩn; điện thoại thêm dòng 2 (trạng thái · thể loại) và dòng 3 (còn thiếu,
	 * cờ) ngay trong ô tên, các cột còn lại ẩn. Nút Ẩn gửi form riêng đặt ngoài bảng (bảng nằm trong form lô).
	 */
	private static function dong( \WP_Post $bai, ?array $tt_bai, array $viec, bool $lo, string $ve_ds ): string {
		$id   = (int) $bai->ID;
		$tt   = self::TRANG_THAI[ $bai->post_status ] ?? $bai->post_status;
		$loai = TheLoai::DS[ (string) ( $tt_bai['the_loai'] ?? '' ) ] ?? 'Chưa có thể loại';
		$sua  = 'trash' !== $bai->post_status && current_user_can( 'edit_post', $id );
		$xem  = ! $sua ? '' : ( in_array( $bai->post_status, array( 'publish', 'private' ), true ) ? (string) get_permalink( $bai ) : (string) get_preview_post_link( $bai ) );
		$ten  = '' !== trim( $bai->post_title ) ? $bai->post_title : '(Chưa đặt tên)';
		$ten  = $sua ? '<a href="' . esc_url( SuaTacPham::url( $id, $ve_ds ) ) . '">' . esc_html( $ten ) . '</a>' : esc_html( $ten );
		$hanh = array();
		if ( $sua ) {
			$hanh[] = '<a href="' . esc_url( SuaTacPham::url( $id, $ve_ds ) ) . '" aria-label="' . esc_attr( 'Sửa bài #' . $id ) . '">Sửa</a>';
			$hanh[] = '<a href="' . esc_url( $xem ) . '" aria-label="' . esc_attr( 'Xem bài #' . $id ) . '">Xem</a>';
			if ( 'publish' === $bai->post_status ) {
				$hanh[] = '<button type="submit" form="phongdao-an-' . $id . '" class="button-link" aria-label="' . esc_attr( 'Ẩn bài #' . $id . ' khỏi người đọc' ) . '">Ẩn</button>';
			}
		}
		$co    = ! empty( $tt_bai['co'] );
		$thieu = self::chu_thieu( $tt_bai );
		$con   = esc_html( in_array( $thieu, array( 'Đủ phần chính', 'đang tính' ), true ) ? ( 'đang tính' === $thieu ? 'Còn thiếu: đang tính' : $thieu ) : 'Thiếu: ' . $thieu ) . ( $co ? ' · <strong>Có cờ</strong>' : '' );
		$ve    = (int) ( $viec[ $id ]['so'] ?? 0 );
		$viec_lien = $ve ? '<a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => (int) $viec[ $id ]['dau'] ), admin_url( 'admin.php' ) ) ) . '">' . $ve . ' việc mở</a>' : '—';
		$nguon = '' !== (string) ( $tt_bai['nguon_may'] ?? '' ) ? NguonTuDong::ten( (string) $tt_bai['nguon_may'] ) : ( ! empty( $tt_bai['nguon_nguoi'] ) ? 'Nguồn người điền' : 'Chưa ghi nguồn' );
		$chon  = $lo ? '<td class="phongdao-tp-chon" data-nhan="Chọn"><input type="checkbox" name="ids[]" value="' . $id . '" id="chon-bai-' . $id . '"><label for="chon-bai-' . $id . '" class="screen-reader-text">Chọn bài #' . $id . '</label></td>' : '';
		return '<tr>' . $chon . '<td class="phongdao-tp-an-dt" data-nhan="ID">' . $id . '</td>'
			. '<td class="phongdao-tp-ten" data-nhan="Tác phẩm"><strong>' . $ten . '</strong>'
			. '<span class="phongdao-tp-dong2">' . esc_html( $tt . ' · ' . $loai ) . ( $ve ? ' · ' . $viec_lien : '' ) . '</span>'
			. '<span class="phongdao-tp-dong3">' . $con . '</span>'
			. ( $hanh ? '<span class="phongdao-tp-hanh">' . implode( ' <span aria-hidden="true">·</span> ', $hanh ) . '</span>' : '' ) . '</td>'
			. '<td class="phongdao-tp-an-dt" data-nhan="Trạng thái">' . esc_html( $tt ) . '</td><td class="phongdao-tp-an-dt" data-nhan="Thể loại">' . esc_html( $loai ) . '</td>'
			. '<td class="phongdao-tp-an-dt" data-nhan="Còn thiếu">' . $con . '</td><td class="phongdao-tp-an-dt" data-nhan="Nguồn">' . esc_html( $nguon ) . '</td>'
			. '<td class="phongdao-tp-an-dt" data-nhan="Việc">' . $viec_lien . '</td><td class="phongdao-tp-an-dt" data-nhan="Sửa cuối">' . esc_html( mysql2date( 'd/m/Y H:i', $bai->post_modified ) ) . '</td></tr>';
	}

	/** Form Ẩn của từng bài đã đăng trên trang (đặt ngoài bảng, nút Ẩn trên hàng gửi bằng thuộc tính `form`). */
	private static function form_an( array $bai, string $ve ): string {
		$o = '';
		foreach ( $bai as $p ) {
			if ( 'publish' === $p->post_status && current_user_can( 'edit_post', $p->ID ) ) {
				$o .= '<form id="phongdao-an-' . (int) $p->ID . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" hidden><input type="hidden" name="action" value="phongdao_doi_trang_thai">'
					. '<input type="hidden" name="id" value="' . (int) $p->ID . '"><input type="hidden" name="trang_thai" value="private"><input type="hidden" name="trang_thai_cu" value="publish"><input type="hidden" name="quay_lai" value="' . esc_attr( $ve ) . '">'
					. wp_nonce_field( 'phongdao_doi_trang_thai_' . $p->ID, '_wpnonce', false, false ) . '</form>';
			}
		}
		return $o;
	}

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::TACH, array( self::class, 'tach' ) );
	}

	/** Người quản trị chọn một bài trong nhóm trùng đường dẫn để nhận đường dẫn riêng (H6: chỉ khi người bấm). */
	public static function tach(): void {
		$id = absint( $_POST['id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dưới
		if ( ! current_user_can( 'cadao_nguon' ) || ! $id ) {
			wp_die( 'Chỉ người quản trị được đổi đường dẫn của bài đã đăng.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::TACH . '_' . $id );
		set_transient( 'phongdao_tach_bao_' . get_current_user_id(), DuongDan::tach( $id, get_current_user_id() ), MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::TRANG ) );
		exit;
	}

	/**
	 * Khối báo tác phẩm đã đăng dùng chung đường dẫn (T-178): người đọc vào địa chỉ đó chỉ mở được một bài. Ai có quyền
	 * biên soạn đều thấy để báo; nút tách chỉ hiện với người quản trị.
	 */
	public static function bao_trung(): string {
		$o   = '';
		$bao = get_transient( 'phongdao_tach_bao_' . get_current_user_id() );
		if ( is_array( $bao ) ) {
			delete_transient( 'phongdao_tach_bao_' . get_current_user_id() );
			$o .= '<div class="notice ' . ( $bao['ok'] ? 'notice-success' : 'notice-error' ) . '" role="status"><p>' . esc_html( (string) $bao['thong_bao'] ) . '</p></div>';
		}
		$nhom = DuongDan::trung();
		if ( ! $nhom ) {
			return $o;
		}
		$quyen = current_user_can( 'cadao_nguon' );
		$o    .= '<div class="notice notice-warning phongdao-trung-duong-dan"><p><strong>' . count( $nhom ) . ' đường dẫn đang dùng chung cho nhiều bài đã đăng.</strong> Người đọc vào mỗi địa chỉ chỉ mở được một bài; bài còn lại không có lối vào (thẻ của nó mở ra bài khác). '
			. ( $quyen ? 'Chọn bài nhận đường dẫn riêng (thêm đuôi số). Đây là đổi đường dẫn của bài đã đăng: chỉ đổi khi bạn bấm.' : 'Báo người quản trị chọn bài nào nhận đường dẫn riêng.' ) . '</p><ul>';
		foreach ( $nhom as $ten => $ds ) {
			$o .= '<li><code>' . esc_html( (string) $ten ) . '</code><ul>';
			foreach ( $ds as $b ) {
				$o .= '<li><a href="' . esc_url( SuaTacPham::url( $b['id'] ) ) . '">#' . $b['id'] . ' ' . esc_html( $b['ten'] ) . '</a> · đăng ' . esc_html( mysql2date( 'd/m/Y', $b['ngay'] ) ) . ' · '
					. ( $b['mo_duoc'] ? '<strong>người đọc đang mở được bài này</strong>' : 'người đọc không mở được bài này' )
					. ( $quyen ? ' <form method="post" class="phongdao-tach" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::TACH . '"><input type="hidden" name="id" value="' . $b['id'] . '">' . wp_nonce_field( self::TACH . '_' . $b['id'], '_wpnonce', true, false ) . '<button type="submit" class="button" aria-label="Cho bài này đường dẫn riêng: #' . $b['id'] . '">Cho bài này đường dẫn riêng</button></form>' : '' )
					. '</li>';
			}
			$o .= '</ul></li>';
		}
		return $o . '</ul></div>';
	}

	/** Tab trạng thái kèm số và hai nút lọc nhanh (Thiếu ý nghĩa, Có cờ); giữ các bộ lọc khác. */
	private static function tab( array $loc ): string {
		$dem = self::dem_tab();
		$o   = '<nav class="phongdao-hv-tab phongdao-tp-tab" aria-label="Trạng thái bài">';
		foreach ( self::TAB as $tt ) {
			$o .= '<a class="phongdao-chip" href="' . esc_url( self::url( array( 'tt' => $tt, 'paged' => 1 ) + $loc ) ) . '"' . ( $tt === $loc['tt'] ? ' aria-current="page"' : '' ) . '>' . esc_html( self::TRANG_THAI[ $tt ] ) . ' <span class="phongdao-chip-so">' . esc_html( number_format_i18n( $dem[ $tt ] ?? 0 ) ) . '</span></a>';
		}
		$o .= '</nav><p class="phongdao-tp-nhanh">';
		foreach ( array( 'thieu_y_nghia' => 'Thiếu ý nghĩa', 'co_co' => 'Có cờ' ) as $muc => $chu ) {
			$dang = $muc === $loc['muc'];
			$o   .= '<a class="button" href="' . esc_url( self::url( array( 'muc' => $dang ? 'tat_ca' : $muc, 'paged' => 1 ) + $loc ) ) . '"' . ( $dang ? ' aria-current="true"' : '' ) . '>' . esc_html( $chu ) . ( $dang ? ' ✓' : '' ) . '</a> ';
		}
		return $o . '</p>';
	}

	/** Ô tìm luôn hiện; các bộ lọc còn lại gập trong "Thêm bộ lọc" (mở sẵn khi đang dùng một bộ lọc trong đó). */
	private static function form_loc( array $loc ): string {
		$ds_the_loai = array( '' => 'Mọi thể loại' ) + TheLoai::DS;
		$ds_chu_de   = array( '' => 'Mọi chủ đề' );
		$ds          = get_terms( array( 'taxonomy' => 'chu_de', 'hide_empty' => false ) );
		foreach ( is_wp_error( $ds ) ? array() : $ds as $term ) {
			$ds_chu_de[ $term->slug ] = $term->name;
		}
		$dang = '' !== $loc['the_loai'] || '' !== $loc['chu_de'] || 'tat_ca' !== $loc['nguon'] || '' !== $loc['xep'] || ! in_array( $loc['muc'], array( 'tat_ca', 'thieu_y_nghia', 'co_co' ), true );
		return '<form class="phongdao-tp-tim" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '"><input type="hidden" name="tt" value="' . esc_attr( (string) $loc['tt'] ) . '">'
			. '<div class="phongdao-tp-tim-o"><label for="phongdao-q">Tìm ID, tên hoặc lời</label><input id="phongdao-q" type="search" name="q" value="' . esc_attr( (string) $loc['q'] ) . '"><button class="button button-primary" type="submit">Tìm</button></div>'
			. '<details class="phongdao-tp-them"' . ( $dang ? ' open' : '' ) . '><summary>Thêm bộ lọc' . ( $dang ? ' (đang dùng)' : '' ) . '</summary><div class="phongdao-tp-loc">'
			. self::chon( 'the_loai', 'Thể loại', $ds_the_loai, (string) $loc['the_loai'] )
			. self::chon( 'chu_de', 'Chủ đề', $ds_chu_de, (string) $loc['chu_de'] )
			. self::chon( 'muc', 'Còn thiếu, có cờ', self::MUC, (string) $loc['muc'] )
			. self::chon( 'nguon', 'Nguồn', self::tu_nguon( NguonTuDong::tat_ca() ), (string) $loc['nguon'] )
			. self::chon( 'xep', 'Xếp', self::XEP, (string) $loc['xep'] )
			. '<button class="button" type="submit">Lọc</button></div></details></form>';
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem danh sách này.</p></div>';
		}
		$loc  = self::loc();
		$kq   = self::tim( $loc );
		if ( $kq['ids'] ) {
			_prime_post_caches( $kq['ids'], false, false );
		}
		$bai  = array_values( array_filter( array_map( 'get_post', $kq['ids'] ) ) );
		$tom  = self::tom_trang( $kq['ids'] );
		$viec = self::viec_trang( $kq['ids'] );
		$ve   = self::url( $loc, (int) $loc['paged'] );
		// Lời báo sau khi Ẩn trên hàng (về lại đúng danh sách)
		$da   = sanitize_text_field( wp_unslash( (string) ( $_GET['da'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ hiện lời báo
		$bao  = '' === $da ? '' : '<div class="notice notice-info"><p role="status">' . esc_html( $da ) . '</p></div>';
		// Làm theo lô (T-171): ô chọn và khối thao tác khi có bài khớp
		$lo   = $kq['tong'] > 0;
		$dong = implode( '', array_map( static fn( \WP_Post $p ): string => self::dong( $p, $tom[ (int) $p->ID ] ?? null, $viec, $lo, $ve ), $bai ) );
		$so_trang = (int) ceil( $kq['tong'] / self::MOI_TRANG );
		$phan_trang = $so_trang > 1 ? '<nav class="phongdao-tp-trang" aria-label="Phân trang"><span>Trang ' . (int) $loc['paged'] . ' / ' . $so_trang . '</span>'
			. ( $loc['paged'] > 1 ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] - 1 ) ) . '">Trang trước</a>' : '' )
			. ( $loc['paged'] < $so_trang ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] + 1 ) ) . '">Trang sau</a>' : '' ) . '</nav>' : '';
		return '<div class="wrap phongdao-admin-tp phongdao-ds-tp"><h1 class="wp-heading-inline">Tác phẩm</h1> <a class="page-title-action" href="' . esc_url( ThemTacPham::url() ) . '">Thêm tác phẩm</a><hr class="wp-header-end">'
			. $bao . TrangLo::lay_bao() . self::bao_trung() . self::tab( $loc ) . self::form_loc( $loc )
			. '<p class="phongdao-tp-tong" role="status"><strong>' . (int) $kq['tong'] . '</strong> tác phẩm khớp bộ lọc. Hiện ' . count( $bai ) . ' mục trên trang này.</p>'
			. ( $lo ? '<form method="post" class="phongdao-lo-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . TrangLo::khoi_chon_bai( $loc, (int) $kq['tong'] ) : '' )
			. ( $lo ? TrangLo::chon_het( (int) $kq['tong'], 'bài' ) : '' )
			. '<table class="widefat striped phongdao-tp-bang"><thead><tr>' . ( $lo ? '<th scope="col">Chọn</th>' : '' ) . '<th scope="col">ID</th><th scope="col">Tác phẩm</th><th scope="col">Trạng thái</th><th scope="col">Thể loại</th><th scope="col">Còn thiếu</th><th scope="col">Nguồn</th><th scope="col">Việc</th><th scope="col">Sửa cuối</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="9">Không có tác phẩm khớp. Thử đổi bộ lọc.</td></tr>' ) . '</tbody></table>' . ( $lo ? '</form>' : '' ) . self::form_an( $bai, $ve ) . $phan_trang . ( $lo ? TrangLo::thanh_noi( 'bài', TrangLo::nut_mo_lo() ) : '' ) . '</div>';
	}
}
