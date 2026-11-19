<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\LuuPhan\LichSu;
use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\DichVu\LuuPhan\LuuTheTho;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\GiaoTiep\BienTap\CheDoSua;
use Phongdao\GiaoTiep\BienTap\Module as BienTap;
use Phongdao\GiaoTiep\BienTap\TamSua;
use Phongdao\GiaoTiep\BienTap\NhapDo;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\SoSanh;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Sửa tác phẩm** (làm lại D-121, docs/42 mục 4): sửa từng phần bằng đúng dịch vụ của tấm sửa trên trang đọc. Cột
 * chính: Lời (chỉ đọc, "Sửa lời", Đối chiếu sách) → Nghĩa (Ý nghĩa mở sẵn) → Phân loại (thẻ chip; chủ đề, vùng máy gợi ý
 * là chip nét đứt, bấm mới gắn) → Phần khác. Cột bên: khung **Đăng** luôn thấy (trạng thái, một nút "Lưu thay đổi" cho mọi
 * phần đã sửa — Lịch sử vẫn ghi từng phần; Đăng / Ẩn / Về nháp), "Bài này còn thiếu", công tắc, căn cứ, đường dẫn, lịch
 * sử. Không có JavaScript thì mỗi phần vẫn có nút lưu riêng. Đến từ danh sách đã lọc có lối "Bài tiếp theo"; chỉ tự sang
 * khi đã lưu hết.
 */
final class SuaTacPham {
	public const TRANG = 'phongdao-sua-tac-pham';

	public static function dang_ky(): void {
		SuaDuongDan::dang_ky();
		add_action( 'admin_post_phongdao_sua_phan', array( self::class, 'luu' ) );
		add_action( 'admin_post_phongdao_sua_the_tho', array( self::class, 'luu_the_tho' ) );
		add_action( 'admin_post_phongdao_sua_phan_loai', array( self::class, 'luu_phan_loai' ) );
		add_action( 'admin_post_phongdao_khoi_phuc', array( self::class, 'khoi_phuc' ) );
		add_action( 'admin_post_phongdao_doi_trang_thai', array( self::class, 'doi_trang_thai' ) );
		add_action( 'admin_post_phongdao_doi_the_loai', array( self::class, 'doi_the_loai' ) );
		add_action( 'admin_post_phongdao_doi_chieu_sach', array( self::class, 'doi_chieu_sach' ) );
		add_action( 'admin_post_phongdao_gop_nghia_bong', array( self::class, 'gop_nghia_bong' ) );
	}

	/** Hành động admin-post mà nút "Lưu thay đổi" gửi chung (mỗi phần một lượt lưu, có Lịch sử riêng). */
	public const LUU_CHUNG = array( 'phongdao_sua_phan', 'phongdao_sua_phan_loai', 'phongdao_sua_the_tho' );

	/** Gửi từ nút "Lưu thay đổi": trả JSON thay vì chuyển trang. */
	private static function tra_json(): bool {
		return ! empty( $_POST['tra_json'] ); // phpcs:ignore WordPress.Security.NonceVerification -- nonce đã kiểm ở hàm gọi
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
		$rieng = array( 'nghia_den' => 'tuc-ngu', 'nghia_bong' => 'tuc-ngu', 'dung_khi' => 'tuc-ngu', 'gan_nghia' => 'tuc-ngu', 'hinh_anh' => 'ca-dao|dong-dao', 'cau_hoi' => 'cau-do', 'dap_an' => 'cau-do', 'goi_y' => 'cau-do', 'boi_canh' => 've', 'doan' => 've' );
		foreach ( array_keys( TamSua::TAM ) as $phan ) {
			if ( 'dong_gop' === $phan && ( ! current_user_can( 'cadao_duyet' ) || ! PhanBai::lay( absint( $_GET['id'] ?? 0 ), 'dong_gop' ) ) ) { continue; } // phpcs:ignore WordPress.Security.NonceVerification -- chỉ vẽ phần đã có
			if ( ! isset( $rieng[ $phan ] ) || in_array( $loai, explode( '|', $rieng[ $phan ] ), true ) ) {
				$ra[] = $phan;
			}
		}
		$thu_tu = array(
			'ca-dao' => array( 'loi', 'y_nghia', 'chu_thich', 'chuyen_quanh', 'di_ban', 'hinh_anh' ),
			'tuc-ngu' => array( 'loi', 'y_nghia', 'nghia_den', 'nghia_bong', 'dung_khi', 'chu_thich', 'chuyen_quanh', 'gan_nghia', 'di_ban' ),
			'dong-dao' => array( 'loi', 'y_nghia', 'chu_thich', 'chuyen_quanh', 'hinh_anh', 'di_ban' ),
			've' => array( 'loi', 'boi_canh', 'doan', 'y_nghia', 'chu_thich', 'chuyen_quanh', 'di_ban' ),
			'cau-do' => array( 'loi', 'cau_hoi', 'dap_an', 'goi_y', 'y_nghia', 'chu_thich', 'chuyen_quanh', 'di_ban' ),
		);
		return array_values( array_unique( array_merge( $thu_tu[ $loai ] ?? array(), $ra ) ) );
	}

	public static function luu_the_tho(): void {
		$id = absint( $_POST['id'] ?? 0 );
		check_admin_referer( 'phongdao_sua_the_tho_' . $id );
		$kq = LuuTheTho::luu( $id, sanitize_key( (string) ( $_POST['che_do'] ?? '' ) ), sanitize_key( (string) ( $_POST['ma'] ?? '' ) ), (string) ( $_POST['phien_the_tho'] ?? '' ), get_current_user_id() );
		if ( self::tra_json() ) {
			wp_send_json( array( 'ok' => $kq['ok'], 'phan' => 'the_tho', 'ket_qua' => $kq['ok'] ? 'da_luu' : 'loi', 'loi' => $kq['ok'] ? '' : implode( ' ', $kq['loi'] ) ) );
		}
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ), MINUTE_IN_SECONDS );
		}
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( array( 'ket_qua' => $kq['ok'] ? 'da_luu_pl' : 'loi', 'phan_loai' => 'the_tho' ), self::url( $id ) ) . '#the-tho' );
		exit;
	}

	private static function the_tho( \WP_Post $bai ): string {
		$chon = LuuTheTho::lay( $bai->ID );
		$dong = \Phongdao\KhoLuu\PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array();
		$may = $dong ? \Phongdao\MienDuLieu\TheTho::DS[ \Phongdao\MienDuLieu\TheTho::nhan( $dong ) ] : 'Chưa có lời';
		$ten = 'may' === $chon['che_do'] ? $may . ' (máy nhận)' : ( '' === $chon['ma'] ? 'không gắn nhãn (người chọn)' : ( \Phongdao\MienDuLieu\TheTho::DS[ $chon['ma'] ] ?? $chon['ma'] ) . ' (người chọn)' );
		$ra = '<details class="phongdao-sua-phan pq-pl-phu" id="the-tho"' . ( 'the_tho' === ( $_GET['phan_loai'] ?? '' ) ? ' open' : '' ) . '><summary>Thể thơ: ' . esc_html( $ten ) . ( current_user_can( 'cadao_duyet' ) ? ' <span class="pq-doi">Đổi</span>' : '' ) . '<span data-phan-trang-thai></span></summary><p>Máy nhận diện từ lời: ' . esc_html( $may ) . '. Lựa chọn của người được giữ khi sửa lời về sau.</p>'; // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở lại khối vừa lưu
		if ( ! current_user_can( 'cadao_duyet' ) ) {
			return $ra . '<p>Người có quyền duyệt có thể đổi lựa chọn thể thơ.</p></details>';
		}
		$ra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-giu-nhap="' . $bai->ID . ':the_tho" data-ten-phan="Thể thơ" data-phien-server="' . esc_attr( LuuTheTho::phien( $bai->ID ) ) . '" data-phien-soan="' . esc_attr( NhapDo::phien() ) . '"><input type="hidden" name="action" value="phongdao_sua_the_tho"><input type="hidden" name="id" value="' . $bai->ID . '"><input type="hidden" name="phien_the_tho" value="' . esc_attr( LuuTheTho::phien( $bai->ID ) ) . '">' . self::an_ve() . wp_nonce_field( 'phongdao_sua_the_tho_' . $bai->ID, '_wpnonce', true, false );
		foreach ( array( 'may' => 'Dùng nhận diện máy', 'nguoi' => 'Theo lựa chọn của người biên soạn' ) as $ma => $nhan ) {
			$ra .= '<label class="choice"><input type="radio" name="che_do" value="' . $ma . '"' . checked( $ma, $chon['che_do'], false ) . '> ' . esc_html( $nhan ) . '</label>';
		}
		$ra .= '<p><label for="the-tho-ma">Thể thơ khi chọn theo người</label><select name="ma" id="the-tho-ma"><option value="">Không gắn nhãn thể thơ</option>';
		foreach ( \Phongdao\MienDuLieu\TheTho::DS as $ma => $nhan ) {
			$ra .= '<option value="' . esc_attr( $ma ) . '"' . selected( $ma, $chon['ma'], false ) . '>' . esc_html( $nhan ) . '</option>';
		}
		return $ra . '</select></p><p class="phongdao-sua-nut"><button type="submit" class="button button-primary">Lưu lựa chọn thể thơ</button></p></form></details>';
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
		$ctx['an_doi_chieu'] = true; // đối chiếu sách là nút riêng (khoi_doi_chieu), không nằm trong ô Sửa lời
		$ctx['sua_day_du'] = true;
		$terms = get_the_terms( $id, KhaiBao::THE_LOAI );
		$loai = is_array( $terms ) && $terms ? $terms[0]->slug : '';
		$ket_qua = sanitize_key( (string) ( $_GET['ket_qua'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc thông báo
		$bao = array( 'da_luu_sang' => 'Đã lưu bài trước. Đây là bài tiếp theo trong danh sách.', 'da_luu' => 'Đã lưu phần bài.', 'da_luu_pl' => 'Đã lưu phân loại.', 'da_khoi_phuc' => 'Đã khôi phục bản trước; lần khôi phục được ghi vào Lịch sử.', 'nhap' => 'Đã lưu nháp vào Hộp việc; người đọc chưa thấy.', 'khong_doi' => 'Không có gì thay đổi.', 'dang' => 'Đã đăng: người đọc thấy bài.', 'an' => 'Đã ẩn bài khỏi người đọc; lấy lại bằng nút Đăng.', 've_nhap' => 'Đã chuyển về bản nháp.', 'loi' => 'Chưa lưu được. Kiểm lại dữ liệu và thử tiếp.', 'them' => 'Đã thêm bài (bản nháp). Viết tiếp các phần rồi bấm Đăng khi xong.', 'them_dang' => 'Đã thêm và đăng bài. Viết tiếp các phần nếu muốn.', 'doi_the_loai' => 'Đã đổi thể loại. Đường dẫn cũ tự chuyển sang đường mới; Lịch sử ghi lại lần đổi.', 'doi_chieu' => 'Đã ghi đối chiếu sách; Lịch sử ghi lại.', 'gop_nghia' => 'Đã gộp Nghĩa bóng cũ vào Ý nghĩa; Lịch sử ghi cả hai phần.' )[ $ket_qua ] ?? '';
		if ( 'loi' === $ket_qua ) {
			$khoa_loi = 'phongdao_sua_loi_' . $id . '_' . get_current_user_id();
			$bao = (string) get_transient( $khoa_loi ) ?: $bao;
			delete_transient( $khoa_loi );
		}
		if ( in_array( $ket_qua, array( 'da_luu_nhieu', 'luu_chua_het' ), true ) ) {
			$bao = self::bao_luu_chung( $loai, $bai );
		}
		$sau = self::bai_sau( $id );
		$phan_loi = sanitize_key( (string) ( $_GET['phan'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc form đã được giữ theo phiên
		if ( 'duong_dan' === $phan_loi && 'da_luu' === $ket_qua ) { $bao = 'Đã lưu đường dẫn tác phẩm. Đường cũ chuyển hướng tới bài khi bài công khai.'; }
		if ( isset( TamSua::TAM[ $phan_loi ] ) && in_array( $ket_qua, array( 'da_luu', 'khong_doi', 'nhap' ), true ) ) {
			$bao .= ' Phần: ' . TamSua::nhan( $phan_loi, $loai ) . '.';
			if ( 'da_luu' === $ket_qua ) {
				$bao .= 'publish' === $bai->post_status ? ' Trang người đọc đã cập nhật.' : ' Bài chưa công khai; khách chưa thấy phần này.';
			}
		}
		if ( ! empty( $_GET['con_chua_luu'] ) ) { $bao .= ' Còn phần khác chưa lưu, vì vậy vẫn ở lại bài này.'; }
		$form_loi = 'loi' === $ket_qua ? NhapDo::lay( (string) ( $_GET['nhap_do'] ?? '' ), $id, $phan_loi ) : null;
		$ctx['form_loi'] = null !== $form_loi ? array( 'phan' => $phan_loi, 'vao' => $form_loi ) : null;
		$an_sau = $sau ? '<input type="hidden" name="bai_sau" value="' . $sau . '">' : '';
		// Tục ngữ nhập Ý nghĩa và Nghĩa đen; Nghĩa bóng, Dùng khi chỉ hiện ở bài cũ đã có (D-118, D-119). Cờ, thô tục là công
		// tắc ở cột bên, không nằm trong danh sách phần (D-104, D-105, D-111).
		$ds_phan = array_values( array_filter( self::phan_theo_loai( $loai ), static fn( string $p ): bool => ! in_array( $p, array( 'co_nghi', 'tho_tuc' ), true ) && ( ! in_array( $p, array( 'nghia_bong', 'dung_khi' ), true ) || null !== ( $ctx['phan'][ $p ] ?? null ) || $p === $phan_loi ) ) );
		$nghia   = array( 'tuc-ngu' => array( 'y_nghia', 'nghia_den', 'nghia_bong', 'dung_khi' ), 'cau-do' => array( 'cau_hoi', 'dap_an', 'goi_y', 'y_nghia' ), 've' => array( 'boi_canh', 'y_nghia' ) )[ $loai ] ?? array( 'y_nghia' );
		$khac    = array_values( array_diff( $ds_phan, $nghia, array( 'loi' ) ) );
		$khoi    = array();
		foreach ( $ds_phan as $phan ) {
			$khoi[ $phan ] = self::khoi_phan( $bai, $phan, $loai, $ctx, $form_loi, $phan_loi, $an_sau, in_array( $phan, $khac, true ) );
		}
		$lay   = static fn( array $ds ): string => implode( '', array_map( static fn( string $p ): string => $khoi[ $p ] ?? '', $ds ) );
		$chinh = self::loi_xem( $ctx['phan']['loi'] ?? null, self::nhac_html( 'loi', $ctx['phan']['loi'] ?? null, $ctx ) . $lay( array( 'loi' ) ) . self::khoi_doi_chieu( $bai, $ctx ) )
			. '<section class="pq-khoi phongdao-nghia-khoi" aria-labelledby="pq-nghia"><h2 class="phongdao-sua-nhom" id="pq-nghia">Nghĩa</h2><div class="phongdao-nghia">' . $lay( array_values( array_intersect( $ds_phan, $nghia ) ) ) . '</div></section>'
			. self::phan_loai( $bai, $loai )
			. ( $khac ? '<section class="pq-khoi phongdao-phan-khac" id="phan-khac" aria-labelledby="pq-phan-khac"><h2 id="pq-phan-khac">Phần khác <span class="pq-dem">' . count( $khac ) . '</span></h2><p class="pq-mo-ta">Phần ít dùng. Bấm tên phần để mở; phần chưa có thì thêm ở đó.</p>' . $lay( $khac ) . '</section>' : '' );
		$ben = self::hop_dang( $bai, $sau ) . self::con_thieu( $bai, $ctx, $loai )
			. '<section class="pq-khoi" id="cong-tac" aria-labelledby="pq-cong-tac"><h2 id="pq-cong-tac">Công tắc</h2>' . self::cong_tac_tho_tuc( $bai, $ctx ) . self::cong_tac_co( $bai, $ctx ) . '</section>'
			. self::can_cu( $id, $ctx ) . SuaDuongDan::hien( $bai, self::$ve ) . self::lich_su( $id );
		$ten_tl = \Phongdao\MienDuLieu\TheLoai::DS[ $loai ] ?? 'Chưa có thể loại';
		$tom    = class_exists( \Phongdao\DichVu\TomTat\TomTat::class ) ? \Phongdao\DichVu\TomTat\TomTat::lay( $id ) : null;
		$doc    = (int) ( $tom['doc_7'] ?? 0 );
		$ds_url = '' !== self::$ve ? self::$ve : admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG );
		return '<div class="wrap pq-man phongdao-admin-sua"><p class="pq-duong"><a href="' . esc_url( $ds_url ) . '">Tác phẩm' . ( '' !== self::$ve ? ' (đúng bộ lọc vừa xem)' : '' ) . '</a> › ' . esc_html( $ten_tl ) . '</p>'
			. '<div class="pq-dau"><div class="pq-dau-chu"><h1><span class="screen-reader-text">Sửa tác phẩm: </span>' . esc_html( '' === $bai->post_title ? '(chưa có tên)' : $bai->post_title ) . ' ' . self::the_trang_thai( $bai->post_status ) . '</h1>'
			. '<p>' . esc_html( $ten_tl . ' · bài #' . $id . ( $doc ? ' · ' . number_format_i18n( $doc ) . ' lượt đọc 7 ngày' : '' ) ) . '</p></div>'
			. '<div class="pq-dau-nut"><a class="button" href="' . esc_url( self::xem( $bai ) ) . '" target="_blank" rel="noopener">' . ( 'publish' === $bai->post_status ? 'Xem trên site' : 'Xem bản đã lưu' ) . ' ↗<span class="screen-reader-text"> (mở tab mới)</span></a></div></div>'
			. ( $bao ? '<div class="notice ' . ( in_array( $ket_qua, array( 'loi', 'luu_chua_het' ), true ) ? 'notice-error' : 'notice-success' ) . '"><p role="status">' . esc_html( $bao ) . ( $sau && ! in_array( $ket_qua, array( 'loi', 'luu_chua_het' ), true ) ? ' <a href="' . esc_url( self::url( $sau ) ) . '">Sang bài tiếp theo →</a>' : '' ) . '</p></div>' : '' )
			. '<div class="pq-hai-cot phongdao-sua-bo"><div class="phongdao-sua-chinh">' . $chinh . '</div><aside class="pq-cot-phai phongdao-sua-ben" aria-label="Đăng, còn thiếu, công tắc và lịch sử">' . $ben . '</aside></div>'
			. '<div class="pq-thanh-luu" data-thanh-luu hidden><span data-thanh-luu-chu></span><button type="button" class="button button-primary" data-luu-tat-ca>Lưu thay đổi</button></div></div>';
	}

	/** Trang người đọc của bài (đã đăng) hoặc bản xem trước (chưa đăng). */
	private static function xem( \WP_Post $bai ): string {
		return 'publish' === $bai->post_status ? (string) get_permalink( $bai ) : (string) get_preview_post_link( $bai );
	}

	/** Thẻ trạng thái màu (xanh lá đã đăng, vàng đã ẩn, xám nháp). */
	private static function the_trang_thai( string $tt ): string {
		$mau = array( 'publish' => ' luc', 'private' => ' vang', 'future' => ' xanh' )[ $tt ] ?? '';
		return '<span class="pq-the' . $mau . '">' . esc_html( self::TEN_TRANG_THAI[ $tt ] ?? $tt ) . '</span>';
	}

	/** Một phần của bài: <details> có form riêng (lưu riêng khi không có JavaScript; nút "Lưu thay đổi" gửi chung). */
	private static function khoi_phan( \WP_Post $bai, string $phan, string $loai, array $ctx, ?array $form_loi, string $phan_loi, string $an_sau, bool $o_phan_khac ): string {
		$id      = (int) $bai->ID;
		$ten     = TamSua::ten( $phan, $loai );
		$cu      = $ctx['phan'][ $phan ] ?? null;
		// Ý nghĩa là ô chính của màn: mở sẵn; phần khác mở khi vừa lưu
		$mo_phan = $phan === $phan_loi || 'y_nghia' === $phan;
		$ten_goc = str_starts_with( $ten, 'Sửa ' ) ? mb_substr( $ten, 4 ) : $ten;
		$hoa     = mb_strtoupper( mb_substr( $ten_goc, 0, 1 ) ) . mb_substr( $ten_goc, 1 );
		if ( $o_phan_khac ) {
			// Hàng của "Phần khác": tên phần và tình trạng (chưa có / N mục / đã có)
			$tt   = null === $cu ? 'chưa có' : ( isset( $cu['muc'] ) ? count( (array) $cu['muc'] ) . ' mục' : 'đã có' );
			$tom  = '<span class="pq-ten-phan">' . esc_html( $hoa ) . '</span><span class="pq-tt-phan' . ( null === $cu ? ' chua' : '' ) . '">' . esc_html( $tt ) . '</span>';
		} else {
			$tom = esc_html( null === $cu ? 'Thêm ' . $ten_goc : $ten );
		}
		$ra = '<details class="phongdao-sua-phan' . ( null === $cu ? ' chua-co' : '' ) . '" id="phan-' . esc_attr( $phan ) . '" data-ten-phan="' . esc_attr( TamSua::nhan( $phan, $loai ) ) . '"' . ( $mo_phan ? ' open' : '' ) . '><summary>' . $tom . '<span data-phan-trang-thai></span></summary>';
		$ra .= CheDoSua::goc( $hoa, $cu, $ctx ) . ( 'loi' === $phan ? '' : self::nhac_html( $phan, $cu, $ctx ) );
		if ( 'y_nghia' === $phan && 'tuc-ngu' === $loai ) {
			$ra .= self::moi_gop_nghia_bong( $bai, $ctx );
		}
		if ( 'chu_thich' === $phan && $cu ) {
			// Từ không có trong lời: nhắc kiểm chính tả (vẫn lưu được — chú thích chung)
			$thieu_tu = \Phongdao\MienDuLieu\TuKho::thieu( (array) ( $ctx['phan']['loi']['dong'] ?? array() ), array_column( (array) ( $cu['muc'] ?? array() ), 'tu' ) );
			$can_xem  = array();
			foreach ( array_values( $cu['muc'] ?? array() ) as $i => $m ) {
				if ( in_array( (string) ( $m['tu'] ?? '' ), $thieu_tu, true ) ) {
					$can_xem[] = '<a href="#hang-s-chu_thich-' . $i . '" data-mo-hang-muc>«' . esc_html( (string) $m['tu'] ) . '»</a>';
				}
			}
			$canh_bao = '<p>Không thấy từ ' . implode( ', ', $can_xem ) . ' trong lời, kiểm lại chính tả. Chọn từ để tới mục.</p>';
			if ( count( $can_xem ) > 6 ) { $canh_bao = '<details><summary>' . count( $can_xem ) . ' mục cần xem trong lời · Mở danh sách</summary>' . $canh_bao . '</details>'; }
			$ra .= $can_xem ? '<div class="notice notice-warning inline">' . $canh_bao . '</div>' : '';
		}
		$loi_o_day  = null !== $form_loi && $phan === $phan_loi;
		$phien_moi  = \Phongdao\DichVu\LuuPhan\LuuPhan::phien( $id, $phan, $cu );
		$phien_form = $loi_o_day ? (string) ( $form_loi['phien_phan'] ?? '' ) : $phien_moi;
		$ra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-giu-nhap="' . $id . ':' . esc_attr( $phan ) . '" data-ten-phan="' . esc_attr( TamSua::nhan( $phan, $loai ) ) . '" data-da-co="' . ( null !== $cu ? '1' : '0' ) . '" data-phien-soan="' . esc_attr( NhapDo::phien() ) . '"' . ( $loi_o_day ? ' data-giu-loi="1"' : '' ) . '><input type="hidden" name="action" value="phongdao_sua_phan">' . self::an_ve() . $an_sau . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="phan" value="' . esc_attr( $phan ) . '">';
		$ra .= wp_nonce_field( 'phongdao_sua_phan_' . $id . '_' . $phan, '_wpnonce', true, false );
		$ra .= '<input type="hidden" name="phien_phan" value="' . esc_attr( $phien_form ) . '">';
		$ra .= TamSua::than( $phan, $loi_o_day ? NhapDo::de_hien( $phan, $form_loi, $cu ) : $cu, $ctx );
		if ( 'hinh_anh' === $phan ) { $ra .= '<p class="description">Phần chữ về hình ảnh văn học trong lời. Tệp ảnh thêm ở phần Ảnh minh hoạ.</p>'; }
		if ( 'y_nghia' === $phan ) { $ra .= '<p class="description">"Lưu nháp vào Hộp việc" chỉ gửi riêng phần này chờ duyệt.</p>'; }
		if ( $loi_o_day && ! hash_equals( $phien_moi, $phien_form ) ) {
			$ra .= TamSua::doi_chieu_phien( $cu, $phien_moi );
		}
		return $ra . '<p class="phongdao-sua-nut">' . TamSua::nut_luu( $phan, $ctx, 'button button-primary', 'button', 'Lưu ' . mb_strtolower( TamSua::nhan( $phan, $loai ) ) ) . '</p></form></details>';
	}

	/**
	 * Khung **Đăng** ở đầu cột bên (dính khi cuộn ở màn rộng): trạng thái, lần sửa cuối, số phần chưa lưu, một nút "Lưu
	 * thay đổi" (JavaScript; gửi lần lượt mọi phần đã sửa, mỗi phần một lượt lưu có Lịch sử riêng), Đăng / Ẩn / Về nháp, lối
	 * xem bài và bài tiếp theo.
	 */
	private static function hop_dang( \WP_Post $bai, int $sau ): string {
		$luc = (int) strtotime( (string) $bai->post_modified_gmt . ' UTC' );
		$url = add_query_arg( array_filter( array( 'page' => self::TRANG, 'id' => (int) $bai->ID, 've' => '' === self::$ve ? '' : rawurlencode( self::$ve ), 'bai_sau' => $sau ) ), admin_url( 'admin.php' ) );
		return '<section class="pq-khoi pq-dang" id="dang" aria-labelledby="pq-dang-h" data-hop-dang data-bai="' . (int) $bai->ID . '" data-url-bai="' . esc_url( $url ) . '"' . ( $sau ? ' data-url-sau="' . esc_url( self::url( $sau ) ) . '"' : '' ) . '>'
			. '<h2 id="pq-dang-h">Đăng</h2><p class="pq-mo-ta">' . self::the_trang_thai( $bai->post_status ) . ( $luc ? ' · sửa lần cuối ' . esc_html( BanLamViec::luc_truoc( $luc, time() ) ) : '' ) . '</p>'
			. '<p class="pq-chua-luu" data-tong-chua-luu role="status" data-chi-js hidden></p>'
			. '<p class="pq-luu-nut" data-chi-js hidden><button type="button" class="button button-primary" data-luu-tat-ca disabled>Lưu thay đổi</button></p>'
			. '<div class="pq-luu-bao" data-luu-bao role="alert" hidden></div>'
			. '<noscript><p class="description">Mỗi phần có nút lưu riêng ở cuối phần.</p></noscript>'
			. self::thanh_trang_thai( $bai )
			. '<p class="pq-dang-lien"><a href="' . esc_url( self::xem( $bai ) ) . '" target="_blank" rel="noopener">' . ( 'publish' === $bai->post_status ? 'Xem bài' : 'Xem bản đã lưu' ) . ' ↗<span class="screen-reader-text"> (mở tab mới)</span></a>' . ( $sau ? ' <a href="' . esc_url( self::url( $sau ) ) . '" data-bai-sau>Bài tiếp theo →</a>' : '' ) . '</p>'
			. ( $sau ? '<p class="phongdao-sang" data-chi-js hidden><label><input type="checkbox" id="phongdao-sang-tiep"> Lưu xong tự sang bài tiếp theo</label></p>' : '' )
			. '</section>';
	}

	/**
	 * "Bài này còn thiếu": phần nên có theo thể loại — xong thì ✓, thiếu thì là lối mở đúng phần. Không phải chỉ tiêu; phần
	 * tuỳ chọn ghi rõ.
	 */
	private static function con_thieu( \WP_Post $bai, array $ctx, string $loai ): string {
		$p  = $ctx['phan'];
		$ds = array( array( 'Lời', ! empty( $p['loi']['dong'] ), 'phan-loi', false ) );
		if ( 'cau-do' === $loai ) {
			$ds[] = array( TamSua::nhan( 'dap_an', $loai ), null !== ( $p['dap_an'] ?? null ), 'phan-dap_an', false );
		}
		$ds[] = array( TamSua::nhan( 'y_nghia', $loai ), null !== ( $p['y_nghia'] ?? null ), 'phan-y_nghia', false );
		if ( LuuPhanLoai::sua_duoc( 'chu_de', $loai ) ) {
			$cd   = wp_get_object_terms( (int) $bai->ID, 'chu_de', array( 'fields' => 'ids' ) );
			$ds[] = array( 'Chủ đề', is_array( $cd ) && $cd, 'phan-loai-chu_de', false );
		}
		$ds[] = array( 'Đối chiếu sách', ! empty( $p['loi']['doi_chieu']['ho_so'] ), 'doi-chieu', false );
		$ds[] = array( TamSua::nhan( 'chu_thich', $loai ), null !== ( $p['chu_thich'] ?? null ), 'phan-chu_thich', true );
		$o    = '';
		foreach ( $ds as [ $ten, $co, $toi, $tuy ] ) {
			$o .= $co ? '<li class="co"><span aria-hidden="true">✓</span> ' . esc_html( $ten ) . '<span class="screen-reader-text"> (đã có)</span></li>'
				: '<li class="chua"><span aria-hidden="true">○</span> <a href="#' . esc_attr( $toi ) . '" data-toi-phan="' . esc_attr( $toi ) . '">' . esc_html( $ten ) . '</a>' . ( $tuy ? ' <span class="pq-tuy">(tuỳ chọn)</span>' : '' ) . '</li>';
		}
		return '<section class="pq-khoi" id="con-thieu" aria-labelledby="pq-con-thieu"><h2 id="pq-con-thieu">Bài này còn thiếu</h2><ul class="pq-can">' . $o . '</ul></section>';
	}

	/** Căn cứ nội bộ (chỉ người biên soạn thấy): trang gốc máy đã lấy, việc đang chờ của bài. '' khi không có gì. */
	private static function can_cu( int $id, array $ctx ): string {
		$o   = '';
		$may = $ctx['phan']['nguon_may'] ?? null;
		if ( ! empty( $may['url_goc'] ) ) {
			$o .= '<p class="goc-nguon">Máy lấy từ nguồn "' . esc_html( \Phongdao\DichVu\NhapNguon\NguonTuDong::ten( (string) ( $may['nguon_id'] ?? '' ) ) ) . '"' . ( empty( $may['ngay_lay'] ) ? '' : ' ngày ' . esc_html( (string) $may['ngay_lay'] ) ) . ': <a href="' . esc_url( (string) $may['url_goc'] ) . '" rel="noreferrer nofollow">xem trang gốc</a> (người đọc không thấy).</p>';
		}
		$mo = array_values( array_filter( Viec::mo( 'post', $id ), static fn( array $v ): bool => 'goi_y' !== $v['loai'] ) );
		if ( $mo ) {
			$o .= '<h3>Việc đang chờ (' . count( $mo ) . ')</h3><ul>';
			foreach ( $mo as $viec ) {
				$ten_viec    = QuyetViec::LOAI[ $viec['loai'] ][0] ?? $viec['loai'];
				$ten_viec_tt = array( Viec::CHO => 'Chờ', Viec::DE_SAU => 'Để sau' )[ $viec['trang_thai'] ] ?? $viec['trang_thai'];
				$o .= '<li><a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => $viec['id'] ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $ten_viec . ' #' . $viec['id'] ) . '</a> · ' . esc_html( $ten_viec_tt ) . '</li>';
			}
			$o .= '</ul>';
		}
		return '' === $o ? '' : '<section class="pq-khoi phongdao-sua-viec" id="can-cu-noi-bo" aria-labelledby="pq-can-cu"><h2 id="pq-can-cu">Căn cứ nội bộ</h2>' . $o . '</section>';
	}

	/** Tên một phần (hoặc bộ phân loại, thể thơ) trong lời báo của "Lưu thay đổi". */
	private static function ten_phan_chung( string $k, string $loai ): string {
		if ( isset( TamSua::TAM[ $k ] ) ) {
			return TamSua::nhan( $k, $loai );
		}
		if ( isset( KhaiBao::PHAN_LOAI[ $k ] ) ) {
			return (string) KhaiBao::PHAN_LOAI[ $k ][1];
		}
		return 'the_tho' === $k ? 'Thể thơ' : '';
	}

	/** Lời báo sau "Lưu thay đổi": các phần đã lưu, gửi nháp, chưa lưu được (tham số do màn tự đặt; chỉ nhận tên phần có thật). */
	private static function bao_luu_chung( string $loai, \WP_Post $bai ): string {
		$ds   = static fn( string $khoa ): array => array_values( array_unique( array_filter( array_map( static fn( string $k ): string => self::ten_phan_chung( sanitize_key( $k ), $loai ), explode( ',', (string) wp_unslash( $_GET[ $khoa ] ?? '' ) ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc lời báo
		$da   = $ds( 'cac_phan' );
		$nhap = $ds( 'nhap' );
		$chua = $ds( 'chua' );
		$o    = $da ? 'Đã lưu ' . count( $da ) . ' phần: ' . implode( ', ', $da ) . '. Lịch sử ghi từng phần.' . ( 'publish' === $bai->post_status ? ' Trang người đọc đã cập nhật.' : ' Bài chưa công khai; khách chưa thấy.' ) : '';
		$o   .= $nhap ? ' Đã gửi nháp vào Hộp việc, chờ duyệt: ' . implode( ', ', $nhap ) . '.' : '';
		$o   .= $chua ? ' Chưa lưu được: ' . implode( ', ', $chua ) . ' — xem lời báo ở từng phần; chữ đang nhập vẫn giữ trên tab này.' : '';
		return '' === trim( $o ) ? 'Không có gì thay đổi.' : trim( $o );
	}

	/**
	 * Nút riêng "Đối chiếu sách" (D-104 ý 6, docs/32 mục 4.4): chọn sách hoặc "+ Sách mới" ngay tại chỗ (tên, người biên
	 * soạn, nhà xuất bản, năm — tạo hồ sơ loại sách; người có quyền Biên tập làm được), trang, bản quét nếu có; dòng "Người
	 * đọc sẽ thấy". Lời trong sách khác bản đang đăng: "Sửa lời theo sách" hoặc "Ghi bản trong sách làm dị bản". Chỉ người
	 * điền; máy không tự điền sách, trang.
	 */
	private static function khoi_doi_chieu( \WP_Post $bai, array $ctx ): string {
		$id   = (int) $bai->ID;
		$loi  = $ctx['phan']['loi'] ?? null;
		$dc   = (array) ( $loi['doi_chieu'] ?? array() );
		$sach = \Phongdao\KhoLuu\HoSo::sach();
		$hien = ! empty( $dc['ho_so'] ) ? 'Lời đã đối chiếu với «' . ( $sach[ (int) $dc['ho_so'] ] ?? (string) get_post_field( 'post_title', (int) $dc['ho_so'] ) ) . '»' . ( empty( $dc['trang'] ) ? '' : ', tr. ' . $dc['trang'] ) : 'Chưa đối chiếu sách.';
		$chon = '<option value="0">— Chọn sách —</option>';
		foreach ( $sach as $sid => $ten ) {
			$chon .= '<option value="' . (int) $sid . '"' . selected( (int) ( $dc['ho_so'] ?? 0 ), $sid, false ) . '>' . esc_html( $ten ) . '</option>';
		}
		$moi = current_user_can( 'cadao_duyet' ) || current_user_can( NguonCaiDat::QUYEN )
			? '<details class="phongdao-sach-moi"><summary>+ Sách mới</summary><p class="description">Tạo hồ sơ nguồn loại sách (giấy phép, xin phép do Quản trị điền sau).</p>'
				. '<p><label for="dc-ten">Tên sách</label><br><input id="dc-ten" name="sach_moi[ten]" class="regular-text"></p><p><label for="dc-tg">Người biên soạn</label><br><input id="dc-tg" name="sach_moi[tac_gia]" class="regular-text"></p>'
				. '<p><label for="dc-nxb">Nhà xuất bản</label><br><input id="dc-nxb" name="sach_moi[nha_xuat_ban]" class="regular-text"></p><p><label for="dc-nam">Năm</label><br><input id="dc-nam" name="sach_moi[nam]" class="small-text"></p></details>'
			: '';
		$dong = implode( "\n", (array) ( $loi['dong'] ?? array() ) );
		return '<details class="phongdao-doi-chieu" id="doi-chieu"' . ( 'doi_chieu' === ( $_GET['phan'] ?? '' ) ? ' open' : '' ) . '><summary class="button">Đối chiếu sách</summary>' // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở lại khối vừa lưu
			. '<p class="phongdao-dc-hien" role="status">' . esc_html( $hien ) . '</p>'
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_doi_chieu_sach"><input type="hidden" name="id" value="' . $id . '">' . self::an_ve() . wp_nonce_field( 'phongdao_doi_chieu_' . $id, '_wpnonce', true, false )
			. '<p><label for="dc-sach">Sách</label><br><select id="dc-sach" name="ho_so">' . $chon . '</select></p>' . $moi
			. '<p><label for="dc-trang">Trang</label><br><input id="dc-trang" name="trang" class="small-text" value="' . esc_attr( (string) ( $dc['trang'] ?? '' ) ) . '"></p>'
			. '<p><label for="dc-url">Đường dẫn bản quét <span class="description">không bắt buộc</span></label><br><input id="dc-url" name="url" type="url" class="regular-text" value="' . esc_attr( (string) ( $dc['url'] ?? '' ) ) . '"></p>'
			. '<fieldset><legend>Lời trong sách</legend>'
			. '<label><input type="radio" name="lam" value="giu" checked> Giống bản đang đăng</label><br>'
			. '<label><input type="radio" name="lam" value="sua"> Khác: sửa lời theo sách</label><br>'
			. '<label><input type="radio" name="lam" value="di_ban"> Khác: ghi bản trong sách làm dị bản</label></fieldset>'
			. '<p><label for="dc-loi">Lời trong sách <span class="description">chỉ cần khi khác; gõ đúng như trong sách, mỗi dòng một câu</span></label><br><textarea id="dc-loi" name="loi_sach" rows="5" class="large-text">' . esc_textarea( $dong ) . '</textarea></p>'
			. '<p class="description" data-dc-xem>Người đọc sẽ thấy: Lời đã đối chiếu với «tên sách», tr. N.</p>'
			. '<p><button type="submit" class="button button-primary">Lưu đối chiếu</button></p></form></details>';
	}

	/**
	 * Lưu đối chiếu sách: kiểm quyền, nonce; sách mới (vai Biên tập trở lên) tạo hồ sơ loại sách; rồi lưu qua đường lưu
	 * phần (Lịch sử): "giữ" — lời giữ nguyên, ghi đối chiếu; "sửa" — lời theo sách (người viết) kèm đối chiếu; "dị bản" —
	 * thêm một dị bản (người viết) mang đối chiếu, lời đang đăng giữ nguyên.
	 *
	 * @return array{ok:bool,loi:string}
	 */
	public static function luu_doi_chieu( int $id, array $p, int $nguoi ): array {
		$bai = get_post( $id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || ! user_can( $nguoi, 'edit_post', $id ) ) {
			return array( 'ok' => false, 'loi' => 'Bạn không có quyền sửa bài này.' );
		}
		$hs  = (int) ( $p['ho_so'] ?? 0 );
		$moi = array_map( 'strval', (array) ( $p['sach_moi'] ?? array() ) );
		if ( ! $hs && '' !== trim( $moi['ten'] ?? '' ) ) {
			if ( ! user_can( $nguoi, 'cadao_duyet' ) && ! user_can( $nguoi, NguonCaiDat::QUYEN ) ) {
				return array( 'ok' => false, 'loi' => 'Thêm sách mới cần quyền Biên tập.' );
			}
			$t = \Phongdao\KhoLuu\HoSo::them( (string) $moi['ten'], array_intersect_key( $moi, array_flip( \Phongdao\MienDuLieu\HoSoNguon::TRUONG_SACH ) ), false, $nguoi );
			if ( ! $t['ok'] ) {
				return array( 'ok' => false, 'loi' => $t['loi'] );
			}
			$hs = $t['id'];
		}
		if ( ! $hs || 'sach' !== ( \Phongdao\KhoLuu\HoSo::truong( $hs )['loai'] ?? '' ) ) {
			return array( 'ok' => false, 'loi' => 'Chọn một sách, hoặc thêm sách mới.' );
		}
		$dc   = array_filter( array( 'ho_so' => $hs, 'trang' => trim( sanitize_text_field( (string) ( $p['trang'] ?? '' ) ) ), 'url' => esc_url_raw( trim( (string) ( $p['url'] ?? '' ) ) ) ) );
		$lam  = in_array( $p['lam'] ?? '', array( 'giu', 'sua', 'di_ban' ), true ) ? (string) $p['lam'] : 'giu';
		$loi  = \Phongdao\KhoLuu\PhanBai::lay( $id, 'loi' );
		$dong = array_map( 'trim', explode( "\n", str_replace( "\r", '', trim( (string) ( $p['loi_sach'] ?? '' ) ) ) ) );
		$nc   = array( 'nguoi' => $nguoi, 'goi' => \Phongdao\MienDuLieu\KiemPhan::GOI_NGUOI, 'duyet' => user_can( $nguoi, 'cadao_duyet' ) );
		if ( 'di_ban' === $lam ) {
			if ( '' === implode( '', $dong ) ) {
				return array( 'ok' => false, 'loi' => 'Gõ lời trong sách để ghi làm dị bản.' );
			}
			$ds   = (array) ( \Phongdao\KhoLuu\PhanBai::lay( $id, 'di_ban' )['muc'] ?? array() );
			$ds[] = array( 'dong' => $dong, 'nguoi_lam' => \Phongdao\MienDuLieu\NguoiLam::NGUOI_VIET, 'nguon' => $dc ); // dị bản ghi nguồn là sách, trang người điền
			$kq   = \Phongdao\DichVu\LuuPhan\LuuPhan::luu( $id, 'di_ban', $ds, $nc );
		} else {
			if ( ! $loi ) {
				return array( 'ok' => false, 'loi' => 'Bài chưa có lời để đối chiếu.' );
			}
			$v = array( 'doi_chieu' => $dc ) + $loi;
			if ( 'sua' === $lam ) {
				if ( '' === implode( '', $dong ) ) {
					return array( 'ok' => false, 'loi' => 'Gõ lời trong sách để sửa theo.' );
				}
				$v['dong']      = $dong;
				$v['nguoi_lam'] = \Phongdao\MienDuLieu\NguoiLam::NGUOI_VIET;
			}
			$kq = \Phongdao\DichVu\LuuPhan\LuuPhan::luu( $id, 'loi', $v, $nc );
		}
		return $kq['ok'] ? array( 'ok' => true, 'loi' => '' ) : array( 'ok' => false, 'loi' => implode( ' ', $kq['loi'] ) );
	}

	public static function doi_chieu_sach(): void {
		$id = absint( $_POST['id'] ?? 0 );
		if ( ! current_user_can( 'cadao_soan' ) || ! $id ) {
			wp_die( 'Bạn không có quyền sửa bài này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_doi_chieu_' . $id );
		$kq = self::luu_doi_chieu( $id, wp_unslash( $_POST ), get_current_user_id() );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), $kq['loi'], MINUTE_IN_SECONDS );
		} elseif ( ! empty( $_POST['trang_doc'] ) ) {
			wp_safe_redirect( self::trang_doc( $id ) ); // gửi từ tấm "Đối chiếu sách" trên trang đọc
			exit;
		}
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( array( 'ket_qua' => $kq['ok'] ? 'doi_chieu' : 'loi', 'phan' => 'doi_chieu' ), self::url( $id ) ) . '#doi-chieu' );
		exit;
	}

	/**
	 * Công tắc cờ "Có thể không phải dân gian" ở cột bên (D-104, D-105): đang tắt → "Bật cờ" mở ô lý do và nguồn (bật phải
	 * có cả hai, người viết có căn cứ); đang bật → lý do hiện tại, "Sửa lý do" và "Tắt cờ". Lưu qua đúng đường lưu một phần
	 * (phần `co_nghi`, có Lịch sử). Máy không bao giờ tự bật.
	 */
	private static function cong_tac_co( \WP_Post $bai, array $ctx ): string {
		$id  = (int) $bai->ID;
		$cu  = $ctx['phan']['co_nghi'] ?? null;
		$phien = \Phongdao\DichVu\LuuPhan\LuuPhan::phien( $id, 'co_nghi', $cu );
		$form = 'co_nghi' === ( $ctx['form_loi']['phan'] ?? '' ) ? $ctx['form_loi']['vao'] : null;
		$phien_form = null !== $form ? (string) ( $form['phien_phan'] ?? '' ) : $phien;
		$an  = '<input type="hidden" name="action" value="phongdao_sua_phan">' . self::an_ve() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="phan" value="co_nghi"><input type="hidden" name="phien_phan" value="' . esc_attr( $phien_form ) . '">' . wp_nonce_field( 'phongdao_sua_phan_' . $id . '_co_nghi', '_wpnonce', true, false );
		$doi_chieu = null !== $form && ! hash_equals( $phien, $phien_form ) ? TamSua::doi_chieu_phien( $cu, $phien ) : '';
		$sua = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-giu-nhap="' . $id . ':co_nghi" data-phien-soan="' . esc_attr( NhapDo::phien() ) . '"' . ( null !== $form ? ' data-giu-loi="1"' : '' ) . '>' . $an . TamSua::than( 'co_nghi', null !== $form ? NhapDo::de_hien( 'co_nghi', $form, $cu ) : $cu, $ctx ) . $doi_chieu . '<p><button type="submit" class="button button-primary">' . ( $cu ? 'Lưu lý do' : 'Bật cờ' ) . '</button></p></form>';
		$tat = $cu ? '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . $an . '<input type="hidden" name="ly_do" value=""><button type="submit" class="button">Tắt cờ</button></form>' : '';
		return '<section class="phongdao-sua-co" id="phan-co_nghi"><h3>Có thể không phải dân gian</h3>'
			. '<p class="phongdao-sua-co-tt" role="status">' . ( $cu ? '<strong>Đang bật.</strong> ' . esc_html( (string) ( $cu['ly_do'] ?? '' ) ) : 'Đang tắt.' ) . '</p>'
			. $tat . '<details' . ( 'co_nghi' === ( $_GET['phan'] ?? '' ) ? ' open' : '' ) . '><summary>' . ( $cu ? 'Sửa lý do, nguồn' : 'Bật cờ' ) . '</summary><p class="description">Bật khi có căn cứ câu này có thể không phải văn học dân gian. Cần lý do và nguồn (sách, trang, đường dẫn) — người đọc thấy "Vì sao?".</p>' . $sua . '</details></section>'; // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở lại khối vừa lưu
	}

	/**
	 * Công tắc "Có từ ngữ thô tục" ở cột bên (D-111): bật thì trang đọc che lời, dị bản, từ khó sau lời báo, người đọc bấm
	 * "Hiển thị lời" mới thấy; lời không bị sửa. Lưu qua đúng đường lưu một phần (phần `tho_tuc`, có kiểm phiên và Lịch sử).
	 * Người bật tay; máy không bao giờ tự bật.
	 */
	private static function cong_tac_tho_tuc( \WP_Post $bai, array $ctx ): string {
		$id    = (int) $bai->ID;
		$cu    = $ctx['phan']['tho_tuc'] ?? null;
		$phien = \Phongdao\DichVu\LuuPhan\LuuPhan::phien( $id, 'tho_tuc', $cu );
		$form  = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_sua_phan">' . self::an_ve()
			. '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="phan" value="tho_tuc"><input type="hidden" name="phien_phan" value="' . esc_attr( $phien ) . '">'
			. wp_nonce_field( 'phongdao_sua_phan_' . $id . '_tho_tuc', '_wpnonce', true, false )
			. ( $cu ? '' : '<input type="hidden" name="bat" value="1">' )
			. '<button type="submit" class="button">' . ( $cu ? 'Tắt' : 'Bật' ) . '</button></form>';
		return '<section class="phongdao-sua-co" id="phan-tho_tuc"><h3>Có từ ngữ thô tục</h3>'
			. '<p class="phongdao-sua-co-tt" role="status">' . ( $cu ? '<strong>Đang bật.</strong> Trang đọc che lời; người đọc bấm "Hiển thị lời" mới thấy.' : 'Đang tắt.' ) . '</p>'
			. '<p class="description">Bật khi lời, dị bản hoặc từ khó của bài có từ ngữ thô tục. Lời không bị sửa, chỉ được che lại kèm lời báo. Bài đang bật không hiện ở trang chủ, Câu hôm nay, Một câu ngẫu nhiên và Đọc tiếp.</p>'
			. $form . '</section>';
	}

	/**
	 * Đổi thể loại của bài (D-104 ý 3; chỉ người có quyền duyệt): đường dẫn theo thể loại mới, đường cũ tự chuyển hướng 301
	 * sang đường mới (KhaiBao::ve_duong_dan_chuan), ghi Lịch sử (trước / sau). Phần riêng của thể loại cũ (vd. đáp án khi
	 * rời câu đố) giữ trong dữ liệu, không xoá.
	 *
	 * @return array{ok:bool,loi:string,cu:string,moi:string}
	 */
	public static function doi_the_loai_bai( int $id, string $moi, int $nguoi ): array {
		$bai = get_post( $id );
		$hong = static fn( string $l ): array => array( 'ok' => false, 'loi' => $l, 'cu' => '', 'moi' => '' );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'trash' === $bai->post_status ) {
			return $hong( 'Không có bài này.' );
		}
		if ( ! user_can( $nguoi, 'cadao_duyet' ) || ! user_can( $nguoi, 'edit_post', $id ) ) {
			return $hong( 'Chỉ người có quyền duyệt được đổi thể loại.' );
		}
		if ( ! \Phongdao\MienDuLieu\TheLoai::co( $moi ) ) {
			return $hong( 'Thể loại không có.' );
		}
		$cu = (string) \Phongdao\DichVu\DungTrang\DocBai::the_loai( $bai );
		if ( $cu === $moi ) {
			return $hong( 'Bài đã ở thể loại này.' );
		}
		$duong_cu = 'publish' === $bai->post_status ? (string) wp_make_link_relative( (string) get_permalink( $bai ) ) : '';
		$kq       = wp_set_object_terms( $id, $moi, KhaiBao::THE_LOAI );
		if ( is_wp_error( $kq ) ) {
			return $hong( 'Chưa đổi được thể loại.' );
		}
		clean_post_cache( $id );
		Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'post',
				'doi_tuong_id'   => $id,
				'phan'           => 'the_loai',
				'nguoi_tao'      => (string) $nguoi,
				'quyet'          => 'doi_the_loai',
				'truoc'          => array( 'the_loai' => $cu, 'duong_dan' => $duong_cu ),
				'sau'            => array( 'the_loai' => $moi, 'duong_dan' => 'publish' === $bai->post_status ? (string) wp_make_link_relative( (string) get_permalink( $id ) ) : '' ),
				'nguoi_quyet'    => $nguoi,
				'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		return array( 'ok' => true, 'loi' => '', 'cu' => $cu, 'moi' => $moi );
	}

	/** Trang đọc của bài (đã đăng: đường dẫn thật; chưa đăng: bản xem trước). */
	public static function trang_doc( int $id ): string {
		clean_post_cache( $id );
		return 'publish' === get_post_status( $id ) ? (string) get_permalink( $id ) : (string) get_preview_post_link( $id );
	}

	/** Form "Đổi thể loại" (admin-post): kiểm quyền, nonce; về lại trang sửa kèm lời báo. */
	public static function doi_the_loai(): void {
		$id = absint( $_POST['id'] ?? 0 );
		if ( ! current_user_can( 'cadao_duyet' ) || ! $id ) {
			wp_die( 'Chỉ người có quyền duyệt được đổi thể loại.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_doi_the_loai_' . $id );
		$kq = self::doi_the_loai_bai( $id, sanitize_key( (string) ( $_POST['the_loai'] ?? '' ) ), get_current_user_id() );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), $kq['loi'], MINUTE_IN_SECONDS );
		} elseif ( ! empty( $_POST['trang_doc'] ) ) {
			wp_safe_redirect( self::trang_doc( $id ) ); // gửi từ tấm "Đổi thể loại" trên trang đọc: mở đường dẫn mới
			exit;
		}
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( 'ket_qua', $kq['ok'] ? 'doi_the_loai' : 'loi', self::url( $id ) ) . '#phan-loai' );
		exit;
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

	/** Đăng (cần quyền duyệt) / Ẩn khỏi người đọc / Về nháp: đúng dịch vụ của dải "Đang sửa" trên trang đọc (kiểm trạng thái lúc mở trang, ghi nhật ký). */
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
		return '<div class="phongdao-trang-thai">' . $o . '</div>';
	}

	/** Lời đang lưu, luôn hiện ở đầu cột chính (chỉ đọc); $them: nhắc trình bày, ô "Sửa lời", Đối chiếu sách. */
	private static function loi_xem( ?array $loi, string $them = '' ): string {
		$dong = array_values( (array) ( $loi['dong'] ?? array() ) );
		if ( ! $dong ) {
			return '<section class="pq-khoi phongdao-loi-xem" id="loi-doi-chieu"><h2>Lời</h2><p>Bài chưa có lời. Mở "Thêm lời" bên dưới để thêm.</p>' . $them . '</section>';
		}
		$o = '';
		$so = 0;
		foreach ( $dong as $d ) {
			$o .= '' === trim( (string) $d ) ? '<li class="ngat" aria-hidden="true"></li>' : '<li><span class="so" aria-hidden="true">' . sprintf( '%02d', ++$so ) . '</span>' . esc_html( (string) $d ) . '</li>';
		}
		return '<section class="pq-khoi phongdao-loi-xem" id="loi-doi-chieu"><h2>Lời <span class="description">(' . $so . ' dòng)</span></h2><ol tabindex="0" aria-label="Lời của bài">' . $o . '</ol>' . $them . '</section>';
	}

	/** Nhắc lỗi trình bày của phần đang lưu (MienDuLieu\KiemTrinhBay) — chỉ nhắc, không chặn lưu, không tự sửa chữ. */
	private static function nhac_html( string $phan, ?array $cu, array $ctx ): string {
		$nhac = \Phongdao\MienDuLieu\KiemTrinhBay::nhac( $phan, $cu, (array) ( $ctx['phan']['loi']['dong'] ?? array() ) );
		return $nhac ? '<div class="notice notice-warning inline phongdao-nhac"><p><strong>Nhắc (không chặn lưu):</strong> ' . esc_html( implode( ' ', array_column( $nhac, 'chu' ) ) ) . '</p></div>' : '';
	}

	/**
	 * Tục ngữ chưa có Ý nghĩa mà còn "Nghĩa bóng" cũ do người viết (D-118, D-119): mời người có quyền duyệt bấm gộp chữ đó
	 * vào Ý nghĩa. Nghĩa bóng máy chép (Wiktionary) không gộp được — Ý nghĩa không nhận chữ máy chép. Máy không tự chuyển.
	 */
	private static function moi_gop_nghia_bong( \WP_Post $bai, array $ctx ): string {
		$nb = $ctx['phan']['nghia_bong'] ?? null;
		if ( null !== ( $ctx['phan']['y_nghia'] ?? null ) || ! is_array( $nb ) || '' === trim( (string) ( $nb['chu'] ?? '' ) ) || ! in_array( $nb['nguoi_lam'] ?? '', array( \Phongdao\MienDuLieu\NguoiLam::NGUOI_VIET, \Phongdao\MienDuLieu\NguoiLam::AI_DA_DUYET ), true ) ) {
			return '';
		}
		$chu = '<p>Bài này còn "Nghĩa bóng" cũ: «' . esc_html( mb_strimwidth( (string) $nb['chu'], 0, 300, '…' ) ) . '»</p>';
		if ( ! current_user_can( 'cadao_duyet' ) ) {
			return '<div class="notice notice-info inline phongdao-gop-nb">' . $chu . '<p class="description">Người có quyền duyệt có thể gộp chữ này vào Ý nghĩa.</p></div>';
		}
		return '<div class="notice notice-info inline phongdao-gop-nb">' . $chu
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_gop_nghia_bong"><input type="hidden" name="id" value="' . (int) $bai->ID . '">' . self::an_ve() . wp_nonce_field( 'phongdao_gop_nghia_bong_' . $bai->ID, '_wpnonce', true, false )
			. '<p><button type="submit" class="button">Gộp vào Ý nghĩa</button> <span class="description">Chữ chuyển nguyên sang Ý nghĩa (giữ người làm, dấu có hỗ trợ AI), Nghĩa bóng để trống; Lịch sử ghi cả hai. Không gộp thì Nghĩa bóng vẫn hiện như cũ.</span></p></form></div>';
	}

	/**
	 * Gộp Nghĩa bóng cũ vào Ý nghĩa (người có quyền duyệt bấm): một lượt lưu hai phần — Ý nghĩa nhận đúng chữ, người làm,
	 * dấu AI, định dạng, nguồn; Nghĩa bóng gỡ. Hỏng giữa chừng thì cả hai giữ như cũ.
	 *
	 * @return array{ok:bool,loi:string}
	 */
	public static function gop_nghia_bong_bai( int $id, int $nguoi ): array {
		global $wpdb;
		if ( ! user_can( $nguoi, 'cadao_duyet' ) || ! user_can( $nguoi, 'edit_post', $id ) || 'tuc-ngu' !== \Phongdao\DichVu\DungTrang\DocBai::the_loai( get_post( $id ) ) ) {
			return array( 'ok' => false, 'loi' => 'Cần quyền duyệt trên một bài tục ngữ.' );
		}
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return array( 'ok' => false, 'loi' => 'Chưa mở được lượt lưu. Thử lại sau.' );
		}
		$nc = array( 'nguoi' => $nguoi, 'goi' => \Phongdao\MienDuLieu\KiemPhan::GOI_NGUOI, 'duyet' => true );
		try {
			if ( ! \Phongdao\DichVu\LuuPhan\LuuPhan::khoa_bai( $id ) ) {
				throw new \RuntimeException( 'Không tìm thấy bài.' );
			}
			$nb = \Phongdao\KhoLuu\PhanBai::lay( $id, 'nghia_bong' );
			if ( null !== \Phongdao\KhoLuu\PhanBai::lay( $id, 'y_nghia' ) || ! is_array( $nb ) || ! in_array( $nb['nguoi_lam'] ?? '', array( \Phongdao\MienDuLieu\NguoiLam::NGUOI_VIET, \Phongdao\MienDuLieu\NguoiLam::AI_DA_DUYET ), true ) ) {
				throw new \RuntimeException( 'Bài đã có Ý nghĩa, hoặc Nghĩa bóng không gộp được. Tải lại để xem bản mới.' );
			}
			$yn = array_intersect_key( $nb, array_flip( array( 'chu', 'ai', 'nguoi_lam', 'nguon', 'dinh_dang' ) ) );
			$k1 = \Phongdao\DichVu\LuuPhan\LuuPhan::luu( $id, 'y_nghia', $yn, $nc );
			$k2 = $k1['ok'] ? \Phongdao\DichVu\LuuPhan\LuuPhan::luu( $id, 'nghia_bong', null, $nc ) : $k1;
			if ( ! $k1['ok'] || ! $k2['ok'] ) {
				throw new \RuntimeException( implode( ' ', array_merge( $k1['loi'], $k2['loi'] ) ) ?: 'Chưa gộp được.' );
			}
			if ( false === $wpdb->query( 'COMMIT' ) ) {
				throw new \RuntimeException( 'Chưa gộp được. Thử lại sau.' );
			}
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			clean_post_cache( $id );
			return array( 'ok' => false, 'loi' => $e->getMessage() );
		}
		return array( 'ok' => true, 'loi' => '' );
	}

	public static function gop_nghia_bong(): void {
		$id = absint( $_POST['id'] ?? 0 );
		check_admin_referer( 'phongdao_gop_nghia_bong_' . $id );
		$kq = self::gop_nghia_bong_bai( $id, get_current_user_id() );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), $kq['loi'], MINUTE_IN_SECONDS );
		}
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( array( 'ket_qua' => $kq['ok'] ? 'gop_nghia' : 'loi', 'phan' => 'y_nghia' ), self::url( $id, self::$ve ) ) . '#phan-y_nghia' );
		exit;
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

	/**
	 * Khối Phân loại (D-121): mỗi bộ (chủ đề, vùng, mô-típ, hình thức, trò chơi) một hàng thẻ chip — chip đặc là mục đang
	 * gắn (bấm để bỏ), chip nét đứt là mục máy gợi ý (thẻ "Gợi ý chủ đề, vùng" đang mở; bấm mới gắn), "＋ Thêm" mở danh sách
	 * mục có sẵn. Mỗi bộ vẫn là một form riêng (kiểm mốc, Lịch sử); "Lưu thay đổi" gửi chung. Thể thơ, thể loại ở cuối.
	 */
	private static function phan_loai( \WP_Post $bai, string $loai ): string {
		$id    = $bai->ID;
		$goi_y = self::goi_y_mo( $id );
		$ra    = '<section class="pq-khoi phongdao-phan-loai" id="phan-loai" aria-labelledby="phan-loai-h"><h2 id="phan-loai-h">Phân loại</h2>';
		foreach ( array_keys( LuuPhanLoai::SUA_DUOC ) as $tax ) {
			if ( LuuPhanLoai::sua_duoc( $tax, $loai ) && taxonomy_exists( $tax ) ) {
				$ra .= self::hang_phan_loai( $bai, $tax, $goi_y[ $tax ] ?? array() );
			}
		}
		$tl = get_the_terms( $id, KhaiBao::THE_LOAI );
		$mt = get_the_terms( $id, 'muc_tu' );
		$ra .= '<div class="pq-pl-cuoi">' . self::the_tho( $bai );
		// Đổi thể loại: chỉ người có quyền duyệt; báo trước đường dẫn đổi, đường cũ tự chuyển
		$ten_tl = is_array( $tl ) && $tl ? implode( ', ', wp_list_pluck( $tl, 'name' ) ) : 'Chưa có';
		if ( current_user_can( 'cadao_duyet' ) ) {
			$chon = '';
			foreach ( \Phongdao\MienDuLieu\TheLoai::DS as $ma => $ten ) {
				$chon .= '<option value="' . esc_attr( $ma ) . '"' . selected( $ma, $loai, false ) . '>' . esc_html( $ten ) . '</option>';
			}
			$ra .= '<details class="phongdao-sua-phan pq-pl-phu" id="doi-the-loai"><summary>Thể loại: ' . esc_html( $ten_tl ) . ' <span class="pq-doi">Đổi thể loại</span></summary><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_doi_the_loai"><input type="hidden" name="id" value="' . $id . '">' . self::an_ve() . wp_nonce_field( 'phongdao_doi_the_loai_' . $id, '_wpnonce', true, false )
				. '<p><label for="phongdao-the-loai-moi">Thể loại mới</label><br><select id="phongdao-the-loai-moi" name="the_loai">' . $chon . '</select></p>'
				. '<p class="description">Đường dẫn của bài đổi theo thể loại mới; đường cũ tự chuyển sang đường mới (người đọc và Google không mất lối). Phần riêng của thể loại cũ (vd. đáp án câu đố) vẫn giữ trong dữ liệu. Lịch sử ghi lại lần đổi.</p>'
				. '<p><button type="submit" class="button">Đổi thể loại</button></p></form></details>';
		} else {
			$ra .= '<p class="pq-pl-phu">Thể loại: ' . esc_html( $ten_tl ) . '</p>';
		}
		$ra .= ( is_array( $mt ) && $mt ? '<p class="pq-mo-ta">Mục từ trong Từ điển: ' . esc_html( implode( ', ', wp_list_pluck( $mt, 'name' ) ) ) . ' · gắn theo phần Từ khó.</p>' : '' );
		return $ra . '</div></section>';
	}

	/**
	 * Gợi ý chủ đề, vùng của máy đang mở cho bài (thẻ "goi_y" Chờ / Để sau).
	 *
	 * @return array<string,array<int,int>> bộ phân loại => [mã mục => mã thẻ việc]
	 */
	public static function goi_y_mo( int $id ): array {
		$ra = array();
		foreach ( Viec::mo( 'post', $id, 'goi_y' ) as $v ) {
			$tax = sanitize_key( (string) ( $v['noi_dung']['loai'] ?? '' ) );
			$t   = '' !== $tax && taxonomy_exists( $tax ) ? get_term_by( 'slug', (string) ( $v['noi_dung']['ma'] ?? '' ), $tax ) : false;
			if ( $t instanceof \WP_Term ) {
				$ra[ $tax ][ (int) $t->term_id ] = (int) $v['id'];
			}
		}
		return $ra;
	}

	/** Một bộ phân loại thành một hàng chip trong form riêng của bộ đó. */
	private static function hang_phan_loai( \WP_Post $bai, string $tax, array $goi_y ): string {
		$id    = (int) $bai->ID;
		$nhan  = KhaiBao::PHAN_LOAI[ $tax ][1];
		$gan   = wp_get_object_terms( $id, $tax );
		$gan   = is_array( $gan ) ? $gan : array();
		$may   = array_values( array_filter( $gan, static fn( \WP_Term $t ): bool => LuuPhanLoai::may_giu( $tax, $t ) ) );
		$dang  = array_map( static fn( \WP_Term $t ): int => (int) $t->term_id, array_values( array_filter( $gan, static fn( \WP_Term $t ): bool => ! LuuPhanLoai::may_giu( $tax, $t ) ) ) );
		$chon  = LuuPhanLoai::lua_chon( $tax );
		$o_id  = 'pl-' . $tax;
		$mo    = $tax === sanitize_key( (string) ( $_GET['phan_loai'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở lại khối vừa lưu
		$o     = static fn( \WP_Term $t, string $lop, string $chu, bool $tick ): string => '<label class="' . $lop . '" for="' . $o_id . '-' . (int) $t->term_id . '"><input type="checkbox" id="' . $o_id . '-' . (int) $t->term_id . '" name="chon[]" value="' . (int) $t->term_id . '"' . checked( $tick, true, false ) . '> ' . $chu . '</label>';
		$chip  = '';
		$them  = '';
		foreach ( $may as $t ) {
			$chip .= '<span class="pq-chip khoa">' . esc_html( $t->name ) . ' <small>thể thơ</small></span>';
		}
		foreach ( $chon as $t ) {
			$tid = (int) $t->term_id;
			if ( in_array( $tid, $dang, true ) ) {
				$chip .= $o( $t, 'pq-chip', esc_html( $t->name ) . '<span class="pq-chip-x" aria-hidden="true">×</span>', true );
			} elseif ( isset( $goi_y[ $tid ] ) ) {
				$chip .= $o( $t, 'pq-chip goi-y', '<span aria-hidden="true">＋</span> ' . esc_html( $t->name ) . ' <small>máy gợi ý</small>', false );
			} else {
				$them .= $o( $t, 'choice', esc_html( $t->name ), false );
			}
		}
		$ra  = '<div class="pq-pl" id="phan-loai-' . esc_attr( $tax ) . '"><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-giu-nhap="' . $id . ':' . esc_attr( $tax ) . '" data-ten-phan="' . esc_attr( $nhan ) . '" data-moc-server="' . esc_attr( (string) wp_json_encode( array_values( $dang ) ) ) . '" data-phien-soan="' . esc_attr( NhapDo::phien() ) . '"><input type="hidden" name="action" value="phongdao_sua_phan_loai">' . self::an_ve() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="tax" value="' . esc_attr( $tax ) . '">';
		$ra .= wp_nonce_field( 'phongdao_sua_phan_loai_' . $id . '_' . $tax, '_wpnonce', true, false );
		foreach ( $dang as $tid ) {
			$ra .= '<input type="hidden" name="da_thay[]" value="' . $tid . '">';
		}
		$ra .= '<fieldset><legend class="pq-pl-ten">' . esc_html( $nhan ) . '<span data-phan-trang-thai></span></legend><div class="pq-chip-ds" id="' . $o_id . '-ds">' . $chip
			. ( '' === $chip ? '<span class="pq-pl-trong">Chưa gắn.</span>' : '' )
			. '<details class="pq-pl-them"' . ( $mo ? ' open' : '' ) . '><summary class="pq-chip them">＋ Thêm<span class="screen-reader-text"> ' . esc_html( mb_strtolower( $nhan ) ) . '</span></summary><div class="pq-pl-hop">'
			. ( count( $chon ) > 12 ? '<p class="o-nhap phongdao-loc-muc" data-chi-js hidden><label for="' . $o_id . '-loc">Lọc danh sách</label><input type="search" id="' . $o_id . '-loc" data-loc-muc="' . $o_id . '-them" autocomplete="off"></p>' : '' )
			. '<div class="phongdao-chon-muc" id="' . $o_id . '-them" data-pl-them>' . ( '' === $them && ! $chon ? '<p>Chưa có mục nào trong bộ ' . esc_html( mb_strtolower( $nhan ) ) . '.</p>' : $them ) . '</div>'
			. '<p class="description">Đánh dấu để gắn. Thêm mục mới vào danh sách không tự gắn vào bài.</p>' . DanhMuc::cong_cu( $tax ) . '</div></details></div></fieldset>';
		return $ra . '<p class="phongdao-sua-nut"><button type="submit" class="button button-primary">Lưu ' . esc_html( mb_strtolower( $nhan ) ) . '</button></p></form></div>';
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
		// Thu gọn ở cột bên (D-121); mở sẵn ngay sau khi khôi phục
		$mo = in_array( sanitize_key( (string) ( $_GET['ket_qua'] ?? '' ) ), array( 'da_khoi_phuc' ), true ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở lại khối vừa dùng
		return '<details class="pq-khoi pq-gon phongdao-sua-viec" id="lich-su"' . ( $mo ? ' open' : '' ) . '><summary>Lịch sử <span class="pq-dem">' . count( $ds ) . '</span></summary>'
			. ( '' === $dau ? '<p>Chưa có lần sửa nào được ghi.</p>' : '<p class="description">Khôi phục chỉ có ở lần sửa mới nhất của mỗi phần, khi chưa ai sửa tiếp; khôi phục cũng được ghi thành một lần sửa mới.</p><ol class="phongdao-lich-su">' . $dau . '</ol>' )
			. ( '' === $cu ? '' : '<details class="phongdao-lich-cu"><summary>Xem ' . ( count( $ds ) - 5 ) . ' lần sửa cũ hơn</summary><ol class="phongdao-lich-su" start="6">' . $cu . '</ol></details>' )
			. '</details>';
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
		$anh = '';
		if ( 'media' === ( $r['phan'] ?? '' ) ) {
			foreach ( array( 'truoc' => 'Ảnh trước', 'sau' => 'Ảnh sau' ) as $key => $label ) {
				$id = \Phongdao\KhoLuu\AnhMinhHoa::id( $r[ $key ] );
				$anh .= '<div><strong>' . $label . '</strong>' . ( $id ? \Phongdao\GiaoTiep\Khoi\AnhMinhHoa::html( $id ) : '<p>Không gắn ảnh.</p>' ) . '</div>';
			}
		}
		return '<details class="phongdao-so-sanh"><summary>Xem trước/sau</summary><ul>' . $o . '</ul>' . $anh . '</details>';
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

	/**
	 * Người vừa gắn một chủ đề / vùng máy gợi ý (chip nét đứt hay chọn trong danh sách): thẻ gợi ý đó xong với quyết "Đồng
	 * ý", ghi người quyết — không còn chờ ở Bổ sung. Máy không tự gắn; chỉ đóng thẻ khi mục đã nằm trên bài.
	 */
	public static function dong_goi_y_da_gan( int $id, string $tax, int $nguoi ): int {
		$so = 0;
		foreach ( self::goi_y_mo( $id )[ $tax ] ?? array() as $tid => $vid ) {
			if ( has_term( (int) $tid, $tax, $id ) && Viec::dong( (int) $vid, Viec::XONG, 'dong_y', $nguoi ) ) {
				++$so;
			}
		}
		return $so;
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
		if ( $kq['ok'] ) {
			self::dong_goi_y_da_gan( $id, $tax, get_current_user_id() );
		}
		if ( self::tra_json() ) {
			wp_send_json( array( 'ok' => $kq['ok'], 'phan' => $tax, 'ket_qua' => ! $kq['ok'] ? 'loi' : ( $kq['doi'] ? 'da_luu' : 'khong_doi' ), 'loi' => $kq['ok'] ? '' : implode( ' ', $kq['loi'] ) ) );
		}
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ), MINUTE_IN_SECONDS );
		}
		$ket_qua = ! $kq['ok'] ? 'loi' : ( $kq['doi'] ? 'da_luu_pl' : 'khong_doi' );
		if ( $kq['ok'] && ! empty( $_POST['trang_doc'] ) ) {
			wp_safe_redirect( self::trang_doc( $id ) . '#doc-them' ); // gửi từ tấm "Sửa phân loại" trên trang đọc
			exit;
		}
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
		// Bật cờ "Có thể không phải dân gian" phải có cả lý do và nguồn (D-105); để trống lý do = tắt cờ
		$co_trong = static function ( $x ) use ( &$co_trong ): bool {
			return is_array( $x ) ? ! array_filter( $x, static fn( $y ): bool => ! $co_trong( $y ) ) : '' === trim( (string) $x );
		};
		if ( 'co_nghi' === $phan && '' !== trim( (string) wp_unslash( $_POST['ly_do'] ?? '' ) ) && $co_trong( wp_unslash( $_POST['nguon'] ?? array() ) ) ) {
			if ( self::tra_json() ) {
				wp_send_json( array( 'ok' => false, 'phan' => $phan, 'ket_qua' => 'loi', 'loi' => 'Bật cờ cần ghi cả nguồn cho lý do (sách, trang hoặc đường dẫn). Chưa lưu.' ) );
			}
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), 'Bật cờ cần ghi cả nguồn cho lý do (sách, trang hoặc đường dẫn). Chưa lưu.', MINUTE_IN_SECONDS );
			self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
			wp_safe_redirect( add_query_arg( array( 'ket_qua' => 'loi', 'phan' => 'co_nghi' ), self::url( $id ) ) . '#phan-co_nghi' );
			exit;
		}
		$yc = new \WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $id . '/phan/' . $phan );
		$yc->set_param( 'id', $id );
		$yc->set_param( 'phan', $phan );
		$yc->set_body_params( wp_unslash( $_POST ) );
		$kq = BienTap::luu_phan( $yc );
		if ( self::tra_json() ) {
			$tb = (string) ( $kq['thong_bao'] ?? '' );
			wp_send_json( array( 'ok' => (bool) $kq['ok'], 'phan' => $phan, 'ket_qua' => ! $kq['ok'] ? 'loi' : ( str_contains( $tb, 'nháp' ) ? 'nhap' : ( ! empty( $kq['doi'] ) ? 'da_luu' : 'khong_doi' ) ), 'loi' => $kq['ok'] ? '' : implode( ' ', $kq['loi'] ?? array() ) ) );
		}
		$ve_nhap = '';
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ?? array() ), MINUTE_IN_SECONDS );
			$ve_nhap = NhapDo::giu( $id, $phan, wp_unslash( $_POST ) );
		}
		$ket_qua = ! $kq['ok'] ? 'loi' : ( ! empty( $kq['thong_bao'] ) && str_contains( $kq['thong_bao'], 'nháp' ) ? 'nhap' : ( ! empty( $kq['doi'] ) ? 'da_luu' : 'khong_doi' ) );
		self::$ve = self::ve_hop_le( wp_unslash( (string) ( $_POST['ve'] ?? '' ) ) );
		// Đến từ danh sách đã lọc: lưu được và người chọn "tự sang bài tiếp" thì mở bài kế; không thì giữ lối sang bài kế
		$sau = absint( $_POST['bai_sau'] ?? 0 );
		$sau = $sau && $sau !== $id && self::sua_duoc( $sau ) ? $sau : 0;
		if ( $kq['ok'] && $sau && ! empty( $_POST['sang'] ) && empty( $_POST['con_chua_luu'] ) ) {
			wp_safe_redirect( add_query_arg( array( 'ket_qua' => 'da_luu_sang', 'da_luu_bai' => $id, 'da_luu_phan' => $phan ), self::url( $sau ) ) );
			exit;
		}
		wp_safe_redirect( add_query_arg( array_filter( array( 'ket_qua' => $ket_qua, 'phan' => $phan, 'bai_sau' => $sau, 'nhap_do' => $ve_nhap, 'con_chua_luu' => empty( $_POST['con_chua_luu'] ) ? 0 : 1 ) ), self::url( $id ) ) . '#phan-' . $phan );
		exit;
	}
}
