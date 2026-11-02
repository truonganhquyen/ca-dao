<?php
/**
 * Cú pháp mọi tệp PHP, cây thư mục theo docs/26 mục 3, không gian tên khớp đường dẫn, số phiên bản khớp nhau.
 */

declare(strict_types=1);

$plugin = V2_GOC . '/plugins/ca-dao';
$theme  = V2_GOC . '/themes/ca-dao';

// Cú pháp: token_get_all(TOKEN_PARSE) ném ParseError khi tệp sai cú pháp
$loi_cu_phap = array();
foreach ( array_merge( tep_php( $plugin ), tep_php( $theme ), tep_php( V2_GOC . '/tests' ) ) as $tep ) {
	try {
		token_get_all( (string) file_get_contents( $tep ), TOKEN_PARSE );
	} catch ( ParseError $e ) {
		$loi_cu_phap[] = $tep . ':' . $e->getLine() . ' ' . $e->getMessage();
	}
}
kt( 'mọi tệp PHP của plugin, theme, kiểm thử đúng cú pháp', array() === $loi_cu_phap, $loi_cu_phap );

$thieu = array();
foreach ( array( 'ca-dao.php', 'src/tu-nap.php', 'src/MienDuLieu', 'src/KhoLuu', 'src/DichVu', 'src/GiaoTiep/Rest', 'src/GiaoTiep/Khoi', 'src/GiaoTiep/Cli', 'src/GiaoTiep/QuanTri', 'src/HaTang', 'khoi', 'quan-tri/src', 'quan-tri/build', 'assets' ) as $duong ) {
	if ( ! file_exists( "$plugin/$duong" ) ) {
		$thieu[] = $duong;
	}
}
kt( 'cây thư mục plugin đúng docs/26 mục 3', array() === $thieu, $thieu );
$thieu = array_values( array_filter( array( 'style.css', 'theme.json', 'functions.php', 'templates/index.html', 'parts/dau-trang.html', 'parts/chan-trang.html' ), static fn( $t ) => ! file_exists( "$theme/$t" ) ) );
kt( 'theme khối có style.css, theme.json, functions.php, templates/index.html, phần đầu / chân trang', array() === $thieu, $thieu );

// PSR-4: Phongdao\A\B nằm ở src/A/B.php, mỗi tệp một khai báo cùng tên tệp
$lech = array();
foreach ( tep_php( "$plugin/src" ) as $tep ) {
	if ( str_ends_with( $tep, '/tu-nap.php' ) ) {
		continue;
	}
	$nguon = (string) file_get_contents( $tep );
	$tuong_doi = substr( $tep, strlen( str_replace( '\\', '/', "$plugin/src/" ) ) );
	$mong = 'Phongdao\\' . str_replace( '/', '\\', dirname( $tuong_doi ) );
	preg_match( '/^namespace\s+([^;]+);/m', $nguon, $ns );
	preg_match( '/^(?:final\s+|abstract\s+)?(?:class|interface|trait|enum)\s+(\w+)/m', $nguon, $ten );
	if ( ( $ns[1] ?? '' ) !== $mong || ( $ten[1] ?? '' ) !== basename( $tep, '.php' ) ) {
		$lech[] = $tuong_doi;
	}
}
kt( 'không gian tên và tên lớp khớp đường dẫn (PSR-4)', array() === $lech, $lech );

// Module khai báo trong KhoiDong đều có tệp và cài đặt HaTang\Module
$khoi_dong = (string) file_get_contents( "$plugin/src/HaTang/KhoiDong.php" );
preg_match_all( '/^\s*([\w\\\\]+)::class,$/m', $khoi_dong, $m );
$module_hong = array();
foreach ( $m[1] as $lop ) {
	$duong = str_starts_with( $lop, 'GiaoTiep\\' ) ? $lop : 'HaTang\\' . $lop;
	$tep = "$plugin/src/" . str_replace( '\\', '/', $duong ) . '.php';
	if ( ! is_file( $tep ) || ! preg_match( '/implements\s+[\\\\\w]*Module\b/', (string) file_get_contents( $tep ) ) ) {
		$module_hong[] = $lop;
	}
}
kt( 'KhoiDong khai báo ≥ 5 module, mỗi module có tệp và cài đặt HaTang\Module', count( $m[1] ) >= 5 && array() === $module_hong, array( 'so' => count( $m[1] ), 'hong' => $module_hong ) );

// Phiên bản: hằng CADAO_PHIEN_BAN = dòng Version của plugin; theme có Version; cùng yêu cầu PHP 8.3
$tep_plugin = (string) file_get_contents( "$plugin/ca-dao.php" );
$tep_theme  = (string) file_get_contents( "$theme/style.css" );
preg_match( "/CADAO_PHIEN_BAN\s*=\s*'([0-9.]+)'/", $tep_plugin, $hang );
preg_match( '/^\s*\*\s*Version:\s*([0-9.]+)/m', $tep_plugin, $dong );
preg_match( '/^Version:\s*([0-9.]+)/m', $tep_theme, $theme_ver );
kt( 'CADAO_PHIEN_BAN khớp dòng Version: của plugin', isset( $hang[1], $dong[1] ) && $hang[1] === $dong[1], array( $hang[1] ?? null, $dong[1] ?? null ) );
kt( 'theme có dòng Version:', isset( $theme_ver[1] ) );
kt( 'plugin và theme cùng yêu cầu PHP 8.3 (máy chủ chạy 8.5)', str_contains( $tep_plugin, 'Requires PHP:      8.3' ) && (bool) preg_match( '/^Requires PHP:\s*8\.3$/m', $tep_theme ) );
kt( 'theme.json là JSON hợp lệ, phiên bản 3', 3 === ( json_decode( (string) file_get_contents( "$theme/theme.json" ), true )['version'] ?? null ) );
$dong_functions = count( file( "$theme/functions.php" ) );
kt( 'functions.php của theme dưới 50 dòng (docs/26 mục 6)', $dong_functions < 50, $dong_functions );
