<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\DichVu\UngVien\UngVien;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Màn Kho trong wp-admin (docs/30 mục 2, 4.6; T-184): người biên soạn tự tìm, lọc câu đã thu thập và đưa một câu đủ điều
 * kiện vào Hộp việc. Kho là tư liệu riêng, không phải Hộp việc: nút chỉ tạo thẻ "Câu mới" đang chờ (UngVien), không đăng,
 * gộp, sửa lời hay bỏ câu. Dữ liệu kho chỉ hiện cho người có `cadao_soan`; không bao giờ ra trang người đọc.
 */
final class DanhSachKho {

	public const TRANG     = 'phongdao-kho';
	public const HANH_DONG = 'phongdao_kho_dua_vao';
	private const MOI_TRANG = 30;
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
	/** Cờ máy đặt khi đọc nguồn => chữ hiện cho người biên soạn. */
	private const CO = array(
		'tho_tuc'       => 'có từ thô tục',
		'hien_dai'      => 'nghi là câu hiện đại',
		'can_xem'       => 'nguồn ghi cần xem',
		'lech_api_html' => 'lời giữa hai lối lấy khác nhau',
	);

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'dua_vao' ) );
	}

	/** @return array<string,string|int> bộ lọc đã kiểm; SQL và URL chỉ nhận các giá trị này */
	private static function loc(): array {
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc bộ lọc
		$nguon = sanitize_text_field( wp_unslash( (string) ( $_GET['nguon'] ?? '' ) ) );
		$loai  = sanitize_key( (string) ( $_GET['loai'] ?? '' ) );
		$tt    = sanitize_key( (string) ( $_GET['tt'] ?? 'moi' ) );
		$the   = sanitize_key( (string) ( $_GET['the'] ?? 'tat_ca' ) );
		$loc   = array(
			'q'     => trim( sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ) ),
			'nguon' => preg_match( '/^[a-z0-9_-]{1,64}$/', $nguon ) ? $nguon : '',
			'loai'  => isset( TheLoai::TU_LOAI_GOC[ $loai ] ) ? $loai : '',
			'tt'    => isset( self::TRANG_THAI[ $tt ] ) ? $tt : 'moi',
			'the'   => isset( self::THE[ $the ] ) ? $the : 'tat_ca',
			'paged' => max( 1, (int) ( $_GET['paged'] ?? 1 ) ),
		);
		// phpcs:enable
		return $loc;
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
		return implode( ' AND ', $dk );
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
	 * Thẻ Câu mới đang mở và cặp chờ quyết trùng của đúng các câu trên trang (hai truy vấn, không theo từng dòng).
	 *
	 * @return array{the:array<int,int>,trung:array<int,bool>}
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
			$trung[ (int) substr( (string) $a, 4 ) ] = true;
		}
		$bai = array_filter( array_map( static fn( object $m ): int => (int) $m->post_id, $ds ) );
		if ( $bai ) {
			_prime_post_caches( $bai, false, false );
		}
		return array( 'the' => $the, 'trung' => $trung );
	}

	public static function url( array $loc = array(), int $trang = 1 ): string {
		$loc += array( 'q' => '', 'nguon' => '', 'loai' => '', 'tt' => 'moi', 'the' => 'tat_ca' );
		return add_query_arg(
			array_filter(
				array( 'page' => self::TRANG, 'q' => $loc['q'], 'nguon' => $loc['nguon'], 'loai' => $loc['loai'], 'tt' => 'moi' === $loc['tt'] ? '' : $loc['tt'], 'the' => 'tat_ca' === $loc['the'] ? '' : $loc['the'], 'paged' => $trang > 1 ? $trang : '' ),
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

	private static function ten_loai( string $loai ): string {
		return TheLoai::DS[ TheLoai::TU_LOAI_GOC[ $loai ] ?? '' ] ?? ( '' === $loai ? 'Chưa rõ' : $loai );
	}

	private static function ngay( string $mysql ): string {
		return '' === $mysql ? '' : (string) mysql2date( 'd/m/Y', $mysql );
	}

	private static function dong( object $m, array $tt_trang, array $nguon, string $quay_lai ): string {
		$id   = (int) $m->id;
		$d    = Kho::du_lieu( $m );
		$dong = array_values( array_filter( explode( "\n", (string) $m->loi ), static fn( string $l ): bool => '' !== trim( $l ) ) );
		$ten  = '' !== trim( (string) $m->tieu_de ) ? (string) $m->tieu_de : (string) ( $dong[0] ?? '' );
		$cau  = '<strong>' . esc_html( $ten ) . '</strong><span class="phongdao-kho-loi">' . implode( '<br>', array_map( 'esc_html', array_slice( $dong, 0, 4 ) ) ) . '</span>'
			. ( count( $dong ) > 4 ? '<span class="description">… ' . count( $dong ) . ' dòng</span>' : '' );
		$them = array( count( (array) ( $d['chu_thich'] ?? array() ) ) . ' chú thích', count( (array) ( $d['di_ban'] ?? array() ) ) . ' dị bản' );
		$co   = array_map( static fn( $c ): string => self::CO[ $c ] ?? (string) $c, (array) ( $d['co'] ?? array() ) );
		$cau .= '<span class="description">' . esc_html( implode( ' · ', $them ) ) . '</span>' . ( $co ? '<span class="phongdao-kho-co">Cờ: ' . esc_html( implode( ', ', $co ) ) . '</span>' : '' );

		$ten_nguon = (string) ( $nguon[ $m->nguon_id ]['ten'] ?? $m->nguon_id );
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
		$trung = ! empty( $tt_trang['trung'][ $id ] );
		if ( $trung ) {
			$tt[] = 'Đang chờ quyết trùng';
		}

		$ly_do = UngVien::chua_chon_duoc( $m );
		if ( '' === $ly_do && ! $the && ! $trung ) {
			$lam = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::HANH_DONG . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="quay_lai" value="' . esc_attr( $quay_lai ) . '">'
				. wp_nonce_field( self::HANH_DONG . '_' . $id, '_wpnonce', false, false )
				. '<button type="submit" class="button button-primary" aria-label="' . esc_attr( 'Đưa vào Hộp việc: câu #' . $id ) . '">Đưa vào Hộp việc</button></form>';
		} else {
			$lam = '<span class="description">' . esc_html( $the ? 'Đã có trong Hộp việc' : ( $trung ? 'Quyết trùng trước' : $ly_do ) ) . '</span>';
		}
		return '<tr><td data-nhan="ID">' . $id . '</td><td data-nhan="Câu" class="phongdao-kho-cau">' . $cau . '</td><td data-nhan="Thể loại">' . esc_html( self::ten_loai( (string) $m->loai ) ) . '</td><td data-nhan="Nguồn">' . $goc . '</td><td data-nhan="Trạng thái">' . implode( '<br>', $tt ) . '</td><td data-nhan="Làm gì">' . $lam . '</td></tr>';
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
			$ds_nguon[ (string) $id ] = (string) ( $nguon[ $id ]['ten'] ?? $id );
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
		$form = '<form class="phongdao-tp-loc" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '"><div class="phongdao-tp-truong"><label for="phongdao-kho-q">Tìm ID hoặc chữ trong lời</label><input id="phongdao-kho-q" type="search" name="q" value="' . esc_attr( (string) $loc['q'] ) . '"></div>'
			. self::chon( 'nguon', 'Nguồn', $ds_nguon, (string) $loc['nguon'] )
			. self::chon( 'loai', 'Thể loại', $ds_loai, (string) $loc['loai'] )
			. self::chon( 'tt', 'Trạng thái', self::TRANG_THAI, (string) $loc['tt'] )
			. self::chon( 'the', 'Hộp việc', self::THE, (string) $loc['the'] )
			. '<button class="button button-primary" type="submit">Tìm và lọc</button></form>';
		$quay_lai = self::url( $loc, (int) $loc['paged'] );
		$dong     = implode( '', array_map( static fn( object $m ): string => self::dong( $m, $tt, $nguon, $quay_lai ), $kq['ds'] ) );
		$so_trang = (int) ceil( $kq['tong'] / self::MOI_TRANG );
		$phan_trang = $so_trang > 1 ? '<nav class="phongdao-tp-trang" aria-label="Phân trang"><span>Trang ' . (int) $loc['paged'] . ' / ' . $so_trang . '</span>'
			. ( $loc['paged'] > 1 ? '<a class="button" href="' . esc_url( self::url( $loc, (int) $loc['paged'] - 1 ) ) . '">Trang trước</a>' : '' )
			. ( $loc['paged'] < $so_trang ? '<a class="button" href="' . esc_url( self::url( $loc, (int) $loc['paged'] + 1 ) ) . '">Trang sau</a>' : '' ) . '</nav>' : '';
		return '<div class="wrap phongdao-admin-tp phongdao-admin-kho"><h1>Kho</h1>'
			. '<p class="description">Câu máy đã thu thập từ nguồn, chưa lên trang. Tự chọn câu muốn đăng: bấm "Đưa vào Hộp việc" để tạo thẻ Câu mới, rồi quyết Đăng, Sửa rồi đăng hay Bỏ ở Hộp việc. Nút không tự đăng, không sửa lời.</p>'
			. $bao . $form
			. '<p class="phongdao-tp-tong" role="status"><strong>' . (int) $kq['tong'] . '</strong> câu khớp bộ lọc. Hiện ' . count( $kq['ds'] ) . ' câu trên trang này.</p>'
			. '<table class="widefat striped"><thead><tr><th scope="col">ID</th><th scope="col">Câu</th><th scope="col">Thể loại</th><th scope="col">Nguồn</th><th scope="col">Trạng thái</th><th scope="col">Làm gì</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="6">Không có câu nào khớp. Thử đổi bộ lọc.</td></tr>' ) . '</tbody></table>' . $phan_trang . '</div>';
	}

	/** Nút "Đưa vào Hộp việc": kiểm quyền, nonce từng câu; ghi qua UngVien; quay về đúng trang danh sách. */
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
