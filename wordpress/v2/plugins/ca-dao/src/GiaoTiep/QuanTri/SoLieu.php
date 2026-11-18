<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\LuuPhan\DuongDan;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\DichVu\XepViec\SoViec;
use Phongdao\GiaoTiep\BienTap\SoLieuTuan;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Số liệu** (D-121, docs/42 mục 3; đường dẫn cũ `/bien-tap/so-lieu/` chuyển về đây): các bảng kê trước nằm thu gọn
 * ở cuối Hôm nay — kho theo thể loại và cấp chất lượng, lượt đọc 7 ngày, ngoại lệ dữ liệu, hồ sơ nguồn chưa rõ, việc vào
 * / ra và tuổi thẻ của Hộp việc. Chỉ tính khi mở màn này (Hôm nay mở nhanh). Chỉ đọc; không phải chỉ tiêu.
 */
final class SoLieu {

	public const TRANG = 'phongdao-so-lieu';

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::TRANG );
	}

	public static function hien( ?int $bay_gio = null ): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		$bay_gio = $bay_gio ?? time();
		return '<div class="wrap pq-man phongdao-so-lieu"><p class="pq-duong"><a href="' . esc_url( BanLamViec::url() ) . '">Hôm nay</a> › Số liệu</p>'
			. '<div class="pq-dau"><div class="pq-dau-chu"><h1>Số liệu</h1><p>Số đếm để theo dõi kho và Hộp việc, tính lúc mở trang. Không phải chỉ tiêu: bài nào cần thì làm. Mỗi số mở đúng danh sách để làm tiếp.</p></div></div>'
			. '<div class="pq-luoi hai">' . self::khoi_kho() . '<section class="pq-khoi">' . SoLieuTuan::doc_7_ngay( 2 ) . '</section></div>'
			. self::khoi_ngoai_le()
			. '<section class="pq-khoi" aria-labelledby="pq-vao-ra">' . self::khoi_vao_ra( $bay_gio ) . self::khoi_tuoi( $bay_gio ) . '</section>'
			. '</div>';
	}

	private static function bang( string $chu_thich, array $ds ): string {
		$hang = implode( '', array_map( static fn( string $nhan, int $n ): string => '<tr><th scope="row">' . esc_html( $nhan ) . '</th><td>' . esc_html( number_format_i18n( $n ) ) . '</td></tr>', array_keys( $ds ), $ds ) );
		return '<table class="widefat striped pq-bang"><caption class="screen-reader-text">' . esc_html( $chu_thich ) . '</caption><tbody>' . $hang . '</tbody></table>';
	}

	/** Kho: số bài đã đăng, theo thể loại, theo cấp chất lượng (một truy vấn gộp trên bảng tóm tắt bài). */
	private static function khoi_kho(): string {
		$so   = SoLieuTuan::so();
		$loai = array();
		foreach ( \Phongdao\DichVu\DungTrang\DuLieuTrangChu::so_bai_the_loai() as $ma => $n ) {
			$loai[ \Phongdao\MienDuLieu\TheLoai::DS[ $ma ] ?? $ma ] = (int) $n;
		}
		$cap = array_combine( array( 'L0 · có lời', 'L1 · có thể loại', 'L2 · có ý nghĩa', 'L3 · có đối chiếu sách / bối cảnh' ), array_map( 'intval', $so['cap'] ) );
		return '<section class="pq-khoi" aria-labelledby="pq-kho"><h2 id="pq-kho">Kho bài</h2><p>' . esc_html( number_format_i18n( $so['bai'] ) ) . ' tác phẩm đã đăng · ' . esc_html( number_format_i18n( $so['cap'][2] + $so['cap'][3] ) ) . ' bài đủ cấp L2 · ' . esc_html( number_format_i18n( $so['kho'] ) ) . ' câu trong Kho chưa xử lý.</p>'
			. '<h3>Theo thể loại</h3>' . self::bang( 'Số bài đã đăng theo thể loại', $loai )
			. '<h3>Theo cấp chất lượng</h3>' . self::bang( 'Số bài đã đăng theo cấp chất lượng', $cap ) . '<p class="description">Cấp tính tự động, chỉ người biên soạn thấy, không chặn đăng.</p></section>';
	}

	/**
	 * Hồ sơ nguồn đang được bài dùng mà còn thiếu loại nguồn hoặc giấy phép. Đếm bài dùng của mọi hồ sơ thiếu trong một
	 * lượt quét (trước: hai lượt quét post meta cho mỗi hồ sơ).
	 *
	 * @return array<int,array{id:int,ten:string,thieu:string[],bai:int}>
	 */
	public static function nguon_chua_ro(): array {
		$thieu = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'any', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) ) as $hs ) {
			$t = HoSo::truong( $hs->ID );
			$k = array_keys( array_filter( array( 'loại nguồn' => '' === (string) ( $t['loai'] ?? '' ), 'giấy phép' => '' === trim( (string) ( $t['giay_phep'] ?? '' ) ) ) ) );
			if ( $k ) {
				$thieu[ $hs->ID ] = array( $hs, $k );
			}
		}
		$dem = NguonCaiDat::dem_bai_dung( array_keys( $thieu ) );
		$ra  = array();
		foreach ( $thieu as $id => [ $hs, $k ] ) {
			if ( ( $dem[ $id ] ?? 0 ) > 0 ) {
				$ra[] = array( 'id' => (int) $id, 'ten' => $hs->post_title, 'thieu' => $k, 'bai' => $dem[ $id ] );
			}
		}
		return $ra;
	}

	private static function hop_viec( array $q = array() ): string {
		return add_query_arg( array( 'page' => DanhSachViec::TRANG ) + $q, admin_url( 'admin.php' ) );
	}

	private static function khoi_ngoai_le(): string {
		$mo_trung = Viec::dem( Viec::CHO, 'co_the_trung' ) + Viec::dem( Viec::DE_SAU, 'co_the_trung' );
		$dong     = array(
			array( 'Đã đăng mà thiếu lời', DanhSachTacPham::dem_loc( array( 'tt' => 'publish', 'muc' => 'thieu_loi' ) ), DanhSachTacPham::url_loc( array( 'tt' => 'publish', 'muc' => 'thieu_loi' ) ) ),
			array( 'Đã đăng mà chưa có ý nghĩa (không phải chỉ tiêu)', DanhSachTacPham::dem_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) ), DanhSachTacPham::url_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) ) ),
			array( 'Có cờ nghi', DanhSachTacPham::dem_loc( array( 'muc' => 'co_co' ) ), DanhSachTacPham::url_loc( array( 'muc' => 'co_co' ) ) ),
			array( 'Cặp câu nghi trùng chưa quyết (Chờ + Để sau)', $mo_trung, self::hop_viec( array( 'loai' => 'co_the_trung' ) ) ),
			array( 'Đường dẫn dùng chung cho nhiều bài đã đăng', count( DuongDan::trung() ), DanhSachTacPham::url_loc( array() ) ),
		);
		$hang = '';
		foreach ( $dong as [ $nhan, $so, $url ] ) {
			$hang .= '<tr><th scope="row">' . esc_html( $nhan ) . '</th><td>' . ( $so ? '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( number_format_i18n( $so ) . ' — ' . $nhan ) . '">' . number_format_i18n( $so ) . '</a>' : '0' ) . '</td></tr>';
		}
		$quyen = current_user_can( NguonCaiDat::QUYEN );
		$ds_ng = '';
		foreach ( self::nguon_chua_ro() as $n ) {
			$ten    = esc_html( $n['ten'] );
			$ds_ng .= '<li>' . ( $quyen ? '<a href="' . esc_url( add_query_arg( array( 'page' => Module::TRANG, 'the' => 'ho_so', 'ho_so' => $n['id'] ), admin_url( 'admin.php' ) ) ) . '">' . $ten . '</a>' : $ten ) . ' — thiếu ' . esc_html( implode( ', ', $n['thieu'] ) ) . ' · ' . number_format_i18n( $n['bai'] ) . ' bài dùng</li>';
		}
		return '<div class="pq-luoi hai"><section class="pq-khoi" aria-labelledby="pq-ngoai-le"><h2 id="pq-ngoai-le">Ngoại lệ dữ liệu</h2><table class="widefat striped pq-bang"><caption class="screen-reader-text">Số bài và việc thuộc từng loại ngoại lệ</caption><thead><tr><th scope="col">Loại</th><th scope="col">Số</th></tr></thead><tbody>' . $hang . '</tbody></table></section>'
			. '<section class="pq-khoi" aria-labelledby="pq-nguon"><h2 id="pq-nguon">Hồ sơ nguồn chưa rõ</h2>' . ( $ds_ng ? '<p>Hồ sơ đang được bài dùng mà còn thiếu loại nguồn hoặc giấy phép. Chỉ người biên soạn biết điền gì; máy không điền.</p><ul>' . $ds_ng . '</ul>' : '<p>Không có hồ sơ nguồn nào đang dùng mà còn thiếu loại hoặc giấy phép.</p>' ) . '</section></div>';
	}

	private static function khoi_vao_ra( int $bay_gio ): string {
		$so   = SoViec::vao_ra( $bay_gio );
		$mo   = Viec::dem_loai( Viec::CHO );
		$sau  = Viec::dem_loai( Viec::DE_SAU );
		$tong = array( 0, 0, 0, 0, 0 );
		$hang = '';
		foreach ( QuyetViec::LOAI as $loai => [ $nhan ] ) {
			$s    = $so[ $loai ];
			$m    = ( $mo[ $loai ] ?? 0 ) + ( $sau[ $loai ] ?? 0 );
			$dong = array( $s['vao'], $s['ra'], $s['vao_truoc'], $s['ra_truoc'], $m );
			foreach ( $dong as $i => $v ) {
				$tong[ $i ] += $v;
			}
			$hang .= '<tr><th scope="row"><a href="' . esc_url( self::hop_viec( array( 'loai' => $loai ) ) ) . '">' . esc_html( $nhan ) . '</a></th>' . implode( '', array_map( static fn( int $v ): string => '<td>' . number_format_i18n( $v ) . '</td>', $dong ) ) . '</tr>';
		}
		$chenh = $tong[0] - $tong[1];
		return '<h2 id="pq-vao-ra">Hộp việc: vào, ra</h2><p>7 ngày qua: <strong>' . number_format_i18n( $tong[0] ) . '</strong> thẻ mới, <strong>' . number_format_i18n( $tong[1] ) . '</strong> thẻ đã xong hoặc bỏ — Hộp việc ' . ( $chenh > 0 ? 'dày thêm ' . number_format_i18n( $chenh ) : ( $chenh < 0 ? 'vơi đi ' . number_format_i18n( -$chenh ) : 'giữ nguyên' ) ) . '.</p>'
			. '<div class="pq-cuon"><table class="widefat striped pq-bang"><caption class="screen-reader-text">Thẻ vào và ra theo loại</caption><thead><tr><th scope="col">Loại thẻ</th><th scope="col">Vào, 7 ngày qua</th><th scope="col">Ra, 7 ngày qua</th><th scope="col">Vào, 7 ngày trước đó</th><th scope="col">Ra, 7 ngày trước đó</th><th scope="col">Đang mở (Chờ + Để sau)</th></tr></thead><tbody>' . $hang . '</tbody>'
			. '<tfoot><tr><th scope="row">Cộng</th>' . implode( '', array_map( static fn( int $v ): string => '<td>' . number_format_i18n( $v ) . '</td>', $tong ) ) . '</tr></tfoot></table></div>';
	}

	private static function khoi_tuoi( int $bay_gio ): string {
		$t   = SoViec::tuoi( $bay_gio );
		$lau = SoViec::lau_nhat( 5 );
		$ten = DanhSachViec::ten_trang( $lau );
		$o   = '';
		foreach ( $lau as $v ) {
			$ngay = max( 0, intdiv( $bay_gio - (int) strtotime( (string) $v['tao_luc'] . ' UTC' ), DAY_IN_SECONDS ) );
			$o   .= '<li><a href="' . esc_url( self::hop_viec( array( 'viec' => (int) $v['id'], 'tt' => (string) $v['trang_thai'] ) ) ) . '">' . esc_html( ( QuyetViec::LOAI[ $v['loai'] ][0] ?? $v['loai'] ) . ' #' . (int) $v['id'] ) . '</a> · ' . esc_html( DanhSachViec::doi_tuong( $v, $ten ) ) . ' · mở ' . number_format_i18n( $ngay ) . ' ngày</li>';
		}
		return '<h3>Tuổi thẻ đang mở</h3><p>' . number_format_i18n( $t['tong'] ) . ' thẻ đang mở: dưới 1 ngày <strong>' . number_format_i18n( $t['duoi_1'] ) . '</strong> · 1–7 ngày <strong>' . number_format_i18n( $t['tu_1_7'] ) . '</strong> · 7–30 ngày <strong>' . number_format_i18n( $t['tu_7_30'] ) . '</strong> · trên 30 ngày <strong>' . number_format_i18n( $t['tren_30'] ) . '</strong>.</p>'
			. ( $o ? '<h3>Mở lâu nhất</h3><ul>' . $o . '</ul>' : '' );
	}
}
