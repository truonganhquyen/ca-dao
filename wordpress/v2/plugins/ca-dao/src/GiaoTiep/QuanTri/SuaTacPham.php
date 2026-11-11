<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\LuuPhan\LichSu;
use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\GiaoTiep\BienTap\CheDoSua;
use Phongdao\GiaoTiep\BienTap\Module as BienTap;
use Phongdao\GiaoTiep\BienTap\TamSua;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\SoSanh;

defined( 'ABSPATH' ) || exit;

/**
 * Sửa từng phần trong wp-admin bằng đúng dịch vụ của tấm sửa trên trang đọc. Một màn hai cột (T-188): bên trái lời (luôn
 * hiện, có số dòng) và các phần — phần đã có mở sẵn, phần "Chưa có" gập lại; bên phải (cố định khi cuộn ở màn rộng) trạng
 * thái kèm nút Đăng / Ẩn / Về nháp, nguồn, việc đang chờ, phân loại, lịch sử rút gọn có nút khôi phục. Đến từ một danh sách
 * Tác phẩm đã lọc thì có lối "Bài tiếp theo" và tuỳ chọn lưu xong tự sang bài tiếp.
 */
final class SuaTacPham {
	public const TRANG = 'phongdao-sua-tac-pham';

	public static function dang_ky(): void {
		add_action( 'admin_post_phongdao_sua_phan', array( self::class, 'luu' ) );
		add_action( 'admin_post_phongdao_sua_phan_loai', array( self::class, 'luu_phan_loai' ) );
		add_action( 'admin_post_phongdao_khoi_phuc', array( self::class, 'khoi_phuc' ) );
		add_action( 'admin_post_phongdao_doi_trang_thai', array( self::class, 'doi_trang_thai' ) );
	}

	/** Tên trạng thái bài cho người đọc màn. */
	private const TEN_TRANG_THAI = array( 'publish' => 'Đã đăng', 'draft' => 'Bản nháp', 'pending' => 'Chờ', 'private' => 'Đã ẩn', 'future' => 'Đã hẹn' );

	/** Địa chỉ danh sách Tác phẩm (đúng bộ lọc, trang) để quay về sau khi sửa (T-172, kịch bản 1). */
	private static string $ve = '';

	/** Chỉ nhận trang danh sách Tác phẩm của site; còn lại bỏ. */
	public static function ve_hop_le( string $ve ): string {
		$ve = esc_url_raw( $ve );
		return str_starts_with( $ve, admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) ) ? $ve : '';
	}

	public static function url( int $id, string $ve = '' ): string {
		$ve = '' !== $ve ? $ve : self::$ve;
		return add_query_arg( array_filter( array( 'page' => self::TRANG, 'id' => $id, 've' => '' === $ve ? '' : rawurlencode( $ve ) ) ), admin_url( 'admin.php' ) );
	}

	private static function an_ve(): string {
		return '' === self::$ve ? '' : '<input type="hidden" name="ve" value="' . esc_attr( self::$ve ) . '">';
	}

	/** @return string[] */
	private static function phan_theo_loai( string $loai ): array {
		$ra = array();
		$rieng = array( 'nghia_den' => 'tuc-ngu', 'nghia_bong' => 'tuc-ngu', 'dung_khi' => 'tuc-ngu', 'cau_hoi' => 'cau-do', 'dap_an' => 'cau-do', 'goi_y' => 'cau-do', 'boi_canh' => 've', 'doan' => 've' );
		foreach ( array_keys( TamSua::TAM ) as $phan ) {
			if ( ! isset( $rieng[ $phan ] ) || $loai === $rieng[ $phan ] ) {
				$ra[] = $phan;
			}
		}
		return $ra;
	}

	public static function hien(): string {
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_GET['ve'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ là lối quay về, đã kiểm
		$id = absint( $_GET['id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$bai = $id ? get_post( $id ) : null;
		if ( ! current_user_can( 'cadao_soan' ) || ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || ! current_user_can( 'edit_post', $id ) || 'trash' === $bai->post_status ) {
			return '<div class="wrap"><h1>Sửa tác phẩm</h1><p>Chọn tác phẩm có quyền sửa từ <a href="' . esc_url( admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) ) . '">danh sách Tác phẩm</a>.</p></div>';
		}
		$ctx = CheDoSua::ngu_canh( $bai );
		if ( null === $ctx ) {
			return '<div class="wrap"><p>Bạn không có quyền sửa bài này.</p></div>';
		}
		$terms = get_the_terms( $id, KhaiBao::THE_LOAI );
		$loai = is_array( $terms ) && $terms ? $terms[0]->slug : '';
		$mo = Viec::mo( 'post', $id );
		$ket_qua = sanitize_key( (string) ( $_GET['ket_qua'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc thông báo
		$bao = array( 'da_luu_sang' => 'Đã lưu bài vừa rồi. Đây là bài tiếp theo trong danh sách.', 'da_luu' => 'Đã lưu phần bài.', 'da_luu_pl' => 'Đã lưu phân loại.', 'da_khoi_phuc' => 'Đã khôi phục bản trước; lần khôi phục được ghi vào Lịch sử.', 'nhap' => 'Đã lưu nháp vào Hộp việc; người đọc chưa thấy.', 'khong_doi' => 'Không có gì thay đổi.', 'dang' => 'Đã đăng: người đọc thấy bài.', 'an' => 'Đã ẩn bài khỏi người đọc; lấy lại bằng nút Đăng.', 've_nhap' => 'Đã chuyển về bản nháp.', 'loi' => 'Chưa lưu được. Kiểm lại dữ liệu và thử tiếp.', 'them' => 'Đã thêm bài (bản nháp). Viết tiếp các phần rồi bấm Đăng khi xong.', 'them_dang' => 'Đã thêm và đăng bài. Viết tiếp các phần nếu muốn.' )[ $ket_qua ] ?? '';
		if ( 'loi' === $ket_qua ) {
			$khoa_loi = 'phongdao_sua_loi_' . $id . '_' . get_current_user_id();
			$bao = (string) get_transient( $khoa_loi ) ?: $bao;
			delete_transient( $khoa_loi );
		}
		$sau = self::bai_sau( $id );
		$ra  = '<div class="wrap phongdao-admin-sua"><h1>Sửa tác phẩm #' . $id . ': ' . esc_html( $bai->post_title ) . '</h1>';
		$ra .= '<p class="phongdao-sua-lien"><a href="' . esc_url( '' !== self::$ve ? self::$ve : admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) ) . '">← Danh sách Tác phẩm' . ( '' !== self::$ve ? ' (đúng bộ lọc vừa xem)' : '' ) . '</a> · <a href="' . esc_url( get_preview_post_link( $bai ) ) . '">Xem trên trang đọc</a>'
			. ( $sau ? ' · <a href="' . esc_url( self::url( $sau ) ) . '" data-bai-sau>Bài tiếp theo trong danh sách →</a>' : '' ) . '</p>';
		if ( $bao ) {
			$ra .= '<div class="notice ' . ( 'loi' === $ket_qua ? 'notice-error' : 'notice-success' ) . '"><p role="status">' . esc_html( $bao ) . ( $sau && 'loi' !== $ket_qua ? ' <a href="' . esc_url( self::url( $sau ) ) . '">Sang bài tiếp theo →</a>' : '' ) . '</p></div>';
		}
		// Cột chính: lời luôn hiện, rồi các phần — phần đã có mở sẵn để thấy nội dung, phần "Chưa có" gập lại
		$chinh = self::loi_xem( $ctx['phan']['loi'] ?? null ) . '<h2>Các phần của bài</h2>';
		$an_sau = $sau ? '<input type="hidden" name="bai_sau" value="' . $sau . '">' : '';
		foreach ( self::phan_theo_loai( $loai ) as $phan ) {
			$ten = TamSua::TAM[ $phan ][0];
			$cu = $ctx['phan'][ $phan ] ?? null;
			$mo_phan = $phan === ( $_GET['phan'] ?? '' ) || ( null !== $cu && 'loi' !== $phan ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở lại phần vừa lưu
			$ten_goc = str_starts_with( $ten, 'Sửa ' ) ? mb_substr( $ten, 4 ) : $ten;
			// Phần chưa có: "Thêm ý nghĩa" (không phải "Sửa ý nghĩa · Chưa có")
			$chinh .= '<details class="phongdao-sua-phan' . ( null === $cu ? ' chua-co' : '' ) . '" id="phan-' . esc_attr( $phan ) . '"' . ( $mo_phan ? ' open' : '' ) . '><summary>' . esc_html( null === $cu ? 'Thêm ' . $ten_goc : $ten ) . '</summary>';
			$ten_goc = mb_strtoupper( mb_substr( $ten_goc, 0, 1 ) ) . mb_substr( $ten_goc, 1 );
			$chinh .= CheDoSua::goc( $ten_goc, $cu, $ctx );
			$chinh .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_sua_phan">' . self::an_ve() . $an_sau . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="phan" value="' . esc_attr( $phan ) . '">';
			$chinh .= wp_nonce_field( 'phongdao_sua_phan_' . $id . '_' . $phan, '_wpnonce', true, false );
			$chinh .= TamSua::than( $phan, $cu, $ctx ) . '<p class="phongdao-sua-nut">' . TamSua::nut_luu( $phan, $ctx, 'button button-primary', 'button', 'Lưu phần này' ) . '</p></form></details>';
		}
		// Cột bên: trạng thái, nguồn, việc chờ, phân loại, lịch sử
		$ben = self::thanh_trang_thai( $bai )
			. ( $sau ? '<p class="phongdao-sang" data-chi-js hidden><label><input type="checkbox" id="phongdao-sang-tiep"> Lưu xong tự sang bài tiếp theo trong danh sách</label></p>' : '' );
		// Căn cứ trước khi quyết (T-179): trang gốc máy đã lấy — chỉ hiện trong admin, không bao giờ ra trang người đọc
		$may = $ctx['phan']['nguon_may'] ?? null;
		if ( ! empty( $may['url_goc'] ) ) {
			$ben .= '<p class="goc-nguon">Bài này máy lấy từ nguồn "' . esc_html( \Phongdao\DichVu\NhapNguon\NguonTuDong::ten( (string) ( $may['nguon_id'] ?? '' ) ) ) . '"' . ( empty( $may['ngay_lay'] ) ? '' : ' ngày ' . esc_html( (string) $may['ngay_lay'] ) ) . ': <a href="' . esc_url( (string) $may['url_goc'] ) . '" rel="noreferrer nofollow">xem trang gốc</a> (chỉ người biên soạn thấy).</p>';
		}
		$ben .= '<section class="phongdao-sua-viec"><h2>Việc đang chờ (' . count( $mo ) . ')</h2>';
		if ( ! $mo ) {
			$ben .= '<p>Không có việc đang chờ cho bài này.</p>';
		} else {
			$ben .= '<ul>';
			foreach ( $mo as $viec ) {
				$ten_viec = QuyetViec::LOAI[ $viec['loai'] ][0] ?? $viec['loai'];
				$ten_viec_tt = array( Viec::CHO => 'Chờ', Viec::DE_SAU => 'Để sau' )[ $viec['trang_thai'] ] ?? $viec['trang_thai'];
				$ben .= '<li><a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => $viec['id'] ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $ten_viec . ' #' . $viec['id'] ) . '</a> · ' . esc_html( $ten_viec_tt ) . '</li>';
			}
			$ben .= '</ul>';
		}
		$ben .= '</section>' . self::phan_loai( $bai, $loai ) . self::lich_su( $id );
		return $ra . '<div class="phongdao-sua-bo"><div class="phongdao-sua-chinh">' . $chinh . '</div><aside class="phongdao-sua-ben" aria-label="Trạng thái, phân loại và lịch sử">' . $ben . '</aside></div></div>';
	}

	/**
	 * Bài kế tiếp để sửa: bài mang theo sau lần lưu trước (`bai_sau`), hoặc bài đứng sau bài này trong danh sách Tác phẩm đã
	 * lọc (lối "ve") — vd. Thiếu ý nghĩa: viết xong một bài, sang bài kế. 0 khi không có.
	 */
	private static function bai_sau( int $id ): int {
		$sau = absint( $_GET['bai_sau'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ là lối sang bài kế, kiểm quyền dưới
		if ( $sau && $sau !== $id && self::sua_duoc( $sau ) ) {
			return $sau;
		}
		if ( '' === self::$ve ) {
			return 0;
		}
		$q = array();
		wp_parse_str( (string) wp_parse_url( self::$ve, PHP_URL_QUERY ), $q );
		$ds = DanhSachTacPham::ids_loc( $q, 1000 );
		$i  = array_search( $id, $ds, true );
		$sau = false !== $i ? (int) ( $ds[ $i + 1 ] ?? 0 ) : 0;
		return $sau && self::sua_duoc( $sau ) ? $sau : 0;
	}

	private static function sua_duoc( int $id ): bool {
		$p = get_post( $id );
		return $p && KhaiBao::TAC_PHAM === $p->post_type && 'trash' !== $p->post_status && current_user_can( 'edit_post', $id );
	}

	/**
	 * Trạng thái bài và nút đổi ngay trên trang sửa (T-187): Đăng (cần quyền duyệt), Ẩn khỏi người đọc, Về nháp. Đi qua
	 * đúng dịch vụ của dải "Đang sửa" trên trang đọc: kiểm trạng thái lúc mở trang, ghi nhật ký trong một giao dịch.
	 */
	private static function thanh_trang_thai( \WP_Post $bai ): string {
		$tt  = $bai->post_status;
		$nut = array();
		if ( 'publish' !== $tt && current_user_can( 'cadao_duyet' ) ) {
			$nut['publish'] = array( 'Đăng', 'button button-primary' );
		}
		if ( 'private' !== $tt ) {
			$nut['private'] = array( 'Ẩn khỏi người đọc', 'button' );
		}
		if ( 'draft' !== $tt ) {
			$nut['draft'] = array( 'Về bản nháp', 'button' );
		}
		$o = '';
		foreach ( $nut as $moi => [ $nhan, $lop ] ) {
			$o .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_doi_trang_thai">' . self::an_ve()
				. '<input type="hidden" name="id" value="' . (int) $bai->ID . '"><input type="hidden" name="trang_thai" value="' . esc_attr( $moi ) . '"><input type="hidden" name="trang_thai_cu" value="' . esc_attr( $tt ) . '">'
				. wp_nonce_field( 'phongdao_doi_trang_thai_' . $bai->ID, '_wpnonce', false, false ) . '<button type="submit" class="' . esc_attr( $lop ) . '">' . esc_html( $nhan ) . '</button></form>';
		}
		return '<div class="phongdao-trang-thai"><p>Trạng thái: <strong>' . esc_html( self::TEN_TRANG_THAI[ $tt ] ?? $tt ) . '</strong></p>' . $o . '</div>';
	}

	/** Lời đang lưu, luôn hiện ở đầu trang sửa để viết Ý nghĩa, Từ khó… mà không phải mở trang đọc (T-187). */
	private static function loi_xem( ?array $loi ): string {
		$dong = array_values( (array) ( $loi['dong'] ?? array() ) );
		if ( ! $dong ) {
			return '<section class="phongdao-loi-xem"><h2>Lời</h2><p>Bài chưa có lời. Mở "Sửa lời" bên dưới để thêm.</p></section>';
		}
		$o = '';
		$so = 0;
		foreach ( $dong as $d ) {
			$o .= '' === trim( (string) $d ) ? '<li class="ngat" aria-hidden="true"></li>' : '<li><span class="so" aria-hidden="true">' . sprintf( '%02d', ++$so ) . '</span>' . esc_html( (string) $d ) . '</li>';
		}
		return '<section class="phongdao-loi-xem"><h2>Lời <span class="description">(' . $so . ' dòng)</span></h2><ol tabindex="0" aria-label="Lời của bài">' . $o . '</ol></section>';
	}

	/** Nút Đăng / Ẩn / Về nháp: kiểm quyền trên bài và nonce, ghi qua dịch vụ đổi trạng thái của trang đọc. */
	public static function doi_trang_thai(): void {
		$id  = absint( $_POST['id'] ?? 0 );
		$bai = $id ? get_post( $id ) : null;
		if ( ! current_user_can( 'cadao_soan' ) || ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'trash' === $bai->post_status || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Bạn không có quyền đổi trạng thái bài này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_doi_trang_thai_' . $id );
		$yc = new \WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $id . '/trang-thai' );
		$yc->set_param( 'id', $id );
		$yc->set_param( 'trang_thai', sanitize_key( (string) ( $_POST['trang_thai'] ?? '' ) ) );
		$yc->set_param( 'trang_thai_cu', sanitize_key( (string) ( $_POST['trang_thai_cu'] ?? '' ) ) );
		$kq = BienTap::trang_thai( $yc );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ), MINUTE_IN_SECONDS );
		}
		$moi     = get_post_status( $id );
		$ket_qua = ! $kq['ok'] ? 'loi' : ( 'Không có gì thay đổi.' === ( $kq['thong_bao'] ?? '' ) ? 'khong_doi' : ( array( 'publish' => 'dang', 'private' => 'an', 'draft' => 've_nhap' )[ $moi ] ?? 'da_luu' ) );
		// Lối "Ẩn nếu nhầm" sau khi Đăng ở Hộp việc, nút Ẩn trên hàng ở Tác phẩm: xong thì về lại đúng màn đó, lời báo ở đó
		$quay_lai = esc_url_raw( wp_unslash( (string) ( $_POST['quay_lai'] ?? '' ) ) );
		if ( '' !== $quay_lai && ( str_starts_with( $quay_lai, admin_url( 'admin.php?page=' . DanhSachViec::TRANG ) ) || str_starts_with( $quay_lai, admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) ) || str_starts_with( $quay_lai, admin_url( 'admin.php?page=' . TrangLo::TRANG ) ) ) ) {
			if ( str_starts_with( $quay_lai, admin_url( 'admin.php?page=' . TrangLo::TRANG ) ) ) {
				// Màn kết quả lô đọc lời báo một lần (cùng chỗ với các lời báo khác của lô)
				set_transient( 'phongdao_lo_bao_' . get_current_user_id(), array( 'ok' => 'an' === $ket_qua, 'chu' => 'an' === $ket_qua ? 'Đã ẩn “' . get_post_field( 'post_title', $id ) . '” khỏi người đọc; lấy lại bằng nút Đăng ở trang sửa bài.' : implode( ' ', $kq['loi'] ?? array( 'Bài không đổi trạng thái.' ) ), 'lo' => 0 ), MINUTE_IN_SECONDS );
				wp_safe_redirect( $quay_lai );
				exit;
			}
			$chu = 'an' === $ket_qua ? 'Đã ẩn “' . get_post_field( 'post_title', $id ) . '” khỏi người đọc; lấy lại bằng nút Đăng ở trang sửa bài.' : ( 'loi' === $ket_qua ? 'Chưa ẩn được: ' . implode( ' ', $kq['loi'] ) : 'Bài không đổi trạng thái.' );
			wp_safe_redirect( add_query_arg( 'da', rawurlencode( $chu ), remove_query_arg( array( 'da', 'dang_bai', 'vua', 'hoan_viec', 'hoan_phien' ), $quay_lai ) ) );
			exit;
		}
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( 'ket_qua', $ket_qua, self::url( $id ) ) );
		exit;
	}

	/** Khối Phân loại: thể loại, mục từ chỉ xem; các bộ còn lại chọn trong mục có sẵn. */
	private static function phan_loai( \WP_Post $bai, string $loai ): string {
		$id   = $bai->ID;
		$ten  = static fn( array $ds ): string => implode( ', ', array_map( static fn( \WP_Term $t ): string => $t->name, $ds ) );
		$tl   = get_the_terms( $id, KhaiBao::THE_LOAI );
		$mt   = get_the_terms( $id, 'muc_tu' );
		$ra   = '<h2 id="phan-loai">Phân loại</h2>';
		$ra  .= '<p>Thể loại: <strong>' . esc_html( is_array( $tl ) && $tl ? $ten( $tl ) : 'Chưa có' ) . '</strong> · không đổi ở đây vì sẽ đổi đường dẫn của bài.'
			. ( is_array( $mt ) && $mt ? '<br>Mục từ trong Từ điển: ' . esc_html( $ten( $mt ) ) . ' · gắn theo phần Từ khó.' : '' ) . '</p>';
		$mo   = sanitize_key( (string) ( $_GET['phan_loai'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở lại khối vừa lưu
		foreach ( array_keys( LuuPhanLoai::SUA_DUOC ) as $tax ) {
			if ( ! LuuPhanLoai::sua_duoc( $tax, $loai ) || ! taxonomy_exists( $tax ) ) {
				continue;
			}
			$nhan   = KhaiBao::PHAN_LOAI[ $tax ][1];
			$gan    = wp_get_object_terms( $id, $tax );
			$gan    = is_array( $gan ) ? $gan : array();
			$may    = array_values( array_filter( $gan, static fn( \WP_Term $t ): bool => LuuPhanLoai::may_giu( $tax, $t ) ) );
			$nguoi  = array_values( array_filter( $gan, static fn( \WP_Term $t ): bool => ! LuuPhanLoai::may_giu( $tax, $t ) ) );
			$dang   = array_map( static fn( \WP_Term $t ): int => (int) $t->term_id, $nguoi );
			$chon   = LuuPhanLoai::lua_chon( $tax );
			$o_id   = 'pl-' . $tax;
			$ra    .= '<details class="phongdao-sua-phan" id="phan-loai-' . esc_attr( $tax ) . '"' . ( $mo === $tax ? ' open' : '' ) . '><summary>' . esc_html( $nhan . ' · ' . ( $gan ? implode( ', ', array_filter( array( $ten( $may ) . ( $may ? ' (máy)' : '' ), $ten( $nguoi ) ) ) ) : 'Chưa có' ) ) . '</summary>';
			if ( $may ) {
				$ra .= '<p class="goc-nguon">Thể thơ máy tự tính từ lời: ' . esc_html( $ten( $may ) ) . '. Sửa lời thì máy tính lại.</p>';
			}
			if ( ! $chon ) {
				$ra .= '<p>Chưa có mục nào để chọn trong bộ ' . esc_html( $nhan ) . '. Màn này chỉ chọn mục có sẵn, không tạo mục mới.</p></details>';
				continue;
			}
			$ra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_sua_phan_loai">' . self::an_ve() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="tax" value="' . esc_attr( $tax ) . '">';
			$ra .= wp_nonce_field( 'phongdao_sua_phan_loai_' . $id . '_' . $tax, '_wpnonce', true, false );
			foreach ( $dang as $tid ) {
				$ra .= '<input type="hidden" name="da_thay[]" value="' . $tid . '">';
			}
			$ra .= '<fieldset><legend class="screen-reader-text">Chọn ' . esc_html( mb_strtolower( $nhan ) ) . ' của bài</legend>';
			if ( count( $chon ) > 12 ) {
				$ra .= '<p class="o-nhap phongdao-loc-muc" data-chi-js hidden><label for="' . $o_id . '-loc">Lọc danh sách</label><input type="search" id="' . $o_id . '-loc" data-loc-muc="' . $o_id . '-ds" autocomplete="off"></p>';
			}
			$ra .= '<div class="phongdao-chon-muc" id="' . $o_id . '-ds">';
			foreach ( $chon as $t ) {
				$tid = (int) $t->term_id;
				$ra .= '<label class="choice" for="' . $o_id . '-' . $tid . '"><input type="checkbox" id="' . $o_id . '-' . $tid . '" name="chon[]" value="' . $tid . '"' . checked( in_array( $tid, $dang, true ), true, false ) . '> ' . esc_html( $t->name ) . '</label>';
			}
			$ra .= '</div></fieldset><p class="description">Đánh dấu để gắn, bỏ dấu để gỡ. Chỉ chọn mục có sẵn; màn này không tạo mục mới.</p>';
			$ra .= '<p><button type="submit" class="button button-primary">Lưu ' . esc_html( mb_strtolower( $nhan ) ) . '</button></p></form></details>';
		}
		return $ra;
	}

	/**
	 * Khối Lịch sử (T-170): 50 lần sửa, quyết mới nhất của bài — kể cả đề xuất đã bỏ. Mỗi lần sửa xem được trước/sau từng
	 * dòng; lần sửa mới nhất của một phần (khi chưa có thay đổi mới hơn) có nút "Khôi phục bản trước".
	 */
	private static function lich_su( int $id ): string {
		$ds       = Viec::lich_su( 'post', $id, 50, array( Viec::XONG, Viec::BO ) );
		$moi_nhat = array();
		foreach ( $ds as $r ) {
			if ( Viec::SUA === $r['loai'] && ! isset( $moi_nhat[ $r['phan'] ] ) ) {
				$moi_nhat[ $r['phan'] ] = (int) $r['id']; // danh sách mới nhất trước: lần gặp đầu là lần sửa mới nhất
			}
		}
		$o    = '';
		$them = '';
		$so   = 0;
		foreach ( $ds as $r ) {
			if ( 5 === $so++ ) {
				$them = $o;
				$o    = '';
			}
			$t     = strtotime( (string) $r['quyet_luc'] . ' UTC' );
			$luc   = $t ? wp_date( 'd/m/Y H:i', $t ) : (string) $r['quyet_luc'];
			$o    .= '<li><strong>' . esc_html( $luc ) . '</strong> · ' . esc_html( CheDoSua::mo_ta( $r ) );
			if ( Viec::SUA === $r['loai'] ) {
				$o .= self::so_sanh( $r );
				$ly_do = LichSu::ly_do_khong( $r, $moi_nhat[ $r['phan'] ] ?? 0 );
				if ( '' === $ly_do ) {
					$o .= '<form method="post" class="phongdao-khoi-phuc" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_khoi_phuc">' . self::an_ve() . '<input type="hidden" name="viec" value="' . (int) $r['id'] . '">'
						. wp_nonce_field( 'phongdao_khoi_phuc_' . (int) $r['id'], '_wpnonce', false, false )
						. '<button type="submit" class="button" aria-label="' . esc_attr( 'Khôi phục bản trước: ' . CheDoSua::mo_ta( $r ) . ', ' . $luc ) . '">Khôi phục bản trước</button></form>';
				}
			}
			$o .= '</li>';
		}
		// 5 lần gần nhất hiện sẵn; các lần cũ hơn trong khối gập
		[ $dau, $cu ] = '' === $them ? array( $o, '' ) : array( $them, $o );
		return '<section class="phongdao-sua-viec" id="lich-su"><h2>Lịch sử</h2>'
			. ( '' === $dau ? '<p>Chưa có lần sửa nào được ghi.</p>' : '<p class="description">Khôi phục chỉ có ở lần sửa mới nhất của mỗi phần, khi chưa ai sửa tiếp; khôi phục cũng được ghi thành một lần sửa mới.</p><ol class="phongdao-lich-su">' . $dau . '</ol>' )
			. ( '' === $cu ? '' : '<details class="phongdao-lich-cu"><summary>Xem ' . ( count( $ds ) - 5 ) . ' lần sửa cũ hơn</summary><ol class="phongdao-lich-su" start="6">' . $cu . '</ol></details>' )
			. '</section>';
	}

	/** Trước/sau của một lần sửa, so từng dòng: "−" dòng bỏ, "+" dòng thêm (không chỉ dựa vào màu). */
	private static function so_sanh( array $r ): string {
		$a = SoSanh::dong( $r['truoc'] );
		$b = SoSanh::dong( $r['sau'] );
		if ( ! $a && ! $b ) {
			return '';
		}
		$o = '';
		foreach ( SoSanh::khac( $a, $b ) as [ $dau, $dong ] ) {
			$chu = esc_html( '' === $dong ? ' ' : $dong );
			$o  .= match ( $dau ) {
				'-'     => '<li class="bo"><span aria-hidden="true">− </span><del><span class="screen-reader-text">bỏ: </span>' . $chu . '</del></li>',
				'+'     => '<li class="them"><span aria-hidden="true">+ </span><ins><span class="screen-reader-text">thêm: </span>' . $chu . '</ins></li>',
				default => '<li class="giu"><span aria-hidden="true">  </span>' . $chu . '</li>',
			};
		}
		return '<details class="phongdao-so-sanh"><summary>Xem trước/sau</summary><ul>' . $o . '</ul></details>';
	}

	/** Nút "Khôi phục bản trước": kiểm quyền trên bài của lần sửa và nonce từng dòng; ghi qua LichSu. */
	public static function khoi_phuc(): void {
		$vid = absint( $_POST['viec'] ?? 0 );
		$v   = $vid ? Viec::lay( $vid ) : null;
		$id  = $v && 'post' === $v['doi_tuong_loai'] ? (int) $v['doi_tuong_id'] : 0;
		$bai = $id ? get_post( $id ) : null;
		if ( ! current_user_can( 'cadao_soan' ) || ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Bạn không có quyền khôi phục lần sửa này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_khoi_phuc_' . $vid );
		$kq = LichSu::khoi_phuc( $vid, get_current_user_id(), current_user_can( 'cadao_duyet' ) );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ), MINUTE_IN_SECONDS );
		}
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( 'ket_qua', $kq['ok'] ? ( $kq['doi'] ? 'da_khoi_phuc' : 'khong_doi' ) : 'loi', self::url( $id ) ) . '#lich-su' );
		exit;
	}

	/** Lưu phân loại từ biểu mẫu: kiểm quyền từng bài và nonce, ghi qua LuuPhanLoai. */
	public static function luu_phan_loai(): void {
		$id  = absint( $_POST['id'] ?? 0 );
		$tax = sanitize_key( (string) ( $_POST['tax'] ?? '' ) );
		$bai = $id ? get_post( $id ) : null;
		if ( ! current_user_can( 'cadao_soan' ) || ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'trash' === $bai->post_status || ! current_user_can( 'edit_post', $id ) || ! array_key_exists( $tax, LuuPhanLoai::SUA_DUOC ) ) {
			wp_die( 'Bạn không có quyền sửa phân loại này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_sua_phan_loai_' . $id . '_' . $tax );
		$chon    = array_map( 'absint', (array) wp_unslash( $_POST['chon'] ?? array() ) );
		$da_thay = array_map( 'absint', (array) wp_unslash( $_POST['da_thay'] ?? array() ) );
		$kq      = LuuPhanLoai::luu( $id, $tax, $chon, $da_thay, get_current_user_id() );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ), MINUTE_IN_SECONDS );
		}
		$ket_qua = ! $kq['ok'] ? 'loi' : ( $kq['doi'] ? 'da_luu_pl' : 'khong_doi' );
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( array( 'ket_qua' => $ket_qua, 'phan_loai' => $tax ), self::url( $id ) ) . '#phan-loai-' . $tax );
		exit;
	}

	public static function luu(): void {
		$id = absint( $_POST['id'] ?? 0 );
		$phan = sanitize_key( (string) ( $_POST['phan'] ?? '' ) );
		$bai = $id ? get_post( $id ) : null;
		if ( ! current_user_can( 'cadao_soan' ) || ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'trash' === $bai->post_status || ! current_user_can( 'edit_post', $id ) || ! isset( TamSua::TAM[ $phan ] ) ) {
			wp_die( 'Bạn không có quyền sửa phần này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_sua_phan_' . $id . '_' . $phan );
		$yc = new \WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $id . '/phan/' . $phan );
		$yc->set_param( 'id', $id );
		$yc->set_param( 'phan', $phan );
		$yc->set_body_params( wp_unslash( $_POST ) );
		$kq = BienTap::luu_phan( $yc );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ?? array() ), MINUTE_IN_SECONDS );
		}
		$ket_qua = ! $kq['ok'] ? 'loi' : ( ! empty( $kq['thong_bao'] ) && str_contains( $kq['thong_bao'], 'nháp' ) ? 'nhap' : ( ! empty( $kq['doi'] ) ? 'da_luu' : 'khong_doi' ) );
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		// Đến từ danh sách đã lọc: lưu được và người chọn "tự sang bài tiếp" thì mở bài kế; không thì giữ lối sang bài kế
		$sau = absint( $_POST['bai_sau'] ?? 0 );
		$sau = $sau && $sau !== $id && self::sua_duoc( $sau ) ? $sau : 0;
		if ( $kq['ok'] && $sau && ! empty( $_POST['sang'] ) ) {
			wp_safe_redirect( add_query_arg( 'ket_qua', 'da_luu_sang', self::url( $sau ) ) );
			exit;
		}
		wp_safe_redirect( add_query_arg( array_filter( array( 'ket_qua' => $ket_qua, 'phan' => $phan, 'bai_sau' => $sau ) ), self::url( $id ) ) . '#phan-' . $phan );
		exit;
	}
}
