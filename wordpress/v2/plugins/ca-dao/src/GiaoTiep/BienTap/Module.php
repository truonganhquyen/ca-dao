<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\Rest\Module as Rest;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;

defined( 'ABSPATH' ) || exit;

/**
 * Biên tập trên trang đọc (D-094, docs/26 mục 7): REST lưu một phần / đổi trạng thái / hoàn tác (quyền riêng + nonce REST
 * của WordPress), mã sửa `bien-tap.js` chỉ nạp cho người có quyền, trang của người đăng nhập không vào bộ nhớ đệm.
 */
final class Module implements \Phongdao\HaTang\Module {

	public function dang_ky(): void {
		AnhMinhHoa::dang_ky();
		add_action( 'rest_api_init', array( self::class, 'route' ) );
		add_action(
			'send_headers',
			static function (): void {
				if ( is_user_logged_in() ) {
					header( 'Cache-Control: private, no-store, max-age=0' );
				}
			}
		);
		add_action( 'wp_enqueue_scripts', array( self::class, 'nap_ma' ) );
		HopViec::dang_ky();
		SuaMucTu::dang_ky();
		ThemBai::dang_ky();
	}

	/** Mã sửa (JavaScript thuần, không bước dựng) chỉ cho người biên soạn, ở trang bài và các trang biên tập. */
	public static function nap_ma(): void {
		if ( ! CheDoSua::co_quyen() || ! ( is_singular( KhaiBao::TAC_PHAM ) || \Phongdao\GiaoTiep\TrangCoDinh\Module::la_bien_tap() ) ) {
			return;
		}
		$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
		wp_enqueue_script( 'phongdao-giu-nhap', plugins_url( 'assets/giu-nhap.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_enqueue_style( 'phongdao-giu-nhap', plugins_url( 'assets/giu-nhap.css', $tep ), array(), CADAO_PHIEN_BAN );
		wp_enqueue_script( 'phongdao-bien-tap', plugins_url( 'assets/bien-tap.js', $tep ), array( 'phongdao-giu-nhap' ), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script(
			'phongdao-bien-tap',
			'window.phongdaoBienTap=' . wp_json_encode(
				array(
					'api'   => esc_url_raw( rest_url( Rest::KHONG_GIAN ) ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				)
			) . ';',
			'before'
		);
	}

	public static function route(): void {
		$sua_bai = static fn( \WP_REST_Request $yc ): bool => current_user_can( 'cadao_soan' ) && current_user_can( 'edit_post', (int) $yc['id'] ) && KhaiBao::TAC_PHAM === get_post_type( (int) $yc['id'] );
		register_rest_route(
			Rest::KHONG_GIAN,
			'/tac-pham/(?P<id>\d+)/phan/(?P<phan>[a-z_]+)',
			array(
				'methods'             => 'POST',
				'permission_callback' => $sua_bai,
				'callback'            => array( self::class, 'luu_phan' ),
			)
		);
		register_rest_route(
			Rest::KHONG_GIAN,
			'/tac-pham/(?P<id>\d+)/trang-thai',
			array(
				'methods'             => 'POST',
				'permission_callback' => $sua_bai,
				'callback'            => array( self::class, 'trang_thai' ),
			)
		);
		register_rest_route(
			Rest::KHONG_GIAN,
			'/trung/kiem',
			array(
				'methods'             => 'POST',
				'permission_callback' => static fn(): bool => current_user_can( 'cadao_soan' ),
				'callback'            => static fn( \WP_REST_Request $yc ): array => ThemBai::kiem( mb_substr( (string) $yc['loi'], 0, 20000 ) ),
			)
		);
		register_rest_route(
			Rest::KHONG_GIAN,
			'/viec/(?P<id>\d+)/hoan-tac',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function ( \WP_REST_Request $yc ): bool {
					$v = Viec::lay( (int) $yc['id'] );
					return $v && 'post' === $v['doi_tuong_loai'] && current_user_can( 'cadao_soan' ) && current_user_can( 'edit_post', (int) $v['doi_tuong_id'] );
				},
				'callback'            => static function ( \WP_REST_Request $yc ): array {
					$kq = LuuPhan::khoi_phuc( (int) $yc['id'], self::nc() );
					return $kq + array( 'thong_bao' => $kq['ok'] ? 'Đã hoàn tác.' : '' );
				},
			)
		);
	}

	/** Người đang bấm: ngữ cảnh lưu (quyền duyệt theo vai). */
	private static function nc(): array {
		return array(
			'nguoi' => get_current_user_id(),
			'goi'   => KiemPhan::GOI_NGUOI,
			'duyet' => current_user_can( 'cadao_duyet' ),
		);
	}

	/** Lưu một phần từ tấm sửa; ý nghĩa chưa duyệt thành nháp trong Hộp việc. */
	public static function luu_phan( \WP_REST_Request $yc ): array {
		$id   = (int) $yc['id'];
		$phan = (string) $yc['phan'];
		if ( ! isset( TamSua::TAM[ $phan ] ) ) {
			return array( 'ok' => false, 'loi' => array( 'Phần này chưa sửa được ở đây.' ) );
		}
		$vao = (array) $yc->get_body_params();
		$phien = is_string( $vao['phien_phan'] ?? null ) ? $vao['phien_phan'] : '';
		$kq = LuuPhan::trong_phien( $id, $phan, $phien, static fn( ?array $cu ): array => self::doc_va_luu( $id, $phan, $vao, $cu ) );
		if ( ! empty( $kq['xung_dot'] ) ) {
			$kq['so_ban_moi'] = TamSua::doi_chieu_phien( $kq['hien_tai'], $kq['phien_moi'] );
			unset( $kq['hien_tai'] );
		}
		return $kq;
	}

	/** Đọc form sau khi giữ khóa và so đúng bản đã mở. */
	private static function doc_va_luu( int $id, string $phan, array $vao, ?array $cu ): array {
		if ( 'media' === $phan && ! current_user_can( 'upload_files' ) ) {
			return array( 'ok' => false, 'loi' => array( 'Tài khoản cần quyền tải tệp để lưu ảnh minh hoạ.' ), 'doi' => false, 'viec' => 0 );
		}
		$loi_form = TamSua::loi_form( $phan, $vao );
		if ( $loi_form ) {
			return array( 'ok' => false, 'loi' => $loi_form, 'doi' => false, 'viec' => 0 );
		}
		if ( ! current_user_can( 'cadao_duyet' ) ) {
			unset( $vao['duyet'] ); // chỉ người có quyền duyệt mới "hiện cho người đọc"; còn lại luôn là nháp
		}
		$doc = TamSua::doc( $phan, $vao, $cu );
		if ( null !== $doc['nhap'] ) {
			$viec = Viec::tao(
				array(
					'loai'           => 'nhap_y_nghia',
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'phan'           => $phan,
					'noi_dung'       => $doc['nhap'],
					'nguoi_tao'      => (string) get_current_user_id(),
					'uu_tien'        => 35,
				)
			);
			return array( 'ok' => (bool) $viec, 'loi' => array(), 'viec' => 0, 'thong_bao' => 'Đã lưu nháp vào Hộp việc; người đọc chưa thấy.' . self::nhac( $id, $phan, $doc['nhap'] ) );
		}
		$loai = (string) \Phongdao\DichVu\DungTrang\DocBai::the_loai( get_post( $id ) );
		if ( 'gan_nghia' === $phan ) {
			$loi_gn = self::kiem_gan_nghia( $id, $doc );
			if ( $loi_gn ) {
				return array( 'ok' => false, 'loi' => $loi_gn, 'doi' => false, 'viec' => 0 );
			}
		}
		$kq = LuuPhan::luu( $id, $phan, $doc['gia_tri'], self::nc() );
		$them_bao = '';
		// Ô "Nối Từ điển" (D-126): gắn mục từ vừa chọn vào bài, gỡ mục từ vừa bỏ nối nếu không từ khó nào khác còn dùng
		if ( $kq['ok'] && 'chu_thich' === $phan && ( TamSua::$doi_noi['them'] || TamSua::$doi_noi['bo'] ) ) {
			$con  = array_map( 'intval', array_column( (array) ( $doc['gia_tri']['muc'] ?? $doc['gia_tri'] ?? array() ), 'muc_tu_id' ) );
			$dang = \Phongdao\DichVu\LuuPhan\LuuPhanLoai::dang_gan( $id, 'muc_tu' ) ?? array();
			$moi  = array_values( array_unique( array_merge( array_diff( $dang, array_diff( TamSua::$doi_noi['bo'], $con ) ), TamSua::$doi_noi['them'] ) ) );
			sort( $moi, SORT_NUMERIC );
			if ( $moi !== $dang ) {
				$kq_mt = \Phongdao\DichVu\LuuPhan\LuuPhanLoai::luu_muc_tu( $id, $moi, $dang, get_current_user_id(), 'luu', 0, true );
				if ( ! $kq_mt['ok'] ) {
					return array( 'ok' => false, 'loi' => array_merge( array( 'Chưa nối được Từ điển; từ khó chưa đổi.' ), $kq_mt['loi'] ), 'doi' => false, 'viec' => 0 );
				}
			}
		}
		if ( $kq['ok'] && ! empty( $doc['chuyen'] ) ) {
			// Người đánh dấu "Chuyển sang Chuyện quanh bài" (D-119): mục rời Từ khó, nối vào cuối Chuyện quanh bài, cùng một lượt
			// lưu (một phần hỏng thì cả hai không đổi); người làm, nguồn của mục giữ nguyên
			$ten_cq = TamSua::ten_chuyen_quanh( $loai );
			$kq_cq  = LuuPhan::luu( $id, 'chuyen_quanh', array_merge( array_values( PhanBai::lay( $id, 'chuyen_quanh' )['muc'] ?? array() ), $doc['chuyen'] ), self::nc() );
			if ( ! $kq_cq['ok'] ) {
				return array( 'ok' => false, 'loi' => array_merge( array( 'Chưa chuyển được mục sang ' . $ten_cq . '; từ khó chưa đổi.' ), $kq_cq['loi'] ), 'doi' => false, 'viec' => 0 );
			}
			$them_bao = ' Đã chuyển ' . count( $doc['chuyen'] ) . ' mục sang ' . $ten_cq . '.';
		}
		if ( $kq['ok'] && 'gan_nghia' === $phan && ! empty( $vao['hai_chieu'] ) ) {
			$hai = self::gan_nghia_hai_chieu( $id, $cu, (array) ( $doc['gia_tri'] ?? array() ) );
			if ( ! $hai['ok'] ) {
				return $hai;
			}
			$them_bao = $hai['so'] ? ' Đã ghi cả ở ' . $hai['so'] . ' câu kia.' : '';
		}
		$ten = TamSua::nhan( $phan, $loai );
		$kq['loi'] = TamSua::nhan_loi( $phan, $kq['loi'], $loai );
		$bao = $kq['ok'] ? ( $kq['doi'] ? 'Đã lưu.' : 'Không có gì thay đổi.' ) : '';
		if ( $kq['ok'] && $kq['doi'] ) {
			$bao .= ' Phần: ' . $ten . '. ' . ( 'publish' === get_post_status( $id ) ? 'Trang người đọc đã cập nhật.' : 'Bài chưa công khai; khách chưa thấy phần này.' );
		}
		if ( $kq['ok'] && 'chu_thich' === $phan ) {
			// Từ không có trong lời: nhắc kiểm chính tả, vẫn lưu (chú thích chung) — D-105
			$thieu = \Phongdao\MienDuLieu\TuKho::thieu( (array) ( PhanBai::lay( $id, 'loi' )['dong'] ?? array() ), array_column( (array) ( $doc['gia_tri']['muc'] ?? $doc['gia_tri'] ?? array() ), 'tu' ) );
			$bao  .= $thieu ? ' Không thấy từ ' . implode( ', ', array_map( static fn( string $t ): string => '«' . $t . '»', $thieu ) ) . ' trong lời, kiểm lại chính tả.' : '';
			// Chữ của từ khó đã đổi: nối Từ điển cũ bị bỏ (D-125) — nói rõ để người sửa biết mà nối lại nếu cần
			$bo   = array_values( array_unique( array_filter( TamSua::$bo_noi, 'strlen' ) ) );
			$bao .= $bo ? ' Đã bỏ nối Từ điển của ' . implode( ', ', array_map( static fn( string $t ): string => '«' . $t . '»', $bo ) ) . ' vì chữ đã đổi.' : '';
		}
		return $kq + array( 'thong_bao' => $bao . $them_bao . ( $kq['ok'] ? self::nhac( $id, $phan, $doc['gia_tri'] ) : '' ) );
	}

	/** Nhắc lỗi trình bày theo quy tắc viết (D-119): chỉ nhắc sau khi lưu, không chặn, không tự sửa chữ. */
	private static function nhac( int $id, string $phan, ?array $gia_tri ): string {
		$nhac = \Phongdao\MienDuLieu\KiemTrinhBay::nhac( $phan, $gia_tri, (array) ( PhanBai::lay( $id, 'loi' )['dong'] ?? array() ) );
		return $nhac ? ' Nhắc (không chặn lưu): ' . implode( ' ', array_column( $nhac, 'chu' ) ) : '';
	}

	/**
	 * Câu gần nghĩa · trái nghĩa (D-119): dòng dán phải ra đúng một bài; câu được nối là tác phẩm (chưa vào thùng rác) và không
	 * phải chính câu này. Không đoán: chưa chắc thì báo, chữ đang nhập được giữ.
	 *
	 * @return string[]
	 */
	private static function kiem_gan_nghia( int $id, array $doc ): array {
		$loi = array();
		foreach ( (array) ( $doc['khong_thay'] ?? array() ) as $dong ) {
			$loi[] = 'Không tìm thấy bài cho “' . mb_substr( (string) $dong, 0, 80 ) . '”. Dán đúng đường dẫn bài, số bài, hoặc tên bài đúng từng chữ.';
		}
		foreach ( (array) ( $doc['gia_tri'] ?? array() ) as $m ) {
			$bai = get_post( (int) $m['tac_pham_id'] );
			if ( (int) $m['tac_pham_id'] === $id ) {
				$loi[] = 'Không nối một câu với chính nó.';
			} elseif ( ! $bai || \Phongdao\HaTang\KhaiBao::TAC_PHAM !== $bai->post_type || 'trash' === $bai->post_status ) {
				$loi[] = 'Bài #' . (int) $m['tac_pham_id'] . ' không phải tác phẩm đang có.';
			}
		}
		return array_values( array_unique( $loi ) );
	}

	/**
	 * "Ghi cả ở câu kia": câu vừa nối thêm (không phải câu đã nối từ trước) mà là tục ngữ người đang sửa có quyền sửa thì nhận
	 * chiều ngược lại cùng quan hệ, trong cùng lượt lưu, có Lịch sử ở câu kia. Bỏ nối không lan sang câu kia.
	 *
	 * @param list<array{tac_pham_id:int,quan_he:string}> $moi
	 * @return array{ok:bool,loi:string[],doi:bool,viec:int,so:int}
	 */
	private static function gan_nghia_hai_chieu( int $id, ?array $cu, array $moi ): array {
		$truoc = array_map( 'intval', array_column( (array) ( $cu['muc'] ?? array() ), 'tac_pham_id' ) );
		$so    = 0;
		foreach ( $moi as $m ) {
			$kia = (int) $m['tac_pham_id'];
			$bai = get_post( $kia );
			if ( in_array( $kia, $truoc, true ) || ! $bai || 'tuc-ngu' !== \Phongdao\DichVu\DungTrang\DocBai::the_loai( $bai ) || ! current_user_can( 'edit_post', $kia ) ) {
				continue;
			}
			if ( ! LuuPhan::khoa_bai( $kia ) ) {
				return array( 'ok' => false, 'loi' => array( 'Chưa ghi được ở câu kia. Thử lại sau.' ), 'doi' => false, 'viec' => 0, 'so' => 0 );
			}
			$ds = array_values( PhanBai::lay( $kia, 'gan_nghia' )['muc'] ?? array() );
			if ( in_array( $id, array_map( 'intval', array_column( $ds, 'tac_pham_id' ) ), true ) ) {
				continue;
			}
			$ds[] = array( 'tac_pham_id' => $id, 'quan_he' => $m['quan_he'] );
			$kq   = LuuPhan::luu( $kia, 'gan_nghia', $ds, self::nc() );
			if ( ! $kq['ok'] ) {
				return array( 'ok' => false, 'loi' => array_merge( array( 'Chưa ghi được ở câu #' . $kia . '; câu này chưa đổi.' ), $kq['loi'] ), 'doi' => false, 'viec' => 0, 'so' => 0 );
			}
			++$so;
		}
		return array( 'ok' => true, 'loi' => array(), 'doi' => true, 'viec' => 0, 'so' => $so );
	}

	/** Đăng / Ẩn / về nháp. Đăng cần quyền duyệt. Ghi một dòng nhật ký. */
	public static function trang_thai( \WP_REST_Request $yc ): array {
		global $wpdb;
		$id  = (int) $yc['id'];
		$moi = (string) $yc['trang_thai'];
		$mong_doi = (string) $yc['trang_thai_cu'];
		$map = array( 'publish' => 'dang', 'private' => 'an', 'draft' => 'nhap' );
		if ( ! isset( $map[ $moi ] ) || ( 'publish' === $moi && ! current_user_can( 'cadao_duyet' ) ) ) {
			return array( 'ok' => false, 'loi' => array( 'publish' === $moi ? 'Cần quyền duyệt để đăng.' : 'Trạng thái không hợp lệ.' ) );
		}
		if ( ! isset( $map[ $mong_doi ] ) && ! in_array( $mong_doi, array( 'pending', 'future' ), true ) ) {
			return array( 'ok' => false, 'loi' => array( 'Tải lại bài trước khi đổi trạng thái.' ) );
		}
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return array( 'ok' => false, 'loi' => array( 'Chưa mở được lượt đổi trạng thái. Thử lại sau.' ) );
		}
		$cu = $wpdb->get_var( $wpdb->prepare( "SELECT post_status FROM $wpdb->posts WHERE ID = %d AND post_type = %s FOR UPDATE", $id, KhaiBao::TAC_PHAM ) );
		if ( null === $cu || $cu !== $mong_doi ) {
			$wpdb->query( 'ROLLBACK' );
			return array( 'ok' => false, 'loi' => array( 'Bài đã đổi ở phiên khác. Tải lại trước khi quyết định.' ) );
		}
		if ( $cu === $moi ) {
			$wpdb->query( 'ROLLBACK' );
			return array( 'ok' => true, 'loi' => array(), 'thong_bao' => 'Không có gì thay đổi.' );
		}
		try {
			\Phongdao\DichVu\LuuPhan\DauVetTrangThai::$bo_qua = true; // lối này tự ghi dòng nhật ký bên dưới
			try {
				$cap_nhat = wp_update_post( array( 'ID' => $id, 'post_status' => $moi ), true );
			} finally {
				\Phongdao\DichVu\LuuPhan\DauVetTrangThai::$bo_qua = false;
			}
			$da_luu = $wpdb->get_var( $wpdb->prepare( "SELECT post_status FROM $wpdb->posts WHERE ID = %d", $id ) );
			if ( is_wp_error( $cap_nhat ) || (int) $cap_nhat !== $id || $da_luu !== $moi ) {
				throw new \RuntimeException( 'post_status' );
			}
			$viec = Viec::tao(
				array(
					'loai'           => Viec::SUA,
					'trang_thai'     => Viec::XONG,
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'phan'           => 'trang_thai',
					'nguoi_tao'      => (string) get_current_user_id(),
					'quyet'          => $map[ $moi ],
					'truoc'          => array( 'trang_thai' => $cu ),
					'sau'            => array( 'trang_thai' => $moi ),
					'nguoi_quyet'    => get_current_user_id(),
					'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
				)
			);
			if ( ! $viec || false === $wpdb->query( 'COMMIT' ) ) {
				throw new \RuntimeException( 'nhat_ky' );
			}
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			clean_post_cache( $id );
			return array( 'ok' => false, 'loi' => array( 'Chưa lưu được trạng thái và nhật ký. Tải lại bài trước khi thử tiếp.' ) );
		}
		return array( 'ok' => true, 'loi' => array(), 'viec' => 0, 'thong_bao' => array( 'dang' => 'Đã đăng.', 'an' => 'Đã ẩn bài khỏi người đọc.', 'nhap' => 'Đã chuyển về bản nháp.' )[ $map[ $moi ] ], 'nhat_ky' => $viec );
	}
}
