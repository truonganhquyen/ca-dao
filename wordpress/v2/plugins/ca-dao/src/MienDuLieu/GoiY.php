<?php

namespace Phongdao\MienDuLieu;

/**
 * Gợi ý vùng và chủ đề cho một tác phẩm (chuyển nguyên quy tắc của MVP goi-y.php; docs/25 mục 4.2, 4.4; docs/28: thẻ
 * "Gợi ý" trong Hộp việc). PHP thuần. Máy chỉ gợi ý kèm lý do, người bấm Đồng ý mới gắn.
 *
 * - Vùng: từ lời giải của chú thích (ghi phương ngữ, tỉnh, miền) và toạ độ của địa danh đã xác nhận trên Wikidata.
 * - Chủ đề: chủ đề của nguồn qua bảng ánh xạ người sáng lập đã duyệt (chủ đề nguồn chỉ dùng nội bộ, không bao giờ ra
 *   trang); lối hát (hát ru, hò, lý) theo lời; nhóm kinh nghiệm của tục ngữ theo từ khoá.
 */
final class GoiY {

	/** Vùng của dự án (bảy vùng, docs/25 mục 4.2) — cùng tên đường dẫn với MVP. */
	public const VUNG = array(
		'bac-bo'         => 'Bắc Bộ',
		'bac-trung-bo'   => 'Bắc Trung Bộ',
		'trung-trung-bo' => 'Trung Trung Bộ',
		'nam-trung-bo'   => 'Nam Trung Bộ',
		'tay-nguyen'     => 'Tây Nguyên',
		'nam-bo'         => 'Nam Bộ',
		'toan-quoc'      => 'Phổ biến toàn quốc',
	);

	/** Từ khoá trong lời giải của chú thích → vùng. */
	public const VUNG_TU_KHOA = array(
		'nam-bo'         => '/Nam Bộ|miền Nam|Nam Kỳ|Lục tỉnh|Sài Gòn|Gia Định|Đồng Nai|Cần Thơ|Bến Tre|Vĩnh Long|Mỹ Tho|Châu Đốc|Cà Mau|Bạc Liêu|Sóc Trăng|Long An|Tây Ninh|An Giang|Đồng Tháp|Kiên Giang|Rạch Giá|Hậu Giang|Trà Vinh|Biên Hòa|Biên Hoà|Bà Rịa/u',
		'bac-trung-bo'   => '/Nghệ|Hà Tĩnh|Thanh Hóa|Thanh Hoá|xứ Thanh|Quảng Bình|Quảng Trị/u',
		'trung-trung-bo' => '/Huế|Thừa Thiên|Quảng Nam|Đà Nẵng|Quảng Ngãi|Hội An/u',
		'nam-trung-bo'   => '/Bình Định|Phú Yên|Khánh Hòa|Khánh Hoà|Nha Trang|Ninh Thuận|Bình Thuận|Phan Thiết/u',
		'tay-nguyen'     => '/Tây Nguyên|Gia Lai|Kon Tum|Đắk Lắk|Đắc Lắc|Lâm Đồng|Đà Lạt|Buôn Ma Thuột/u',
		'bac-bo'         => '/Bắc Bộ|miền Bắc|Bắc Kỳ|Hà Nội|Thăng Long|Kinh Bắc|Bắc Ninh|Nam Định|Thái Bình|Hải Dương|Hải Phòng|Hưng Yên|Hà Nam|Ninh Bình|Phú Thọ|Sơn Tây/u',
	);

	/** Chủ đề của dự án: nhóm → [tên đường dẫn → tên] — cùng bộ với MVP. */
	public const CHU_DE = array(
		''            => array(
			'gia-dinh'              => 'Gia đình',
			'tinh-yeu-hon-nhan'     => 'Tình yêu, hôn nhân',
			'que-huong-dat-nuoc'    => 'Quê hương, đất nước',
			'lao-dong-san-xuat'     => 'Lao động, sản xuất',
			'thien-nhien-thoi-tiet' => 'Thiên nhiên, thời tiết',
			'ung-xu-dao-duc'        => 'Ứng xử, đạo đức',
			'than-phan-con-nguoi'   => 'Thân phận con người',
			'phe-phan-xa-hoi'       => 'Phê phán xã hội',
			'hai-huoc-cham-biem'    => 'Hài hước, châm biếm',
			'hoc-tap-thay-tro'      => 'Học tập, thầy trò',
			'dia-danh-lich-su'      => 'Địa danh, lịch sử',
		),
		'kinh-nghiem' => array(
			'kn-thoi-tiet' => 'Kinh nghiệm thời tiết',
			'kn-mua-vu'    => 'Kinh nghiệm mùa vụ',
			'kn-chan-nuoi' => 'Kinh nghiệm chăn nuôi',
			'kn-an-uong'   => 'Ăn uống, sức khỏe',
			'kn-doi-nhan'  => 'Đối nhân xử thế',
		),
		'loi-hat'     => array(
			'hat-ru'  => 'Hát ru',
			'ho'      => 'Hò',
			'ly'      => 'Lý',
			'hat-doi' => 'Hát đối',
		),
		'loai-cau-do' => array(
			'do-do-vat'      => 'Đố đồ vật',
			'do-con-vat'     => 'Đố con vật',
			'do-cay-coi'     => 'Đố cây cối, hoa quả',
			'do-thien-nhien' => 'Đố thiên nhiên',
			'do-con-nguoi'   => 'Đố con người, sinh hoạt',
		),
	);

	public const NHOM_CHU_DE = array(
		'kinh-nghiem' => 'Kinh nghiệm (tục ngữ)',
		'loi-hat'     => 'Lối hát',
		'loai-cau-do' => 'Loại câu đố',
	);

	/** Bảng ánh xạ ĐỀ XUẤT chủ đề nguồn (Ca dao Mẹ) → chủ đề dự án; dùng khi người sáng lập chưa lưu bảng riêng. */
	public const ANH_XA_DE_XUAT = array(
		'Tình yêu đôi lứa'             => 'tinh-yeu-hon-nhan',
		'Quê hương đất nước'           => 'que-huong-dat-nuoc',
		'Tình cảm gia đình, bạn bè'    => 'gia-dinh',
		'Than thân trách phận'         => 'than-phan-con-nguoi',
		'Trào phúng, phê phán đả kích' => 'phe-phan-xa-hoi',
		'Đấu tranh, phản kháng'        => 'phe-phan-xa-hoi',
		'Lịch sử'                      => 'dia-danh-lich-su',
		'Lao động sản xuất'            => 'lao-dong-san-xuat',
		'Quan hệ thiên nhiên'          => 'thien-nhien-thoi-tiet',
		'Ru con'                       => 'hat-ru',
		'Đồ vật'                       => 'do-do-vat',
		'Con vật'                      => 'do-con-vat',
		'Cây cối - hoa quả - rau củ'   => 'do-cay-coi',
		'Thiên nhiên'                  => 'do-thien-nhien',
		'Con người và sinh hoạt'       => 'do-con-nguoi',
		'Vũ trụ, con người và xã hội'  => '',
		'Khác'                         => '',
	);

	/** Từ khoá (trong lời) của nhóm kinh nghiệm — chỉ xét với tục ngữ – thành ngữ. Chặn chữ cái hai bên thay \b. */
	public const KINH_NGHIEM_TU_KHOA = array(
		'kn-thoi-tiet' => '/(?<![\p{L}\p{M}])(mưa|nắng|gió|bão|trời|mây|sấm|chớp|cầu vồng|rét|nồm)(?![\p{L}\p{M}])/u',
		'kn-mua-vu'    => '/(?<![\p{L}\p{M}])(lúa|mạ|cấy|gặt|ruộng|mùa|thóc|cày|bừa|phân|giống)(?![\p{L}\p{M}])/u',
		'kn-chan-nuoi' => '/(?<![\p{L}\p{M}])(trâu|bò|lợn|heo|gà|vịt|chó|mèo|ngựa|cá)(?![\p{L}\p{M}])/u',
		'kn-an-uong'   => '/(?<![\p{L}\p{M}])(ăn|uống|cơm|canh|no|đói|bệnh|ốm|thuốc|ngủ)(?![\p{L}\p{M}])/u',
	);

	/** Vùng gần đúng theo toạ độ (chỉ để gợi ý). */
	public static function vung_theo_toa_do( float $lat, float $lon ): string {
		if ( $lat >= 20.3 ) {
			return 'bac-bo';
		}
		if ( $lat >= 16.6 ) {
			return 'bac-trung-bo';
		}
		if ( $lat >= 14.6 ) {
			return 'trung-trung-bo';
		}
		if ( $lat >= 11.4 ) {
			return $lon >= 108.6 ? 'nam-trung-bo' : 'tay-nguyen';
		}
		return $lon >= 107.8 && $lat >= 10.5 ? 'nam-trung-bo' : 'nam-bo';
	}

	/** Nhóm của một chủ đề trong bộ dự án: '' (chủ đề chung), mã nhóm ('loi-hat'…), null = ngoài bộ (D-114). */
	public static function nhom_cua( string $slug ): ?string {
		// Mục cha mang tên nhóm (CaiDat bước 9 tạo "Lối hát", "Loại câu đố", "Kinh nghiệm (tục ngữ)" làm cha của nhóm)
		if ( '' !== $slug && isset( self::CHU_DE[ $slug ] ) ) {
			return $slug;
		}
		foreach ( self::CHU_DE as $nhom => $ds ) {
			if ( isset( $ds[ $slug ] ) ) {
				return (string) $nhom;
			}
		}
		return null;
	}

	/** Tên nhóm để hiện: "Chủ đề chung", "Lối hát"…; null = "Ngoài bộ chủ đề". */
	public static function ten_nhom( ?string $nhom ): string {
		return null === $nhom ? 'Ngoài bộ chủ đề' : ( '' === $nhom ? 'Chủ đề chung' : (string) ( self::NHOM_CHU_DE[ $nhom ] ?? $nhom ) );
	}

	/** Tên hiển thị của một chủ đề ("Lối hát → Hò"). */
	public static function ten_chu_de( string $ma ): string {
		foreach ( self::CHU_DE as $nhom => $ds ) {
			if ( isset( $ds[ $ma ] ) ) {
				return ( '' !== $nhom ? self::NHOM_CHU_DE[ $nhom ] . ' → ' : '' ) . $ds[ $ma ];
			}
		}
		return $ma;
	}

	/**
	 * Các gợi ý cho một bài, bỏ những gì bài đã có hoặc người đã quyết.
	 *
	 * @param array{tieu_de:string,loi:string[],the_loai:string,chu_thich:list<array{tu:string,loi_giai:string,toa_do?:?array}>,chu_de_goc:string[],anh_xa:array<string,string>,da_co:list<string>,da_quyet:list<string>} $v
	 *        da_co, da_quyet: khoá "vung:<mã>" / "chu_de:<mã>"
	 * @return list<array{loai:string,ma:string,ly_do:string}>
	 */
	public static function cho( array $v ): array {
		$ra   = array();
		$them = static function ( string $loai, string $ma, string $ly_do ) use ( &$ra ): void {
			$ra[ "$loai:$ma" ] ??= array( 'loai' => $loai, 'ma' => $ma, 'ly_do' => $ly_do );
		};
		foreach ( $v['chu_thich'] as $ct ) {
			foreach ( self::VUNG_TU_KHOA as $vung => $mau ) {
				if ( preg_match( $mau, (string) $ct['loi_giai'], $m ) ) {
					$them( 'vung', $vung, 'chú thích "' . $ct['tu'] . '" nhắc "' . $m[0] . '"' );
				}
			}
			if ( ! empty( $ct['toa_do'] ) && 2 === count( $ct['toa_do'] ) ) {
				$vung = self::vung_theo_toa_do( (float) $ct['toa_do'][0], (float) $ct['toa_do'][1] );
				$them( 'vung', $vung, 'địa danh "' . $ct['tu'] . '" (đã xác nhận) ở ' . self::VUNG[ $vung ] );
			}
		}
		foreach ( $v['chu_de_goc'] as $cg ) {
			$ma = (string) ( $v['anh_xa'][ $cg ] ?? '' );
			if ( '' !== $ma ) {
				$them( 'chu_de', $ma, 'nguồn xếp vào "' . $cg . '"' );
			}
		}
		$loi = mb_strtolower( implode( "\n", $v['loi'] ) );
		$dau = mb_strtolower( (string) ( $v['loi'][0] ?? '' ) );
		if ( preg_match( '/(^|\s)(à ơi|ầu ơ|ạ ời|ví dầu|ru con|con ơi con ngủ)/u', $loi ) ) {
			$them( 'chu_de', 'hat-ru', 'lời ru ("à ơi", "ầu ơ", "ru con"…)' );
		}
		if ( preg_match( '/(^|\s)hò(\s|,|$)/u', $dau ) ) {
			$them( 'chu_de', 'ho', 'dòng đầu có "hò"' );
		}
		if ( preg_match( '/^lý\s/u', mb_strtolower( $v['tieu_de'] ) ) ) {
			$them( 'chu_de', 'ly', 'tên bài bắt đầu bằng "Lý"' );
		}
		if ( 'tuc-ngu' === $v['the_loai'] ) {
			foreach ( self::KINH_NGHIEM_TU_KHOA as $ma => $mau ) {
				if ( preg_match( $mau, $loi, $m ) ) {
					$them( 'chu_de', $ma, 'lời có "' . $m[0] . '"' );
				}
			}
		}
		return array_values( array_diff_key( $ra, array_flip( array_merge( $v['da_co'], $v['da_quyet'] ) ) ) );
	}
}
