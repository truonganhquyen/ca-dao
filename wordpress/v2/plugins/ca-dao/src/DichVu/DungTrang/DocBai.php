<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\Phan;
use Phongdao\MienDuLieu\TheTho;

defined( 'ABSPATH' ) || exit;

/**
 * Lấy dữ liệu một tác phẩm từ WordPress (các phần, phân loại, tên hồ sơ nguồn, tuyển tập chứa bài) rồi giao
 * cho TrangTacPham dựng. Không đọc phần nguon_may (nội bộ).
 */
final class DocBai {

	/** @return array<string,mixed>|null dữ liệu cho GiaoTiep\Khoi\TacPham; null khi không phải tác phẩm có lời */
	public static function tac_pham( \WP_Post $bai ): ?array {
		if ( KhaiBao::TAC_PHAM !== $bai->post_type ) {
			return null;
		}
		$phan = self::phan( $bai );
		$ho_so = array();
		foreach ( array_merge( array( $phan['loi']['doi_chieu']['ho_so'] ?? 0 ), array_map( static fn( $c ) => $c['nguon']['ho_so'] ?? 0, $phan['chu_thich']['muc'] ?? array() ) ) as $id ) {
			$hs = $id ? get_post( (int) $id ) : null;
			if ( $hs && KhaiBao::HO_SO_NGUON === $hs->post_type && 'publish' === $hs->post_status ) {
				$ho_so[ $hs->ID ] = $hs->post_title;
			}
		}
		$hinh_thuc = self::term( $bai, 'hinh_thuc' );
		$tp = TrangTacPham::tu_phan(
			$phan,
			array(
				'id'        => $bai->ID,
				'tieu_de'   => $bai->post_title,
				'url'       => (string) get_permalink( $bai ),
				'the_loai'  => self::the_loai( $bai ),
				'the_tho'   => self::the_tho( $hinh_thuc ),
				'hinh_thuc' => implode( ' · ', array_column( $hinh_thuc, 0 ) ),
				'chu_de'    => self::term( $bai, 'chu_de' ),
				'vung'      => self::term( $bai, 'vung' ),
				'tuyen_tap' => self::tuyen_tap( $bai->ID ),
				'so_lieu'   => \Phongdao\DichVu\DoLuong\DoLuong::so_lieu( $bai->ID ),
				'ho_so'     => $ho_so,
			)
		);
		if ( ! $tp ) {
			return null;
		}
		// Từ khó nối mục từ: lối sang Từ điển (số bài), ảnh, bản đồ của địa danh đã xác nhận; đáp án câu đố có ảnh
		foreach ( $tp['chu_thich'] as $i => $c ) {
			$t = $c['muc'] ? get_term( $c['muc'], 'muc_tu' ) : null;
			if ( $t instanceof \WP_Term ) {
				$tp['chu_thich'][ $i ]['muc'] = array( 'url' => (string) get_term_link( $t ), 'so_bai' => (int) $t->count, 'them' => \Phongdao\GiaoTiep\Khoi\DiaDanh::html( \Phongdao\GiaoTiep\Khoi\DiaDanh::cua_muc_tu( (int) $t->term_id ) ?? array(), 'thumbnail' ) );
			} else {
				$tp['chu_thich'][ $i ]['muc'] = null;
			}
		}
		$tp['dap_an_them'] = '' === $tp['dap_an'] ? '' : \Phongdao\GiaoTiep\Khoi\DiaDanh::html( \Phongdao\GiaoTiep\Khoi\DiaDanh::cua_dap_an( $tp['dap_an'] ) ?? array(), 'thumbnail' );
		return array( 'cam_nghi' => self::cam_nghi( $bai->ID ), 'cach_hieu' => self::cam_nghi( $bai->ID, 'cach_hieu' ) ) + $tp;
	}

	/**
	 * Dữ liệu nhẹ cho ảnh chia sẻ (DichVu\AnhChiaSe): chỉ lời, ý nghĩa, cờ nghi, thể loại, thể thơ — không đọc cảm nghĩ, số liệu,
	 * tuyển tập. @return array<string,mixed>|null như tac_pham()
	 */
	public static function cho_anh( \WP_Post $bai ): ?array {
		if ( KhaiBao::TAC_PHAM !== $bai->post_type ) {
			return null;
		}
		return TrangTacPham::tu_phan(
			array(
				'loi'     => PhanBai::lay( $bai->ID, 'loi' ),
				'y_nghia' => PhanBai::lay( $bai->ID, 'y_nghia' ),
				'co_nghi' => PhanBai::lay( $bai->ID, 'co_nghi' ),
			),
			array(
				'id'       => $bai->ID,
				'tieu_de'  => $bai->post_title,
				'url'      => (string) get_permalink( $bai ),
				'the_loai' => self::the_loai( $bai ),
				'the_tho'  => self::the_tho( self::term( $bai, 'hinh_thuc' ) ),
			)
		);
	}

	/** Thể thơ trong các hình thức của bài ('' = không rõ). */
	private static function the_tho( array $hinh_thuc ): string {
		$ra = '';
		foreach ( $hinh_thuc as $t ) {
			if ( isset( TheTho::DS[ $t['ma'] ] ) ) {
				$ra = $t['ma'];
			}
		}
		return $ra;
	}

	/**
	 * Cảm nghĩ (hoặc cách hiểu của bạn đọc, `$loai = 'cach_hieu'`) đã được người bấm Hiện, cũ trước.
	 *
	 * @return list<array{id:int,ten:string,chu:string,ngay:string}>
	 */
	public static function cam_nghi( int $id, string $loai = 'comment' ): array {
		return array_map(
			static fn( \WP_Comment $c ): array => array(
				'id'   => (int) $c->comment_ID,
				'ten'  => (string) $c->comment_author ?: 'Một bạn đọc',
				'chu'  => (string) $c->comment_content,
				'ngay' => (string) mysql2date( 'd/m/Y', $c->comment_date ),
			),
			get_comments( array( 'post_id' => $id, 'status' => 'approve', 'type' => $loai, 'order' => 'ASC', 'number' => 50 ) ) ?: array()
		);
	}

	/** Thẻ bài trong danh sách. */
	public static function the( \WP_Post $bai ): ?array {
		return TrangTacPham::the(
			self::phan( $bai ),
			array(
				'id'       => $bai->ID,
				'tieu_de'  => $bai->post_title,
				'url'      => (string) get_permalink( $bai ),
				'the_loai' => self::the_loai( $bai ),
			)
		);
	}

	public static function the_loai( \WP_Post $bai ): ?string {
		$t = get_the_terms( $bai, KhaiBao::THE_LOAI );
		return is_array( $t ) && $t ? $t[0]->slug : null;
	}

	/** Các phần hiển thị (bỏ nguon_may ngay từ đây). */
	private static function phan( \WP_Post $bai ): array {
		$ra = array();
		foreach ( Phan::ds( $bai->post_type ) as $p ) {
			if ( 'nguon_may' !== $p ) {
				$ra[ $p ] = PhanBai::lay( $bai->ID, $p );
			}
		}
		return $ra;
	}

	/** @return array<int,array{0:string,1:string,ma:string}> [tên, đường dẫn] mỗi mục phân loại */
	private static function term( \WP_Post $bai, string $phan_loai ): array {
		$ra = array();
		foreach ( get_the_terms( $bai, $phan_loai ) ?: array() as $t ) {
			if ( $t instanceof \WP_Term ) {
				$url  = get_term_link( $t );
				$ra[] = array( $t->name, is_string( $url ) ? $url : '', 'ma' => $t->slug );
			}
		}
		return $ra;
	}

	/** Tuyển tập đã đăng có bài này (vài chục tuyển tập, đọc thẳng meta). */
	private static function tuyen_tap( int $id ): array {
		$ra = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ) as $tt ) {
			$muc = PhanBai::lay( $tt->ID, 'muc' )['muc'] ?? array();
			if ( in_array( $id, array_map( 'intval', array_column( $muc, 'tac_pham_id' ) ), true ) ) {
				$ra[] = array( $tt->post_title, (string) get_permalink( $tt ) );
			}
		}
		return $ra;
	}
}
