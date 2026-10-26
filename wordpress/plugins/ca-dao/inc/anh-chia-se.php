<?php
/**
 * Ảnh chia sẻ 1200×630 cho từng bài (T-050, docs/20 FR-4.2; gộp T-060): lời (tối đa 4 dòng), thể loại, dòng mức kiểm
 * (D-072), tên miền. Vẽ bằng GD trên máy chủ với phông TTF tự lưu (Literata, Be Vietnam Pro — OFL, cắt còn Latin và
 * tiếng Việt, assets/phong/). Lưu ở uploads/cadao-anh/<id>-<dấu băm>.png: dấu băm đổi khi lời, thể loại, mức kiểm hay
 * tên miền đổi, nên ảnh tự sinh lại; ảnh cũ của bài bị xoá. Máy không có GD/FreeType thì không có ảnh riêng (trang
 * vẫn chạy, thẻ og:image dùng ảnh chung nếu có).
 */

defined( 'ABSPATH' ) || exit;

const CADAO_ANH_RONG      = 1200;
const CADAO_ANH_CAO       = 630;
const CADAO_ANH_PHIEN_BAN = 1; // đổi khi đổi cách vẽ: mọi ảnh sinh lại

/**
 * Vẽ được chữ trên máy chủ này không: thử đo chữ thật với phông của dự án (có bản GD tự báo có FreeType nhưng không
 * vẽ được chữ, như PHP của WordPress Playground).
 */
function cadao_anh_ve_duoc(): bool {
	static $duoc = null;
	if ( null === $duoc ) {
		$duoc = function_exists( 'imagecreatetruecolor' ) && function_exists( 'imagettfbbox' ) && function_exists( 'imagepng' )
			&& false !== @imagettfbbox( 12, 0, cadao_anh_phong( 'bvp-400' ), 'Ngưỡng' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- không vẽ được thì báo false
	}
	return $duoc;
}

function cadao_anh_phong( string $ten ): string {
	return CADAO_THU_MUC . '/assets/phong/' . $ten . '.ttf';
}

/**
 * Nội dung của ảnh một bài.
 *
 * @return array{loai: string, dong: string[], con: bool, luc_bat: bool, muc_kiem: string, ten_mien: string}|null
 */
function cadao_anh_noi_dung( WP_Post $bai ): ?array {
	$dong = array_values( array_filter( explode( "\n", cadao_loi_cac_khoi_tho( $bai->post_content )[0] ?? '' ), static fn( $d ) => '' !== trim( $d ) ) );
	if ( ! $dong ) {
		return null;
	}
	$tho = get_the_terms( $bai, 'the_tho' );
	$dc  = function_exists( 'cadao_chu_doi_chieu' ) ? cadao_chu_doi_chieu( $bai->ID ) : '';
	return array(
		'loai'     => cadao_ten_the_loai( $bai ),
		'dong'     => array_map( static fn( $d ) => cadao_nfc( trim( $d ) ), array_slice( $dong, 0, 4 ) ),
		'con'      => count( $dong ) > 4,
		'luc_bat'  => $tho && ! is_wp_error( $tho ) && in_array( 'luc-bat', wp_list_pluck( $tho, 'slug' ), true ),
		'muc_kiem' => '' !== $dc ? 'Lời đã đối chiếu với ' . $dc : CADAO_MUC_KIEM_CHUA,
		'ten_mien' => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
	);
}

/** Thư mục, địa chỉ lưu ảnh. */
function cadao_anh_thu_muc(): array {
	$u = wp_upload_dir( null, false );
	return array( trailingslashit( $u['basedir'] ) . 'cadao-anh', trailingslashit( $u['baseurl'] ) . 'cadao-anh' );
}

/** Rộng của chữ (px) với phông, cỡ. */
function cadao_anh_rong( string $chu, string $phong, float $co ): int {
	$h = imagettfbbox( $co, 0, $phong, $chu );
	return $h ? (int) ( $h[2] - $h[0] ) : 0;
}

/** Cắt chữ cho vừa bề rộng, thêm "…". */
function cadao_anh_cat( string $chu, string $phong, float $co, int $toi_da ): string {
	if ( cadao_anh_rong( $chu, $phong, $co ) <= $toi_da ) {
		return $chu;
	}
	while ( mb_strlen( $chu ) > 1 && cadao_anh_rong( $chu . '…', $phong, $co ) > $toi_da ) {
		$chu = mb_substr( $chu, 0, -1 );
	}
	return rtrim( $chu ) . '…';
}

/**
 * Vẽ ảnh vào tệp $tep.
 *
 * @param array{loai: string, dong: string[], con: bool, luc_bat: bool, muc_kiem: string, ten_mien: string} $nd
 */
function cadao_anh_ve( array $nd, string $tep ): bool {
	if ( ! cadao_anh_ve_duoc() ) {
		return false;
	}
	$w   = CADAO_ANH_RONG;
	$h   = CADAO_ANH_CAO;
	$le  = 88;
	$anh = imagecreatetruecolor( $w, $h );
	$mau = static fn( string $hex ) => imagecolorallocate( $anh, hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
	$giay = $mau( '#fbf8f1' );
	$muc  = $mau( '#22201c' );
	$son  = $mau( '#9e2a22' );
	$nhat = $mau( '#5f594f' );
	$ke   = $mau( '#ddd6c8' );
	imagefilledrectangle( $anh, 0, 0, $w, $h, $giay );
	imagefilledrectangle( $anh, 0, 0, 15, $h, $son );
	$tho  = cadao_anh_phong( 'literata-400' );
	$dam  = cadao_anh_phong( 'bvp-600' );
	$vua  = cadao_anh_phong( 'bvp-400' );
	$rong = $w - 2 * $le;

	// Thể loại
	imagettftext( $anh, 21, 0, $le, 108, $son, $dam, mb_strtoupper( $nd['loai'] ) );

	// Lời: cỡ lớn nhất (36–27) để mọi dòng vừa một hàng; dòng quá dài thì cắt "…"
	$thut = $nd['luc_bat'] ? 44 : 0;
	$co   = 36.0;
	while ( $co > 27 && max( array_map( static fn( $d ) => cadao_anh_rong( $d, $tho, $co ), $nd['dong'] ) ) > $rong - $thut ) {
		$co -= 1.0;
	}
	$dong = $nd['dong'];
	if ( $nd['con'] ) {
		$dong[ count( $dong ) - 1 ] .= ' …';
	}
	$cao_dong = (int) round( $co * 1.33 * 1.6 ); // điểm → px (96 dpi), giãn dòng 1,6
	$dau      = (int) ( 150 + ( ( $h - 150 - 150 ) - $cao_dong * count( $dong ) ) / 2 + $co * 1.33 );
	foreach ( $dong as $i => $d ) {
		$x = $le + ( $nd['luc_bat'] && 0 === $i % 2 && count( $dong ) > 1 ? $thut : 0 );
		imagettftext( $anh, $co, 0, $x, $dau + $i * $cao_dong, $muc, $tho, cadao_anh_cat( $d, $tho, $co, $w - $le - $x ) );
	}

	// Chân: kẻ mảnh, dòng mức kiểm (trái), tên miền (phải)
	imagefilledrectangle( $anh, $le, $h - 128, $w - $le, $h - 127, $ke );
	$ten   = $nd['ten_mien'];
	$r_ten = cadao_anh_rong( $ten, $dam, 20 );
	imagettftext( $anh, 20, 0, $w - $le - $r_ten, $h - 72, $son, $dam, $ten );
	imagettftext( $anh, 17, 0, $le, $h - 72, $nhat, $vua, cadao_anh_cat( $nd['muc_kiem'], $vua, 17, $rong - $r_ten - 40 ) );

	wp_mkdir_p( dirname( $tep ) );
	$ok = imagepng( $anh, $tep, 9 );
	imagedestroy( $anh );
	return $ok;
}

/**
 * Ảnh chia sẻ của một bài: tạo nếu chưa có (hoặc nội dung đã đổi), xoá ảnh cũ của bài.
 *
 * @return array{url: string, rong: int, cao: int}|null
 */
function cadao_anh_chia_se( WP_Post $bai ): ?array {
	$nd = cadao_anh_noi_dung( $bai );
	if ( ! $nd || ! cadao_anh_ve_duoc() ) {
		return null;
	}
	list( $thu_muc, $url ) = cadao_anh_thu_muc();
	$ten = $bai->ID . '-' . substr( md5( wp_json_encode( $nd ) . CADAO_ANH_PHIEN_BAN ), 0, 10 ) . '.png';
	$tep = $thu_muc . '/' . $ten;
	if ( ! file_exists( $tep ) ) {
		foreach ( (array) glob( $thu_muc . '/' . $bai->ID . '-*.png' ) as $cu ) {
			wp_delete_file( $cu );
		}
		if ( ! cadao_anh_ve( $nd, $tep ) ) {
			return null;
		}
	}
	return array( 'url' => $url . '/' . $ten, 'rong' => CADAO_ANH_RONG, 'cao' => CADAO_ANH_CAO );
}

/** Ảnh chung của site (trang chủ, danh sách): tên dự án, lời giới thiệu ngắn. */
function cadao_anh_chung(): ?array {
	if ( ! cadao_anh_ve_duoc() ) {
		return null;
	}
	$nd  = array(
		'loai'     => 'Kho tàng',
		'dong'     => array( 'Ca dao, tục ngữ Việt Nam', 'đọc, hiểu, trích dẫn' ),
		'con'      => false,
		'luc_bat'  => false,
		'muc_kiem' => 'Lời giữ nguyên văn · có chú thích từ khó, dị bản',
		'ten_mien' => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
	);
	list( $thu_muc, $url ) = cadao_anh_thu_muc();
	$ten = 'chung-' . substr( md5( wp_json_encode( $nd ) . CADAO_ANH_PHIEN_BAN ), 0, 10 ) . '.png';
	if ( ! file_exists( $thu_muc . '/' . $ten ) && ! cadao_anh_ve( $nd, $thu_muc . '/' . $ten ) ) {
		return null;
	}
	return array( 'url' => $url . '/' . $ten, 'rong' => CADAO_ANH_RONG, 'cao' => CADAO_ANH_CAO );
}

// Bài bị xoá hẳn: xoá ảnh
add_action(
	'before_delete_post',
	static function ( $id ) {
		if ( CADAO_LOAI_BAI === get_post_type( $id ) ) {
			foreach ( (array) glob( cadao_anh_thu_muc()[0] . '/' . (int) $id . '-*.png' ) as $cu ) {
				wp_delete_file( $cu );
			}
		}
	}
);
