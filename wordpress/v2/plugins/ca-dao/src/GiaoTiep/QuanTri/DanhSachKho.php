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

	public static function dang_ky(): void {
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
		);
	}

	/** Điều kiện SQL của bộ lọc (bảng kho đặt bí danh k). Câu chưa có lời (chờ đọc trang) không hiện. */
	private static function dieu_kien( array $loc ): string {
		global $wpdb;
		$dk = array( "k.loi <> ''" );
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
		return self::dieu_kien( $loc ) . " AND k.trang_thai = 'moi' AND k.post_id = 0 AND NOT EXISTS (SELECT 1 FROM " . Viec::bang() . " v WHERE v.doi_tuong_loai = 'kho' AND v.doi_tuong_id = k.id AND v.loai = '" . UngVien::VIEC . "' AND v.trang_thai IN ('cho','de_sau'))";
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
		$bai = array_filter( array_map( static fn( object $m ): int => (int) $m->post_id, $ds ) );
		if ( $bai ) {
			_prime_post_caches( $bai, false, false );
		}
		return array( 'the' => $the, 'trung' => $trung );
	}

	public static function url( array $loc = array(), int $trang = 1 ): string {
		$loc += array( 'q' => '', 'nguon' => '', 'loai' => '', 'tt' => 'moi', 'the' => 'tat_ca', 'goi_y' => '', 'danh' => '', 'dac' => '' );
		return add_query_arg(
			array_filter(
				array( 'page' => self::TRANG, 'q' => $loc['q'], 'nguon' => $loc['nguon'], 'loai' => $loc['loai'], 'tt' => 'moi' === $loc['tt'] ? '' : $loc['tt'], 'the' => 'tat_ca' === $loc['the'] ? '' : $loc['the'], 'goi_y' => $loc['goi_y'], 'danh' => $loc['danh'], 'dac' => $loc['dac'], 'paged' => $trang > 1 ? $trang : '' ),
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

	private static function dong( object $m, array $tt_trang, array $nguon, string $quay_lai, array $dau = array() ): string {
		$id   = (int) $m->id;
		$cau  = self::o_cau( $m );

		$ten_nguon = NguonTuDong::ten( (string) $m->nguon_id );
		$doc       = (string) $m->doc_trang;
		$goc       = esc_html( $ten_nguon ) . '<br><span class="description">Lấy ' . esc_html( self::ngay( (string) $m->ngay_lay ) )
			. ( '' === $doc ? ' · chưa đọc trang gốc' : ( preg_match( '/^\d{4}-/', $doc ) ? '' : ' · không đọc được trang gốc' ) ) . '</span>'
			. ( '' === (string) $m->url ? '' : '<br><a href="' . esc_url( (string) $m->url ) . '" rel="noreferrer nofollow">Trang gốc</a>' );

		$tt   = array( esc_html( self::TRANG_THAI[ $m->trang_thai ] ?? (string) $m->trang_thai ) );
		$bai  = (int) $m->post_id ? get_post( (int) $m->post_id ) : null;
		if ( $bai ) {
			$tt[] = current_user_can( 'edit_post', $bai->ID ) ? '<a href="' . esc_url( SuaTacPham::url( $bai->ID ) ) . '">Bài #' . (int) $bai->ID . '</a>' : 'Bài #' . (int) $bai->ID;
		} elseif ( 'da_gop' === $m->trang_thai && '' !== (string) $m->gop_vao ) {
			$tt[] = esc_html( 'vào ' . $m->gop_vao . ( '' === (string) $m->gop_lam ? '' : ' (' . ( 'di_ban' === $m->gop_lam ? 'làm dị bản' : 'cùng bài' ) . ')' ) );
		}
		$the = $tt_trang['the'][ $id ] ?? 0;
		if ( $the ) {
			$tt[] = '<a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => $the ), admin_url( 'admin.php' ) ) ) . '">Thẻ Câu mới #' . $the . '</a>';
		}
		$dau_cau = $dau[ $id ] ?? null;
		if ( $dau_cau ) {
			$ai   = get_userdata( (int) $dau_cau['nguoi'] );
			$tt[] = '<strong>' . esc_html( DanhDau::LOAI[ $dau_cau['loai'] ] ?? '' ) . '</strong>' . ( $ai ? ' <span class="description">(' . esc_html( $ai->display_name ) . ')</span>' : '' );
		}
		$trung    = array_key_exists( $id, $tt_trang['trung'] );
		if ( $trung ) {
			$tt[] = 'Đang chờ quyết trùng';
		}

		$ly_do = UngVien::chua_chon_duoc( $m );
		$chon  = '' === $ly_do && ! $the && ! $trung
			? '<input type="checkbox" name="ids[]" value="' . $id . '" form="' . self::FORM_LO . '" id="chon-kho-' . $id . '"><label for="chon-kho-' . $id . '" class="screen-reader-text">Chọn câu #' . $id . '</label>'
			: '';
		if ( '' === $ly_do && ! $the && ! $trung ) {
			// Thao tác nhỏ trên hàng (không còn 30 nút chính trên một trang): xem trước và đăng một câu (gửi form lô), đưa vào Hộp việc
			// "Để dành / Đề nghị đăng" thay "Đưa vào Hộp việc" (D-104): câu nằm lại Bài mới, lọc lại được
			$dang  = current_user_can( Lo::THAO_TAC['kho_dang'][2] ) ? '<button type="submit" form="' . self::FORM_LO . '" name="mot" value="' . $id . '" class="button button-small" aria-label="' . esc_attr( 'Xem trước và đăng: câu #' . $id ) . '">Xem trước và đăng</button>' : '';
			$nhan  = $dau_cau ? 'Bỏ dấu' : DanhDau::LOAI[ DanhDau::loai_cua( get_current_user_id() ) ];
			$lam   = '<form method="post" class="phongdao-kho-lam" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::DANH_DAU . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="lam" value="' . ( $dau_cau ? 'bo' : 'danh' ) . '"><input type="hidden" name="quay_lai" value="' . esc_attr( $quay_lai ) . '">'
				. wp_nonce_field( self::DANH_DAU . '_' . $id, '_wpnonce', false, false )
				. $dang . '<button type="submit" class="button button-small" aria-label="' . esc_attr( $nhan . ': câu #' . $id ) . '">' . esc_html( $nhan ) . '</button></form>';
		} else {
			$lam = '<span class="description">' . esc_html( $the ? 'Đã có trong Hộp việc' : ( $trung ? 'Quyết trùng trước' : $ly_do ) ) . '</span>';
			if ( $trung ) {
				$lam .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::DOI_SOAT . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="quay_lai" value="' . esc_attr( $quay_lai ) . '">'
					. wp_nonce_field( self::DOI_SOAT . '_' . $id, '_wpnonce', false, false ) . '<button type="submit" class="button button-small">Kiểm tra chờ trùng</button></form>';
			}
		}
		// Điện thoại (thẻ ba dòng): câu; thể loại · nguồn; trạng thái và nút — các cột ID, Thể loại, Nguồn ẩn
		$dong2 = '<span class="phongdao-tp-dong2">' . esc_html( self::ten_loai( (string) $m->loai ) . ' · ' . $ten_nguon ) . '</span>';
		return '<tr><td class="phongdao-tp-chon" data-nhan="Chọn">' . $chon . '</td><td class="phongdao-tp-an-dt" data-nhan="ID">' . $id . '</td><td class="phongdao-kho-cau phongdao-tp-ten" data-nhan="Câu">' . $cau . $dong2 . '</td><td class="phongdao-tp-an-dt" data-nhan="Thể loại">' . esc_html( self::ten_loai( (string) $m->loai ) ) . '</td><td class="phongdao-tp-an-dt" data-nhan="Nguồn">' . $goc . '</td><td class="phongdao-kho-tt" data-nhan="Trạng thái">' . implode( '<br>', $tt ) . '</td><td class="phongdao-kho-lam-o" data-nhan="Làm gì">' . $lam . '</td></tr>';
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><h1>Kho</h1><p>Bạn không có quyền xem kho thu thập.</p></div>';
		}
		global $wpdb;
		$loc   = self::loc();
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
		$bao = '';
		$ket = get_transient( 'phongdao_kho_bao_' . get_current_user_id() );
		if ( is_array( $ket ) ) {
			delete_transient( 'phongdao_kho_bao_' . get_current_user_id() );
			$mo  = (int) ( $ket['viec'] ?? 0 ) ? ' <a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => (int) $ket['viec'] ), admin_url( 'admin.php' ) ) ) . '">Mở thẻ</a>' : '';
			$bao = '<div class="notice ' . ( empty( $ket['ok'] ) ? 'notice-error' : 'notice-success' ) . '"><p role="status">' . esc_html( (string) ( $ket['chu'] ?? '' ) ) . $mo . '</p></div>';
		}
		// Ô tìm luôn hiện; bộ lọc còn lại gập trong "Thêm bộ lọc" (mở sẵn khi đang dùng) — điện thoại thấy câu ngay màn đầu
		$dang_loc = '' !== $loc['nguon'] || '' !== $loc['loai'] || 'moi' !== $loc['tt'] || 'tat_ca' !== $loc['the'] || '' !== $loc['dac'] || in_array( $loc['danh'], array( 'tat_ca' ), true );
		$form = '<form class="phongdao-tp-tim" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '">'
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
		$dong     = implode( '', array_map( static fn( object $m ): string => self::dong( $m, $tt, $nguon, $quay_lai, $dau ), $kq['ds'] ) );
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
		return '<div class="wrap phongdao-admin-tp phongdao-admin-kho"><h1 class="wp-heading-inline">Bài mới</h1> <a class="page-title-action" href="' . esc_url( ThemTacPham::url() ) . '">Thêm tác phẩm</a><hr class="wp-header-end"><h2 class="phongdao-kho-dau">Câu trong Kho</h2>'
			. '<p class="description">' . ( $duyet
				? 'Câu máy đã thu thập, chưa lên trang. Chọn câu rồi bấm "Xem trước và đăng": đọc lại một lượt, bấm Đăng là xong. Câu muốn quyết sau thì "Để dành". Không có gì được đăng khi bạn chưa bấm; máy không sửa lời.'
				: 'Câu máy đã thu thập, chưa lên trang. Câu nên đăng thì bấm "Đề nghị đăng": người có quyền duyệt thấy số đề nghị ở Hôm nay và đăng qua màn xem trước. Nút không đăng, không sửa lời.' ) . '</p>'
			. $bao . self::nhanh( $loc ) . $form
			. '<p class="phongdao-tp-tong" role="status"><strong>' . (int) $kq['tong'] . '</strong> câu khớp bộ lọc. Hiện ' . count( $kq['ds'] ) . ' câu trên trang này.</p>'
			. ( $kq['tong'] > 0 ? self::khoi_lo( $loc, $so_chon, $duyet ) . TrangLo::chon_het( $so_chon, 'câu' ) : '' )
			. '<table class="widefat striped phongdao-tp-bang"><thead><tr><th scope="col">Chọn</th><th scope="col">ID</th><th scope="col">Câu</th><th scope="col">Thể loại</th><th scope="col">Nguồn</th><th scope="col">Trạng thái</th><th scope="col">Làm gì</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="7">' . ( 'hom_nay' === $loc['goi_y'] && ! UngVien::goi_y_hom_nay()['ids'] ? 'Máy chưa có gợi ý hôm nay (máy chọn lúc 06:00 cho các nguồn đang bật).' : 'Không có câu nào khớp. Thử đổi bộ lọc.' ) . '</td></tr>' ) . '</tbody></table>' . $phan_trang . $noi
			. self::khoi_nhap() . '</div>';
	}

	/** Lọc nhanh của Bài mới: Máy gợi ý hôm nay (thay thẻ Câu mới hằng ngày), Để dành, Đề nghị đăng — kèm số. */
	private static function nhanh( array $loc ): string {
		$goi = UngVien::goi_y_hom_nay();
		$o   = '';
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
		return '<nav class="phongdao-hv-tab" aria-label="Lọc nhanh câu kho">' . $o . '</nav>';
	}

	/**
	 * Khối "Nhập câu từ tệp" (gập): dán danh sách hoặc chọn tệp chữ (câu đã gõ từ một cuốn sách), chọn hồ sơ nguồn và thể
	 * loại; máy tách câu, đưa vào Kho qua chống trùng — không đăng thẳng. Hồ sơ để trống thì câu không có nguồn.
	 */
	private static function khoi_nhap(): string {
		$ho_so = '<option value="0">Không gắn hồ sơ nguồn</option>';
		foreach ( NhapTep::ho_so_chon() as $id => $ten ) {
			$ho_so .= '<option value="' . (int) $id . '">' . esc_html( $ten ) . '</option>';
		}
		$loai = '';
		foreach ( array_keys( TheLoai::TU_LOAI_GOC ) as $l ) {
			$loai .= '<option value="' . esc_attr( $l ) . '">' . esc_html( self::ten_loai( $l ) ) . '</option>';
		}
		return '<details class="phongdao-tp-them phongdao-kho-nhap"><summary>Nhập câu từ tệp vào Kho</summary>'
			. '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . NhapTep::HANH_DONG . '">' . wp_nonce_field( NhapTep::HANH_DONG, '_wpnonce', true, false )
			. '<p class="description">Câu bạn đã gõ từ một cuốn sách: mỗi câu cách nhau <strong>một dòng trống</strong>, giữ nguyên từng dòng trong câu. Máy tách câu và đưa vào Kho qua chống trùng; <strong>không đăng thẳng</strong>, không sửa lời. Mỗi lần tối đa ' . esc_html( number_format_i18n( NhapTep::TOI_DA ) ) . ' câu.</p>'
			. '<div class="phongdao-tp-loc"><div class="phongdao-tp-truong"><label for="phongdao-nhap-ho-so">Hồ sơ nguồn (sách)</label><select id="phongdao-nhap-ho-so" name="ho_so">' . $ho_so . '</select></div>'
			. '<div class="phongdao-tp-truong"><label for="phongdao-nhap-loai">Thể loại</label><select id="phongdao-nhap-loai" name="loai">' . $loai . '</select></div></div>'
			. '<p><label for="phongdao-nhap-chu">Dán danh sách câu</label><br><textarea id="phongdao-nhap-chu" name="chu" rows="8" class="large-text"></textarea></p>'
			. '<p><label for="phongdao-nhap-tep">Hoặc chọn tệp chữ (.txt, UTF-8, tối đa 1 MB)</label><br><input id="phongdao-nhap-tep" type="file" name="tep" accept=".txt,text/plain"></p>'
			. '<p><button type="submit" class="button button-primary">Đưa vào Kho</button></p></form></details>';
	}

	/**
	 * Khối "Làm với nhiều câu": phạm vi đã chọn trên trang / mọi câu khớp bộ lọc, rồi "Xem trước và đăng" (người có quyền
	 * duyệt, D-101) hoặc "Đưa vào Hộp việc" — cả hai sang một màn xem trước của lô. Nút "Xem trước và đăng" trên từng dòng
	 * cũng gửi form này (tên `mot`).
	 */
	private static function khoi_lo( array $loc, int $so, bool $duyet ): string {
		$an  = '';
		foreach ( array( 'q', 'nguon', 'loai', 'tt', 'the', 'goi_y', 'danh', 'dac' ) as $k ) {
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
