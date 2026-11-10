<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\NhapNguon\CadaoMe;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\HoSoNguon;
use Phongdao\MienDuLieu\SoSanh;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Nguồn & cài đặt trong wp-admin (docs/28 mục 4.7; bộ thiết kế A11, A12, A13, A14, A17) — thay các trang quản trị
 * nguồn, chống rác của MVP. Chỉ quyền cadao_nguon (Quản trị). Mọi form gửi admin-post có nonce.
 * Trạng thái xin phép, giấy phép, chính sách hiện của hồ sơ nguồn chỉ người bấm ở đây đổi; máy và agent không đổi.
 */
final class NguonCaiDat {

	public const QUYEN = 'cadao_nguon';

	public static function dang_ky(): void {
		foreach ( array( 'nguon', 'ho_so', 'turnstile', 'may' ) as $viec ) {
			add_action( 'admin_post_phongdao_luu_' . $viec, array( self::class, 'nhan_' . $viec ) );
		}
	}

	// ---------- Lưu (tách khỏi admin-post để kiểm thử gọi thẳng) ----------

	/** @return string lời báo */
	public static function luu_nguon( string $id, array $p ): string {
		$n = NguonTuDong::lay( $id );
		if ( ! $n ) {
			return 'Không có nguồn này.';
		}
		$cu                   = self::cai_dat( $n );
		$n['bat']             = ! empty( $p['bat'] );
		$n['so_bai_moi_ngay'] = max( 1, min( 50, (int) ( $p['so_bai_moi_ngay'] ?? $n['so_bai_moi_ngay'] ) ) );
		$n['loai_lay']        = array_values( array_intersect( array_keys( CadaoMe::LOAI ), array_map( 'strval', (array) ( $p['loai_lay'] ?? array() ) ) ) );
		$n['chep_chu_thich']  = ! empty( $p['chep_chu_thich'] );
		$n['bo_qua_co']       = empty( $p['bo_tho_tuc'] ) ? array() : array( 'tho_tuc' );
		NguonTuDong::luu( $n );
		NguonTuDong::hen_lich();
		self::ghi_dau_vet( 'nguon', 0, mb_substr( $id, 0, 32 ), $cu, self::cai_dat( $n ) );
		return $n['bat'] ? 'Đã lưu. Nguồn đang bật: máy đọc theo lịch, câu mới vào Hộp việc mỗi ngày.' : 'Đã lưu. Nguồn đang tắt.';
	}

	/** @return string lời báo */
	public static function luu_ho_so( int $id, array $p ): string {
		$hs = get_post( $id );
		if ( ! $hs || KhaiBao::HO_SO_NGUON !== $hs->post_type ) {
			return 'Không có hồ sơ này.';
		}
		$cu  = array( 'ten' => $hs->post_title ) + HoSo::truong( $id );
		$ten = trim( sanitize_text_field( (string) ( $p['ten'] ?? '' ) ) );
		if ( '' !== $ten && $ten !== $hs->post_title ) {
			wp_update_post( wp_slash( array( 'ID' => $id, 'post_title' => $ten ) ) );
		}
		$truong = array();
		foreach ( HoSoNguon::TRUONG as $k ) {
			$truong[ $k ] = 'chep_chu_thich' === $k ? ! empty( $p[ $k ] ) : sanitize_text_field( (string) ( $p[ $k ] ?? '' ) );
		}
		update_post_meta( $id, HoSo::META, wp_slash( HoSoNguon::chuan( $truong ) ) );
		self::ghi_dau_vet( 'ho_so', $id, 'ho_so', $cu, array( 'ten' => (string) get_post_field( 'post_title', $id ) ) + HoSo::truong( $id ) );
		return 'Đã lưu hồ sơ nguồn.';
	}

	/** Các cài đặt của nguồn tự động cần dấu vết (bật, số câu, loại lấy, chép chú thích, bỏ qua cờ). */
	private static function cai_dat( array $n ): array {
		return array(
			'bat'             => ! empty( $n['bat'] ),
			'so_bai_moi_ngay' => (int) ( $n['so_bai_moi_ngay'] ?? 0 ),
			'loai_lay'        => array_values( (array) ( $n['loai_lay'] ?? array() ) ),
			'chep_chu_thich'  => ! empty( $n['chep_chu_thich'] ),
			'bo_qua_co'       => array_values( (array) ( $n['bo_qua_co'] ?? array() ) ),
		);
	}

	/** Dấu vết một lần đổi hồ sơ nguồn / cài đặt nguồn (T-170): ai, lúc nào, trước, sau — chỉ khi có trường đổi. */
	private static function ghi_dau_vet( string $loai, int $id, string $phan, array $truoc, array $sau ): void {
		if ( ! SoSanh::truong_doi( $truoc, $sau ) ) {
			return;
		}
		Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => $loai,
				'doi_tuong_id'   => $id,
				'phan'           => $phan,
				'nguoi_tao'      => (string) get_current_user_id(),
				'quyet'          => 'luu',
				'truoc'          => $truoc,
				'sau'            => $sau,
				'nguoi_quyet'    => get_current_user_id(),
				'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
			)
		);
	}

	/** Khối "Bài chịu ảnh hưởng" của một hồ sơ nguồn, kèm lối mở danh sách Tác phẩm của nguồn tự động dùng hồ sơ này. */
	private static function anh_huong_html( int $id ): string {
		$a   = self::anh_huong( $id );
		$ten = array( 'loi' => 'Lời', 'chu_thich' => 'Từ khó', 'di_ban' => 'Dị bản', 'co_nghi' => 'Cờ nghi', 'boi_canh' => 'Bối cảnh', 'y_nghia' => 'Ý nghĩa', 'nghia_den' => 'Nghĩa đen', 'nghia_bong' => 'Nghĩa bóng', 'dung_khi' => 'Dùng khi' );
		$chi = implode( ', ', array_map( static fn( string $k, int $so ): string => ( $ten[ $k ] ?? $k ) . ' ' . $so . ' bài', array_keys( $a['phan'] ), $a['phan'] ) );
		$lien = array();
		foreach ( NguonTuDong::tat_ca() as $nid => $n ) {
			if ( (int) ( $n['ho_so'] ?? 0 ) === $id && preg_match( '/^[a-z0-9_-]+$/', (string) $nid ) ) {
				$lien[] = '<a href="' . esc_url( add_query_arg( array( 'page' => DanhSachTacPham::TRANG, 'tt' => 'tat_ca', 'nguon' => 'may:' . $nid ), admin_url( 'admin.php' ) ) ) . '">Tác phẩm từ nguồn ' . esc_html( (string) ( $n['ten'] ?: $nid ) ) . '</a>';
			}
		}
		return '<div class="notice notice-info inline"><p><strong>Bài chịu ảnh hưởng:</strong> ' . (int) $a['tong'] . ' bài có phần chép từ hoặc đối chiếu với hồ sơ này' . ( '' === $chi ? '' : ' (' . esc_html( $chi ) . ')' ) . '. Đổi giấy phép, cách ghi công hay "Chép chú thích" ảnh hưởng tới các bài này; máy không tự đổi bài đã đăng.' . ( $lien ? ' ' . implode( ' · ', $lien ) : '' ) . '</p></div>';
	}

	/** Danh sách dấu vết: ngày giờ, người, các trường đổi "cũ → mới". */
	private static function dau_vet( array $ds ): string {
		$o = '';
		foreach ( $ds as $r ) {
			$t  = strtotime( (string) $r['quyet_luc'] . ' UTC' );
			$nd = (int) $r['nguoi_quyet'] ? get_userdata( (int) $r['nguoi_quyet'] ) : null;
			$o .= '<li><strong>' . esc_html( $t ? wp_date( 'd/m/Y H:i', $t ) : (string) $r['quyet_luc'] ) . '</strong> · ' . esc_html( $nd ? $nd->display_name : 'máy' ) . ' · '
				. esc_html( implode( '; ', SoSanh::truong_doi( (array) $r['truoc'], (array) $r['sau'] ) ) ) . '</li>';
		}
		return '<h3>Lịch sử thay đổi</h3>' . ( '' === $o ? '<p>Chưa có thay đổi nào được ghi.</p>' : '<ol>' . $o . '</ol>' );
	}

	/** Khoá bí mật để trống = giữ khoá cũ; xoá Site key = tắt Turnstile. @return string lời báo */
	public static function luu_turnstile( array $p ): string {
		$site = sanitize_text_field( (string) ( $p['site'] ?? '' ) );
		update_option( 'cadao_turnstile_site', $site, false );
		$bi_mat = sanitize_text_field( (string) ( $p['bi_mat'] ?? '' ) );
		if ( '' === $site ) {
			update_option( 'cadao_turnstile_bi_mat', '', false );
			return 'Đã tắt Turnstile.';
		}
		if ( '' !== $bi_mat ) {
			update_option( 'cadao_turnstile_bi_mat', $bi_mat, false );
		}
		return 'Đã lưu. Turnstile bật cho các form người đọc gửi.';
	}

	// ---------- admin-post ----------

	public static function nhan_nguon(): void {
		self::kiem( 'phongdao_luu_nguon' );
		$id = sanitize_key( wp_unslash( $_POST['nguon'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- kiem() đã kiểm
		self::ve( 'nguon', self::luu_nguon( $id, wp_unslash( $_POST ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	public static function nhan_ho_so(): void {
		self::kiem( 'phongdao_luu_ho_so' );
		$id = (int) ( $_POST['ho_so'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		self::ve( 'ho_so', self::luu_ho_so( $id, wp_unslash( $_POST ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	/** Tạm dừng / chạy lại một việc nền (bảng điều khiển máy, T-179). */
	public static function nhan_may(): void {
		self::kiem( 'phongdao_luu_may' );
		$viec = sanitize_key( wp_unslash( (string) ( $_POST['viec'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- kiem() đã kiểm
		$kq   = \Phongdao\DichVu\May\DieuKhien::dat( $viec, 'dung' === ( $_POST['lam'] ?? '' ), get_current_user_id(), sanitize_text_field( wp_unslash( (string) ( $_POST['ly_do'] ?? '' ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		self::ve( 'may', $kq['chu'] );
	}

	public static function nhan_turnstile(): void {
		self::kiem( 'phongdao_luu_turnstile' );
		self::ve( 'chong_rac', self::luu_turnstile( wp_unslash( $_POST ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	private static function kiem( string $hanh_dong ): void {
		if ( ! current_user_can( self::QUYEN ) || ! check_admin_referer( $hanh_dong ) ) {
			wp_die( esc_html__( 'Không có quyền.', 'ca-dao' ), 403 );
		}
	}

	/** Về trang; lời báo giữ một phút cho đúng người vừa bấm (không đặt chữ trong đường dẫn — ai cũng tạo được liên kết). */
	private static function ve( string $the, string $bao ): void {
		set_transient( 'phongdao_bao_' . get_current_user_id(), $bao, MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( array( 'page' => Module::TRANG, 'the' => $the ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function bao(): string {
		$khoa = 'phongdao_bao_' . get_current_user_id();
		$bao  = (string) get_transient( $khoa );
		delete_transient( $khoa );
		return $bao;
	}

	// ---------- Trang ----------

	public static function hien( string $the, int $ho_so, string $bao ): string {
		$cac = array(
			'nguon'     => 'Nguồn tự động',
			'ho_so'     => 'Hồ sơ nguồn',
			'may'       => 'Máy đang làm',
			'chong_rac' => 'Chống rác',
		);
		$the = isset( $cac[ $the ] ) ? $the : 'nguon';
		$h   = '<div class="wrap phongdao-nguon"><h1>Nguồn &amp; cài đặt</h1>'
			. ( '' !== $bao ? '<div class="notice notice-success"><p>' . esc_html( $bao ) . '</p></div>' : '' )
			. '<nav class="nav-tab-wrapper" aria-label="Phần của trang">';
		foreach ( $cac as $ma => $ten ) {
			$h .= '<a class="nav-tab' . ( $ma === $the ? ' nav-tab-active' : '' ) . '" href="' . esc_url( add_query_arg( array( 'page' => Module::TRANG, 'the' => $ma ), admin_url( 'admin.php' ) ) ) . '"' . ( $ma === $the ? ' aria-current="page"' : '' ) . '>' . esc_html( $ten ) . '</a>';
		}
		$h .= '</nav>';
		if ( 'ho_so' === $the ) {
			$h .= $ho_so ? self::sua_ho_so( $ho_so ) : self::ds_ho_so();
		} elseif ( 'may' === $the ) {
			$h .= self::may();
		} elseif ( 'chong_rac' === $the ) {
			$h .= self::chong_rac();
		} else {
			$h .= self::nguon();
		}
		return $h . '</div>';
	}

	private static function form( string $hanh_dong, string $than, string $nut = 'Lưu' ): string {
		return '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . esc_attr( $hanh_dong ) . '">'
			. wp_nonce_field( $hanh_dong, '_wpnonce', true, false ) . $than . '<p class="submit"><button type="submit" class="button button-primary">' . esc_html( $nut ) . '</button></p></form>';
	}

	private static function nguon(): string {
		$dem = Kho::dem();
		$h   = '<p>Kho: <strong>' . (int) $dem['moi'] . '</strong> câu mới chờ chọn · ' . (int) $dem['da_tao_bai'] . ' đã thành tác phẩm · ' . (int) $dem['da_gop'] . ' đã gộp · ' . (int) $dem['bo_qua'] . ' bỏ qua. Kho không bao giờ hiện cho người đọc.</p>';
		foreach ( NguonTuDong::tat_ca() as $id => $n ) {
			$n    += NguonTuDong::mac_dinh();
			$loai  = '';
			foreach ( CadaoMe::LOAI as $ma => $ten ) {
				$loai .= '<label style="margin-right:12px"><input type="checkbox" name="loai_lay[]" value="' . esc_attr( $ma ) . '"' . checked( in_array( $ma, (array) $n['loai_lay'], true ), true, false ) . '> ' . esc_html( $ten ) . '</label>';
			}
			$than = '<input type="hidden" name="nguon" value="' . esc_attr( (string) $id ) . '"><table class="form-table" role="presentation"><tbody>'
				. '<tr><th scope="row">Bật</th><td><label><input type="checkbox" name="bat" value="1"' . checked( ! empty( $n['bat'] ), true, false ) . '> Máy đọc nguồn này theo lịch, mỗi ngày chọn câu mới vào Hộp việc</label></td></tr>'
				. '<tr><th scope="row"><label for="so-' . esc_attr( (string) $id ) . '">Số câu mới mỗi ngày</label></th><td><input type="number" min="1" max="50" id="so-' . esc_attr( (string) $id ) . '" name="so_bai_moi_ngay" value="' . (int) $n['so_bai_moi_ngay'] . '"></td></tr>'
				. '<tr><th scope="row">Loại lấy</th><td>' . $loai . '</td></tr>'
				. '<tr><th scope="row">Chép chú thích</th><td><label><input type="checkbox" name="chep_chu_thich" value="1"' . checked( ! empty( $n['chep_chu_thich'] ), true, false ) . '> Chép chú thích, lời giải đáp án của nguồn khi người bấm Đăng</label></td></tr>'
				. '<tr><th scope="row">Bỏ câu có từ thô tục</th><td><label><input type="checkbox" name="bo_tho_tuc" value="1"' . checked( in_array( 'tho_tuc', (array) $n['bo_qua_co'], true ), true, false ) . '> Không chọn vào Hộp việc</label></td></tr>'
				. '</tbody></table>';
			$nk = '';
			foreach ( array_slice( (array) $n['nhat_ky'], 0, 8 ) as $d ) {
				$nk .= '<li>' . esc_html( (string) ( $d['luc'] ?? '' ) . ' · ' . (string) ( $d['viec'] ?? '' ) . ( isset( $d['loi'] ) && '' !== (string) $d['loi'] ? ' · lỗi: ' . (string) $d['loi'] : '' ) ) . '</li>';
			}
			$h .= '<h2>' . esc_html( (string) ( $n['ten'] ?: $id ) ) . ( empty( $n['url'] ) ? '' : ' <a class="page-title-action" href="' . esc_url( $n['url'] ) . '" rel="noopener">Mở trang nguồn</a>' ) . '</h2>'
				. self::form( 'phongdao_luu_nguon', $than ) . '<h3>Máy đang làm (gần nhất)</h3>' . ( '' === $nk ? '<p>Chưa chạy lần nào.</p>' : '<ul>' . $nk . '</ul>' )
				. self::dau_vet( array_slice( array_values( array_filter( Viec::lich_su( 'nguon', 0, 100 ), static fn( array $r ): bool => mb_substr( (string) $id, 0, 32 ) === $r['phan'] ) ), 0, 10 ) );
		}
		return $h;
	}

	private static function ds_ho_so(): string {
		$h = '<p>Mỗi nguồn một hồ sơ: loại, giấy phép, cách ghi công, trạng thái xin phép, chính sách hiện với người đọc. Thiếu thì để trống ("chưa rõ"), không đoán.</p>'
			. '<table class="widefat striped"><thead><tr><th scope="col">Tên</th><th scope="col">Loại</th><th scope="col">Xin phép</th><th scope="col">Hiện với người đọc</th><th scope="col">Chép chú thích</th></tr></thead><tbody>';
		$ds = get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) );
		foreach ( $ds as $p ) {
			$t  = HoSo::truong( $p->ID );
			$h .= '<tr><td><a href="' . esc_url( add_query_arg( array( 'page' => Module::TRANG, 'the' => 'ho_so', 'ho_so' => $p->ID ), admin_url( 'admin.php' ) ) ) . '"><strong>' . esc_html( $p->post_title ) . '</strong></a></td>'
				. '<td>' . esc_html( HoSoNguon::LOAI[ $t['loai'] ?? '' ] ?? 'chưa rõ' ) . '</td><td>' . esc_html( HoSoNguon::XIN_PHEP[ $t['xin_phep'] ?? '' ] ?? 'chưa rõ' ) . '</td>'
				. '<td>' . esc_html( HoSoNguon::CHINH_SACH[ $t['chinh_sach_hien'] ?? '' ] ?? 'chưa rõ' ) . '</td><td>' . ( ! empty( $t['chep_chu_thich'] ) ? 'có' : 'không' ) . '</td></tr>';
		}
		return $h . ( $ds ? '' : '<tr><td colspan="5">Chưa có hồ sơ nào.</td></tr>' ) . '</tbody></table>';
	}

	private static function sua_ho_so( int $id ): string {
		$p = get_post( $id );
		if ( ! $p || KhaiBao::HO_SO_NGUON !== $p->post_type ) {
			return '<p>Không có hồ sơ này.</p>';
		}
		$t    = HoSo::truong( $id );
		$o    = static fn( string $k, string $nhan ): string => '<tr><th scope="row"><label for="hs-' . $k . '">' . esc_html( $nhan ) . '</label></th><td><input class="regular-text" id="hs-' . $k . '" name="' . $k . '" value="' . esc_attr( (string) ( $t[ $k ] ?? '' ) ) . '"></td></tr>';
		$chon = static function ( string $k, string $nhan, array $ds ) use ( $t ): string {
			$o = '<option value="">chưa rõ</option>';
			foreach ( $ds as $ma => $ten ) {
				$o .= '<option value="' . esc_attr( $ma ) . '"' . selected( (string) ( $t[ $k ] ?? '' ), $ma, false ) . '>' . esc_html( $ten ) . '</option>';
			}
			return '<tr><th scope="row"><label for="hs-' . $k . '">' . esc_html( $nhan ) . '</label></th><td><select id="hs-' . $k . '" name="' . $k . '">' . $o . '</select></td></tr>';
		};
		$than = '<input type="hidden" name="ho_so" value="' . (int) $id . '"><table class="form-table" role="presentation"><tbody>'
			. '<tr><th scope="row"><label for="hs-ten">Tên</label></th><td><input class="regular-text" id="hs-ten" name="ten" value="' . esc_attr( $p->post_title ) . '"></td></tr>'
			. $chon( 'loai', 'Loại', HoSoNguon::LOAI ) . $o( 'url', 'Đường dẫn' ) . $o( 'tac_gia', 'Tác giả, người biên soạn' ) . $o( 'nam', 'Năm' ) . $o( 'giay_phep', 'Giấy phép' ) . $o( 'ghi_cong', 'Cách ghi công' )
			. $chon( 'xin_phep', 'Xin phép', HoSoNguon::XIN_PHEP ) . $chon( 'chinh_sach_hien', 'Hiện với người đọc', HoSoNguon::CHINH_SACH )
			. '<tr><th scope="row">Chép chú thích</th><td><label><input type="checkbox" name="chep_chu_thich" value="1"' . checked( ! empty( $t['chep_chu_thich'] ), true, false ) . '> Được chép chú thích, lời giải của nguồn này</label></td></tr>'
			. $o( 'ghi_chu', 'Ghi chú' ) . '</tbody></table>';
		return '<p><a href="' . esc_url( add_query_arg( array( 'page' => Module::TRANG, 'the' => 'ho_so' ), admin_url( 'admin.php' ) ) ) . '">← Mọi hồ sơ</a></p><h2>' . esc_html( $p->post_title ) . '</h2>' . self::anh_huong_html( $id ) . self::form( 'phongdao_luu_ho_so', $than ) . self::dau_vet( Viec::lich_su( 'ho_so', $id, 10 ) );
	}

	/**
	 * Bảng điều khiển máy (T-179): mỗi việc nền — đang chạy theo lịch hay tạm dừng, lần chạy tới, lần chạy gần nhất, lỗi gần
	 * nhất, hạn mức; nút Tạm dừng / Chạy lại có lý do; nhật ký các lần bấm. Chỉ mục tìm chỉ xem, không tạm dừng (cần cho tìm).
	 */
	private static function may(): string {
		$h = '<p>Máy chỉ đọc nguồn, chọn câu vào Hộp việc và gợi ý; mọi quyết định đăng vẫn ở người. Tạm dừng chỉ bỏ qua lượt chạy theo lịch: không gỡ bài, không xoá kho, không đổi cài đặt nguồn.</p>';
		foreach ( \Phongdao\DichVu\May\DieuKhien::trang_thai() as $ma => $v ) {
			$toi  = $v['lan_toi'] ? wp_date( 'd/m/Y H:i', $v['lan_toi'] ) : 'chưa hẹn';
			$than = '<input type="hidden" name="viec" value="' . esc_attr( $ma ) . '"><input type="hidden" name="lam" value="' . ( $v['tam_dung'] ? 'chay' : 'dung' ) . '">'
				. '<p><label for="may-ly-do-' . esc_attr( $ma ) . '">Lý do (không bắt buộc)</label><br><input class="regular-text" id="may-ly-do-' . esc_attr( $ma ) . '" name="ly_do" value=""></p>';
			$h .= '<h2>' . esc_html( $v['ten'] ) . '</h2><p class="description">' . esc_html( $v['giai'] ) . '</p>'
				. '<table class="form-table" role="presentation"><tbody>'
				. '<tr><th scope="row">Trạng thái</th><td><strong>' . esc_html( $v['tam_dung'] ? 'Đang tạm dừng' : ( empty( $v['khong_nguon'] ) ? 'Chạy theo lịch' : 'Không có nguồn nào bật — lượt chạy theo lịch không làm gì' ) ) . '</strong></td></tr>'
				. '<tr><th scope="row">Lần chạy tới</th><td>' . esc_html( $v['tam_dung'] ? $toi . ' (sẽ bỏ qua khi còn tạm dừng)' : $toi ) . '</td></tr>'
				. '<tr><th scope="row">Lần chạy gần nhất</th><td>' . esc_html( '' === $v['lan_cuoi'] ? 'chưa ghi' : $v['lan_cuoi'] ) . '</td></tr>'
				. '<tr><th scope="row">Lỗi gần nhất</th><td>' . esc_html( '' === $v['loi'] ? 'không có' : $v['loi'] ) . '</td></tr>'
				. '<tr><th scope="row">Hạn mức, phạm vi</th><td>' . esc_html( $v['han_muc'] ) . '</td></tr>'
				. '</tbody></table>'
				. self::form( 'phongdao_luu_may', $than, $v['tam_dung'] ? 'Chạy lại' : 'Tạm dừng' )
				. self::dau_vet_may( array_slice( array_values( array_filter( Viec::lich_su( 'may', 0, 100 ), static fn( array $r ): bool => $ma === $r['phan'] ) ), 0, 10 ) );
		}
		global $wpdb;
		$sau = (int) get_option( \Phongdao\DichVu\ChiMucTim\ChiMucTim::SAU, -1 );
		$h  .= '<h2>Dựng lại chỉ mục tìm</h2><p class="description">Chạy khi cần dựng lại chỉ mục tìm sau nâng cấp; chỉ xem (tìm kiếm cần chỉ mục đủ nên không tạm dừng ở đây).</p><p>'
			. esc_html( $sau < 0 ? 'Không có lượt dựng lại đang dở.' : 'Đang dở, sau bài #' . $sau . '.' ) . ' Bảng chỉ mục có ' . (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . \Phongdao\DichVu\ChiMucTim\ChiMucTim::bang() ) . ' dòng.</p>'; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- không có giá trị ngoài
		return $h;
	}

	/** Nhật ký tạm dừng / chạy lại của một việc nền. */
	private static function dau_vet_may( array $ds ): string {
		$o = '';
		foreach ( $ds as $r ) {
			$t  = strtotime( (string) $r['quyet_luc'] . ' UTC' );
			$nd = (int) $r['nguoi_quyet'] ? get_userdata( (int) $r['nguoi_quyet'] ) : null;
			$o .= '<li><strong>' . esc_html( $t ? wp_date( 'd/m/Y H:i', $t ) : (string) $r['quyet_luc'] ) . '</strong> · ' . esc_html( ( $nd ? $nd->display_name : 'máy' ) . ' · ' . ( 'tam_dung' === $r['quyet'] ? 'tạm dừng' : 'chạy lại' ) . ( empty( $r['noi_dung']['ly_do'] ) ? '' : ' — ' . $r['noi_dung']['ly_do'] ) ) . '</li>';
		}
		return '<h3>Nhật ký tạm dừng, chạy lại</h3>' . ( '' === $o ? '<p>Chưa có lần nào.</p>' : '<ol>' . $o . '</ol>' );
	}

	/**
	 * Bài có phần chép từ (hoặc đối chiếu với) một hồ sơ nguồn, theo từng phần — để biết đổi chính sách nguồn ảnh hưởng tới
	 * đâu trước khi quyết (T-179). Một truy vấn gom theo phần.
	 *
	 * @return array{tong:int,phan:array<string,int>}
	 */
	public static function anh_huong( int $ho_so ): array {
		global $wpdb;
		$tu   = " FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status <> 'trash' AND pm.meta_key LIKE %s AND pm.meta_value LIKE %s";
		$tham = array( KhaiBao::TAC_PHAM, $wpdb->esc_like( '_phongdao_' ) . '%', '%' . $wpdb->esc_like( '"ho_so";i:' . $ho_so . ';' ) . '%' );
		$tong = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT pm.post_id)' . $tu, $tham ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$phan = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT pm.meta_key AS k, COUNT(DISTINCT pm.post_id) AS so' . $tu . ' GROUP BY pm.meta_key', $tham ), ARRAY_A ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$phan[ substr( (string) $h['k'], strlen( '_phongdao_' ) ) ] = (int) $h['so'];
		}
		return array( 'tong' => $tong, 'phan' => $phan );
	}

	private static function chong_rac(): string {
		$site = (string) get_option( 'cadao_turnstile_site', '' );
		$co   = '' !== (string) get_option( 'cadao_turnstile_bi_mat', '' );
		$than = '<table class="form-table" role="presentation"><tbody>'
			. '<tr><th scope="row"><label for="ts-site">Site key</label></th><td><input class="regular-text" id="ts-site" name="site" value="' . esc_attr( $site ) . '"></td></tr>'
			. '<tr><th scope="row"><label for="ts-bi-mat">Secret key</label></th><td><input class="regular-text" type="password" id="ts-bi-mat" name="bi_mat" value="" autocomplete="off" placeholder="' . ( $co ? 'đã lưu — để trống thì giữ nguyên' : '' ) . '"></td></tr>'
			. '</tbody></table>';
		return '<p>Form người đọc gửi luôn có ô bẫy ẩn và giới hạn 5 lần gửi mỗi giờ. Muốn chặn chắc hơn, bật <strong>Cloudflare Turnstile</strong> (miễn phí): Cloudflare → Turnstile → Add widget, tên miền <code>' . esc_html( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) . '</code>, chế độ "Managed", rồi dán hai khoá vào đây. <strong>Không gửi khoá bí mật cho ai, kể cả agent.</strong> Muốn tắt: xoá Site key rồi Lưu.</p>'
			. self::form( 'phongdao_luu_turnstile', $than );
	}
}
