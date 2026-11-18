<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\DoLuong\DoLuong;
use Phongdao\DichVu\LuuPhan\DuongDan;
use Phongdao\DichVu\UngVien\DanhDau;
use Phongdao\DichVu\UngVien\UngVien;
use Phongdao\DichVu\XepViec\SoViec;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Hôm nay** trong wp-admin (D-103; làm lại theo D-121, docs/42 mục 3): việc gấp gom một dòng; ba việc chính — Câu
 * mới chờ đăng · Việc cần quyết · Bài cần bổ sung — mỗi việc một số to và một nút; Tuần này (bốn số và tiến độ lên
 * Google); Đang làm dở (bài bạn vừa sửa, mở lại một chạm); Việc khác thu gọn. Mọi số đọc bằng vài truy vấn gộp (bảng tóm
 * tắt bài, bảng việc, đo lường) nên màn mở nhanh cả khi kho lớn; các bảng kê nặng (ngoại lệ dữ liệu, hồ sơ nguồn chưa rõ,
 * việc vào / ra, tuổi thẻ, bài theo cấp) ở màn Số liệu, chỉ tính khi mở màn đó. Chỉ đọc; không đặt chỉ tiêu điền kín
 * cả kho, không chọn bài hay viết nội dung thay người biên soạn.
 */
final class BanLamViec {

	/** Giữ đường dẫn cũ của Bàn làm việc (sổ tay, dấu trang, lối sau đăng nhập). */
	public const TRANG = 'phongdao-ban-lam-viec';

	/** Số đã đếm trong một lượt mở trang: nhiều khối dùng chung một số, mỗi số chỉ hỏi CSDL một lần. */
	private static array $nho = array();

	/** Tên ngày trong tuần (không phụ thuộc ngôn ngữ cài cho site). */
	private const THU = array( 'Chủ nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy' );

	/** Việc trong Hộp việc bằng chữ, cho dòng tóm tắt dưới số "Việc cần quyết". */
	private const LOAI_CHO = array(
		'co_the_trung' => '%s cặp câu có thể trùng',
		'nhap_y_nghia' => '%s nháp Ý nghĩa chờ duyệt',
		'cau_moi'      => '%s câu mới từ nguồn',
		'nguoi_doc'    => '%s nội dung bạn đọc gửi',
		'can_xem'      => '%s bài cần xem',
	);

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::TRANG );
	}

	public static function hien( ?int $bay_gio = null ): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		self::$nho = array();
		$bay_gio   = $bay_gio ?? time();
		$nen       = self::viec_nen_lam( $bay_gio );
		$so        = self::so_ba_dong();
		return '<div class="wrap pq-man phongdao-ban">' . self::dau( $so, $bay_gio )
			. self::khoi_gap( array_values( array_filter( $nen, static fn( array $x ): bool => $x['gap'] ) ) )
			. self::ba_viec( $so )
			. '<div class="pq-luoi hai">' . self::khoi_tuan( $bay_gio ) . self::khoi_dang_lam( $bay_gio ) . '</div>'
			. self::khoi_khac( array_values( array_filter( $nen, static fn( array $x ): bool => ! $x['gap'] ) ) )
			. '</div>';
	}

	/** Đọc một số đã đếm trong lượt này, chưa có thì đếm. */
	private static function nho( string $khoa, callable $dem ): mixed {
		if ( ! array_key_exists( $khoa, self::$nho ) ) {
			self::$nho[ $khoa ] = $dem();
		}
		return self::$nho[ $khoa ];
	}

	private static function dau( array $so, int $bay_gio ): string {
		$viec = $so['goi_y'] + $so['cho'] + ( current_user_can( 'cadao_duyet' ) ? $so['de_nghi'] : 0 );
		$ngay = self::THU[ (int) wp_date( 'w', $bay_gio ) ] . ', ' . wp_date( 'd/m/Y', $bay_gio );
		return '<div class="pq-dau"><div class="pq-dau-chu"><h1>Hôm nay</h1><p>' . esc_html( $ngay ) . ' · '
			. ( $viec ? 'Có <strong>' . esc_html( number_format_i18n( $viec ) ) . ' việc</strong> đang chờ. ' : '' ) . 'Bắt đầu từ ô bên trái; xong ô nào sang ô kế.'
			. '</p></div><div class="pq-dau-nut"><a class="button" href="' . esc_url( ThemTacPham::url() ) . '">＋ Thêm tác phẩm</a></div></div>';
	}

	/** Việc gấp (báo lỗi, vi phạm của người đọc; đường dẫn dùng chung; câu nghi trùng chờ quyết) gom một dòng ở đầu màn. */
	private static function khoi_gap( array $ds ): string {
		if ( ! $ds ) {
			return '';
		}
		$o = implode( ' · ', array_map( static fn( array $x ): string => '<a href="' . esc_url( $x['url'] ) . '">' . esc_html( $x['chu'] ) . '</a>', array_slice( $ds, 0, 3 ) ) );
		return '<p class="pq-gap"><strong>Gấp:</strong> ' . $o . '</p>';
	}

	/**
	 * Số của ba việc chính: câu máy gợi ý hôm nay, câu được đề nghị đăng, câu Kho còn đăng được (Bài mới); việc đang chờ
	 * (Hộp việc) và thẻ cần làm trước nhất; bài đã đăng chưa có ý nghĩa (Bổ sung).
	 *
	 * @return array{goi_y:int,de_nghi:int,kho:int,cho:int,dau:int,thieu:int}
	 */
	public static function so_ba_dong(): array {
		return self::nho(
			'ba_dong',
			static function (): array {
				$cho = Viec::dem_hop_viec();
				return array(
					'goi_y'   => self::goi_y(),
					'de_nghi' => self::de_nghi(),
					'kho'     => DanhSachKho::dem_chon( DanhSachKho::loc_tu( array() ) ),
					'cho'     => $cho,
					'dau'     => $cho ? (int) ( Viec::tim_quan_tri( Viec::CHO, '', '', 1, 1 )['ds'][0]['id'] ?? 0 ) : 0,
					'thieu'   => DanhSachTacPham::dem_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) ),
				);
			}
		);
	}

	private static function goi_y(): int {
		return self::nho( 'goi_y', static fn(): int => count( UngVien::goi_y_hom_nay()['ids'] ) );
	}

	private static function de_nghi(): int {
		return self::nho( 'de_nghi', static fn(): int => DanhDau::dem( 'de_nghi' ) );
	}

	private static function cho_loai(): array {
		return self::nho( 'cho_loai', static fn(): array => Viec::dem_loai( Viec::CHO ) );
	}

	/** Ba việc chính, mỗi việc một số to, một câu và một nút bắt đầu. */
	private static function ba_viec( array $so ): string {
		// Câu mới chờ đăng: ưu tiên câu máy gợi ý hôm nay; không có thì mọi câu Kho chưa đăng
		$goi    = $so['goi_y'] > 0;
		$loc    = $goi ? array( 'goi_y' => 'hom_nay' ) : array();
		$n_kho  = $goi ? $so['goi_y'] : $so['kho'];
		$de     = current_user_can( 'cadao_duyet' ) && $so['de_nghi'] > 0 ? ' <a href="' . esc_url( DanhSachKho::url( array( 'danh' => 'de_nghi' ) ) ) . '">' . esc_html( number_format_i18n( $so['de_nghi'] ) ) . ' câu được đề nghị đăng</a>.' : '';
		$cho    = array();
		foreach ( self::LOAI_CHO as $loai => $mau ) {
			$n = (int) ( self::cho_loai()[ $loai ] ?? 0 );
			if ( $n > 0 ) {
				$cho[] = sprintf( $mau, number_format_i18n( $n ) );
			}
		}
		$o = array(
			array( 'tim', 'Câu mới chờ đăng', $n_kho, $goi ? 'câu máy gợi ý hôm nay' : 'câu trong Kho chưa đăng', 'Đọc lời, viết Ý nghĩa nếu có, bấm Đăng — mỗi câu một thẻ.' . $de, $n_kho ? 'Đăng nhanh →' : 'Mở Bài mới', $n_kho ? DanhSachKho::url_nhanh( $loc ) : DanhSachKho::url() ),
			array( 'vang', 'Việc cần quyết', $so['cho'], 'việc trong Hộp việc', $cho ? esc_html( implode( ' · ', array_slice( $cho, 0, 3 ) ) ) . '.' : 'Không có việc nào chờ quyết.', $so['dau'] ? 'Bắt đầu duyệt →' : 'Mở Hộp việc', $so['dau'] ? DanhSachViec::url( array(), 1, $so['dau'] ) : DanhSachViec::url( array() ) ),
			array( 'luc', 'Bài cần bổ sung', $so['thieu'], 'bài đã đăng chưa có Ý nghĩa', 'Bài đọc nhiều làm trước; xong một bài, lưu rồi sang bài kế.', 'Bắt đầu bổ sung →', BoSung::url() ),
		);
		$ra = '';
		foreach ( $o as $i => [ $mau, $ten, $n, $nhan, $cau, $nut, $url ] ) {
			$ra .= '<section class="pq-khoi pq-viec ' . $mau . '" aria-labelledby="pq-viec-' . $i . '"><h2 id="pq-viec-' . $i . '">' . esc_html( $ten ) . '</h2>'
				. '<p class="pq-so-lon"><strong>' . esc_html( number_format_i18n( (int) $n ) ) . '</strong> <span>' . esc_html( $nhan ) . '</span></p>'
				. '<p>' . $cau . '</p><p class="pq-viec-nut"><a class="button' . ( $n ? ' button-primary' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $nut ) . '</a></p></section>';
		}
		return '<div class="pq-luoi ba">' . $ra . '</div>';
	}

	/**
	 * Số 7 ngày qua: bài đã đăng, bài được viết Ý nghĩa, nội dung bạn đọc gửi, lượt đọc; và số bài lên Google (có phần
	 * thư viện tự viết, D-117) trên số bài đã đăng.
	 *
	 * @return array{dang:int,y_nghia:int,gop:int,doc:int,lap:int,tong:int}
	 */
	public static function so_tuan( int $bay_gio ): array {
		global $wpdb;
		$tu = gmdate( 'Y-m-d H:i:s', $bay_gio - 7 * DAY_IN_SECONDS );
		return array(
			'dang'    => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_date_gmt >= %s", KhaiBao::TAC_PHAM, $tu ) ),
			'y_nghia' => (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT doi_tuong_id) FROM ' . Viec::bang() . " WHERE loai = %s AND doi_tuong_loai = 'post' AND phan = 'y_nghia' AND tao_luc >= %s", Viec::SUA, $tu ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng
			'gop'     => (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . ' WHERE loai = %s AND tao_luc >= %s', 'nguoi_doc', $tu ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng
			'doc'     => (int) array_sum( DoLuong::theo_ngay( 7 ) ),
			'lap'     => count( \Phongdao\GiaoTiep\Seo\Module::ds_lap() ),
			'tong'    => (int) ( wp_count_posts( KhaiBao::TAC_PHAM )->publish ?? 0 ),
		);
	}

	private static function khoi_tuan( int $bay_gio ): string {
		$s  = self::so_tuan( $bay_gio );
		$o  = '';
		foreach ( array( 'dang' => 'bài đã đăng', 'y_nghia' => 'bài được viết Ý nghĩa', 'gop' => 'nội dung bạn đọc gửi', 'doc' => 'lượt đọc' ) as $k => $nhan ) {
			$o .= '<li><strong>' . esc_html( number_format_i18n( $s[ $k ] ) ) . '</strong> <span>' . esc_html( $nhan ) . '</span></li>';
		}
		$lap = number_format_i18n( $s['lap'] ) . ' / ' . number_format_i18n( $s['tong'] );
		return '<section class="pq-khoi" aria-labelledby="pq-tuan"><h2 id="pq-tuan">Tuần này</h2><p class="pq-mo-ta">7 ngày qua</p><ul class="pq-so-lieu">' . $o . '</ul>'
			. '<p class="pq-mo-ta pq-lap">Bài lên Google (có phần thư viện tự viết): <strong>' . esc_html( $lap ) . '</strong></p>'
			. '<progress class="pq-tien-do" max="' . esc_attr( (string) max( 1, $s['tong'] ) ) . '" value="' . esc_attr( (string) min( $s['lap'], max( 1, $s['tong'] ) ) ) . '" aria-label="' . esc_attr( 'Bài lên Google: ' . $lap . ' bài' ) . '">' . esc_html( $lap ) . '</progress>'
			. '<p class="pq-lien"><a href="' . esc_url( SoLieu::url() ) . '">Số liệu chi tiết: kho theo cấp, ngoại lệ dữ liệu, Hộp việc vào / ra →</a></p></section>';
	}

	/**
	 * Bài bạn vừa sửa (theo nhật ký: lần lưu một phần, mới nhất trước), tối đa 3 bài, để mở lại làm tiếp.
	 *
	 * @return array<int,array{id:int,phan:string,luc:string}>
	 */
	public static function dang_lam( int $nguoi, int $so = 3 ): array {
		global $wpdb;
		$hang = $wpdb->get_results( $wpdb->prepare( 'SELECT doi_tuong_id, phan, tao_luc FROM ' . Viec::bang() . " WHERE loai = %s AND doi_tuong_loai = 'post' AND nguoi_quyet = %d ORDER BY id DESC LIMIT 60", Viec::SUA, $nguoi ), ARRAY_A ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng
		$ra   = array();
		foreach ( $hang as $h ) {
			$id = (int) $h['doi_tuong_id'];
			if ( $id && ! isset( $ra[ $id ] ) ) {
				$ra[ $id ] = array( 'id' => $id, 'phan' => (string) $h['phan'], 'luc' => (string) $h['tao_luc'] );
			}
		}
		_prime_post_caches( array_keys( $ra ), false, false );
		$ra = array_filter( $ra, static fn( array $x ): bool => in_array( get_post_type( $x['id'] ), array( KhaiBao::TAC_PHAM, KhaiBao::TUYEN_TAP ), true ) && 'trash' !== get_post_status( $x['id'] ) );
		return array_slice( array_values( $ra ), 0, $so );
	}

	private static function khoi_dang_lam( int $bay_gio ): string {
		$o = '';
		foreach ( self::dang_lam( get_current_user_id() ) as $x ) {
			$tt   = KhaiBao::TUYEN_TAP === get_post_type( $x['id'] );
			$url  = $tt ? SuaTuyenTap::url( array( 'id' => $x['id'] ) ) : SuaTacPham::url( $x['id'] );
			$phan = \Phongdao\GiaoTiep\BienTap\CheDoSua::TEN_PHAN[ $x['phan'] ] ?? '';
			$luc  = (int) strtotime( $x['luc'] . ' UTC' );
			$ten  = get_the_title( $x['id'] );
			$o   .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( '' === $ten ? '(chưa có tên)' : $ten ) . '</a><span>'
				. esc_html( ( $tt ? 'tuyển tập' : ( '' === $phan ? 'đã lưu' : 'đã lưu ' . $phan ) ) . ' · ' . self::luc_truoc( $luc, $bay_gio ) ) . '</span></li>';
		}
		return '<section class="pq-khoi" aria-labelledby="pq-dang-lam"><h2 id="pq-dang-lam">Đang làm dở</h2>'
			. ( '' === $o ? '<p class="pq-mo-ta">Chưa có bài nào bạn sửa gần đây. Bài bạn lưu sẽ hiện ở đây để mở lại làm tiếp.</p>' : '<ul class="pq-ds-phan">' . $o . '</ul>' ) . '</section>';
	}

	/** Lúc đã qua bằng chữ (không phụ thuộc ngôn ngữ cài cho site): vừa xong, 5 phút trước, 3 giờ trước, hôm qua, 4 ngày trước. */
	public static function luc_truoc( int $luc, int $bay_gio ): string {
		$giay = max( 0, $bay_gio - $luc );
		return match ( true ) {
			$giay < MINUTE_IN_SECONDS    => 'vừa xong',
			$giay < HOUR_IN_SECONDS      => intdiv( $giay, MINUTE_IN_SECONDS ) . ' phút trước',
			$giay < DAY_IN_SECONDS       => intdiv( $giay, HOUR_IN_SECONDS ) . ' giờ trước',
			$giay < 2 * DAY_IN_SECONDS   => 'hôm qua',
			$giay < 30 * DAY_IN_SECONDS  => intdiv( $giay, DAY_IN_SECONDS ) . ' ngày trước',
			default                      => wp_date( 'd/m/Y', $luc ),
		};
	}

	/** Việc không gấp trong tuần, thu gọn; kèm lối sang Số liệu và Lịch sử thao tác hàng loạt. */
	private static function khoi_khac( array $ds ): string {
		$o = '';
		foreach ( $ds as $x ) {
			$con = $x['ds'] ? '<ul>' . implode( '', array_map( static fn( array $c ): string => '<li><a href="' . esc_url( $c[1] ) . '">' . esc_html( $c[0] ) . '</a></li>', $x['ds'] ) ) . '</ul>' : '';
			$o  .= '<li><a href="' . esc_url( $x['url'] ) . '">' . esc_html( $x['chu'] ) . '</a>' . $con . '</li>';
		}
		$o .= '<li><a href="' . esc_url( SoLieu::url() ) . '">Số liệu và ngoại lệ dữ liệu</a> — kho theo cấp, hồ sơ nguồn chưa rõ, việc vào / ra, tuổi thẻ.</li>'
			. '<li><a href="' . esc_url( TrangLo::url() ) . '">Lịch sử thao tác hàng loạt</a> — các lô bạn đã chạy, kết quả từng mục.</li>';
		return '<details class="pq-khoi pq-gon"><summary>Việc khác trong tuần (không gấp)' . ( $ds ? ' <span class="pq-dem">' . esc_html( number_format_i18n( count( $ds ) ) ) . '</span>' : '' ) . '</summary><ul class="pq-ds-viec">' . $o . '</ul></details>';
	}

	private static function hop_viec( array $q = array() ): string {
		return add_query_arg( array( 'page' => DanhSachViec::TRANG ) + $q, admin_url( 'admin.php' ) );
	}

	/**
	 * Tối đa 10 việc nên làm, theo thứ tự ưu tiên của docs/28 mục 4.1 (báo lỗi → đường dẫn dùng chung → câu nghi trùng →
	 * mong có → ý nghĩa bài đọc nhiều → câu tìm chưa có → nháp → đề nghị đăng → gợi ý → câu mới → người đọc gửi khác → gợi ý
	 * chủ đề → cần xem → thẻ cũ); bỏ việc đang là 0. `gap`: việc gấp, gom một dòng ở đầu Hôm nay.
	 *
	 * @return array<int,array{so:int,chu:string,url:string,ds:array<int,array{0:string,1:string}>,gap:bool}> ds: [chữ, đường dẫn]
	 */
	public static function viec_nen_lam( int $bay_gio ): array {
		$nd      = SoViec::nguoi_doc_cho();
		$bao_loi = ( $nd['bao_loi'] ?? 0 ) + ( $nd['vi_pham'] ?? 0 );
		$cho     = self::cho_loai();
		$trung   = DuongDan::trung();
		$mong    = DoLuong::nhieu_nhat( 'mong_co_cau', 7, 20 );
		$tim     = DoLuong::nhieu_nhat( 'tim_khong_ket_qua', 7, 5 );
		// Bài đọc nhiều chưa có Ý nghĩa: đọc từ bảng tóm tắt bài (Hôm nay mở nhanh cả khi kho lớn)
		$doc     = array_flip( BoSung::doc_nhieu_thieu( 10 ) );
		$tuoi    = SoViec::tuoi( $bay_gio );
		$ten_kho = static fn( string $k ): string => (string) ( Kho::lay( (int) substr( $k, 4 ) )?->tieu_de ?? '' );
		$de_nghi = current_user_can( 'cadao_duyet' ) ? self::de_nghi() : 0;
		$goi_y   = self::goi_y();
		$ds      = array(
			array( $bao_loi, $bao_loi . ' báo lỗi, báo vi phạm của người đọc đang chờ', self::hop_viec( array( 'loai' => 'nguoi_doc' ) ), array(), true ),
			array( count( $trung ), count( $trung ) . ' đường dẫn đang dùng chung cho nhiều bài — người đọc mất lối vào một bài', DanhSachTacPham::url_loc( array() ), array(), true ),
			array( $cho['co_the_trung'] ?? 0, ( $cho['co_the_trung'] ?? 0 ) . ' cặp câu có thể trùng chờ quyết trước khi đăng', self::hop_viec( array( 'loai' => 'co_the_trung' ) ), array(), true ),
			array( count( $mong ), count( $mong ) . ' câu người đọc bấm "Mong có" trong 7 ngày (câu đang chờ trong kho)', DanhSachKho::url(), array_values( array_filter( array_map( static fn( string $k ): array => array( $ten_kho( $k ), DanhSachKho::url( array( 'q' => $ten_kho( $k ) ) ) ), array_slice( array_keys( $mong ), 0, 5 ) ), static fn( array $x ): bool => '' !== $x[0] ) ), false ),
			array( count( $doc ), 'Viết ý nghĩa cho ' . count( $doc ) . ' bài đọc nhiều tuần này mà chưa có', DanhSachTacPham::url_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) ), array_map( static fn( int $id ): array => array( get_the_title( $id ), SuaTacPham::url( $id ) ), array_slice( array_keys( $doc ), 0, 5 ) ), false ),
			array( count( $tim ), count( $tim ) . ' câu người đọc tìm mà chưa có (7 ngày) — xem kho có không', DanhSachKho::url(), array_map( static fn( string $q ): array => array( $q, DanhSachKho::url( array( 'q' => $q ) ) ), array_keys( $tim ) ), false ),
			array( $cho['nhap_y_nghia'] ?? 0, ( $cho['nhap_y_nghia'] ?? 0 ) . ' nháp ý nghĩa chờ duyệt', self::hop_viec( array( 'loai' => 'nhap_y_nghia' ) ), array(), false ),
			array( $de_nghi, $de_nghi . ' câu được đề nghị đăng chờ bạn xem', DanhSachKho::url( array( 'danh' => 'de_nghi' ) ), array(), false ),
			array( $goi_y, $goi_y . ' câu máy gợi ý hôm nay ở Bài mới', DanhSachKho::url( array( 'goi_y' => 'hom_nay' ) ), array(), false ),
			array( $cho['cau_moi'] ?? 0, ( $cho['cau_moi'] ?? 0 ) . ' câu mới từ nguồn chờ quyết', self::hop_viec( array( 'loai' => 'cau_moi' ) ), array(), false ),
			array( array_sum( $nd ) - $bao_loi, ( array_sum( $nd ) - $bao_loi ) . ' nội dung người đọc gửi khác (dị bản, góp ý, cảm nghĩ, bài cảm nhận)', self::hop_viec( array( 'loai' => 'nguoi_doc' ) ), array(), false ),
			array( $cho['goi_y'] ?? 0, ( $cho['goi_y'] ?? 0 ) . ' gợi ý chủ đề, vùng của máy chờ duyệt ở Bổ sung', BoSung::url_goi_y(), array(), false ),
			array( $cho['can_xem'] ?? 0, ( $cho['can_xem'] ?? 0 ) . ' bài "Cần xem" sau chuyển dữ liệu', self::hop_viec( array( 'loai' => 'can_xem' ) ), array(), false ),
			array( $tuoi['tren_30'], $tuoi['tren_30'] . ' thẻ đã mở quá 30 ngày', self::hop_viec(), array(), false ),
		);
		$ra = array();
		foreach ( $ds as [ $so, $chu, $url, $con, $gap ] ) {
			if ( $so > 0 && count( $ra ) < 10 ) {
				$ra[] = array( 'so' => (int) $so, 'chu' => $chu, 'url' => $url, 'ds' => $con, 'gap' => $gap );
			}
		}
		return $ra;
	}
}
