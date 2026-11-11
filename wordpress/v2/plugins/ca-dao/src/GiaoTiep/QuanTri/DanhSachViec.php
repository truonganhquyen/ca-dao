<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\GoiY\GoiY;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\DichVu\XuLyLo\Lo;
use Phongdao\GiaoTiep\BienTap\HopViec;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Hộp việc wp-admin kiểu hộp thư (T-188, D-100; docs/e2e-review/v2-quan-tri/BAO-CAO.md mục 7): tab trạng thái và loại việc
 * có số; bên trái danh sách thẻ, mỗi thẻ hai dòng nội dung; bên phải thẻ đang chọn với đủ căn cứ và nút quyết — cùng form,
 * nonce, phiên với trang biên tập (HopViec::the). Quyết xong tự sang thẻ kế, có Hoàn tác 10 giây và phím tắt J/K, 1/2/3.
 * Màn hẹp: chỉ danh sách; bấm một thẻ thì mở thẻ đó cả màn. Đánh dấu nhiều thẻ: thanh nổi làm nhanh vài thẻ (Để sau, Bỏ,
 * Đồng ý gợi ý — hộp xác nhận ngắn) hoặc Làm theo lô qua xem trước; Đăng nhiều câu luôn qua xem trước.
 */
final class DanhSachViec {

	public const TRANG = 'phongdao-hop-viec';
	private const MOI_TRANG = 30;
	/** id của form lô (thanh nổi gửi form này bằng thuộc tính `form`). */
	public const FORM_LO = 'phongdao-hv-lo';
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

	public static function url( array $loc, int $trang = 1, int $viec = 0 ): string {
		$loc += array( 'tt' => Viec::CHO, 'loai' => '', 'q' => '' );
		return add_query_arg( array_filter( array( 'page' => self::TRANG, 'tt' => $loc['tt'], 'loai' => $loc['loai'], 'q' => $loc['q'], 'paged' => $trang > 1 ? $trang : 0, 'viec' => $viec ), static fn( $v ) => '' !== $v && 0 !== $v ), admin_url( 'admin.php' ) );
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
					$ten[ 'kho:' . $hang['id'] ]     = $hang['tieu_de'] ?: (string) strtok( $hang['loi'], "\n" );
					$ten[ 'kho_loi:' . $hang['id'] ] = (string) $hang['loi'];
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

	/** Chữ ngắn gọn (một dòng, tối đa $so ký tự). */
	private static function gon( string $chu, int $so = 140 ): string {
		$chu = trim( (string) preg_replace( '/\s+/u', ' ', $chu ) );
		return mb_strlen( $chu ) > $so ? mb_substr( $chu, 0, $so - 1 ) . '…' : $chu;
	}

	/**
	 * Tên và hai dòng nội dung của từng thẻ trên trang (lời câu mới, "→ Chủ đề: …", nháp ý nghĩa, lời người đọc gửi…), đủ để
	 * biết thẻ nói gì mà không phải mở. Đọc theo lô: tên đã có từ ten_trang, bình luận một truy vấn.
	 *
	 * @return array<int,array{0:string,1:string}>
	 */
	private static function tom_trang( array $ds, array $ten ): array {
		$bl = array();
		$ma_bl = array();
		foreach ( $ds as $v ) {
			if ( 'comment' === $v['doi_tuong_loai'] ) {
				$ma_bl[] = (int) $v['doi_tuong_id'];
			}
		}
		if ( $ma_bl ) {
			foreach ( get_comments( array( 'comment__in' => $ma_bl, 'status' => 'all', 'update_comment_meta_cache' => false ) ) as $c ) {
				$bl[ (int) $c->comment_ID ] = (string) $c->comment_content;
			}
		}
		$ra = array();
		foreach ( $ds as $v ) {
			$n   = (array) $v['noi_dung'];
			$ten_dt = self::doi_tuong( $v, $ten );
			$id  = (int) $v['doi_tuong_id'];
			switch ( $v['loai'] ) {
				case 'cau_moi':
					$dong = array_values( array_filter( array_map( 'trim', explode( "\n", (string) ( $ten[ 'kho_loi:' . $id ] ?? '' ) ) ), 'strlen' ) );
					$ra[ (int) $v['id'] ] = array( $ten_dt, implode( ' / ', array_slice( $dong, 0, 2 ) ) );
					break;
				case 'nhap_y_nghia':
					$ra[ (int) $v['id'] ] = array( $ten_dt, ( empty( $n['ai'] ) ? 'Nháp: ' : 'Nháp có hỗ trợ AI: ' ) . self::gon( (string) ( $n['chu'] ?? '' ) ) );
					break;
				case 'goi_y':
					$ra[ (int) $v['id'] ] = array( $ten_dt, '→ ' . GoiY::ten( $n ) . ( empty( $n['ly_do'] ) ? '' : ' · ' . self::gon( (string) $n['ly_do'], 80 ) ) );
					break;
				case 'can_xem':
					$ra[ (int) $v['id'] ] = array( $ten_dt, self::gon( implode( ' · ', array_map( 'strval', (array) ( $n['ly_do'] ?? array() ) ) ) ) );
					break;
				case 'nguoi_doc':
					$chu = 'comment' === $v['doi_tuong_loai'] ? ( $bl[ $id ] ?? '' ) : (string) ( get_post( $id )->post_content ?? '' );
					$ra[ (int) $v['id'] ] = array( ( \Phongdao\MienDuLieu\GopNguoiDoc::LOAI[ (string) ( $n['loai'] ?? '' ) ][0] ?? 'Người đọc gửi' ) . ( empty( $n['bai'] ) ? '' : ' — ' . get_the_title( (int) $n['bai'] ) ), self::gon( wp_strip_all_tags( $chu ) ) );
					break;
				case 'co_the_trung':
					$ra[ (int) $v['id'] ] = array( self::ten_muc( (string) ( $n['a'] ?? '' ) ), 'Giống ' . (int) ( $n['giong'] ?? 0 ) . '% với: ' . self::ten_muc( (string) ( $n['b'] ?? '' ) ) );
					break;
				default:
					$ra[ (int) $v['id'] ] = array( $ten_dt, '' );
			}
		}
		return $ra;
	}

	/** Tên một mục so trùng 'bai:…' / 'kho:…'. */
	private static function ten_muc( string $muc ): string {
		if ( '' === $muc ) {
			return '';
		}
		[ $loai, $id ] = ChongTrung::tach_muc( $muc );
		if ( 'bai' === $loai ) {
			return get_the_title( $id ) ?: 'Bài #' . $id;
		}
		$m = Kho::lay( $id );
		return $m ? ( $m->tieu_de ?: (string) strtok( (string) $m->loi, "\n" ) ) : 'Câu kho #' . $id;
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

	/** Tab trạng thái (Chờ, Để sau có số) và tab loại việc có số của trạng thái đang xem. */
	private static function tab( array $loc ): string {
		$so = array( Viec::CHO => Viec::dem( Viec::CHO ), Viec::DE_SAU => Viec::dem( Viec::DE_SAU ) );
		$o  = '<nav class="phongdao-hv-tab" aria-label="Trạng thái thẻ">';
		foreach ( self::TRANG_THAI as $ma => $ten ) {
			$o .= '<a class="phongdao-chip" href="' . esc_url( self::url( array( 'tt' => $ma, 'loai' => '', 'q' => '' ) ) ) . '"' . ( $ma === $loc['tt'] ? ' aria-current="page"' : '' ) . '>' . esc_html( $ten ) . ( isset( $so[ $ma ] ) ? ' <span class="phongdao-chip-so">' . esc_html( number_format_i18n( $so[ $ma ] ) ) . '</span>' : '' ) . '</a>';
		}
		$o  .= '</nav>';
		$dem = 'tat_ca' === $loc['tt'] ? array() : array_intersect_key( Viec::dem_loai( $loc['tt'] ), QuyetViec::LOAI );
		$o  .= '<nav class="phongdao-hv-tab" aria-label="Loại việc"><a class="phongdao-chip" href="' . esc_url( self::url( array( 'loai' => '' ) + $loc ) ) . '"' . ( '' === $loc['loai'] ? ' aria-current="page"' : '' ) . '>Tất cả' . ( $dem ? ' <span class="phongdao-chip-so">' . esc_html( number_format_i18n( array_sum( $dem ) ) ) . '</span>' : '' ) . '</a>';
		foreach ( QuyetViec::LOAI as $ma => [ $ten ] ) {
			if ( 'tat_ca' !== $loc['tt'] && empty( $dem[ $ma ] ) && $ma !== $loc['loai'] ) {
				continue;
			}
			$o .= '<a class="phongdao-chip" href="' . esc_url( self::url( array( 'loai' => $ma ) + $loc ) ) . '"' . ( $ma === $loc['loai'] ? ' aria-current="page"' : '' ) . '>' . esc_html( $ten ) . ( isset( $dem[ $ma ] ) ? ' <span class="phongdao-chip-so">' . esc_html( number_format_i18n( (int) $dem[ $ma ] ) ) . '</span>' : '' ) . '</a>';
		}
		return $o . '</nav>';
	}

	/** Lời báo sau khi quyết (chuyển trang): kết quả, Hoàn tác 10 giây của thẻ vừa quyết, lối xem lại thẻ đó. */
	private static function bao_quyet( array $loc ): string {
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ hiện lời báo và form; máy chủ kiểm lại khi gửi
		$da    = sanitize_text_field( wp_unslash( (string) ( $_GET['da'] ?? '' ) ) );
		$vua   = (int) ( $_GET['vua'] ?? 0 );
		$hoan  = (int) ( $_GET['hoan_viec'] ?? 0 );
		$phien = (int) ( $_GET['hoan_phien'] ?? -1 );
		// phpcs:enable
		if ( '' === $da ) {
			return '';
		}
		$o = '<div class="notice notice-info phongdao-hv-bao"><p role="status">' . esc_html( $da ) . ( $vua ? ' <a href="' . esc_url( self::url( $loc, (int) $loc['paged'], $vua ) ) . '">Xem lại thẻ vừa quyết</a>' : '' ) . '</p>';
		$v = $hoan ? Viec::lay( $hoan ) : null;
		if ( $v && QuyetViec::co_the_hoan_tac( $v, get_current_user_id(), $phien ) ) {
			$o .= '<form method="post" data-hv-hoan data-han="' . ( strtotime( $v['quyet_luc'] . ' UTC' ) + 10 ) . '" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . HopViec::HOAN_TAC . '"><input type="hidden" name="viec" value="' . $hoan . '"><input type="hidden" name="phien" value="' . (int) $v['phien'] . '">'
				. '<input type="hidden" name="tiep" value="' . esc_attr( self::url( $loc, (int) $loc['paged'], $hoan ) ) . '">'
				. wp_nonce_field( HopViec::HOAN_TAC . $hoan, '_wpnonce', true, false ) . '<p><button class="button" type="submit">Hoàn tác trong 10 giây</button></p></form>';
		}
		return $o . '</div>';
	}

	/**
	 * Ô bên phải: thẻ đang chọn (hoặc thẻ đầu danh sách ở màn rộng). Thẻ còn mở có form quyết kèm đích "tự sang thẻ kế"
	 * (thẻ sau trong danh sách, không có thì thẻ trước, hết thì danh sách); thẻ đã đóng ghi quyết định, lối bài và thẻ tiếp.
	 *
	 * @param array[] $ds thẻ trên trang danh sách
	 */
	private static function o_the( ?array $v, array $loc, array $ds, int $tong, int $so_trang ): string {
		$ve = '<p class="phongdao-hv-ve"><a href="' . esc_url( self::url( $loc, (int) $loc['paged'] ) ) . '">← Về danh sách việc</a></p>';
		if ( ! $v ) {
			return $loc['viec'] ? $ve . '<p role="status">Không tìm thấy thẻ việc này.</p>' : '';
		}
		$tt  = self::TRANG_THAI[ $v['trang_thai'] ] ?? $v['trang_thai'];
		$bao = '<p class="phongdao-viec-tt">Trạng thái: <strong>' . esc_html( $tt ) . '</strong></p>';
		if ( ! in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ) {
			$nguoi = (int) $v['nguoi_quyet'] ? get_userdata( (int) $v['nguoi_quyet'] ) : null;
			$luc   = '' !== (string) ( $v['quyet_luc'] ?? '' ) ? get_date_from_gmt( (string) $v['quyet_luc'], 'd/m/Y H:i' ) : '';
			$ai    = trim( ( $nguoi ? $nguoi->display_name : '' ) . ( '' !== $luc ? ' · ' . $luc : '' ), ' ·' );
			$quyet = '' !== $v['quyet'] ? '<p>Đã quyết: <strong>' . esc_html( self::nhan_quyet( $v ) ) . '</strong>' . ( '' !== $ai ? ' (' . esc_html( $ai ) . ')' : '' ) . '.</p>' : '<p>Thẻ đã đóng.</p>';
			return $ve . $bao . $quyet . self::lien_bai( $v ) . self::tiep( $loc, $v ) . HopViec::the( $v, false );
		}
		$ma = array_map( static fn( array $x ): int => (int) $x['id'], $ds );
		$i  = array_search( (int) $v['id'], $ma, true );
		$truoc = false !== $i && $i > 0 ? $ma[ $i - 1 ] : 0;
		$sau   = false !== $i ? ( $ma[ $i + 1 ] ?? 0 ) : (int) ( array_values( array_diff( $ma, array( (int) $v['id'] ) ) )[0] ?? 0 );
		if ( false !== $i && ! $sau && (int) $loc['paged'] < $so_trang ) {
			$sau = (int) ( Viec::tim_quan_tri( $loc['tt'], $loc['loai'], $loc['q'], (int) $loc['paged'] + 1, self::MOI_TRANG )['ds'][0]['id'] ?? 0 );
		}
		// Quyết xong: sang thẻ sau; thẻ cuối thì thẻ trước; không còn thì về danh sách (báo hết thẻ)
		$dich = $sau ?: $truoc;
		$tiep = self::url( $loc, (int) $loc['paged'], $dich );
		$vi   = false !== $i ? ( (int) $loc['paged'] - 1 ) * self::MOI_TRANG + $i + 1 : 0;
		$di   = '<nav class="phongdao-hv-di" aria-label="Chuyển thẻ">'
			. ( $truoc ? '<a class="button" data-hv="truoc" href="' . esc_url( self::url( $loc, (int) $loc['paged'], $truoc ) ) . '">← Thẻ trước</a>' : '' )
			. ( $vi ? '<span class="description">Thẻ ' . $vi . ' / ' . $tong . '</span>' : '' )
			. ( $sau ? '<a class="button" data-hv="sau" href="' . esc_url( self::url( $loc, (int) $loc['paged'], $sau ) ) . '">Thẻ sau →</a>' : '' )
			. '</nav><p class="description" data-hv="goi-y" hidden>Phím tắt: J thẻ sau, K thẻ trước; 1, 2, 3 bấm nút quyết theo thứ tự. Quyết xong tự sang thẻ kế.</p>';
		return $ve . $bao . HopViec::the( $v, true, $tiep ) . self::lien_bai( $v ) . $di;
	}

	/**
	 * Nút của thanh nổi theo loại việc đang xem: làm nhanh vài thẻ đã đánh dấu (Để sau, Bỏ, Đồng ý gợi ý — làm ngay sau hộp
	 * xác nhận), Đăng câu mới qua xem trước, và Làm theo lô. Thẻ người đọc gửi không bỏ theo lô.
	 */
	private static function nut_nhanh( array $loc, int $tong ): string {
		if ( '' === $loc['loai'] ) {
			return '<span class="description">Chọn một loại việc ở trên để làm nhanh nhiều thẻ.</span>' . TrangLo::nut_mo_lo();
		}
		$co  = QuyetViec::LOAI[ $loc['loai'] ][1];
		$ten = mb_strtolower( QuyetViec::LOAI[ $loc['loai'] ][0] );
		$nut = '';
		$mot = static function ( string $thao_tac, string $chu, string $hoi, bool $chinh = false ) use ( $tong ): string {
			return '<button type="submit" form="' . self::FORM_LO . '" name="nhanh" value="' . esc_attr( $thao_tac ) . '" class="button' . ( $chinh ? ' button-primary' : '' ) . '" data-hoi="' . esc_attr( $hoi ) . '" data-tong="' . $tong . '">' . esc_html( $chu ) . '</button>';
		};
		if ( 'goi_y' === $loc['loai'] && current_user_can( Lo::THAO_TAC['goi_y_dong_y'][2] ) ) {
			$nut .= $mot( 'goi_y_dong_y', 'Đồng ý', 'Đồng ý %d gợi ý đã chọn? Làm ngay, không qua màn xem trước.', true );
		}
		if ( 'cau_moi' === $loc['loai'] && current_user_can( Lo::THAO_TAC['cau_moi_dang'][2] ) ) {
			$nut .= '<button type="submit" form="' . self::FORM_LO . '" name="nhanh" value="cau_moi_dang" class="button button-primary">Xem trước và đăng</button>';
		}
		if ( isset( $co['de_sau'] ) && Viec::DE_SAU !== $loc['tt'] ) {
			$nut .= $mot( 'viec_de_sau', 'Để sau', 'Để sau %d thẻ ' . $ten . ' đã chọn? Làm ngay; thẻ vẫn ở mục Để sau.' );
		}
		if ( isset( $co['bo'] ) && 'nguoi_doc' !== $loc['loai'] ) {
			$nut .= $mot( 'viec_bo', 'Bỏ', 'Bỏ %d thẻ ' . $ten . ' đã chọn? Làm ngay, không qua màn xem trước.' );
		}
		return $nut . TrangLo::nut_mo_lo();
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem Hộp việc.</p></div>';
		}
		$loc = self::loc();
		$kq  = Viec::tim_quan_tri( $loc['tt'], $loc['loai'], $loc['q'], $loc['paged'], self::MOI_TRANG );
		$so_trang = (int) ceil( $kq['tong'] / self::MOI_TRANG );
		$v   = $loc['viec'] ? Viec::lay( $loc['viec'] ) : null;
		if ( $v && in_array( $v['loai'], array( Viec::SUA, Viec::LO ), true ) ) {
			$v = null;
		}
		// Không chọn thẻ: màn rộng mở sẵn thẻ đầu danh sách (màn hẹp chỉ hiện danh sách)
		$tu_chon = ! $loc['viec'] && $kq['ds'] && in_array( $loc['tt'], array( Viec::CHO, Viec::DE_SAU ), true );
		$the     = $v ?: ( $tu_chon ? $kq['ds'][0] : null );
		$chon_id = $the ? (int) $the['id'] : 0;
		$ten_trang = self::ten_trang( $kq['ds'] );
		$tom       = self::tom_trang( $kq['ds'], $ten_trang );
		// Làm theo lô (T-171): chỉ khi xem thẻ còn mở (Chờ / Để sau)
		$lo   = in_array( $loc['tt'], array( Viec::CHO, Viec::DE_SAU ), true ) && $kq['tong'] > 0;
		$dong = '';
		foreach ( $kq['ds'] as $x ) {
			$id   = (int) $x['id'];
			$ten  = QuyetViec::LOAI[ $x['loai'] ][0] ?? $x['loai'];
			$dang = $id === $chon_id;
			$dong .= '<li class="phongdao-hv-dong' . ( $dang ? ' dang-chon' : '' ) . '">'
				. ( $lo ? '<input type="checkbox" name="ids[]" value="' . $id . '" id="chon-viec-' . $id . '"><label for="chon-viec-' . $id . '" class="screen-reader-text">Chọn thẻ #' . $id . '</label>' : '' )
				. '<a class="phongdao-hv-mo" href="' . esc_url( self::url( $loc, (int) $loc['paged'], $id ) ) . '"' . ( $dang ? ' aria-current="true"' : '' ) . '>'
				. '<span class="phongdao-hv-dau">' . esc_html( $ten ) . ' #' . $id . '</span>'
				. '<strong class="phongdao-hv-ten">' . esc_html( $tom[ $id ][0] ?? self::doi_tuong( $x, $ten_trang ) ) . '</strong>'
				. ( '' !== ( $tom[ $id ][1] ?? '' ) ? '<span class="phongdao-hv-tom">' . esc_html( $tom[ $id ][1] ) . '</span>' : '' )
				. '<span class="phongdao-hv-luc">' . esc_html( ( self::TRANG_THAI[ $x['trang_thai'] ] ?? $x['trang_thai'] ) . ' · ' . get_date_from_gmt( (string) $x['tao_luc'], 'd/m/Y H:i' ) ) . '</span></a></li>';
		}
		$phan_trang = $so_trang > 1 ? '<nav class="phongdao-viec-trang" aria-label="Phân trang Hộp việc"><span>Trang ' . (int) $loc['paged'] . ' / ' . $so_trang . '</span>'
			. ( $loc['paged'] > 1 ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] - 1 ) ) . '">Trang trước</a>' : '' )
			. ( $loc['paged'] < $so_trang ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] + 1 ) ) . '">Trang sau</a>' : '' ) . '</nav>' : '';
		$rong = Viec::CHO === $loc['tt'] && '' === $loc['loai'] && '' === $loc['q']
			? '<div class="phongdao-hv-rong" role="status"><h2>Hết việc rồi</h2><p>Không còn thẻ nào chờ. Có thể viết Ý nghĩa cho bài đang thiếu.</p><p><a class="button button-primary" href="' . esc_url( add_query_arg( array( 'page' => DanhSachTacPham::TRANG, 'muc' => 'thieu_y_nghia' ), admin_url( 'admin.php' ) ) ) . '">Bài thiếu Ý nghĩa</a></p></div>'
			: '<p class="phongdao-hv-rong" role="status">Không có thẻ phù hợp. Thử đổi bộ lọc.</p>';
		$ds   = ( $lo ? TrangLo::chon_het( (int) $kq['tong'], 'thẻ' ) : '' ) . ( $dong ? '<ul class="phongdao-hv-ds">' . $dong . '</ul>' : $rong );
		$tim  = '<form class="phongdao-hv-tim" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '"><input type="hidden" name="tt" value="' . esc_attr( $loc['tt'] ) . '"><input type="hidden" name="loai" value="' . esc_attr( $loc['loai'] ) . '">'
			. '<label for="phongdao-viec-q">Tìm thẻ</label><input id="phongdao-viec-q" name="q" type="search" value="' . esc_attr( $loc['q'] ) . '" placeholder="ID, tên bài hoặc chữ trong thẻ"><button class="button" type="submit">Tìm</button></form>';
		return '<div class="wrap phongdao-admin-viec"><h1>Hộp việc</h1>'
			. self::bao_quyet( $loc ) . TrangLo::lay_bao() . self::tab( $loc ) . $tim
			. '<p class="phongdao-hv-so" role="status"><strong>' . (int) $kq['tong'] . '</strong> thẻ khớp bộ lọc; hiện ' . count( $kq['ds'] ) . ' thẻ trên trang này.</p>'
			. '<div class="phongdao-hv' . ( $v ? ' co-chon' : '' ) . '"><div class="phongdao-hv-trai">'
			. ( $lo ? '<form method="post" id="' . self::FORM_LO . '" class="phongdao-lo-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . TrangLo::khoi_chon( $loc, (int) $kq['tong'] ) . $ds . '</form>' : $ds )
			. $phan_trang . '</div>'
			. '<div class="phongdao-hv-phai"' . ( $tu_chon ? ' data-tu-chon' : '' ) . '>' . self::o_the( $the, $loc, $kq['ds'], (int) $kq['tong'], $so_trang ) . '</div></div>'
			. ( $lo ? TrangLo::thanh_noi( 'thẻ', self::nut_nhanh( $loc, (int) $kq['tong'] ) ) : '' ) . '</div>';
	}
}
