<?php

namespace Phongdao\DichVu\XoaTacPham;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\LuuPhan\DauVetTrangThai;

defined( 'ABSPATH' ) || exit;

/** Chỉ xoá tác phẩm trong thùng rác sau xác nhận của quản trị; giữ nhật ký, không xoá tệp ảnh. */
final class XoaTacPham {

	public static function phien( int $id ): string {
		$p = get_post( $id, ARRAY_A );
		return $p ? hash( 'sha256', (string) wp_json_encode( array( $p, get_post_meta( $id ) ) ) ) : '';
	}

	public static function xoa( int $id, string $phien, int $nguoi ): array {
		global $wpdb;
		if ( ! user_can( $nguoi, 'manage_options' ) || ! user_can( $nguoi, 'cadao_duyet' ) || ! user_can( $nguoi, 'delete_post', $id ) ) {
			return array( 'loi', 'Chỉ quản trị có quyền duyệt và xoá bài mới xoá vĩnh viễn được.' );
		}
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) { return array( 'loi', 'Chưa mở được giao dịch xoá.' ); }
		$cu = DauVetTrangThai::$bo_qua;
		$binh_luan = array();
		$con = array();
		try {
			if ( false === $wpdb->query( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE", $id ) ) ) { throw new \RuntimeException( 'Chưa khoá được bài.' ); }
			clean_post_cache( $id );
			$p = get_post( $id );
			if ( ! $p || KhaiBao::TAC_PHAM !== $p->post_type || 'trash' !== $p->post_status || ! hash_equals( $phien, self::phien( $id ) ) ) {
				$wpdb->query( 'ROLLBACK' );
				return array( 'xung_dot', 'Bài đã đổi hoặc được lấy ra khỏi thùng rác; cần xem trước lại.' );
			}
			$nhat_ky = Viec::tao( array( 'loai' => Viec::SUA, 'trang_thai' => Viec::XONG, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $id, 'phan' => 'trang_thai', 'quyet' => 'xoa_vinh_vien', 'nguoi_tao' => (string) $nguoi, 'nguoi_quyet' => $nguoi, 'quyet_luc' => gmdate( 'Y-m-d H:i:s' ), 'truoc' => array( 'trang_thai' => 'trash', 'ten' => $p->post_title ), 'sau' => null ) );
			if ( ! $nhat_ky ) { throw new \RuntimeException( 'Chưa ghi được nhật ký; chưa xoá bài.' ); }
			$binh_luan = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d", $id ) );
			if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa đọc được bình luận của bài.' ); }
			$con = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d", $id ) );
			if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa đọc được mục liên quan của bài.' ); }
			DauVetTrangThai::$bo_qua = true;
			if ( ! wp_delete_post( $id, true ) || get_post( $id ) ) { throw new \RuntimeException( 'WordPress chưa xoá được bài.' ); }
			if ( ! ChongTrung::xoa( 'bai:' . $id ) ) { throw new \RuntimeException( 'Chưa dọn được vân tay của bài.' ); }
			// WordPress có các hook không trả lỗi cho lớp gọi: kiểm lại dữ liệu bắt buộc phải dọn.
			foreach ( array( $wpdb->postmeta => 'post_id', $wpdb->comments => 'comment_post_ID', $wpdb->term_relationships => 'object_id', \Phongdao\DichVu\ChiMucTim\ChiMucTim::bang() => 'post_id', \Phongdao\DichVu\TomTat\TomTat::bang() => 'post_id' ) as $bang => $cot ) {
				$con_sot = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $bang WHERE $cot = %d", $id ) );
				if ( $wpdb->last_error || (int) $con_sot ) { throw new \RuntimeException( 'Chưa dọn hết dữ liệu của bài; đã dừng xoá.' ); }
			}
			if ( ! ChongTrung::dong_cap_het_hieu_luc( 'bai:' . $id, $nguoi, 'Tác phẩm đã bị xoá vĩnh viễn.' ) ) { throw new \RuntimeException( 'Chưa đối soát được cặp chờ trùng.' ); }
			if ( false === $wpdb->query( 'COMMIT' ) ) { throw new \RuntimeException( 'Chưa xác nhận được giao dịch xoá.' ); }
			return array( 'thanh_cong', 'Đã xoá vĩnh viễn bài #' . $id . '; nhật ký được giữ.' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			clean_post_cache( $id );
			foreach ( $con as $ma ) { clean_post_cache( (int) $ma ); }
			clean_comment_cache( $binh_luan );
			return array( 'loi', $e->getMessage() );
		} finally { DauVetTrangThai::$bo_qua = $cu; }
	}
}
