<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\BanChu;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * Lưu một phần của bài — đường ghi duy nhất (D-094, docs/26 mục 4.3): kiểm lằn ranh (MienDuLieu\KiemPhan),
 * ghi một dòng nhật ký vào bảng việc, ghi post meta, cập nhật bản chữ. Hỏng giữa chừng thì trả lại như cũ.
 *
 * Ngữ cảnh $nc: nguoi (ID người dùng; 0 khi máy), goi (KiemPhan::GOI_*), duyet (người có quyền duyệt bấm),
 * luc (giờ UTC 'Y-m-d H:i:s', tuỳ chọn — bước nhập dữ liệu cũ giữ giờ của MVP).
 * Quyền đăng nhập, nonce kiểm ở lớp REST (docs/26 mục 5); lớp này chỉ nhận ngữ cảnh đã kiểm.
 */
final class LuuPhan {

	/** Dấu của chính bản đã dựng form, kèm lần sửa để phân biệt cả khôi phục về cùng chữ. */
	public static function phien( int $bai, string $phan, ?array $gia_tri ): string {
		return hash( 'sha256', wp_json_encode( array( $bai, $phan, $gia_tri, Viec::moi_nhat_sua( $bai, $phan ) ) ) );
	}

	/** Caller đã mở giao dịch: lấy cùng khóa trước mọi lần đọc nhất quán của phần/bản chữ. Không mở giao dịch lồng. */
	public static function khoa_bai( int $bai ): bool {
		global $wpdb;
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE", $bai ) );
		if ( null === $id || (int) $id !== $bai ) {
			return false;
		}
		clean_post_cache( $bai );
		return true;
	}

	/** Entry point lưu trực tiếp: khóa bài, đọc mới, so phiên rồi mới đọc form/ghi; không mở giao dịch lồng. */
	public static function trong_phien( int $bai, string $phan, string $phien, callable $ghi ): array {
		global $wpdb;
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return self::hong( 'Chưa mở được lượt lưu. Phần đang nhập chưa được ghi; thử lại sau.' );
		}
		try {
			if ( ! self::khoa_bai( $bai ) ) {
				throw new \RuntimeException( 'khong_co_bai' );
			}
			$cu = PhanBai::lay( $bai, $phan );
			$moi = self::phien( $bai, $phan, $cu );
			if ( ! hash_equals( $moi, $phien ) ) {
				$wpdb->query( 'ROLLBACK' );
				return self::hong( 'Phần này đã đổi hoặc phiên mở bài không còn hợp lệ. Đối chiếu bản đang lưu trước khi lưu tiếp; chữ đang nhập vẫn được giữ.' )
					+ array( 'xung_dot' => true, 'phien_moi' => $moi, 'hien_tai' => $cu );
			}
			$kq = $ghi( $cu );
			if ( empty( $kq['ok'] ) ) {
				$wpdb->query( 'ROLLBACK' );
				clean_post_cache( $bai );
				return $kq;
			}
			if ( false === $wpdb->query( 'COMMIT' ) ) {
				throw new \RuntimeException( 'commit' );
			}
			return $kq;
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			clean_post_cache( $bai );
			return self::hong( 'Chưa lưu được phần bài và lịch sử. Chữ đang nhập chưa được xác nhận; thử lại sau.' );
		}
	}

	/**
	 * @param array{nguoi:int,goi:string,duyet?:bool,luc?:string} $nc
	 * @return array{ok:bool,loi:string[],doi:bool,viec:int}
	 */
	public static function luu( int $bai_id, string $phan, mixed $gia_tri, array $nc ): array {
		$nc  = self::nc( $nc );
		$bai = get_post( $bai_id );
		if ( ! $bai || ! Phan::co( $bai->post_type, $phan ) ) {
			return self::hong( "Không có phần “{$phan}” cho bài này." );
		}
		[ $sach, $loi ] = KiemPhan::kiem( $phan, $gia_tri, self::ngu_canh_kiem( $bai, $nc ) );
		if ( $loi ) {
			return self::hong( ...$loi );
		}
		if ( 'media' === $phan && ( $loi_anh = self::kiem_anh( $sach ) ) ) {
			return self::hong( $loi_anh );
		}
		$truoc = PhanBai::lay( $bai->ID, $phan );
		if ( KiemPhan::noi_dung( $truoc ) === $sach ) {
			return self::xong( false, 0 );
		}
		$luc = self::luc( $nc );
		if ( null !== $sach ) {
			$sach['sua_boi'] = $nc['nguoi'];
			$sach['sua_luc'] = $luc;
			if ( ! empty( $sach['ai'] ) ) {
				$sach['duyet_boi'] = $nc['nguoi'];
				$sach['duyet_luc'] = $luc;
			}
		}
		return self::ghi( $bai, $phan, $truoc, $sach, $nc, null === $sach ? 'xoa' : 'luu', $luc );
	}

	/**
	 * Đặt lại giá trị "trước" của một lần sửa (Hoàn tác 10 giây; khôi phục bản cũ từ Lịch sử).
	 * Giá trị cũ vẫn phải qua bộ kiểm với quyền của người đang bấm (vd. bản cũ có hỗ trợ AI cần quyền duyệt).
	 *
	 * @return array{ok:bool,loi:string[],doi:bool,viec:int}
	 */
	public static function khoi_phuc( int $viec_id, array $nc ): array {
		if ( ! empty( $nc['trong_giao_dich'] ) ) {
			return self::khoi_phuc_da_khoa( $viec_id, $nc );
		}
		$v = Viec::lay( $viec_id );
		if ( ! $v || Viec::SUA !== $v['loai'] || 'post' !== $v['doi_tuong_loai'] ) {
			return self::hong( 'Không tìm thấy lần sửa này.' );
		}
		$bai = (int) $v['doi_tuong_id'];
		$phan = (string) $v['phan'];
		$phien = self::phien( $bai, $phan, PhanBai::lay( $bai, $phan ) );
		$kq = self::trong_phien( $bai, $phan, $phien, static fn(): array => self::khoi_phuc_da_khoa( $viec_id, $nc ) );
		// Lối hoàn tác chỉ cần báo mở lại lịch sử, không đưa snapshot nội bộ ra REST.
		return ! empty( $kq['xung_dot'] ) ? self::hong( 'Phần này vừa đổi ở phiên khác. Mở lại Lịch sử để đối chiếu trước khi khôi phục.' ) : $kq;
	}

	private static function khoi_phuc_da_khoa( int $viec_id, array $nc ): array {
		$nc   = self::nc( $nc );
		$viec = Viec::lay( $viec_id );
		if ( ! $viec || Viec::SUA !== $viec['loai'] || 'post' !== $viec['doi_tuong_loai'] ) {
			return self::hong( 'Không tìm thấy lần sửa này.' );
		}
		$bai  = get_post( $viec['doi_tuong_id'] );
		$phan = (string) $viec['phan'];
		if ( ! $bai || ! Phan::co( $bai->post_type, $phan ) ) {
			return self::hong( 'Bài của lần sửa này không còn.' );
		}
		$cu = is_array( $viec['truoc'] ) ? $viec['truoc'] : null;
		if ( 'media' === $phan && ( $loi_anh = self::kiem_anh( $cu ) ) ) {
			return self::hong( $loi_anh );
		}
		[ , $loi ] = KiemPhan::kiem( $phan, KiemPhan::noi_dung( $cu ), self::ngu_canh_kiem( $bai, $nc ) );
		if ( $loi ) {
			return self::hong( ...$loi );
		}
		$truoc = PhanBai::lay( $bai->ID, $phan );
		if ( $truoc === $cu ) {
			return self::xong( false, 0 );
		}
		// Chỉ khôi phục lần sửa mới nhất của phần, khi phần còn đúng bản ngay sau lần đó: có lần sửa mới hơn thì từ chối,
		// không ghi đè (T-170)
		if ( Viec::moi_nhat_sua( $bai->ID, $phan ) !== $viec_id || ! self::con_nguyen( $truoc, $viec['sau'] ) ) {
			return self::hong( 'Phần này đã được sửa sau lần đó. Khôi phục từ lần sửa mới nhất trong Lịch sử, hoặc sửa tay.' );
		}
		return self::ghi( $bai, $phan, $truoc, $cu, $nc, 'khoi_phuc', self::luc( $nc ), $viec_id );
	}

	/** Giá trị đang lưu còn đúng bản "sau" của một dòng nhật ký (so dạng JSON: nhật ký lưu JSON, phần lưu meta). */
	public static function con_nguyen( mixed $hien_tai, mixed $sau ): bool {
		return wp_json_encode( $hien_tai ) === wp_json_encode( $sau );
	}

	private static function ghi( \WP_Post $bai, string $phan, ?array $truoc, ?array $sau, array $nc, string $quyet, string $luc, int $tu_viec = 0 ): array {
		$viec = Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'post',
				'doi_tuong_id'   => $bai->ID,
				'phan'           => $phan,
				'noi_dung'       => $tu_viec ? array( 'tu_viec' => $tu_viec ) : null,
				'nguoi_tao'      => KiemPhan::GOI_NGUOI === $nc['goi'] ? (string) $nc['nguoi'] : $nc['goi'],
				'tao_luc'        => $luc,
				'quyet'          => $quyet,
				'truoc'          => $truoc,
				'sau'            => $sau,
				'nguoi_quyet'    => $nc['nguoi'],
				'quyet_luc'      => $luc,
			)
		);
		if ( ! $viec ) {
			return self::hong( 'Không ghi được nhật ký nên chưa lưu. Thử lại sau.' );
		}
		if ( ! PhanBai::ghi( $bai->ID, $phan, $sau ) ) {
			PhanBai::ghi( $bai->ID, $phan, $truoc );
			Viec::xoa( $viec );
			return self::hong( 'Chưa lưu được phần này. Thử lại sau.' );
		}
		if ( KhaiBao::TAC_PHAM === $bai->post_type && in_array( $phan, BanChu::PHAN, true ) ) {
			$cap_nhat = wp_update_post(
				wp_slash(
					array(
						'ID'           => $bai->ID,
						'post_content' => BanChu::tu_phan( PhanBai::tat_ca( $bai->ID, $bai->post_type ) ),
					)
				),
				true
			);
			if ( is_wp_error( $cap_nhat ) || ! $cap_nhat ) {
				PhanBai::ghi( $bai->ID, $phan, $truoc );
				Viec::xoa( $viec );
				return self::hong( 'Chưa cập nhật được bản chữ; phần bài và nhật ký chưa được xác nhận.' );
			}
		}
		/** Sau khi lưu một phần: dựng lại chỉ mục tìm, xoá đệm của bài (T-144, T-152). */
		do_action( 'phongdao_da_luu_phan', $bai->ID, $phan, $viec );
		return self::xong( true, $viec );
	}

	private static function kiem_anh( ?array $media ): string {
		foreach ( $media['muc'] ?? array() as $m ) {
			if ( 'anh' === ( $m['loai'] ?? '' ) && ! \Phongdao\KhoLuu\AnhMinhHoa::hop_le( (int) $m['attachment_id'] ) ) {
				return 'Ảnh không còn hoặc định dạng chưa dùng được. Chọn một ảnh JPEG, PNG, GIF, WebP hoặc AVIF đã tải vào Thư viện của site.';
			}
		}
		return '';
	}

	private static function nc( array $nc ): array {
		return array(
			'nguoi' => (int) ( $nc['nguoi'] ?? 0 ),
			'goi'   => (string) ( $nc['goi'] ?? '' ),
			'duyet' => ! empty( $nc['duyet'] ),
			'luc'   => (string) ( $nc['luc'] ?? '' ),
		);
	}

	/** Thể loại của bài (tác phẩm) để kiểm phần riêng của thể loại. */
	private static function ngu_canh_kiem( \WP_Post $bai, array $nc ): array {
		$the_loai = null;
		if ( KhaiBao::TAC_PHAM === $bai->post_type ) {
			$terms    = get_the_terms( $bai, KhaiBao::THE_LOAI );
			$the_loai = is_array( $terms ) && $terms ? $terms[0]->slug : null;
		}
		return array(
			'loai_bai' => $bai->post_type,
			'the_loai' => $the_loai,
			'goi'      => $nc['goi'],
			'duyet'    => $nc['duyet'],
		);
	}

	private static function luc( array $nc ): string {
		$luc = $nc['luc'];
		return preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $luc ) ? $luc : gmdate( 'Y-m-d H:i:s' );
	}

	private static function xong( bool $doi, int $viec ): array {
		return array(
			'ok'   => true,
			'loi'  => array(),
			'doi'  => $doi,
			'viec' => $viec,
		);
	}

	private static function hong( string ...$loi ): array {
		return array(
			'ok'   => false,
			'loi'  => $loi,
			'doi'  => false,
			'viec' => 0,
		);
	}
}
