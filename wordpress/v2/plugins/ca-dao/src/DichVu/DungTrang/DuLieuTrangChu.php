<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\CauHomNay;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Dữ liệu trang chủ (docs/27 mục 4, bộ thiết kế R01): Câu hôm nay, số bài mỗi thể loại, bài vừa bổ sung, tuyển tập.
 * Khối nào không có dữ liệu thì trả rỗng để trang chủ không hiện khối đó.
 */
final class DuLieuTrangChu implements Module {

	/** Danh sách bài làm được Câu hôm nay (đệm 6 giờ; xoá khi lời hoặc trạng thái bài đổi). */
	private const DEM = 'phongdao_cau_hom_nay_ds';

	public function dang_ky(): void {
		add_action(
			'phongdao_da_luu_phan',
			static function ( int $bai, string $phan ): void {
				if ( 'loi' === $phan ) {
					delete_transient( self::DEM );
				}
			},
			10,
			2
		);
		add_action(
			'transition_post_status',
			static function ( string $moi, string $cu, \WP_Post $bai ): void {
				if ( KhaiBao::TAC_PHAM === $bai->post_type && $moi !== $cu ) {
					delete_transient( self::DEM );
				}
			},
			10,
			3
		);
	}

	/** Dữ liệu trang tác phẩm của Câu hôm nay, hoặc null khi chưa có bài nào hợp. */
	public static function cau_hom_nay(): ?array {
		$ds = get_transient( self::DEM );
		if ( ! is_array( $ds ) ) {
			$ds  = array();
			$ids = get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC' ) );
			update_meta_cache( 'post', $ids );
			foreach ( $ids as $id ) {
				if ( CauHomNay::hop_le( PhanBai::lay( (int) $id, 'loi' )['dong'] ?? array() ) ) {
					$ds[] = (int) $id;
				}
			}
			set_transient( self::DEM, $ds, 6 * HOUR_IN_SECONDS );
		}
		$id  = CauHomNay::chon( $ds, wp_date( 'Y-m-d' ) );
		$bai = $id ? get_post( $id ) : null;
		return $bai && 'publish' === $bai->post_status ? DocBai::tac_pham( $bai ) : null;
	}

	/** @return array<string,int> mã thể loại => số bài đã đăng (thể loại 0 bài không có mặt) */
	public static function so_bai_the_loai(): array {
		$ra = array();
		foreach ( array_keys( TheLoai::DS ) as $ma ) {
			$t = get_term_by( 'slug', $ma, KhaiBao::THE_LOAI );
			if ( $t instanceof \WP_Term && $t->count > 0 ) {
				$ra[ $ma ] = (int) $t->count;
			}
		}
		return $ra;
	}

	/** Thẻ các bài mới đăng gần nhất. */
	public static function vua_bo_sung( int $so = 2 ): array {
		$ra = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'numberposts' => $so * 2, 'orderby' => 'date', 'order' => 'DESC' ) ) as $bai ) {
			$the = DocBai::the( $bai );
			if ( $the && count( $ra ) < $so ) {
				$ra[] = $the;
			}
		}
		return $ra;
	}

	/**
	 * Tuyển tập đã đăng, theo loại (chu_de, dip, tinh_huong, bai_hoc; rỗng = mọi loại).
	 *
	 * @return array<int,array{ten:string,url:string,so:int}>
	 */
	public static function tuyen_tap( string $loai = '', int $so = 6 ): array {
		$ra = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'menu_order title', 'order' => 'ASC' ) ) as $tt ) {
			if ( '' !== $loai && ( PhanBai::lay( $tt->ID, 'thong_tin' )['loai'] ?? '' ) !== $loai ) {
				continue;
			}
			$muc = PhanBai::lay( $tt->ID, 'muc' )['muc'] ?? array();
			if ( $muc && count( $ra ) < $so ) {
				$ra[] = array(
					'ten' => $tt->post_title,
					'url' => (string) get_permalink( $tt ),
					'so'  => count( $muc ),
				);
			}
		}
		return $ra;
	}
}
