<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Bổ sung** trong wp-admin (D-103, docs/32 mục 3.5): làm bài đã đăng đầy đủ hơn, không chỉ tiêu. Đọc từ bảng tóm tắt
 * bài. Bài đọc nhiều chưa có Ý nghĩa lên đầu; danh sách ưu tiên (thiếu Ý nghĩa, tục ngữ thiếu nghĩa, câu đố thiếu đáp án
 * rồi thiếu giải thích, thiếu từ khó, thiếu đối chiếu sách, có cờ) xếp bài đọc nhiều trước; nút "Bắt đầu" mở bài đầu ở
 * trang Sửa tác phẩm kèm lối "Bài tiếp theo". Chỉ đọc và dẫn đường; máy không viết nội dung, không tự gắn phân loại.
 */
final class BoSung {

	public const TRANG = 'phongdao-bo-sung';

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::TRANG );
	}

	/**
	 * Bài đọc nhiều 7 ngày qua mà chưa có Ý nghĩa (tối đa $so bài, đọc nhiều trước).
	 *
	 * @return int[]
	 */
	public static function doc_nhieu_thieu( int $so = 10 ): array {
		global $wpdb;
		// Đọc từ bảng tóm tắt bài (lượt đọc 7 ngày tính sẵn): một truy vấn, không đọc từng bài
		return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . \Phongdao\DichVu\TomTat\TomTat::bang() . " WHERE trang_thai = 'publish' AND doc_7 > 0 AND thieu LIKE %s ORDER BY doc_7 DESC, post_id ASC LIMIT %d", '%,y_nghia,%', $so ) ) ?: array() ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng của plugin
	}

	/**
	 * Danh sách ưu tiên: [tên, số, đường dẫn danh sách, đường dẫn bắt đầu] — "bắt đầu" mở bài đầu tiên của danh sách ở trang
	 * sửa, giữ lối về đúng danh sách đã lọc để sang bài kế.
	 *
	 * @return array<int,array{0:string,1:int,2:string,3:string}>
	 */
	public static function uu_tien(): array {
		$ra = array();
		foreach (
			array(
				array( 'Bài đã đăng chưa có Ý nghĩa', array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia', 'xep' => 'doc' ) ),
				array( 'Tục ngữ thiếu nghĩa đen / nghĩa bóng', array( 'tt' => 'publish', 'muc' => 'thieu_nghia_tn', 'xep' => 'doc' ) ),
				array( 'Câu đố thiếu đáp án', array( 'tt' => 'publish', 'muc' => 'thieu_dap_an', 'xep' => 'doc' ) ),
				array( 'Câu đố thiếu giải thích đáp án', array( 'tt' => 'publish', 'muc' => 'thieu_giai_thich', 'xep' => 'doc' ) ),
				array( 'Thiếu từ khó', array( 'tt' => 'publish', 'muc' => 'thieu_tu_kho', 'xep' => 'doc' ) ),
				array( 'Thiếu đối chiếu sách', array( 'tt' => 'publish', 'muc' => 'thieu_doi_chieu', 'xep' => 'doc' ) ),
				array( 'Bài có cờ nghi cần xem lại', array( 'muc' => 'co_co' ) ),
			) as [ $ten, $loc ]
		) {
			$so   = DanhSachTacPham::dem_loc( $loc );
			$ds   = DanhSachTacPham::url_loc( $loc );
			$dau  = $so ? (int) ( DanhSachTacPham::ids_loc( $loc, 1 )[0] ?? 0 ) : 0;
			$ra[] = array( $ten, $so, $ds, $dau ? SuaTacPham::url( $dau, $ds ) : $ds );
		}
		$goi_y = Viec::dem( Viec::CHO, 'goi_y' );
		$ra[]  = array( 'Gợi ý chủ đề, vùng của máy chờ xem', $goi_y, DanhSachViec::url( array( 'loai' => 'goi_y' ) ), DanhSachViec::url( array( 'loai' => 'goi_y' ) ) );
		return $ra;
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		$doc = self::doc_nhieu_thieu();
		$o   = '';
		foreach ( $doc as $id ) {
			$o .= '<li><a href="' . esc_url( SuaTacPham::url( $id ) ) . '">' . esc_html( get_the_title( $id ) ?: 'Bài #' . $id ) . '</a></li>';
		}
		$hang = '';
		foreach ( self::uu_tien() as [ $ten, $so, $ds, $dau ] ) {
			$hang .= '<tr><th scope="row">' . esc_html( $ten ) . '</th><td>' . ( $so ? '<a href="' . esc_url( $ds ) . '">' . esc_html( number_format_i18n( $so ) ) . '</a>' : '0' ) . '</td>'
				. '<td>' . ( $so ? '<a class="button" href="' . esc_url( $dau ) . '" aria-label="' . esc_attr( 'Bắt đầu: ' . $ten ) . '">Bắt đầu</a>' : '—' ) . '</td></tr>';
		}
		return '<div class="wrap phongdao-ban phongdao-bo-sung"><h1>Bổ sung</h1>'
			. '<p class="description">Làm bài đã đăng đầy đủ hơn: Ý nghĩa, từ khó, dị bản, đối chiếu sách. Bài đọc nhiều lên đầu. Không có chỉ tiêu phải điền kín cả kho; viết xong một bài, lưu và sang bài kế.</p>'
			. '<h2>Bài đọc nhiều tuần này chưa có Ý nghĩa</h2>'
			. ( $o ? '<ol class="phongdao-nen-lam">' . $o . '</ol>' : '<p>Tuần này chưa có bài đọc nhiều nào thiếu Ý nghĩa.</p>' )
			. '<h2>Danh sách ưu tiên</h2><table class="widefat striped phongdao-ban-so-lieu"><caption class="screen-reader-text">Việc bổ sung theo loại</caption><thead><tr><th scope="col">Việc</th><th scope="col">Số bài</th><th scope="col">Làm</th></tr></thead><tbody>' . $hang . '</tbody></table>'
			. '<p class="description">Máy chỉ đếm và xếp thứ tự; không viết nội dung, không tự gắn chủ đề hay vùng.</p></div>';
	}
}
