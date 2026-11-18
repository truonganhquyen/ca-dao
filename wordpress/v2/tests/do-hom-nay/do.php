<?php
/**
 * Đo tốc độ màn Hôm nay và màn Số liệu trên kho thử lớn (T-217): `npm run do:hom-nay:v2 [-- 16000]` (Playground, SQLite). Đổ nhanh bằng
 * SQL: N tác phẩm đã đăng (lời, chú thích có hồ sơ nguồn, dòng tóm tắt), 8 hồ sơ nguồn thiếu giấy phép, N/2 câu kho mới,
 * 40 thẻ Hộp việc. Đo từng phần và cả màn: mili giây (trung vị 3 lần), số câu SQL. Ghi đè dữ liệu site đang chạy: CHỈ
 * dùng trên site thử.
 */

declare(strict_types=1);

use Phongdao\DichVu\TomTat\TomTat;
use Phongdao\GiaoTiep\QuanTri\BanLamViec;
use Phongdao\GiaoTiep\QuanTri\SoLieu;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;

global $wpdb;
$n   = defined( 'DO_HOM_NAY_CO' ) ? (int) DO_HOM_NAY_CO : 16000;
$ra  = array();
$ghi = static function ( string $dong ) use ( &$ra ): void {
	$ra[] = $dong;
};
$quan_tri = get_users( array( 'role' => 'administrator', 'number' => 1 ) )[0] ?? null;
wp_set_current_user( $quan_tri ? $quan_tri->ID : 1 );

// ---------- Đổ dữ liệu ----------
$t0 = microtime( true );
$wpdb->query( 'START TRANSACTION' );
$ho_so = array();
for ( $i = 1; $i <= 8; $i++ ) {
	$ho_so[] = (int) wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => 'Hồ sơ đo ' . $i ) );
}
$tl = term_exists( 'ca-dao', KhaiBao::THE_LOAI ) ?: wp_insert_term( 'Ca dao', KhaiBao::THE_LOAI, array( 'slug' => 'ca-dao' ) );
$tt = (int) $tl['term_taxonomy_id'];
$dau = (int) $wpdb->get_var( "SELECT COALESCE(MAX(ID),0) FROM {$wpdb->posts}" ) + 1;
$bay = gmdate( 'Y-m-d H:i:s' );
foreach ( array_chunk( range( 0, $n - 1 ), 400 ) as $lo ) {
	$p = $m = $r = $t = array();
	foreach ( $lo as $i ) {
		$id  = $dau + $i;
		$p[] = $wpdb->prepare( '(%d, 1, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', $id, $bay, $bay, '', 'Bài đo ' . $i, '', 'publish', 'bai-do-' . $i, '', '', $bay, $bay, '', KhaiBao::TAC_PHAM );
		$loi = serialize( array( 'dong' => array( "Dòng một của bài đo $i", "Dòng hai của bài đo $i" ), 'nguoi_lam' => 'may_chep' ) );
		$m[] = $wpdb->prepare( '(%d, %s, %s)', $id, '_phongdao_loi', $loi );
		if ( 0 === $i % 2 ) {
			$ct  = serialize( array( 'muc' => array( array( 'tu' => 'đo', 'loi_giai' => "Lời giải đo $i", 'nguoi_lam' => 'may_chep', 'nguon' => array( 'ho_so' => $ho_so[ $i % 8 ] ) ) ) ) );
			$m[] = $wpdb->prepare( '(%d, %s, %s)', $id, '_phongdao_chu_thich', $ct );
		}
		$m[] = $wpdb->prepare( '(%d, %s, %s)', $id, '_phongdao_nguon_may', serialize( array( 'nguon_id' => 'do', 'ma_goc' => (string) $i ) ) );
		$r[] = $wpdb->prepare( '(%d, %d)', $id, $tt );
		$t[] = $wpdb->prepare( '(%d, %s, %s, %d, %d, %s, %d, %s, %d, %d, %s)', $id, 'ca-dao', 'publish', 1, 0, ',y_nghia,tu_kho,doi_chieu,', 0, 'do', 0, $i % 50, $bay );
	}
	$wpdb->query( "INSERT INTO {$wpdb->posts} (ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type) VALUES " . implode( ',', $p ) ); // phpcs:ignore
	$wpdb->query( "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES " . implode( ',', $m ) ); // phpcs:ignore
	$wpdb->query( "INSERT INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id) VALUES " . implode( ',', $r ) ); // phpcs:ignore
	$wpdb->query( 'INSERT INTO ' . TomTat::bang() . ' (post_id, the_loai, trang_thai, cap, uu_tien_doc, thieu, co, nguon_may, nguon_nguoi, doc_7, sua_luc) VALUES ' . implode( ',', $t ) ); // phpcs:ignore
}
$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET count = count + %d WHERE term_taxonomy_id = %d", $n, $tt ) );
foreach ( array_chunk( range( 1, intdiv( $n, 2 ) ), 400 ) as $lo ) {
	$k = array();
	foreach ( $lo as $i ) {
		$k[] = $wpdb->prepare( '(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', 'do', 'kho-do-' . $i, 'https://example.org/do/' . $i, 'ca_dao', 'Câu kho đo ' . $i, "Câu kho đo $i dòng một\nCâu kho đo $i dòng hai", '{}', $bay, 'moi', gmdate( 'Y-m-d' ) );
	}
	$wpdb->query( 'INSERT INTO ' . Kho::bang() . ' (nguon_id, ma_goc, url, loai, tieu_de, loi, du_lieu, ngay_lay, trang_thai, doc_trang) VALUES ' . implode( ',', $k ) ); // phpcs:ignore
}
for ( $i = 1; $i <= 40; $i++ ) {
	Viec::tao( array( 'loai' => 0 === $i % 2 ? 'goi_y' : 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $dau + $i, 'noi_dung' => array( 'loai' => 'chu_de', 'ma' => 'lao-dong', 'chu' => 'Nháp đo.' ), 'nguoi_tao' => 'may' ) );
}
$wpdb->query( 'COMMIT' );
wp_cache_flush();
$ghi( '# Đo màn Hôm nay · Playground (SQLite) · PHP ' . PHP_VERSION . ' · ' . gmdate( 'Y-m-d H:i' ) . ' UTC' );
$ghi( '' );
$ghi( sprintf( 'Kho thử: %d tác phẩm đã đăng, %d câu kho mới, 8 hồ sơ nguồn thiếu giấy phép, 40 thẻ Hộp việc. Đổ dữ liệu: %.1f giây.', $n, intdiv( $n, 2 ), microtime( true ) - $t0 ) );
$ghi( '' );

// ---------- Đo ----------
$do = static function ( callable $f ): array {
	global $wpdb;
	$ms = array();
	$sql = 0;
	for ( $l = 0; $l < 3; $l++ ) {
		wp_cache_flush();
		delete_transient( \Phongdao\GiaoTiep\Seo\Module::DS_LAP );
		( new ReflectionProperty( BanLamViec::class, 'nho' ) )->setValue( null, array() );
		$q0 = $wpdb->num_queries;
		$a  = microtime( true );
		$f();
		$ms[]  = ( microtime( true ) - $a ) * 1000;
		$sql   = $wpdb->num_queries - $q0;
	}
	sort( $ms );
	return array( $ms[1], $sql );
};
$rieng = static function ( string $ham, array $tham = array(), string $lop = BanLamViec::class ): callable {
	return static function () use ( $ham, $tham, $lop ) {
		if ( method_exists( $lop, $ham ) ) {
			$m = new ReflectionMethod( $lop, $ham );
			$m->setAccessible( true );
			return $m->invokeArgs( null, $tham );
		}
		return null;
	};
};
$ghi( '| Phần | ms | SQL |' );
$ghi( '|---|---:|---:|' );
$bay_gio = time();
foreach ( array(
	'Cả màn Hôm nay (BanLamViec::hien)'         => static fn() => BanLamViec::hien( $bay_gio ),
	'Việc nên làm (viec_nen_lam)'               => $rieng( 'viec_nen_lam', array( $bay_gio ) ),
	'Ba việc chính (so_ba_dong)'                => $rieng( 'so_ba_dong' ),
	'Tuần này (so_tuan)'                        => $rieng( 'so_tuan', array( $bay_gio ) ),
	'Đang làm dở (dang_lam)'                    => $rieng( 'dang_lam', array( get_current_user_id() ) ),
	'Cả màn Số liệu (SoLieu::hien)'             => static fn() => SoLieu::hien( $bay_gio ),
	'Số liệu: kho theo cấp (khoi_kho)'          => $rieng( 'khoi_kho', array(), SoLieu::class ),
	'Số liệu: ngoại lệ dữ liệu (khoi_ngoai_le)' => $rieng( 'khoi_ngoai_le', array(), SoLieu::class ),
	'Số liệu: hồ sơ nguồn chưa rõ'              => $rieng( 'nguon_chua_ro', array(), SoLieu::class ),
) as $nhan => $f ) {
	[ $ms, $sql ] = $do( $f );
	$ghi( sprintf( '| %s | %.0f | %d |', $nhan, $ms, $sql ) );
}
file_put_contents( '/repo/tests/.do-hom-nay.txt', implode( "\n", $ra ) . "\n" );
