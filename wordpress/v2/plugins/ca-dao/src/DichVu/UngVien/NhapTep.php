<?php

namespace Phongdao\DichVu\UngVien;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
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
		global $wpdb;
		foreach ( $cau as $dong ) {
			$loi    = implode( "\n", $dong );
			$ma_goc = 'nhap-' . substr( md5( VanTay::chuan_loi( $loi ) ), 0, 16 );
			$tt     = Kho::luu(
				self::NGUON,
				array(
					'ma_goc'    => $ma_goc,
					'url'       => '',
					'loai'      => $loai,
					'tieu_de'   => mb_substr( $dong[0], 0, 190 ),
					'loi'       => $loi,
					'doc_trang' => gmdate( 'Y-m-d' ),
					'ho_so'     => $ho_so,
					'nhap_boi'  => $nguoi,
				)
			);
			$id = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . Kho::bang() . ' WHERE nguon_id = %s AND ma_goc = %s', self::NGUON, $ma_goc ) );
			if ( 'moi' !== $tt ) {
				++$ra['da_co'];
				continue;
			}
			$m = Kho::lay( $id );
			if ( $m && 'moi' !== $m->trang_thai ) {
				++$ra['trung']; // máy đã gắn vào bài có sẵn (trùng hẳn)
			}
			++$ra['them'];
			$ra['ids'][] = $id;
		}
		$ra['ok'] = true;
		return $ra;
	}

	/** Form nhập (Bài mới): kiểm quyền, nonce; đọc chữ dán hoặc tệp chữ ≤ 1 MB; về lại Bài mới kèm lời báo. */
	public static function nhan_form(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền nhập câu vào Kho.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::HANH_DONG );
		$chu = (string) wp_unslash( $_POST['chu'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- chữ người gõ, chỉ tách dòng, không hiện ra HTML thô
		$tep = $_FILES['tep'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( is_array( $tep ) && UPLOAD_ERR_OK === (int) ( $tep['error'] ?? UPLOAD_ERR_NO_FILE ) && is_uploaded_file( (string) $tep['tmp_name'] ) ) {
			$noi = (int) $tep['size'] <= MB_IN_BYTES ? (string) file_get_contents( (string) $tep['tmp_name'] ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- tệp tạm vừa tải lên
			if ( '' !== $noi && mb_check_encoding( $noi, 'UTF-8' ) ) {
				$chu = trim( $chu . "\n\n" . $noi );
			} else {
				self::bao( false, 'Tệp phải là tệp chữ UTF-8 (.txt), tối đa 1 MB.' );
			}
		}
		$kq = self::nhap( $chu, absint( $_POST['ho_so'] ?? 0 ), sanitize_key( (string) ( $_POST['loai'] ?? '' ) ), get_current_user_id() );
		self::bao(
			$kq['ok'],
			$kq['ok']
				? 'Đã đưa ' . $kq['them'] . ' câu vào Kho (chưa đăng câu nào)' . ( $kq['da_co'] ? '; ' . $kq['da_co'] . ' câu đã có từ lần nhập trước, bỏ qua' : '' ) . ( $kq['trung'] ? '; ' . $kq['trung'] . ' câu trùng hẳn bài đã có, máy đã gắn vào bài đó' : '' ) . '. Câu máy thấy có thể trùng thành thẻ "Có thể trùng" ở Hộp việc.'
				: $kq['loi']
		);
		wp_safe_redirect( add_query_arg( array( 'page' => \Phongdao\GiaoTiep\QuanTri\DanhSachKho::TRANG, 'nguon' => self::NGUON ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private static function bao( bool $ok, string $chu ): void {
		set_transient( 'phongdao_kho_bao_' . get_current_user_id(), array( 'ok' => $ok, 'chu' => $chu, 'viec' => 0 ), MINUTE_IN_SECONDS );
	}
}
