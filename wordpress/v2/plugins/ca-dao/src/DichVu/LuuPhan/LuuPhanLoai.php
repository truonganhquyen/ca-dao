<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheTho;

defined( 'ABSPATH' ) || exit;

/**
 * Người biên soạn sửa tay phân loại của một tác phẩm (docs/30 mục 2, 4; T-169) — đường ghi duy nhất cho thao tác này.
 * Chỉ chọn mục đã có trong bộ phân loại, không tạo mục mới (AGENTS.md "Không bao giờ" 6). Thể loại không sửa ở đây vì
 * đổi đường dẫn bài (H6); mục từ đi theo phần Từ khó; thể thơ do máy tính từ lời (GanHinhThuc) nên giữ nguyên.
 * Màn hình gửi kèm tập mục nó đã thấy: nếu bài vừa đổi ở nơi khác thì từ chối, không ghi đè. Nhật ký và phân loại ghi
 * trong một giao dịch; hỏng giữa chừng thì trả lại như cũ.
 *
 * Quyền đăng nhập, nonce kiểm ở lớp gọi; lớp này chỉ nhận người đã kiểm.
 */
final class LuuPhanLoai {

	/** Phân loại sửa tay được => các thể loại áp dụng (null = mọi thể loại). */
	public const SUA_DUOC = array(
		'chu_de'    => null,
		'vung'      => null,
		'mo_tip'    => null,
		'hinh_thuc' => null,
		'tro_choi'  => array( 'dong-dao' ),
	);

	/** Mục do máy giữ, người không gắn hay gỡ tay: thể thơ tính từ lời. */
	public static function may_giu( string $tax, \WP_Term $t ): bool {
		return 'hinh_thuc' === $tax && isset( TheTho::DS[ $t->slug ] );
	}

	public static function sua_duoc( string $tax, ?string $the_loai ): bool {
		if ( ! array_key_exists( $tax, self::SUA_DUOC ) ) {
			return false;
		}
		return null === self::SUA_DUOC[ $tax ] || in_array( (string) $the_loai, self::SUA_DUOC[ $tax ], true );
	}

	/** Mục người chọn được trong một bộ phân loại, theo tên. @return \WP_Term[] */
	public static function lua_chon( string $tax ): array {
		$ds = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
		if ( ! is_array( $ds ) ) {
			return array();
		}
		return array_values( array_filter( $ds, static fn( $t ): bool => $t instanceof \WP_Term && ! self::may_giu( $tax, $t ) ) );
	}

	/** Mục đang gắn (phần người sửa được), ID tăng dần; null khi không đọc được. @return int[]|null */
	public static function dang_gan( int $bai, string $tax ): ?array {
		$ds = wp_get_object_terms( $bai, $tax );
		if ( is_wp_error( $ds ) ) {
			return null;
		}
		$ids = array();
		foreach ( $ds as $t ) {
			if ( $t instanceof \WP_Term && ! self::may_giu( $tax, $t ) ) {
				$ids[] = (int) $t->term_id;
			}
		}
		sort( $ids, SORT_NUMERIC );
		return $ids;
	}

	/**
	 * @param int[] $chon    tập mục người chọn
	 * @param int[] $da_thay tập mục màn hình đã thấy lúc mở
	 * @return array{ok:bool,loi:string[],doi:bool,viec:int}
	 */
	public static function luu( int $bai_id, string $tax, array $chon, array $da_thay, int $nguoi ): array {
		global $wpdb;
		$bai = get_post( $bai_id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'trash' === $bai->post_status ) {
			return self::hong( 'Không tìm thấy tác phẩm này.' );
		}
		$tl       = get_the_terms( $bai, KhaiBao::THE_LOAI );
		$the_loai = is_array( $tl ) && $tl ? $tl[0]->slug : null;
		$ten      = KhaiBao::PHAN_LOAI[ $tax ][1] ?? $tax;
		if ( ! self::sua_duoc( $tax, $the_loai ) ) {
			return self::hong( "Không sửa “{$ten}” của bài này ở đây." );
		}
		$chon = self::ids( $chon );
		foreach ( $chon as $id ) {
			$t = get_term( $id, $tax );
			if ( ! $t instanceof \WP_Term || self::may_giu( $tax, $t ) ) {
				return self::hong( "Mục #{$id} không có trong bộ {$ten}. Màn này chỉ chọn mục có sẵn, không tạo mục mới." );
			}
		}
		$hien_tai = self::dang_gan( $bai->ID, $tax );
		if ( null === $hien_tai ) {
			return self::hong( 'Chưa đọc được phân loại của bài. Thử lại sau.' );
		}
		if ( self::ids( $da_thay ) !== $hien_tai ) {
			return self::hong( "{$ten} của bài vừa được đổi ở nơi khác. Tải lại trang để xem bản mới rồi sửa tiếp." );
		}
		if ( $chon === $hien_tai ) {
			return self::xong( false, 0 );
		}
		$giu = array();
		foreach ( wp_get_object_terms( $bai->ID, $tax ) ?: array() as $t ) {
			if ( $t instanceof \WP_Term && self::may_giu( $tax, $t ) ) {
				$giu[] = (int) $t->term_id;
			}
		}
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return self::hong( 'Chưa mở được lượt lưu. Thử lại sau.' );
		}
		$luc  = gmdate( 'Y-m-d H:i:s' );
		$viec = Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'post',
				'doi_tuong_id'   => $bai->ID,
				'phan'           => $tax,
				'nguoi_tao'      => (string) $nguoi,
				'tao_luc'        => $luc,
				'quyet'          => 'luu',
				'truoc'          => array( 'term_ids' => $hien_tai, 'ten' => self::ten( $hien_tai, $tax ) ),
				'sau'            => array( 'term_ids' => $chon, 'ten' => self::ten( $chon, $tax ) ),
				'nguoi_quyet'    => $nguoi,
				'quyet_luc'      => $luc,
			)
		);
		if ( ! $viec ) {
			$wpdb->query( 'ROLLBACK' );
			return self::hong( 'Không ghi được nhật ký nên chưa lưu. Thử lại sau.' );
		}
		$gan = wp_set_object_terms( $bai->ID, array_merge( $chon, $giu ), $tax, false );
		if ( is_wp_error( $gan ) || self::dang_gan( $bai->ID, $tax ) !== $chon || false === $wpdb->query( 'COMMIT' ) ) {
			$wpdb->query( 'ROLLBACK' );
			clean_object_term_cache( $bai->ID, $bai->post_type );
			return self::hong( 'Chưa lưu được phân loại; mọi thay đổi đã được huỷ.' );
		}
		/** Sau khi người sửa tay phân loại của bài. */
		do_action( 'phongdao_da_luu_phan_loai', $bai->ID, $tax, $viec );
		return self::xong( true, $viec );
	}

	/** @return int[] ID dương, không trùng, tăng dần */
	private static function ids( array $ds ): array {
		$ids = array_values( array_unique( array_filter( array_map( 'intval', $ds ), static fn( int $i ): bool => $i > 0 ) ) );
		sort( $ids, SORT_NUMERIC );
		return $ids;
	}

	/** Tên mục lúc ghi (mục có thể đổi tên hay bị xoá sau này). @return string[] */
	private static function ten( array $ids, string $tax ): array {
		$ra = array();
		foreach ( $ids as $id ) {
			$t    = get_term( $id, $tax );
			$ra[] = $t instanceof \WP_Term ? $t->name : '#' . $id;
		}
		return $ra;
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
