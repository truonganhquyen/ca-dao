<?php
/**
 * Đo tìm kiếm trên kho thử lớn (docs/22 T-181): `npm run do:tim:v2` (Playground, SQLite) hoặc
 * `php wordpress/v2/tests/do-tim/mysql.php …` (MySQL/MariaDB riêng để đo). Mỗi cỡ kho, mỗi truy vấn đại diện, ba cách:
 *  - cũ: 500 ứng viên đầu bảng chưa sắp rồi xếp (cách trước T-181);
 *  - lấy hết: mọi ứng viên kèm chữ rồi xếp (ChiMuc::xep) — kết quả ĐÚNG để so;
 *  - mới: ChiMucTim::tim (cờ CSDL + đọc chữ có chọn lọc).
 * Ghi: số ứng viên, tổng hiện ra, 20 kết quả đầu có đúng thứ tự không, bài mới nhất có lọt không, thời gian (trung vị
 * 5 lần), số câu SQL, bộ nhớ đỉnh. Ghi đè bảng chỉ mục tìm của site đang chạy: CHỈ dùng trên site thử.
 */

declare(strict_types=1);

use Phongdao\DichVu\ChiMucTim\ChiMucTim;
use Phongdao\MienDuLieu\ChiMuc;

require_once __DIR__ . '/kho-thu.php';

/**
 * @param int[]                  $co_kho các cỡ kho, tăng dần
 * @param callable(string):void  $ra    ghi một dòng báo cáo
 * @param callable(array):void   $tu_vung đặt từ vựng âm tiết cho gợi ý gõ sai
 */
function do_tim_chay( array $co_kho, callable $ra, callable $tu_vung, string $moi_truong ): bool {
	global $wpdb;
	$bang = ChiMucTim::bang();
	$wpdb->query( "DELETE FROM $bang" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$kho  = do_tim_kho( max( $co_kho ) );
	$da   = 0;
	$tv   = array();
	$dung = true;
	$ra( "# Đo tìm kiếm · $moi_truong · PHP " . PHP_VERSION . ' · ' . gmdate( 'Y-m-d H:i' ) . ' UTC' );
	$ra( '' );
	$ra( 'Thời gian: trung vị 5 lần, mili giây. Đúng: tổng và 20 kết quả đầu (đúng thứ tự) trùng cách "lấy hết". Mới nhất: bài mới nhất có cụm này nằm trong 20 kết quả đầu.' );
	foreach ( $co_kho as $n ) {
		// Thêm bài đến đủ n (lô 200 dòng mỗi câu INSERT)
		foreach ( array_chunk( array_slice( $kho, $da, $n - $da ), 200 ) as $lo ) {
			$gt = array();
			foreach ( $lo as [ $id, $chinh, $phu ] ) {
				$h = ChiMuc::dong_chi_muc( $chinh, $phu );
				if ( $h ) {
					$gt[] = $wpdb->prepare( '(%d, %s, %s, %s, %s)', $id, $h['chinh'], $h['chinh_kd'], $h['phu'], $h['phu_kd'] );
					foreach ( array_unique( explode( ' ', trim( $h['chinh_kd'] ) ) ) as $am ) {
						$tv[ $am ] = ( $tv[ $am ] ?? 0 ) + 1;
					}
				}
			}
			$wpdb->query( "INSERT INTO $bang (post_id, chinh, chinh_kd, phu, phu_kd) VALUES " . implode( ',', $gt ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$da = $n;
		$tu_vung( $tv );
		$tv_q = do_tim_truy_van( array_slice( $kho, 0, $n ) );
		$ra( '' );
		$ra( "## Kho $n bài (bài mới nhất làm mẫu: #{$tv_q['moi']})" );
		$ra( '' );
		$ra( '| Truy vấn | Ứng viên | Cũ: tổng · đúng · mới nhất · ms · SQL · MB | Lấy hết: ms · MB | Mới: tổng · đúng · ms · SQL · đọc chữ · MB |' );
		$ra( '|---|---:|---|---|---|' );
		foreach ( $tv_q['ds'] as $nhan => $q ) {
			$het = do_tim_do( static fn() => do_tim_cach( $q, null ) );
			$cu  = do_tim_do( static fn() => do_tim_cach( $q, 500 ) );
			$moi = do_tim_do( static fn() => ChiMucTim::tim( $q, 20 ) );
			$ds  = static fn( array $kq ): array => array_map( static fn( array $x ): array => array( $x['id'], $x['diem'], $x['roi'], $x['lien'] ), $kq['ds'] );
			$cu_dung  = $ds( $cu['kq'] ) === $ds( $het['kq'] ) && $cu['kq']['tong'] === $het['kq']['tong'];
			$moi_dung = $ds( $moi['kq'] ) === $ds( $het['kq'] ) && $moi['kq']['tong'] === $het['kq']['tong'];
			$dung     = $dung && $moi_dung;
			$co_moi   = in_array( $tv_q['moi'], array_column( $het['kq']['ds'], 'id' ), true );
			$cu_moi   = $co_moi ? ( in_array( $tv_q['moi'], array_column( $cu['kq']['ds'], 'id' ), true ) ? 'có' : '**thiếu**' ) : '—';
			$ra(
				sprintf(
					'| %s `%s` | %d | %d · %s · %s · %.1f · %d · %.1f | %.1f · %.1f | %d · %s · %.1f · %d · %d · %.1f |',
					$nhan,
					$q,
					$het['kq']['tong'],
					$cu['kq']['tong'],
					$cu_dung ? 'đúng' : '**sai**',
					$cu_moi,
					$cu['ms'],
					$cu['sql'],
					$cu['mb'],
					$het['ms'],
					$het['mb'],
					$moi['kq']['tong'],
					$moi_dung ? 'đúng' : '**SAI**',
					$moi['ms'],
					$moi['sql'],
					$moi['kq']['doc'],
					$moi['mb']
				)
			);
		}
		// Gõ sai: không ra kết quả nên trang thử thay từng âm tiết (cách cũ đếm bằng tìm 500 ứng viên, mới bằng COUNT)
		$sai = $tv_q['ds']['Gõ sai một chữ cái'];
		$g_c = do_tim_do( static fn() => do_tim_goi_y_cu( $sai ) );
		$g_m = do_tim_do( static fn() => ChiMucTim::goi_y( $sai ) );
		$ra( '' );
		$ra( sprintf( 'Gợi ý gõ sai `%s` → cũ `%s` %.1f ms, %d SQL · mới `%s` %.1f ms, %d SQL.', $sai, (string) $g_c['kq'], $g_c['ms'], $g_c['sql'], (string) $g_m['kq'], $g_m['ms'], $g_m['sql'] ) );
		$dung = $dung && $g_c['kq'] === $g_m['kq'];
	}
	$wpdb->query( "DELETE FROM $bang" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$ra( '' );
	$ra( $dung ? 'KẾT QUẢ: cách mới ĐÚNG ở mọi truy vấn.' : 'KẾT QUẢ: cách mới SAI ở ít nhất một truy vấn.' );
	return $dung;
}

/** Cách cũ ($gioi_han = 500) hoặc lấy hết (null): ứng viên kèm chữ rồi ChiMuc::xep. */
function do_tim_cach( string $q, ?int $gioi_han, int $so = 20 ): array {
	global $wpdb;
	$kd = ChiMuc::khong_dau( ChiMuc::chu( $q ) );
	if ( '' === $kd ) {
		return array( 'ds' => array(), 'tong' => 0 );
	}
	$giong = static fn( string $s ): string => '%' . $wpdb->esc_like( $s ) . '%';
	$dk    = implode( ' AND ', array_map( static fn( string $t ): string => $wpdb->prepare( '(chinh_kd LIKE BINARY %s OR phu_kd LIKE BINARY %s)', $giong( $t ), $giong( $t ) ), ChiMuc::mau( $kd ) ) );
	$sql   = 'SELECT post_id, chinh, chinh_kd, phu, phu_kd FROM ' . ChiMucTim::bang() . " WHERE $dk" . ( null === $gioi_han ? ' ORDER BY post_id' : " LIMIT $gioi_han" );
	return ChiMuc::xep( $wpdb->get_results( $sql, ARRAY_A ) ?: array(), $q, $so ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/** Gợi ý gõ sai theo cách cũ: đếm mỗi phương án bằng tìm 500 ứng viên. */
function do_tim_goi_y_cu( string $q ): ?string {
	$tu = explode( ' ', ChiMuc::khong_dau( ChiMuc::chu( $q ) ) );
	$tv = get_option( ChiMucTim::TU_VUNG, array() );
	$tot   = null;
	$nhieu = 0;
	foreach ( $tu as $i => $t ) {
		foreach ( ChiMuc::tu_gan( $t, is_array( $tv ) ? $tv : array() ) as $thay ) {
			$thu       = $tu;
			$thu[ $i ] = $thay;
			$moi       = implode( ' ', $thu );
			$dem       = do_tim_cach( $moi, 500, 1 )['tong'];
			if ( $dem > $nhieu ) {
				$tot   = $moi;
				$nhieu = $dem;
			}
		}
	}
	return $tot;
}

/** Chạy $f một lần làm nóng rồi 5 lần đo: kết quả, trung vị ms, số câu SQL một lần, bộ nhớ đỉnh (MB). */
function do_tim_do( callable $f ): array {
	global $wpdb;
	$kq = $f();
	$ms = array();
	$sql = 0;
	$mb  = 0.0;
	for ( $i = 0; $i < 5; $i++ ) {
		$truoc = $wpdb->num_queries;
		gc_collect_cycles();
		memory_reset_peak_usage();
		$nen = memory_get_usage();
		$bd  = hrtime( true );
		$kq  = $f();
		$ms[] = ( hrtime( true ) - $bd ) / 1e6;
		$mb   = max( $mb, ( memory_get_peak_usage() - $nen ) / 1048576 );
		$sql  = $wpdb->num_queries - $truoc;
	}
	sort( $ms );
	return array( 'kq' => $kq, 'ms' => $ms[2], 'sql' => $sql, 'mb' => $mb );
}

// Playground (blueprint do-tim/blueprint.json): WordPress đã nạp, ghi báo cáo ra /repo/tests/.do-tim.txt
if ( defined( 'ABSPATH' ) && ! defined( 'DO_TIM_NGOAI' ) ) {
	$tep = '/repo/tests/.do-tim.txt';
	file_put_contents( $tep, '' );
	$co = defined( 'DO_TIM_CO' ) ? array_map( 'intval', explode( ',', DO_TIM_CO ) ) : array( 1000, 5000, 10000 );
	do_tim_chay(
		$co,
		static fn( string $d ) => file_put_contents( $tep, $d . "\n", FILE_APPEND ),
		static fn( array $tv ) => update_option( ChiMucTim::TU_VUNG, $tv, false ),
		'Playground (SQLite) · WordPress ' . get_bloginfo( 'version' )
	);
	require __DIR__ . '/doc.php';
	do_tim_doc( $co, static fn( string $d ) => file_put_contents( $tep, $d . "\n", FILE_APPEND ) );

}
