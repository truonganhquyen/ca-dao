<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Tác phẩm mẫu tổng hợp cho bản xem trước và kiểm thử (blueprint Playground) — chữ mẫu tự đặt, không phải lời,
 * chú thích thật của nguồn (repo công khai, D-050). Lưu qua DichVu\LuuPhan như người biên soạn bấm.
 * Không chạy trên site thật: chỉ blueprint và kiểm thử gọi.
 */
final class GieoMau {

	private const BAI = array(
		'dong-mau-mot-cua-bai' => array(
			'the_loai' => 'ca-dao',
			'tieu_de'  => 'Dòng mẫu một của bài',
			'chu_de'   => array( 'Lao động' ),
			'phan'     => array(
				'loi'       => array( 'dong' => array( 'Dòng mẫu một của bài này', 'Dòng mẫu hai dài hơn chút cho vừa', 'Dòng mẫu ba của bài này', 'Dòng mẫu bốn dài hơn chút cho xong' ), 'nguoi_lam' => NguoiLam::MAY_CHEP ),
				'y_nghia'   => array( 'chu' => 'Một câu giải nghĩa tổng hợp để xem khối Ý nghĩa.', 'ai' => true, 'nguoi_lam' => NguoiLam::AI_DA_DUYET ),
				'chu_thich' => array(
					array( 'tu' => 'chút', 'loi_giai' => 'Chú thích tổng hợp để xem giao diện (từ có trong lời: chạm để xem).', 'nguoi_lam' => NguoiLam::MAY_CHEP ),
					array( 'loi_giai' => 'Chú thích không ghi từ, người biên soạn viết.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				),
				'di_ban'    => array( array( 'dong' => array( 'Dòng mẫu khác của bài này', 'Dòng mẫu hai khác dài hơn cho vừa' ), 'nguoi_lam' => NguoiLam::MAY_CHEP ) ),
			),
		),
		'cau-mau-thu-hai'      => array(
			'the_loai' => 'ca-dao',
			'tieu_de'  => 'Câu mẫu thứ hai',
			'chu_de'   => array( 'Lao động' ),
			'phan'     => array(
				'loi' => array( 'dong' => array( 'Câu mẫu thứ hai sáu chữ', 'Câu tám chữ mẫu viết cho đủ dòng' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
		'tuc-ngu-mau-ngan'     => array(
			'the_loai' => 'tuc-ngu',
			'tieu_de'  => 'Tục ngữ mẫu ngắn',
			'phan'     => array(
				'loi'        => array( 'dong' => array( 'Tục ngữ mẫu ngắn một dòng' ), 'nguoi_lam' => NguoiLam::MAY_CHEP ),
				'y_nghia'    => array( 'chu' => 'Một câu giải nghĩa tổng hợp để xem khối Ý nghĩa.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'nghia_den'  => array( 'chu' => 'Nội dung thử để kiểm thứ bậc các lớp nghĩa.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'nghia_bong' => array( 'chu' => 'Nội dung thử, không phải diễn giải một câu dân gian thật.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'dung_khi'   => array( 'chu' => "Ví dụ mẫu thứ nhất do người biên soạn duyệt.\nVí dụ mẫu thứ hai; mẫu này chỉ kiểm bố cục.", 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
		'dong-dao-mau'         => array(
			'the_loai' => 'dong-dao',
			'tieu_de'  => 'Đồng dao mẫu',
			'phan'     => array(
				'loi' => array( 'dong' => array( 'Đồng dao mẫu một', 'Đồng dao mẫu hai', 'Dòng mẫu thứ ba', 'Dòng mẫu thứ tư' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
		've-mau'               => array(
			'the_loai' => 've',
			'tieu_de'  => 'Vè mẫu',
			'phan'     => array(
				'loi' => array( 'dong' => array( 'Vè mẫu dòng một kể chuyện', 'Vè mẫu dòng hai kể tiếp', 'Vè mẫu dòng ba kể nữa', 'Vè mẫu dòng bốn kết lại' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
		'cau-do-mau'           => array(
			'the_loai' => 'cau-do',
			'tieu_de'  => 'Câu đố mẫu',
			'phan'     => array(
				'loi'    => array( 'dong' => array( 'Câu đố mẫu dòng một', 'Câu đố mẫu dòng hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'goi_y'  => array( 'chu' => 'Gợi ý tổng hợp để kiểm tương tác mở từng lớp.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
				'dap_an' => array( 'chu' => 'Đáp án mẫu', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
			),
		),
	);

	/** @return array<string,int> tên đường dẫn => ID các tác phẩm mẫu (đã có thì giữ) */
	public static function gieo( int $nguoi ): array {
		$ra = array();
		foreach ( self::BAI as $ten => $bai ) {
			$co = get_page_by_path( $ten, OBJECT, KhaiBao::TAC_PHAM );
			if ( $co ) {
				$ra[ $ten ] = $co->ID;
				continue;
			}
			$id = wp_insert_post(
				wp_slash(
					array(
						'post_type'   => KhaiBao::TAC_PHAM,
						'post_status' => 'publish',
						'post_name'   => $ten,
						'post_title'  => $bai['tieu_de'],
						'post_author' => $nguoi,
					)
				)
			);
			if ( ! $id ) {
				continue;
			}
			wp_set_object_terms( $id, $bai['the_loai'], KhaiBao::THE_LOAI );
			wp_set_object_terms( $id, $bai['chu_de'] ?? array(), 'chu_de' );
			foreach ( $bai['phan'] as $phan => $gia_tri ) {
				LuuPhan::luu( $id, $phan, $gia_tri, array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
			}
			$ra[ $ten ] = $id;
		}
		return $ra;
	}
}
