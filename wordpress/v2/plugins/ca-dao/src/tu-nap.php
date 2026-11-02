<?php
/**
 * Tự nạp lớp Phongdao\… từ src/ (PSR-4, không cần Composer): Phongdao\MienDuLieu\BoDau → src/MienDuLieu/BoDau.php.
 * Dùng chung cho plugin và bộ kiểm PHP thuần (wordpress/v2/tests/don-vi/), nên tệp này không gọi hàm WordPress.
 */

spl_autoload_register(
	static function ( string $lop ): void {
		if ( ! str_starts_with( $lop, 'Phongdao\\' ) ) {
			return;
		}
		$tep = __DIR__ . '/' . str_replace( '\\', '/', substr( $lop, strlen( 'Phongdao\\' ) ) ) . '.php';
		if ( is_file( $tep ) ) {
			require $tep;
		}
	}
);
