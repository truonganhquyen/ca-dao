<?php

namespace Phongdao\DichVu\GoiY;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\GoiY as Quy;

defined( 'ABSPATH' ) || exit;

/**
 * Thẻ "Gợi ý chủ đề, vùng" trong Hộp việc (docs/28 mục 4; thay trang "Gợi ý vùng, chủ đề" của MVP): mỗi gợi ý — một
 * vùng hoặc một chủ đề cho một bài, kèm lý do — là một việc. Đồng ý gắn phân loại; Bỏ ghi lại để máy không gợi ý lại.
 * Tính lại khi lời, chú thích đổi, và một lượt cho mọi bài đã đăng (việc nền theo lô). Bài người sáng lập đã duyệt
 * gợi ý trên MVP (chuyển sang thành việc đã quyết mã "*") không gợi ý lại.
 */
final class GoiY implements \Phongdao\HaTang\Module {

	public const LOAI = 'goi_y';

	public const VIEC_NEN = 'phongdao_goi_y_lo';

	/** Con trỏ lô (ID bài cuối đã xét; -1 = xong). */
	public const SAU = 'phongdao_goi_y_sau';

	public function dang_ky(): void {
		add_action(
			'phongdao_da_luu_phan',
			static function ( int $bai, string $phan ): void {
				if ( in_array( $phan, array( 'loi', 'chu_thich' ), true ) ) {
					self::ghi( $bai );
				}
			},
			20,
			2
		);
		add_action( self::VIEC_NEN, array( self::class, 'lo' ) );
	}

	/** Bắt đầu một lượt cho mọi bài đã đăng (nâng cấp CSDL). */
	public static function bat_dau(): void {
		update_option( self::SAU, 0, false );
		if ( ! wp_next_scheduled( self::VIEC_NEN ) ) {
			wp_schedule_single_event( time() + 120, self::VIEC_NEN );
		}
	}

	/** Một lô; còn bài thì hẹn lô sau. @return int số bài đã xét */
	public static function lo( int $so = 200 ): int {
		global $wpdb;
		$sau = (int) get_option( self::SAU, -1 );
		if ( $sau < 0 ) {
			return 0;
		}
		$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND ID > %d ORDER BY ID LIMIT %d", KhaiBao::TAC_PHAM, $sau, $so ) );
		update_meta_cache( 'post', array_map( 'intval', $ds ) );
		foreach ( $ds as $id ) {
			self::ghi( (int) $id );
		}
		update_option( self::SAU, count( $ds ) === $so ? (int) end( $ds ) : -1, false );
		if ( count( $ds ) === $so ) {
			wp_schedule_single_event( time() + 60, self::VIEC_NEN );
		}
		return count( $ds );
	}

	/** Tính gợi ý của một bài đã đăng, tạo việc cho gợi ý mới. @return int số việc mới */
	public static function ghi( int $id ): int {
		$bai = get_post( $id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'publish' !== $bai->post_status ) {
			return 0;
		}
		if ( 'dang' === get_option( \Phongdao\DichVu\NhapDuLieuCu\NhapLam::TRANG_THAI ) ) {
			return 0; // đang nhập dữ liệu cũ: nhập xong mới gợi ý một lượt (hoàn tác không phải gỡ việc máy tạo giữa chừng)
		}
		$viec = Viec::cua( 'post', $id, self::LOAI );
		if ( array_filter( $viec, static fn( array $v ): bool => '*' === ( $v['noi_dung']['ma'] ?? '' ) ) ) {
			return 0; // người sáng lập đã duyệt gợi ý cả bài trên MVP
		}
		$vao = self::vao( $bai, $viec );
		// Việc đang mở mà bài đã có phân loại đó (người gắn ở chỗ khác): đóng
		foreach ( $viec as $v ) {
			if ( in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) && in_array( self::khoa( $v ), $vao['da_co'], true ) ) {
				Viec::dong( (int) $v['id'], Viec::XONG, 'da_co', 0 );
			}
		}
		$so = 0;
		foreach ( Quy::cho( $vao ) as $g ) {
			if ( ! term_exists( $g['ma'], $g['loai'] ) ) {
				continue; // bộ chủ đề, vùng của site chưa có mục này
			}
			Viec::tao(
				array(
					'loai'           => self::LOAI,
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'phan'           => $g['loai'],
					'noi_dung'       => $g,
					'nguoi_tao'      => 'may',
					'uu_tien'        => 15,
				)
			);
			++$so;
		}
		return $so;
	}

	/** Đồng ý một gợi ý: gắn phân loại (thêm vào, không bỏ cái bài đang có). */
	public static function dong_y( array $v ): bool {
		$n = $v['noi_dung'];
		if ( ! in_array( $n['loai'] ?? '', array( 'vung', 'chu_de' ), true ) || ! term_exists( (string) $n['ma'], (string) $n['loai'] ) ) {
			return false;
		}
		return ! is_wp_error( wp_set_object_terms( (int) $v['doi_tuong_id'], (string) $n['ma'], (string) $n['loai'], true ) );
	}

	/** Tên dễ đọc của gợi ý: "Vùng: Nam Bộ", "Chủ đề: Lối hát → Hò". */
	public static function ten( array $n ): string {
		return 'vung' === ( $n['loai'] ?? '' ) ? 'Vùng: ' . ( Quy::VUNG[ $n['ma'] ] ?? $n['ma'] ) : 'Chủ đề: ' . Quy::ten_chu_de( (string) ( $n['ma'] ?? '' ) );
	}

	private static function khoa( array $v ): string {
		return ( $v['noi_dung']['loai'] ?? '' ) . ':' . ( $v['noi_dung']['ma'] ?? '' );
	}

	/** Dữ liệu vào của quy tắc gợi ý (MienDuLieu\GoiY::cho). */
	private static function vao( \WP_Post $bai, array $viec ): array {
		global $wpdb;
		$ct = array();
		foreach ( PhanBai::lay( $bai->ID, 'chu_thich' )['muc'] ?? array() as $c ) {
			$wd   = empty( $c['muc_tu_id'] ) ? null : get_term_meta( (int) $c['muc_tu_id'], '_phongdao_wikidata', true );
			$ct[] = array(
				'tu'       => (string) ( $c['tu'] ?? '' ),
				'loi_giai' => (string) ( $c['loi_giai'] ?? '' ),
				'toa_do'   => is_array( $wd ) ? ( $wd['toa_do'] ?? null ) : null,
			);
		}
		$du     = $wpdb->get_var( $wpdb->prepare( 'SELECT du_lieu FROM ' . Kho::bang() . ' WHERE post_id = %d ORDER BY id LIMIT 1', $bai->ID ) );
		$anh_xa = get_option( 'cadao_anh_xa_chu_de' ); // bảng ánh xạ người sáng lập đã lưu ở MVP (mang sang nguyên tuỳ chọn)
		$da_co  = array();
		foreach ( array( 'vung', 'chu_de' ) as $tax ) {
			foreach ( wp_get_object_terms( $bai->ID, $tax, array( 'fields' => 'slugs' ) ) ?: array() as $s ) {
				$da_co[] = "$tax:$s";
			}
		}
		return array(
			'tieu_de'    => $bai->post_title,
			'loi'        => array_values( array_filter( PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array(), 'strlen' ) ),
			'the_loai'   => (string) DocBai::the_loai( $bai ),
			'chu_thich'  => $ct,
			'chu_de_goc' => array_map( 'strval', (array) ( json_decode( (string) $du, true )['chu_de_goc'] ?? array() ) ),
			'anh_xa'     => is_array( $anh_xa ) ? $anh_xa : Quy::ANH_XA_DE_XUAT,
			'da_co'      => $da_co,
			'da_quyet'   => array_map( array( self::class, 'khoa' ), $viec ),
		);
	}
}
