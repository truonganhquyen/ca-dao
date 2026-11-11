<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\DoLuong\DoLuong;
use Phongdao\DichVu\LuuPhan\DuongDan;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\DichVu\XepViec\SoViec;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Bàn làm việc trong wp-admin (T-180, docs/31 mục 2; T-188): mở đầu bằng bốn ô số bấm được và nút "Bắt đầu duyệt" (mở thẻ
 * cần làm trước nhất ở Hộp việc, quyết xong tự sang thẻ kế); việc nên làm tính từ dữ liệu hiện có (mỗi dòng mở đúng danh
 * sách đã lọc), ngoại lệ dữ liệu, lối thử làm sâu 20–30 bài; cuối trang thu gọn việc vào / ra, tuổi thẻ và Số liệu tuần
 * (gộp từ `/bien-tap/so-lieu/`, D-100). Chỉ đọc; không đặt chỉ tiêu điền kín cả kho, không chọn bài hay viết nội dung
 * thay người biên soạn.
 */
final class BanLamViec {

	public const TRANG = 'phongdao-ban-lam-viec';

	public const HUONG_DAN = 'https://github.com/truonganhquyen/ca-dao/blob/main/docs/huong-dan/thu-lam-sau.md';

	public static function hien( ?int $bay_gio = null ): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		$bay_gio = $bay_gio ?? time();
		return '<div class="wrap phongdao-ban"><h1>Bàn làm việc</h1>'
			. self::khoi_dau() . self::khoi_nen_lam( $bay_gio ) . self::khoi_ngoai_le() . self::khoi_thu_sau()
			. '<details class="phongdao-ban-gon"><summary>Việc vào, ra và tuổi thẻ</summary>' . self::khoi_vao_ra( $bay_gio ) . self::khoi_tuoi( $bay_gio ) . '</details>'
			. '<details class="phongdao-ban-gon"><summary>Số liệu tuần: bài theo thể loại, cấp chất lượng, lượt đọc</summary>' . self::khoi_so_lieu() . '</details>'
			. '<p class="description">Tính từ dữ liệu lúc mở trang. Mỗi dòng mở đúng danh sách để làm tiếp. Không có chỉ tiêu phải điền kín cả kho: bài nào cần thì làm.</p></div>';
	}

	/** Bốn ô số bấm được và nút "Bắt đầu duyệt": mở thẻ cần làm trước nhất (ưu tiên cao, cũ trước) trong Hộp việc. */
	private static function khoi_dau(): string {
		$cho   = Viec::dem_cho();
		$loai  = Viec::dem_loai( Viec::CHO );
		$thieu = array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' );
		$o     = '';
		foreach (
			array(
				array( $cho, 'việc đang chờ', DanhSachViec::url( array() ) ),
				array( (int) ( $loai['cau_moi'] ?? 0 ), 'câu mới từ nguồn', DanhSachViec::url( array( 'loai' => 'cau_moi' ) ) ),
				array( DanhSachTacPham::dem_loc( $thieu ), 'bài đã đăng chưa có ý nghĩa', DanhSachTacPham::url_loc( $thieu ) ),
				array( (int) ( $loai['nguoi_doc'] ?? 0 ), 'nội dung người đọc gửi', DanhSachViec::url( array( 'loai' => 'nguoi_doc' ) ) ),
			) as [ $so, $nhan, $url ]
		) {
			$o .= '<li><a class="phongdao-ban-o" href="' . esc_url( $url ) . '"><strong>' . esc_html( number_format_i18n( (int) $so ) ) . '</strong> <span>' . esc_html( $nhan ) . '</span></a></li>';
		}
		$dau = $cho ? ( Viec::tim_quan_tri( Viec::CHO, '', '', 1, 1 )['ds'][0] ?? null ) : null;
		$nut = $dau
			? '<p class="phongdao-ban-bat-dau"><a class="button button-primary button-hero" href="' . esc_url( DanhSachViec::url( array(), 1, (int) $dau['id'] ) ) . '">Bắt đầu duyệt (' . esc_html( number_format_i18n( $cho ) ) . ' việc)</a> <span class="description">Mở thẻ cần làm trước nhất; quyết xong tự sang thẻ kế.</span></p>'
			: '<p class="phongdao-ban-bat-dau" role="status">Không còn việc chờ quyết.</p>';
		return '<ul class="phongdao-ban-so">' . $o . '</ul>' . $nut;
	}

	/** Số liệu tuần (gộp từ `/bien-tap/so-lieu/`): bài theo thể loại, theo cấp chất lượng, lượt đọc 7 ngày. */
	private static function khoi_so_lieu(): string {
		$so   = \Phongdao\GiaoTiep\BienTap\SoLieuTuan::so();
		$hang = static fn( array $ds ): string => implode( '', array_map( static fn( string $nhan, int $n ): string => '<tr><th scope="row">' . esc_html( $nhan ) . '</th><td>' . esc_html( number_format_i18n( $n ) ) . '</td></tr>', array_keys( $ds ), $ds ) );
		$loai = array();
		foreach ( \Phongdao\DichVu\DungTrang\DuLieuTrangChu::so_bai_the_loai() as $ma => $n ) {
			$loai[ \Phongdao\MienDuLieu\TheLoai::DS[ $ma ] ?? $ma ] = (int) $n;
		}
		$cap = array_combine( array( 'L0 · có lời', 'L1 · có thể loại', 'L2 · có ý nghĩa', 'L3 · có đối chiếu sách / bối cảnh' ), array_map( 'intval', $so['cap'] ) );
		return '<h2>Số liệu tuần</h2><p>' . esc_html( number_format_i18n( $so['bai'] ) ) . ' tác phẩm đã đăng · ' . esc_html( number_format_i18n( $so['cap'][2] + $so['cap'][3] ) ) . ' bài đủ cấp L2 (lên Google khi mở) · ' . esc_html( number_format_i18n( $so['kho'] ) ) . ' câu trong kho chưa xử lý.</p>'
			. '<h3>Theo thể loại</h3><table class="widefat striped phongdao-ban-so-lieu"><caption class="screen-reader-text">Số bài đã đăng theo thể loại</caption><tbody>' . $hang( $loai ) . '</tbody></table>'
			. '<h3>Theo cấp chất lượng</h3><table class="widefat striped phongdao-ban-so-lieu"><caption class="screen-reader-text">Số bài đã đăng theo cấp chất lượng</caption><tbody>' . $hang( $cap ) . '</tbody></table><p class="description">Cấp tính tự động, chỉ người biên soạn thấy, không chặn đăng.</p>'
			. \Phongdao\GiaoTiep\BienTap\SoLieuTuan::doc_7_ngay( 3 );
	}

	private static function hop_viec( array $q = array() ): string {
		return add_query_arg( array( 'page' => DanhSachViec::TRANG ) + $q, admin_url( 'admin.php' ) );
	}

	/**
	 * Tối đa 10 việc nên làm, theo thứ tự ưu tiên của docs/28 mục 4.1 (báo lỗi → mong có → ý nghĩa bài đọc nhiều → câu mới →
	 * có thể trùng → người đọc gửi khác → gợi ý → cần xem); bỏ việc đang là 0.
	 *
	 * @return array<int,array{so:int,chu:string,url:string,ds:array<int,array{0:string,1:string}>}> ds: [chữ, đường dẫn]
	 */
	public static function viec_nen_lam( int $bay_gio ): array {
		$nd      = SoViec::nguoi_doc_cho();
		$bao_loi = ( $nd['bao_loi'] ?? 0 ) + ( $nd['vi_pham'] ?? 0 );
		$cho     = Viec::dem_loai( Viec::CHO );
		$trung   = DuongDan::trung();
		$mong    = DoLuong::nhieu_nhat( 'mong_co_cau', 7, 20 );
		$tim     = DoLuong::nhieu_nhat( 'tim_khong_ket_qua', 7, 5 );
		$doc     = array_slice( array_filter( DoLuong::doc_nhieu( 7, 60 ), static fn( int $id ): bool => null === PhanBai::lay( $id, 'y_nghia' ), ARRAY_FILTER_USE_KEY ), 0, 10, true );
		$tuoi    = SoViec::tuoi( $bay_gio );
		$ten_kho = static fn( string $k ): string => (string) ( Kho::lay( (int) substr( $k, 4 ) )?->tieu_de ?? '' );
		$ds      = array(
			array( $bao_loi, $bao_loi . ' báo lỗi, báo vi phạm của người đọc đang chờ', self::hop_viec( array( 'loai' => 'nguoi_doc' ) ), array() ),
			array( count( $trung ), count( $trung ) . ' đường dẫn đang dùng chung cho nhiều bài — người đọc mất lối vào một bài', DanhSachTacPham::url_loc( array() ), array() ),
			array( count( $mong ), count( $mong ) . ' câu người đọc bấm "Mong có" trong 7 ngày (câu đang chờ trong kho)', DanhSachKho::url(), array_values( array_filter( array_map( static fn( string $k ): array => array( $ten_kho( $k ), DanhSachKho::url( array( 'q' => $ten_kho( $k ) ) ) ), array_slice( array_keys( $mong ), 0, 5 ) ), static fn( array $x ): bool => '' !== $x[0] ) ) ),
			array( count( $doc ), 'Viết ý nghĩa cho ' . count( $doc ) . ' bài đọc nhiều tuần này mà chưa có', DanhSachTacPham::url_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) ), array_map( static fn( int $id ): array => array( get_the_title( $id ), SuaTacPham::url( $id ) ), array_slice( array_keys( $doc ), 0, 5 ) ) ),
			array( count( $tim ), count( $tim ) . ' câu người đọc tìm mà chưa có (7 ngày) — xem kho có không', DanhSachKho::url(), array_map( static fn( string $q ): array => array( $q, DanhSachKho::url( array( 'q' => $q ) ) ), array_keys( $tim ) ) ),
			array( $cho['nhap_y_nghia'] ?? 0, ( $cho['nhap_y_nghia'] ?? 0 ) . ' nháp ý nghĩa chờ duyệt', self::hop_viec( array( 'loai' => 'nhap_y_nghia' ) ), array() ),
			array( $cho['cau_moi'] ?? 0, ( $cho['cau_moi'] ?? 0 ) . ' câu mới từ nguồn chờ quyết', self::hop_viec( array( 'loai' => 'cau_moi' ) ), array() ),
			array( $cho['co_the_trung'] ?? 0, ( $cho['co_the_trung'] ?? 0 ) . ' cặp câu có thể trùng chờ quyết', self::hop_viec( array( 'loai' => 'co_the_trung' ) ), array() ),
			array( array_sum( $nd ) - $bao_loi, ( array_sum( $nd ) - $bao_loi ) . ' nội dung người đọc gửi khác (dị bản, góp ý, cảm nghĩ, bài cảm nhận)', self::hop_viec( array( 'loai' => 'nguoi_doc' ) ), array() ),
			array( $cho['goi_y'] ?? 0, ( $cho['goi_y'] ?? 0 ) . ' gợi ý chủ đề, vùng chờ xem', self::hop_viec( array( 'loai' => 'goi_y' ) ), array() ),
			array( $cho['can_xem'] ?? 0, ( $cho['can_xem'] ?? 0 ) . ' bài "Cần xem" sau chuyển dữ liệu', self::hop_viec( array( 'loai' => 'can_xem' ) ), array() ),
			array( $tuoi['tren_30'], $tuoi['tren_30'] . ' thẻ đã mở quá 30 ngày', self::hop_viec(), array() ),
		);
		$ra = array();
		foreach ( $ds as [ $so, $chu, $url, $con ] ) {
			if ( $so > 0 && count( $ra ) < 10 ) {
				$ra[] = array( 'so' => (int) $so, 'chu' => $chu, 'url' => $url, 'ds' => $con );
			}
		}
		return $ra;
	}

	private static function khoi_nen_lam( int $bay_gio ): string {
		$ds = self::viec_nen_lam( $bay_gio );
		if ( ! $ds ) {
			return '<h2>Nên làm tuần này</h2><p>Hết việc gấp. Có thể mở <a href="' . esc_url( DanhSachTacPham::url_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) ) ) . '">bài chưa có ý nghĩa</a> nếu muốn làm thêm.</p>';
		}
		$o = '';
		foreach ( $ds as $x ) {
			$con = $x['ds'] ? '<ul>' . implode( '', array_map( static fn( array $c ): string => '<li><a href="' . esc_url( $c[1] ) . '">' . esc_html( $c[0] ) . '</a></li>', $x['ds'] ) ) . '</ul>' : '';
			$o  .= '<li><a href="' . esc_url( $x['url'] ) . '"><strong>' . esc_html( $x['chu'] ) . '</strong></a>' . $con . '</li>';
		}
		return '<h2>Nên làm tuần này</h2><ol class="phongdao-nen-lam">' . $o . '</ol>';
	}

	private static function khoi_vao_ra( int $bay_gio ): string {
		$so  = SoViec::vao_ra( $bay_gio );
		$mo  = Viec::dem_loai( Viec::CHO );
		$sau = Viec::dem_loai( Viec::DE_SAU );
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
		return '<h2>Việc vào, ra</h2><p>7 ngày qua: <strong>' . number_format_i18n( $tong[0] ) . '</strong> thẻ mới, <strong>' . number_format_i18n( $tong[1] ) . '</strong> thẻ đã xong hoặc bỏ — Hộp việc ' . ( $chenh > 0 ? 'dày thêm ' . number_format_i18n( $chenh ) : ( $chenh < 0 ? 'vơi đi ' . number_format_i18n( -$chenh ) : 'giữ nguyên' ) ) . '.</p>'
			. '<table class="widefat striped phongdao-vao-ra"><caption class="screen-reader-text">Thẻ vào và ra theo loại</caption><thead><tr><th scope="col">Loại thẻ</th><th scope="col">Vào, 7 ngày qua</th><th scope="col">Ra, 7 ngày qua</th><th scope="col">Vào, 7 ngày trước đó</th><th scope="col">Ra, 7 ngày trước đó</th><th scope="col">Đang mở (Chờ + Để sau)</th></tr></thead><tbody>' . $hang . '</tbody>'
			. '<tfoot><tr><th scope="row">Cộng</th>' . implode( '', array_map( static fn( int $v ): string => '<td>' . number_format_i18n( $v ) . '</td>', $tong ) ) . '</tr></tfoot></table>';
	}

	private static function khoi_tuoi( int $bay_gio ): string {
		$t    = SoViec::tuoi( $bay_gio );
		$lau  = SoViec::lau_nhat( 5 );
		$ten  = DanhSachViec::ten_trang( $lau );
		$o    = '';
		foreach ( $lau as $v ) {
			$ngay = max( 0, intdiv( $bay_gio - (int) strtotime( (string) $v['tao_luc'] . ' UTC' ), DAY_IN_SECONDS ) );
			$o   .= '<li><a href="' . esc_url( self::hop_viec( array( 'viec' => (int) $v['id'], 'tt' => (string) $v['trang_thai'] ) ) ) . '">' . esc_html( ( QuyetViec::LOAI[ $v['loai'] ][0] ?? $v['loai'] ) . ' #' . (int) $v['id'] ) . '</a> · ' . esc_html( DanhSachViec::doi_tuong( $v, $ten ) ) . ' · mở ' . number_format_i18n( $ngay ) . ' ngày</li>';
		}
		return '<h2>Tuổi thẻ đang mở</h2><p>' . number_format_i18n( $t['tong'] ) . ' thẻ đang mở: dưới 1 ngày <strong>' . number_format_i18n( $t['duoi_1'] ) . '</strong> · 1–7 ngày <strong>' . number_format_i18n( $t['tu_1_7'] ) . '</strong> · 7–30 ngày <strong>' . number_format_i18n( $t['tu_7_30'] ) . '</strong> · trên 30 ngày <strong>' . number_format_i18n( $t['tren_30'] ) . '</strong>.</p>'
			. ( $o ? '<h3>Mở lâu nhất</h3><ul>' . $o . '</ul>' : '' );
	}

	/**
	 * Hồ sơ nguồn đang được bài dùng mà còn thiếu loại nguồn hoặc giấy phép.
	 *
	 * @return array<int,array{id:int,ten:string,thieu:string[],bai:int}>
	 */
	public static function nguon_chua_ro(): array {
		$ra = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'any', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) ) as $hs ) {
			$t     = HoSo::truong( $hs->ID );
			$thieu = array_keys( array_filter( array( 'loại nguồn' => '' === (string) ( $t['loai'] ?? '' ), 'giấy phép' => '' === trim( (string) ( $t['giay_phep'] ?? '' ) ) ) ) );
			if ( $thieu ) {
				$bai = NguonCaiDat::anh_huong( $hs->ID )['tong'];
				if ( $bai > 0 ) {
					$ra[] = array( 'id' => $hs->ID, 'ten' => $hs->post_title, 'thieu' => $thieu, 'bai' => $bai );
				}
			}
		}
		return $ra;
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
		$nguon = self::nguon_chua_ro();
		$quyen = current_user_can( NguonCaiDat::QUYEN );
		$ds_ng = '';
		foreach ( $nguon as $n ) {
			$ten    = esc_html( $n['ten'] );
			$ds_ng .= '<li>' . ( $quyen ? '<a href="' . esc_url( add_query_arg( array( 'page' => Module::TRANG, 'the' => 'ho_so', 'ho_so' => $n['id'] ), admin_url( 'admin.php' ) ) ) . '">' . $ten . '</a>' : $ten ) . ' — thiếu ' . esc_html( implode( ', ', $n['thieu'] ) ) . ' · ' . number_format_i18n( $n['bai'] ) . ' bài dùng</li>';
		}
		return '<h2>Ngoại lệ dữ liệu</h2><table class="widefat striped phongdao-ngoai-le"><caption class="screen-reader-text">Số bài và việc thuộc từng loại ngoại lệ</caption><thead><tr><th scope="col">Loại</th><th scope="col">Số</th></tr></thead><tbody>' . $hang . '</tbody></table>'
			. '<h3>Hồ sơ nguồn chưa rõ</h3>' . ( $ds_ng ? '<p>Hồ sơ đang được bài dùng mà còn thiếu loại nguồn hoặc giấy phép. Chỉ người biên soạn biết điền gì; máy không điền.</p><ul>' . $ds_ng . '</ul>' : '<p>Không có hồ sơ nguồn nào đang dùng mà còn thiếu loại hoặc giấy phép.</p>' );
	}

	private static function khoi_thu_sau(): string {
		return '<h2>Thử làm sâu 20–30 bài</h2><p>Người biên tập tự chọn 20–30 bài để làm kỹ (ý nghĩa, từ khó, đối chiếu nguồn), ghi thời gian mỗi bài và chỗ khó. Gợi ý chọn từ "bài đọc nhiều chưa có ý nghĩa" ở trên. Máy không chọn thay, không viết nội dung. <a href="' . esc_url( self::HUONG_DAN ) . '">Cách ghi kết quả thử</a>.</p>';
	}
}
