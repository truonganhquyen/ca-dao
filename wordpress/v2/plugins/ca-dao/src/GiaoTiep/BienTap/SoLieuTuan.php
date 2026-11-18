<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\DichVu\TomTat\TomTat;
use Phongdao\KhoLuu\Kho;

defined( 'ABSPATH' ) || exit;

/**
 * Số liệu tuần (màn Số liệu trong wp-admin — D-121, docs/42; trước ở Hôm nay — D-104): số bài, theo cấp L0–L3, kho, lượt
 * đọc 7 ngày, bài đọc nhiều, câu tìm không ra. Không định danh người đọc.
 */
final class SoLieuTuan {

	/**
	 * Số đếm cho màn Số liệu (wp-admin): số bài đã đăng, theo cấp L0–L3, bài thiếu ý nghĩa, câu kho chưa xử lý. Đọc từ bảng
	 * tóm tắt bài (một truy vấn gộp), không đọc phần của từng bài: kho 16.000 bài trước đây mất 80 giây, 32.000 câu SQL.
	 *
	 * @return array{bai:int,cap:array<int,int>,thieu:int,kho:int}
	 */
	public static function so(): array {
		global $wpdb;
		$cap   = array( 0 => 0, 1 => 0, 2 => 0, 3 => 0 );
		$bai   = 0;
		$thieu = 0;
		$hang  = $wpdb->get_results( $wpdb->prepare( 'SELECT cap, COUNT(*) AS so, SUM(CASE WHEN thieu LIKE %s THEN 1 ELSE 0 END) AS thieu FROM ' . TomTat::bang() . ' WHERE trang_thai = %s GROUP BY cap', '%,y_nghia,%', 'publish' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng
		foreach ( $hang ?: array() as $h ) {
			$c      = (int) $h['cap'];
			$bai   += (int) $h['so'];
			$thieu += (int) $h['thieu'];
			if ( isset( $cap[ $c ] ) ) {
				$cap[ $c ] += (int) $h['so'];
			}
		}
		return array( 'bai' => $bai, 'cap' => $cap, 'thieu' => $thieu, 'kho' => (int) ( Kho::dem()['moi'] ?? 0 ) );
	}

	/**
	 * Đọc 7 ngày: tổng theo ngày, bài đọc nhiều, người đọc đến từ, câu tìm không ra (gợi ý biên soạn tiếp).
	 *
	 * @param int $cap cấp tiêu đề của khối (2 ở trang này, 3 khi lồng trong Bàn làm việc)
	 */
	public static function doc_7_ngay( int $cap = 2 ): string {
		$dl   = \Phongdao\DichVu\DoLuong\DoLuong::class;
		$ngay = $dl::theo_ngay( 7 );
		$h1   = 'h' . max( 2, min( 5, $cap ) );
		$h2   = 'h' . max( 3, min( 6, $cap + 1 ) );
		$h    = '<section class="section"><' . $h1 . '>Lượt đọc 7 ngày: ' . number_format( array_sum( $ngay ), 0, ',', '.' ) . '</' . $h1 . '><p class="small muted">'
			. esc_html( implode( ' · ', array_map( static fn( $d, $so ) => wp_date( 'd/m', (int) strtotime( $d ) ) . ': ' . $so, array_keys( $ngay ), $ngay ) ) ) . '</p>';
		$bai  = $dl::doc_nhieu( 7, 5 );
		if ( $bai ) {
			$h .= '<' . $h2 . '>Bài đọc nhiều</' . $h2 . '><ol>' . implode( '', array_map( static fn( $id, $so ) => '<li><a href="' . esc_url( (string) get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a> — ' . (int) $so . '</li>', array_keys( $bai ), $bai ) ) . '</ol>';
		}
		foreach ( array( 'gioi_thieu' => 'Người đọc đến từ', 'tim_khong_ket_qua' => 'Tìm mà chưa có (nên biên soạn)' ) as $loai => $tieu ) {
			$ds = $dl::nhieu_nhat( $loai, 7, 8 );
			if ( $ds ) {
				$h .= '<' . $h2 . '>' . esc_html( $tieu ) . '</' . $h2 . '><ul>' . implode( '', array_map( static fn( $k, $so ) => '<li>' . esc_html( (string) $k ) . ' — ' . (int) $so . '</li>', array_keys( $ds ), $ds ) ) . '</ul>';
			}
		}
		// Câu trong kho người đọc bấm "Mong có câu này" (tên câu kho chỉ người biên soạn thấy ở trang này)
		$mong = $dl::nhieu_nhat( 'mong_co_cau', 7, 8 );
		if ( $mong ) {
			$h .= '<' . $h2 . '>Người đọc mong có (câu đang chờ trong kho)</' . $h2 . '><ul>' . implode( '', array_map( static fn( $k, $so ) => '<li>' . esc_html( (string) ( \Phongdao\KhoLuu\Kho::lay( (int) substr( (string) $k, 4 ) )?->tieu_de ?? $k ) ) . ' — ' . (int) $so . '</li>', array_keys( $mong ), $mong ) ) . '</ul>';
		}
		return $h . '</section>';
	}
}
