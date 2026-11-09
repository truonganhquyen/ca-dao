<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Chế độ sửa trên trang bài (D-094, docs/28 mục 4.2; màn mẫu B02): dải "Đang sửa", nút Sửa trên từng khối, dòng nguồn
 * gốc của từng phần, khối Lịch sử, các tấm sửa. Chỉ dựng khi người xem có quyền `cadao_soan` — khách không bao giờ nhận
 * các phần này trong HTML.
 */
final class CheDoSua {

	public static function co_quyen(): bool {
		return current_user_can( 'cadao_soan' );
	}

	/** Dữ liệu chế độ sửa của một tác phẩm (null khi người xem không có quyền). */
	public static function ngu_canh( \WP_Post $bai ): ?array {
		if ( ! self::co_quyen() || ! current_user_can( 'edit_post', $bai->ID ) ) {
			return null;
		}
		$ho_so = array();
		$sach  = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) ) as $hs ) {
			$ho_so[ $hs->ID ] = $hs->post_title;
			if ( 'sach' === ( HoSo::truong( $hs->ID )['loai'] ?? '' ) ) {
				$sach[ $hs->ID ] = $hs->post_title;
			}
		}
		$phan = PhanBai::tat_ca( $bai->ID, $bai->post_type );
		return array(
			'id'         => $bai->ID,
			'trang_thai' => $bai->post_status,
			'phan'       => $phan,
			'so_dong'    => count( array_filter( $phan['loi']['dong'] ?? array(), static fn( $d ): bool => '' !== trim( (string) $d ) ) ),
			'ho_so'      => $ho_so,
			'sach'       => $sach,
			'duyet'      => current_user_can( 'cadao_duyet' ),
			'lich_su'    => Viec::lich_su( 'post', $bai->ID, 15 ),
			'nhap'       => Viec::mo( 'post', $bai->ID, 'nhap_y_nghia' ),
		);
	}

	public static function nut( string $tam, string $nhan ): string {
		return '<button type="button" class="nut-sua no-print" data-action="mo-tam" data-tam="' . esc_attr( $tam ) . '">' . Ve::icon( 'edit' ) . '<span>' . esc_html( $nhan ) . '</span></button>';
	}

	/** Dải "Đang sửa": trạng thái, Xem như người đọc, Đăng / Ẩn bài. */
	public static function dai( array $sua ): string {
		$tt   = array( 'publish' => 'Đã đăng', 'draft' => 'Bản nháp', 'private' => 'Đã ẩn', 'pending' => 'Chờ' )[ $sua['trang_thai'] ] ?? $sua['trang_thai'];
		$cuoi = $sua['lich_su'][0]['quyet_luc'] ?? '';
		$nut  = 'publish' === $sua['trang_thai']
			? '<button type="button" class="btn compact ghost" data-action="trang-thai" data-trang-thai="private">Ẩn bài</button>'
			: ( $sua['duyet'] ? '<button type="button" class="btn compact primary" data-action="trang-thai" data-trang-thai="publish">Đăng</button>' : '' );
		return '<div class="dai-bien-tap no-print" role="region" aria-label="Chế độ sửa" data-bai="' . (int) $sua['id'] . '" data-trang-thai="' . esc_attr( $sua['trang_thai'] ) . '"><span class="dbt-trai">' . Ve::icon( 'edit' ) . '<strong>Đang sửa</strong><span class="tag-pill plain">' . esc_html( $tt ) . '</span>'
			. ( '' === $cuoi ? '' : '<span class="small muted">sửa lần cuối ' . esc_html( self::ngay( $cuoi ) ) . '</span>' ) . '</span>'
			. '<span class="dbt-phai"><button type="button" class="btn compact" data-action="xem-nhu-doc-gia" aria-pressed="false">Xem như người đọc</button>' . $nut . '</span></div>';
	}

	/** Dòng nguồn gốc nhỏ dưới một phần (chỉ người biên soạn thấy): ai làm, từ đâu. */
	public static function goc( string $nhan, ?array $gt, array $sua ): string {
		if ( ! $gt ) {
			return '';
		}
		$muc = isset( $gt['muc'] ) ? $gt['muc'] : array( $gt );
		$may = count( array_filter( $muc, static fn( $m ) => NguoiLam::MAY_CHEP === ( $m['nguoi_lam'] ?? '' ) ) );
		$ai  = count( array_filter( $muc, static fn( $m ) => ! empty( $m['ai'] ) ) );
		$hs  = $sua['ho_so'][ (int) ( $muc[0]['nguon']['ho_so'] ?? 0 ) ] ?? '';
		$o   = array();
		if ( $may ) {
			$o[] = ( count( $muc ) > 1 ? $may . ' mục ' : '' ) . 'máy chép' . ( '' === $hs ? '' : ' từ ' . $hs ) . ( 'Lời' === $nhan && ! empty( $sua['phan']['nguon_may']['ngay_lay'] ) ? ', ' . self::ngay( $sua['phan']['nguon_may']['ngay_lay'] ) . ' · chưa sửa tay' : '' );
		}
		if ( count( $muc ) - $may > 0 ) {
			$o[] = ( count( $muc ) > 1 ? ( count( $muc ) - $may ) . ' mục ' : '' ) . 'người viết' . ( $ai ? ', có hỗ trợ AI đã duyệt' : '' );
		}
		return '<p class="goc-nguon no-print">' . Ve::icon( 'info' ) . '<span>' . esc_html( $nhan . ': ' . implode( ' · ', $o ) ) . '</span></p>';
	}

	/** Khối Lịch sử (chỉ người biên soạn thấy). */
	public static function lich_su( array $sua ): string {
		$ten = array( 'loi' => 'Lời', 'y_nghia' => 'Ý nghĩa', 'chu_thich' => 'Từ khó', 'di_ban' => 'Dị bản', 'nghia_den' => 'Nghĩa đen', 'nghia_bong' => 'Nghĩa bóng', 'dung_khi' => 'Dùng khi', 'cau_hoi' => 'Câu hỏi', 'dap_an' => 'Đáp án', 'goi_y' => 'Gợi ý', 'co_nghi' => 'Cờ nghi', 'boi_canh' => 'Bối cảnh', 'doan' => 'Đoạn', 'chu_de' => 'Chủ đề', 'vung' => 'Vùng', 'goi_y' => 'Gợi ý', 'nguon_may' => 'Địa chỉ gốc', 'trang_thai' => 'Trạng thái', 'ten' => 'Tên bài' );
		$viec = array( 'luu' => 'sửa', 'xoa' => 'gỡ', 'khoi_phuc' => 'khôi phục bản trước', 'dang' => 'đăng', 'an' => 'ẩn', 'nhap' => 'về nháp', 'dong_y' => 'đồng ý gợi ý', 'da_co' => 'đã có', 'da_duyet_mvp' => 'đã duyệt trên bản cũ' );
		$o    = '';
		foreach ( $sua['lich_su'] as $r ) {
			$nd = (int) $r['nguoi_quyet'] ? get_userdata( (int) $r['nguoi_quyet'] ) : null;
			$ai = (int) $r['nguoi_quyet'] ? ( $nd ? $nd->display_name : '' ) : 'máy';
			$o .= '<li><strong>' . esc_html( self::ngay( (string) $r['quyet_luc'] ) ) . '</strong> · ' . esc_html( ( $ten[ $r['phan'] ] ?? $r['phan'] ) . ': ' . ( $viec[ $r['quyet'] ] ?? $r['quyet'] ) . ( '' === $ai ? '' : " ($ai)" ) ) . '</li>';
		}
		return '<section class="block tint-dat no-print" id="lich-su"><div class="block-dau"><h2>Lịch sử</h2></div>' . ( '' === $o ? '<p class="small muted">Chưa có lần sửa nào được ghi.</p>' : '<ol class="lich-su">' . $o . '</ol>' ) . '</section>';
	}

	/** Mọi tấm sửa hợp thể loại của bài. */
	public static function tam( array $sua, string $the_loai ): string {
		$o = '';
		foreach ( array_keys( TamSua::TAM ) as $phan ) {
			$rieng = array( 'nghia_den' => 'tuc-ngu', 'nghia_bong' => 'tuc-ngu', 'dung_khi' => 'tuc-ngu', 'cau_hoi' => 'cau-do', 'dap_an' => 'cau-do', 'goi_y' => 'cau-do', 'boi_canh' => 've', 'doan' => 've' )[ $phan ] ?? null;
			if ( null === $rieng || $rieng === $the_loai ) {
				$o .= TamSua::ve( $phan, $sua['phan'][ $phan ] ?? null, $sua );
			}
		}
		return $o;
	}

	/** Giờ UTC trong cơ sở dữ liệu → ngày theo giờ của site (dd/mm). */
	public static function ngay( string $utc ): string {
		$t = strtotime( $utc . ( strlen( $utc ) > 10 ? ' UTC' : '' ) );
		return $t ? wp_date( 'd/m', $t ) : $utc;
	}
}
