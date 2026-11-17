<?php

namespace Phongdao\MienDuLieu;

/**
 * "Phần riêng" của một tác phẩm (D-117): phần thư viện tự làm thêm ngoài lời lấy về — Ý nghĩa (câu đố: giải thích đáp án),
 * nghĩa tục ngữ, Dùng khi, Hình ảnh trong bài, Bối cảnh, Gợi ý câu đố, chú thích và chuyện quanh bài do người biên soạn viết, dị bản người viết
 * hoặc bạn đọc góp, đối chiếu sách, ảnh hay bản thu người thật, câu SGK. Không tính: lời, đáp án, câu hỏi, và mọi phần máy
 * chép từ nguồn (chú thích, dị bản, nghĩa Wiktionary) — đó vẫn là nội dung của nơi khác. Chỉ bài có phần riêng mới được lập
 * chỉ mục khi mở Google, để thư viện không thành bản sao hàng loạt của nguồn. PHP thuần.
 */
final class PhanRieng {

	/** Phần một giá trị: có chữ (hoặc trường bối cảnh) và không phải máy chép. */
	public const MOT = array( 'y_nghia', 'nghia_den', 'nghia_bong', 'dung_khi', 'hinh_anh', 'boi_canh', 'goi_y' );

	/** Phần danh sách: có ít nhất một mục không phải máy chép (media: ảnh, giọng người — không tính giọng máy). */
	public const DS = array( 'chu_thich', 'chuyen_quanh', 'di_ban', 'media', 'sgk' );

	/** Tên hiện cho người biên soạn. */
	public const TEN = array(
		'y_nghia'   => 'Ý nghĩa',
		'nghia_den' => 'nghĩa đen',
		'nghia_bong' => 'nghĩa bóng',
		'dung_khi'  => 'Dùng khi',
		'hinh_anh'  => 'Hình ảnh trong bài',
		'boi_canh'  => 'Bối cảnh',
		'goi_y'     => 'Gợi ý',
		'chu_thich' => 'chú thích người viết',
		'chuyen_quanh' => 'chuyện quanh bài người viết',
		'di_ban'    => 'dị bản người viết / bạn đọc góp',
		'media'     => 'ảnh, bản thu',
		'sgk'       => 'câu SGK',
		'doi_chieu' => 'đối chiếu sách',
	);

	/** @param array<string,?array> $phan các phần của bài (PhanBai::tat_ca) */
	public static function co( array $phan ): bool {
		return array() !== self::cac( $phan );
	}

	/**
	 * Các phần riêng bài đang có, theo thứ tự TEN.
	 *
	 * @param array<string,?array> $phan
	 * @return list<string> mã phần (thêm 'doi_chieu' khi lời đã đối chiếu sách)
	 */
	public static function cac( array $phan ): array {
		$ra = array();
		foreach ( self::MOT as $p ) {
			$v = $phan[ $p ] ?? null;
			if ( is_array( $v ) && ! self::may( $v ) ) {
				foreach ( array( 'chu', 'su_kien', 'nam', 'noi' ) as $k ) {
					if ( '' !== trim( (string) ( $v[ $k ] ?? '' ) ) ) {
						$ra[] = $p;
						break;
					}
				}
			}
		}
		foreach ( self::DS as $p ) {
			foreach ( (array) ( $phan[ $p ]['muc'] ?? array() ) as $m ) {
				if ( is_array( $m ) && ! self::may( $m ) && ! ( 'media' === $p && 'giong_may' === ( $m['loai'] ?? '' ) ) ) {
					$ra[] = $p;
					break;
				}
			}
		}
		if ( ! empty( $phan['loi']['doi_chieu']['ho_so'] ) ) {
			$ra[] = 'doi_chieu';
		}
		return $ra;
	}

	/** Mục / phần do máy chép từ nguồn. Thiếu người làm (dữ liệu cũ người nhập) thì không coi là máy chép. */
	private static function may( array $v ): bool {
		return NguoiLam::MAY_CHEP === ( $v['nguoi_lam'] ?? '' );
	}
}
