<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\GoiY\GoiY;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\DichVu\XuLyLo\Lo;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Màn Lô thao tác trong wp-admin (docs/30 mục 3; T-171): xem trước lô (bỏ chọn ngoại lệ), áp dụng từng lượt, tiếp tục sau
 * gián đoạn, xem kết quả từng mục. Lô tạo từ danh sách Hộp việc (chọn trên trang hoặc mọi mục khớp bộ lọc). Mọi thay đổi
 * đi qua DichVu\XuLyLo\Lo; form có nonce, máy chủ kiểm quyền và chủ lô.
 */
final class TrangLo {

	public const TRANG = 'phongdao-lo';
	private const MOI_TRANG = 30;
	private const TRANG_THAI = array(
		Viec::LO_XEM  => 'Chờ xem trước',
		Viec::LO_DANG => 'Đang chạy',
		Viec::LO_XONG => 'Xong',
		Viec::LO_HUY  => 'Đã dừng',
	);

	public static function dang_ky(): void {
		add_action( 'admin_post_phongdao_lo_tao', array( self::class, 'tao' ) );
		add_action( 'admin_post_phongdao_lo_lam', array( self::class, 'lam' ) );
		add_action( 'admin_post_phongdao_lo_tao_bai', array( self::class, 'tao_bai' ) );
	}

	public static function url( int $lo = 0, array $them = array() ): string {
		return add_query_arg( array_filter( array( 'page' => self::TRANG, 'lo' => $lo ) + $them, static fn( $v ) => '' !== $v && 0 !== $v ), admin_url( 'admin.php' ) );
	}

	private static function bao( bool $ok, string $chu ): void {
		set_transient( 'phongdao_lo_bao_' . get_current_user_id(), array( 'ok' => $ok, 'chu' => $chu ), MINUTE_IN_SECONDS );
	}

	/** Lời báo một lần sau khi chuyển trang (dùng chung cho Hộp việc và màn Lô). */
	public static function lay_bao(): string {
		$k = 'phongdao_lo_bao_' . get_current_user_id();
		$b = get_transient( $k );
		if ( ! is_array( $b ) ) {
			return '';
		}
		delete_transient( $k );
		return '<div class="notice ' . ( empty( $b['ok'] ) ? 'notice-error' : 'notice-success' ) . '"><p role="status">' . esc_html( (string) ( $b['chu'] ?? '' ) ) . '</p></div>';
	}

	/** Khối "Làm theo lô" trên danh sách Hộp việc (chỉ khi xem thẻ Chờ / Để sau). */
	public static function khoi_chon( array $loc, int $tong ): string {
		$tt = '';
		foreach ( Lo::THAO_TAC as $ma => [ $ten, $dt, $quyen ] ) {
			if ( 'viec' === $dt && current_user_can( $quyen ) ) {
				$tt .= '<option value="' . esc_attr( $ma ) . '">' . esc_html( $ten ) . '</option>';
			}
		}
		return '<fieldset class="phongdao-lo-chon"><legend>Làm theo lô</legend>'
			. '<input type="hidden" name="action" value="phongdao_lo_tao">' . wp_nonce_field( 'phongdao_lo_tao', '_wpnonce', false, false )
			. '<input type="hidden" name="tt" value="' . esc_attr( (string) $loc['tt'] ) . '"><input type="hidden" name="loai" value="' . esc_attr( (string) $loc['loai'] ) . '"><input type="hidden" name="q" value="' . esc_attr( (string) $loc['q'] ) . '">'
			. '<div class="phongdao-lo-o"><label for="phongdao-lo-thao-tac">Thao tác</label><select id="phongdao-lo-thao-tac" name="thao_tac"><option value="">Chọn thao tác</option>' . $tt . '</select></div>'
			. '<fieldset class="phongdao-lo-pham-vi"><legend>Phạm vi</legend>'
			. '<label><input type="radio" name="pham_vi" value="chon" checked> Các thẻ đã chọn trên trang này</label>'
			. '<label><input type="radio" name="pham_vi" value="loc"> Mọi thẻ khớp bộ lọc (' . (int) $tong . ' thẻ)</label></fieldset>'
			. '<button type="submit" class="button">Xem trước</button>'
			. '<p class="description">Chỉ chọn các thẻ cùng một loại. Bước xem trước chốt danh sách, cho bỏ chọn ngoại lệ; chưa có gì thay đổi cho tới khi bấm Áp dụng.</p></fieldset>';
	}

	/** Tạo lô từ danh sách Hộp việc. */
	public static function tao(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền tạo lô.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_lo_tao' );
		$loc = array(
			'tt'   => sanitize_key( (string) ( $_POST['tt'] ?? Viec::CHO ) ),
			'loai' => sanitize_key( (string) ( $_POST['loai'] ?? '' ) ),
			'q'    => sanitize_text_field( wp_unslash( (string) ( $_POST['q'] ?? '' ) ) ),
		);
		$pham_vi = 'loc' === ( $_POST['pham_vi'] ?? '' ) ? 'loc' : 'chon';
		$ids     = 'loc' === $pham_vi ? Viec::ids_quan_tri( $loc['tt'], $loc['loai'], $loc['q'], Lo::TOI_DA ) : array_map( 'absint', (array) wp_unslash( $_POST['ids'] ?? array() ) );
		$thao_tac = sanitize_key( (string) ( $_POST['thao_tac'] ?? '' ) );
		$kq       = 'viec' !== ( Lo::THAO_TAC[ $thao_tac ][1] ?? '' ) ? array( 'ok' => false, 'lo' => 0, 'loi' => 'Chọn một thao tác.' ) : Lo::tao( $thao_tac, $ids, $pham_vi, $loc, get_current_user_id() );
		if ( ! $kq['ok'] ) {
			self::bao( false, $kq['loi'] );
			wp_safe_redirect( add_query_arg( array_filter( array( 'page' => DanhSachViec::TRANG ) + $loc, static fn( $v ) => '' !== $v ), admin_url( 'admin.php' ) ) );
			exit;
		}
		wp_safe_redirect( self::url( $kq['lo'] ) );
		exit;
	}

	/** Lưu bỏ chọn / áp dụng / tiếp tục / dừng. */
	public static function lam(): void {
		$lo = absint( $_POST['lo'] ?? 0 );
		if ( ! current_user_can( 'cadao_soan' ) || ! $lo ) {
			wp_die( 'Bạn không có quyền với lô này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_lo_' . $lo );
		$nguoi = get_current_user_id();
		$lam   = sanitize_key( (string) ( $_POST['lam'] ?? '' ) );
		$trang = max( 1, (int) ( $_POST['trang'] ?? 1 ) );
		$them  = array( 'trang' => $trang > 1 ? $trang : '' );
		if ( in_array( $lam, array( 'luu_bo', 'ap_dung' ), true ) && isset( $_POST['tren_trang'] ) ) {
			$bc = Lo::bo_chon( $lo, array_map( 'absint', (array) wp_unslash( $_POST['tren_trang'] ) ), array_map( 'absint', (array) wp_unslash( $_POST['giu'] ?? array() ) ), $nguoi );
			if ( ! $bc['ok'] ) {
				self::bao( false, $bc['loi'] );
				wp_safe_redirect( self::url( $lo, $them ) );
				exit;
			}
			if ( 'luu_bo' === $lam ) {
				self::bao( true, 'Đã lưu bỏ chọn: ' . $bc['bo'] . ' mục sẽ bỏ qua.' );
			}
		}
		if ( in_array( $lam, array( 'ap_dung', 'tiep' ), true ) ) {
			$kq = Lo::ap_dung( $lo, $nguoi, current_user_can( 'cadao_duyet' ) );
			if ( ! $kq['ok'] ) {
				self::bao( false, $kq['loi'] );
			} elseif ( $kq['con'] > 0 ) {
				$them = array( 'tu_chay' => empty( $_POST['tu_chay'] ) ? '' : '1' );
			} else {
				self::bao( true, 'Lô đã chạy xong.' );
				$them = array();
			}
		} elseif ( 'huy' === $lam ) {
			$kq = Lo::huy( $lo, $nguoi );
			self::bao( $kq['ok'], $kq['ok'] ? 'Đã dừng lô; mục chưa làm giữ nguyên.' : $kq['loi'] );
			$them = array();
		}
		wp_safe_redirect( self::url( $lo, $them ) );
		exit;
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><h1>Lô thao tác</h1><p>Bạn không có quyền xem lô.</p></div>';
		}
		$id = absint( $_GET['lo'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$h  = '<div class="wrap phongdao-admin-tp phongdao-admin-lo"><h1>Lô thao tác</h1>' . self::lay_bao();
		if ( ! $id ) {
			return $h . self::ds() . '</div>';
		}
		$lo = Viec::lay( $id );
		if ( ! $lo || Viec::LO !== $lo['loai'] || (string) get_current_user_id() !== $lo['nguoi_tao'] ) {
			return $h . '<p role="status">Không tìm thấy lô này, hoặc lô do người khác tạo.</p><p><a href="' . esc_url( self::url() ) . '">← Các lô của bạn</a></p></div>';
		}
		$nd    = $lo['noi_dung'];
		$tong  = count( (array) $nd['muc'] );
		$kq    = (array) ( $lo['sau']['ket_qua'] ?? array() );
		$bai   = self::la_bai( $lo );
		$ten   = Lo::THAO_TAC[ $nd['thao_tac'] ][0] ?? $nd['thao_tac'];
		$loai  = $bai ? self::mo_ta_tham_so( (array) ( $nd['tham_so'] ?? array() ) ) : ( QuyetViec::LOAI[ $nd['loai_viec'] ][0] ?? $nd['loai_viec'] );
		$t     = strtotime( (string) $lo['tao_luc'] . ' UTC' );
		$h    .= '<p><a href="' . esc_url( self::url() ) . '">← Các lô của bạn</a></p>'
			. '<p>Lô #' . (int) $lo['id'] . ' · <strong>' . esc_html( $ten ) . '</strong>' . ( '' === $loai ? '' : ' · ' . esc_html( $loai ) ) . ' · ' . $tong . ( $bai ? ' bài' : ' thẻ' ) . ' · '
			. esc_html( 'loc' === $nd['pham_vi'] ? 'mọi mục khớp bộ lọc lúc chọn' : 'các mục đã chọn' ) . ' · tạo ' . esc_html( $t ? wp_date( 'd/m/Y H:i', $t ) : '' )
			. ' · <strong>' . esc_html( self::TRANG_THAI[ $lo['trang_thai'] ] ?? $lo['trang_thai'] ) . '</strong></p>';
		if ( Viec::LO_XEM === $lo['trang_thai'] ) {
			return $h . ( $bai ? self::xem_truoc_bai( $lo ) : self::xem_truoc( $lo ) ) . '</div>';
		}
		$dem = Lo::dem( $kq );
		$h  .= '<p class="phongdao-tp-tong" role="status">Đã xử lý <strong>' . count( $kq ) . '</strong> / ' . $tong . ' mục: '
			. esc_html( implode( ' · ', array_map( static fn( string $ma ): string => Lo::KET_QUA[ $ma ] . ' ' . $dem[ $ma ], array_keys( Lo::KET_QUA ) ) ) ) . '.</p>';
		if ( Viec::LO_DANG === $lo['trang_thai'] ) {
			$tu = ! empty( $_GET['tu_chay'] ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ quyết có tự bấm Tiếp tục
			$h .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' . ( $tu ? ' data-tu-chay' : '' ) . '>' . self::an( (int) $lo['id'] ) . ( $tu ? '<input type="hidden" name="tu_chay" value="1">' : '' )
				. '<p class="phongdao-lo-nut"><button type="submit" name="lam" value="tiep" class="button button-primary" data-tiep>Tiếp tục</button> <button type="submit" name="lam" value="huy" class="button">Dừng lô</button></p>'
				. ( $tu ? '<p class="description" data-chi-js hidden>Đang tự chạy tiếp từng lượt ' . Lo::MOI_LUOT . ' mục. <a href="' . esc_url( self::url( (int) $lo['id'] ) ) . '">Dừng tự chạy</a></p>' : '' ) . '</form>';
		}
		return $h . self::ket_qua( $lo ) . '</div>';
	}

	private static function an( int $lo, int $trang = 1 ): string {
		return '<input type="hidden" name="action" value="phongdao_lo_lam"><input type="hidden" name="lo" value="' . $lo . '"><input type="hidden" name="trang" value="' . $trang . '">' . wp_nonce_field( 'phongdao_lo_' . $lo, '_wpnonce', false, false );
	}

	private static function la_bai( array $lo ): bool {
		return 'post' === ( $lo['noi_dung']['doi_tuong'] ?? 'viec' );
	}

	private static function mo_ta_tham_so( array $ts ): string {
		return empty( $ts['tax'] ) ? '' : ( \Phongdao\HaTang\KhaiBao::PHAN_LOAI[ $ts['tax'] ][1] ?? $ts['tax'] ) . ': ' . ( $ts['ten'] ?? '' );
	}

	/** Tên tác phẩm (liên kết Sửa bài) của một trang mục. @return array<int,\WP_Post> */
	private static function bai_trang( array $ids ): array {
		if ( ! $ids ) {
			return array();
		}
		_prime_post_caches( $ids, true, false );
		$ra = array();
		foreach ( $ids as $i ) {
			$p = get_post( (int) $i );
			if ( $p ) {
				$ra[ (int) $i ] = $p;
			}
		}
		return $ra;
	}

	private static function lien_bai( int $id, ?\WP_Post $p ): string {
		$ten = $p ? ( '' !== trim( $p->post_title ) ? $p->post_title : '(Chưa đặt tên)' ) : 'Bài #' . $id;
		return $p && 'trash' !== $p->post_status && current_user_can( 'edit_post', $id ) ? '<a href="' . esc_url( SuaTacPham::url( $id ) ) . '">' . esc_html( $ten ) . '</a> <span class="description">#' . $id . '</span>' : esc_html( $ten ) . ' <span class="description">#' . $id . '</span>';
	}

	/** Số việc còn mở và số tuyển tập chứa từng bài (ẩn / thùng rác cần thấy trước). @return array{0:array<int,int>,1:array<int,int>} */
	private static function lien_quan( array $ids ): array {
		global $wpdb;
		if ( ! $ids ) {
			return array( array(), array() );
		}
		$dau  = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$viec = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT doi_tuong_id, COUNT(*) AS so FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'post' AND trang_thai IN ('cho','de_sau') AND doi_tuong_id IN ($dau) GROUP BY doi_tuong_id", $ids ), ARRAY_A ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$viec[ (int) $h['doi_tuong_id'] ] = (int) $h['so'];
		}
		$tt = array();
		foreach ( get_posts( array( 'post_type' => \Phongdao\HaTang\KhaiBao::TUYEN_TAP, 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => 500 ) ) as $p ) {
			foreach ( array_unique( array_map( 'intval', array_column( \Phongdao\KhoLuu\PhanBai::lay( $p->ID, 'muc' )['muc'] ?? array(), 'tac_pham_id' ) ) ) as $b ) {
				$tt[ $b ] = ( $tt[ $b ] ?? 0 ) + 1;
			}
		}
		return array( $viec, $tt );
	}

	/** Xem trước lô trên tác phẩm: trạng thái, phân loại hiện có / sau khi áp dụng, việc mở và tuyển tập (ẩn, thùng rác). */
	private static function xem_truoc_bai( array $lo ): string {
		$id    = (int) $lo['id'];
		$nd    = $lo['noi_dung'];
		$ts    = (array) ( $nd['tham_so'] ?? array() );
		$pl    = Lo::la_phan_loai( (string) $nd['thao_tac'] );
		$muc   = (array) $nd['muc'];
		$bo    = array_flip( array_map( 'intval', (array) $nd['bo'] ) );
		$trang = max( 1, (int) ( $_GET['trang'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$so    = (int) ceil( count( $muc ) / self::MOI_TRANG );
		$trang = min( $trang, max( 1, $so ) );
		$tren  = array_slice( $muc, ( $trang - 1 ) * self::MOI_TRANG, self::MOI_TRANG );
		$ids   = array_map( static fn( array $m ): int => (int) $m[0], $tren );
		$bai   = self::bai_trang( $ids );
		[ $viec, $tt ] = $pl ? array( array(), array() ) : self::lien_quan( $ids );
		$ten_tt = array( 'publish' => 'Đã đăng', 'draft' => 'Bản nháp', 'pending' => 'Chờ', 'private' => 'Đã ẩn', 'future' => 'Đã hẹn', 'trash' => 'Thùng rác' );
		$dong  = '';
		foreach ( $tren as [ $bid, $ban ] ) {
			$bid = (int) $bid;
			$p   = $bai[ $bid ] ?? null;
			$o   = '';
			if ( $pl ) {
				$hien = $p ? ( \Phongdao\DichVu\LuuPhan\LuuPhanLoai::dang_gan( $bid, (string) $ts['tax'] ) ?? array() ) : array();
				$ten  = static fn( array $ids_t ): string => implode( ', ', array_filter( array_map( static fn( int $t ): string => ( $x = get_term( $t ) ) instanceof \WP_Term ? $x->name : '', $ids_t ) ) ) ?: '(chưa có)';
				$doi  = (string) wp_json_encode( $hien ) !== (string) $ban ? ' (đã đổi sau lúc xem trước — sẽ báo xung đột)' : '';
				$o    = '<td data-nhan="Đang có">' . esc_html( $ten( $hien ) . $doi ) . '</td>';
			} else {
				$o = '<td data-nhan="Việc mở">' . (int) ( $viec[ $bid ] ?? 0 ) . '</td><td data-nhan="Trong tuyển tập">' . (int) ( $tt[ $bid ] ?? 0 ) . '</td>';
			}
			$trang_thai = $p ? ( $ten_tt[ $p->post_status ] ?? $p->post_status ) . ( ! $pl && $p->post_status !== $ban ? ' (đã đổi sau lúc xem trước)' : '' ) : 'Không còn bài này';
			$dong .= '<tr><td data-nhan="Áp dụng"><input type="hidden" name="tren_trang[]" value="' . $bid . '"><input type="checkbox" name="giu[]" value="' . $bid . '" id="lo-giu-' . $bid . '"' . checked( ! isset( $bo[ $bid ] ), true, false ) . '><label for="lo-giu-' . $bid . '" class="screen-reader-text">Áp dụng cho bài #' . $bid . '</label></td>'
				. '<td data-nhan="Tác phẩm">' . self::lien_bai( $bid, $p ) . '</td><td data-nhan="Trạng thái">' . esc_html( $trang_thai ) . '</td>' . $o . '</tr>';
		}
		$dau = $pl ? '<th scope="col">Đang có (' . esc_html( \Phongdao\HaTang\KhaiBao::PHAN_LOAI[ $ts['tax'] ][1] ?? '' ) . ')</th>' : '<th scope="col">Việc mở</th><th scope="col">Trong tuyển tập</th>';
		$ap  = count( $muc ) - count( $bo );
		$giai = $pl ? 'Mỗi bài ghi một lần sửa phân loại vào Lịch sử (khôi phục được). Không tạo mục mới.' : ( 'thung_rac' === $nd['thao_tac'] ? 'Bài vào thùng rác thì người đọc không thấy; lấy ra được bằng lô "Lấy ra khỏi thùng rác". Không xoá vĩnh viễn.' : 'Mỗi lần đổi trạng thái có dấu vết trong Lịch sử của bài.' );
		return '<p class="description">Chưa có gì thay đổi. ' . esc_html( $giai ) . ' Bỏ dấu ở bài không muốn áp dụng; bài đổi sau lúc xem trước sẽ được bỏ qua và báo xung đột.</p>'
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . self::an( $id, $trang )
			. '<table class="widefat striped"><thead><tr><th scope="col">Áp dụng</th><th scope="col">Tác phẩm</th><th scope="col">Trạng thái</th>' . $dau . '</tr></thead><tbody>' . $dong . '</tbody></table>'
			. '<p class="phongdao-lo-nut"><button type="submit" name="lam" value="ap_dung" class="button button-primary">Áp dụng cho ' . $ap . ' mục (tính cả bỏ chọn trên trang này)</button> <button type="submit" name="lam" value="luu_bo" class="button">Lưu bỏ chọn trên trang này</button> <button type="submit" name="lam" value="huy" class="button">Huỷ lô</button></p>'
			. '<p><label><input type="checkbox" name="tu_chay" value="1" checked> Tự chạy tiếp đến hết (mỗi lượt ' . Lo::MOI_LUOT . ' mục; bỏ chọn thì bấm Tiếp tục từng lượt)</label></p></form>'
			. self::phan_trang( $id, $trang, $so );
	}

	/** Khối "Làm theo lô" trên danh sách Tác phẩm. */
	public static function khoi_chon_bai( array $loc, int $tong ): string {
		$tt = '';
		foreach ( Lo::THAO_TAC as $ma => [ $ten, $dt, $quyen ] ) {
			if ( 'post' === $dt && current_user_can( $quyen ) ) {
				$tt .= '<option value="' . esc_attr( $ma ) . '">' . esc_html( $ten ) . '</option>';
			}
		}
		$muc = '<option value="">Chọn mục (cho thêm / gỡ / thay thế)</option>';
		foreach ( array_keys( \Phongdao\DichVu\LuuPhan\LuuPhanLoai::SUA_DUOC ) as $tax ) {
			$ds = \Phongdao\DichVu\LuuPhan\LuuPhanLoai::lua_chon( $tax );
			if ( ! $ds ) {
				continue;
			}
			$muc .= '<optgroup label="' . esc_attr( \Phongdao\HaTang\KhaiBao::PHAN_LOAI[ $tax ][1] ) . '">';
			foreach ( $ds as $t ) {
				$muc .= '<option value="' . esc_attr( $tax . ':' . $t->term_id ) . '">' . esc_html( $t->name ) . '</option>';
			}
			$muc .= '</optgroup>';
		}
		$an = '';
		foreach ( $loc as $k => $v ) {
			if ( 'paged' !== $k ) {
				$an .= '<input type="hidden" name="loc_' . esc_attr( (string) $k ) . '" value="' . esc_attr( (string) $v ) . '">';
			}
		}
		return '<fieldset class="phongdao-lo-chon"><legend>Làm theo lô</legend>'
			. '<input type="hidden" name="action" value="phongdao_lo_tao_bai">' . wp_nonce_field( 'phongdao_lo_tao_bai', '_wpnonce', false, false ) . $an
			. '<div class="phongdao-lo-o"><label for="phongdao-lo-tp-thao-tac">Thao tác</label><select id="phongdao-lo-tp-thao-tac" name="thao_tac"><option value="">Chọn thao tác</option>' . $tt . '</select></div>'
			. '<div class="phongdao-lo-o"><label for="phongdao-lo-tp-muc">Mục phân loại có sẵn</label><select id="phongdao-lo-tp-muc" name="muc_pl">' . $muc . '</select></div>'
			. '<fieldset class="phongdao-lo-pham-vi"><legend>Phạm vi</legend>'
			. '<label><input type="radio" name="pham_vi" value="chon" checked> Các bài đã chọn trên trang này</label>'
			. '<label><input type="radio" name="pham_vi" value="loc"> Mọi bài khớp bộ lọc (' . (int) $tong . ' bài)</label></fieldset>'
			. '<button type="submit" class="button">Xem trước</button>'
			. '<p class="description">Bước xem trước chốt danh sách và cho bỏ chọn ngoại lệ; chưa có gì thay đổi cho tới khi bấm Áp dụng. Không có xoá vĩnh viễn.</p></fieldset>';
	}

	/** Tạo lô từ danh sách Tác phẩm. */
	public static function tao_bai(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền tạo lô.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_lo_tao_bai' );
		$loc = array();
		foreach ( array( 'q', 'tt', 'the_loai', 'chu_de', 'muc', 'nguon' ) as $k ) {
			$loc[ $k ] = sanitize_text_field( wp_unslash( (string) ( $_POST[ 'loc_' . $k ] ?? '' ) ) );
		}
		$pham_vi  = 'loc' === ( $_POST['pham_vi'] ?? '' ) ? 'loc' : 'chon';
		$ids      = 'loc' === $pham_vi ? DanhSachTacPham::ids_loc( $loc, Lo::TOI_DA ) : array_map( 'absint', (array) wp_unslash( $_POST['ids'] ?? array() ) );
		$thao_tac = sanitize_key( (string) ( $_POST['thao_tac'] ?? '' ) );
		[ $tax, $term ] = array_pad( explode( ':', sanitize_text_field( wp_unslash( (string) ( $_POST['muc_pl'] ?? '' ) ) ), 2 ), 2, '' );
		$kq = 'post' !== ( Lo::THAO_TAC[ $thao_tac ][1] ?? '' ) ? array( 'ok' => false, 'lo' => 0, 'loi' => 'Chọn một thao tác.' ) : Lo::tao( $thao_tac, $ids, $pham_vi, $loc, get_current_user_id(), array( 'tax' => sanitize_key( $tax ), 'term' => (int) $term ) );
		if ( ! $kq['ok'] ) {
			self::bao( false, $kq['loi'] );
			wp_safe_redirect( add_query_arg( array_filter( array( 'page' => DanhSachTacPham::TRANG ) + $loc, static fn( $v ) => '' !== $v ), admin_url( 'admin.php' ) ) );
			exit;
		}
		wp_safe_redirect( self::url( $kq['lo'] ) );
		exit;
	}

	/** Thẻ của một trang mục (một truy vấn) và tên đối tượng. @return array{0:array<int,array>,1:array<string,string>} */
	private static function the_trang( array $ids ): array {
		$ds = array();
		foreach ( $ids as $i ) {
			$v = Viec::lay( (int) $i );
			if ( $v ) {
				$ds[ (int) $i ] = $v;
			}
		}
		return array( $ds, DanhSachViec::ten_trang( array_values( $ds ) ) );
	}

	private static function mo_ta( array $v ): string {
		if ( 'goi_y' === $v['loai'] ) {
			return GoiY::ten( (array) $v['noi_dung'] ) . ( empty( $v['noi_dung']['ly_do'] ) ? '' : ' — ' . $v['noi_dung']['ly_do'] );
		}
		return QuyetViec::LOAI[ $v['loai'] ][0] ?? $v['loai'];
	}

	private static function lien_the( int $id ): string {
		return '<a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => $id ), admin_url( 'admin.php' ) ) ) . '">Thẻ #' . $id . '</a>';
	}

	private static function phan_trang( int $lo, int $trang, int $so_trang, array $them = array() ): string {
		if ( $so_trang < 2 ) {
			return '';
		}
		return '<nav class="phongdao-tp-trang" aria-label="Phân trang lô"><span>Trang ' . $trang . ' / ' . $so_trang . '</span>'
			. ( $trang > 1 ? '<a class="button" href="' . esc_url( self::url( $lo, $them + array( 'trang' => $trang - 1 ) ) ) . '">Trang trước</a>' : '' )
			. ( $trang < $so_trang ? '<a class="button" href="' . esc_url( self::url( $lo, $them + array( 'trang' => $trang + 1 ) ) ) . '">Trang sau</a>' : '' ) . '</nav>';
	}

	/** Bước xem trước: mỗi mục một dòng, đánh dấu = áp dụng; bỏ dấu = ngoại lệ. */
	private static function xem_truoc( array $lo ): string {
		$id      = (int) $lo['id'];
		$muc     = (array) $lo['noi_dung']['muc'];
		$bo      = array_flip( array_map( 'intval', (array) $lo['noi_dung']['bo'] ) );
		$trang   = max( 1, (int) ( $_GET['trang'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$so      = (int) ceil( count( $muc ) / self::MOI_TRANG );
		$trang   = min( $trang, max( 1, $so ) );
		$tren    = array_slice( $muc, ( $trang - 1 ) * self::MOI_TRANG, self::MOI_TRANG );
		[ $the, $ten ] = self::the_trang( array_map( static fn( array $m ): int => (int) $m[0], $tren ) );
		$dong = '';
		foreach ( $tren as [ $vid, $p ] ) {
			$vid = (int) $vid;
			$v   = $the[ $vid ] ?? null;
			$hien = ! $v ? 'Không còn thẻ này' : ( ! in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ? 'Đã được quyết (sẽ bỏ qua)' : ( (int) $v['phien'] !== (int) $p ? 'Đã đổi sau lúc xem trước (sẽ báo xung đột)' : ( Viec::DE_SAU === $v['trang_thai'] ? 'Để sau' : 'Chờ' ) ) );
			$dong .= '<tr><td data-nhan="Áp dụng"><input type="hidden" name="tren_trang[]" value="' . $vid . '"><input type="checkbox" name="giu[]" value="' . $vid . '" id="lo-giu-' . $vid . '"' . checked( ! isset( $bo[ $vid ] ), true, false ) . '><label for="lo-giu-' . $vid . '" class="screen-reader-text">Áp dụng cho thẻ #' . $vid . '</label></td>'
				. '<td data-nhan="Thẻ">' . self::lien_the( $vid ) . '</td><td data-nhan="Bài / câu">' . esc_html( $v ? DanhSachViec::doi_tuong( $v, $ten ) : '' ) . '</td><td data-nhan="Nội dung">' . esc_html( $v ? self::mo_ta( $v ) : '' ) . '</td><td data-nhan="Lúc này">' . esc_html( $hien ) . '</td></tr>';
		}
		$ap = count( $muc ) - count( $bo );
		return '<p class="description">Chưa có gì thay đổi. Bỏ dấu ở mục không muốn áp dụng (ngoại lệ), bấm "Lưu bỏ chọn" nếu còn trang khác cần xem, rồi bấm Áp dụng. Mục đổi sau lúc xem trước sẽ được bỏ qua và báo xung đột, không ghi đè.</p>'
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . self::an( $id, $trang )
			. '<table class="widefat striped"><thead><tr><th scope="col">Áp dụng</th><th scope="col">Thẻ</th><th scope="col">Bài / câu</th><th scope="col">Nội dung</th><th scope="col">Lúc này</th></tr></thead><tbody>' . $dong . '</tbody></table>'
			. '<p class="phongdao-lo-nut"><button type="submit" name="lam" value="ap_dung" class="button button-primary">Áp dụng cho ' . $ap . ' mục (tính cả bỏ chọn trên trang này)</button> <button type="submit" name="lam" value="luu_bo" class="button">Lưu bỏ chọn trên trang này</button> <button type="submit" name="lam" value="huy" class="button">Huỷ lô</button></p>'
			. '<p><label><input type="checkbox" name="tu_chay" value="1" checked> Tự chạy tiếp đến hết (mỗi lượt ' . Lo::MOI_LUOT . ' mục; bỏ chọn thì bấm Tiếp tục từng lượt)</label></p></form>'
			. self::phan_trang( $id, $trang, $so );
	}

	/** Kết quả từng mục (lọc theo loại kết quả). */
	private static function ket_qua( array $lo ): string {
		$id  = (int) $lo['id'];
		$kq  = (array) ( $lo['sau']['ket_qua'] ?? array() );
		$loc = sanitize_key( (string) ( $_GET['kq'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$loc = isset( Lo::KET_QUA[ $loc ] ) ? $loc : '';
		$ds  = array();
		foreach ( $kq as $vid => $k ) {
			if ( '' === $loc || $loc === ( $k[0] ?? '' ) ) {
				$ds[ (int) $vid ] = $k;
			}
		}
		$loc_ht = '<p class="phongdao-lo-loc">Xem: <a href="' . esc_url( self::url( $id ) ) . '"' . ( '' === $loc ? ' aria-current="true"' : '' ) . '>Tất cả</a>';
		foreach ( Lo::KET_QUA as $ma => $ten ) {
			$loc_ht .= ' · <a href="' . esc_url( self::url( $id, array( 'kq' => $ma ) ) ) . '"' . ( $ma === $loc ? ' aria-current="true"' : '' ) . '>' . esc_html( $ten ) . '</a>';
		}
		$loc_ht .= '</p>';
		$trang = max( 1, (int) ( $_GET['trang'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$so    = (int) ceil( count( $ds ) / self::MOI_TRANG );
		$tren  = array_slice( $ds, ( min( $trang, max( 1, $so ) ) - 1 ) * self::MOI_TRANG, self::MOI_TRANG, true );
		$la_bai = self::la_bai( $lo );
		[ $the, $ten ] = $la_bai ? array( array(), array() ) : self::the_trang( array_keys( $tren ) );
		$bai   = $la_bai ? self::bai_trang( array_keys( $tren ) ) : array();
		$dong  = '';
		foreach ( $tren as $vid => $k ) {
			$v     = $the[ $vid ] ?? null;
			$dau   = $la_bai ? '<td data-nhan="Tác phẩm">' . self::lien_bai( (int) $vid, $bai[ $vid ] ?? null ) . '</td>' : '<td data-nhan="Thẻ">' . self::lien_the( $vid ) . '</td><td data-nhan="Bài / câu">' . esc_html( $v ? DanhSachViec::doi_tuong( $v, $ten ) : '' ) . '</td>';
			$dong .= '<tr>' . $dau . '<td data-nhan="Kết quả"><strong>' . esc_html( Lo::KET_QUA[ $k[0] ?? '' ] ?? (string) ( $k[0] ?? '' ) ) . '</strong></td><td data-nhan="Chi tiết">' . esc_html( (string) ( $k[1] ?? '' ) ) . '</td></tr>';
		}
		$cot = $la_bai ? '<th scope="col">Tác phẩm</th>' : '<th scope="col">Thẻ</th><th scope="col">Bài / câu</th>';
		return $loc_ht . '<table class="widefat striped"><thead><tr>' . $cot . '<th scope="col">Kết quả</th><th scope="col">Chi tiết</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="' . ( $la_bai ? 3 : 4 ) . '">Chưa có mục nào ở đây.</td></tr>' ) . '</tbody></table>' . self::phan_trang( $id, $trang, $so, '' === $loc ? array() : array( 'kq' => $loc ) );
	}

	/** Các lô gần đây của người đang xem. */
	private static function ds(): string {
		$dong = '';
		foreach ( Viec::ds_lo( get_current_user_id(), 20 ) as $lo ) {
			$nd    = $lo['noi_dung'];
			$t     = strtotime( (string) $lo['tao_luc'] . ' UTC' );
			$dong .= '<tr><td data-nhan="Lô"><a href="' . esc_url( self::url( (int) $lo['id'] ) ) . '">Lô #' . (int) $lo['id'] . '</a></td><td data-nhan="Thao tác">' . esc_html( Lo::THAO_TAC[ $nd['thao_tac'] ?? '' ][0] ?? (string) ( $nd['thao_tac'] ?? '' ) ) . '</td>'
				. '<td data-nhan="Trạng thái">' . esc_html( self::TRANG_THAI[ $lo['trang_thai'] ] ?? $lo['trang_thai'] ) . '</td><td data-nhan="Đã xử lý">' . count( (array) ( $lo['sau']['ket_qua'] ?? array() ) ) . ' / ' . count( (array) ( $nd['muc'] ?? array() ) ) . '</td>'
				. '<td data-nhan="Tạo lúc">' . esc_html( $t ? wp_date( 'd/m/Y H:i', $t ) : '' ) . '</td></tr>';
		}
		return '<p class="description">Tạo lô từ Hộp việc: lọc thẻ cùng một loại, chọn thẻ (hoặc mọi thẻ khớp bộ lọc), chọn thao tác, bấm Xem trước. Lô đang chạy dở mở lại ở đây để tiếp tục.</p>'
			. '<table class="widefat striped"><thead><tr><th scope="col">Lô</th><th scope="col">Thao tác</th><th scope="col">Trạng thái</th><th scope="col">Đã xử lý</th><th scope="col">Tạo lúc</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="5">Bạn chưa tạo lô nào.</td></tr>' ) . '</tbody></table>';
	}
}
