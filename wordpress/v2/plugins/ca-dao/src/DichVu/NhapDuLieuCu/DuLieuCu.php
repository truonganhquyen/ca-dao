<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

defined( 'ABSPATH' ) || exit;

/**
 * Đọc dữ liệu MVP — chỉ đọc, bằng truy vấn trực tiếp nên chạy được cả khi plugin MVP đang bật (tệp chạy thử
 * cong-cu/thu-nhap.php trên site thật) lẫn sau khi V2 thay chỗ (phân loại cũ không còn được khai báo).
 */
final class DuLieuCu {

	public const LOAI_BAI = 'cdtn';

	/** Bài MVP còn dùng được (không tính thùng rác, bản nháp tự động). */
	public static function bai_ids(): array {
		global $wpdb;
		return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ('publish','future','private','draft','pending') ORDER BY ID", self::LOAI_BAI ) ) );
	}

	/**
	 * Một bài MVP đủ cho DungPhan, kèm thông tin để quyết cách nhập.
	 *
	 * @return array{id:int, tieu_de:string, ten:string, trang_thai:string, tac_gia:int, ngay:string, sua_cuoi:int, noi_dung:string, bam_khop:bool, the_loai:string[], khoi:array, tom_tat:string, meta:array, kho:list<array>}|null
	 */
	public static function bai( int $id ): ?array {
		$p = get_post( $id );
		if ( ! $p || self::LOAI_BAI !== $p->post_type ) {
			return null;
		}
		$meta = array();
		foreach ( get_post_meta( $id ) as $k => $v ) {
			$meta[ $k ] = maybe_unserialize( $v[0] ?? '' );
		}
		$bam = (string) ( $meta['_cadao_bam'] ?? '' );
		return array(
			'id'         => $id,
			'tieu_de'    => (string) $p->post_title,
			'ten'        => (string) $p->post_name,
			'trang_thai' => (string) $p->post_status,
			'tac_gia'    => (int) $p->post_author,
			// Lúc người làm sửa bài lần cuối trên MVP (nháp chưa có ngày đăng GMT) — giờ của nhật ký khi nhập
			'ngay'       => str_starts_with( (string) $p->post_modified_gmt, '0000' ) ? (string) $p->post_date_gmt : (string) $p->post_modified_gmt,
			'sua_luc'    => array( (string) $p->post_modified, (string) $p->post_modified_gmt ),
			'ngay_tao'   => array( (string) $p->post_date, (string) $p->post_date_gmt ),
			'sua_cuoi'   => (int) ( $meta['_edit_last'] ?? 0 ),
			'noi_dung'   => (string) $p->post_content,
			'bam_khop'   => '' !== $bam && md5( $p->post_content ) === $bam,
			'the_loai'   => self::the( $id, 'the_loai' ),
			'khoi'       => parse_blocks( (string) $p->post_content ),
			'tom_tat'    => (string) $p->post_excerpt,
			'meta'       => $meta,
			'kho'        => self::kho_cua_bai( $id ),
		);
	}

	/**
	 * Tên đường dẫn (slug) các term của một bài theo một phân loại, cùng thứ tự get_the_terms (theo tên) —
	 * đọc thẳng bảng nên không cần phân loại còn được khai báo.
	 *
	 * @return string[]
	 */
	public static function the( int $id, string $phan_loai ): array {
		global $wpdb;
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT t.slug FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id JOIN {$wpdb->term_relationships} r ON r.term_taxonomy_id = tt.term_taxonomy_id WHERE r.object_id = %d AND tt.taxonomy = %s ORDER BY t.name, t.term_id",
				$id,
				$phan_loai
			)
		);
	}

	/**
	 * Câu trong kho của bài: câu đã thành bài trước, rồi các câu đã gộp vào câu đó hoặc vào bài (như MVP dựng nội dung).
	 *
	 * @return list<array{id:int, loi:string, du_lieu:array, nguon_id:string, chinh:bool}>
	 */
	public static function kho_cua_bai( int $id ): array {
		global $wpdb;
		$bang = $wpdb->prefix . 'cadao_kho';
		if ( ! self::co_bang( $bang ) ) {
			return array();
		}
		$chinh = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $bang WHERE post_id = %d ORDER BY id", $id ) ) ?: array();
		$khoa  = array_merge( array( 'bai:' . $id ), array_map( static fn( $m ) => 'kho:' . $m->id, $chinh ) );
		$gop   = $wpdb->get_results( "SELECT * FROM $bang WHERE trang_thai = 'da_gop' AND gop_vao IN (" . implode( ',', array_map( static fn( $k ) => $wpdb->prepare( '%s', $k ), $khoa ) ) . ') ORDER BY id' ) ?: array();
		$ra    = array();
		foreach ( array_merge( $chinh, $gop ) as $i => $m ) {
			$ra[] = array(
				'id'       => (int) $m->id,
				'loi'      => (string) $m->loi,
				'du_lieu'  => json_decode( (string) $m->du_lieu, true ) ?: array(),
				'nguon_id' => (string) $m->nguon_id,
				'chinh'    => $i < count( $chinh ),
			);
		}
		return $ra;
	}

	public static function co_bang( string $bang ): bool {
		global $wpdb;
		return $bang === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $bang ) ) );
	}
}
