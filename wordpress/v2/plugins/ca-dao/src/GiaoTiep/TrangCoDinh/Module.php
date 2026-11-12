<?php

namespace Phongdao\GiaoTiep\TrangCoDinh;

defined( 'ABSPATH' ) || exit;

/**
 * Trang cố định của thư viện (docs/27 mục 3): /tim/, /da-luu/, /kham-pha/… — đường dẫn do plugin khai báo, không tạo
 * trang trong cơ sở dữ liệu (không ai xoá nhầm được). Theme có mẫu `trang-<mã>.html` chứa khối `cadao/<mã>`.
 * Trang cũ cùng tên của MVP (nếu có) bị đường dẫn này che.
 */
final class Module implements \Phongdao\HaTang\Module {

	/**
	 * Mã (= đường dẫn) => tên trang. Thêm trang: thêm vào đây, thêm mẫu theme `trang-<mã, / thành ->.html`, tăng
	 * KhaiBao::PHIEN_BAN_DUONG_DAN. Trang `bien-tap…` (D-094) phải đăng nhập với quyền biên tập, không lập chỉ mục.
	 */
	public const TRANG = array(
		'tim'               => 'Tìm',
		'tu-dien'           => 'Từ điển',
		'gop'               => 'Góp tư liệu',
		'da-luu'            => 'Đã lưu',
		'ban-do'            => 'Bản đồ',
		'bien-tap'          => 'Hộp việc',
		'bien-tap/tac-pham' => 'Tác phẩm',
		'bien-tap/them'     => 'Thêm bài',
		'bien-tap/so-lieu'  => 'Số liệu tuần',
		'kham-pha'          => 'Khám phá',
		'chu-de'            => 'Chủ đề',
		'mo-tip'            => 'Mô-típ',
		'ngau-nhien'        => 'Một câu ngẫu nhiên',
		'hay-bi-nham'       => 'Hay bị nhầm là ca dao',
	);

	public static function la_bien_tap(): bool {
		return str_starts_with( self::dang_xem(), 'bien-tap' );
	}

	/**
	 * Màn wp-admin thay cho một trang `/bien-tap/…` cũ (D-104): Hộp việc (giữ `viec`, `loai`, `tt` Để sau), Tác phẩm (bộ lọc
	 * "thiếu ý nghĩa" → `muc=thieu_y_nghia`), Thêm tác phẩm, Hôm nay (Số liệu tuần).
	 *
	 * @param array<string,mixed> $q tham số trên đường dẫn cũ
	 */
	public static function dich_quan_tri( string $trang, array $q ): string {
		$di = static fn( string $p, array $them = array() ): string => add_query_arg( array( 'page' => $p ) + array_filter( $them, static fn( $v ) => '' !== (string) $v ), admin_url( 'admin.php' ) );
		switch ( $trang ) {
			case 'bien-tap/tac-pham':
				return $di( 'phongdao-tac-pham', array( 'muc' => 'thieu-y-nghia' === ( $q['loc'] ?? '' ) ? 'thieu_y_nghia' : '', 'tt' => 'ban-nhap' === ( $q['loc'] ?? '' ) ? 'draft' : '', 'q' => rawurlencode( sanitize_text_field( (string) ( $q['q'] ?? '' ) ) ) ) );
			case 'bien-tap/them':
				return $di( 'phongdao-them' );
			case 'bien-tap/so-lieu':
				return $di( 'phongdao-ban-lam-viec' );
			default:
				return $di( 'phongdao-hop-viec', array( 'viec' => absint( $q['viec'] ?? 0 ) ?: '', 'loai' => sanitize_key( (string) ( $q['loai'] ?? '' ) ), 'tt' => 'de_sau' === ( $q['tt'] ?? '' ) ? 'de_sau' : '' ) );
		}
	}

	public const BIEN = 'phongdao_trang';

	public function dang_ky(): void {
		add_action( 'init', array( self::class, 'duong_dan' ), 1 );
		add_filter( 'query_vars', static fn( array $v ): array => array_merge( $v, array( self::BIEN ) ) );
		add_action( 'parse_query', array( self::class, 'danh_dau' ) );
		add_filter( 'pre_handle_404', static fn( bool $co ): bool => $co || '' !== self::dang_xem() );
		add_filter( 'index_template_hierarchy', static fn( array $mau ): array => '' === self::dang_xem() ? $mau : array_merge( array( 'trang-' . str_replace( '/', '-', self::dang_xem() ) ), $mau ) );
		// Một câu ngẫu nhiên: không để đệm giữ một bài; Hay bị nhầm dưới 5 câu: khách nhận 404 (docs/27 mục 7.8)
		add_action(
			'template_redirect',
			static function (): void {
				if ( 'ngau-nhien' === self::dang_xem() ) {
					nocache_headers();
				}
				if ( 'hay-bi-nham' === self::dang_xem() && ! \Phongdao\GiaoTiep\Khoi\KhamPha::hay_bi_nham_cong_khai() && ! current_user_can( 'cadao_soan' ) ) {
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					nocache_headers();
				}
			},
			2
		);
		// Trang biên tập `/bien-tap/…` đã gộp về wp-admin (D-104): chuyển hướng 301 sang màn tương ứng (giữ thẻ, loại việc,
		// bộ lọc thiếu ý nghĩa); wp-admin tự đòi đăng nhập
		add_action(
			'template_redirect',
			static function (): void {
				if ( self::la_bien_tap() ) {
					wp_safe_redirect( self::dich_quan_tri( self::dang_xem(), wp_unslash( $_GET ) ), 301 ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc để chuyển hướng
					exit;
				}
			},
			0
		);
		// Trang biên tập: chưa đăng nhập → trang đăng nhập rồi quay lại; không đủ quyền → 403
		add_action(
			'template_redirect',
			static function (): void {
				if ( ! self::la_bien_tap() ) {
					return;
				}
				if ( ! is_user_logged_in() ) {
					auth_redirect();
				}
				if ( ! current_user_can( 'cadao_soan' ) ) {
					wp_die( esc_html__( 'Trang này dành cho người biên soạn của thư viện.', 'ca-dao' ), esc_html__( 'Không có quyền', 'ca-dao' ), array( 'response' => 403 ) );
				}
			},
			1
		);
		add_filter(
			'document_title_parts',
			static function ( array $phan ): array {
				if ( '' !== self::dang_xem() ) {
					$phan['title'] = self::TRANG[ self::dang_xem() ];
				}
				return $phan;
			}
		);
		// Trang kết quả tìm không cần lập chỉ mục
		add_filter(
			'wp_robots',
			static function ( array $r ): array {
				if ( self::la_bien_tap() || in_array( self::dang_xem(), array( 'gop', 'da-luu', 'ngau-nhien' ), true ) || ( in_array( self::dang_xem(), array( 'tim', 'tu-dien' ), true ) && '' !== trim( (string) ( $_GET['q'] ?? '' ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$r['noindex'] = true;
				}
				return $r;
			}
		);
		// Ô tìm mặc định của WordPress (?s=) chuyển về /tim/
		add_action(
			'template_redirect',
			static function (): void {
				if ( is_search() ) {
					wp_safe_redirect( add_query_arg( 'q', rawurlencode( get_search_query( false ) ), home_url( '/tim/' ) ), 302 );
					exit;
				}
			}
		);
	}

	public static function duong_dan(): void {
		add_rewrite_rule( '^(' . implode( '|', array_map( 'preg_quote', array_keys( self::TRANG ) ) ) . ')/?$', 'index.php?' . self::BIEN . '=$matches[1]', 'top' );
	}

	/** Trang cố định không phải trang chủ, không phải 404; không cần truy vấn bài. */
	public static function danh_dau( \WP_Query $q ): void {
		if ( ! $q->is_main_query() || ! isset( self::TRANG[ (string) $q->get( self::BIEN ) ] ) ) {
			return;
		}
		$q->is_home       = false;
		$q->is_404        = false;
		$q->is_archive    = false;
		$q->set( 'posts_per_page', 1 );
		$q->set( 'no_found_rows', true );
		$q->set( 'post_type', 'page' );
		$q->set( 'post__in', array( 0 ) );
	}

	/** Mã trang cố định đang xem, hoặc rỗng. */
	public static function dang_xem(): string {
		$ma = (string) get_query_var( self::BIEN );
		return isset( self::TRANG[ $ma ] ) ? $ma : '';
	}
}
