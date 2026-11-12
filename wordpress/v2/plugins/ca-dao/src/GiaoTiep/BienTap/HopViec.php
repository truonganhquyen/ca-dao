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
 * Thẻ Hộp việc (trước ở `/bien-tap/`, nay ở wp-admin — D-104; D-094, docs/28 mục 4.1; màn mẫu B01): mọi việc chờ một quyết định, ưu tiên cao trước, lọc theo
 * loại; "Duyệt từng thẻ" (một thẻ một màn). Nút quyết là form gửi `admin-post.php` (tắt JavaScript vẫn quyết được), có
 * nonce riêng cho từng việc. Lời trong kho chỉ hiện ở đây (trang phải đăng nhập), không ra trang người đọc.
 */
final class HopViec {

	public const HANH_DONG = 'phongdao_quyet';
	public const HOAN_TAC = 'phongdao_hoan_tac_quyet';

	/** Gắn xử lý form quyết (gọi từ Module). */
	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan_form' ) );
		add_action( 'admin_post_' . self::HOAN_TAC, array( self::class, 'nhan_hoan_tac' ) );
	}

	/**
	 * Đích "tự sang thẻ kế" do Hộp việc wp-admin gửi kèm form (T-188): chỉ nhận đường dẫn tới chính màn Hộp việc trong
	 * quản trị; còn lại bỏ qua (về trang vừa gửi như trước).
	 */
	private static function tiep(): string {
		$tiep = esc_url_raw( wp_unslash( (string) ( $_POST['tiep'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- nonce kiểm ở nơi gọi
		return '' !== $tiep && str_starts_with( $tiep, admin_url( 'admin.php?page=' . \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG ) ) ? $tiep : '';
	}

	public static function nhan_form(): void {
		$id    = (int) ( $_POST['viec'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dưới
		$quyet = sanitize_key( (string) ( $_POST['quyet'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! current_user_can( 'cadao_soan' ) || ! wp_verify_nonce( (string) ( $_POST['_wpnonce'] ?? '' ), self::HANH_DONG . $id ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			wp_die( esc_html__( 'Phiên làm việc đã hết hoặc không có quyền. Tải lại trang rồi thử lại.', 'ca-dao' ), '', array( 'response' => 403 ) );
		}
		$phien = isset( $_POST['phien'] ) && ctype_digit( (string) $_POST['phien'] ) ? (int) $_POST['phien'] : PHP_INT_MAX; // phpcs:ignore WordPress.Security.NonceVerification -- nonce đã kiểm trên
		$chu_sua = 'duyet' === $quyet && isset( $_POST['chu'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['chu'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- nonce đã kiểm trên
		$kq   = QuyetViec::quyet( $id, $quyet, get_current_user_id(), current_user_can( 'cadao_duyet' ), $phien, $chu_sua );
		$ve   = (string) wp_get_referer() ?: admin_url( 'admin.php?page=' . \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG );
		// Quyết được thì sang thẻ kế (Hộp việc wp-admin), kèm lối xem lại thẻ vừa quyết; quyết hỏng thì ở lại thẻ này
		$tiep = $kq['ok'] && ! $kq['chuyen'] ? self::tiep() : '';
		$dich = $kq['chuyen'] ?: remove_query_arg( array( 'da', 'hoan_viec', 'hoan_phien', 'vua', 'dang_bai' ), '' !== $tiep ? $tiep : $ve );
		if ( '' !== $tiep ) {
			$dich = add_query_arg( 'vua', $id, $dich );
		}
		$v_sau = $kq['ok'] ? Viec::lay( $id ) : null;
		$co_hoan = QuyetViec::co_the_hoan_tac( $v_sau, get_current_user_id(), (int) ( $v_sau['phien'] ?? -1 ) );
		if ( $co_hoan ) {
			$dich = add_query_arg( array( 'hoan_viec' => $id, 'hoan_phien' => $v_sau['phien'] ), $dich );
		}
		// Đăng câu mới: lời báo ghi tên bài, kèm lối Xem bài · Ẩn nếu nhầm (một bấm, có nhật ký) thay cho câu "không có Hoàn tác nhanh"
		$bai_dang = 0;
		if ( $kq['ok'] && 'dang' === $quyet && 'cau_moi' === ( $v_sau['loai'] ?? '' ) ) {
			$bai_dang = (int) ( Kho::lay( (int) $v_sau['doi_tuong_id'] )?->post_id ?? 0 );
		}
		if ( $bai_dang && 'publish' === get_post_status( $bai_dang ) ) {
			$thong_bao = 'Đã đăng “' . get_post_field( 'post_title', $bai_dang ) . '”.';
			$dich      = add_query_arg( 'dang_bai', $bai_dang, $dich );
		} else {
			$thong_bao = $kq['ok'] ? $kq['thong_bao'] . ( $co_hoan || $kq['chuyen'] ? '' : ' Quyết định này không có Hoàn tác nhanh.' ) : 'Chưa xong: ' . implode( ' ', $kq['loi'] );
		}
		wp_safe_redirect( add_query_arg( 'da', rawurlencode( $thong_bao ), $dich ) );
		exit;
	}

	/** Form Hoàn tác nhanh: quyền, nonce, người quyết, hạn 10 giây và phiên đều được kiểm lại trên máy chủ. */
	public static function nhan_hoan_tac(): void {
		$id = (int) ( $_POST['viec'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dưới
		if ( ! current_user_can( 'cadao_soan' ) || ! wp_verify_nonce( (string) ( $_POST['_wpnonce'] ?? '' ), self::HOAN_TAC . $id ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			wp_die( esc_html__( 'Phiên làm việc đã hết hoặc không có quyền. Tải lại trang rồi thử lại.', 'ca-dao' ), '', array( 'response' => 403 ) );
		}
		$phien = isset( $_POST['phien'] ) && ctype_digit( (string) $_POST['phien'] ) ? (int) $_POST['phien'] : -1; // phpcs:ignore WordPress.Security.NonceVerification -- nonce đã kiểm trên
		$kq = QuyetViec::hoan_tac( $id, $phien, get_current_user_id() );
		$ve = (string) wp_get_referer() ?: admin_url( 'admin.php?page=' . \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG );
		$tiep = self::tiep();
		$dich = remove_query_arg( array( 'da', 'hoan_viec', 'hoan_phien', 'vua', 'dang_bai' ), '' !== $tiep ? $tiep : $ve );
		wp_safe_redirect( add_query_arg( 'da', rawurlencode( $kq['ok'] ? $kq['thong_bao'] : 'Chưa xong: ' . implode( ' ', $kq['loi'] ) ), $dich ) );
		exit;
	}

	/**
	 * Một thẻ việc: đủ thông tin để quyết ngay trên thẻ.
	 *
	 * @param string $tiep  Đường dẫn thẻ kế (Hộp việc wp-admin): quyết xong thì sang đó.
	 * @param bool   $lo_mo Có lối "Mở bài →" ở cuối thẻ (Hộp việc wp-admin đã có "Sửa bài · Xem bài" thì tắt, không lặp).
	 */
	public static function the( array $v, bool $co_form = true, string $tiep = '', bool $lo_mo = true ): string {
		[ $ten, $nut ] = QuyetViec::LOAI[ $v['loai'] ] ?? array( $v['loai'], array() );
		$tieu_de = '';
		$than    = '';
		$ly_do   = '';
		$g       = 'ca-dao';
		$mo      = '';
		$an_ten  = false;
		if ( 'cau_moi' === $v['loai'] ) {
			$m = Kho::lay( (int) $v['doi_tuong_id'] );
			if ( $m ) {
				$d       = Kho::du_lieu( $m );
				$g       = TheLoai::TU_LOAI_GOC[ $m->loai ] ?? 'ca-dao';
				$tieu_de = $m->tieu_de ?: (string) strtok( $m->loi, "\n" );
				// Tên câu chính là dòng đầu: không in lại làm tiêu đề (vẫn giữ cho trình đọc màn hình)
				$an_ten  = trim( $tieu_de ) === trim( (string) strtok( $m->loi, "\n" ) );
				$than    = Ve::loi( array_slice( array_values( array_filter( explode( "\n", $m->loi ), 'strlen' ) ), 0, 8 ), 'ca-dao' === $g ? 'lucbat' : '', array(), array( 'nhom' => 'hv-' . $v['id'] ) )
					. '<p class="small muted">' . esc_html( TheLoai::DS[ $g ] . ' · ' . count( (array) ( $d['chu_thich'] ?? array() ) ) . ' chú thích · ' . count( (array) ( $d['di_ban'] ?? array() ) ) . ' dị bản' ) . '</p>'
					. ( empty( $d['co'] ) ? '' : '<p class="tv-co">Cờ của nguồn: ' . esc_html( implode( ', ', \Phongdao\GiaoTiep\QuanTri\DanhSachKho::ten_co( (array) $d['co'] ) ) ) . '</p>' );
				$ly_do = ( empty( $v['noi_dung']['chon_boi'] ) ? '' : 'người chọn từ Kho · ' ) . 'nguồn: ' . \Phongdao\DichVu\NhapNguon\NguonTuDong::ten( (string) $m->nguon_id );
				// Căn cứ trước khi quyết (T-179): trang gốc máy đã lấy — chỉ người biên soạn thấy thẻ này
				if ( '' !== (string) $m->url ) {
					$than .= '<p class="small"><a href="' . esc_url( (string) $m->url ) . '" rel="noreferrer nofollow">Xem trang gốc máy đã lấy</a></p>';
				}
			}
		} elseif ( 'nhap_y_nghia' === $v['loai'] ) {
			$bai = get_post( (int) $v['doi_tuong_id'] );
			if ( $bai ) {
				$tieu_de = $bai->post_title;
				$mo      = (string) get_permalink( $bai );
				$than    = Ve::loi( array_slice( PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array(), 0, 4 ), '', array(), array( 'nhom' => 'hv-' . $v['id'] ) )
					. '<div class="nhap-ai"><span class="tag">' . ( empty( $v['noi_dung']['ai'] ) ? 'Nháp' : 'AI' ) . ( in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ? ' · chưa duyệt' : '' ) . '</span>'
					// Người có quyền duyệt sửa vài chữ ngay trên thẻ rồi bấm Duyệt (ô nằm trong form quyết, gửi kèm nút Duyệt)
					. ( $co_form && current_user_can( 'cadao_duyet' ) && in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true )
						? '<label class="screen-reader-text" for="nhap-sua-' . (int) $v['id'] . '">Nháp ý nghĩa (sửa được trước khi duyệt)</label><textarea class="nhap-chu nhap-sua" id="nhap-sua-' . (int) $v['id'] . '" name="chu" form="hv-form-' . (int) $v['id'] . '" rows="4">' . esc_textarea( (string) ( $v['noi_dung']['chu'] ?? '' ) ) . '</textarea><span class="small muted">Sửa vài chữ nếu cần rồi bấm Duyệt; bản đã sửa vào bài kèm Lịch sử.</span>'
						: '<p class="nhap-chu">' . esc_html( (string) ( $v['noi_dung']['chu'] ?? '' ) ) . '</p>' )
						. '<p class="small muted">' . esc_html( 'Người viết: ' . self::nguoi_viet( $v ) . ( empty( $v['noi_dung']['ai'] ) ? '' : ' · có hỗ trợ AI' ) ) . '</p></div>';
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
			if ( 'cach_hieu' === $loai ) {
				// Cách hiểu không thay Ý nghĩa; muốn dùng làm gốc thì người biên soạn tự viết lại (máy không chép)
				$than .= '<p class="small muted">Hiện trên trang thì cách hiểu nằm ở tầng "Hiểu thêm", tách riêng với Ý nghĩa của ban biên tập. Muốn dùng làm gốc cho Ý nghĩa: tự viết lại'
					. ( $bai ? ' ở <a href="' . esc_url( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( $bai->ID ) ) . '">trang Sửa tác phẩm</a>' : '' ) . ', ghi "có tham khảo cách hiểu của bạn đọc ' . esc_html( $them[0] ?? '' ) . '".</p>';
			}
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
		// Thẻ đã đóng: không còn ghi "chờ duyệt / người đọc chưa thấy" (nội dung đã được quyết)
		if ( ! in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) && in_array( $v['loai'], array( 'nhap_y_nghia', 'nguoi_doc' ), true ) ) {
			$ly_do = '';
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
			. '<h3' . ( $an_ten ? ' class="screen-reader-text"' : '' ) . '>' . esc_html( $tieu_de ?: 'Việc #' . $v['id'] ) . '</h3>' . $than
			. ( $co_form ? '<form method="post" id="hv-form-' . (int) $v['id'] . '" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="tv-nut"><input type="hidden" name="action" value="' . self::HANH_DONG . '"><input type="hidden" name="viec" value="' . (int) $v['id'] . '"><input type="hidden" name="phien" value="' . (int) $v['phien'] . '">'
				. ( '' === $tiep ? '' : '<input type="hidden" name="tiep" value="' . esc_attr( $tiep ) . '">' )
				. wp_nonce_field( self::HANH_DONG . $v['id'], '_wpnonce', true, false ) . $form . '</form>' : '' )
			. ( '' === $mo || ! $lo_mo ? '' : '<a class="tv-mo" href="' . esc_url( $mo ) . '">Mở bài ' . Ve::icon( 'arrow' ) . '</a>' ) . '</article>';
	}

	/** Người viết một nháp: tên người trong site, hoặc "máy (agent soạn theo lô)" khi do lệnh nạp nháp tạo. */
	public static function nguoi_viet( array $v ): string {
		$tao = (string) ( $v['nguoi_tao'] ?? '' );
		if ( ctype_digit( $tao ) && (int) $tao > 0 ) {
			$u = get_userdata( (int) $tao );
			return $u ? $u->display_name : 'người dùng #' . $tao;
		}
		return 'may' === $tao ? 'máy (agent soạn theo lô)' : ( '' === $tao ? 'chưa rõ' : $tao );
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
