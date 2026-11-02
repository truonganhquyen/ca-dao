<?php

namespace Phongdao\MienDuLieu;

/**
 * "Câu hôm nay" (giữ cách của MVP inc/trang-chu.php): mỗi ngày một bài ngắn (1–4 câu) đang đăng, chọn theo ngày
 * nên ai mở cũng thấy cùng câu; không ngẫu nhiên theo người đọc, không cần lưu gì.
 */
final class CauHomNay {

	/** Bài đủ ngắn để làm Câu hôm nay: 1–4 câu (dòng trống không tính). */
	public static function hop_le( array $dong ): bool {
		$so = count( array_filter( $dong, static fn( $d ): bool => is_string( $d ) && '' !== trim( $d ) ) );
		return $so >= 1 && $so <= 4;
	}

	/**
	 * @param int[]  $ds   ID các bài hợp lệ, xếp tăng dần
	 * @param string $ngay ngày theo giờ của site, dạng NNNN-TT-NN
	 */
	public static function chon( array $ds, string $ngay ): ?int {
		$ds = array_values( $ds );
		return $ds ? (int) $ds[ abs( crc32( $ngay ) ) % count( $ds ) ] : null;
	}
}
