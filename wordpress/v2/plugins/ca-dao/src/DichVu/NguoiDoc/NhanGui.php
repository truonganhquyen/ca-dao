<?php

namespace Phongdao\DichVu\NguoiDoc;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\DiaChiMang;
use Phongdao\MienDuLieu\GopNguoiDoc;

defined( 'ABSPATH' ) || exit;

/**
 * Nhận một lần người đọc gửi (docs/27 mục 9; chuyển từ MVP nguoi-doc.php, phan-tich.php). Mọi thứ vào chờ duyệt và
 * thành một thẻ "Người đọc gửi" trong Hộp việc; không gì tự hiện.
 *
 * Chống rác: ô bẫy ẩn; tối đa 5 lần gửi mỗi giờ (giữ dấu băm của địa chỉ mạng + giờ trong bộ nhớ tạm một giờ — không
 * lưu địa chỉ); Cloudflare Turnstile khi người quản trị đã dán khoá (tuỳ chọn cadao_turnstile_*, giữ tên MVP).
 * Cảm nghĩ lưu là bình luận nhưng không ghi địa chỉ mạng, trình duyệt, email (form bình luận mặc định của WordPress
 * có ghi địa chỉ mạng — không dùng).
 */
final class NhanGui {

	public const HANH_DONG = 'phongdao_gop';

	public const GUI_MOI_GIO = 5;

	/** Chỉ sống trong yêu cầu POST bị lỗi; không ghi nháp người đọc vào cookie, URL hay cơ sở dữ liệu. */
	private static ?array $loi_trang = null;

	public static function loi_trang(): ?array {
		return self::$loi_trang;
	}

	/** Trang Góp nhận POST tại chỗ: lỗi vẽ lại đúng chữ, kể cả khi tắt JavaScript. */
	public static function nhan_trang(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || 'gop' !== \Phongdao\GiaoTiep\TrangCoDinh\Module::dang_xem() ) {
			return;
		}
		$vao = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dưới
		$p   = array_filter( $vao, 'is_scalar' );
		if ( self::HANH_DONG !== ( $p['action'] ?? '' ) ) {
			return;
		}
		nocache_headers();
		header( 'Cache-Control: private, no-store, max-age=0' );
		// Không bỏ qua ô bẫy/token bị gửi sai dạng mảng rồi vô tình coi chúng như ô trống.
		$ma = count( $p ) !== count( $vao ) ? 'loi' : '';
		if ( '' === $ma && ! wp_verify_nonce( (string) ( $p['_wpnonce'] ?? '' ), self::HANH_DONG ) ) {
			$ma = 'cu';
		}
		$kq = '' === $ma ? self::luu( $p ) : array( 'ok' => false, 'ma' => $ma, 'bai' => 0 );
		if ( $kq['ok'] ) {
			$toi = $kq['bai'] ? add_query_arg( 'gop', 'da_gui', (string) get_permalink( $kq['bai'] ) ) . '#gop' : add_query_arg( 'gui', 'da_gui', home_url( '/gop/' ) );
			wp_safe_redirect( $toi, 303 );
			exit;
		}
		// Không giữ nonce / token chống rác; lần vẽ lại sinh nonce mới và yêu cầu kiểm chống rác lại.
		$giu = array_flip( array( 'loai', 'bai', 'vi_pham', 'noi_dung', 'sach', 'trang', 'tieu_de', 'vai', 'noi_nghe', 'nguoi_ke', 'ten', 'tu_viet', 'dong_y' ) );
		self::$loi_trang = array( 'ma' => $kq['ma'], 'p' => array_intersect_key( $p, $giu ) );
		status_header( 422 );
	}

	/** @return array{site:string,bi_mat:string} */
	public static function turnstile(): array {
		return array(
			'site'   => (string) get_option( 'cadao_turnstile_site', '' ),
			'bi_mat' => (string) get_option( 'cadao_turnstile_bi_mat', '' ),
		);
	}

	/**
	 * Kiểm và lưu. $p đã bỏ dấu \.
	 *
	 * @return array{ok:bool,ma:string,id:int,bai:int}
	 */
	public static function luu( array $p ): array {
		[ $ma, $d ] = GopNguoiDoc::kiem( $p );
		$bai_id     = (int) ( $d['bai'] ?? 0 );
		$hong       = static fn( string $ma ): array => array( 'ok' => false, 'ma' => $ma, 'id' => 0, 'bai' => $bai_id );
		if ( null !== $ma ) {
			return $hong( $ma );
		}
		$bai = $bai_id ? get_post( $bai_id ) : null;
		if ( $bai_id && ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'publish' !== $bai->post_status ) ) {
			return $hong( 'bai' );
		}
		if ( '' !== trim( (string) ( $p['web'] ?? '' ) ) ) {
			return $hong( 'bay' ); // ô bẫy có chữ: máy tự động
		}
		// Bấm gửi hai lần (mạng chậm) hoặc gửi lại form vừa gửi: nhận là đã gửi, không tạo bản thứ hai. Khoá chỉ là dấu băm của
		// chính nội dung gửi, giữ 10 phút; không lưu địa chỉ mạng hay gì của người gửi.
		$lap = 'phongdao_gop_lap_' . substr( wp_hash( (string) wp_json_encode( $d ) ), 0, 24 );
		$cu  = get_transient( $lap );
		if ( is_array( $cu ) && Viec::mo( (string) $cu['loai'], (int) $cu['id'], 'nguoi_doc' ) ) {
			return array( 'ok' => true, 'ma' => '', 'id' => (int) $cu['id'], 'bai' => $bai_id );
		}
		$chan = self::chong_rac( $p );
		if ( null !== $chan ) {
			return $hong( $chan );
		}
		$luu = self::ghi( $d, $bai );
		if ( ! $luu ) {
			return $hong( 'loi' );
		}
		set_transient( $lap, $luu, 10 * MINUTE_IN_SECONDS );
		return array( 'ok' => true, 'ma' => '', 'id' => $luu['id'], 'bai' => $bai_id );
	}

	/**
	 * Ghi nội dung và thẻ "Người đọc gửi" trong cùng một giao dịch: một bước lỗi thì không còn nội dung nào nằm ngoài
	 * Hộp việc (người đọc được báo chưa gửi được để gửi lại).
	 *
	 * @return array{id:int,loai:string}|null
	 */
	private static function ghi( array $d, ?\WP_Post $bai ): ?array {
		global $wpdb;
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return null;
		}
		$bai_id  = $bai ? $bai->ID : 0;
		$ten_bai = $bai ? $bai->post_title : 'không gắn bài';
		if ( 'cam_nghi' === $d['loai'] || 'cach_hieu' === $d['loai'] ) {
			// Cảm nghĩ và cách hiểu của bạn đọc: bình luận chờ duyệt (cách hiểu mang loại bình luận riêng `cach_hieu`)
			$id   = (int) wp_insert_comment(
				array(
					'comment_post_ID'      => $bai_id,
					'comment_author'       => '' !== $d['ten'] ? $d['ten'] : 'Một bạn đọc',
					'comment_author_email' => '',
					'comment_author_url'   => '',
					'comment_author_IP'    => '',
					'comment_agent'        => '',
					'comment_content'      => $d['noi_dung'],
					'comment_approved'     => 0,
					'comment_type'         => 'cach_hieu' === $d['loai'] ? 'cach_hieu' : 'comment',
					'user_id'              => 0,
				)
			);
			$loai = 'comment';
		} elseif ( 'phan_tich' === $d['loai'] ) {
			$id   = (int) wp_insert_post(
				wp_slash(
					array(
						'post_type'    => KhaiBao::PHAN_TICH,
						'post_status'  => 'pending',
						'post_title'   => $d['tieu_de'],
						'post_content' => GopNguoiDoc::van_ban( $d['noi_dung'] ),
						'post_author'  => 0,
						'meta_input'   => array(
							'_cadao_bai'      => $bai_id,
							'_cadao_but_danh' => $d['but_danh'],
							'_cadao_vai_tro'  => $d['vai'],
							'_cadao_du_an'    => false,
						),
					)
				)
			);
			$loai = 'post';
		} else {
			$noi = $d['noi_dung'];
			if ( 'so_trang' === $d['loai'] && ( '' !== $d['sach'] || '' !== $d['trang'] ) ) {
				$noi = trim( 'Sách: ' . ( '' !== $d['sach'] ? $d['sach'] : '(chưa ghi)' ) . ' · Trang: ' . ( '' !== $d['trang'] ? $d['trang'] : '(chưa ghi)' ) . "\n\n" . $noi );
			}
			$id   = (int) wp_insert_post(
				wp_slash(
					array(
						'post_type'    => KhaiBao::GOP_Y,
						'post_status'  => 'pending',
						'post_title'   => GopNguoiDoc::LOAI[ $d['loai'] ][0] . ' — ' . $ten_bai,
						'post_content' => $noi,
						'post_author'  => 0,
						// Cùng khoá meta MVP: góp ý cũ và mới đọc như nhau
						'meta_input'   => array(
							'_cadao_loai'     => $d['loai'],
							'_cadao_bai'      => $bai_id,
							'_cadao_vi_pham'  => $d['vi_pham'],
							'_cadao_noi_nghe' => $d['noi_nghe'],
							'_cadao_nguoi_ke' => $d['nguoi_ke'],
							'_cadao_ten'      => $d['ten'],
						),
					)
				)
			);
			$loai = 'post';
		}
		$the = $id ? Viec::tao(
			array(
				'loai'           => 'nguoi_doc',
				'doi_tuong_loai' => $loai,
				'doi_tuong_id'   => $id,
				'noi_dung'       => array(
					'loai' => $d['loai'],
					'bai'  => $bai_id,
				),
				'nguoi_tao'      => 'nguoi_doc',
				'uu_tien'        => 30,
			)
		) : 0;
		if ( ! $the || false === $wpdb->query( 'COMMIT' ) ) {
			$wpdb->query( 'ROLLBACK' );
			if ( $id && 'comment' === $loai ) {
				clean_comment_cache( $id );
			} elseif ( $id ) {
				clean_post_cache( $id );
			}
			return null;
		}
		return array( 'id' => $id, 'loai' => $loai );
	}

	/** @return ?string mã lời báo khi bị chặn; null = cho qua (đã tính một lần gửi) */
	private static function chong_rac( array $p ): ?string {
		$ip   = DiaChiMang::cua( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ), (string) ( $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '' ) );
		$khoa = 'phongdao_gui_' . substr( wp_hash( $ip . gmdate( 'YmdH' ) ), 0, 20 ); // chỉ dấu băm, hết hạn sau một giờ
		$so   = (int) get_transient( $khoa );
		if ( $so >= self::GUI_MOI_GIO ) {
			return 'nhieu';
		}
		$t = self::turnstile();
		if ( '' !== $t['bi_mat'] ) {
			$tl = wp_remote_post(
				'https://challenges.cloudflare.com/turnstile/v0/siteverify',
				array(
					'timeout' => 10,
					'body'    => array(
						'secret'   => $t['bi_mat'],
						'response' => (string) ( $p['cf-turnstile-response'] ?? '' ),
					),
				)
			);
			$j  = is_wp_error( $tl ) ? null : json_decode( wp_remote_retrieve_body( $tl ), true );
			if ( empty( $j['success'] ) ) {
				return 'turnstile';
			}
		}
		set_transient( $khoa, $so + 1, HOUR_IN_SECONDS );
		return null;
	}

	/** admin-post: kiểm nonce, lưu, quay lại trang bài (đã gửi) hoặc trang Góp (chưa gửi được). */
	public static function xu_ly(): void {
		$p  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dưới
		$kq = wp_verify_nonce( (string) ( $p['_wpnonce'] ?? '' ), self::HANH_DONG ) ? self::luu( $p ) : array( 'ok' => false, 'ma' => 'cu', 'id' => 0, 'bai' => (int) ( $p['bai'] ?? 0 ) );
		$goc = home_url( '/gop/' );
		$cach_hieu = 'cach_hieu' === ( $p['loai'] ?? '' ) && $kq['bai'];
		if ( $cach_hieu ) {
			// Form nhanh trên trang bài: quay lại đúng tầng Hiểu thêm, lời báo nằm cạnh form
			$toi = add_query_arg( array( 'gop' => $kq['ok'] ? 'da_gui' : 'loi', 'gop_loai' => 'cach_hieu', 'ma' => $kq['ok'] ? false : $kq['ma'] ), (string) get_permalink( $kq['bai'] ) ) . '#cach-hieu';
		} elseif ( $kq['ok'] ) {
			$toi = $kq['bai'] ? add_query_arg( 'gop', 'da_gui', (string) get_permalink( $kq['bai'] ) ) . '#gop' : add_query_arg( 'gui', 'da_gui', $goc );
		} else {
			$toi = add_query_arg(
				array_filter(
					array(
						'gui'  => 'loi',
						'ma'   => $kq['ma'],
						'loai' => sanitize_key( (string) ( $p['loai'] ?? '' ) ),
						'bai'  => $kq['bai'] ?: null,
					)
				),
				$goc
			) . '#gop-form';
		}
		wp_safe_redirect( $toi, 303 );
		exit;
	}
}
