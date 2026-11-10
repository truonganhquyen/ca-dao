<?php
/**
 * Đo tìm kiếm trên MySQL/MariaDB (gần máy chủ thật hơn SQLite của Playground), không cần cài WordPress:
 *   php wordpress/v2/tests/do-tim/mysql.php <máy> <cổng> <người dùng> <mật khẩu> <CSDL rỗng dành riêng để đo> [1000,5000,10000]
 * Cần PHP ≥ 8.3 có mysqli, intl, mbstring. Dựng bảng wp_cadao_tim trong CSDL đó (xoá bảng cùng tên nếu có), xoá khi đo xong.
 * TUYỆT ĐỐI không trỏ vào CSDL của site thật. $wpdb dưới đây chỉ có đúng những gì ChiMucTim và do.php dùng.
 */

declare(strict_types=1);

[ , $may, $cong, $nguoi, $mk, $csdl ] = $argv + array_fill( 0, 6, '' );
if ( '' === $csdl ) {
	fwrite( STDERR, "Cách dùng: php mysql.php <máy> <cổng> <người dùng> <mật khẩu> <CSDL> [cỡ kho]\n" );
	exit( 2 );
}
define( 'ABSPATH', __DIR__ . '/' );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'DO_TIM_NGOAI', true );
require __DIR__ . '/../../plugins/ca-dao/src/tu-nap.php';

/** $wpdb tối thiểu trên mysqli. */
final class DoTimCsdl {
	public string $prefix   = 'wp_';
	public int $num_queries = 0;
	public function __construct( private mysqli $m ) {}
	public function esc_like( string $s ): string {
		return addcslashes( $s, '_%\\' );
	}
	public function prepare( string $sql, mixed ...$gt ): string {
		$i = 0;
		return (string) preg_replace_callback(
			'/%[sd]/',
			function ( array $m ) use ( &$i, $gt ): string {
				$v = $gt[ $i++ ];
				return '%d' === $m[0] ? (string) (int) $v : "'" . $this->m->real_escape_string( (string) $v ) . "'";
			},
			$sql
		);
	}
	public function query( string $sql ): int|bool {
		++$this->num_queries;
		$kq = $this->m->query( $sql );
		return $kq instanceof mysqli_result ? $kq->num_rows : $kq;
	}
	public function get_results( string $sql, string $kieu = ARRAY_A ): array {
		++$this->num_queries;
		return $this->m->query( $sql )->fetch_all( MYSQLI_ASSOC );
	}
	public function get_var( string $sql ): ?string {
		++$this->num_queries;
		$h = $this->m->query( $sql )->fetch_row();
		return null === $h ? null : (string) $h[0];
	}
}

$GLOBALS['do_tim_tuy_chon'] = array();
function get_option( string $ten, mixed $mac_dinh = false ): mixed {
	return $GLOBALS['do_tim_tuy_chon'][ $ten ] ?? $mac_dinh;
}

mysqli_report( MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );
$m = new mysqli( $may, $nguoi, $mk, $csdl, (int) $cong );
$m->set_charset( 'utf8mb4' );
$GLOBALS['wpdb'] = new DoTimCsdl( $m );
$m->query( 'DROP TABLE IF EXISTS wp_cadao_tim' ); // CSDL dành riêng để đo: dựng lại bảng mỗi lần
$m->query( \Phongdao\DichVu\ChiMucTim\ChiMucTim::sql_tao( 'wp_cadao_tim', 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci' ) );
$phien_ban = $m->query( 'SELECT VERSION()' )->fetch_row()[0];

require __DIR__ . '/do.php';
$ok = do_tim_chay(
	array_map( 'intval', explode( ',', $argv[6] ?? '1000,5000,10000' ) ),
	static function ( string $d ): void {
		echo $d, "\n";
	},
	static function ( array $tv ): void {
		$GLOBALS['do_tim_tuy_chon'][ \Phongdao\DichVu\ChiMucTim\ChiMucTim::TU_VUNG ] = $tv;
	},
	'MySQL/MariaDB ' . $phien_ban
);
$m->query( 'DROP TABLE wp_cadao_tim' );
exit( $ok ? 0 : 1 );
