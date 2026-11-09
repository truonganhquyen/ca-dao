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
		foreach ( Lo::THAO_TAC as $ma => [ $ten, , $quyen ] ) {
			if ( current_user_can( $quyen ) ) {
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
		$kq       = '' === $thao_tac ? array( 'ok' => false, 'lo' => 0, 'loi' => 'Chọn một thao tác.' ) : Lo::tao( $thao_tac, $ids, $pham_vi, $loc, get_current_user_id() );
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
		$ten   = Lo::THAO_TAC[ $nd['thao_tac'] ][0] ?? $nd['thao_tac'];
		$loai  = QuyetViec::LOAI[ $nd['loai_viec'] ][0] ?? $nd['loai_viec'];
		$t     = strtotime( (string) $lo['tao_luc'] . ' UTC' );
		$h    .= '<p><a href="' . esc_url( self::url() ) . '">← Các lô của bạn</a></p>'
			. '<p>Lô #' . (int) $lo['id'] . ' · <strong>' . esc_html( $ten ) . '</strong> · ' . esc_html( $loai ) . ' · ' . $tong . ' thẻ · '
			. esc_html( 'loc' === $nd['pham_vi'] ? 'mọi thẻ khớp bộ lọc lúc chọn' : 'các thẻ đã chọn' ) . ' · tạo ' . esc_html( $t ? wp_date( 'd/m/Y H:i', $t ) : '' )
			. ' · <strong>' . esc_html( self::TRANG_THAI[ $lo['trang_thai'] ] ?? $lo['trang_thai'] ) . '</strong></p>';
		if ( Viec::LO_XEM === $lo['trang_thai'] ) {
			return $h . self::xem_truoc( $lo ) . '</div>';
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
		[ $the, $ten ] = self::the_trang( array_keys( $tren ) );
		$dong = '';
		foreach ( $tren as $vid => $k ) {
			$v     = $the[ $vid ] ?? null;
			$dong .= '<tr><td data-nhan="Thẻ">' . self::lien_the( $vid ) . '</td><td data-nhan="Bài / câu">' . esc_html( $v ? DanhSachViec::doi_tuong( $v, $ten ) : '' ) . '</td><td data-nhan="Kết quả"><strong>' . esc_html( Lo::KET_QUA[ $k[0] ?? '' ] ?? (string) ( $k[0] ?? '' ) ) . '</strong></td><td data-nhan="Chi tiết">' . esc_html( (string) ( $k[1] ?? '' ) ) . '</td></tr>';
		}
		return $loc_ht . '<table class="widefat striped"><thead><tr><th scope="col">Thẻ</th><th scope="col">Bài / câu</th><th scope="col">Kết quả</th><th scope="col">Chi tiết</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="4">Chưa có mục nào ở đây.</td></tr>' ) . '</tbody></table>' . self::phan_trang( $id, $trang, $so, '' === $loc ? array() : array( 'kq' => $loc ) );
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
