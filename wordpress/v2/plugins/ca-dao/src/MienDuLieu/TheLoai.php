<?php

namespace Phongdao\MienDuLieu;

/**
 * Danh sách thể loại cố định trong mã (docs/26 mục 4.2). Thể loại mới chỉ thêm khi qua cổng (D-091):
 * truyen, hat-ru, ho, ly để sau. Tên đường dẫn của thể loại cũng là tiền tố đường dẫn tác phẩm.
 */
final class TheLoai {

	public const DS = array(
		'ca-dao'   => 'Ca dao',
		'tuc-ngu'  => 'Tục ngữ – Thành ngữ',
		'dong-dao' => 'Đồng dao',
		've'       => 'Vè',
		'cau-do'   => 'Câu đố',
	);

	/** Loại trên nguồn (kho) → thể loại của thư viện; thành ngữ – tục ngữ gộp một mục (D-079). */
	public const TU_LOAI_GOC = array(
		'ca_dao'            => 'ca-dao',
		'thanh_ngu_tuc_ngu' => 'tuc-ngu',
		'dong_dao'          => 'dong-dao',
		've'                => 've',
		'cau_do'            => 'cau-do',
	);

	public static function co( ?string $ma ): bool {
		return null !== $ma && isset( self::DS[ $ma ] );
	}
}
