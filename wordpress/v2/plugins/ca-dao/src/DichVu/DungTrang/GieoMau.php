<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Tác phẩm mẫu tổng hợp cho bản xem trước và kiểm thử (blueprint Playground) — chữ mẫu tự đặt, không phải lời,
 * chú thích thật của nguồn (repo công khai, D-050). Lưu qua DichVu\LuuPhan như người biên soạn bấm.
 * Không chạy trên site thật: chỉ blueprint và kiểm thử gọi.
 */
final class GieoMau {

	private const BAI = array(
		'dong-mau-mot-cua-bai' => array(
			'the_loai' => 'ca-dao',
			'tieu_de'  => 'Dòng mẫu một của bài',
			'chu_de'   => array( 'Lao động' ),
			'phan'     => array(
				'loi'       => array( 'dong' => array( 'Dòng mẫu một của bài này', 'Dòng mẫu hai dài hơn chút cho vừa', 'Dòng mẫu ba của bài này', 'Dòng mẫu bốn dài hơn chút cho xong' ), 'nguoi_lam' => NguoiLam::MAY_CHEP ),
				'y_nghia'   => array( 'chu' => 'Một câu giải nghĩa tổng hợp để xem khối Ý nghĩa.', 'ai' => true, 'nguoi_lam' => NguoiLam::AI_DA_DUYET ),
				'chu_thich' => array(
					array( 'tu' => 'chút', 'loi_giai' => 'Chú thích tổng hợp để xem giao diện (từ có trong lời: chạm để xem).', 'nguoi_lam' => NguoiLam::MAY_CHEP ),
					array( 'loi_giai' => 'Chú thích không ghi từ, người biên soạn viết.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				),
				'di_ban'    => array( array( 'dong' => array( 'Dòng mẫu khác của bài này', 'Dòng mẫu hai khác dài hơn cho vừa' ), 'nguoi_lam' => NguoiLam::MAY_CHEP ) ),
			),
		),
		'cau-mau-thu-hai'      => array(
			'the_loai' => 'ca-dao',
			'tieu_de'  => 'Câu mẫu thứ hai',
			'chu_de'   => array( 'Lao động' ),
			'phan'     => array(
				'loi' => array( 'dong' => array( 'Câu mẫu thứ hai sáu chữ', 'Câu tám chữ mẫu viết cho đủ dòng' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
		'tuc-ngu-mau-ngan'     => array(
			'the_loai' => 'tuc-ngu',
			'tieu_de'  => 'Tục ngữ mẫu ngắn',
			'phan'     => array(
				'loi'        => array( 'dong' => array( 'Tục ngữ mẫu ngắn một dòng' ), 'nguoi_lam' => NguoiLam::MAY_CHEP ),
				'y_nghia'    => array( 'chu' => 'Một câu giải nghĩa tổng hợp để xem khối Ý nghĩa.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'nghia_den'  => array( 'chu' => 'Nội dung thử để kiểm thứ bậc các lớp nghĩa.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'nghia_bong' => array( 'chu' => 'Nội dung thử, không phải diễn giải một câu dân gian thật.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'dung_khi'   => array( 'chu' => "Ví dụ mẫu thứ nhất do người biên soạn duyệt.\nVí dụ mẫu thứ hai; mẫu này chỉ kiểm bố cục.", 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
		'dong-dao-mau'         => array(
			'the_loai' => 'dong-dao',
			'tieu_de'  => 'Đồng dao mẫu',
			'phan'     => array(
				'loi' => array( 'dong' => array( 'Đồng dao mẫu một', 'Đồng dao mẫu hai', 'Dòng mẫu thứ ba', 'Dòng mẫu thứ tư' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
		've-mau'               => array(
			'the_loai' => 've',
			'tieu_de'  => 'Vè mẫu',
			'phan'     => array(), // vè dài: dựng ở ve_dai()
		),
		'cau-do-mau'           => array(
			'the_loai' => 'cau-do',
			'tieu_de'  => 'Câu đố mẫu',
			'phan'     => array(
				'loi'    => array( 'dong' => array( 'Câu đố mẫu dòng một', 'Câu đố mẫu dòng hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'goi_y'  => array( 'chu' => 'Gợi ý tổng hợp để kiểm tương tác mở từng lớp.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'dap_an' => array( 'chu' => 'Đáp án mẫu', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
	);

	/**
	 * Mục từ và tuyển tập mẫu (trang Từ điển, Tuyển tập) — gọi sau gieo(). Tuyển tập mẫu loại Tình huống (ngưỡng công
	 * khai 2 câu, bộ mẫu chỉ có 6 tác phẩm). @return array{muc_tu:int,tuyen_tap:int}
	 */
	public static function gieo_tra_cuu( int $nguoi ): array {
		$bai = self::gieo( $nguoi );
		$co  = term_exists( 'chut', 'muc_tu' );
		$mt  = $co ? (int) $co['term_id'] : (int) ( wp_insert_term( 'chút', 'muc_tu', array( 'slug' => 'chut', 'description' => 'Chú thích tổng hợp để xem giao diện (từ có trong lời: chạm để xem).' ) )['term_id'] ?? 0 );
		foreach ( array( 'dong-mau-mot-cua-bai', 'tuc-ngu-mau-ngan' ) as $ten ) {
			if ( $mt && isset( $bai[ $ten ] ) ) {
				wp_set_object_terms( $bai[ $ten ], $mt, 'muc_tu', true );
			}
		}
		$tt = get_page_by_path( 'gui-mot-loi-nhan-mau', OBJECT, KhaiBao::TUYEN_TAP );
		$id = $tt ? $tt->ID : (int) wp_insert_post( wp_slash( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'publish', 'post_name' => 'gui-mot-loi-nhan-mau', 'post_title' => 'Gửi một lời nhắn (mẫu)', 'post_author' => $nguoi ) ) );
		if ( $id && ! $tt ) {
			$nc = array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
			LuuPhan::luu( $id, 'thong_tin', array( 'loai' => 'tinh_huong' ), $nc );
			LuuPhan::luu( $id, 'loi_dan', array( 'chu' => 'Lời dẫn tổng hợp để xem bố cục tuyển tập đọc liền.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc );
			LuuPhan::luu( $id, 'muc', array_map( static fn( int $b ): array => array( 'tac_pham_id' => $b, 'ghi_chu' => 'Ghi chú mẫu cho câu này.' ), array_values( $bai ) ), $nc );
		}
		// Một địa danh tổng hợp đã "xác nhận" có toạ độ (trang Bản đồ); toạ độ tự đặt, không phải dữ liệu Wikidata thật
		$dd = term_exists( 'ben-mau-bac', 'muc_tu' );
		$dd = $dd ? (int) $dd['term_id'] : (int) ( wp_insert_term( 'bến Mẫu Bắc', 'muc_tu', array( 'slug' => 'ben-mau-bac', 'description' => 'Địa danh tổng hợp ở phía bắc để xem bản đồ.' ) )['term_id'] ?? 0 );
		if ( $dd && isset( $bai['dong-dao-mau'] ) ) {
			wp_set_object_terms( $bai['dong-dao-mau'], $dd, 'muc_tu', true );
			update_term_meta( $dd, '_phongdao_wikidata', array( 'wd' => 'Q0', 'nhan' => 'bến Mẫu Bắc', 'mo_ta' => 'địa danh tổng hợp để xem giao diện', 'toa_do' => array( 21.0, 105.8 ) ) );
		}
		// Một bài cảm nhận đã duyệt (trang R23, danh sách cuối trang bài)
		$pt = get_page_by_path( 'mot-cach-doc-mau', OBJECT, KhaiBao::PHAN_TICH );
		$pt = $pt ? $pt->ID : (int) wp_insert_post(
			wp_slash(
				array(
					'post_type'    => KhaiBao::PHAN_TICH,
					'post_status'  => 'publish',
					'post_name'    => 'mot-cach-doc-mau',
					'post_title'   => 'Một cách đọc bài mẫu',
					'post_content' => \Phongdao\MienDuLieu\GopNguoiDoc::van_ban( "Nội dung tổng hợp để xem cách trình bày bài cảm nhận đã duyệt.\n\n> Dòng mẫu một của bài này\n\nPhần người viết bàn luận được tách rõ khỏi lời tác phẩm." ),
					'post_author'  => $nguoi,
					'meta_input'   => array(
						'_cadao_bai'      => $bai['dong-mau-mot-cua-bai'] ?? 0,
						'_cadao_but_danh' => 'Bút danh mẫu',
						'_cadao_vai_tro'  => 'giao_vien',
					),
				)
			)
		);
		return array( 'muc_tu' => $mt, 'tuyen_tap' => $id, 'phan_tich' => $pt );
	}

	/**
	 * Gợi ý chủ đề mẫu cho bản xem trước (màn Bổ sung → duyệt gợi ý theo nhóm, T-189.4): mỗi bài mẫu đã đăng chưa có chủ đề
	 * "Lao động" nhận một đề xuất máy chờ duyệt. Chỉ blueprint xem trước gọi (qua gieo_kho); không chạy trên site thật.
	 */
	private static function gieo_goi_y(): void {
		$t = get_term_by( 'name', 'Lao động', 'chu_de' );
		if ( ! $t instanceof \WP_Term ) {
			return;
		}
		foreach ( get_posts( array( 'post_type' => \Phongdao\HaTang\KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'numberposts' => 20, 'fields' => 'ids' ) ) as $id ) {
			if ( ! has_term( (int) $t->term_id, 'chu_de', (int) $id ) && ! \Phongdao\KhoLuu\Viec::mo( 'post', (int) $id, 'goi_y' ) ) {
				\Phongdao\KhoLuu\Viec::tao( array( 'loai' => 'goi_y', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => (int) $id, 'noi_dung' => array( 'loai' => 'chu_de', 'ma' => $t->slug, 'ly_do' => 'gợi ý mẫu của bản xem trước' ), 'nguoi_tao' => 'may' ) );
			}
		}
	}

	/**
	 * Câu kho mẫu cho bản xem trước (màn Kho wp-admin, T-184): chữ tự đặt, không phải lời thật; nguồn "mau" không có hồ sơ.
	 * Chỉ blueprint xem trước gọi; không chạy trên site thật.
	 *
	 * @return int[] ID kho
	 */
	public static function gieo_kho(): array {
		self::gieo_goi_y();
		$ra = array();
		// Chữ mỗi câu khác hẳn nhau (máy chống trùng không coi là cùng một câu); là danh sách từ, không phải lời dân gian
		$mau = array(
			'ca_dao'            => array( 'Câu kho mẫu', "Kho mẫu một táo lê đào mận\nnhãn vải hồng xoài na ổi" ),
			'thanh_ngu_tuc_ngu' => array( 'Tục ngữ kho mẫu', "Kho mẫu hai xanh đỏ tím vàng\ncam lục chàm trắng nâu đen" ),
			'cau_do'            => array( 'Câu đố kho mẫu', "Kho mẫu ba đông tây nam bắc\nsáng trưa chiều tối nửa đêm" ),
		);
		foreach ( $mau as $loai => [ $ten, $loi ] ) {
			\Phongdao\KhoLuu\Kho::luu(
				'mau',
				array(
					'ma_goc'    => 'kho-mau-' . $loai,
					'url'       => 'https://example.org/kho-mau/' . $loai . '/',
					'loai'      => $loai,
					'tieu_de'   => $ten,
					'loi'       => $loi,
					'doc_trang' => gmdate( 'Y-m-d' ),
				)
			);
			global $wpdb;
			$ra[] = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . \Phongdao\KhoLuu\Kho::bang() . ' WHERE nguon_id = %s AND ma_goc = %s', 'mau', 'kho-mau-' . $loai ) );
		}
		return $ra;
	}

	/**
	 * Vè mẫu dài: 48 dòng đánh số, ba đoạn có tiêu đề, một chỗ ngắt khổ, bối cảnh — xem cách hiện bài dài (tóm tắt, mục
	 * lục, "Đọc toàn bài"). Chữ mẫu tự đặt, không phải lời vè thật.
	 */
	private static function ve_dai(): array {
		$dong = array( 'Vè mẫu dòng một kể chuyện', 'Vè mẫu dòng hai kể tiếp', 'Vè mẫu dòng ba kể nữa', 'Vè mẫu dòng bốn kết lại' );
		for ( $i = 5; $i <= 48; $i++ ) {
			$dong[] = "Dòng mẫu $i kể tiếp chuyện";
			if ( 8 === $i ) {
				$dong[] = '';
			}
		}
		return array(
			'loi'      => array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			'boi_canh' => array( 'chu' => 'Bối cảnh tổng hợp để xem cách hiện một bài vè dài.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			'doan'     => array(
				array( 'tieu_de' => 'Phần mẫu thứ nhất', 'tu_dong' => 1, 'den_dong' => 16 ),
				array( 'tieu_de' => 'Phần mẫu thứ hai', 'tu_dong' => 17, 'den_dong' => 32 ),
				array( 'tieu_de' => 'Phần mẫu thứ ba', 'tu_dong' => 33, 'den_dong' => 48 ),
			),
		);
	}

	/** @return array<string,int> tên đường dẫn => ID các tác phẩm mẫu (đã có thì giữ) */
	public static function gieo( int $nguoi ): array {
		$ra = array();
		$anh = self::anh_mau();
		foreach ( self::BAI as $ten => $bai ) {
			$co = get_page_by_path( $ten, OBJECT, KhaiBao::TAC_PHAM );
			if ( $co ) {
				$ra[ $ten ] = $co->ID;
				continue;
			}
			$id = wp_insert_post(
				wp_slash(
					array(
						'post_type'   => KhaiBao::TAC_PHAM,
						'post_status' => 'publish',
						'post_name'   => $ten,
						'post_title'  => $bai['tieu_de'],
						'post_author' => $nguoi,
					)
				)
			);
			if ( ! $id ) {
				continue;
			}
			wp_set_object_terms( $id, $bai['the_loai'], KhaiBao::THE_LOAI );
			wp_set_object_terms( $id, $bai['chu_de'] ?? array(), 'chu_de' );
			foreach ( ( 've-mau' === $ten ? self::ve_dai() : $bai['phan'] ) as $phan => $gia_tri ) {
				LuuPhan::luu( $id, $phan, $gia_tri, array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
			}
			$ra[ $ten ] = $id;
			if ( $anh ) {
				LuuPhan::luu( $id, 'media', array( array( 'attachment_id' => $anh, 'loai' => 'anh' ) ), array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI ) );
			}
		}
		return $ra;
	}

	/** Ảnh lưới kỹ thuật trong blueprint; không phải ảnh minh hoạ văn hoá hoặc một xác nhận bản quyền. */
	private static function anh_mau(): int {
		$co = get_page_by_path( 'ux-test-anh-ky-thuat', OBJECT, 'attachment' );
		if ( $co ) {
			return (int) $co->ID;
		}
		$tep = dirname( __DIR__, 3 ) . '/du-lieu-mau/anh-ky-thuat.png';
		if ( ! is_file( $tep ) ) {
			return 0;
		}
		$upload = wp_upload_bits( 'ux-test-anh-ky-thuat.png', null, file_get_contents( $tep ) );
		if ( $upload['error'] ) {
			return 0;
		}
		$id = (int) wp_insert_attachment( array( 'post_title' => '[UX-TEST] Ảnh lưới kỹ thuật', 'post_name' => 'ux-test-anh-ky-thuat', 'post_mime_type' => 'image/png', 'post_excerpt' => '[UX-TEST] Ảnh kiểm giao diện, không phải tư liệu văn hoá.', 'post_status' => 'inherit' ), $upload['file'] );
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		update_post_meta( $id, '_wp_attachment_image_alt', '[UX-TEST] Lưới chữ nhật hai màu' );
		return $id;
	}
}
