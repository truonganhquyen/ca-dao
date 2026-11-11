<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\GiaoTiep\BienTap\HopViec;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/** Hộp việc wp-admin: cùng bảng và cùng form quyết với trang biên tập đang chạy. */
final class DanhSachViec {

	public const TRANG = 'phongdao-hop-viec';
	private const MOI_TRANG = 30;
	private const TRANG_THAI = array(
		'cho'     => 'Chờ',
		'de_sau'  => 'Để sau',
		'xong'    => 'Xong',
		'bo'      => 'Bỏ',
		'tat_ca' => 'Mọi trạng thái',
	);

	private static function loc(): array {
		$tt = sanitize_key( (string) ( $_GET['tt'] ?? Viec::CHO ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$loai = sanitize_key( (string) ( $_GET['loai'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		return array(
			'tt'    => isset( self::TRANG_THAI[ $tt ] ) ? $tt : Viec::CHO,
			'loai'  => isset( QuyetViec::LOAI[ $loai ] ) ? $loai : '',
			'q'     => sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ), // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
			'paged' => max( 1, (int) ( $_GET['paged'] ?? 1 ) ), // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
			'viec'  => max( 0, (int) ( $_GET['viec'] ?? 0 ) ), // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		);
	}

	private static function url( array $loc, int $trang = 1, int $viec = 0 ): string {
		return add_query_arg( array_filter( array( 'page' => self::TRANG, 'tt' => $loc['tt'], 'loai' => $loc['loai'], 'q' => $loc['q'], 'paged' => $trang, 'viec' => $viec ), static fn( $v ) => '' !== $v && 0 !== $v ), admin_url( 'admin.php' ) );
	}

	/** Tên của đúng các đối tượng trên trang; tối đa hai truy vấn thay vì từng dòng một. */
	public static function ten_trang( array $ds ): array {
		global $wpdb;
		$ids = array( 'post' => array(), 'kho' => array() );
		foreach ( $ds as $v ) {
			if ( isset( $ids[ $v['doi_tuong_loai'] ] ) ) {
				$ids[ $v['doi_tuong_loai'] ][] = (int) $v['doi_tuong_id'];
			}
		}
		$ten = array();
		foreach ( $ids as $loai => $so ) {
			$so = array_values( array_unique( $so ) );
			if ( ! $so ) {
				continue;
			}
			$dau = implode( ',', array_fill( 0, count( $so ), '%d' ) );
			if ( 'post' === $loai ) {
				$sql = $wpdb->prepare( 'SELECT ID, post_title FROM ' . $wpdb->posts . " WHERE ID IN ($dau)", $so );
				foreach ( $wpdb->get_results( $sql, ARRAY_A ) ?: array() as $hang ) {
					$ten[ 'post:' . $hang['ID'] ] = $hang['post_title'];
				}
			} else {
				$sql = $wpdb->prepare( 'SELECT id, tieu_de, loi FROM ' . Kho::bang() . " WHERE id IN ($dau)", $so );
				foreach ( $wpdb->get_results( $sql, ARRAY_A ) ?: array() as $hang ) {
					$ten[ 'kho:' . $hang['id'] ] = $hang['tieu_de'] ?: (string) strtok( $hang['loi'], "\n" );
				}
			}
		}
		return $ten;
	}

	public static function doi_tuong( array $v, array $ten ): string {
		$id = (int) $v['doi_tuong_id'];
		if ( 'post' === $v['doi_tuong_loai'] ) {
			return $ten[ 'post:' . $id ] ?? 'Bài #' . $id;
		}
		if ( 'kho' === $v['doi_tuong_loai'] ) {
			return $ten[ 'kho:' . $id ] ?? 'Câu kho #' . $id;
		}
		return ( array( 'comment' => 'Bình luận', 'term' => 'Mục từ' )[ $v['doi_tuong_loai'] ] ?? 'Đối tượng' ) . ' #' . $id;
	}

	private static function chi_tiet( array $loc ): string {
		$v = Viec::lay( $loc['viec'] );
		$ve = '<p><a href="' . esc_url( self::url( $loc ) ) . '">← Về danh sách việc</a></p>';
		if ( ! $v || Viec::SUA === $v['loai'] ) {
			return $ve . '<p role="status">Không tìm thấy thẻ việc này.</p>';
		}
		$tt = self::TRANG_THAI[ $v['trang_thai'] ] ?? $v['trang_thai'];
		$bao = '<p class="phongdao-viec-tt">Trạng thái: <strong>' . esc_html( $tt ) . '</strong></p>';
		$da = sanitize_text_field( wp_unslash( (string) ( $_GET['da'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- thông báo sau redirect
		if ( '' !== $da ) {
			$bao .= '<div class="notice notice-info"><p role="status">' . esc_html( $da ) . '</p></div>';
		}
		$hoan_id = (int) ( $_GET['hoan_viec'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ hiện form; máy chủ kiểm lại
		$hoan_phien = (int) ( $_GET['hoan_phien'] ?? -1 ); // phpcs:ignore WordPress.Security.NonceVerification -- máy chủ kiểm lại
		if ( $hoan_id === (int) $v['id'] && QuyetViec::co_the_hoan_tac( $v, get_current_user_id(), $hoan_phien ) ) {
			$bao .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . HopViec::HOAN_TAC . '"><input type="hidden" name="viec" value="' . (int) $v['id'] . '"><input type="hidden" name="phien" value="' . (int) $v['phien'] . '">'
				. wp_nonce_field( HopViec::HOAN_TAC . $v['id'], '_wpnonce', true, false ) . '<button class="button" type="submit">Hoàn tác trong 10 giây</button></form>';
		}
		$tiep = self::tiep( $loc, $v );
		if ( ! in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ) {
			$nguoi = (int) $v['nguoi_quyet'] ? get_userdata( (int) $v['nguoi_quyet'] ) : null;
			$luc   = '' !== (string) ( $v['quyet_luc'] ?? '' ) ? get_date_from_gmt( (string) $v['quyet_luc'], 'd/m/Y H:i' ) : '';
			$ai    = trim( ( $nguoi ? $nguoi->display_name : '' ) . ( '' !== $luc ? ' · ' . $luc : '' ), ' ·' );
			$quyet = '' !== $v['quyet'] ? '<p>Đã quyết: <strong>' . esc_html( self::nhan_quyet( $v ) ) . '</strong>' . ( '' !== $ai ? ' (' . esc_html( $ai ) . ')' : '' ) . '.</p>' : '<p>Thẻ đã đóng.</p>';
			return $ve . $bao . $quyet . self::lien_bai( $v ) . $tiep . HopViec::the( $v, false );
		}
		return $ve . $bao . HopViec::the( $v ) . self::lien_bai( $v ) . $tiep;
	}

	/** Nhãn quyết định cho người đọc (không in mã như "dang"). */
	private static function nhan_quyet( array $v ): string {
		$nut = QuyetViec::LOAI[ $v['loai'] ][1][ $v['quyet'] ][0] ?? '';
		if ( '' !== $nut ) {
			return $nut;
		}
		return array(
			'da_co'          => 'Bài đã có sẵn phân loại này',
			'da_duyet_mvp'   => 'Đã duyệt trên bản cũ',
			'hoan_tac'       => 'Hoàn tác',
			'da_gop'         => 'Đã gộp',
			'tach_duong_dan' => 'Cho đường dẫn riêng',
		)[ $v['quyet'] ] ?? $v['quyet'];
	}

	/** Bài của thẻ (thẻ trên bài, hoặc bài đã đăng từ câu kho): lối Sửa bài trong quản trị và Xem bài trên trang đọc. */
	private static function lien_bai( array $v ): string {
		$bai = 'post' === $v['doi_tuong_loai'] ? (int) $v['doi_tuong_id'] : 0;
		if ( 'kho' === $v['doi_tuong_loai'] ) {
			$bai = (int) ( Kho::lay( (int) $v['doi_tuong_id'] )?->post_id ?? 0 );
		}
		$p = $bai ? get_post( $bai ) : null;
		if ( ! $p || \Phongdao\HaTang\KhaiBao::TAC_PHAM !== $p->post_type || 'trash' === $p->post_status ) {
			return '';
		}
		$lien = array();
		if ( current_user_can( 'edit_post', $bai ) ) {
			$lien[] = '<a class="button" href="' . esc_url( SuaTacPham::url( $bai ) ) . '">Sửa bài</a>';
		}
		if ( 'publish' === $p->post_status ) {
			$lien[] = '<a class="button" href="' . esc_url( (string) get_permalink( $p ) ) . '">Xem bài trên trang đọc</a>';
		}
		return $lien ? '<p class="phongdao-viec-bai">Bài: <strong>' . esc_html( $p->post_title ) . '</strong> ' . implode( ' ', $lien ) . '</p>' : '';
	}

	/** Lối sang thẻ kế tiếp cùng bộ lọc (thẻ đang chờ hoặc để sau), để không phải về danh sách sau mỗi lần quyết. */
	private static function tiep( array $loc, array $v ): string {
		$tt  = Viec::DE_SAU === $loc['tt'] ? Viec::DE_SAU : Viec::CHO;
		$kq  = Viec::tim_quan_tri( $tt, $loc['loai'], $loc['q'], 1, 2 );
		$con = $kq['tong'] - ( $tt === $v['trang_thai'] && ( '' === $loc['loai'] || $loc['loai'] === $v['loai'] ) ? 1 : 0 );
		foreach ( $kq['ds'] as $n ) {
			if ( (int) $n['id'] !== (int) $v['id'] ) {
				return '<p class="phongdao-viec-tiep"><a class="button button-primary" href="' . esc_url( self::url( array( 'tt' => $tt ) + $loc, 1, (int) $n['id'] ) ) . '">Thẻ tiếp theo →</a> <span class="description">Còn ' . max( 0, $con ) . ' thẻ ' . esc_html( Viec::DE_SAU === $tt ? 'để sau' : 'đang chờ' ) . ( '' !== $loc['loai'] ? ' loại này' : '' ) . '.</span></p>';
			}
		}
		return '<p class="phongdao-viec-tiep" role="status">Hết thẻ ' . esc_html( Viec::DE_SAU === $tt ? 'để sau' : 'đang chờ' ) . ( '' !== $loc['loai'] ? ' loại này' : '' ) . '. <a href="' . esc_url( self::url( array( 'tt' => $tt, 'loai' => '' ) + $loc ) ) . '">Xem mọi loại việc</a></p>';
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem Hộp việc.</p></div>';
		}
		$loc = self::loc();
		if ( $loc['viec'] ) {
			return '<div class="wrap phongdao-admin-viec"><h1>Hộp việc</h1>' . self::chi_tiet( $loc ) . '</div>';
		}
		$kq = Viec::tim_quan_tri( $loc['tt'], $loc['loai'], $loc['q'], $loc['paged'], self::MOI_TRANG );
		$ten_trang = self::ten_trang( $kq['ds'] );
		$tt = '<label for="phongdao-viec-tt">Trạng thái</label><select id="phongdao-viec-tt" name="tt">';
		foreach ( self::TRANG_THAI as $ma => $ten ) {
			$tt .= '<option value="' . esc_attr( $ma ) . '"' . selected( $ma, $loc['tt'], false ) . '>' . esc_html( $ten ) . '</option>';
		}
		$tt .= '</select>';
		$loai = '<label for="phongdao-viec-loai">Loại việc</label><select id="phongdao-viec-loai" name="loai"><option value="">Mọi loại</option>';
		foreach ( QuyetViec::LOAI as $ma => $ten ) {
			$loai .= '<option value="' . esc_attr( $ma ) . '"' . selected( $ma, $loc['loai'], false ) . '>' . esc_html( $ten[0] ) . '</option>';
		}
		$loai .= '</select>';
		// Làm theo lô (T-171): chỉ khi xem thẻ còn mở (Chờ / Để sau)
		$lo   = in_array( $loc['tt'], array( Viec::CHO, Viec::DE_SAU ), true ) && $kq['tong'] > 0;
		$dong = '';
		foreach ( $kq['ds'] as $v ) {
			$id = (int) $v['id'];
			$ten = QuyetViec::LOAI[ $v['loai'] ][0] ?? $v['loai'];
			$ten = '<a href="' . esc_url( self::url( $loc, $loc['paged'], $id ) ) . '">' . esc_html( $ten ) . ' #' . $id . '</a>';
			$chon = $lo ? '<td data-nhan="Chọn"><input type="checkbox" name="ids[]" value="' . $id . '" id="chon-viec-' . $id . '"><label for="chon-viec-' . $id . '" class="screen-reader-text">Chọn thẻ #' . $id . '</label></td>' : '';
			$dong .= '<tr>' . $chon . '<td data-nhan="Việc">' . $ten . '</td><td data-nhan="Đối tượng">' . esc_html( self::doi_tuong( $v, $ten_trang ) ) . '</td><td data-nhan="Trạng thái">' . esc_html( self::TRANG_THAI[ $v['trang_thai'] ] ?? $v['trang_thai'] ) . '</td><td data-nhan="Tạo lúc">' . esc_html( get_date_from_gmt( (string) $v['tao_luc'], 'd/m/Y H:i' ) ) . '</td></tr>';
		}
		$so_trang = (int) ceil( $kq['tong'] / self::MOI_TRANG );
		$phan_trang = $so_trang > 1 ? '<nav class="phongdao-viec-trang" aria-label="Phân trang Hộp việc"><span>Trang ' . (int) $loc['paged'] . ' / ' . $so_trang . '</span>'
			. ( $loc['paged'] > 1 ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] - 1 ) ) . '">Trang trước</a>' : '' )
			. ( $loc['paged'] < $so_trang ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] + 1 ) ) . '">Trang sau</a>' : '' ) . '</nav>' : '';
		$bang = ( $lo ? TrangLo::chon_het( (int) $kq['tong'], 'thẻ' ) : '' ) . '<table class="widefat striped"><thead><tr>' . ( $lo ? '<th scope="col">Chọn</th>' : '' ) . '<th scope="col">Việc</th><th scope="col">Đối tượng</th><th scope="col">Trạng thái</th><th scope="col">Tạo lúc</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="4">Không có thẻ phù hợp. Thử đổi bộ lọc.</td></tr>' ) . '</tbody></table>';
		return '<div class="wrap phongdao-admin-viec"><h1>Hộp việc</h1><p class="description">Tìm lại thẻ đã quyết; mở thẻ Chờ hoặc Để sau để quyết bằng nghiệp vụ hiện có, hoặc chọn nhiều thẻ cùng loại để làm theo lô.</p>'
			. TrangLo::lay_bao()
			. '<form class="phongdao-viec-loc" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '"><div><label for="phongdao-viec-q">Tìm ID, bài hoặc nội dung</label><input id="phongdao-viec-q" name="q" type="search" value="' . esc_attr( $loc['q'] ) . '"></div><div>' . $tt . '</div><div>' . $loai . '</div><button class="button button-primary" type="submit">Tìm và lọc</button></form>'
			. '<p role="status"><strong>' . (int) $kq['tong'] . '</strong> thẻ khớp bộ lọc; hiện ' . count( $kq['ds'] ) . ' thẻ trên trang này.</p>'
			. ( $lo ? '<form method="post" class="phongdao-lo-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . TrangLo::khoi_chon( $loc, (int) $kq['tong'] ) . $bang . '</form>' : $bang )
			. $phan_trang . ( $lo ? TrangLo::thanh_noi( 'thẻ', TrangLo::nut_mo_lo() ) : '' ) . '</div>';
	}
}
