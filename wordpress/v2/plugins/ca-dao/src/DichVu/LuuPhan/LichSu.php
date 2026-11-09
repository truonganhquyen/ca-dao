<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * Khôi phục từ Lịch sử (docs/30 mục 4.4; T-170): đưa một phần của bài (hay một bộ phân loại sửa tay) về bản trước một lần
 * sửa. Chỉ lần sửa mới nhất của phần đó, khi bài còn đúng bản ngay sau lần ấy — có thay đổi mới hơn thì từ chối, không ghi
 * đè. Khôi phục là một lần ghi mới (quyết "khoi_phuc"), đi qua đúng bộ kiểm của lối sửa. Trạng thái bài, quyết thẻ, địa
 * chỉ gốc máy lấy chỉ để xem dấu vết, không khôi phục ở đây.
 */
final class LichSu {

	/**
	 * Lý do không khôi phục được một dòng lịch sử; rỗng = được.
	 *
	 * @param int|null $moi_nhat mã dòng sửa mới nhất của phần đó (màn Lịch sử tính sẵn cho cả trang); null = tự đọc
	 */
	public static function ly_do_khong( array $r, ?int $moi_nhat = null ): string {
		if ( Viec::SUA !== $r['loai'] || Viec::XONG !== $r['trang_thai'] || 'post' !== $r['doi_tuong_loai'] ) {
			return 'Lần này chỉ để xem dấu vết.';
		}
		$phan = (string) $r['phan'];
		$bai  = get_post( (int) $r['doi_tuong_id'] );
		if ( ! $bai || 'trash' === $bai->post_status ) {
			return 'Bài không còn hoặc đang ở thùng rác.';
		}
		$la_phan_loai = array_key_exists( $phan, LuuPhanLoai::SUA_DUOC );
		if ( $la_phan_loai ? ! is_array( $r['truoc']['term_ids'] ?? null ) : ( ! Phan::co( $bai->post_type, $phan ) || 'nguon_may' === $phan ) ) {
			return 'Lần này chỉ để xem dấu vết.';
		}
		if ( ( $moi_nhat ?? Viec::moi_nhat_sua( $bai->ID, $phan ) ) !== (int) $r['id'] ) {
			return 'Đã có lần sửa mới hơn.';
		}
		$con = $la_phan_loai ? LuuPhanLoai::con_nguyen( $r ) : LuuPhan::con_nguyen( PhanBai::lay( $bai->ID, $phan ), $r['sau'] );
		return $con ? '' : 'Đã có thay đổi mới hơn (ngoài lối sửa này).';
	}

	/** @return array{ok:bool,loi:string[],doi:bool,viec:int} */
	public static function khoi_phuc( int $viec_id, int $nguoi, bool $duyet ): array {
		$r     = Viec::lay( $viec_id );
		$ly_do = $r ? self::ly_do_khong( $r ) : 'Không tìm thấy lần sửa này.';
		if ( '' !== $ly_do ) {
			return array(
				'ok'   => false,
				'loi'  => array( $ly_do ),
				'doi'  => false,
				'viec' => 0,
			);
		}
		return array_key_exists( (string) $r['phan'], LuuPhanLoai::SUA_DUOC )
			? LuuPhanLoai::khoi_phuc( $viec_id, $nguoi )
			: LuuPhan::khoi_phuc( $viec_id, array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => $duyet ) );
	}
}
