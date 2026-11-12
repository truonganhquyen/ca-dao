<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\DichVu\DungTrang\DuLieuTrangChu;
use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\CapChatLuong;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Số liệu tuần (nay hiện ở màn Hôm nay trong wp-admin — D-104; docs/28 mục 4.6; màn mẫu B05): số bài, theo thể loại, theo cấp L0–L3, việc chờ, kho,
 * kèm "Nên làm tuần này". Lượt đọc, câu tìm không ra, sao chép, chia sẻ thêm khi có phần đo lường. Không định danh người đọc.
 */
final class SoLieuTuan {

	/**
	 * Số đếm của trang (dùng cả ở Bàn làm việc wp-admin): số bài đã đăng, theo cấp L0–L3, bài thiếu ý nghĩa, câu kho chưa
	 * xử lý.
	 *
	 * @return array{bai:int,cap:array<int,int>,thieu:int,kho:int}
	 */
	public static function so(): array {
		$ids = get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
		update_meta_cache( 'post', $ids );
		$cap    = array( 0 => 0, 1 => 0, 2 => 0, 3 => 0 );
		$thieu  = 0;
		foreach ( $ids as $id ) {
			$bai = get_post( $id );
			$c   = CapChatLuong::tinh( PhanBai::tat_ca( (int) $id, KhaiBao::TAC_PHAM ), DocBai::the_loai( $bai ) );
			if ( null !== $c ) {
				++$cap[ $c ];
			}
			$thieu += null === PhanBai::lay( (int) $id, 'y_nghia' ) ? 1 : 0;
		}
		return array( 'bai' => count( $ids ), 'cap' => $cap, 'thieu' => $thieu, 'kho' => (int) ( Kho::dem()['moi'] ?? 0 ) );
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
