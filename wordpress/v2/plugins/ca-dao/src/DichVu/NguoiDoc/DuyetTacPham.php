<?php

namespace Phongdao\DichVu\NguoiDoc;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\VanTay;

defined( 'ABSPATH' ) || exit;

/** Nhận quyết định của người duyệt, trong giao dịch thẻ; không gọi từ đường khách. */
final class DuyetTacPham {
	private static array $da_cham = array();

	public static function loi( int $id ): string {
		$d = get_post_meta( $id, '_phongdao_gop', true );
		return is_array( $d ) ? (string) ( $d['noi_dung'] ?? '' ) : '';
	}

	/** Cache ngoài giao dịch SQL phải được xoá cả khi huỷ, kể cả bài vừa tạo. */
	public static function don(): void {
		foreach ( self::$da_cham as $id ) { clean_post_cache( $id ); clean_object_term_cache( $id, KhaiBao::TAC_PHAM ); }
		self::$da_cham = array();
	}

	public static function la( array $v ): bool {
		return 'nguoi_doc' === $v['loai'] && 'tac_pham' === ( $v['noi_dung']['loai'] ?? '' );
	}

	public static function ban( int $gop, int $bai = 0 ): string {
		$p = get_post( $gop );
		return hash( 'sha256', wp_json_encode( array( $p ? array( $p->post_content, $p->post_status, get_post_meta( $gop, '_phongdao_gop', true ) ) : null, $bai ? array( get_post_status( $bai ), array_filter( get_post_meta( $bai ), static fn( $k ) => str_starts_with( $k, '_phongdao_' ), ARRAY_FILTER_USE_KEY ) ) : null ) ) );
	}

	public static function quyet( array $v, string $lam, int $nguoi, array $f ): array {
		global $wpdb;
		$hong = static fn( string $s ) => array( 'ok' => false, 'loi' => array( $s ), 'thong_bao' => '', 'chuyen' => '' );
		if ( ! user_can( $nguoi, 'cadao_duyet' ) ) { return $hong( 'Cần quyền duyệt tác phẩm bạn đọc góp.' ); }
		$id = (int) $v['doi_tuong_id']; $bai = absint( $f['bai_dich'] ?? 0 ); $moi = 'gop_dang' === $lam;
		if ( ! in_array( $lam, array( 'gop_dang', 'gop_di_ban', 'gop_nguon' ), true ) ) { return $hong( 'Chọn cách sử dụng đóng góp.' ); }
		// Cùng khoá bài ở mọi đường duyệt; tạo mới lấy khoá bảng chỉ mục bằng một hàng option chung.
		$khoa = $wpdb->get_var( "SELECT option_id FROM {$wpdb->options} WHERE option_name='siteurl' FOR UPDATE" );
		if ( ! $khoa || ( ! $moi && ( ! $bai || ! LuuPhan::khoa_bai( $bai ) ) ) ) { return $hong( 'Chưa khoá được bài để đối chiếu.' ); }
		clean_post_cache( $id );
		$p = get_post( $id );
		if ( ! $p || KhaiBao::GOP_Y !== $p->post_type || 'pending' !== $p->post_status || ! hash_equals( self::ban( $id, $moi ? 0 : $bai ), (string) ( $f['ban_gop'] ?? '' ) ) ) { return $hong( 'Đóng góp hoặc bài đích đã đổi. Xem lại trước khi quyết.' ); }
		$d = get_post_meta( $id, '_phongdao_gop', true );
		if ( ! is_array( $d ) ) { return $hong( 'Thiếu bản ghi tư liệu của đóng góp.' ); }
		$loi = self::loi( $id );
		if ( '' === $loi ) { return $hong( 'Thiếu lời đóng góp.' ); }
		$dong = explode( "\n", $loi );
		$vt = VanTay::cua( $loi );
		$nc = array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
		if ( $moi ) {
			$tl = (string) ( $f['gop_the_loai'] ?? '' );
			if ( ! TheLoai::co( $tl ) ) { return $hong( 'Chọn thể loại trước khi đăng.' ); }
			foreach ( SoTrung::tim( $loi, false, $id ) as $r ) {
				if ( $r['khop'] && $r['bai'] ) { return $hong( 'Lời này đã có trong tác phẩm. Chọn bài đó để bổ sung nguồn; không tạo thêm tác phẩm y hệt.' ); }
			}
			$bai = (int) wp_insert_post( wp_slash( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => mb_substr( $dong[0], 0, 190 ), 'post_author' => $nguoi ) ) );
			self::$da_cham[] = $bai;
			if ( ! $bai || is_wp_error( wp_set_object_terms( $bai, $tl, 'the_loai' ) ) ) { return $hong( 'Chưa tạo được bài và thể loại.' ); }
			if ( ! LuuPhan::luu( $bai, 'loi', array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::DOC_GIA_GOP ), $nc )['ok'] ) { return $hong( 'Chưa ghi được lời; chưa đăng đóng góp.' ); }
			$phan = 'loi';
		} else {
			self::$da_cham[] = $bai;
			if ( KhaiBao::TAC_PHAM !== get_post_type( $bai ) || ! in_array( get_post_status( $bai ), array( 'publish', 'draft' ), true ) ) { return $hong( 'Bài đích không còn ở trạng thái có thể bổ sung.' ); }
			$ds = PhanBai::lay( $bai, 'di_ban' )['muc'] ?? array();
			$phan = '';
			if ( VanTay::cua( implode( "\n", PhanBai::lay( $bai, 'loi' )['dong'] ?? array() ) )['vt1'] === $vt['vt1'] ) { $phan = 'loi'; }
			foreach ( $ds as $r ) { if ( VanTay::cua( implode( "\n", $r['dong'] ) )['vt1'] === $vt['vt1'] ) { $phan = 'di_ban'; } }
			if ( 'gop_di_ban' === $lam ) {
				if ( $phan ) { return $hong( 'Lời đã có trong bài hoặc dị bản. Dùng Bổ sung nguồn thay vì thêm dị bản y hệt.' ); }
				$ds[] = array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::DOC_GIA_GOP );
				if ( ! LuuPhan::luu( $bai, 'di_ban', $ds, $nc )['ok'] ) { return $hong( 'Chưa ghi được dị bản.' ); }
				$phan = 'di_ban';
			} elseif ( ! $phan ) { return $hong( 'Lời đóng góp chưa khớp lời chính hoặc dị bản của bài đích; đối chiếu lại trước khi bổ sung nguồn.' ); }
		}
		$ten = ! empty( $d['hien_ten'] ) && ! empty( $f['gop_hien_ten'] ) ? (string) ( $d['ten'] ?? '' ) : '';
		$xuat_xu = sanitize_textarea_field( (string) ( $f['gop_nguon'] ?? $d['xuat_xu'] ?? '' ) );
		if ( 'gop_nguon' === $lam && '' === trim( $xuat_xu ) ) { return $hong( 'Bổ sung nguồn cần có thông tin nguồn. Lời đã có mà chưa có tư liệu thêm thì bạn có thể Để sau hoặc Bỏ; thẻ và bản gửi vẫn được giữ.' ); }
		$credits = PhanBai::lay( $bai, 'dong_gop' )['muc'] ?? array();
		$credits[] = array( 'gop_id' => $id, 'phan' => $phan, 'dau_loi' => $vt['vt1'], 'ten' => $ten, 'xuat_xu' => mb_substr( $xuat_xu, 0, 2000 ) );
		if ( ! LuuPhan::luu( $bai, 'dong_gop', $credits, $nc )['ok'] ) { return $hong( 'Chưa ghi được nguồn và ghi công.' ); }
		if ( $moi ) {
			$goiy = (array) ( $f['gop_pl'] ?? array() );
			foreach ( array( 'chu_de', 'vung' ) as $tax ) {
				$ids = array(); foreach ( $goiy as $key ) { if ( preg_match( '/^' . $tax . ':(\d+)$/', (string) $key, $m ) && term_exists( (int) $m[1], $tax ) ) { $ids[] = (int) $m[1]; } }
				if ( $ids && ! LuuPhanLoai::luu( $bai, $tax, $ids, array(), $nguoi, 'luu', $v['id'], true )['ok'] ) { return $hong( 'Chưa ghi được phân loại.' ); }
			}
			$r = wp_update_post( array( 'ID' => $bai, 'post_status' => 'publish' ), true ); clean_post_cache( $bai );
			if ( is_wp_error( $r ) || 'publish' !== get_post_status( $bai ) ) { return $hong( 'Chưa chuyển được bài sang Đăng.' ); }
		}
		if ( ! ChongTrung::van_tay_bai( $bai ) ) { return $hong( 'Chưa ghi được chỉ mục kiểm trùng; chưa chốt đóng góp.' ); }
		$r = wp_update_post( array( 'ID' => $id, 'post_status' => 'private' ), true ); clean_post_cache( $id );
		$ket = $wpdb->update( Viec::bang(), array( 'sau' => wp_json_encode( array( 'bai' => $bai, 'phan' => $phan ) ) ), array( 'id' => $v['id'] ) );
		if ( is_wp_error( $r ) || 'private' !== get_post_status( $id ) || false === $ket || ! ChongTrung::xoa( 'gop:' . $id ) || ! Viec::dong( $v['id'], Viec::XONG, $lam, $nguoi, $v['trang_thai'] ) ) { return $hong( 'Chưa chốt được kết quả; đóng góp vẫn cần đối soát.' ); }
		return array( 'ok' => true, 'loi' => array(), 'thong_bao' => ( $moi ? 'Đã đăng tác phẩm' : ( 'gop_di_ban' === $lam ? 'Đã thêm dị bản' : 'Đã bổ sung nguồn' ) ) . ' từ đóng góp vào bài #' . $bai . '.' . ( 'draft' === get_post_status( $bai ) ? ' Bài đích đang ở Bản nháp, chưa công khai.' : '' ), 'chuyen' => '' );
	}

}
