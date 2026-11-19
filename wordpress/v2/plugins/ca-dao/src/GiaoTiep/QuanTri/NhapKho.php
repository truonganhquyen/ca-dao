<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\UngVien\NhapTep;
use Phongdao\DichVu\UngVien\LanNhap;
use Phongdao\KhoLuu\Kho;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/** Nhập và xem trước ngay trong Bài mới; form PHP dùng được khi tắt JavaScript. */
final class NhapKho {

	public const AP_DUNG = 'phongdao_ap_dung_nhap';
	public const MAU = 'phongdao_mau_nhap';

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::AP_DUNG, array( self::class, 'nhan_ap_dung' ) );
		add_action( 'admin_post_' . self::MAU, array( self::class, 'mau' ) );
	}

	public static function url_xem( int $id, bool $tu_chay = false ): string {
		return add_query_arg( array_filter( array( 'page' => DanhSachKho::TRANG, 'xem_nhap' => $id, 'tu_chay' => $tu_chay ? 1 : 0 ) ), admin_url( 'admin.php' ) );
	}

	public static function url_lan( int $id ): string {
		return DanhSachKho::url( array( 'lan_nhap' => $id, 'tt' => 'tat_ca' ) );
	}

	/** Đích sau quyết chỉ là đúng lần nhập chứa câu của thẻ; không nhận URL ngoài hoặc lô không liên quan. */
	public static function ve_tu_the( string $url, int $viec ): string {
		$u = wp_parse_url( $url ); $g = wp_parse_url( admin_url( 'admin.php' ) );
		parse_str( (string) ( $u['query'] ?? '' ), $q );
		$v = \Phongdao\KhoLuu\Viec::lay( $viec );
		$id = absint( $q['lan_nhap'] ?? 0 );
		if ( ! current_user_can( 'cadao_soan' ) || ( $u['host'] ?? '' ) !== ( $g['host'] ?? '' ) || ( $u['path'] ?? '' ) !== ( $g['path'] ?? '' ) || ( $q['page'] ?? '' ) !== DanhSachKho::TRANG || ! $v || 'kho' !== $v['doi_tuong_loai'] || ! in_array( (int) $v['doi_tuong_id'], LanNhap::ids( $id ), true ) ) {
			return '';
		}
		return self::url_lan( $id );
	}

	private static function bao( string $chu, bool $ok = false ): void {
		set_transient( 'phongdao_kho_bao_' . get_current_user_id(), array( 'ok' => $ok, 'chu' => $chu ), MINUTE_IN_SECONDS );
	}

	public static function nhan_form(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) { wp_die( 'Bạn không có quyền nhập tệp.', '', array( 'response' => 403 ) ); }
		check_admin_referer( NhapTep::HANH_DONG );
		$chu = (string) wp_unslash( $_POST['chu'] ?? '' ); // nguyên văn, chỉ escape khi dựng biểu mẫu
		$ten = 'Danh sách đã dán';
		$kieu = sanitize_key( (string) ( $_POST['kieu'] ?? 'txt' ) );
		$loi = '';
		$f = $_FILES['tep'] ?? null;
		if ( is_array( $f ) && UPLOAD_ERR_NO_FILE !== (int) ( $f['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			if ( '' !== trim( $chu ) ) {
				$loi = 'Chọn tệp hoặc dán chữ cho một lần nhập; phần chữ bạn dán được giữ lại.';
			} elseif ( UPLOAD_ERR_OK !== (int) $f['error'] || ! is_uploaded_file( (string) $f['tmp_name'] ) || (int) $f['size'] > MB_IN_BYTES ) {
				$loi = 'Chưa đọc được tệp. Chọn TXT/CSV UTF-8, tối đa 1 MB.';
			} else {
				$ten = sanitize_file_name( (string) $f['name'] );
				$kieu = strtolower( pathinfo( $ten, PATHINFO_EXTENSION ) );
				$chu = (string) file_get_contents( (string) $f['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- tệp tải lên đã kiểm
			}
		}
		$hs = absint( $_POST['ho_so'] ?? 0 );
		$loai = sanitize_key( (string) ( $_POST['loai'] ?? '' ) );
		$nl = NguoiLam::MAY_CHEP === ( $_POST['nguoi_lam'] ?? '' ) ? NguoiLam::MAY_CHEP : NguoiLam::NGUOI_VIET;
		$doc = '' === $loi ? NhapTep::doc( $chu, $kieu, $loai ) : array( 'ok' => false, 'loi' => $loi );
		if ( ! $doc['ok'] ) {
			// Lỗi tách không làm mất chữ; tệp lỗi UTF-8 không được đưa vào trường HTML/DB.
			set_transient( 'phongdao_nhap_chu_' . get_current_user_id(), array( 'chu' => mb_check_encoding( $chu, 'UTF-8' ) && strlen( $chu ) <= MB_IN_BYTES ? $chu : '', 'ho_so' => $hs, 'loai' => $loai, 'kieu' => $kieu, 'nguoi_lam' => $nl ), 30 * MINUTE_IN_SECONDS );
			self::bao( $doc['loi'] );
			wp_safe_redirect( add_query_arg( 'nhap', 1, DanhSachKho::url() ) . '#nhap-tep' );
			exit;
		}
		$kq = LanNhap::tao( $doc['muc'], $hs, $ten, hash( 'sha256', $chu ), get_current_user_id(), $nl );
		if ( $kq['ok'] ) {
			delete_transient( 'phongdao_nhap_chu_' . get_current_user_id() );
			if ( $kq['da_co'] ) { self::bao( 'Đã nhận dữ liệu này từ trước; mở lại lần nhập cũ, không ghi đè.', true ); }
			wp_safe_redirect( self::url_xem( $kq['lan'] ) );
		} else {
			set_transient( 'phongdao_nhap_chu_' . get_current_user_id(), array( 'chu' => $chu, 'ho_so' => $hs, 'loai' => $loai, 'kieu' => $kieu, 'nguoi_lam' => $nl ), 30 * MINUTE_IN_SECONDS );
			self::bao( $kq['loi'] );
			wp_safe_redirect( add_query_arg( 'nhap', 1, DanhSachKho::url() ) . '#nhap-tep' );
		}
		exit;
	}

	public static function nhan_ap_dung(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) { wp_die( 'Bạn không có quyền nhập tệp.', '', array( 'response' => 403 ) ); }
		$id = absint( $_POST['lan'] ?? 0 );
		check_admin_referer( self::AP_DUNG . '_' . $id );
		$phien = (int) ( $_POST['phien'] ?? -1 );
		$lam = sanitize_key( (string) ( $_POST['lam'] ?? '' ) );
		if ( in_array( $lam, array( 'thu_lai', 'xem_bo_chon' ), true ) ) {
			$kq = LanNhap::thu_lai( $id, $phien, get_current_user_id(), 'xem_bo_chon' === $lam ? 'bo_qua' : 'loi' );
			if ( $kq['ok'] ) { wp_safe_redirect( self::url_xem( $kq['lan'] ) ); exit; }
		} elseif ( in_array( $lam, array( 'luu', 'nhap', 'tiep' ), true ) ) {
			if ( 'tiep' !== $lam && '1' !== ( $_POST['du_mau'] ?? '' ) ) {
				self::bao( 'Biểu mẫu lựa chọn chưa gửi đầy đủ. Chưa nhập mục nào; chia nhỏ tệp rồi xem trước lại.' );
				wp_safe_redirect( self::url_xem( $id ) ); exit;
			}
			$giu = 'tiep' === $lam ? null : array_map( 'absint', (array) wp_unslash( $_POST['giu'] ?? array() ) );
			$loai = array_map( 'sanitize_key', (array) wp_unslash( $_POST['loai'] ?? array() ) );
			$kq = LanNhap::ap_dung( $id, $phien, get_current_user_id(), $giu, $loai, sanitize_key( (string) ( $_POST['loai_chung'] ?? '' ) ), 'luu' === $lam );
			if ( $kq['ok'] ) {
				if ( ! empty( $kq['nhac'] ) ) { self::bao( $kq['nhac'] ); wp_safe_redirect( self::url_xem( $id ) ); exit; }
				if ( 0 === $kq['con'] && 'luu' !== $lam ) { wp_safe_redirect( self::url_lan( $id ) ); exit; }
				wp_safe_redirect( self::url_xem( $id, 'luu' !== $lam && ! empty( $_POST['tu_chay'] ) ) );
				exit;
			}
		} else {
			$kq = array( 'ok' => false, 'loi' => 'Chọn thao tác nhập hợp lệ.' );
		}
		self::bao( $kq['loi'] );
		wp_safe_redirect( self::url_xem( $id ) );
		exit;
	}

	public static function mau(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) { wp_die( 'Bạn không có quyền tải mẫu.', '', array( 'response' => 403 ) ); }
		check_admin_referer( self::MAU );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="phongdao-mau-nhap.csv"' );
		echo "\xEF\xBB\xBFma_muc,loi,the_loai,trang,url\r\n";
		exit;
	}

	public static function chon_loai( string $name, string $id, string $chon = '', string $rong = '— Chọn thể loại —' ): string {
		$o = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '"><option value="">' . esc_html( $rong ) . '</option>';
		foreach ( TheLoai::TU_LOAI_GOC as $ma => $slug ) {
			$o .= '<option value="' . esc_attr( $ma ) . '"' . selected( $ma, $chon, false ) . '>' . esc_html( TheLoai::DS[ $slug ] ) . '</option>';
		}
		return $o . '</select>';
	}

	public static function form(): string {
		$cu = get_transient( 'phongdao_nhap_chu_' . get_current_user_id() );
		$cu = is_array( $cu ) ? $cu : array();
		$hs = '<option value="0">Chưa rõ nguồn</option>';
		foreach ( NhapTep::ho_so_chon() as $id => $ten ) { $hs .= '<option value="' . (int) $id . '"' . selected( $id, (int) ( $cu['ho_so'] ?? 0 ), false ) . '>' . esc_html( $ten ) . '</option>'; }
		$mo = ! empty( $_GET['nhap'] ) || $cu; // phpcs:ignore WordPress.Security.NonceVerification -- chỉ mở biểu mẫu
		$mau = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::MAU ), self::MAU );
		return '<details class="pq-khoi phongdao-kho-nhap" id="nhap-tep"' . ( $mo ? ' open' : '' ) . '><summary>Nhập tệp vào Kho</summary><p>Chọn TXT/CSV hoặc dán chữ. Xem trước cách tách và thể loại rồi đưa vào Kho; chưa đăng bài nào. Tối đa ' . NhapTep::TOI_DA . ' mục, 1 MB.</p>'
			. '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . NhapTep::HANH_DONG . '">' . wp_nonce_field( NhapTep::HANH_DONG, '_wpnonce', true, false )
			. '<div class="phongdao-tp-loc"><div class="phongdao-tp-truong"><label for="nhap-ho-so">Hồ sơ nguồn</label><select id="nhap-ho-so" name="ho_so">' . $hs . '</select></div><div class="phongdao-tp-truong"><label for="nhap-loai">Thể loại chung (có thể chọn từng mục ở bước sau)</label>' . self::chon_loai( 'loai', 'nhap-loai', (string) ( $cu['loai'] ?? '' ) ) . '</div></div>'
			. '<p><a href="' . esc_url( admin_url( 'admin.php?page=phongdao&the=ho_so' ) ) . '">Quản lý hồ sơ nguồn</a> · <a href="' . esc_url( $mau ) . '">Tải mẫu CSV</a></p>'
			. '<p><label for="nhap-tep-chon">Chọn tệp TXT/CSV UTF-8</label><br><input id="nhap-tep-chon" type="file" name="tep" accept=".txt,.csv,text/plain,text/csv"></p>'
			. '<details' . ( $cu ? ' open' : '' ) . '><summary>Hoặc dán chữ</summary><p><label for="nhap-chu">Nội dung tệp (TXT: các mục cách nhau một dòng trống)</label><textarea id="nhap-chu" name="chu" rows="7" class="large-text">' . esc_textarea( (string) ( $cu['chu'] ?? '' ) ) . '</textarea></p><label for="nhap-kieu">Định dạng chữ dán</label><select id="nhap-kieu" name="kieu"><option value="txt">TXT</option><option value="csv"' . selected( 'csv', (string) ( $cu['kieu'] ?? '' ), false ) . '>CSV theo mẫu</option></select></details>'
			. '<details><summary>Cách ghi tệp và nguồn gốc lời</summary><p>CSV cần cột loi; tuỳ chọn ma_muc, the_loai, trang, url. Ô lời nhiều dòng/ngắt khổ đặt trong ngoặc kép. Thể loại nhận tên hoặc mã: ca_dao, thanh_ngu_tuc_ngu, dong_dao, ve, cau_do. TXT tách bằng dòng trống; nếu chỗ ngắt khổ dễ nhầm, dùng CSV. Giữ nguyên lời, chỉ ghi trang/URL đã có trong tư liệu.</p><label for="nhap-nguoi-lam">Lời trong tệp được nhận bằng cách nào?</label><select id="nhap-nguoi-lam" name="nguoi_lam"><option value="nguoi_viet">Người cung cấp gõ hoặc chép lại</option><option value="may_chep"' . selected( NguoiLam::MAY_CHEP, (string) ( $cu['nguoi_lam'] ?? '' ), false ) . '>Máy trích từ trang nguồn (cần URL từng mục)</option></select></details>'
			. '<p><button class="button button-primary" type="submit">Xem trước nhập</button></p></form></details>';
	}

	private static function an( array $v ): string {
		return '<input type="hidden" name="action" value="' . self::AP_DUNG . '"><input type="hidden" name="lan" value="' . (int) $v['id'] . '"><input type="hidden" name="phien" value="' . (int) $v['phien'] . '">' . wp_nonce_field( self::AP_DUNG . '_' . $v['id'], '_wpnonce', true, false );
	}

	public static function tom_tat( array $v ): string {
		$d = LanNhap::dem( $v );
		$so = count( (array) ( $v['sau']['ket_qua'] ?? array() ) );
		$dat = $d['moi'] + $d['da_co'] + $d['da_gop'] + $d['nghi_trung'];
		return 'Đã ghi nhận ' . $dat . '/' . count( $v['noi_dung']['muc'] ) . ' mục trong Kho; kết quả lượt nhập: mới ' . $d['moi'] . ' · có từ trước ' . $d['da_co'] . ' · có lời khớp ' . $d['da_gop'] . ' · cần đối chiếu ' . $d['nghi_trung'] . ' · lỗi ' . $d['loi'] . ' · xung đột ' . $d['xung_dot'] . ' · bỏ qua ' . $d['bo_qua'] . '. Đã xử lý ' . $so . ' mục. Lượt nhập chưa đăng bài nào.';
	}

	public static function xem( int $id ): string {
		$v = LanNhap::lay( $id );
		if ( ! $v ) { return '<div class="wrap"><h1>Bài mới</h1><p role="status">Không có lần nhập này.</p></div>'; }
		$nd = $v['noi_dung'];
		$o = '<div class="wrap pq-man phongdao-admin-lo phongdao-nhap"><h1>Bài mới · Xem trước nhập</h1><p><a href="' . esc_url( DanhSachKho::url() ) . '">← Bài mới</a> · <strong>' . esc_html( $nd['ten'] ) . '</strong> · ' . count( $nd['muc'] ) . ' mục</p>' . DanhSachKho::lay_bao();
		if ( LanNhap::XEM !== $v['trang_thai'] ) {
			$o .= '<p role="status">' . esc_html( self::tom_tat( $v ) ) . '</p><p><a class="button" href="' . esc_url( self::url_lan( $id ) ) . '">Mở các mục trong Bài mới</a></p>';
			if ( LanNhap::DANG === $v['trang_thai'] ) {
				$tu = ! empty( $_GET['tu_chay'] ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ tự tiếp tục lượt đã bấm nhập
				$o .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' . ( $tu ? ' data-tu-chay' : '' ) . '>' . self::an( $v ) . ( $tu ? '<input type="hidden" name="tu_chay" value="1">' : '' ) . '<p><button name="lam" value="tiep" class="button button-primary" data-tiep>Tiếp tục nhập</button> <a class="button" href="' . esc_url( self::url_xem( $id ) ) . '">Dừng tự chạy</a></p><p>Mỗi lượt tối đa 25 mục; có thể mở lại để tiếp tục. Chưa đăng bài nào.</p></form>';
			}
			return $o . self::chi_tiet( $v ) . '</div>';
		}
		$o .= '<p>Kiểm cách tách mục và thể loại. Dấu hiệu trùng chỉ để đối chiếu; chưa ghi mục nào vào Kho và chưa thay bài cũ. Hồ sơ nguồn: <strong>' . esc_html( $nd['ho_so'] ? get_the_title( $nd['ho_so'] ) : 'Chưa rõ' ) . '</strong>.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . self::an( $v )
			. '<p><label for="nhap-loai-chung">Áp dụng thể loại cho cả lần nhập (để trống để dùng từng mục)</label> ' . self::chon_loai( 'loai_chung', 'nhap-loai-chung', '', '— Dùng lựa chọn từng mục —' ) . '</p><div class="phongdao-nhap-cuon"><table class="widefat phongdao-nhap-bang"><thead><tr><th scope="col">Nhập</th><th scope="col">Lời và vị trí nguồn</th><th scope="col">Thể loại</th></tr></thead><tbody>';
		$doi_chieu = NhapTep::doi_chieu( $nd['muc'] );
		foreach ( $nd['muc'] as $i => $m ) {
			$goi = $doi_chieu[ $i ];
			$o .= '<tr><td><input type="checkbox" id="nhap-giu-' . $i . '" name="giu[]" value="' . $i . '"' . checked( ! in_array( $i, $nd['bo'], true ), true, false ) . '><label for="nhap-giu-' . $i . '">Mục ' . ( $i + 1 ) . '</label></td><td><pre>' . esc_html( (string) $m['loi'] ) . '</pre><p class="description">' . esc_html( ( '' !== $m['ma_muc'] ? 'Mã: ' . $m['ma_muc'] . ' · ' : '' ) . ( '' !== $m['trang'] ? 'Trang: ' . $m['trang'] . ' · ' : '' ) . $m['url'] ) . '</p><p>' . esc_html( $goi ) . '</p></td><td><label for="nhap-loai-' . $i . '" class="screen-reader-text">Thể loại mục ' . ( $i + 1 ) . '</label>' . self::chon_loai( 'loai[' . $i . ']', 'nhap-loai-' . $i, (string) $m['loai'] ) . '</td></tr>';
		}
		return $o . '</tbody></table></div><p><button type="submit" name="lam" value="nhap" class="button button-primary">Đưa các mục đã chọn vào Kho</button> <button type="submit" name="lam" value="luu" class="button">Xem lại lựa chọn</button></p><p><label><input type="checkbox" name="tu_chay" value="1" checked> Tự tiếp tục đến hết, mỗi lượt 25 mục</label></p><p>Không đăng bài và không sửa phần công khai của bài cũ. TXT tách chưa hợp lý: sửa tệp rồi tải lại hoặc dùng CSV giữ ngắt khổ.</p><input type="hidden" name="du_mau" value="1"></form></div>';
	}

	public static function chi_tiet( array $v ): string {
		$d = LanNhap::dem( $v );
		$o = '<details class="pq-khoi"><summary>Biên nhận và kết quả từng mục</summary><p>' . esc_html( self::tom_tat( $v ) ) . '</p><ol>';
		foreach ( (array) ( $v['sau']['ket_qua'] ?? array() ) as $i => $k ) {
			$loc = array( 'q' => (string) $k['id'], 'tt' => 'tat_ca', 'lan_nhap' => 'xung_dot' === $k['tt'] ? 0 : $v['id'] );
			$o .= '<li>Mục ' . ( (int) $i + 1 ) . ': ' . esc_html( $k['chu'] ) . ( $k['id'] ? ' <a href="' . esc_url( DanhSachKho::url( $loc ) ) . '">Xem trong Kho</a>' : '' ) . '</li>';
		}
		$o .= '</ol></details>';
		if ( $d['loi'] && LanNhap::XONG === $v['trang_thai'] ) {
			$o .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . self::an( $v ) . '<p><button name="lam" value="thu_lai" class="button">Xem trước và thử lại ' . $d['loi'] . ' mục lỗi</button></p></form>';
		}
		if ( $d['bo_qua'] && LanNhap::XONG === $v['trang_thai'] ) {
			$o .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . self::an( $v ) . '<p><button name="lam" value="xem_bo_chon" class="button">Xem lại ' . $d['bo_qua'] . ' mục đã bỏ chọn</button></p></form>';
		}
		return $o;
	}

	public static function trong_danh_sach( int $id ): string {
		$o = '';
		if ( $id ) {
			$v = LanNhap::lay( $id );
			if ( ! $v ) { return '<p role="status">Lần nhập không tồn tại; danh sách được giữ rỗng để tránh chọn nhầm mục.</p>'; }
			$o .= '<div class="pq-khoi"><p><strong>Lần nhập: ' . esc_html( $v['noi_dung']['ten'] ) . '</strong> · ' . esc_html( wp_date( 'd/m/Y H:i', strtotime( $v['tao_luc'] . ' UTC' ) ) ) . ' · <a href="' . esc_url( DanhSachKho::url() ) . '">Bỏ lọc lần nhập</a></p><p role="status">' . esc_html( self::hien_tai( $id ) ) . '</p>' . ( LanNhap::XONG !== $v['trang_thai'] ? '<p><a class="button" href="' . esc_url( self::url_xem( $id ) ) . '">Tiếp tục nhập</a></p>' : '' ) . self::chi_tiet( $v ) . '</div>';
		}
		$ds = LanNhap::gan_day();
		if ( $ds ) {
			$o .= '<details class="pq-khoi"><summary>Các lần nhập gần đây</summary><ul>';
			foreach ( $ds as $v ) { $o .= '<li><a href="' . esc_url( LanNhap::XONG === $v['trang_thai'] ? self::url_lan( $v['id'] ) : self::url_xem( $v['id'] ) ) . '">' . esc_html( $v['noi_dung']['ten'] . ' · ' . wp_date( 'd/m H:i', strtotime( $v['tao_luc'] . ' UTC' ) ) ) . '</a></li>'; }
			$o .= '</ul></details>';
		}
		return $o;
	}

	/** Đếm đúng tình trạng hiện tại, không dùng lời báo nhập chưa đăng sau khi người đã đăng tiếp. */
	public static function hien_tai( int $id ): string {
		global $wpdb;
		$ids = LanNhap::ids( $id );
		$da = 0; $chua = 0; $khop = 0;
		if ( $ids ) {
			$ds = $wpdb->get_results( 'SELECT k.trang_thai, p.post_status, COUNT(*) AS so FROM ' . Kho::bang() . " k LEFT JOIN {$wpdb->posts} p ON p.ID=k.post_id WHERE k.id IN (" . implode( ',', $ids ) . ') GROUP BY k.trang_thai,p.post_status' ); // chỉ ID đã ép int từ biên nhận
			foreach ( $ds ?: array() as $r ) {
				if ( 'da_gop' === $r->trang_thai ) { $khop += (int) $r->so; }
				elseif ( 'da_tao_bai' === $r->trang_thai && 'publish' === $r->post_status ) { $da += (int) $r->so; }
				else { $chua += (int) $r->so; }
			}
		}
		return 'Hiện tại: đã đăng ' . $da . ' bài · chưa đăng ' . $chua . ' mục · có lời khớp ' . $khop . ' mục. Chi tiết lỗi và mục chưa nhập ở biên nhận bên dưới.';
	}
}
