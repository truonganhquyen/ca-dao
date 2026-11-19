<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\DichVu\UngVien\DanhDau;
use Phongdao\DichVu\UngVien\NhapTep;
use Phongdao\DichVu\UngVien\UngVien;
use Phongdao\DichVu\XuLyLo\Lo;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Màn Kho trong wp-admin (docs/30 mục 2, 4.6; T-184): người biên soạn tự tìm, lọc câu đã thu thập và đưa một câu đủ điều
 * kiện vào Hộp việc. Kho là tư liệu riêng, không phải Hộp việc: nút "Đưa vào Hộp việc" chỉ tạo thẻ "Câu mới" đang chờ
 * (UngVien), không đăng, gộp, sửa lời hay bỏ câu. Chọn nhiều câu (hoặc mọi câu khớp bộ lọc) tạo một lô có xem trước: "Đưa
 * vào Hộp việc" (D-099) hoặc, với người có quyền duyệt, "Xem trước và đăng" một mạch (D-101) — vẫn một màn xem trước, vẫn
 * do người bấm Đăng. Dữ liệu kho chỉ hiện cho người có `cadao_soan`; không bao giờ ra trang người đọc.
 */
final class DanhSachKho {

	public const TRANG     = 'phongdao-kho';
	public const HANH_DONG = 'phongdao_kho_dua_vao';
	/** admin-post đánh / bỏ dấu Để dành, Đề nghị đăng (một câu, hay các câu đã chọn qua form lô). */
	public const DANH_DAU = 'phongdao_kho_danh_dau';
	public const DOI_SOAT = 'phongdao_kho_kiem_trung';
	/** Bộ lọc dấu. */
	private const DANH = array(
		''        => 'Có dấu hay không',
		'tat_ca'  => 'Có dấu (Để dành, Đề nghị đăng)',
		'de_danh' => 'Để dành',
		'de_nghi' => 'Đề nghị đăng',
	);
	/** Bộ lọc đặc điểm câu (xếp câu đáng đăng trước). */
	private const DAC = array(
		''          => 'Mọi câu',
		'mong'      => 'Người đọc mong có (7 ngày)',
		'chu_thich' => 'Có chú thích của nguồn',
		'khong_co'  => 'Không có cờ của nguồn',
		'ngan'      => 'Câu ngắn (1–2 dòng)',
		'dai'       => 'Câu dài (từ 5 dòng)',
	);
	private const MOI_TRANG = 30;
	/** Form lô đặt ngoài bảng (mỗi dòng đã có form riêng); ô chọn gắn vào bằng thuộc tính `form`. */
	private const FORM_LO = 'phongdao-kho-lo';
	private const TRANG_THAI = array(
		'moi'        => 'Chưa dùng',
		'tat_ca'     => 'Mọi trạng thái',
		'da_tao_bai' => 'Đã thành tác phẩm',
		'da_gop'     => 'Đã gộp',
		'bo_qua'     => 'Đã bỏ',
	);
	private const THE = array(
		'tat_ca' => 'Có hay chưa có thẻ',
		'chua'   => 'Chưa có thẻ Câu mới',
		'co'     => 'Đang có thẻ Câu mới',
	);
	/** Cờ máy đặt khi đọc nguồn => chữ hiện cho người biên soạn (mọi nơi hiện cờ dùng chung, không in mã máy). */
	public const CO = array(
		'tho_tuc'       => 'có từ thô tục',
		'hien_dai'      => 'nghi là câu hiện đại',
		'can_xem'       => 'nguồn ghi cần xem',
		'lech_api_html' => 'lời giữa hai lối lấy khác nhau',
	);

	/** admin-post của thẻ Đăng nhanh (Đăng / Để sau). */
	public const DANG_NHANH = 'phongdao_kho_dang_nhanh';

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::DANG_NHANH, array( self::class, 'nhan_dang_nhanh' ) );
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'dua_vao' ) );
		add_action( 'admin_post_' . self::DANH_DAU, array( self::class, 'danh_dau' ) );
		add_action( 'admin_post_' . self::DOI_SOAT, array( self::class, 'kiem_trung' ) );
	}

	/**
	 * Chữ của các cờ máy đặt khi đọc nguồn (vd. "có từ thô tục"); mã lạ thì ghi "cờ khác của nguồn", không in mã máy.
	 *
	 * @param array<int,mixed> $co
	 * @return string[]
	 */
	public static function ten_co( array $co ): array {
		return array_values( array_unique( array_map( static fn( $c ): string => self::CO[ (string) $c ] ?? 'cờ khác của nguồn', $co ) ) );
	}

	/**
	 * @param array<string,mixed>|null $vao bộ lọc thô (mặc định $_GET; form lô gửi qua ô ẩn `loc_*`)
	 * @return array<string,string|int> bộ lọc đã kiểm; SQL và URL chỉ nhận các giá trị này
	 */
	private static function loc( ?array $vao = null ): array {
		$g     = $vao ?? $_GET; // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc bộ lọc
		$nguon = sanitize_text_field( wp_unslash( (string) ( $g['nguon'] ?? '' ) ) );
		$loai  = sanitize_key( (string) ( $g['loai'] ?? '' ) );
		$tt    = sanitize_key( (string) ( $g['tt'] ?? 'moi' ) );
		$the   = sanitize_key( (string) ( $g['the'] ?? 'tat_ca' ) );
		$danh  = sanitize_key( (string) ( $g['danh'] ?? '' ) );
		$dac   = sanitize_key( (string) ( $g['dac'] ?? '' ) );
		return array(
			'q'     => trim( sanitize_text_field( wp_unslash( (string) ( $g['q'] ?? '' ) ) ) ),
			'nguon' => preg_match( '/^[a-z0-9_-]{1,64}$/', $nguon ) ? $nguon : '',
			'loai'  => isset( TheLoai::TU_LOAI_GOC[ $loai ] ) ? $loai : '',
			'tt'    => isset( self::TRANG_THAI[ $tt ] ) ? $tt : 'moi',
			'the'   => isset( self::THE[ $the ] ) ? $the : 'tat_ca',
			'goi_y' => 'hom_nay' === ( $g['goi_y'] ?? '' ) ? 'hom_nay' : '',
			'danh'  => isset( self::DANH[ $danh ] ) ? $danh : '',
			'dac'   => isset( self::DAC[ $dac ] ) ? $dac : '',
			'paged' => max( 1, (int) ( $g['paged'] ?? 1 ) ),
			'lan_nhap' => max( 0, (int) ( $g['lan_nhap'] ?? 0 ) ),
		);
	}

	/** Điều kiện SQL của bộ lọc (bảng kho đặt bí danh k). Câu chưa có lời (chờ đọc trang) không hiện. */
	private static function dieu_kien( array $loc ): string {
		global $wpdb;
		$dk = array( "k.loi <> ''" );
		if ( ! empty( $loc['lan_nhap'] ) ) {
			$dk[] = self::trong( \Phongdao\DichVu\UngVien\LanNhap::ids( (int) $loc['lan_nhap'] ) );
		}
		if ( '' !== $loc['q'] ) {
			$dk[] = ctype_digit( $loc['q'] )
				? $wpdb->prepare( 'k.id = %d', (int) $loc['q'] )
				: $wpdb->prepare( '(k.tieu_de LIKE %s OR k.loi LIKE %s)', '%' . $wpdb->esc_like( $loc['q'] ) . '%', '%' . $wpdb->esc_like( $loc['q'] ) . '%' );
		}
		if ( '' !== $loc['nguon'] ) {
			$dk[] = $wpdb->prepare( 'k.nguon_id = %s', $loc['nguon'] );
		}
		if ( '' !== $loc['loai'] ) {
			$dk[] = $wpdb->prepare( 'k.loai = %s', $loc['loai'] );
		}
		if ( 'tat_ca' !== $loc['tt'] ) {
			$dk[] = $wpdb->prepare( 'k.trang_thai = %s', $loc['tt'] );
		}
		if ( 'tat_ca' !== $loc['the'] ) {
			$dk[] = ( 'co' === $loc['the'] ? '' : 'NOT ' ) . 'EXISTS (SELECT 1 FROM ' . Viec::bang() . " v WHERE v.doi_tuong_loai = 'kho' AND v.doi_tuong_id = k.id AND v.loai = '" . UngVien::VIEC . "' AND v.trang_thai IN ('cho','de_sau'))";
		}
		// Máy gợi ý hôm nay (thay thẻ Câu mới hằng ngày, D-104)
		if ( 'hom_nay' === ( $loc['goi_y'] ?? '' ) ) {
			$dk[] = self::trong( UngVien::goi_y_hom_nay()['ids'] );
		}
		if ( '' !== ( $loc['danh'] ?? '' ) ) {
			$dk[] = DanhDau::sql_co( 'tat_ca' === $loc['danh'] ? '' : (string) $loc['danh'] );
		}
		switch ( $loc['dac'] ?? '' ) {
			case 'mong':
				$dk[] = self::trong( array_map( static fn( string $khoa ): int => (int) substr( $khoa, 4 ), array_filter( array_keys( \Phongdao\DichVu\DoLuong\DoLuong::nhieu_nhat( 'mong_co_cau', 7, 200 ) ), static fn( $khoa ): bool => str_starts_with( (string) $khoa, 'kho:' ) ) ) );
				break;
			case 'chu_thich':
				$dk[] = "k.du_lieu LIKE '%\"chu_thich\":[{%'";
				break;
			case 'khong_co':
				$dk[] = "k.du_lieu NOT LIKE '%\"co\":[\"%'";
				break;
			case 'ngan':
				$dk[] = "(LENGTH(k.loi) - LENGTH(REPLACE(k.loi, '\n', ''))) <= 1";
				break;
			case 'dai':
				$dk[] = "(LENGTH(k.loi) - LENGTH(REPLACE(k.loi, '\n', ''))) >= 4";
				break;
		}
		return implode( ' AND ', $dk );
	}

	/** Điều kiện "k.id thuộc danh sách" (danh sách rỗng = không câu nào). */
	private static function trong( array $ids ): string {
		$ids = array_values( array_filter( array_map( 'intval', $ids ) ) );
		return $ids ? 'k.id IN (' . implode( ',', $ids ) . ')' : '1 = 0';
	}

	/** Điều kiện của câu đưa vào Hộp việc được trong bộ lọc: Chưa dùng, chưa gắn bài, chưa có thẻ Câu mới đang mở. */
	private static function dieu_kien_chon( array $loc ): string {
		return self::dieu_kien( $loc ) . " AND k.trang_thai = 'moi' AND k.post_id = 0";
	}

	/** Số câu khớp bộ lọc còn đưa vào Hộp việc được (cho phạm vi "mọi câu khớp bộ lọc"). */
	public static function dem_chon( array $loc ): int {
		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Kho::bang() . ' k WHERE ' . self::dieu_kien_chon( $loc ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- điều kiện đã prepare từng phần
	}

	/** @return int[] mã câu khớp bộ lọc còn đưa vào được, theo thứ tự trên màn (mới lấy trước), tối đa $toi_da + 1 */
	public static function ids_chon( array $loc, int $toi_da ): array {
		global $wpdb;
		return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( 'SELECT k.id FROM ' . Kho::bang() . ' k WHERE ' . self::dieu_kien_chon( $loc ) . ' ORDER BY k.id DESC LIMIT %d', $toi_da + 1 ) ) ?: array() ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/** Bộ lọc đọc từ ô ẩn `loc_*` của form lô, kiểm như bộ lọc trên URL. */
	public static function loc_tu( array $vao ): array {
		return self::loc( $vao );
	}

	/** @return array{tong:int,ds:object[]} một trang câu kho khớp bộ lọc, mới lấy trước; tổng đếm toàn kho */
	public static function tim( array $loc ): array {
		global $wpdb;
		$tu   = ' FROM ' . Kho::bang() . ' k WHERE ' . self::dieu_kien( $loc );
		$tong = (int) $wpdb->get_var( 'SELECT COUNT(*)' . $tu ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- điều kiện đã prepare từng phần
		$ds   = $wpdb->get_results( $wpdb->prepare( 'SELECT k.*' . $tu . ' ORDER BY k.id DESC LIMIT %d OFFSET %d', self::MOI_TRANG, ( (int) $loc['paged'] - 1 ) * self::MOI_TRANG ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array( 'tong' => $tong, 'ds' => $ds ?: array() );
	}

	/**
	 * Thẻ Câu mới đang mở và cặp chờ quyết trùng của đúng các câu trên trang (ba truy vấn, không theo từng dòng). Cặp chờ
	 * quyết trùng mang mã thẻ "Có thể trùng" đang mở (0 khi chưa có thẻ) để Kho mở thẳng đúng thẻ.
	 *
	 * @return array{the:array<int,int>,trung:array<int,int>}
	 */
	private static function trang_thai_trang( array $ds ): array {
		global $wpdb;
		$ids = array_map( static fn( object $m ): int => (int) $m->id, $ds );
		if ( ! $ids ) {
			return array( 'the' => array(), 'trung' => array() );
		}
		$dau = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$the = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT doi_tuong_id, MIN(id) AS dau FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'kho' AND loai = %s AND trang_thai IN ('cho','de_sau') AND doi_tuong_id IN ($dau) GROUP BY doi_tuong_id", array_merge( array( UngVien::VIEC ), $ids ) ) ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$the[ (int) $h->doi_tuong_id ] = (int) $h->dau;
		}
		$khoa  = array_map( static fn( int $i ): string => 'kho:' . $i, $ids );
		$trung = array();
		foreach ( $wpdb->get_col( $wpdb->prepare( 'SELECT a FROM ' . ChongTrung::bang( 'trung' ) . " WHERE trang_thai IN ('cho','de_sau') AND a IN (" . implode( ',', array_fill( 0, count( $khoa ), '%s' ) ) . ')', $khoa ) ) ?: array() as $a ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$trung[ (int) substr( (string) $a, 4 ) ] = 0;
		}
		foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT doi_tuong_id, MIN(id) AS dau FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'kho' AND loai = 'co_the_trung' AND trang_thai IN ('cho','de_sau') AND doi_tuong_id IN ($dau) GROUP BY doi_tuong_id", $ids ) ) ?: array() as $h ) {
			if ( array_key_exists( (int) $h->doi_tuong_id, $trung ) ) { $trung[ (int) $h->doi_tuong_id ] = (int) $h->dau; }
		}
		$bai = array_filter( array_map( static fn( object $m ): int => (int) $m->post_id, $ds ) );
		if ( $bai ) {
			_prime_post_caches( $bai, false, false );
		}
		return array( 'the' => $the, 'trung' => $trung );
	}

	public static function url( array $loc = array(), int $trang = 1 ): string {
		$loc += array( 'q' => '', 'nguon' => '', 'loai' => '', 'tt' => 'moi', 'the' => 'tat_ca', 'goi_y' => '', 'danh' => '', 'dac' => '', 'lan_nhap' => 0 );
		return add_query_arg(
			array_filter(
				array( 'page' => self::TRANG, 'q' => $loc['q'], 'nguon' => $loc['nguon'], 'loai' => $loc['loai'], 'tt' => 'moi' === $loc['tt'] ? '' : $loc['tt'], 'the' => 'tat_ca' === $loc['the'] ? '' : $loc['the'], 'goi_y' => $loc['goi_y'], 'danh' => $loc['danh'], 'dac' => $loc['dac'], 'lan_nhap' => $loc['lan_nhap'] ?: '', 'paged' => $trang > 1 ? $trang : '' ),
				static fn( $v ) => '' !== $v
			),
			admin_url( 'admin.php' )
		);
	}

	private static function chon( string $ten, string $nhan, array $ds, string $gia_tri ): string {
		$ra = '<div class="phongdao-tp-truong"><label for="phongdao-kho-' . esc_attr( $ten ) . '">' . esc_html( $nhan ) . '</label><select id="phongdao-kho-' . esc_attr( $ten ) . '" name="' . esc_attr( $ten ) . '">';
		foreach ( $ds as $ma => $ten_hien ) {
			$ra .= '<option value="' . esc_attr( (string) $ma ) . '"' . selected( (string) $ma, $gia_tri, false ) . '>' . esc_html( (string) $ten_hien ) . '</option>';
		}
		return $ra . '</select></div>';
	}

	public static function ten_loai( string $loai ): string {
		return TheLoai::DS[ TheLoai::TU_LOAI_GOC[ $loai ] ?? '' ] ?? ( '' === $loai ? 'Chưa rõ' : $loai );
	}

	private static function ngay( string $mysql ): string {
		return '' === $mysql ? '' : (string) mysql2date( 'd/m/Y', $mysql );
	}

	/** Ô "Câu" của một câu kho: tên, bốn dòng đầu, số chú thích / dị bản, cờ của nguồn (dùng ở Kho và xem trước lô). */
	public static function o_cau( object $m ): string {
		$d    = Kho::du_lieu( $m );
		$dong = array_values( array_filter( explode( "\n", (string) $m->loi ), static fn( string $l ): bool => '' !== trim( $l ) ) );
		$ten  = '' !== trim( (string) $m->tieu_de ) ? (string) $m->tieu_de : (string) ( $dong[0] ?? '' );
		$cau  = '<strong>' . esc_html( $ten ) . '</strong><span class="phongdao-kho-loi">' . implode( '<br>', array_map( 'esc_html', array_slice( $dong, 0, 4 ) ) ) . '</span>'
			. ( count( $dong ) > 4 ? '<span class="description">… ' . count( $dong ) . ' dòng</span>' : '' );
		$them = array( count( (array) ( $d['chu_thich'] ?? array() ) ) . ' chú thích', count( (array) ( $d['di_ban'] ?? array() ) ) . ' dị bản' );
		$co   = self::ten_co( (array) ( $d['co'] ?? array() ) );
		return $cau . '<span class="description">' . esc_html( implode( ' · ', $them ) ) . '</span>' . ( $co ? '<span class="phongdao-kho-co">Cờ: ' . esc_html( implode( ', ', $co ) ) . '</span>' : '' );
	}

	/**
	 * Một hàng (D-121 đợt 3): ô chọn · số câu · ô câu (thể loại, tên, lời bốn dòng, dòng 2: chú thích / dị bản từ nguồn,
	 * nguồn, ngày lấy, cờ, trạng thái) · cột việc: "Đăng" mở thẻ Đăng nhanh đúng câu này (người có quyền duyệt), "Để dành" /
	 * "Đề nghị đăng" / "Bỏ dấu". Câu đã có thẻ, chờ quyết trùng hay chưa chọn được thì chỉ ghi lý do.
	 *
	 * @param int $so vị trí của câu trong bộ lọc (từ 1) — mở đúng thẻ Đăng nhanh
	 */
	private static function dong( object $m, array $tt_trang, array $nguon, string $quay_lai, array $dau, array $loc, int $so ): string {
		$id        = (int) $m->id;
		$d         = Kho::du_lieu( $m );
		$dong      = array_values( array_filter( explode( "\n", (string) $m->loi ), static fn( string $l ): bool => '' !== trim( $l ) ) );
		$ten       = '' !== trim( (string) $m->tieu_de ) ? (string) $m->tieu_de : (string) ( $dong[0] ?? '' );
		$ten_nguon = NguonTuDong::ten( (string) $m->nguon_id );
		if ( \Phongdao\DichVu\UngVien\NhapTep::NGUON === (string) $m->nguon_id ) {
			$ten_nguon = 'Nhập từ tệp · ' . ( ! empty( $d['ho_so'] ) ? get_the_title( (int) $d['ho_so'] ) : 'Chưa rõ nguồn' );
		}
		$doc       = (string) $m->doc_trang;
		$so_ct     = count( (array) ( $d['chu_thich'] ?? array() ) );
		$so_db     = count( (array) ( $d['di_ban'] ?? array() ) );
		$co        = self::ten_co( (array) ( $d['co'] ?? array() ) );
		$phu       = ( $so_ct ? '<span class="pq-the xanh">' . $so_ct . ' chú thích</span>' : '' ) . ( $so_db ? '<span class="pq-the xanh">' . $so_db . ' dị bản</span>' : '' )
			. ( $co ? '<span class="pq-the do phongdao-kho-co">Cờ: ' . esc_html( implode( ', ', $co ) ) . '</span>' : '' )
			. '<span class="pq-mo">' . esc_html( $ten_nguon . ( \Phongdao\DichVu\UngVien\NhapTep::NGUON === (string) $m->nguon_id ? ' · nhập ' . self::ngay( (string) $m->ngay_lay ) : ' · lấy ' . self::ngay( (string) $m->ngay_lay ) . ( '' === $doc ? ' · chưa đọc trang gốc' : ( preg_match( '/^\d{4}-/', $doc ) ? '' : ' · không đọc được trang gốc' ) ) ) ) . '</span>'
			. ( '' === (string) $m->url ? '' : '<a href="' . esc_url( (string) $m->url ) . '" rel="noreferrer nofollow">Trang gốc ↗</a>' );

		$tt  = array();
		$bai = (int) $m->post_id ? get_post( (int) $m->post_id ) : null;
		if ( 'moi' !== (string) $m->trang_thai ) {
			$tt[] = '<span class="pq-the">' . esc_html( self::TRANG_THAI[ $m->trang_thai ] ?? (string) $m->trang_thai ) . '</span>';
		}
		if ( $bai ) {
			$tt[] = current_user_can( 'edit_post', $bai->ID ) ? '<a href="' . esc_url( SuaTacPham::url( $bai->ID ) ) . '">Bài #' . (int) $bai->ID . '</a>' : 'Bài #' . (int) $bai->ID;
		} elseif ( 'da_gop' === $m->trang_thai && '' !== (string) $m->gop_vao ) {
			$dich = ChongTrung::bai_cua_muc( (string) $m->gop_vao );
			$tt[] = $dich ? '<a href="' . esc_url( SuaTacPham::url( $dich ) ) . '">Đã có bài #' . $dich . '</a>' : 'Đã gắn với mục trong Kho';
			$tt[] = ! empty( $d['nhap_chi_kho'] ) && empty( $d['nhap_da_quyet'] ) ? 'Chứng cứ nguồn giữ trong Kho' : ( 'di_ban' === $m->gop_lam ? 'Đã thêm dị bản' : 'Đã gắn vào bài có sẵn' );
		}
		$the = $tt_trang['the'][ $id ] ?? 0;
		if ( $the ) {
			$tt[] = '<a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => $the ), admin_url( 'admin.php' ) ) ) . '">Thẻ Câu mới #' . $the . '</a>';
		}
		$dau_cau = $dau[ $id ] ?? null;
		if ( $dau_cau && 'moi' === (string) $m->trang_thai ) {
			$ai   = get_userdata( (int) $dau_cau['nguoi'] );
			$tt[] = '<span class="pq-the tim"><strong>' . esc_html( DanhDau::LOAI[ $dau_cau['loai'] ] ?? '' ) . '</strong></span>' . ( $ai ? ' <span class="description">(' . esc_html( $ai->display_name ) . ')</span>' : '' );
		}
		$trung = array_key_exists( $id, $tt_trang['trung'] );
		if ( $trung ) {
			$tt[] = '<span class="pq-the do">Đang chờ quyết trùng</span>';
		}

		$ly_do = UngVien::chua_chon_duoc( $m );
		$duoc  = '' === $ly_do && ! $trung;
		$chon  = $duoc ? '<input type="checkbox" name="ids[]" value="' . $id . '" form="' . self::FORM_LO . '" id="chon-kho-' . $id . '"><label for="chon-kho-' . $id . '" class="screen-reader-text">Chọn câu #' . $id . '</label>' : '';
		if ( $duoc ) {
			// "Đăng" mở thẻ Đăng nhanh của đúng câu này (đọc lại, Ý nghĩa nếu có, chủ đề, rồi Đăng); "Để dành / Đề nghị đăng" (D-104)
			$dang = current_user_can( 'cadao_duyet' ) ? '<a class="button button-small button-primary" href="' . esc_url( self::url_nhanh( $loc, $so ) ) . '" aria-label="' . esc_attr( ( $the ? 'Tiếp tục đăng' : 'Xem để đăng' ) . ' câu #' . $id ) . '">' . ( $the ? 'Tiếp tục đăng' : 'Xem để đăng' ) . '</a>' : '';
			$nhan = $dau_cau ? 'Bỏ dấu' : DanhDau::LOAI[ DanhDau::loai_cua( get_current_user_id() ) ];
			$lam  = $dang . '<form method="post" class="phongdao-kho-lam" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::DANH_DAU . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="lam" value="' . ( $dau_cau ? 'bo' : 'danh' ) . '"><input type="hidden" name="quay_lai" value="' . esc_attr( $quay_lai ) . '">'
				. wp_nonce_field( self::DANH_DAU . '_' . $id, '_wpnonce', false, false )
				. '<button type="submit" class="button button-small" aria-label="' . esc_attr( $nhan . ': câu #' . $id ) . '">' . esc_html( $nhan ) . '</button></form>';
		} else {
			$lam = '<span class="description">' . esc_html( $the ? 'Đã có trong Hộp việc' : ( $trung ? 'Quyết trùng trước' : $ly_do ) ) . '</span>';
			if ( $trung ) {
				if ( $tt_trang['trung'][ $id ] ) {
					$lam .= '<a class="button" href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => $tt_trang['trung'][ $id ], 'lan_nhap' => (int) ( $loc['lan_nhap'] ?? 0 ) ), admin_url( 'admin.php' ) ) ) . '">Xem đối chiếu</a>';
				}
				$lam .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::DOI_SOAT . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="quay_lai" value="' . esc_attr( $quay_lai ) . '">'
					. wp_nonce_field( self::DOI_SOAT . '_' . $id, '_wpnonce', false, false ) . '<button type="submit" class="button button-small">Kiểm tra chờ trùng</button></form>';
			}
		}
		return '<tr><td class="phongdao-tp-chon">' . $chon . '</td><td class="phongdao-tp-id" data-nhan="ID">' . $id . '</td>'
			. '<td class="phongdao-kho-cau phongdao-tp-ten" data-nhan="Câu"><span class="pq-loai">' . esc_html( self::ten_loai( (string) $m->loai ) ) . '</span> <strong>' . esc_html( $ten ) . '</strong>'
			. '<span class="phongdao-kho-loi">' . implode( '<br>', array_map( 'esc_html', array_slice( $dong, 0, 4 ) ) ) . ( count( $dong ) > 4 ? ' <span class="pq-mo">… ' . count( $dong ) . ' dòng</span>' : '' ) . '</span>'
			. '<span class="phongdao-tp-dong2">' . $phu . '</span>' . ( $tt ? '<span class="phongdao-tp-dong3 phongdao-kho-tt">' . implode( ' ', $tt ) . '</span>' : '' ) . '</td>'
			. '<td class="phongdao-tp-lam phongdao-kho-lam-o"><span class="phongdao-tp-hanh">' . $lam . '</span></td></tr>';
	}

	/** Đổi cách xem Bài mới: Danh sách · Đăng nhanh (người có quyền duyệt). */
	private static function chuyen_xem( array $loc, bool $nhanh ): string {
		if ( ! current_user_can( 'cadao_duyet' ) ) {
			return '';
		}
		return '<nav class="pq-chuyen-xem" aria-label="Cách xem Bài mới"><a href="' . esc_url( self::url( $loc ) ) . '"' . ( $nhanh ? '' : ' aria-current="page"' ) . '>Danh sách</a><a href="' . esc_url( self::url_nhanh( $loc ) ) . '"' . ( $nhanh ? ' aria-current="page"' : '' ) . '>Đăng nhanh</a></nav>';
	}

	public static function lay_bao(): string {
		$ket = get_transient( 'phongdao_kho_bao_' . get_current_user_id() );
		if ( ! is_array( $ket ) ) { return ''; }
		delete_transient( 'phongdao_kho_bao_' . get_current_user_id() );
		$mo = ! empty( $ket['viec'] ) ? ' <a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => (int) $ket['viec'] ), admin_url( 'admin.php' ) ) ) . '">Mở thẻ</a>' : '';
		return '<div class="notice ' . ( empty( $ket['ok'] ) ? 'notice-error' : 'notice-success' ) . '"><p role="status">' . esc_html( (string) ( $ket['chu'] ?? '' ) ) . $mo . '</p></div>';
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><h1>Kho</h1><p>Bạn không có quyền xem kho thu thập.</p></div>';
		}
		global $wpdb;
		$loc   = self::loc();
		if ( ! empty( $_GET['xem_nhap'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- chỉ xem bản chuẩn bị nhập
			return NhapKho::xem( absint( $_GET['xem_nhap'] ) );
		}
		// Đăng nhanh (D-119): mỗi câu một thẻ, theo đúng bộ lọc đang chọn; chỉ người có quyền duyệt (người đăng)
		if ( 'nhanh' === ( $_GET['xem'] ?? '' ) && current_user_can( 'cadao_duyet' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- chỉ chọn cách xem
			return self::hien_nhanh( $loc );
		}
		$kq    = self::tim( $loc );
		$nguon = NguonTuDong::tat_ca();
		$tt    = self::trang_thai_trang( $kq['ds'] );
		$ds_nguon = array( '' => 'Mọi nguồn' );
		foreach ( $wpdb->get_col( 'SELECT DISTINCT nguon_id FROM ' . Kho::bang() . ' ORDER BY nguon_id' ) ?: array() as $id ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- không có giá trị ngoài
			$ds_nguon[ (string) $id ] = isset( $nguon[ $id ]['ten'] ) && '' !== (string) $nguon[ $id ]['ten'] ? (string) $nguon[ $id ]['ten'] : 'Nguồn chưa đặt tên (' . $id . ')';
		}
		$ds_loai = array( '' => 'Mọi thể loại' );
		foreach ( array_keys( TheLoai::TU_LOAI_GOC ) as $l ) {
			$ds_loai[ $l ] = self::ten_loai( $l );
		}
		$bao = self::lay_bao();
		if ( ! empty( $loc['lan_nhap'] ) && \Phongdao\DichVu\UngVien\LanNhap::lay( (int) $loc['lan_nhap'] ) ) {
			$bao .= DanhSachViec::bao_quyet( array( 'tt' => Viec::CHO, 'loai' => '', 'q' => '', 'paged' => 1, 'viec' => 0, 'lan_nhap' => (int) $loc['lan_nhap'] ) );
		}
		// Ô tìm luôn hiện; bộ lọc còn lại gập trong "Thêm bộ lọc" (mở sẵn khi đang dùng) — điện thoại thấy câu ngay màn đầu
		$dang_loc = '' !== $loc['nguon'] || '' !== $loc['loai'] || 'moi' !== $loc['tt'] || 'tat_ca' !== $loc['the'] || '' !== $loc['dac'] || in_array( $loc['danh'], array( 'tat_ca' ), true );
		$form = '<form class="phongdao-tp-tim" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '">'
			. '<input type="hidden" name="lan_nhap" value="' . (int) $loc['lan_nhap'] . '">'
			. '<input type="hidden" name="goi_y" value="' . esc_attr( (string) $loc['goi_y'] ) . '">'
			. '<div class="phongdao-tp-tim-o"><label for="phongdao-kho-q">Tìm ID hoặc chữ trong lời</label><input id="phongdao-kho-q" type="search" name="q" value="' . esc_attr( (string) $loc['q'] ) . '"><button class="button button-primary" type="submit">Tìm</button></div>'
			. '<details class="phongdao-tp-them"' . ( $dang_loc ? ' open' : '' ) . '><summary>Thêm bộ lọc' . ( $dang_loc ? ' (đang dùng)' : '' ) . '</summary><div class="phongdao-tp-loc">'
			. self::chon( 'nguon', 'Nguồn', $ds_nguon, (string) $loc['nguon'] )
			. self::chon( 'loai', 'Thể loại', $ds_loai, (string) $loc['loai'] )
			. self::chon( 'tt', 'Trạng thái', self::TRANG_THAI, (string) $loc['tt'] )
			. self::chon( 'dac', 'Đặc điểm', self::DAC, (string) $loc['dac'] )
			. self::chon( 'danh', 'Dấu', self::DANH, (string) $loc['danh'] )
			. '<button class="button" type="submit">Lọc</button></div></details></form>';
		$bao     .= TrangLo::lay_bao();
		$quay_lai = self::url( $loc, (int) $loc['paged'] );
		$dau      = DanhDau::cua_trang( array_map( static fn( object $m ): int => (int) $m->id, $kq['ds'] ) );
		$vt       = ( (int) $loc['paged'] - 1 ) * self::MOI_TRANG;
		$dong     = '';
		foreach ( $kq['ds'] as $i => $m ) {
			$dong .= self::dong( $m, $tt, $nguon, $quay_lai, $dau, $loc, $vt + $i + 1 );
		}
		$so_trang = (int) ceil( $kq['tong'] / self::MOI_TRANG );
		$phan_trang = $so_trang > 1 ? '<nav class="phongdao-tp-trang" aria-label="Phân trang"><span>Trang ' . (int) $loc['paged'] . ' / ' . $so_trang . '</span>'
			. ( $loc['paged'] > 1 ? '<a class="button" href="' . esc_url( self::url( $loc, (int) $loc['paged'] - 1 ) ) . '">Trang trước</a>' : '' )
			. ( $loc['paged'] < $so_trang ? '<a class="button" href="' . esc_url( self::url( $loc, (int) $loc['paged'] + 1 ) ) . '">Trang sau</a>' : '' ) . '</nav>' : '';
		$so_chon = $kq['tong'] > 0 ? self::dem_chon( $loc ) : 0;
		$duyet   = current_user_can( Lo::THAO_TAC['kho_dang'][2] );
		$ten_dau = DanhDau::LOAI[ DanhDau::loai_cua( get_current_user_id() ) ];
		$noi     = $so_chon ? TrangLo::thanh_noi(
			'câu',
			( $duyet ? '<button type="submit" form="' . self::FORM_LO . '" name="lam" value="dang" class="button button-primary">Xem trước và đăng</button>' : '' )
				. '<button type="submit" form="' . self::FORM_LO . '" name="lam" value="de_danh" class="button' . ( $duyet ? '' : ' button-primary' ) . '">' . esc_html( $ten_dau ) . '</button>'
		) : '';
		return '<div class="wrap pq-man phongdao-admin-tp phongdao-admin-kho"><div class="pq-dau"><div class="pq-dau-chu"><h1>Bài mới</h1><p>' . ( $duyet
				? 'Câu máy đã thu thập trong Kho, chưa lên trang. Bấm "Đăng" để mở thẻ đọc lại rồi đăng; chọn nhiều câu để xem trước và đăng một lượt. Không có gì được đăng khi bạn chưa bấm; máy không sửa lời.'
				: 'Câu máy đã thu thập trong Kho, chưa lên trang. Câu nên đăng thì bấm "Đề nghị đăng": người có quyền duyệt thấy ở Hôm nay. Nút không đăng, không sửa lời.' ) . '</p></div>'
			. '<div class="pq-dau-nut">' . self::chuyen_xem( $loc, false ) . '<a class="button" href="' . esc_url( add_query_arg( 'nhap', 1, self::url() ) . '#nhap-tep' ) . '">Nhập tệp</a><a class="button" href="' . esc_url( ThemTacPham::url() ) . '">＋ Thêm tác phẩm</a></div></div><hr class="wp-header-end">'
			. $bao . self::khoi_nhap() . NhapKho::trong_danh_sach( (int) $loc['lan_nhap'] ) . self::nhanh( $loc ) . $form
			. '<p class="phongdao-tp-tong pq-mo-ta" role="status"><strong>' . (int) $kq['tong'] . '</strong> câu khớp bộ lọc. Hiện ' . count( $kq['ds'] ) . ' câu trên trang này.</p>'
			. ( $kq['tong'] > 0 ? self::khoi_lo( $loc, $so_chon, $duyet ) . TrangLo::chon_het( $so_chon, 'câu' ) : '' )
			. '<table class="widefat phongdao-tp-bang pq-ds-bang"><thead class="screen-reader-text"><tr><th scope="col">Chọn</th><th scope="col">Số câu</th><th scope="col">Câu</th><th scope="col">Làm gì</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="4">' . ( 'hom_nay' === $loc['goi_y'] && ! UngVien::goi_y_hom_nay()['ids'] ? 'Máy chưa có gợi ý hôm nay (máy chọn lúc 06:00 cho các nguồn đang bật).' : 'Không có câu nào khớp. Thử đổi bộ lọc.' ) . '</td></tr>' ) . '</tbody></table>' . $phan_trang . $noi
			. '</div>';
	}

	/** Câu thứ $so (từ 1) khớp bộ lọc, cùng thứ tự với danh sách. @return array{tong:int,cau:?object} */
	public static function cau_thu( array $loc, int $so ): array {
		global $wpdb;
		$tu   = ' FROM ' . Kho::bang() . ' k WHERE ' . self::dieu_kien( $loc );
		$tong = (int) $wpdb->get_var( 'SELECT COUNT(*)' . $tu ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- điều kiện đã prepare từng phần
		$cau  = $tong ? $wpdb->get_row( $wpdb->prepare( 'SELECT k.*' . $tu . ' ORDER BY k.id DESC LIMIT 1 OFFSET %d', max( 0, min( $so, $tong ) - 1 ) ) ) : null; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array( 'tong' => $tong, 'cau' => $cau ?: null );
	}

	/** Địa chỉ một thẻ Đăng nhanh theo bộ lọc. */
	public static function url_nhanh( array $loc, int $so = 1 ): string {
		if ( ! empty( $loc['lan_nhap'] ) ) { $loc['tt'] = 'moi'; }
		return add_query_arg( array( 'xem' => 'nhanh', 'so' => max( 1, $so ) ), self::url( $loc ) );
	}

	/**
	 * Màn Đăng nhanh (D-119, docs/41 mục 2 điều 9): mỗi câu kho một thẻ — lời, nguồn, số chú thích / dị bản máy chép, ô Ý
	 * nghĩa (tuỳ chọn, người viết), công tắc "Có từ ngữ thô tục" (chỉ người bật). Đăng là đúng đường "Xem trước và đăng" của
	 * Kho (máy so trùng lại; câu chờ quyết trùng thì không đăng). Bài không cần sửa: mở thẻ, bấm Đăng, sang thẻ sau.
	 */
	private static function hien_nhanh( array $loc ): string {
		$so  = max( 1, absint( $_GET['so'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc vị trí thẻ
		$kq  = self::cau_thu( $loc, $so );
		$so  = min( $so, max( 1, $kq['tong'] ) );
		$bao = '';
		$ket = get_transient( 'phongdao_kho_bao_' . get_current_user_id() );
		if ( is_array( $ket ) ) {
			delete_transient( 'phongdao_kho_bao_' . get_current_user_id() );
			$bao = '<div class="notice ' . ( empty( $ket['ok'] ) ? 'notice-error' : 'notice-success' ) . '"><p role="status">' . esc_html( (string) ( $ket['chu'] ?? '' ) )
				. ( empty( $ket['bai'] ) ? '' : ' <a href="' . esc_url( (string) get_permalink( (int) $ket['bai'] ) ) . '">Xem bài</a> · <a href="' . esc_url( SuaTacPham::url( (int) $ket['bai'] ) ) . '">Sửa đầy đủ</a>' ) . '</p></div>';
		}
		$dau = '<div class="wrap pq-man phongdao-admin-tp phongdao-admin-kho phongdao-dang-nhanh"><div class="pq-dau"><div class="pq-dau-chu"><h1>Bài mới · Đăng nhanh</h1><p>Mỗi câu một thẻ. Câu không cần sửa: đọc, bấm Đăng — tự sang câu kế.</p></div><div class="pq-dau-nut">' . self::chuyen_xem( $loc, true ) . '</div></div><hr class="wp-header-end">'
			. $bao . self::nhanh( $loc );
		$m = $kq['cau'];
		if ( ! $m ) {
			return $dau . '<p>Không có câu nào khớp bộ lọc. <a href="' . esc_url( self::url( $loc ) ) . '">Về danh sách</a></p></div>';
		}
		$d      = Kho::du_lieu( $m );
		$dong   = array_values( array_filter( explode( "\n", (string) $m->loi ), static fn( string $l ): bool => '' !== trim( $l ) ) );
		$ten    = '' !== trim( (string) $m->tieu_de ) ? (string) $m->tieu_de : (string) ( $dong[0] ?? '' );
		$nguon  = NguonTuDong::tat_ca()[ (string) $m->nguon_id ]['ten'] ?? (string) $m->nguon_id;
		$loai   = TheLoai::TU_LOAI_GOC[ (string) $m->loai ] ?? '';
		$trung  = ChongTrung::dang_cho( (int) $m->id );
		$da_co  = (int) $m->post_id;
		$o_y    = 'cau-do' === $loai ? 'Giải thích đáp án' : 'Ý nghĩa';
		$sau    = $so < $kq['tong'] ? '<a class="button" href="' . esc_url( self::url_nhanh( $loc, $so + 1 ) ) . '" data-phim="sau">Sang thẻ sau</a>' : '';
		$the    = '<div class="phongdao-dn-tien"><p class="phongdao-dn-dem">Thẻ ' . (int) $so . ' / ' . (int) $kq['tong'] . '</p><progress class="pq-tien-do" max="' . (int) max( 1, $kq['tong'] ) . '" value="' . (int) $so . '" aria-label="' . esc_attr( 'Thẻ ' . $so . ' trên ' . $kq['tong'] ) . '">' . (int) $so . ' / ' . (int) $kq['tong'] . '</progress></div>'
			. '<article class="pq-khoi phongdao-dn-the" aria-labelledby="dn-ten">'
			. '<p class="pq-mo-ta"><span class="pq-loai">' . esc_html( self::ten_loai( (string) $m->loai ) ) . '</span> · Nguồn: ' . esc_html( $nguon ) . ( '' === (string) $m->url ? '' : ' · <a href="' . esc_url( (string) $m->url ) . '" rel="noreferrer nofollow">trang gốc ↗</a>' ) . '</p>'
			. '<h2 id="dn-ten">' . esc_html( $ten ) . '</h2><div class="phongdao-dn-loi">' . implode( '<br>', array_map( 'esc_html', $dong ) ) . '</div>'
			. '<p class="description">' . esc_html( count( (array) ( $d['chu_thich'] ?? array() ) ) . ' chú thích, ' . count( (array) ( $d['di_ban'] ?? array() ) ) . ' dị bản từ nguồn (chú thích chỉ chép khi nguồn bật "Chép chú thích"). Máy so trùng lại khi đăng.' ) . '</p>';
		if ( $da_co || 'moi' !== (string) $m->trang_thai ) {
			return $dau . $the . '<p class="notice notice-info inline">Câu này không còn chờ đăng' . ( $da_co ? ' (đã thành bài #' . $da_co . ')' : '' ) . '.</p><p>' . $sau . '</p></article></div>';
		}
		$giu = get_transient( 'phongdao_dn_giu_' . get_current_user_id() );
		$giu = is_array( $giu ) && (int) ( $giu['id'] ?? 0 ) === (int) $m->id ? (array) $giu['vao'] : array();
		$form = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="phongdao-dn-form"><input type="hidden" name="action" value="' . self::DANG_NHANH . '">'
			. '<input type="hidden" name="id" value="' . (int) $m->id . '"><input type="hidden" name="ban" value="' . esc_attr( Lo::phien_kho( $m ) ) . '"><input type="hidden" name="quay_lai" value="' . esc_attr( self::url_nhanh( $loc, $so ) ) . '"><input type="hidden" name="sau" value="' . esc_attr( self::url_nhanh( $loc, $so + 1 ) ) . '">'
			. wp_nonce_field( self::DANG_NHANH . '_' . (int) $m->id, '_wpnonce', true, false )
			. '<p><label for="dn-y">' . esc_html( $o_y ) . ' <span class="description">tuỳ chọn · 2–3 câu, khoảng 30–60 chữ, bắt đầu bằng nghĩa; để trống thì đăng chưa có nghĩa</span></label><textarea id="dn-y" name="y_nghia" rows="3" class="large-text">' . esc_textarea( (string) ( $giu['y_nghia'] ?? '' ) ) . '</textarea></p>'
			. self::chip_goi_y( $m, (array) ( $giu['pl'] ?? array() ) )
			. '<p><label><input type="checkbox" name="tho_tuc" value="1"' . checked( ! empty( $giu['tho_tuc'] ), true, false ) . '> Có từ ngữ thô tục <span class="description">(chỉ người bật; trang đọc che lời sau lời báo)</span></label></p>'
			. '<p class="phongdao-dn-nut">' . ( $trung ? '' : '<button type="submit" name="lam" value="dang" class="button button-primary" data-phim="dang">Đăng</button>' )
			. '<button type="submit" name="lam" value="de_sau" class="button" data-phim="de-sau">Để sau</button>' . $sau . '</p>'
			. '<p class="phongdao-dn-phim description" data-chi-js hidden><kbd>Ctrl</kbd>+<kbd>Enter</kbd> Đăng · <kbd>Alt</kbd>+<kbd>S</kbd> Để sau · <kbd>Alt</kbd>+<kbd>→</kbd> Thẻ sau</p></form>';
		return $dau . $the . ( $trung ? '<div class="notice notice-warning inline"><p>Câu đang chờ quyết trùng: quyết thẻ "Có thể trùng" ở Hộp việc trước khi đăng.</p></div>' : '' ) . $form . '</article></div>';
	}

	/**
	 * Chủ đề, vùng máy gợi ý cho câu (cùng quy tắc với bài đã đăng): chip nét đứt chưa đánh dấu — người bấm mới gắn khi Đăng
	 * (D-121). Không có gợi ý thì không hiện gì.
	 */
	private static function chip_goi_y( object $m, array $da_chon = array() ): string {
		$o = '';
		foreach ( \Phongdao\DichVu\GoiY\GoiY::cho_kho( $m ) as $g ) {
			$t   = $g['term'];
			$ma  = 'dn-pl-' . $g['loai'] . '-' . (int) $t->term_id;
			$o  .= '<label class="pq-chip goi-y" for="' . esc_attr( $ma ) . '" title="' . esc_attr( (string) $g['ly_do'] ) . '"><input type="checkbox" id="' . esc_attr( $ma ) . '" name="pl[]" value="' . esc_attr( $g['loai'] . ':' . (int) $t->term_id ) . '"' . checked( in_array( $g['loai'] . ':' . (int) $t->term_id, $da_chon, true ), true, false ) . '> <span aria-hidden="true">＋</span> ' . esc_html( $t->name ) . ' <small>' . esc_html( \Phongdao\HaTang\KhaiBao::PHAN_LOAI[ $g['loai'] ][1] ?? $g['loai'] ) . '</small></label>';
		}
		return '' === $o ? '' : '<fieldset class="phongdao-dn-pl"><legend>Chủ đề, vùng máy gợi ý <span class="description">nét đứt — bấm để gắn khi Đăng</span></legend><div class="pq-chip-ds">' . $o . '</div></fieldset>';
	}

	/**
	 * Một thẻ Đăng nhanh: "dang" — đăng qua đường của Kho, rồi (nếu người viết) lưu Ý nghĩa người viết và bật "Có từ ngữ thô
	 * tục" khi người đánh dấu; "de_sau" — đánh dấu Để dành. Máy không viết Ý nghĩa, không tự bật cờ.
	 *
	 * @param array{lam?:string,y_nghia?:string,tho_tuc?:string,ban?:string} $vao
	 * @return array{ok:bool,chu:string,bai:int}
	 */
	public static function dang_nhanh_mot( int $id, array $vao, int $nguoi ): array {
		if ( 'de_sau' === ( $vao['lam'] ?? '' ) ) {
			$kq = DanhDau::danh( $id, $nguoi, 'de_danh' );
			return array( 'ok' => $kq['ok'], 'chu' => $kq['chu'], 'bai' => 0 );
		}
		if ( ! user_can( $nguoi, 'cadao_duyet' ) ) {
			return array( 'ok' => false, 'chu' => 'Đăng cần quyền duyệt.', 'bai' => 0 );
		}
		[ $kq, $chu ] = Lo::dang_mot_kho( $id, (string) ( $vao['ban'] ?? '' ), $nguoi );
		$bai          = 'thanh_cong' === $kq ? (int) ( Kho::lay( $id )?->post_id ?? 0 ) : 0;
		if ( ! $bai ) {
			return array( 'ok' => false, 'chu' => $chu, 'bai' => 0 );
		}
		$nc   = array( 'nguoi' => $nguoi, 'goi' => \Phongdao\MienDuLieu\KiemPhan::GOI_NGUOI, 'duyet' => true );
		$them = '';
		if ( ! empty( $vao['tho_tuc'] ) ) {
			$t     = \Phongdao\DichVu\LuuPhan\LuuPhan::luu( $bai, 'tho_tuc', array( 'nguoi_lam' => \Phongdao\MienDuLieu\NguoiLam::NGUOI_VIET ), $nc );
			$them .= $t['ok'] ? ' Đã bật "Có từ ngữ thô tục".' : ' Chưa bật được "Có từ ngữ thô tục": ' . implode( ' ', $t['loi'] );
		}
		// Chủ đề, vùng người bấm trong chip gợi ý: gắn qua đường lưu phân loại (Lịch sử), thẻ gợi ý tương ứng đóng "Đồng ý"
		$pl = array();
		foreach ( (array) ( $vao['pl'] ?? array() ) as $x ) {
			if ( preg_match( '/^(chu_de|vung):(\d+)$/', (string) $x, $k ) ) {
				$pl[ $k[1] ][] = (int) $k[2];
			}
		}
		$loai_bai = (string) \Phongdao\DichVu\DungTrang\DocBai::the_loai( get_post( $bai ) );
		foreach ( $pl as $tax => $ids ) {
			if ( ! \Phongdao\DichVu\LuuPhan\LuuPhanLoai::sua_duoc( $tax, $loai_bai ) ) {
				continue;
			}
			$cu = (array) \Phongdao\DichVu\LuuPhan\LuuPhanLoai::dang_gan( $bai, $tax );
			$t  = \Phongdao\DichVu\LuuPhan\LuuPhanLoai::luu( $bai, $tax, array_values( array_unique( array_merge( $cu, $ids ) ) ), $cu, $nguoi );
			if ( $t['ok'] ) {
				SuaTacPham::dong_goi_y_da_gan( $bai, $tax, $nguoi );
				$them .= ' Đã gắn ' . mb_strtolower( (string) ( \Phongdao\HaTang\KhaiBao::PHAN_LOAI[ $tax ][1] ?? $tax ) ) . '.';
			} else {
				$them .= ' Chưa gắn được ' . mb_strtolower( (string) ( \Phongdao\HaTang\KhaiBao::PHAN_LOAI[ $tax ][1] ?? $tax ) ) . ': ' . implode( ' ', $t['loi'] );
			}
		}
		$y = trim( str_replace( "\r", '', (string) ( $vao['y_nghia'] ?? '' ) ) );
		if ( '' !== $y ) {
			$t     = \Phongdao\DichVu\LuuPhan\LuuPhan::luu( $bai, 'y_nghia', array( 'chu' => $y, 'ai' => false, 'nguoi_lam' => \Phongdao\MienDuLieu\NguoiLam::NGUOI_VIET ), $nc );
			$nhac  = $t['ok'] ? \Phongdao\MienDuLieu\KiemTrinhBay::nhac( 'y_nghia', array( 'chu' => $y ), (array) ( \Phongdao\KhoLuu\PhanBai::lay( $bai, 'loi' )['dong'] ?? array() ) ) : array();
			$them .= $t['ok'] ? ' Đã lưu Ý nghĩa.' . ( $nhac ? ' Nhắc (không chặn lưu): ' . implode( ' ', array_column( $nhac, 'chu' ) ) : '' ) : ' Chưa lưu được Ý nghĩa: ' . implode( ' ', $t['loi'] ) . ' Mở "Sửa đầy đủ" để viết lại.';
		}
		return array( 'ok' => true, 'chu' => 'Đã đăng «' . get_the_title( $bai ) . '».' . $them, 'bai' => $bai );
	}

	public static function nhan_dang_nhanh(): void {
		$id = absint( $_POST['id'] ?? 0 );
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền với câu kho.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::DANG_NHANH . '_' . $id );
		$vao       = array_map( static fn( $v ): string => is_scalar( $v ) ? (string) $v : '', wp_unslash( array_intersect_key( $_POST, array_flip( array( 'lam', 'y_nghia', 'tho_tuc', 'ban' ) ) ) ) );
		$vao['pl'] = array_map( 'sanitize_text_field', array_filter( (array) wp_unslash( $_POST['pl'] ?? array() ), 'is_scalar' ) );
		$nguoi = get_current_user_id();
		$kq    = 'dang' === ( $vao['lam'] ?? '' ) && ! str_starts_with( (string) ( $vao['ban'] ?? '' ), 'kho:' )
			? array( 'ok' => false, 'chu' => 'Bản xem trước đã cũ; mở lại câu để kiểm tra trước khi đăng.', 'bai' => 0 )
			: self::dang_nhanh_mot( $id, $vao, $nguoi );
		set_transient( 'phongdao_kho_bao_' . $nguoi, array( 'ok' => $kq['ok'], 'chu' => $kq['chu'], 'bai' => $kq['bai'] ), MINUTE_IN_SECONDS );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_dn_giu_' . $nguoi, array( 'id' => $id, 'vao' => $vao ), 30 * MINUTE_IN_SECONDS );
		} else {
			delete_transient( 'phongdao_dn_giu_' . $nguoi );
		}
		// Đăng xong câu rời bộ lọc "chưa đăng": cùng số thẻ là câu kế; Để sau / lỗi: sang thẻ sau
		$den = esc_url_raw( (string) ( 'dang' === ( $vao['lam'] ?? '' ) ? ( $_POST['quay_lai'] ?? '' ) : ( $_POST['sau'] ?? '' ) ) );
		wp_safe_redirect( str_starts_with( $den, admin_url( 'admin.php?page=' . self::TRANG ) ) ? $den : self::url_nhanh( array() ) );
		exit;
	}

	/** Lọc nhanh của Bài mới: Máy gợi ý hôm nay (thay thẻ Câu mới hằng ngày), Để dành, Đề nghị đăng — kèm số. */
	private static function nhanh( array $loc ): string {
		$goi = UngVien::goi_y_hom_nay();
		$tat = '' === $loc['goi_y'] && '' === $loc['danh'];
		$o   = '<a class="phongdao-chip" href="' . esc_url( self::url( array( 'goi_y' => '', 'danh' => '' ) + $loc ) ) . '"' . ( $tat ? ' aria-current="true"' : '' ) . '>Tất cả <span class="phongdao-chip-so">' . esc_html( number_format_i18n( (int) ( Kho::dem()['moi'] ?? 0 ) ) ) . '</span></a>';
		foreach (
			array(
				array( 'goi_y', 'hom_nay', 'Máy gợi ý hôm nay', count( $goi['ids'] ) ),
				array( 'danh', 'de_danh', 'Để dành', DanhDau::dem( 'de_danh' ) ),
				array( 'danh', 'de_nghi', 'Đề nghị đăng', DanhDau::dem( 'de_nghi' ) ),
			) as [ $khoa, $gia_tri, $chu, $so ]
		) {
			$dang = $gia_tri === $loc[ $khoa ];
			$moi  = array( $khoa => $dang ? '' : $gia_tri ) + ( 'goi_y' === $khoa ? array( 'danh' => '' ) : array( 'goi_y' => '' ) ) + $loc;
			$o   .= '<a class="phongdao-chip" href="' . esc_url( self::url( $moi ) ) . '"' . ( $dang ? ' aria-current="true"' : '' ) . '>' . esc_html( $chu ) . ' <span class="phongdao-chip-so">' . esc_html( number_format_i18n( $so ) ) . '</span></a>';
		}
		return '<nav class="phongdao-hv-tab" aria-label="Nhóm câu trong Kho">' . $o . '</nav>';
	}

	/**
	 * Khối "Nhập câu từ tệp" (gập): dán danh sách hoặc chọn tệp chữ (câu đã gõ từ một cuốn sách), chọn hồ sơ nguồn và thể
	 * loại; máy tách câu, đưa vào Kho qua chống trùng — không đăng thẳng. Hồ sơ để trống thì câu không có nguồn.
	 */
	private static function khoi_nhap(): string {
		return NhapKho::form();
	}

	/**
	 * Khối "Làm với nhiều câu": phạm vi đã chọn trên trang / mọi câu khớp bộ lọc, rồi "Xem trước và đăng" (người có quyền
	 * duyệt, D-101) hoặc "Đưa vào Hộp việc" — cả hai sang một màn xem trước của lô. Nút "Xem trước và đăng" trên từng dòng
	 * cũng gửi form này (tên `mot`).
	 */
	private static function khoi_lo( array $loc, int $so, bool $duyet ): string {
		$an  = '';
		foreach ( array( 'q', 'nguon', 'loai', 'tt', 'the', 'goi_y', 'danh', 'dac', 'lan_nhap' ) as $k ) {
			$an .= '<input type="hidden" name="loc_' . $k . '" value="' . esc_attr( (string) $loc[ $k ] ) . '">';
		}
		$qua = $so > Lo::TOI_DA ? ' — một lô tối đa ' . number_format_i18n( Lo::TOI_DA ) . ' câu, lọc theo nguồn hoặc thể loại để chia nhỏ' : '';
		$ten = $duyet ? 'Đăng hoặc để dành nhiều câu' : 'Đề nghị đăng nhiều câu';
		return '<form id="' . self::FORM_LO . '" class="phongdao-lo-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><details class="phongdao-lo-gon"><summary>' . esc_html( $ten ) . '<span class="phongdao-lo-dem" aria-live="polite"></span></summary><fieldset class="phongdao-lo-chon"><legend class="screen-reader-text">' . esc_html( $ten ) . '</legend>'
			. '<input type="hidden" name="action" value="' . TrangLo::TAO_KHO . '">' . wp_nonce_field( TrangLo::TAO_KHO, '_wpnonce', false, false ) . $an
			. '<fieldset class="phongdao-lo-pham-vi"><legend>Phạm vi</legend>'
			. '<label><input type="radio" name="pham_vi" value="chon" checked> Các câu đã chọn trên trang này</label>'
			. '<label><input type="radio" name="pham_vi" value="loc"' . disabled( 0 === $so, true, false ) . '> Mọi câu khớp bộ lọc còn đưa vào được (' . esc_html( number_format_i18n( $so ) ) . ' câu' . esc_html( $qua ) . ')</label></fieldset>'
			. '<p class="phongdao-lo-nut">' . ( $duyet ? '<button type="submit" name="lam" value="dang" class="button button-primary">Xem trước và đăng</button> ' : '' )
			. '<button type="submit" name="lam" value="de_danh" class="button">' . esc_html( DanhDau::LOAI[ DanhDau::loai_cua( get_current_user_id() ) ] ) . ' các câu đã chọn</button></p>'
			. '<p class="description">Chỉ tính câu Chưa dùng, có lời. ' . ( $duyet ? '"Xem trước và đăng" mở một màn đọc lại rồi bấm Đăng; câu có cờ của nguồn để sẵn bỏ chọn, câu chờ quyết trùng bỏ qua; chưa có gì được đăng cho tới khi bấm Đăng ở đó. ' : '' ) . 'Dấu chỉ để lọc lại, không đăng gì.</p>'
			. '</fieldset></details></form>';
	}

	/**
	 * Đánh / bỏ dấu Để dành, Đề nghị đăng: một câu (nút trên hàng, nonce từng câu) hoặc các câu đã chọn (thanh nổi, nonce của
	 * form lô). Chỉ là dấu: không đăng, không tạo thẻ. Về lại đúng trang danh sách kèm lời báo.
	 */
	public static function danh_dau(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền đánh dấu câu kho.', '', array( 'response' => 403 ) );
		}
		$id    = absint( $_POST['id'] ?? 0 );
		$nguoi = get_current_user_id();
		if ( $id ) {
			check_admin_referer( self::DANH_DAU . '_' . $id );
			$ids = array( $id );
		} else {
			check_admin_referer( TrangLo::TAO_KHO );
			$ids = array_slice( array_map( 'absint', (array) wp_unslash( $_POST['ids'] ?? array() ) ), 0, Lo::TOI_DA );
		}
		$bo  = 'bo' === ( $_POST['lam'] ?? '' );
		$dem = 0;
		$chu = '';
		foreach ( $ids as $k ) {
			if ( $bo ) {
				$dem += DanhDau::bo( $k, $nguoi ) ? 1 : 0;
				continue;
			}
			$kq   = DanhDau::danh( $k, $nguoi );
			$dem += $kq['moi'] ? 1 : 0;
			$chu  = $kq['chu'];
		}
		$ten = DanhDau::LOAI[ DanhDau::loai_cua( $nguoi ) ];
		set_transient( 'phongdao_kho_bao_' . $nguoi, array( 'ok' => $bo || $dem > 0 || 1 === count( $ids ), 'chu' => $bo ? 'Đã bỏ dấu ' . $dem . ' câu.' : ( 1 === count( $ids ) ? $chu : 'Đã đánh dấu "' . $ten . '" cho ' . $dem . ' câu. Câu nằm lại Bài mới, lọc lại được.' ), 'viec' => 0 ), MINUTE_IN_SECONDS );
		$quay_lai = esc_url_raw( wp_unslash( (string) ( $_POST['quay_lai'] ?? '' ) ) );
		if ( '' === $quay_lai ) {
			$vao = array();
			foreach ( array( 'q', 'nguon', 'loai', 'tt', 'the', 'goi_y', 'danh', 'dac' ) as $k ) {
				$vao[ $k ] = (string) ( $_POST[ 'loc_' . $k ] ?? '' );
			}
			$quay_lai = self::url( self::loc( $vao ) );
		}
		wp_safe_redirect( str_starts_with( $quay_lai, admin_url( 'admin.php?page=' . self::TRANG ) ) ? $quay_lai : self::url() );
		exit;
	}

	/** Đối soát chỉ khi người bấm POST, mở cả thẻ đang để sau bằng đúng bộ lọc. */
	public static function kiem_trung(): void {
		$id = absint( $_POST['id'] ?? 0 );
		if ( ! current_user_can( 'cadao_soan' ) || ! $id ) {
			wp_die( 'Bạn không có quyền kiểm tra câu kho.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::DOI_SOAT . '_' . $id );
		$kq = ChongTrung::doi_soat_cho( $id, get_current_user_id() );
		set_transient( 'phongdao_kho_bao_' . get_current_user_id(), array( 'ok' => $kq['ok'], 'chu' => $kq['thong_bao'], 'viec' => 0 ), MINUTE_IN_SECONDS );
		$quay = esc_url_raw( wp_unslash( (string) ( $_POST['quay_lai'] ?? '' ) ) );
		$quay = str_starts_with( $quay, admin_url( 'admin.php?page=' . self::TRANG ) ) ? $quay : self::url();
		wp_safe_redirect( $kq['ok'] && $kq['viec'] ? DanhSachViec::url( array( 'loai' => 'co_the_trung', 'tt' => $kq['tt'] ), 1, $kq['viec'] ) : $quay );
		exit;
	}

	/** Nút "Đưa vào Hộp việc" (lối cũ, giữ cho đường dẫn đã lưu): kiểm quyền, nonce từng câu; ghi qua UngVien. */
	public static function dua_vao(): void {
		$id = absint( $_POST['id'] ?? 0 );
		if ( ! current_user_can( 'cadao_soan' ) || ! $id ) {
			wp_die( 'Bạn không có quyền đưa câu vào Hộp việc.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::HANH_DONG . '_' . $id );
		$kq = UngVien::dua_vao_hop_viec( $id, get_current_user_id() );
		set_transient( 'phongdao_kho_bao_' . get_current_user_id(), array( 'ok' => $kq['ok'], 'chu' => $kq['thong_bao'], 'viec' => $kq['viec'] ), MINUTE_IN_SECONDS );
		$quay_lai = esc_url_raw( wp_unslash( (string) ( $_POST['quay_lai'] ?? '' ) ) );
		wp_safe_redirect( str_starts_with( $quay_lai, admin_url( 'admin.php?page=' . self::TRANG ) ) ? $quay_lai : self::url() );
		exit;
	}
}
