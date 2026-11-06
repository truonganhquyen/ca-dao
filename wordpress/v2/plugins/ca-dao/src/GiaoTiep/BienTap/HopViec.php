<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\ToChoKhac;

defined( 'ABSPATH' ) || exit;

/**
 * Hộp việc `/bien-tap/` (D-094, docs/28 mục 4.1; màn mẫu B01): mọi việc chờ một quyết định, ưu tiên cao trước, lọc theo
 * loại; "Duyệt từng thẻ" (một thẻ một màn). Nút quyết là form gửi `admin-post.php` (tắt JavaScript vẫn quyết được), có
 * nonce riêng cho từng việc. Lời trong kho chỉ hiện ở đây (trang phải đăng nhập), không ra trang người đọc.
 */
final class HopViec {

	public const HANH_DONG = 'phongdao_quyet';

	/** Gắn xử lý form quyết (gọi từ Module). */
	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan_form' ) );
	}

	public static function nhan_form(): void {
		$id    = (int) ( $_POST['viec'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dưới
		$quyet = sanitize_key( (string) ( $_POST['quyet'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! current_user_can( 'cadao_soan' ) || ! wp_verify_nonce( (string) ( $_POST['_wpnonce'] ?? '' ), self::HANH_DONG . $id ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			wp_die( esc_html__( 'Phiên làm việc đã hết hoặc không có quyền. Tải lại trang rồi thử lại.', 'ca-dao' ), '', array( 'response' => 403 ) );
		}
		$phien = isset( $_POST['phien'] ) && ctype_digit( (string) $_POST['phien'] ) ? (int) $_POST['phien'] : PHP_INT_MAX; // phpcs:ignore WordPress.Security.NonceVerification -- nonce đã kiểm trên
		$kq   = QuyetViec::quyet( $id, $quyet, get_current_user_id(), current_user_can( 'cadao_duyet' ), $phien );
		$ve   = (string) wp_get_referer() ?: home_url( '/bien-tap/' );
		$dich = $kq['chuyen'] ?: remove_query_arg( 'da', $ve );
		wp_safe_redirect( add_query_arg( 'da', rawurlencode( $kq['ok'] ? $kq['thong_bao'] : 'Chưa xong: ' . implode( ' ', $kq['loi'] ) ), $dich ) );
		exit;
	}

	public static function hien(): string {
		$loai  = sanitize_key( (string) ( $_GET['loai'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$loai  = isset( QuyetViec::LOAI[ $loai ] ) ? $loai : '';
		$tt    = Viec::DE_SAU === (string) ( $_GET['tt'] ?? '' ) ? Viec::DE_SAU : Viec::CHO; // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$tung  = ! empty( $_GET['tung'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$da    = sanitize_text_field( wp_unslash( (string) ( $_GET['da'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$cho   = Viec::dem_cho();
		$hoan  = Viec::dem( Viec::DE_SAU );
		$dem   = Viec::dem_loai( Viec::CHO );
		$dem_tt = Viec::CHO === $tt ? $dem : Viec::dem_loai( Viec::DE_SAU );
		$tong  = Viec::dem( $tt, $loai );
		$bao   = '' === $da ? '' : '<p class="notice" role="status">' . esc_html( $da ) . '</p>';
		$dau   = '<div class="row between hv-dau"><div><p class="eyebrow">Biên tập</p><h1>Hộp việc <span class="dem-lon">' . $cho . '</span></h1></div>'
			. ( $tong ? '<a class="btn compact" href="' . esc_url( $tung ? remove_query_arg( array( 'tung', 'vi', 'da' ) ) : add_query_arg( 'tung', 1, remove_query_arg( array( 'trang', 'da' ) ) ) ) . '">' . ( $tung ? 'Xem danh sách' : 'Duyệt từng thẻ' ) . '</a>' : '' ) . '</div>';
		$trang_thai = '<nav class="loc-viec" aria-label="Trạng thái việc"><a class="chip" href="' . esc_url( remove_query_arg( array( 'tt', 'vi', 'trang', 'da' ) ) ) . '" aria-current="' . ( Viec::CHO === $tt ? 'true' : 'false' ) . '">Chờ <span class="dem">' . $cho . '</span></a>'
			. '<a class="chip" href="' . esc_url( add_query_arg( 'tt', Viec::DE_SAU, remove_query_arg( array( 'vi', 'trang', 'da' ) ) ) ) . '" aria-current="' . ( Viec::DE_SAU === $tt ? 'true' : 'false' ) . '">Để sau <span class="dem">' . $hoan . '</span></a></nav>';
		$tuan = '<p class="tuan-nay">' . Ve::icon( 'calendar' ) . '<span>Đang chờ: <strong>' . (int) ( $dem['cau_moi'] ?? 0 ) . '</strong> câu mới · <strong>' . (int) ( $dem['nhap_y_nghia'] ?? 0 ) . '</strong> nháp ý nghĩa · <strong>' . (int) ( $dem['co_the_trung'] ?? 0 ) . '</strong> có thể trùng · <a href="' . esc_url( home_url( '/bien-tap/so-lieu/' ) ) . '">Số liệu tuần</a></span></p>';
		$loc = '<a class="chip" href="' . esc_url( remove_query_arg( array( 'loai', 'vi', 'trang', 'da' ) ) ) . '" aria-current="' . ( '' === $loai ? 'true' : 'false' ) . '">Tất cả <span class="dem">' . array_sum( $dem_tt ) . '</span></a>';
		foreach ( QuyetViec::LOAI as $ma => [ $ten ] ) {
			if ( ! empty( $dem_tt[ $ma ] ) ) {
				$loc .= '<a class="chip" href="' . esc_url( add_query_arg( 'loai', $ma, remove_query_arg( array( 'vi', 'trang', 'da' ) ) ) ) . '" aria-current="' . ( $ma === $loai ? 'true' : 'false' ) . '">' . esc_html( $ten ) . ' <span class="dem">' . (int) $dem_tt[ $ma ] . '</span></a>';
			}
		}
		$khung = $dau . $bao . $trang_thai . $tuan . '<nav class="loc-viec" aria-label="Lọc việc">' . $loc . '</nav>';
		if ( ! $tong ) {
			return $khung . ( Viec::CHO === $tt && '' === $loai
				? '<div class="empty">' . Ve::icon( 'check' ) . '<h2>Hết việc rồi</h2><p class="muted">Không còn thẻ nào chờ. Có thể viết ý nghĩa cho bài đọc nhiều.</p><a class="btn primary" href="' . esc_url( home_url( '/bien-tap/tac-pham/?loc=thieu-y-nghia' ) ) . '">Bài thiếu ý nghĩa</a></div>'
				: '<div class="empty"><h2>Không có thẻ phù hợp</h2><p class="muted">Chọn trạng thái hoặc loại việc khác.</p></div>' );
		}
		if ( $tung ) {
			$i = max( 0, min( $tong - 1, (int) ( $_GET['vi'] ?? 0 ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$ds = Viec::trang( $tt, $loai, 1, $i );
			if ( ! $ds ) {
				return $khung . '<p class="notice" role="status">Danh sách vừa thay đổi. Tải lại trang để xem tiếp.</p>';
			}
			$nav = '<div class="row between">' . ( $i > 0 ? '<a class="btn compact ghost" href="' . esc_url( add_query_arg( 'vi', $i - 1 ) ) . '">Thẻ trước</a>' : '<span></span>' )
				. ( $i < $tong - 1 ? '<a class="btn compact" href="' . esc_url( add_query_arg( 'vi', $i + 1 ) ) . '">Thẻ sau</a>' : '<span></span>' ) . '</div>';
			return $khung . '<div class="tung-the"><div class="row between"><span class="small muted">Thẻ ' . ( $i + 1 ) . ' / ' . $tong . '</span></div>'
				. '<div class="progress"><span style="width:' . (int) round( 100 * ( $i + 1 ) / $tong ) . '%"></span></div>' . self::the( $ds[0] ) . $nav . '</div>';
		}
		$so_trang = (int) ceil( $tong / 30 );
		$trang    = max( 1, min( $so_trang, (int) ( $_GET['trang'] ?? 1 ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$ds       = Viec::trang( $tt, $loai, 30, ( $trang - 1 ) * 30 );
		$phan_trang = '<nav class="row between" aria-label="Trang Hộp việc">'
			. ( $trang > 1 ? '<a class="btn compact ghost" href="' . esc_url( add_query_arg( 'trang', $trang - 1, remove_query_arg( 'da' ) ) ) . '">Trang trước</a>' : '<span></span>' )
			. '<span class="small muted">Trang ' . $trang . ' / ' . $so_trang . '</span>'
			. ( $trang < $so_trang ? '<a class="btn compact" href="' . esc_url( add_query_arg( 'trang', $trang + 1, remove_query_arg( 'da' ) ) ) . '">Trang sau</a>' : '<span></span>' ) . '</nav>';
		return $khung . '<div class="ds-viec">' . implode( '', array_map( array( self::class, 'the' ), $ds ) ) . '</div>' . $phan_trang;
	}

	/** Một thẻ việc: đủ thông tin để quyết ngay trên thẻ. */
	public static function the( array $v ): string {
		[ $ten, $nut ] = QuyetViec::LOAI[ $v['loai'] ] ?? array( $v['loai'], array() );
		$tieu_de = '';
		$than    = '';
		$ly_do   = '';
		$g       = 'ca-dao';
		$mo      = '';
		if ( 'cau_moi' === $v['loai'] ) {
			$m = Kho::lay( (int) $v['doi_tuong_id'] );
			if ( $m ) {
				$d       = Kho::du_lieu( $m );
				$g       = TheLoai::TU_LOAI_GOC[ $m->loai ] ?? 'ca-dao';
				$tieu_de = $m->tieu_de ?: (string) strtok( $m->loi, "\n" );
				$than    = Ve::loi( array_slice( array_values( array_filter( explode( "\n", $m->loi ), 'strlen' ) ), 0, 8 ), 'ca-dao' === $g ? 'lucbat' : '', array(), array( 'nhom' => 'hv-' . $v['id'] ) )
					. '<p class="small muted">' . esc_html( TheLoai::DS[ $g ] . ' · ' . count( (array) ( $d['chu_thich'] ?? array() ) ) . ' chú thích · ' . count( (array) ( $d['di_ban'] ?? array() ) ) . ' dị bản' . ( empty( $d['co'] ) ? '' : ' · cờ: ' . implode( ', ', (array) $d['co'] ) ) ) . '</p>';
				$ly_do = 'nguồn: ' . $m->nguon_id;
			}
		} elseif ( 'nhap_y_nghia' === $v['loai'] ) {
			$bai = get_post( (int) $v['doi_tuong_id'] );
			if ( $bai ) {
				$tieu_de = $bai->post_title;
				$mo      = (string) get_permalink( $bai );
				$than    = Ve::loi( array_slice( PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array(), 0, 4 ), '', array(), array( 'nhom' => 'hv-' . $v['id'] ) )
					. '<div class="nhap-ai"><span class="tag">' . ( empty( $v['noi_dung']['ai'] ) ? 'Nháp · chưa duyệt' : 'AI · chưa duyệt' ) . '</span><p>' . esc_html( (string) ( $v['noi_dung']['chu'] ?? '' ) ) . '</p></div>';
				$ly_do   = 'người đọc chưa thấy';
			}
		} elseif ( 'nguoi_doc' === $v['loai'] ) {
			$nut  = QuyetViec::nut_nguoi_doc( $v );
			$loai = (string) ( $v['noi_dung']['loai'] ?? '' );
			$bai  = get_post( (int) ( $v['noi_dung']['bai'] ?? 0 ) );
			$mo   = $bai ? (string) get_permalink( $bai ) : '';
			if ( 'comment' === $v['doi_tuong_loai'] ) {
				$c    = get_comment( (int) $v['doi_tuong_id'] );
				$chu  = $c ? (string) $c->comment_content : '';
				$them = $c ? array( (string) $c->comment_author ) : array();
			} else {
				$p    = get_post( (int) $v['doi_tuong_id'] );
				$chu  = $p ? trim( wp_strip_all_tags( str_replace( array( '<br>', '</p>' ), array( "\n", "\n\n" ), (string) $p->post_content ) ) ) : '';
				$them = $p ? array_filter( array( $p->post_type === KhaiBao::PHAN_TICH ? $p->post_title : '', (string) get_post_meta( $p->ID, '_cadao_noi_nghe', true ), (string) get_post_meta( $p->ID, '_cadao_nguoi_ke', true ), (string) get_post_meta( $p->ID, '_cadao_ten', true ) ?: (string) get_post_meta( $p->ID, '_cadao_but_danh', true ) ) ) : array();
			}
			$tieu_de = ( \Phongdao\MienDuLieu\GopNguoiDoc::LOAI[ $loai ][0] ?? 'Người đọc gửi' ) . ( $bai ? ' — ' . $bai->post_title : '' );
			$than    = '<blockquote class="small">' . nl2br( esc_html( mb_strlen( $chu ) > 800 ? mb_substr( $chu, 0, 799 ) . '…' : $chu ) ) . '</blockquote>'
				. ( $them ? '<p class="small muted">' . esc_html( implode( ' · ', $them ) ) . '</p>' : '' );
			$ly_do   = 'chờ duyệt, người đọc chưa thấy';
		} elseif ( 'goi_y' === $v['loai'] ) {
			$bai = get_post( (int) $v['doi_tuong_id'] );
			if ( $bai ) {
				$tieu_de = \Phongdao\DichVu\GoiY\GoiY::ten( $v['noi_dung'] ) . ' — ' . $bai->post_title;
				$mo      = (string) get_permalink( $bai );
				$than    = Ve::loi( array_slice( PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array(), 0, 4 ), '', array(), array( 'nhom' => 'hv-' . $v['id'] ) );
				$ly_do   = 'máy gợi ý: ' . (string) ( $v['noi_dung']['ly_do'] ?? '' );
			}
		} elseif ( 'can_xem' === $v['loai'] ) {
			$bai = get_post( (int) $v['doi_tuong_id'] );
			if ( $bai ) {
				$tieu_de = $bai->post_title;
				$mo      = (string) get_permalink( $bai );
				$than    = '<ul class="small">' . implode( '', array_map( static fn( $l ) => '<li>' . esc_html( (string) $l ) . '</li>', (array) ( $v['noi_dung']['ly_do'] ?? array() ) ) ) . '</ul>'
					. '<p class="small muted">Bài đã chuyển sang với những phần chắc chắn. Mở bài, sửa phần cần sửa, rồi đánh dấu đã xem.</p>';
				$ly_do   = 'từ bản cũ';
			}
		} elseif ( 'co_the_trung' === $v['loai'] ) {
			$n     = $v['noi_dung'];
			$lua   = self::loi_muc( (string) $n['a'] );
			$lub   = self::loi_muc( (string) $n['b'] );
			$tieu_de = $lua[0];
			$cot   = static function ( array $l, array $so ): string {
				$h = Ve::loi( array_slice( $l[1], 0, 6 ), '', array(), array( 'nhom' => 'hv-tr' ) );
				foreach ( ToChoKhac::tu_khac( $so, $l[1] ) as $k ) {
					$h = str_replace( ' ' . esc_html( $k ), ' <ins>' . esc_html( $k ) . '</ins>', $h );
				}
				return '<div class="variant"><p class="small muted">' . esc_html( $l[2] ) . '</p>' . $h . '</div>';
			};
			$than  = '<div class="hai-cot">' . $cot( $lua, $lub[1] ) . $cot( $lub, $lua[1] ) . '</div>';
			$ly_do = 'giống ' . (int) ( $n['giong'] ?? 0 ) . '%' . ( empty( $n['ly_do'] ) ? '' : ' · ' . $n['ly_do'] );
			$mo    = $lub[3];
			$g     = 'tim';
		}
		$form = '';
		foreach ( $nut as $ma => [ $chu, $kieu ] ) {
			if ( Viec::DE_SAU === $v['trang_thai'] && 'de_sau' === $ma ) {
				continue;
			}
			if ( ( 'dang' === $ma || 'duyet' === $ma ) && ! current_user_can( 'cadao_duyet' ) ) {
				continue;
			}
			$form .= '<button type="submit" name="quyet" value="' . esc_attr( $ma ) . '" class="btn ' . esc_attr( $kieu ) . '">' . esc_html( $chu ) . '</button>';
		}
		return '<article class="the-viec g-' . esc_attr( $g ) . '" data-loai="' . esc_attr( $v['loai'] ) . '" id="viec-' . (int) $v['id'] . '">'
			. '<header class="tv-dau"><span class="tv-loai">' . esc_html( $ten ) . '</span><span class="small muted">' . esc_html( $ly_do ) . '</span></header>'
			. '<h3>' . esc_html( $tieu_de ?: 'Việc #' . $v['id'] ) . '</h3>' . $than
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="tv-nut"><input type="hidden" name="action" value="' . self::HANH_DONG . '"><input type="hidden" name="viec" value="' . (int) $v['id'] . '"><input type="hidden" name="phien" value="' . (int) $v['phien'] . '">'
			. wp_nonce_field( self::HANH_DONG . $v['id'], '_wpnonce', true, false ) . $form . '</form>'
			. ( '' === $mo ? '' : '<a class="tv-mo" href="' . esc_url( $mo ) . '">Mở bài ' . Ve::icon( 'arrow' ) . '</a>' ) . '</article>';
	}

	/** @return array{0:string,1:string[],2:string,3:string} [tên, các dòng, nhãn nơi ở, đường dẫn mở] của một mục 'bai:…' / 'kho:…' */
	private static function loi_muc( string $muc ): array {
		[ $loai, $id ] = ChongTrung::tach_muc( $muc );
		if ( 'bai' === $loai ) {
			$bai = get_post( $id );
			return array( $bai ? $bai->post_title : $muc, PhanBai::lay( $id, 'loi' )['dong'] ?? array(), 'Tác phẩm đã có', $bai ? (string) get_permalink( $bai ) : '' );
		}
		$m = Kho::lay( $id );
		return array( $m ? ( $m->tieu_de ?: (string) strtok( $m->loi, "\n" ) ) : $muc, $m ? explode( "\n", $m->loi ) : array(), 'Câu mới trong kho', '' );
	}
}
