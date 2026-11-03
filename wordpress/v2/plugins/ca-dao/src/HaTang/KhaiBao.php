<?php

namespace Phongdao\HaTang;

use Phongdao\MienDuLieu\Phan;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Khai báo loại bài, phân loại, các khoá meta của phần bài và đường dẫn tác phẩm `/<thể loại>/<tên>/`
 * (docs/26 mục 4.1, 4.2; docs/27 mục 3). Chạy mỗi lần nạp (init), không ghi cơ sở dữ liệu.
 */
final class KhaiBao implements Module {

	public const TAC_PHAM    = 'tac_pham';
	public const TUYEN_TAP   = 'tuyen_tap';
	public const PHAN_TICH   = 'phan_tich';
	public const GOP_Y       = 'gop_y';
	public const HO_SO_NGUON = 'ho_so_nguon';

	public const THE_LOAI = 'the_loai';

	/** Phân loại của tác phẩm => [tiền tố đường dẫn, tên]. Trò chơi gắn đồng dao; mục từ là Từ điển. */
	public const PHAN_LOAI = array(
		'the_loai'  => array( 'the-loai', 'Thể loại' ),
		'chu_de'    => array( 'chu-de', 'Chủ đề' ),
		'vung'      => array( 'vung', 'Vùng lưu truyền' ),
		'mo_tip'    => array( 'mo-tip', 'Mô-típ' ),
		'hinh_thuc' => array( 'hinh-thuc', 'Hình thức' ),
		'muc_tu'    => array( 'tu-dien', 'Mục từ' ),
		'tro_choi'  => array( 'tro-choi', 'Trò chơi' ),
	);

	/** Tăng khi thêm / đổi luật đường dẫn (trang cố định, loại bài…): lần nạp sau tự làm mới luật viết lại. */
	public const PHIEN_BAN_DUONG_DAN = 5;

	public function dang_ky(): void {
		add_action( 'init', array( self::class, 'khai_bao' ), 0 );
		add_action(
			'init',
			static function (): void {
				if ( (int) get_option( 'phongdao_duong_dan' ) !== self::PHIEN_BAN_DUONG_DAN ) {
					flush_rewrite_rules( false );
					update_option( 'phongdao_duong_dan', self::PHIEN_BAN_DUONG_DAN );
				}
			},
			99
		);
		add_filter( 'post_type_link', array( self::class, 'duong_dan' ), 10, 2 );
		add_action( 'template_redirect', array( self::class, 've_duong_dan_chuan' ) );
		add_action( 'pre_get_posts', array( self::class, 'truy_van_danh_sach' ) );
		add_filter( 'query_vars', static fn( array $v ): array => array_merge( $v, array( 'sap' ) ) );
	}

	/** Trang Tất cả (/kho/) và trang phân loại: 30 bài mỗi trang, A–Z (?sap=moi: mới thêm trước). */
	public static function truy_van_danh_sach( \WP_Query $q ): void {
		if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_post_type_archive( self::TAC_PHAM ) || $q->is_tax( array_keys( self::PHAN_LOAI ) ) ) ) {
			return;
		}
		$q->set( 'post_type', self::TAC_PHAM );
		$q->set( 'posts_per_page', 30 );
		$moi = 'moi' === $q->get( 'sap' );
		$q->set( 'orderby', $moi ? 'date' : 'title' );
		$q->set( 'order', $moi ? 'DESC' : 'ASC' );
	}

	public static function khai_bao(): void {
		if ( post_type_exists( self::TAC_PHAM ) ) {
			return;
		}
		// Tác phẩm: không trình soạn khối (D-086), không REST mặc định, không bản sửa — lịch sử ở bảng việc (D-094)
		register_post_type(
			self::TAC_PHAM,
			array(
				'labels'          => array( 'name' => 'Tác phẩm', 'singular_name' => 'Tác phẩm', 'all_items' => 'Tất cả tác phẩm' ),
				'public'          => true,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'show_in_rest'    => false,
				'supports'        => array( 'title', 'author', 'comments' ),
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'kho', 'with_front' => false, 'feeds' => false ),
				'query_var'       => self::TAC_PHAM,
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
		register_post_type(
			self::TUYEN_TAP,
			array(
				'labels'          => array( 'name' => 'Tuyển tập', 'singular_name' => 'Tuyển tập' ),
				'public'          => true,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'show_in_rest'    => false,
				'supports'        => array( 'title', 'author' ),
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'tuyen-tap', 'with_front' => false, 'feeds' => false ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
		// Giữ từ MVP (cùng tên loại bài để dữ liệu cũ nối đúng, docs/29)
		register_post_type(
			self::PHAN_TICH,
			array(
				'labels'       => array( 'name' => 'Cảm nhận – phân tích', 'singular_name' => 'Bài cảm nhận' ),
				'public'       => true,
				'show_in_menu' => false,
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'author', 'revisions' ),
				'rewrite'      => array( 'slug' => 'phan-tich', 'with_front' => false ),
				'has_archive'  => false,
			)
		);
		register_post_type(
			self::GOP_Y,
			array(
				'labels'       => array( 'name' => 'Góp ý của người đọc', 'singular_name' => 'Góp ý' ),
				'public'       => false,
				'show_ui'      => false,
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor' ),
			)
		);
		// Hồ sơ nguồn: không công khai, không trình soạn; sửa ở trang Nguồn & cài đặt (T-143, T-155)
		register_post_type(
			self::HO_SO_NGUON,
			array(
				'labels'       => array( 'name' => 'Hồ sơ nguồn', 'singular_name' => 'Hồ sơ nguồn' ),
				'public'       => false,
				'show_ui'      => false,
				'show_in_rest' => false,
				'rewrite'      => false,
				'query_var'    => false,
				'supports'     => array( 'title' ),
			)
		);

		foreach ( self::PHAN_LOAI as $ten => [ $tien_to, $nhan ] ) {
			register_taxonomy(
				$ten,
				array( self::TAC_PHAM ),
				array(
					'labels'            => array( 'name' => $nhan, 'singular_name' => $nhan ),
					'public'            => true,
					'hierarchical'      => false,
					'show_ui'           => true,
					'show_in_menu'      => false,
					'show_in_rest'      => false,
					'show_admin_column' => false,
					'rewrite'           => array( 'slug' => $tien_to, 'with_front' => false ),
				)
			);
		}

		// Phần của bài: chỉ ghi qua DichVu\LuuPhan; không mở qua REST, không sửa được qua "Trường tuỳ chỉnh"
		foreach ( Phan::loai_bai() as $loai_bai ) {
			foreach ( Phan::ds( $loai_bai ) as $phan ) {
				register_post_meta(
					$loai_bai,
					Phan::khoa( $phan ),
					array(
						'type'              => 'array',
						'single'            => true,
						'show_in_rest'      => false,
						'auth_callback'     => '__return_false',
						'revisions_enabled' => false,
					)
				);
			}
		}

		register_post_meta(
			self::HO_SO_NGUON,
			\Phongdao\KhoLuu\HoSo::META,
			array(
				'type'          => 'array',
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => '__return_false',
			)
		);

		add_rewrite_rule( '^(' . implode( '|', array_map( 'preg_quote', array_keys( TheLoai::DS ) ) ) . ')/([^/]+)/?$', 'index.php?' . self::TAC_PHAM . '=$matches[2]', 'top' );
	}

	/** Đường dẫn tác phẩm theo thể loại; bài chưa có tên đường dẫn hoặc thể loại thì giữ mặc định. */
	public static function duong_dan( string $url, \WP_Post $bai ): string {
		if ( self::TAC_PHAM !== $bai->post_type || '' === $bai->post_name || ! get_option( 'permalink_structure' ) ) {
			return $url;
		}
		$the_loai = get_the_terms( $bai, self::THE_LOAI );
		if ( ! is_array( $the_loai ) || ! $the_loai || ! TheLoai::co( $the_loai[0]->slug ) ) {
			return $url;
		}
		return home_url( user_trailingslashit( $the_loai[0]->slug . '/' . $bai->post_name ) );
	}

	/** /kho/<tên>/ hoặc sai thể loại trong đường dẫn → 301 về đường dẫn chuẩn (một bài một địa chỉ). */
	public static function ve_duong_dan_chuan(): void {
		if ( ! is_singular( self::TAC_PHAM ) || is_preview() ) {
			return;
		}
		$chuan = (string) get_permalink();
		$dang  = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
		$muon  = (string) wp_parse_url( $chuan, PHP_URL_PATH );
		if ( '' !== $muon && untrailingslashit( rawurldecode( $dang ) ) !== untrailingslashit( rawurldecode( $muon ) ) ) {
			wp_safe_redirect( $chuan, 301 );
			exit;
		}
	}
}
