<?php
/**
 * Bộ kiểm PHP thuần của V2 (không cần WordPress, vài giây): `php wordpress/v2/tests/don-vi/chay.php`
 * hoặc `npm run test:v2`. Mỗi tệp kiem-*.php trong thư mục này gọi kt() cho từng điều kiện.
 * Dữ liệu tổng hợp — không chứa nội dung thật của nguồn (repo này công khai).
 */

declare(strict_types=1);

const V2_GOC = __DIR__ . '/../..';

require V2_GOC . '/plugins/ca-dao/src/tu-nap.php';

$GLOBALS['v2_dat']  = 0;
$GLOBALS['v2_hong'] = 0;

function kt( string $ten, bool $dk, mixed $chi_tiet = null ): void {
	if ( $dk ) {
		++$GLOBALS['v2_dat'];
		echo "ĐẠT   $ten\n";
		return;
	}
	++$GLOBALS['v2_hong'];
	echo "HỎNG  $ten" . ( null !== $chi_tiet ? ' — ' . var_export( $chi_tiet, true ) : '' ) . "\n";
}

/** Mọi tệp PHP dưới một thư mục (đường dẫn dùng dấu /). */
function tep_php( string $thu_muc ): array {
	$ds = array();
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $thu_muc, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $tep ) {
		if ( 'php' === $tep->getExtension() ) {
			$ds[] = str_replace( '\\', '/', $tep->getPathname() );
		}
	}
	sort( $ds );
	return $ds;
}

$bat_dau = microtime( true );
foreach ( glob( __DIR__ . '/kiem-*.php' ) as $tep ) {
	echo "\n# " . basename( $tep, '.php' ) . "\n";
	require $tep;
}
$giay = round( microtime( true ) - $bat_dau, 2 );
$tong = $GLOBALS['v2_dat'] + $GLOBALS['v2_hong'];
echo "\nKẾT QUẢ: " . ( $GLOBALS['v2_hong'] ? "HỎNG {$GLOBALS['v2_hong']} / $tong" : "ĐẠT $tong" ) . " · {$giay} giây · PHP " . PHP_VERSION . "\n";
exit( $GLOBALS['v2_hong'] ? 1 : 0 );
