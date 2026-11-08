<?php

namespace Phongdao\DichVu\ChongTrung;

use Phongdao\DichVu\UngVien\UngVien;
use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\VanTay;

defined( 'ABSPATH' ) || exit;

/**
 * Chống trùng (chuyển từ MVP inc/trung.php; docs/28 mục 3.4): máy báo, người quyết. Giữ nguyên ba bảng của MVP
 * (`wp_cadao_van_tay`, `wp_cadao_cap_am`, `wp_cadao_trung`) và khoá mục 'bai:<id>' / 'kho:<id>' nên mọi quyết định cũ
 * ("Là bài khác", "Đã gộp") mang sang nguyên vẹn. Câu trong kho chỉ tự gắn vào tác phẩm / câu có sẵn khi lời giống hệt
 * (kể cả chỉ khác dấu câu, chữ hoa, cách bỏ dấu) và cùng thể loại, hoặc khi lời trùng 100% một dị bản đã ghi (D-074);
 * mọi trường hợp khác thành việc "Có thể trùng" trong Hộp việc. Không tự xoá gì; không tự gộp hai tác phẩm.
 */
final class ChongTrung implements Module {

	public function dang_ky(): void {
		add_action( 'phongdao_kho_da_luu', array( self::class, 'sau_khi_luu_kho' ), 10, 1 );
		add_action(
			'phongdao_da_luu_phan',
			static function ( int $bai, string $phan ): void {
				if ( in_array( $phan, array( 'loi', 'di_ban' ), true ) ) {
					self::van_tay_bai( $bai );
				}
			},
			10,
			2
		);
		add_action(
			'transition_post_status',
			static function ( string $moi, string $cu, \WP_Post $bai ): void {
				if ( KhaiBao::TAC_PHAM === $bai->post_type && $moi !== $cu ) {
					self::van_tay_bai( $bai->ID );
				}
			},
			10,
			3
		);
		add_action( 'deleted_post', static fn( int $id ) => self::xoa( 'bai:' . $id ) );
	}

	public static function bang( string $ten ): string {
		global $wpdb;
		return $wpdb->prefix . 'cadao_' . $ten;
	}

	/** @return string[] câu lệnh dbDelta cho ba bảng (cùng cấu trúc MVP) */
	public static function sql_tao( string $bo_ma ): array {
		return array(
			'CREATE TABLE ' . self::bang( 'van_tay' ) . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			muc varchar(32) NOT NULL,
			phan smallint(5) unsigned NOT NULL DEFAULT 0,
			vt1 char(32) NOT NULL,
			vt2 char(32) NOT NULL,
			so_am smallint(5) unsigned NOT NULL DEFAULT 0,
			so_dong smallint(5) unsigned NOT NULL DEFAULT 0,
			so_cap smallint(5) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY muc_phan (muc,phan),
			KEY vt1 (vt1),
			KEY vt2 (vt2)
			) $bo_ma;",
			'CREATE TABLE ' . self::bang( 'cap_am' ) . " (
			cap int(10) unsigned NOT NULL,
			van_tay_id bigint(20) unsigned NOT NULL,
			PRIMARY KEY  (cap,van_tay_id),
			KEY van_tay_id (van_tay_id)
			) $bo_ma;",
			'CREATE TABLE ' . self::bang( 'trung' ) . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			a varchar(32) NOT NULL,
			b varchar(32) NOT NULL,
			muc varchar(16) NOT NULL DEFAULT '',
			giong tinyint(3) unsigned NOT NULL DEFAULT 0,
			ly_do varchar(190) NOT NULL DEFAULT '',
			trang_thai varchar(16) NOT NULL DEFAULT 'cho',
			ngay datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY cap (a,b),
			KEY trang_thai (trang_thai)
			) $bo_ma;",
		);
	}

	// ---------- Chỉ mục vân tay ----------

	public static function xoa( string $muc ): bool {
		global $wpdb;
		$vt  = self::bang( 'van_tay' );
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $vt WHERE muc = %s", $muc ) );
		if ( $wpdb->last_error ) {
			return false;
		}
		if ( $ids ) {
			if ( false === $wpdb->query( 'DELETE FROM ' . self::bang( 'cap_am' ) . ' WHERE van_tay_id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')' ) ) {
				return false;
			}
			if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $vt WHERE muc = %s", $muc ) ) ) {
				return false;
			}
		}
		return true;
	}

	/** @param string[] $cac_loi phần tử 0 = lời chính, còn lại = dị bản */
	public static function ghi( string $muc, array $cac_loi ): bool {
		global $wpdb;
		// Dựng bản mới ở khoá tạm trước: lỗi ghi không được xoá chỉ mục đang dùng.
		$tam = 'tam:' . bin2hex( random_bytes( 8 ) );
		$cu  = 'cu:' . bin2hex( random_bytes( 8 ) );
		$so_ghi = 0;
		foreach ( array_values( $cac_loi ) as $phan => $loi ) {
			$v = VanTay::cua( (string) $loi );
			if ( ! $v['so_am'] ) {
				continue;
			}
			$da_ghi = $wpdb->insert(
				self::bang( 'van_tay' ),
				array( 'muc' => $tam, 'phan' => $phan, 'vt1' => $v['vt1'], 'vt2' => $v['vt2'], 'so_am' => min( 65535, $v['so_am'] ), 'so_dong' => min( 65535, $v['so_dong'] ), 'so_cap' => min( 65535, $v['so_cap'] ) )
			);
			if ( false === $da_ghi ) {
				self::xoa( $tam );
				return false;
			}
			++$so_ghi;
			if ( 0 === $phan && $v['so_am'] >= VanTay::AM_TOI_THIEU ) {
				$id = (int) $wpdb->insert_id;
				foreach ( array_chunk( $v['cap'], 500 ) as $lo ) {
					if ( false === $wpdb->query( 'INSERT IGNORE INTO ' . self::bang( 'cap_am' ) . ' (cap, van_tay_id) VALUES ' . implode( ',', array_map( static fn( $c ) => '(' . (int) $c . ',' . $id . ')', $lo ) ) ) ) {
						self::xoa( $tam );
						return false;
					}
				}
			}
		}
		// Giữ nguyên các id vân tay cũ cùng cặp âm để có thể hoàn nguyên nếu bước kích hoạt lỗi.
		$so_cu = $wpdb->update( self::bang( 'van_tay' ), array( 'muc' => $cu ), array( 'muc' => $muc ) );
		if ( false === $so_cu ) {
			self::xoa( $tam );
			return false;
		}
		if ( $so_ghi && $so_ghi !== $wpdb->update( self::bang( 'van_tay' ), array( 'muc' => $muc ), array( 'muc' => $tam ) ) ) {
			if ( $so_cu ) {
				$wpdb->update( self::bang( 'van_tay' ), array( 'muc' => $muc ), array( 'muc' => $cu ) );
			}
			self::xoa( $tam );
			return false;
		}
		if ( ! self::xoa( $cu ) ) {
			// Bản mới đã hoạt động; giữ kết quả này nếu chưa dọn được bản cũ ẩn.
			if ( $so_ghi ) {
				return true;
			}
			if ( $so_cu ) {
				$wpdb->update( self::bang( 'van_tay' ), array( 'muc' => $muc ), array( 'muc' => $cu ) );
			}
			return false;
		}
		return true;
	}

	/** Chỉ mục cho một tác phẩm từ các phần đã lưu (lời + dị bản); bài trong thùng rác / bản nháp tự động thì bỏ. */
	public static function van_tay_bai( int $id ): bool {
		$bai = get_post( $id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || in_array( $bai->post_status, array( 'trash', 'auto-draft', 'inherit' ), true ) ) {
			return self::xoa( 'bai:' . $id );
		}
		$loi = PhanBai::lay( $id, 'loi' )['dong'] ?? array();
		$ds  = array( implode( "\n", $loi ) );
		foreach ( PhanBai::lay( $id, 'di_ban' )['muc'] ?? array() as $d ) {
			$ds[] = implode( "\n", $d['dong'] ?? array() );
		}
		return self::ghi( 'bai:' . $id, $ds );
	}

	/** Chỉ mục cho một câu trong kho: chỉ câu "mới" (chưa thành tác phẩm, chưa gộp, chưa bỏ) và đã có lời. */
	public static function van_tay_kho( int $kho_id ): void {
		$m = Kho::lay( $kho_id );
		if ( ! $m || 'moi' !== $m->trang_thai || '' === $m->loi ) {
			self::xoa( 'kho:' . $kho_id );
			return;
		}
		self::ghi( 'kho:' . $kho_id, array_merge( array( $m->loi ), array_map( 'strval', (array) ( Kho::du_lieu( $m )['di_ban'] ?? array() ) ) ) );
	}

	/**
	 * Tác phẩm và câu trong kho có lời giống $loi, giống nhất trước.
	 *
	 * @param string[] $tru mục bỏ qua ('bai:12', 'kho:34')
	 * @return array<int,array{muc:string,phan:int,cap_do:string,giong:int,ly_do:string}>
	 */
	public static function tim( string $loi, array $tru = array(), int $so = 10 ): array {
		global $wpdb;
		$v = VanTay::cua( $loi );
		if ( ! $v['so_am'] ) {
			return array();
		}
		$vt  = self::bang( 'van_tay' );
		$tot = array();
		$xet = static function ( object $h, int $chung ) use ( $v, $tru, &$tot ): void {
			if ( str_starts_with( $h->muc, 'tam:' ) || str_starts_with( $h->muc, 'cu:' ) || in_array( $h->muc, $tru, true ) ) {
				return;
			}
			$b = array( 'vt1' => $h->vt1, 'vt2' => $h->vt2, 'so_am' => (int) $h->so_am, 'so_dong' => (int) $h->so_dong, 'so_cap' => (int) $h->so_cap );
			[ $cap_do, $giong, $ly_do ] = VanTay::xep( $v, $b, $chung, (int) $h->phan > 0 );
			if ( 'khac' === $cap_do ) {
				return;
			}
			$hang = array_search( $cap_do, array_keys( VanTay::MUC ), true );
			$cu   = $tot[ $h->muc ] ?? null;
			if ( ! $cu || $hang < $cu['hang'] || ( $hang === $cu['hang'] && $giong > $cu['giong'] ) ) {
				$tot[ $h->muc ] = array( 'muc' => $h->muc, 'phan' => (int) $h->phan, 'cap_do' => $cap_do, 'giong' => $giong, 'ly_do' => $ly_do, 'hang' => $hang );
			}
		};
		// 1. Giống hệt (cả dị bản đã ghi)
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT muc, phan, vt1, vt2, so_am, so_dong, so_cap FROM $vt WHERE (vt1 = %s OR vt2 = %s) AND muc NOT LIKE 'tam:%%' AND muc NOT LIKE 'cu:%%' LIMIT 50", $v['vt1'], $v['vt2'] ) ) ?: array() as $h ) {
			$xet( $h, 0 );
		}
		// 2. Gần giống: lời chính chung nhiều cặp âm tiết
		if ( $v['so_am'] >= VanTay::AM_TOI_THIEU && $v['cap'] ) {
			$trong = implode( ',', array_map( 'intval', $v['cap'] ) );
			$ds    = $wpdb->get_results( 'SELECT t.muc, t.phan, t.vt1, t.vt2, t.so_am, t.so_dong, t.so_cap, COUNT(*) AS chung FROM ' . self::bang( 'cap_am' ) . " c JOIN $vt t ON t.id = c.van_tay_id WHERE c.cap IN ($trong) AND t.muc NOT LIKE 'tam:%' AND t.muc NOT LIKE 'cu:%' GROUP BY c.van_tay_id HAVING chung >= 3 ORDER BY chung DESC LIMIT 60" );
			foreach ( $ds ?: array() as $h ) {
				$xet( $h, (int) $h->chung );
			}
		}
		// Mạnh hơn trước, giống hơn trước; ngang nhau thì tác phẩm đã có trước câu còn trong kho
		usort( $tot, static fn( $x, $y ) => array( $x['hang'], -$x['giong'], str_starts_with( $x['muc'], 'bai:' ) ? 0 : 1, $x['muc'] ) <=> array( $y['hang'], -$y['giong'], str_starts_with( $y['muc'], 'bai:' ) ? 0 : 1, $y['muc'] ) );
		return array_map(
			static function ( array $x ): array {
				unset( $x['hang'] );
				return $x;
			},
			array_slice( $tot, 0, $so )
		);
	}

	/**
	 * Câu trong kho chưa thành bài có chứa cụm người đọc tìm: đủ mọi cặp âm tiết liền nhau của cụm (cụm từ 3 âm tiết).
	 * Trang Tìm hiện "Câu này đang chờ biên soạn" mà không đọc lời kho ra (docs/26 mục 8). 0 = không có.
	 */
	public static function kho_co( string $q ): int {
		global $wpdb;
		$cap = VanTay::cua( $q )['cap'];
		if ( count( $cap ) < 2 ) {
			return 0;
		}
		$ds = $wpdb->get_col( 'SELECT t.muc FROM ' . self::bang( 'cap_am' ) . ' c JOIN ' . self::bang( 'van_tay' ) . ' t ON t.id = c.van_tay_id WHERE c.cap IN (' . implode( ',', array_map( 'intval', $cap ) ) . ") AND t.muc LIKE 'kho:%' GROUP BY c.van_tay_id HAVING COUNT(*) >= " . count( $cap ) . ' LIMIT 20' ); // phpcs:ignore WordPress.DB.PreparedSQL -- chỉ số nguyên
		foreach ( $ds ?: array() as $muc ) {
			$m = Kho::lay( self::tach_muc( (string) $muc )[1] );
			if ( $m && 'moi' === $m->trang_thai && ! (int) $m->post_id ) {
				return (int) $m->id;
			}
		}
		return 0;
	}

	// ---------- Mục ----------

	/** @return array{0:string,1:int} ['bai'|'kho', id] */
	public static function tach_muc( string $muc ): array {
		[ $loai, $id ] = array_pad( explode( ':', $muc, 2 ), 2, '0' );
		return array( $loai, (int) $id );
	}

	/** Thể loại để so (thể loại "Thành ngữ" cũ tính là "Tục ngữ – Thành ngữ", D-079). */
	public static function nhom_the_loai( string $muc ): string {
		[ $loai, $id ] = self::tach_muc( $muc );
		if ( 'kho' === $loai ) {
			$m = Kho::lay( $id );
			return TheLoai::TU_LOAI_GOC[ $m ? $m->loai : '' ] ?? 'ca-dao';
		}
		$t = get_the_terms( $id, KhaiBao::THE_LOAI );
		return is_array( $t ) && $t ? $t[0]->slug : 'ca-dao';
	}

	/** Tác phẩm ứng với một mục: 'bai:<id>' → id; 'kho:<id>' → tác phẩm đã tạo từ câu đó (0 nếu chưa). */
	public static function bai_cua_muc( string $muc ): int {
		[ $loai, $id ] = self::tach_muc( $muc );
		if ( 'bai' === $loai ) {
			return $id;
		}
		$m = Kho::lay( $id );
		return $m ? (int) $m->post_id : 0;
	}

	// ---------- Cặp và quyết định ----------

	/** Ghi một cặp nghi trùng (đã có — kể cả chiều ngược — thì giữ quyết định cũ); cặp mới thành việc "Có thể trùng". */
	public static function ghi_cho( string $a, string $b, string $cap_do, int $giong, string $ly_do ): void {
		global $wpdb;
		$t = self::bang( 'trung' );
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE (a = %s AND b = %s) OR (a = %s AND b = %s)", $a, $b, $b, $a ) ) ) {
			return;
		}
		$wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO $t (a, b, muc, giong, ly_do, trang_thai, ngay) VALUES (%s, %s, %s, %d, %s, 'cho', %s)", $a, $b, $cap_do, $giong, $ly_do, current_time( 'mysql' ) ) );
		[ $loai_a, $id_a ] = self::tach_muc( $a );
		Viec::tao(
			array(
				'loai'           => 'co_the_trung',
				'doi_tuong_loai' => $loai_a,
				'doi_tuong_id'   => $id_a,
				'noi_dung'       => array( 'a' => $a, 'b' => $b, 'muc' => $cap_do, 'giong' => $giong, 'ly_do' => $ly_do ),
				'nguoi_tao'      => 'may',
				'uu_tien'        => 30,
			)
		);
	}

	/** Đặt quyết định cho một cặp: 'khac' (là bài khác), 'de_sau', 'da_gop', 'bo'. */
	public static function dat( string $a, string $b, string $trang_thai ): bool {
		global $wpdb;
		$t = self::bang( 'trung' );
		if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE a = %s AND b = %s", $a, $b ) ) && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE a = %s AND b = %s", $b, $a ) ) ) {
			[ $a, $b ] = array( $b, $a );
		}
		$co = $wpdb->update( $t, array( 'trang_thai' => $trang_thai ), array( 'a' => $a, 'b' => $b ) );
		if ( false === $co ) {
			return false;
		}
		if ( 0 === $co && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE a = %s AND b = %s", $a, $b ) ) ) {
			return 1 === $wpdb->insert( $t, array( 'a' => $a, 'b' => $b, 'trang_thai' => $trang_thai, 'ngay' => current_time( 'mysql' ) ) );
		}
		return true;
	}

	/** Những mục người biên soạn đã nói "là bài khác" với $muc (cả hai chiều). */
	public static function da_khac( string $muc ): array {
		global $wpdb;
		$t = self::bang( 'trung' );
		return array_merge(
			$wpdb->get_col( $wpdb->prepare( "SELECT b FROM $t WHERE a = %s AND trang_thai = 'khac'", $muc ) ),
			$wpdb->get_col( $wpdb->prepare( "SELECT a FROM $t WHERE b = %s AND trang_thai = 'khac'", $muc ) )
		);
	}

	/** Câu trong kho đang chờ người quyết trùng (máy chọn câu mới bỏ qua câu đó). */
	public static function dang_cho( int $kho_id ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::bang( 'trung' ) . " WHERE a = %s AND trang_thai IN ('cho','de_sau') LIMIT 1", 'kho:' . $kho_id ) );
	}

	// ---------- Khi lấy từ nguồn ----------

	/**
	 * So một câu "mới" trong kho với tác phẩm đã có và các câu khác trong kho.
	 *
	 * @return string 'khong' | 'da_gop' | 'cho_quyet'
	 */
	public static function kiem_kho( int $kho_id ): string {
		global $wpdb;
		$m = Kho::lay( $kho_id );
		if ( ! $m || 'moi' !== $m->trang_thai || '' === $m->loi ) {
			return 'khong';
		}
		$khoa = 'kho:' . $kho_id;
		foreach ( self::tim( $m->loi, array_merge( array( $khoa ), self::da_khac( $khoa ) ), 5 ) as $t ) {
			if ( 'lien_quan' === $t['cap_do'] ) {
				continue;
			}
			$cung_loai = self::nhom_the_loai( $khoa ) === self::nhom_the_loai( $t['muc'] );
			if ( $cung_loai && 0 === $t['phan'] && 100 === $t['giong'] && in_array( $t['cap_do'], array( 'y_het', 'gan_y_het' ), true ) ) {
				$lam = 'trung';
			} elseif ( $t['phan'] > 0 && 100 === $t['giong'] && 'di_ban' === $t['cap_do'] ) { // D-074: trùng đúng một dị bản đã ghi
				$lam = 'di_ban';
			} else {
				$lam = '';
			}
			if ( $lam ) {
				if ( self::gop_tu_dong( $khoa, $t['muc'], $lam ) ) {
					return 'da_gop';
				}
				self::ghi_cho( $khoa, $t['muc'], $t['cap_do'], $t['giong'], trim( 'Chưa gộp tự động được; cần kiểm lại. ' . $t['ly_do'] ) );
				return self::dang_cho( $kho_id ) ? 'cho_quyet' : 'khong';
			}
			$ly_do = $cung_loai ? $t['ly_do'] : trim( 'khác thể loại' . ( $t['ly_do'] ? '; ' . $t['ly_do'] : '' ) );
			self::ghi_cho( $khoa, $t['muc'], $t['cap_do'], $t['giong'], $ly_do );
			$tt = $wpdb->get_var( $wpdb->prepare( 'SELECT trang_thai FROM ' . self::bang( 'trung' ) . ' WHERE (a = %s AND b = %s) OR (a = %s AND b = %s)', $khoa, $t['muc'], $t['muc'], $khoa ) );
			if ( in_array( $tt, array( 'cho', 'de_sau' ), true ) ) {
				return 'cho_quyet';
			}
		}
		return 'khong';
	}

	/** Gộp từ nguồn nằm ngoài lượt quyết thẻ: giữ kho, cặp, phần bài và chỉ mục trong cùng giao dịch. */
	private static function gop_tu_dong( string $a, string $b, string $lam ): bool {
		global $wpdb;
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return false;
		}
		try {
			$ok = self::gop( $a, $b, $lam );
		} catch ( \Throwable $e ) {
			$ok = false;
		}
		if ( ! $ok || false === $wpdb->query( 'COMMIT' ) ) {
			$wpdb->query( 'ROLLBACK' );
			$bai = self::bai_cua_muc( $b );
			if ( $bai ) {
				clean_post_cache( $bai );
			}
			return false;
		}
		return true;
	}

	/** Sau khi kho lưu một câu: cập nhật chỉ mục; câu đã đọc trang gốc thì so ngay. */
	public static function sau_khi_luu_kho( int $kho_id ): void {
		if ( ! $kho_id ) {
			return;
		}
		self::van_tay_kho( $kho_id );
		$m = Kho::lay( $kho_id );
		if ( $m && preg_match( '/^\d{4}-/', (string) $m->doc_trang ) ) {
			self::kiem_kho( $kho_id );
		}
	}

	/**
	 * Gắn câu kho $a vào tác phẩm / câu $b. $lam = 'trung' (cùng lời: bổ sung dị bản, chú thích của nguồn kia) hoặc
	 * 'di_ban' (lời của $a thành một dị bản của $b). Tác phẩm đích chỉ được máy cập nhật phần còn do máy chép
	 * (UngVien::dong_bo); phần người đã sửa thì thành việc gợi ý cho người quyết. Không xoá gì.
	 */
	public static function gop( string $a, string $b, string $lam ): bool {
		global $wpdb;
		[ $loai_a, $id_a ] = self::tach_muc( $a );
		if ( 'kho' !== $loai_a || $a === $b ) {
			return false; // gộp hai tác phẩm đã đăng: việc "Gộp tác phẩm" (người quyết)
		}
		$m = Kho::lay( $id_a );
		if ( ! $m || $m->post_id || 'moi' !== $m->trang_thai || ! in_array( $lam, array( 'trung', 'di_ban' ), true ) ) {
			return false;
		}
		$bai  = self::bai_cua_muc( $b );
		$dich = $bai ? 'bai:' . $bai : $b;
		if ( 1 !== $wpdb->update( Kho::bang(), array( 'trang_thai' => 'da_gop', 'gop_vao' => $dich, 'gop_lam' => $lam ), array( 'id' => $id_a, 'trang_thai' => 'moi', 'post_id' => 0 ) ) ) {
			return false;
		}
		if ( false === $wpdb->update( Kho::bang(), array( 'gop_vao' => $dich ), array( 'gop_vao' => $a ) ) ) { // câu đã gộp vào $a đi theo
			return false;
		}
		if ( ! self::xoa( $a ) ) {
			return false;
		}
		if ( ! self::dat( $a, $b, 'da_gop' ) ) {
			return false;
		}
		if ( $bai ) {
			if ( ! UngVien::dong_bo( $bai ) ) {
				return false;
			}
			if ( ! self::van_tay_bai( $bai ) ) {
				return false;
			}
		}
		return true;
	}

	/** Tách một câu đã gộp ra lại (người bấm "Tách ra"): câu thành "mới", cặp ghi "là bài khác". */
	public static function tach( int $kho_id ): bool {
		global $wpdb;
		$m = Kho::lay( $kho_id );
		if ( ! $m || 'da_gop' !== $m->trang_thai ) {
			return false;
		}
		$wpdb->update( Kho::bang(), array( 'trang_thai' => 'moi', 'gop_vao' => '', 'gop_lam' => '' ), array( 'id' => $kho_id ) );
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . self::bang( 'trung' ) . " SET trang_thai = 'khac' WHERE a = %s AND trang_thai = 'da_gop'", 'kho:' . $kho_id ) );
		self::dat( 'kho:' . $kho_id, $m->gop_vao, 'khac' );
		self::van_tay_kho( $kho_id );
		$bai = self::bai_cua_muc( $m->gop_vao );
		if ( $bai ) {
			UngVien::dong_bo( $bai );
			self::van_tay_bai( $bai );
		}
		return true;
	}
}
