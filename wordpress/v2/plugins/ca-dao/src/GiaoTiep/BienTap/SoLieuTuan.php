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
 * Số liệu tuần `/bien-tap/so-lieu/` (docs/28 mục 4.6; màn mẫu B05): số bài, theo thể loại, theo cấp L0–L3, việc chờ, kho,
 * kèm "Nên làm tuần này". Lượt đọc, câu tìm không ra, sao chép, chia sẻ thêm khi có phần đo lường. Không định danh người đọc.
 */
final class SoLieuTuan {

	public static function hien(): string {
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
		$viec = count( Viec::ds_cho( 1000 ) );
		$kho  = Kho::dem();
		$o    = '';
		foreach ( array( array( 'book', count( $ids ), 'tác phẩm đã đăng' ), array( 'check', $cap[2] + $cap[3], 'bài đủ cấp L2 (lên Google khi mở)' ), array( 'calendar', $viec, 'việc đang chờ' ), array( 'layers', $kho['moi'], 'câu trong kho chưa xử lý' ) ) as [ $hinh, $so, $nhan ] ) {
			$o .= '<div class="tile g-dip"><span class="entry-icon">' . Ve::icon( $hinh ) . '</span><strong class="so-to">' . number_format( (int) $so, 0, ',', '.' ) . '</strong><span>' . esc_html( $nhan ) . '</span></div>';
		}
		$the_loai = '';
		foreach ( DuLieuTrangChu::so_bai_the_loai() as $ma => $n ) {
			$the_loai .= '<tr><th scope="row">' . esc_html( TheLoai::DS[ $ma ] ) . '</th><td>' . (int) $n . '</td></tr>';
		}
		$bang_cap = '';
		foreach ( array( 'L0 · có lời', 'L1 · có thể loại', 'L2 · có ý nghĩa', 'L3 · có đối chiếu sách / bối cảnh' ) as $i => $nhan ) {
			$bang_cap .= '<tr><th scope="row">' . esc_html( $nhan ) . '</th><td>' . (int) $cap[ $i ] . '</td></tr>';
		}
		return '<p class="eyebrow">Biên tập</p><h1>Số liệu tuần</h1><div class="tiles so-lieu">' . $o . '</div>'
			. '<section class="section"><h2>Nên làm tuần này</h2><ol class="nen-lam">'
			. ( $thieu ? '<li><strong>Viết ý nghĩa cho ' . (int) $thieu . ' bài còn thiếu</strong> — <a href="' . esc_url( home_url( '/bien-tap/tac-pham/?loc=thieu-y-nghia' ) ) . '">mở danh sách</a></li>' : '' )
			. ( $viec ? '<li><strong>' . (int) $viec . ' việc chờ quyết</strong> — <a href="' . esc_url( home_url( '/bien-tap/' ) ) . '">mở Hộp việc</a></li>' : '' )
			. '<li><strong>Thêm câu người đọc cần</strong> — <a href="' . esc_url( home_url( '/bien-tap/them/' ) ) . '">Thêm bài</a></li></ol></section>'
			. '<section class="section"><h2>Theo thể loại</h2><table class="bang-so"><tbody>' . $the_loai . '</tbody></table></section>'
			. '<section class="section"><h2>Theo cấp chất lượng</h2><table class="bang-so"><tbody>' . $bang_cap . '</tbody></table><p class="small muted">Cấp tính tự động, chỉ người biên soạn thấy, không chặn đăng.</p></section>'
			. '<p class="small muted">Lượt đọc, câu tìm không ra, sao chép, chia sẻ sẽ hiện ở đây khi bật đo lường. Số liệu không định danh người đọc: không cookie, không IP.</p>';
	}
}
