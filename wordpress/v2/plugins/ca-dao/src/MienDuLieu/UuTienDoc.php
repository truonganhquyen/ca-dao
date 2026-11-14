<?php

namespace Phongdao\MienDuLieu;

/** Thứ tự đọc theo phần đang công khai, không phải xác nhận sự thật biên tập (D-112). */
final class UuTienDoc {

	public static function tinh( array $phan, string $loai ): int {
		$chu = static fn( string $p ): bool => '' !== trim( (string) ( $phan[ $p ]['chu'] ?? '' ) );
		$giai = $chu( 'y_nghia' ) || $chu( 'giai_sach' );
		if ( 'cau-do' === $loai ) {
			return $chu( 'dap_an' ) ? ( $giai ? 2 : 1 ) : 0;
		}
		if ( $giai || $chu( 'nghia_den' ) || $chu( 'nghia_bong' ) || $chu( 'hinh_anh' ) || $chu( 'boi_canh' ) ) {
			return 2;
		}
		foreach ( $phan['chu_thich']['muc'] ?? array() as $m ) {
			if ( '' !== trim( (string) ( $m['loi_giai'] ?? '' ) ) ) {
				return 1;
			}
		}
		return 0;
	}
}
