<?php

namespace Phongdao\MienDuLieu;

/**
 * Ai làm ra một phần của bài (docs/26 mục 4.3). Lời, dị bản không bao giờ nhận người làm là AI.
 */
final class NguoiLam {

	/** Máy chép từ nguồn bật "Chép chú thích" (D-055), khi người bấm Đăng. */
	public const MAY_CHEP = 'may_chep';

	public const NGUOI_VIET = 'nguoi_viet';

	/** Nháp AI người có quyền đã đọc và bấm duyệt. */
	public const AI_DA_DUYET = 'ai_da_duyet';

	/** Người đọc gửi, người biên soạn bấm áp dụng. */
	public const DOC_GIA_GOP = 'doc_gia_gop';

	public const DS = array( self::MAY_CHEP, self::NGUOI_VIET, self::AI_DA_DUYET, self::DOC_GIA_GOP );

	public static function la_ai( string $nguoi_lam ): bool {
		return str_starts_with( $nguoi_lam, 'ai_' );
	}
}
