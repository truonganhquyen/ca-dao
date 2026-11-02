<?php

namespace Phongdao\MienDuLieu;

/**
 * Chữ khác của một dị bản so với bản tham chiếu, để gạch dưới khi đặt cạnh nhau (docs/27 mục 6).
 * So từng âm tiết, không phân biệt hoa thường, bỏ dấu câu; không đánh giá bản nào đúng.
 */
final class ToChoKhac {

	/**
	 * @param string[] $chinh lời bản tham chiếu
	 * @param string[] $bien  lời dị bản
	 * @return string[] âm tiết (giữ nguyên cách viết trong dị bản) không có trong bản tham chiếu, mỗi âm tiết một lần
	 */
	public static function tu_khac( array $chinh, array $bien ): array {
		$co = array_flip( self::am_tiet( $chinh ) );
		$ra = array();
		foreach ( $bien as $dong ) {
			foreach ( preg_split( '/\s+/u', trim( BoDau::nfc( $dong ) ), -1, PREG_SPLIT_NO_EMPTY ) ?: array() as $tu ) {
				// Bỏ dấu câu hai đầu bằng biểu thức Unicode (trim() cắt theo byte, làm hỏng chữ có dấu)
				$tu  = (string) preg_replace( '/^[\p{P}\p{S}]+|[\p{P}\p{S}]+$/u', '', $tu );
				$goc = mb_strtolower( $tu );
				if ( '' !== $goc && ! isset( $co[ $goc ] ) && ! in_array( $tu, $ra, true ) ) {
					$ra[] = $tu;
				}
			}
		}
		return $ra;
	}

	/** @return string[] */
	private static function am_tiet( array $dong ): array {
		$ra = array();
		foreach ( $dong as $d ) {
			$chu = mb_strtolower( (string) preg_replace( '/[^\p{L}\p{M}\p{N}\s]/u', ' ', BoDau::nfc( $d ) ) );
			$ra  = array_merge( $ra, preg_split( '/\s+/u', trim( $chu ), -1, PREG_SPLIT_NO_EMPTY ) ?: array() );
		}
		return $ra;
	}
}
