<?php

namespace Phongdao\MienDuLieu;

/**
 * Hồ sơ nguồn (D-085, docs/26 mục 4.3, docs/28 mục 3.2): loại, giấy phép, cách ghi công, trạng thái xin phép, chính sách
 * hiện. Thiếu thông tin thì để trống ("chưa rõ"), không đoán. Trạng thái xin phép và giấy phép chỉ người sáng lập đổi
 * (kiểm quyền ở trang Nguồn & cài đặt); agent không đổi.
 */
final class HoSoNguon {

	public const LOAI = array(
		'web'       => 'Trang web',
		'sach'      => 'Sách',
		'nguoi_ke'  => 'Người kể',
		'nguoi_doc' => 'Người đọc gửi',
		'wiki'      => 'Wiki (giấy phép mở)',
	);

	public const XIN_PHEP = array(
		'chua'      => 'Chưa xin',
		'da_gui'    => 'Đã gửi thư',
		'dong_y'    => 'Đã đồng ý',
		'tu_choi'   => 'Từ chối',
		'khong_can' => 'Không cần (giấy phép mở / hết bản quyền)',
	);

	/** Chính sách hiện với người đọc (người biên soạn vẫn thấy). */
	public const CHINH_SACH = array(
		'tai_cho' => 'Hiện tại chỗ',
		'tu_lieu' => 'Gom về khối "Lời này từ đâu?"',
		'an'      => 'Ẩn với người đọc',
	);

	/** Trường của hồ sơ (ngoài tên = tiêu đề bài, khoá = tên đường dẫn). */
	public const TRUONG = array( 'loai', 'url', 'tac_gia', 'nha_xuat_ban', 'nam', 'giay_phep', 'ghi_cong', 'xin_phep', 'chinh_sach_hien', 'chep_chu_thich', 'ghi_chu' );

	/** Trường của hồ sơ sách mà vai Biên tập điền được (D-104 ý 5, 6); giấy phép, xin phép, chính sách hiện chỉ Quản trị đổi. */
	public const TRUONG_SACH = array( 'tac_gia', 'nha_xuat_ban', 'nam' );

	/**
	 * Chuẩn hoá: chỉ trường đã biết; lựa chọn ngoài danh sách thành trống; không thêm giá trị mặc định nào.
	 *
	 * @return array<string,string|bool>
	 */
	public static function chuan( array $hs ): array {
		$ra = array();
		foreach ( self::TRUONG as $k ) {
			if ( ! array_key_exists( $k, $hs ) ) {
				continue;
			}
			$v = $hs[ $k ];
			if ( 'chep_chu_thich' === $k ) {
				$ra[ $k ] = (bool) $v;
				continue;
			}
			$v   = trim( (string) $v );
			$hop = array( 'loai' => self::LOAI, 'xin_phep' => self::XIN_PHEP, 'chinh_sach_hien' => self::CHINH_SACH )[ $k ] ?? null;
			if ( null !== $hop && ! isset( $hop[ $v ] ) ) {
				$v = '';
			}
			if ( 'url' === $k && '' !== $v && ! preg_match( '#^https?://#i', $v ) ) {
				$v = '';
			}
			$ra[ $k ] = mb_substr( $v, 0, 500 );
		}
		return $ra;
	}
}
