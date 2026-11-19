<?php

namespace Phongdao\DichVu\UngVien;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\VanTay;

defined( 'ABSPATH' ) || exit;

/**
 * Nhập câu từ tệp vào Kho (D-104, docs/32 mục 3.3, 3.7): người dán danh sách hoặc tải tệp chữ (câu đã gõ từ một cuốn
 * sách), chọn hồ sơ nguồn và thể loại; máy tách câu (các câu cách nhau một dòng trống, giữ nguyên từng dòng trong câu) và
 * đưa vào Kho qua chống trùng như câu máy thu — **không đăng thẳng**. Máy không viết, không sửa lời (chỉ bỏ khoảng trắng
 * thừa đầu / cuối dòng), không tự điền nguồn: hồ sơ do người chọn, để trống thì câu không có nguồn.
 */
final class NhapTep {

	/** Mã nguồn của câu nhập từ tệp trong bảng kho. */
	public const NGUON = 'nhap-tay';

	public const HANH_DONG = 'phongdao_nhap_tep';

	/** Mỗi lần nhập tối đa. */
	public const TOI_DA = 500;

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan_form' ) );
		\Phongdao\GiaoTiep\QuanTri\NhapKho::dang_ky();
	}

	/**
	 * Tách chữ thành các câu: câu cách nhau bằng một (hay nhiều) dòng trống; mỗi câu giữ đúng các dòng người gõ.
	 *
	 * @return string[][] mỗi câu là danh sách dòng
	 */
	public static function tach( string $chu ): array {
		$chu = str_replace( array( "\r\n", "\r" ), "\n", $chu );
		$chu = (string) preg_replace( '/^\x{FEFF}/u', '', $chu ); // dấu BOM đầu tệp
		$ra  = array();
		foreach ( preg_split( "/\n[ \t]*\n+/u", trim( $chu ) ) ?: array() as $khoi ) {
			$dong = array_values( array_filter( array_map( 'trim', explode( "\n", $khoi ) ), 'strlen' ) );
			if ( $dong ) {
				$ra[] = array_map( static fn( string $d ): string => \Normalizer::normalize( $d, \Normalizer::FORM_C ) ?: $d, $dong );
			}
		}
		return $ra;
	}

	/** Hồ sơ nguồn chọn được khi nhập (mọi hồ sơ; vai Biên tập thêm được hồ sơ sách ở Nguồn & cài đặt). @return array<int,string> */
	public static function ho_so_chon(): array {
		$ra = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'any', 'numberposts' => 300, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
			$ra[ (int) $p->ID ] = '' !== trim( $p->post_title ) ? $p->post_title : 'Hồ sơ #' . $p->ID;
		}
		return $ra;
	}

	/**
	 * Nhập các câu vào Kho. Câu y hệt câu đã có trong Kho (cùng nguồn nhập) thì bỏ qua; máy so trùng với tác phẩm và câu
	 * khác như câu máy thu (trùng hẳn thì gắn vào bài có sẵn, có thể trùng thì thành thẻ "Có thể trùng").
	 *
	 * @param string $loai mã loại kho (ca_dao, thanh_ngu_tuc_ngu…)
	 * @return array{ok:bool,them:int,da_co:int,trung:int,ids:int[],loi:string}
	 */
	public static function nhap( string $chu, int $ho_so, string $loai, int $nguoi ): array {
		$ra   = array( 'ok' => false, 'them' => 0, 'da_co' => 0, 'trung' => 0, 'ids' => array(), 'loi' => '' );
		$cau  = self::tach( $chu );
		if ( ! $cau ) {
			$ra['loi'] = 'Chưa có câu nào. Dán danh sách (mỗi câu cách nhau một dòng trống) hoặc chọn tệp chữ.';
			return $ra;
		}
		if ( count( $cau ) > self::TOI_DA ) {
			$ra['loi'] = 'Một lần nhập tối đa ' . number_format_i18n( self::TOI_DA ) . ' câu; tệp có ' . number_format_i18n( count( $cau ) ) . ' câu. Chia nhỏ rồi nhập vài lần.';
			return $ra;
		}
		if ( ! isset( TheLoai::TU_LOAI_GOC[ $loai ] ) ) {
			$ra['loi'] = 'Chọn thể loại cho các câu này.';
			return $ra;
		}
		if ( $ho_so && KhaiBao::HO_SO_NGUON !== get_post_type( $ho_so ) ) {
			$ra['loi'] = 'Hồ sơ nguồn không có.';
			return $ra;
		}
		foreach ( $cau as $dong ) {
			$kq = self::nhap_muc( array( 'loi' => implode( "\n", $dong ), 'loai' => $loai ), $ho_so, $nguoi, hash( 'sha256', $chu ) );
			if ( 'da_co' === $kq['tt'] ) {
				++$ra['da_co'];
				continue;
			}
			if ( in_array( $kq['tt'], array( 'loi', 'xung_dot' ), true ) ) {
				$ra['loi'] = $kq['chu'];
				return $ra;
			}
			$ra['trung'] += 'da_gop' === $kq['tt'] ? 1 : 0;
			++$ra['them'];
			$ra['ids'][] = $kq['id'];
		}
		$ra['ok'] = true;
		return $ra;
	}

	/** Một mục tư liệu bất biến: cùng nguồn/vị trí mới được dùng lại; vân tay lời không là khoá của mọi nguồn. */
	public static function nhap_muc( array $muc, int $ho_so, int $nguoi, string $bam_tep, string $nguoi_lam = NguoiLam::NGUOI_VIET, bool $trong_giao_dich = false ): array {
		global $wpdb;
		$loi = (string) ( $muc['loi'] ?? '' );
		$loai = (string) ( $muc['loai'] ?? '' );
		$url = (string) ( $muc['url'] ?? '' );
		$trang = (string) ( $muc['trang'] ?? '' );
		$ma = (string) ( $muc['ma_muc'] ?? '' );
		$kq = static fn( string $tt, int $id, string $chu ): array => array( 'tt' => $tt, 'id' => $id, 'chu' => $chu );
		if ( ! user_can( $nguoi, 'cadao_soan' ) || '' === trim( $loi ) || ! isset( TheLoai::TU_LOAI_GOC[ $loai ] ) || ( $ho_so && KhaiBao::HO_SO_NGUON !== get_post_type( $ho_so ) ) || ! in_array( $nguoi_lam, array( NguoiLam::NGUOI_VIET, NguoiLam::MAY_CHEP ), true ) ) {
			return $kq( 'loi', 0, 'Kiểm lại quyền, lời, thể loại và hồ sơ nguồn.' );
		}
		if ( ( '' !== $url && ( ! preg_match( '#^https?://#i', $url ) || $url !== esc_url_raw( $url ) ) ) || mb_strlen( $trang ) > 80 || mb_strlen( $ma ) > 120 ) {
			return $kq( 'loi', 0, 'Trang, mã mục hoặc URL chưa hợp lệ.' );
		}
		if ( NguoiLam::MAY_CHEP === $nguoi_lam && '' === $url ) {
			return $kq( 'loi', 0, 'Lời máy trích cần URL trang nguồn do người nhập cung cấp.' );
		}
		$vi_tri = '' !== $ma ? array( 'ma' => $ma ) : array( 'loi' => $loi, 'trang' => $trang, 'url' => $url );
		$key = 'tep-' . substr( hash( 'sha256', wp_json_encode( array( $ho_so, $ho_so ? '' : $bam_tep, $nguoi_lam, $vi_tri ) ) ), 0, 56 );
		if ( false === $wpdb->query( $trong_giao_dich ? 'SAVEPOINT phongdao_nhap_muc' : 'START TRANSACTION' ) ) {
			return $kq( 'loi', 0, 'Chưa mở được lượt nhập. Thử lại sau.' );
		}
		try {
			$cu = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Kho::bang() . ' WHERE nguon_id = %s AND ma_goc = %s', self::NGUON, $key ) );
			// Tương thích mục cũ: chỉ dùng lại nếu lời và hồ sơ thật sự giống, không ghi đè hàng cũ.
			if ( ! $cu && $ho_so && '' === $ma && '' === $trang && '' === $url && NguoiLam::NGUOI_VIET === $nguoi_lam ) {
				$old = 'nhap-' . substr( md5( VanTay::chuan_loi( $loi ) ), 0, 16 );
				$ung = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Kho::bang() . ' WHERE nguon_id = %s AND ma_goc = %s', self::NGUON, $old ) );
				if ( $ung && $loi === $ung->loi && $ho_so === (int) ( Kho::du_lieu( $ung )['ho_so'] ?? 0 ) ) {
					$cu = $ung;
				}
			}
			if ( $cu ) {
				$d = Kho::du_lieu( $cu );
				$giong = $loi === $cu->loi && $loai === $cu->loai && $url === $cu->url && $ho_so === (int) ( $d['ho_so'] ?? 0 ) && $trang === (string) ( $d['trang'] ?? '' );
				$wpdb->query( $trong_giao_dich ? 'ROLLBACK TO SAVEPOINT phongdao_nhap_muc' : 'ROLLBACK' );
				if ( $trong_giao_dich ) { $wpdb->query( 'RELEASE SAVEPOINT phongdao_nhap_muc' ); }
				return $kq( $giong ? 'da_co' : 'xung_dot', (int) $cu->id, $giong ? 'Đã có từ lần nhập trước; giữ nguyên mục cũ.' : 'Mã mục đã có nhưng lời, thể loại hoặc vị trí nguồn đã đổi. Mục cũ được giữ nguyên.' );
			}
			$tt = Kho::luu( self::NGUON, array( 'ma_goc' => $key, 'url' => $url, 'loai' => $loai, 'tieu_de' => mb_substr( (string) strtok( $loi, "\n" ), 0, 190 ), 'loi' => $loi, 'doc_trang' => gmdate( 'Y-m-d' ), 'ho_so' => $ho_so, 'nhap_boi' => $nguoi, 'trang' => $trang, 'ma_muc' => $ma, 'nhap_chi_kho' => true, 'nguoi_lam' => $nguoi_lam ), false );
			$id = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . Kho::bang() . ' WHERE nguon_id = %s AND ma_goc = %s', self::NGUON, $key ) );
			if ( 'moi' !== $tt || ! $id || ! ChongTrung::van_tay_kho( $id ) ) {
				throw new \RuntimeException( 'Chưa ghi được mục và chỉ mục so trùng. Thử lại sau.' );
			}
			$trung = ChongTrung::kiem_kho( $id, true );
			if ( 'loi' === $trung ) {
				throw new \RuntimeException( 'Chưa ghi được quan hệ so trùng. Thử lại sau.' );
			}
			if ( false === $wpdb->query( $trong_giao_dich ? 'RELEASE SAVEPOINT phongdao_nhap_muc' : 'COMMIT' ) ) {
				throw new \RuntimeException( 'Chưa chốt được lượt nhập. Thử lại sau.' );
			}
			return $kq( 'da_gop' === $trung ? 'da_gop' : ( 'cho_quyet' === $trung ? 'nghi_trung' : 'moi' ), $id, 'da_gop' === $trung ? 'Đã có lời khớp; chứng cứ nguồn giữ trong Kho, chưa đổi bài cũ.' : ( 'cho_quyet' === $trung ? 'Đã vào Kho; cần xem đối chiếu.' : 'Đã vào Kho, chưa đăng.' ) );
		} catch ( \Throwable $e ) {
			if ( false === $wpdb->query( $trong_giao_dich ? 'ROLLBACK TO SAVEPOINT phongdao_nhap_muc' : 'ROLLBACK' ) && $trong_giao_dich ) {
				throw new \RuntimeException( 'Giao dịch bị ngắt; dừng lượt nhập để đối soát, không xử lý tiếp các mục khác.' );
			}
			if ( $trong_giao_dich && false === $wpdb->query( 'RELEASE SAVEPOINT phongdao_nhap_muc' ) ) {
				throw new \RuntimeException( 'Chưa khép được lượt ghi mục; dừng lượt nhập để đối soát.' );
			}
			return $kq( 'loi', 0, 'Chưa ghi được mục vào Kho; dữ liệu cũ giữ nguyên. Thử lại sau.' );
		}
	}

	/** Đọc TXT hoặc CSV mẫu cố định, chỉ phân tích, không ghi Kho. Lời CSV giữ cả dòng trống/ngắt khổ. */
	public static function doi_chieu( array $muc ): array {
		global $wpdb;
		$vt = array_map( static fn( array $m ): array => VanTay::cua( (string) $m['loi'] ), $muc );
		if ( ! $vt ) { return array(); }
		$a = array_values( array_unique( array_column( $vt, 'vt1' ) ) );
		$b = array_values( array_unique( array_column( $vt, 'vt2' ) ) );
		$sql = 'SELECT vt1, vt2 FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE vt1 IN (' . implode( ',', array_fill( 0, count( $a ), '%s' ) ) . ') OR vt2 IN (' . implode( ',', array_fill( 0, count( $b ), '%s' ) ) . ') LIMIT 2000';
		$ds = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $a, $b ) ), ARRAY_A ) ?: array();
		$co1 = array_flip( array_column( $ds, 'vt1' ) );
		$co2 = array_flip( array_column( $ds, 'vt2' ) );
		return array_map( static fn( array $v ): string => isset( $co1[ $v['vt1'] ] ) ? 'Có lời khớp trong Kho/thư viện; kiểm lại khi nhập.' : ( isset( $co2[ $v['vt2'] ] ) ? 'Có lời gần khớp nhưng khác dấu; cần đối chiếu.' : 'Sẽ kiểm thêm lời gần giống khi nhập.' ), $vt );
	}

	/** Đọc TXT hoặc CSV mẫu cố định, chỉ phân tích, không ghi Kho. Lời CSV giữ cả dòng trống/ngắt khổ. */
	public static function doc( string $chu, string $kieu, string $loai = '' ): array {
		if ( strlen( $chu ) > 1048576 || ! mb_check_encoding( $chu, 'UTF-8' ) || '' === trim( $chu ) ) {
			return array( 'ok' => false, 'muc' => array(), 'loi' => 'Cần tệp chữ UTF-8, tối đa 1 MB, có lời để nhập.' );
		}
		$chu = (string) preg_replace( '/^\x{FEFF}/u', '', str_replace( array( "\r\n", "\r" ), "\n", $chu ) );
		$muc = array();
		if ( 'txt' === $kieu ) {
			foreach ( self::tach( $chu ) as $dong ) {
				$muc[] = array( 'loi' => implode( "\n", $dong ), 'loai' => $loai, 'ma_muc' => '', 'trang' => '', 'url' => '' );
			}
		} elseif ( 'csv' === $kieu ) {
			$f = fopen( 'php://temp', 'r+' );
			fwrite( $f, $chu );
			rewind( $f );
			$cols = fgetcsv( $f, 0, ',', '"', '' );
			if ( ! is_array( $cols ) || ! in_array( 'loi', $cols, true ) || count( $cols ) !== count( array_unique( $cols ) ) || array_diff( $cols, array( 'ma_muc', 'loi', 'the_loai', 'trang', 'url' ) ) ) {
				fclose( $f );
				return array( 'ok' => false, 'muc' => array(), 'loi' => 'CSV cần cột loi; các cột tuỳ chọn: ma_muc, the_loai, trang, url. Dùng dấu phẩy và mẫu tải ở đây.' );
			}
			while ( false !== ( $row = fgetcsv( $f, 0, ',', '"', '' ) ) ) {
				if ( array( null ) === $row ) { continue; }
				if ( count( $cols ) !== count( $row ) ) {
					fclose( $f );
					return array( 'ok' => false, 'muc' => array(), 'loi' => 'Số cột CSV không khớp. Ô lời nhiều dòng phải nằm trong dấu ngoặc kép.' );
				}
				$r = array_combine( $cols, $row );
				$tl = trim( (string) ( $r['the_loai'] ?? '' ) ) ?: $loai;
				$tl = array_search( $tl, TheLoai::DS, true ) ?: $tl;
				$tl = array_search( $tl, TheLoai::TU_LOAI_GOC, true ) ?: $tl;
				$muc[] = array( 'loi' => \Normalizer::normalize( (string) $r['loi'], \Normalizer::FORM_C ), 'loai' => $tl, 'ma_muc' => trim( (string) ( $r['ma_muc'] ?? '' ) ), 'trang' => trim( (string) ( $r['trang'] ?? '' ) ), 'url' => trim( (string) ( $r['url'] ?? '' ) ) );
				if ( count( $muc ) > self::TOI_DA ) { break; }
			}
			fclose( $f );
		} else {
			return array( 'ok' => false, 'muc' => array(), 'loi' => 'Chọn tệp TXT hoặc CSV.' );
		}
		$ok = count( $muc ) > 0 && count( $muc ) <= self::TOI_DA;
		return array( 'ok' => $ok, 'muc' => $ok ? $muc : array(), 'loi' => $ok ? '' : 'Một lần nhập từ 1 đến 500 mục. Chia nhỏ tệp rồi nhập lại.' );
	}

	/** Form nhập (Bài mới): kiểm quyền, nonce; đọc chữ dán hoặc tệp chữ ≤ 1 MB; về lại Bài mới kèm lời báo. */
	public static function nhan_form(): void {
		\Phongdao\GiaoTiep\QuanTri\NhapKho::nhan_form();
	}
}
