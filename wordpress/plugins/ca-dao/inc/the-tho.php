<?php
/**
 * Thể thơ (T-084, docs/25 mục 4.1): máy nhận thể theo số âm tiết từng dòng — lục bát, song thất lục bát, câu 3 / 4 / 5
 * chữ, câu một dòng, tự do. Đếm âm tiết thuần cơ học (như số chữ trên dòng), không phải nhận định văn hóa; lưu vào
 * phân loại "Thể thơ" của bài để lọc danh sách. Theme dùng để trình bày theo thể.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_THE_THO = array(
	'luc-bat'           => 'Lục bát',
	'song-that-luc-bat' => 'Song thất lục bát',
	'ba-chu'            => 'Câu ba chữ',
	'bon-chu'           => 'Câu bốn chữ',
	'nam-chu'           => 'Câu năm chữ',
	'mot-dong'          => 'Câu một dòng',
	'tu-do'             => 'Tự do',
);

add_action(
	'init',
	static function () {
		register_taxonomy(
			'the_tho',
			CADAO_LOAI_BAI,
			array(
				'labels'            => array( 'name' => 'Thể thơ', 'singular_name' => 'Thể thơ' ),
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => false,
				'meta_box_cb'       => false, // máy tự nhận, không cần ô trong trình soạn
				'rewrite'           => array( 'slug' => 'the-tho' ),
			)
		);
	},
	11
);

/** Số âm tiết của một dòng (đếm theo khoảng trắng, bỏ dấu câu đứng riêng). */
function cadao_so_am_tiet( string $dong ): int {
	return '' === trim( $dong ) ? 0 : count( preg_split( '/\s+/u', cadao_chuan_loi( $dong ), -1, PREG_SPLIT_NO_EMPTY ) );
}

/**
 * Thể thơ của một lời.
 *
 * @return string khóa của CADAO_THE_THO
 */
function cadao_the_tho( string $loi ): string {
	$so = array_values( array_filter( array_map( 'cadao_so_am_tiet', explode( "\n", $loi ) ) ) );
	$n  = count( $so );
	if ( $n <= 1 ) {
		return 'mot-dong';
	}
	// Song thất lục bát: khổ 7 – 7 – 6 – 8 (từ 3/4 số khổ). Xét trước lục bát vì khổ có cặp 6 – 8.
	if ( $n >= 4 ) {
		$kho  = intdiv( $n, 4 );
		$dung = 0;
		for ( $i = 0; $i < $kho; $i++ ) {
			$dung += array( 7, 7, 6, 8 ) === array_slice( $so, 4 * $i, 4 ) ? 1 : 0;
		}
		if ( $dung * 4 >= $kho * 3 ) {
			return 'song-that-luc-bat';
		}
	}
	// Lục bát: các cặp dòng 6 – 8 (từ 3/4 số cặp)
	$cap  = intdiv( $n, 2 );
	$dung = 0;
	for ( $i = 0; $i < $cap; $i++ ) {
		$dung += ( 6 === $so[ 2 * $i ] && 8 === $so[ 2 * $i + 1 ] ) ? 1 : 0;
	}
	if ( $dung * 4 >= $cap * 3 ) {
		return 'luc-bat';
	}
	// Câu ngắn đều: từ 80% số dòng có cùng 3, 4 hoặc 5 âm tiết
	foreach ( array( 3 => 'ba-chu', 4 => 'bon-chu', 5 => 'nam-chu' ) as $k => $the ) {
		if ( count( array_filter( $so, static fn( $x ) => $x === $k ) ) * 5 >= $n * 4 ) {
			return $the;
		}
	}
	return 'tu-do';
}

/** Gắn thể thơ cho bài theo lời chính (khi lưu bài). */
function cadao_gan_the_tho( int $id ): void {
	$bai = get_post( $id );
	if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type ) {
		return;
	}
	$loi = cadao_loi_cac_khoi_tho( $bai->post_content )[0] ?? '';
	if ( '' === $loi ) {
		wp_set_object_terms( $id, array(), 'the_tho' );
		return;
	}
	$the = cadao_the_tho( $loi );
	if ( ! term_exists( $the, 'the_tho' ) ) {
		wp_insert_term( CADAO_THE_THO[ $the ], 'the_tho', array( 'slug' => $the ) );
	}
	wp_set_object_terms( $id, $the, 'the_tho' );
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id ) {
		if ( ! wp_is_post_revision( $id ) && ! wp_is_post_autosave( $id ) ) {
			cadao_gan_the_tho( (int) $id );
		}
	},
	25
);

// Bài đã có: gắn thể thơ một lần (làm dần, mỗi lượt 300 bài)
add_action(
	'cadao_thu_thap',
	static function () {
		global $wpdb;
		$sau = (int) get_option( 'cadao_the_tho_sau', 0 );
		if ( $sau < 0 ) {
			return; // đã xong
		}
		$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit') AND ID > %d ORDER BY ID LIMIT 300", CADAO_LOAI_BAI, $sau ) );
		foreach ( $ds as $id ) {
			cadao_gan_the_tho( (int) $id );
		}
		update_option( 'cadao_the_tho_sau', $ds ? (int) end( $ds ) : -1, false );
	},
	7
);
