<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/** Danh sách tác phẩm trong wp-admin: chỉ đọc, sửa bài vẫn mở trên trang bài (T-167). */
final class DanhSachTacPham {

	public const TRANG = 'phongdao-tac-pham';
	private const MOI_TRANG = 30;
	private const TRANG_THAI = array(
		'tat_ca' => 'Tất cả',
		'publish' => 'Đã đăng',
		'draft'   => 'Bản nháp',
		'pending' => 'Chờ',
		'private' => 'Đã ẩn',
		'future'  => 'Đã hẹn',
		'trash'   => 'Thùng rác',
	);
	private const MUC = array(
		'tat_ca'         => 'Mọi mức',
		'thieu_y_nghia'  => 'Thiếu ý nghĩa',
		'thieu_loi'      => 'Thiếu lời',
		'co_co'          => 'Có cờ nghi',
	);

	/** @return array<string,string|int> Bộ lọc đã kiểm; URL và SQL chỉ nhận các giá trị này. */
	private static function loc( ?array $vao = null ): array {
		$vao   = $vao ?? wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$nguon = sanitize_text_field( (string) ( $vao['nguon'] ?? 'tat_ca' ) );
		$ds_nguon = NguonTuDong::tat_ca();
		if ( ! in_array( $nguon, array( 'tat_ca', 'may', 'nguoi', 'khong_may' ), true ) ) {
			$id = str_starts_with( $nguon, 'may:' ) ? substr( $nguon, 4 ) : '';
			$nguon = isset( $ds_nguon[ $id ] ) && preg_match( '/^[a-z0-9_-]+$/', $id ) ? 'may:' . $id : 'tat_ca';
		}
		$tt = sanitize_key( (string) ( $vao['tt'] ?? 'tat_ca' ) );
		$muc = sanitize_key( (string) ( $vao['muc'] ?? 'tat_ca' ) );
		return array(
			'q'        => sanitize_text_field( (string) ( $vao['q'] ?? '' ) ),
			'tt'       => isset( self::TRANG_THAI[ $tt ] ) ? $tt : 'tat_ca',
			'the_loai' => sanitize_title( (string) ( $vao['the_loai'] ?? '' ) ),
			'chu_de'   => sanitize_title( (string) ( $vao['chu_de'] ?? '' ) ),
			'muc'      => isset( self::MUC[ $muc ] ) ? $muc : 'tat_ca',
			'nguon'    => $nguon,
			'paged'    => max( 1, (int) ( $vao['paged'] ?? 1 ) ),
		);
	}

	/**
	 * Mọi ID tác phẩm khớp bộ lọc (phạm vi "mọi bài khớp bộ lọc" của một lô), cùng điều kiện với danh sách; lấy tối đa
	 * $toi_da + 1 để biết có vượt giới hạn.
	 *
	 * @param array<string,string> $vao bộ lọc đã gửi (q, tt, the_loai, chu_de, muc, nguon)
	 * @return int[]
	 */
	public static function ids_loc( array $vao, int $toi_da ): array {
		$args = array_merge( self::tham_so( self::loc( $vao ) ), array( 'fields' => 'ids', 'posts_per_page' => $toi_da + 1, 'paged' => 1, 'no_found_rows' => true, 'update_post_meta_cache' => false, 'update_post_term_cache' => false ) );
		return array_map( 'intval', ( new \WP_Query( $args ) )->posts );
	}

	/** @return array<string,mixed> */
	private static function tham_so( array $loc ): array {
		$tt = 'tat_ca' === $loc['tt'] ? array_slice( array_keys( self::TRANG_THAI ), 1 ) : array( $loc['tt'] );
		$args = array(
			'post_type'              => KhaiBao::TAC_PHAM,
			'post_status'            => $tt,
			'posts_per_page'         => self::MOI_TRANG,
			'paged'                  => $loc['paged'],
			'orderby'                => 'modified',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'         => false,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);
		if ( '' !== $loc['q'] ) {
			if ( ctype_digit( $loc['q'] ) ) {
				$args['post__in'] = array( (int) $loc['q'] );
			} else {
				$args['s'] = $loc['q'];
			}
		}
		$tax = array();
		foreach ( array( 'the_loai', 'chu_de' ) as $ten ) {
			if ( '' !== $loc[ $ten ] ) {
				$tax[] = array( 'taxonomy' => $ten, 'field' => 'slug', 'terms' => $loc[ $ten ] );
			}
		}
		if ( $tax ) {
			$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax );
		}
		$meta = array();
		$phan = array( 'thieu_y_nghia' => 'y_nghia', 'thieu_loi' => 'loi', 'co_co' => 'co_nghi' )[ $loc['muc'] ] ?? '';
		if ( $phan ) {
			$meta[] = array( 'key' => Phan::khoa( $phan ), 'compare' => 'co_co' === $loc['muc'] ? 'EXISTS' : 'NOT EXISTS' );
		}
		if ( 'may' === $loc['nguon'] || str_starts_with( $loc['nguon'], 'may:' ) ) {
			$dk = array( 'key' => Phan::khoa( 'nguon_may' ), 'compare' => 'EXISTS' );
			if ( str_starts_with( $loc['nguon'], 'may:' ) ) {
				$id = substr( $loc['nguon'], 4 );
				$dk = array( 'key' => Phan::khoa( 'nguon_may' ), 'value' => '"nguon_id";s:' . strlen( $id ) . ':"' . $id . '";', 'compare' => 'LIKE' );
			}
			$meta[] = $dk;
		} elseif ( 'nguoi' === $loc['nguon'] ) {
			$meta[] = array( 'key' => Phan::khoa( 'loi' ), 'value' => '"nguon";a:', 'compare' => 'LIKE' );
		} elseif ( 'khong_may' === $loc['nguon'] ) {
			$meta[] = array( 'key' => Phan::khoa( 'nguon_may' ), 'compare' => 'NOT EXISTS' );
		}
		if ( $meta ) {
			$args['meta_query'] = array_merge( array( 'relation' => 'AND' ), $meta );
		}
		return $args;
	}

	/** Đếm việc mở của đúng 30 bài trên trang bằng một truy vấn. */
	private static function viec_trang( array $bai ): array {
		global $wpdb;
		$ids = array_map( static fn( \WP_Post $p ): int => (int) $p->ID, $bai );
		if ( ! $ids ) {
			return array();
		}
		$dau = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql = $wpdb->prepare( 'SELECT doi_tuong_id, COUNT(*) AS so, MIN(id) AS dau FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'post' AND trang_thai IN ('cho','de_sau') AND doi_tuong_id IN ($dau) GROUP BY doi_tuong_id", $ids );
		$ra = array();
		foreach ( $wpdb->get_results( $sql, ARRAY_A ) ?: array() as $hang ) {
			$ra[ (int) $hang['doi_tuong_id'] ] = array( 'so' => (int) $hang['so'], 'dau' => (int) $hang['dau'] );
		}
		return $ra;
	}

	private static function url( array $loc, int $trang = 1 ): string {
		return add_query_arg( array_filter( array( 'page' => self::TRANG, 'q' => $loc['q'], 'tt' => $loc['tt'], 'the_loai' => $loc['the_loai'], 'chu_de' => $loc['chu_de'], 'muc' => $loc['muc'], 'nguon' => $loc['nguon'], 'paged' => $trang ), static fn( $v ) => '' !== $v ), admin_url( 'admin.php' ) );
	}

	private static function chon( string $ten, string $nhan, array $ds, string $gia_tri ): string {
		$ra = '<div class="phongdao-tp-truong"><label for="phongdao-' . esc_attr( $ten ) . '">' . esc_html( $nhan ) . '</label><select id="phongdao-' . esc_attr( $ten ) . '" name="' . esc_attr( $ten ) . '">';
		foreach ( $ds as $ma => $ten_hien ) {
			$ra .= '<option value="' . esc_attr( (string) $ma ) . '"' . selected( (string) $ma, $gia_tri, false ) . '>' . esc_html( (string) $ten_hien ) . '</option>';
		}
		return $ra . '</select></div>';
	}

	private static function tu_nguon( array $ds ): array {
		$ra = array( 'tat_ca' => 'Mọi nguồn', 'may' => 'Có nguồn tự động', 'nguoi' => 'Có nguồn người điền', 'khong_may' => 'Không từ nguồn tự động' );
		foreach ( $ds as $id => $n ) {
			if ( preg_match( '/^[a-z0-9_-]+$/', (string) $id ) ) {
				$ra[ 'may:' . $id ] = (string) ( $n['ten'] ?? $id );
			}
		}
		return $ra;
	}

	private static function dong( \WP_Post $bai, array $viec, array $nguon, bool $lo = false ): string {
		$id = (int) $bai->ID;
		$tt = self::TRANG_THAI[ $bai->post_status ] ?? $bai->post_status;
		$loai = get_the_terms( $id, KhaiBao::THE_LOAI );
		$ten_loai = is_array( $loai ) && $loai ? $loai[0]->name : 'Chưa có thể loại';
		$thieu = array();
		$loi = PhanBai::lay( $id, 'loi' );
		if ( null === $loi ) {
			$thieu[] = 'Lời';
		}
		if ( null === PhanBai::lay( $id, 'y_nghia' ) ) {
			$thieu[] = 'Ý nghĩa';
		}
		$may = PhanBai::lay( $id, 'nguon_may' );
		$nguon_hien = $may ? (string) ( $nguon[ $may['nguon_id'] ?? '' ]['ten'] ?? $may['nguon_id'] ?? 'Nguồn tự động' ) : ( ! empty( $loi['nguon'] ) ? 'Nguồn người điền' : 'Chưa ghi nguồn cho Lời' );
		$ve = (int) ( $viec[ $id ]['so'] ?? 0 );
		$lien = 'trash' === $bai->post_status || ! current_user_can( 'edit_post', $id ) ? '' : ( 'publish' === $bai->post_status || 'private' === $bai->post_status ? (string) get_permalink( $bai ) : (string) get_preview_post_link( $bai ) );
		$ten = '' !== trim( $bai->post_title ) ? $bai->post_title : '(Chưa đặt tên)';
		$ten = $lien ? '<a href="' . esc_url( $lien ) . '">' . esc_html( $ten ) . '</a>' : esc_html( $ten );
		$viec_lien = $ve ? '<a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => (int) $viec[ $id ]['dau'] ), admin_url( 'admin.php' ) ) ) . '">' . $ve . ' việc mở</a>' : '—';
		$sua_lien = 'trash' !== $bai->post_status && current_user_can( 'edit_post', $id ) ? ' <a href="' . esc_url( SuaTacPham::url( $id ) ) . '">Sửa bài</a>' : '';
		$chon = $lo ? '<td data-nhan="Chọn"><input type="checkbox" name="ids[]" value="' . $id . '" id="chon-bai-' . $id . '"><label for="chon-bai-' . $id . '" class="screen-reader-text">Chọn bài #' . $id . '</label></td>' : '';
		return '<tr>' . $chon . '<td data-nhan="ID">' . $id . '</td><td data-nhan="Tác phẩm"><strong>' . $ten . '</strong>' . $sua_lien . '</td><td data-nhan="Trạng thái">' . esc_html( $tt ) . '</td><td data-nhan="Thể loại">' . esc_html( $ten_loai ) . '</td><td data-nhan="Phần thiếu">' . esc_html( $thieu ? implode( ', ', $thieu ) : 'Đủ phần chính' ) . '</td><td data-nhan="Nguồn Lời">' . esc_html( $nguon_hien ) . '</td><td data-nhan="Việc">' . $viec_lien . '</td><td data-nhan="Sửa cuối">' . esc_html( mysql2date( 'd/m/Y H:i', $bai->post_modified ) ) . '</td></tr>';
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem danh sách này.</p></div>';
		}
		$loc = self::loc();
		$truy = new \WP_Query( self::tham_so( $loc ) );
		$nguon = NguonTuDong::tat_ca();
		$viec = self::viec_trang( $truy->posts );
		$ds_the_loai = array( '' => 'Mọi thể loại' );
		$ds_chu_de = array( '' => 'Mọi chủ đề' );
		foreach ( array( 'the_loai', 'chu_de' ) as $tax ) {
			$ds = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
			foreach ( is_wp_error( $ds ) ? array() : $ds as $term ) {
				if ( 'the_loai' === $tax ) {
					$ds_the_loai[ $term->slug ] = $term->name;
				} else {
					$ds_chu_de[ $term->slug ] = $term->name;
				}
			}
		}
		$form = '<form class="phongdao-tp-loc" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . self::TRANG . '"><div class="phongdao-tp-truong"><label for="phongdao-q">Tìm ID, tên hoặc lời</label><input id="phongdao-q" type="search" name="q" value="' . esc_attr( $loc['q'] ) . '"></div>'
			. self::chon( 'tt', 'Trạng thái', self::TRANG_THAI, $loc['tt'] )
			. self::chon( 'the_loai', 'Thể loại', $ds_the_loai, $loc['the_loai'] )
			. self::chon( 'chu_de', 'Chủ đề', $ds_chu_de, $loc['chu_de'] )
			. self::chon( 'muc', 'Phần và cờ', self::MUC, $loc['muc'] )
			. self::chon( 'nguon', 'Nguồn Lời', self::tu_nguon( $nguon ), $loc['nguon'] )
			. '<button class="button button-primary" type="submit">Tìm và lọc</button></form>';
		// Làm theo lô (T-171): ô chọn và khối thao tác khi có bài khớp
		$lo   = (int) $truy->found_posts > 0;
		$dong = implode( '', array_map( static fn( \WP_Post $p ): string => self::dong( $p, $viec, $nguon, $lo ), $truy->posts ) );
		$so_trang = (int) $truy->max_num_pages;
		$phan_trang = $so_trang > 1 ? '<nav class="phongdao-tp-trang" aria-label="Phân trang"><span>Trang ' . (int) $loc['paged'] . ' / ' . $so_trang . '</span>'
			. ( $loc['paged'] > 1 ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] - 1 ) ) . '">Trang trước</a>' : '' )
			. ( $loc['paged'] < $so_trang ? '<a class="button" href="' . esc_url( self::url( $loc, $loc['paged'] + 1 ) ) . '">Trang sau</a>' : '' ) . '</nav>' : '';
		return '<div class="wrap phongdao-admin-tp"><h1>Tác phẩm</h1><p class="description">Danh sách đọc từ toàn kho. Mở bài để sửa bằng biểu mẫu theo phần trên trang bài; việc chờ mở ở Hộp việc.</p>' . $form
			. TrangLo::lay_bao()
			. '<p class="phongdao-tp-tong" role="status"><strong>' . (int) $truy->found_posts . '</strong> tác phẩm khớp bộ lọc. Hiện ' . count( $truy->posts ) . ' mục trên trang này.</p>'
			. ( $lo ? '<form method="post" class="phongdao-lo-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . TrangLo::khoi_chon_bai( $loc, (int) $truy->found_posts ) : '' )
			. '<table class="widefat striped"><thead><tr>' . ( $lo ? '<th scope="col">Chọn</th>' : '' ) . '<th scope="col">ID</th><th scope="col">Tác phẩm</th><th scope="col">Trạng thái</th><th scope="col">Thể loại</th><th scope="col">Phần thiếu</th><th scope="col">Nguồn Lời</th><th scope="col">Việc</th><th scope="col">Sửa cuối</th></tr></thead><tbody>'
			. ( $dong ?: '<tr><td colspan="8">Không có tác phẩm khớp. Thử đổi bộ lọc.</td></tr>' ) . '</tbody></table>' . ( $lo ? '</form>' : '' ) . $phan_trang . '</div>';
	}
}
