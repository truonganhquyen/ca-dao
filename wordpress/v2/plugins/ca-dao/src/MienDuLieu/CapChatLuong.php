<?php

namespace Phongdao\MienDuLieu;

/**
 * Cấp chất lượng nội bộ L0–L3, máy tính, không hiện cho người đọc, không chặn đăng (D-084, docs/26 mục 4.5).
 * Mọi phần nằm trong bài là phần người đã quyết (nháp chỉ ở bảng việc), nên không cần xét trạng thái duyệt.
 * Bài phân tích của người đọc không tính.
 */
final class CapChatLuong {

	/**
	 * @param array<string,?array> $phan các phần của tác phẩm (tên phần => giá trị đã lưu)
	 * @return int|null 0..3; null khi chưa có lời
	 */
	public static function tinh( array $phan, ?string $the_loai ): ?int {
		if ( empty( $phan['loi']['dong'] ) ) {
			return null;
		}
		if ( ! TheLoai::co( $the_loai ) ) {
			return 0;
		}
		// L1 = lời + thể loại; dòng mức kiểm luôn dựng được từ lời (có hoặc chưa đối chiếu)
		$co_nghia = ! empty( $phan['y_nghia'] ) || ( ! empty( $phan['nghia_den'] ) && ! empty( $phan['nghia_bong'] ) );
		if ( ! $co_nghia ) {
			return 1;
		}
		// media: giấy phép ghi ở ảnh / tệp khi tải lên
		$sau = ! empty( $phan['loi']['doi_chieu'] ) || ! empty( $phan['boi_canh'] ) || ! empty( $phan['media'] );
		return $sau ? 3 : 2;
	}
}
