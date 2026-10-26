<?php
/**
 * Dòng số liệu của bài (T-115, D-069): 👁 lượt đọc · ↗ lượt chia sẻ · ❝ lượt trích dẫn, icon nét SVG, số rút gọn
 * (1,2K). Mỗi số chỉ hiện khi đủ ngưỡng (đọc ≥ 10, chia sẻ / trích dẫn ≥ 3) — dưới ngưỡng thì ẩn, không để ô trống.
 * Trang bài: ngay dưới tiêu đề. Danh sách bài (trang thể loại, kho, chủ đề…, Đọc nhiều, kết quả tìm): chỉ 👁.
 * Chia sẻ đếm khi người đọc gửi xong (Web Share báo thành công; bấm rồi hủy thì không tính) hoặc sao chép liên kết;
 * trích dẫn đếm "Sao chép kèm nguồn" (thông dụng, APA). Không cookie, không IP (do-luong.php).
 */

defined( 'ABSPATH' ) || exit;

const CADAO_NGUONG_SO_LIEU = array(
	'doc'       => 10,
	'chia_se'   => 3,
	'trich_dan' => 3,
);

/** Số rút gọn: 999 · 1,2K · 15K · 1,2M. */
function cadao_so_gon( int $n ): string {
	if ( $n < 1000 ) {
		return number_format_i18n( $n );
	}
	foreach ( array( 1000000 => 'M', 1000 => 'K' ) as $don_vi => $chu ) {
		if ( $n >= $don_vi ) {
			$x = $n / $don_vi;
			return ( $x < 10 ? str_replace( ',0', '', number_format( floor( $x * 10 ) / 10, 1, ',', '.' ) ) : number_format( floor( $x ), 0, ',', '.' ) ) . $chu;
		}
	}
	return (string) $n;
}

/** Tổng một sự kiện của bài (mọi ngày), theo các kiểu đã cho. */
function cadao_dem_su_kien_bai( int $id, string $loai, array $kieu ): int {
	global $wpdb;
	$khoa = array_map( static fn( $k ) => $id . ':' . $k, $kieu );
	$cho  = implode( ',', array_fill( 0, count( $khoa ), '%s' ) );
	return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT SUM(so) FROM ' . cadao_su_kien_bang() . " WHERE loai = %s AND khoa IN ($cho)", $loai, ...$khoa ) );
}

/**
 * Số liệu của bài.
 *
 * @return array{doc: int, chia_se: int, trich_dan: int}
 */
function cadao_so_lieu_bai( int $id ): array {
	return array(
		'doc'       => cadao_luot_doc_tong( $id ),
		'chia_se'   => cadao_dem_su_kien_bai( $id, 'chia_se', array_keys( CADAO_KIEU_CHIA_SE ) ),
		'trich_dan' => cadao_dem_su_kien_bai( $id, 'trich_dan_sao_chep', array( 'thong_dung', 'apa' ) ),
	);
}

/** Icon nét (theo màu chữ), ẩn với trình đọc màn hình. */
function cadao_icon_so_lieu( string $loai ): string {
	$net = array(
		'doc'       => '<path d="M1.5 12s3.8-7 10.5-7 10.5 7 10.5 7-3.8 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/>',
		'chia_se'   => '<path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
		'trich_dan' => '<path d="M10 7H6a2 2 0 0 0-2 2v3h5v5H4"/><path d="M20 7h-4a2 2 0 0 0-2 2v3h5v5h-5"/>',
	);
	return '<svg class="cadao-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $net[ $loai ] . '</svg>';
}

/** HTML dòng số liệu ('' nếu chưa số nào đủ ngưỡng). $chi: chỉ các loại này (danh sách: chỉ 'doc'). */
function cadao_html_so_lieu( int $id, array $chi = array( 'doc', 'chia_se', 'trich_dan' ), string $lop = '' ): string {
	$so   = cadao_so_lieu_bai( $id );
	$nhan = array( 'doc' => 'lượt đọc', 'chia_se' => 'lượt chia sẻ', 'trich_dan' => 'lượt trích dẫn' );
	$muc  = array();
	foreach ( $chi as $k ) {
		if ( $so[ $k ] >= CADAO_NGUONG_SO_LIEU[ $k ] ) {
			$day   = number_format_i18n( $so[ $k ] ) . ' ' . $nhan[ $k ];
			$muc[] = '<span class="cadao-so" title="' . esc_attr( $day ) . '">' . cadao_icon_so_lieu( $k ) . '<span aria-hidden="true">' . esc_html( cadao_so_gon( $so[ $k ] ) ) . '</span><span class="screen-reader-text">' . esc_html( $day ) . '</span></span>';
		}
	}
	return $muc ? '<p class="cadao-so-lieu' . ( $lop ? ' ' . esc_attr( $lop ) : '' ) . '">' . implode( '', $muc ) . '</p>' : '';
}

// Tiêu đề bài: trang bài → cả ba số ngay dưới tiêu đề; trong danh sách (vòng lặp bài) → chỉ 👁
add_filter(
	'render_block_core/post-title',
	static function ( $html, $khoi, $the ) {
		$id = (int) ( $the->context['postId'] ?? 0 );
		if ( ! $id || CADAO_LOAI_BAI !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
			return $html;
		}
		$chinh = is_singular( CADAO_LOAI_BAI ) && get_queried_object_id() === $id && empty( $the->context['queryId'] );
		return $html . ( $chinh ? cadao_html_so_lieu( $id ) : cadao_html_so_lieu( $id, array( 'doc' ), 'cadao-so-lieu-ds' ) );
	},
	10,
	3
);
