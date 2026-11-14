<?php

/** Đo đường tìm người đọc T-203 trên WordPress riêng của do:tim:v2, không chạy trên site thật. */
function do_tim_doc( array $co, callable $ra, string $moi_truong = 'SQLite/Playground' ): void {
	global $wpdb;
	$tim = \Phongdao\DichVu\ChiMucTim\ChiMucTim::class;
	$doc = \Phongdao\DichVu\ChiMucTim\TimDoc::class;
	$cm = \Phongdao\MienDuLieu\ChiMuc::class;
	$tom = \Phongdao\DichVu\TomTat\TomTat::bang();
	$kho = do_tim_kho( max( $co ) );
	$base = 10000000 + (int) $wpdb->get_var( "SELECT MAX(ID) FROM {$wpdb->posts}" );
	$da = 0;
	$ok = true;
	$ra( "\n## T-203 · Tìm đã gửi trên tác phẩm tổng hợp ($moi_truong)" );
	$ra( '| Cỡ kho | Truy vấn | Kết quả trọn chữ | Trang cuối | Trung vị ms | SQL | MB tăng | Đúng oracle |' );
	$ra( '|---:|---|---:|---:|---:|---:|---:|---|' );
	try {
		foreach ( $co as $n ) {
			foreach ( array_chunk( array_slice( $kho, $da, $n - $da ), 100 ) as $lo ) {
				$posts = array(); $idx = array(); $sum = array();
				foreach ( $lo as [ $i, $c, $p ] ) {
					$id = $base + $i;
					$r = $cm::dong_chi_muc( $c, $p );
					if ( ! $r ) { continue; }
					$posts[] = $wpdb->prepare( "(%d, 0, '2026-01-01 00:00:00', '2026-01-01 00:00:00', '', %s, '', 'publish', 'closed', 'closed', '', %s, '', '', '2026-01-01 00:00:00', '2026-01-01 00:00:00', '', 0, '', 0, 'tac_pham', '', 0)", $id, 'technical-s203-' . $i, 'technical-s203-' . $i );
					$idx[] = $wpdb->prepare( '(%d,%s,%s,%s,%s)', $id, $r['chinh'], $r['chinh_kd'], $r['phu'], $r['phu_kd'] );
					$sum[] = $wpdb->prepare( "(%d,'ca-dao','publish',1,0,'',0,'',0,0,'2026-01-01 00:00:00')", $id );
				}
				$wpdb->query( "INSERT INTO {$wpdb->posts} (ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count) VALUES " . implode( ',', $posts ) );
				$wpdb->query( 'INSERT INTO ' . $tim::bang() . ' (post_id,chinh,chinh_kd,phu,phu_kd) VALUES ' . implode( ',', $idx ) );
				$wpdb->query( "INSERT INTO $tom (post_id,the_loai,trang_thai,cap,uu_tien_doc,thieu,co,nguon_may,nguon_nguoi,doc_7,sua_luc) VALUES " . implode( ',', $sum ) );
			}
			$da = $n;
			$tv = do_tim_truy_van( array_slice( $kho, 0, $n ) );
			foreach ( array_slice( $tv['ds'], 0, 7, true ) as $ten => $q ) {
				// Oracle đọc mọi chữ: tự lọc trọn âm tiết/đúng dấu rồi tính điểm đầy đủ, không dùng SQL cờ.
				$phep = $cm::phep_so( $q, true );
				$ch = $cm::chu( $q ); $kd = $cm::khong_dau( $ch );
				$dung = array();
				foreach ( array_slice( $kho, 0, $n ) as [ $i, $c, $p ] ) {
					$r = $cm::dong_chi_muc( $c, $p );
					$cot = $ch === $kd ? array( 'chinh_kd', 'phu_kd' ) : array( 'chinh', 'phu' );
					if ( ! array_reduce( $cm::mau( $ch === $kd ? $kd : $ch, true ), static fn( bool $v, string $m ): bool => $v && ( str_contains( $r[ $cot[0] ], $m ) || str_contains( $r[ $cot[1] ], $m ) ), true ) ) { continue; }
					$f = $cm::co( $r, $phep ); $f['uu_tien_doc'] = 0;
					$tu = explode( ' ', $kd );
					$dung[ $base + $i ] = $cm::diem( $f, $cm::gan_nhau( $r['chinh_kd'], $tu, true ), $cm::gan_nhau( $r['phu_kd'], $tu, true ), strlen( $r['chinh_kd'] ) )[0];
				}
				arsort( $dung );
				$m = do_tim_do( static fn(): array => $doc::tim( $q ) );
				$cuoi = max( 1, (int) ceil( count( $dung ) / 20 ) );
				$last = $doc::tim( $q, array( 'trang' => $cuoi ) );
				$pass = count( $dung ) === $m['kq']['tong'] && array_slice( array_keys( $dung ), 0, 20 ) === array_column( $m['kq']['ds'], 'id' ) && array_slice( array_keys( $dung ), ( $cuoi - 1 ) * 20, 20 ) === array_column( $last['ds'], 'id' );
				$ok = $ok && $pass;
				$ra( sprintf( '| %d | %s | %d | %d | %.1f | %d | %.2f | %s |', $n, $ten, count( $dung ), $cuoi, $m['ms'], $m['sql'], $m['mb'], $pass ? 'ĐÚNG' : 'SAI' ) );
			}
		}
	} finally {
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $tim::bang() . ' WHERE post_id > %d AND post_id <= %d', $base, $base + max( $co ) ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $tom WHERE post_id > %d AND post_id <= %d", $base, $base + max( $co ) ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE ID > %d AND ID <= %d", $base, $base + max( $co ) ) );
	}
	$ra( $ok ? 'T-203: đường tìm người đọc ĐÚNG oracle ở mọi truy vấn và trang cuối.' : 'T-203: đường tìm người đọc SAI.' );
	if ( ! $ok ) { throw new RuntimeException( 'T-203: sai oracle' ); }
}
