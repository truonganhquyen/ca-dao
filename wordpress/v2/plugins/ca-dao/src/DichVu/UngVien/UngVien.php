<?php

namespace Phongdao\DichVu\UngVien;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\VanTay;

defined( 'ABSPATH' ) || exit;

/**
 * Câu mới (docs/28 mục 3.3, D-086): mỗi ngày máy chọn câu từ kho thành việc "Câu mới" trong Hộp việc — không còn tự tạo
 * hàng loạt bài Chờ duyệt. Người bấm Đăng thì mới thành tác phẩm; lời, dị bản, chú thích máy chép vào bài với người làm
 * `may_chep` và hồ sơ nguồn (không điền trang, đường dẫn). Chú thích, lời giải chỉ chép khi nguồn bật "Chép chú thích"
 * (D-055). Kho đổi sau đó: chỉ cập nhật phần còn hoàn toàn do máy chép; phần người đã sửa giữ nguyên.
 */
final class UngVien implements Module {

	public const VIEC = 'cau_moi';

	public function dang_ky(): void {
		add_action( NguonTuDong::LICH_NGAY, array( self::class, 'chon_hang_ngay' ) );
		add_action(
			'phongdao_kho_da_luu',
			static function ( int $kho_id, string $tt ): void {
				if ( 'cap_nhat' !== $tt ) {
					return;
				}
				$m = Kho::lay( $kho_id );
				$bai = $m ? ( $m->post_id ? (int) $m->post_id : ( 'da_gop' === $m->trang_thai ? ChongTrung::bai_cua_muc( (string) $m->gop_vao ) : 0 ) ) : 0;
				if ( $bai ) {
					self::dong_bo( $bai );
				}
			},
			20,
			2
		);
	}

	/** Lịch hằng ngày: mỗi nguồn đang bật chọn "số câu mỗi ngày" câu mới vào Hộp việc. */
	public static function chon_hang_ngay(): void {
		if ( \Phongdao\DichVu\May\DieuKhien::dang_dung( 'chon_cau' ) ) {
			return; // người quản trị tạm dừng ở bảng điều khiển máy
		}
		foreach ( array_keys( NguonTuDong::tat_ca() ) as $id ) {
			$n = NguonTuDong::lay( (string) $id );
			if ( $n && $n['bat'] && $n['so_bai_moi_ngay'] > 0 ) {
				$so = self::chon( $n, (int) $n['so_bai_moi_ngay'] );
				NguonTuDong::ghi( $n, 'chọn câu mới', array( 'cau' => $so ) );
				NguonTuDong::luu( $n );
			}
		}
	}

	/**
	 * Chọn tối đa $so câu mới của một nguồn: câu "mới" đã đọc trang, có lời; xoay vòng theo loại (mỗi loại một câu rồi sang
	 * loại kế — loại nhập kho sau không phải chờ hết ca dao); bỏ câu mang cờ nguồn chọn bỏ qua, câu đang chờ quyết trùng,
	 * câu đã có việc "Câu mới" đang mở.
	 *
	 * @return int số việc "Câu mới" đã thêm
	 */
	public static function chon( array $nguon, int $so ): int {
		global $wpdb;
		$bang  = Kho::bang();
		$bo_co = $nguon['bo_qua_co'] ?? array();
		$loai  = $nguon['loai_lay'] ?: $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT loai FROM $bang WHERE nguon_id = %s", $nguon['id'] ) );
		$dong  = array();
		foreach ( $loai as $l ) {
			$dong[] = array( 'dk' => $wpdb->prepare( 'loai = %s', $l ), 'sau' => 0, 'cho' => array(), 'het' => false );
		}
		$lay = static function ( array &$d ) use ( $wpdb, $bang, $nguon, $bo_co ): int {
			while ( ! $d['het'] ) {
				if ( ! $d['cho'] ) {
					$d['cho'] = $wpdb->get_results( $wpdb->prepare( "SELECT id, du_lieu FROM $bang WHERE nguon_id = %s AND trang_thai = 'moi' AND doc_trang LIKE '2%%' AND loi <> '' AND {$d['dk']} AND id > %d ORDER BY id ASC LIMIT 50", $nguon['id'], $d['sau'] ) ) ?: array();
					if ( ! $d['cho'] ) {
						$d['het'] = true;
						break;
					}
				}
				$muc      = array_shift( $d['cho'] );
				$d['sau'] = (int) $muc->id;
				$co       = ( json_decode( (string) $muc->du_lieu, true ) ?: array() )['co'] ?? array();
				if ( array_intersect( (array) $co, $bo_co ) || Viec::mo( 'kho', (int) $muc->id, self::VIEC ) ) {
					continue;
				}
				// Chống trùng: câu trùng thì đã gắn vào tác phẩm có sẵn; câu nghi trùng chờ người quyết
				if ( ChongTrung::dang_cho( (int) $muc->id ) || 'khong' !== ChongTrung::kiem_kho( (int) $muc->id ) ) {
					continue;
				}
				return (int) $muc->id;
			}
			return 0;
		};
		$them = 0;
		while ( $them < $so && array_filter( $dong, static fn( array $d ): bool => ! $d['het'] ) ) {
			foreach ( array_keys( $dong ) as $k ) {
				if ( $dong[ $k ]['het'] ) {
					continue;
				}
				$id = $lay( $dong[ $k ] );
				if ( $id && self::tao_the( $id, array( 'nguon_id' => $nguon['id'] ), 'may', 40 )['moi'] ) {
					++$them;
				}
				if ( $them >= $so ) {
					break;
				}
			}
		}
		return $them;
	}

	/** Thẻ Câu mới người tự chọn từ Kho đứng trước câu máy chọn (40). */
	public const UU_TIEN_CHON_TAY = 50;

	/**
	 * Người biên soạn tự chọn một câu trong Kho đưa vào Hộp việc (docs/30 mục 4.6, T-184). Chỉ tạo thẻ "Câu mới" đang chờ:
	 * không đăng, gộp, sửa lời hay bỏ câu — quyết vẫn ở Hộp việc. Cùng điều kiện với lượt máy chọn: câu mới, có lời, chưa
	 * gắn bài, không chờ quyết trùng, máy so trùng lại trước. Cờ của nguồn (vd. thô tục) chỉ là lời nhắc: người đã thấy và
	 * tự chọn. Bấm lại, hay hai người bấm cùng lúc, vẫn chỉ có một thẻ.
	 *
	 * @return array{ok:bool,moi:bool,viec:int,thong_bao:string}
	 */
	public static function dua_vao_hop_viec( int $kho_id, int $nguoi ): array {
		$m     = Kho::lay( $kho_id );
		$ly_do = self::chua_chon_duoc( $m );
		if ( '' !== $ly_do ) {
			return self::kq_chon( false, false, 0, $ly_do );
		}
		$mo = Viec::mo( 'kho', $kho_id, self::VIEC );
		if ( $mo ) {
			return self::kq_chon( true, false, (int) $mo[0]['id'], 'Câu này đã có trong Hộp việc (thẻ #' . (int) $mo[0]['id'] . ').' );
		}
		if ( ChongTrung::dang_cho( $kho_id ) ) {
			return self::kq_chon( false, false, 0, 'Câu đang chờ quyết trùng ở thẻ "Có thể trùng" trong Hộp việc. Quyết thẻ đó trước.' );
		}
		$trung = ChongTrung::kiem_kho( $kho_id ); // ngoài giao dịch: bước gộp tự động tự mở giao dịch riêng
		if ( 'da_gop' === $trung ) {
			return self::kq_chon( false, false, 0, 'Câu trùng hẳn một bài đã có nên máy đã gắn vào bài đó theo quy tắc tự gộp; không tạo thẻ mới.' );
		}
		if ( 'cho_quyet' === $trung ) {
			return self::kq_chon( false, false, 0, 'Máy vừa thấy câu này có thể trùng; đã thêm thẻ "Có thể trùng" vào Hộp việc để bạn quyết trước.' );
		}
		$t = self::tao_the( $kho_id, array( 'nguon_id' => (string) $m->nguon_id, 'chon_boi' => $nguoi ), (string) $nguoi, self::UU_TIEN_CHON_TAY );
		if ( '' !== $t['loi'] ) {
			return self::kq_chon( false, false, 0, $t['loi'] );
		}
		return $t['moi']
			? self::kq_chon( true, true, $t['viec'], 'Đã đưa câu #' . $kho_id . ' vào Hộp việc (thẻ #' . $t['viec'] . '). Quyết Đăng, Sửa rồi đăng hay Bỏ ở đó.' )
			: self::kq_chon( true, false, $t['viec'], 'Câu này đã có trong Hộp việc (thẻ #' . $t['viec'] . ').' );
	}

	/** Lý do một câu kho chưa đưa vào Hộp việc được; rỗng = được (màn Kho dùng để ẩn nút). */
	public static function chua_chon_duoc( ?object $m ): string {
		if ( ! $m ) {
			return 'Không tìm thấy câu này trong kho.';
		}
		if ( '' === trim( (string) $m->loi ) ) {
			return 'Câu chưa có lời (máy chưa đọc được trang gốc).';
		}
		if ( 'moi' !== $m->trang_thai || (int) $m->post_id ) {
			return array( 'da_tao_bai' => 'Câu đã thành tác phẩm.', 'da_gop' => 'Câu đã gộp vào bài hay câu khác.', 'bo_qua' => 'Câu đã bị bỏ.' )[ $m->trang_thai ] ?? 'Câu không còn ở trạng thái mới.';
		}
		return '';
	}

	/**
	 * Thẻ "Câu mới" của một câu kho, đúng một lần: khoá dòng kho, kiểm lại trạng thái và thẻ đang mở rồi ghi, cùng một giao
	 * dịch (máy chọn hằng ngày và người chọn tay đi chung đường này).
	 *
	 * @return array{viec:int,moi:bool,loi:string}
	 */
	private static function tao_the( int $kho_id, array $noi_dung, string $nguoi_tao, int $uu_tien ): array {
		global $wpdb;
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return array( 'viec' => 0, 'moi' => false, 'loi' => 'Chưa mở được lượt ghi. Thử lại sau.' );
		}
		$hang = $wpdb->get_row( $wpdb->prepare( 'SELECT trang_thai, post_id, loi FROM ' . Kho::bang() . ' WHERE id = %d FOR UPDATE', $kho_id ) );
		if ( ! $hang || 'moi' !== $hang->trang_thai || (int) $hang->post_id || '' === trim( (string) $hang->loi ) ) {
			$wpdb->query( 'ROLLBACK' );
			return array( 'viec' => 0, 'moi' => false, 'loi' => 'Câu vừa đổi trạng thái. Tải lại Kho để xem.' );
		}
		$mo = Viec::mo( 'kho', $kho_id, self::VIEC );
		if ( $mo ) {
			$wpdb->query( 'ROLLBACK' );
			return array( 'viec' => (int) $mo[0]['id'], 'moi' => false, 'loi' => '' );
		}
		$viec = Viec::tao(
			array(
				'loai'           => self::VIEC,
				'doi_tuong_loai' => 'kho',
				'doi_tuong_id'   => $kho_id,
				'noi_dung'       => $noi_dung,
				'nguoi_tao'      => $nguoi_tao,
				'uu_tien'        => $uu_tien,
			)
		);
		if ( ! $viec || false === $wpdb->query( 'COMMIT' ) ) {
			$wpdb->query( 'ROLLBACK' );
			return array( 'viec' => 0, 'moi' => false, 'loi' => 'Chưa ghi được thẻ vào Hộp việc. Thử lại sau.' );
		}
		return array( 'viec' => $viec, 'moi' => true, 'loi' => '' );
	}

	private static function kq_chon( bool $ok, bool $moi, int $viec, string $chu ): array {
		return array(
			'ok'        => $ok,
			'moi'       => $moi,
			'viec'      => $viec,
			'thong_bao' => $chu,
		);
	}

	/**
	 * Các phần của tác phẩm dựng từ một câu kho và các câu đã gộp vào nó.
	 *
	 * @return array<string,array> phần => giá trị (chưa qua KiemPhan)
	 */
	public static function phan_tu_kho( object $muc, int $bai = 0 ): array {
		global $wpdb;
		$nguon = NguonTuDong::lay( (string) $muc->nguon_id ) ?? NguonTuDong::mac_dinh();
		$goc   = static fn( array $n ): array => empty( $n['ho_so'] ) ? array() : array( 'nguon' => array( 'ho_so' => (int) $n['ho_so'] ) );
		$khoa  = array( 'kho:' . $muc->id );
		if ( $bai ) {
			$khoa[] = 'bai:' . $bai;
		}
		$gop  = $wpdb->get_results( 'SELECT * FROM ' . Kho::bang() . " WHERE trang_thai = 'da_gop' AND gop_vao IN (" . implode( ',', array_map( static fn( $k ) => $wpdb->prepare( '%s', $k ), $khoa ) ) . ') ORDER BY id' ) ?: array();
		$cac  = array_merge( array( array( $muc, $nguon ) ), array_map( static fn( $m ) => array( $m, NguonTuDong::lay( (string) $m->nguon_id ) ?? NguonTuDong::mac_dinh() ), $gop ) );
		$phan = array(
			'loi'       => array( 'dong' => explode( "\n", (string) $muc->loi ), 'nguoi_lam' => NguoiLam::MAY_CHEP ) + $goc( $nguon ),
			'nguon_may' => array( 'nguon_id' => (string) $muc->nguon_id, 'url_goc' => (string) $muc->url, 'ma_goc' => (string) $muc->ma_goc, 'ngay_lay' => substr( (string) $muc->ngay_lay, 0, 10 ) ),
		);
		// Dị bản: của câu chính giữ thứ tự; thêm lời câu gộp làm dị bản, dị bản của câu đã gộp — bỏ cái đã có
		$da     = array( VanTay::chuan_loi( (string) $muc->loi ) => true );
		$di_ban = array();
		$cau_hoi = null;
		$dap     = null;
		$ct      = array();
		$ct_da   = array();
		foreach ( $cac as $i => [ $m, $n ] ) {
			$d    = Kho::du_lieu( $m );
			$loi_db = array_map( 'strval', (array) ( $d['di_ban'] ?? array() ) );
			if ( $i > 0 && 'di_ban' === $m->gop_lam ) {
				array_unshift( $loi_db, (string) $m->loi );
			}
			foreach ( $loi_db as $l ) {
				$k = VanTay::chuan_loi( $l );
				if ( '' !== $k && ! isset( $da[ $k ] ) ) {
					$da[ $k ]  = true;
					$di_ban[] = array( 'dong' => explode( "\n", $l ), 'nguoi_lam' => NguoiLam::MAY_CHEP ) + $goc( $n );
				}
			}
			$cau_hoi ??= empty( $d['cau_hoi'] ) ? null : array( 'chu' => (string) $d['cau_hoi'], 'nguoi_lam' => NguoiLam::MAY_CHEP ) + $goc( $n );
			if ( empty( $n['chep_chu_thich'] ) ) {
				continue; // D-055: chú thích, lời giải chỉ chép từ nguồn bật "Chép chú thích"
			}
			$dap ??= empty( $d['dap_an'] ) ? null : array( 'chu' => (string) $d['dap_an'], 'nguoi_lam' => NguoiLam::MAY_CHEP ) + $goc( $n );
			foreach ( (array) ( $d['chu_thich'] ?? array() ) as $c ) {
				$tu   = trim( (string) ( $c['tu'] ?? '' ) );
				$khoa_ct = mb_strtolower( $tu ) . '|' . trim( (string) ( $c['noi_dung'] ?? '' ) );
				if ( '' === trim( (string) ( $c['noi_dung'] ?? '' ) ) || isset( $ct_da[ $khoa_ct ] ) ) {
					continue;
				}
				$ct_da[ $khoa_ct ] = true;
				$ct[]              = array_filter( array( 'tu' => $tu, 'loi_giai' => (string) $c['noi_dung'], 'nguoi_lam' => NguoiLam::MAY_CHEP ) ) + $goc( $n );
			}
		}
		$the_loai = TheLoai::TU_LOAI_GOC[ (string) $muc->loai ] ?? 'ca-dao';
		if ( $di_ban ) {
			$phan['di_ban'] = $di_ban;
		}
		if ( $ct ) {
			$phan['chu_thich'] = $ct;
		}
		if ( 'cau-do' === $the_loai && $cau_hoi ) {
			$phan['cau_hoi'] = $cau_hoi;
		}
		if ( 'cau-do' === $the_loai && $dap ) {
			$phan['dap_an'] = $dap;
		}
		return $phan;
	}

	/**
	 * Người bấm Đăng (hoặc "Sửa rồi đăng" → bản nháp) một câu mới: tạo tác phẩm từ kho.
	 *
	 * @param array{nguoi:int} $nc người bấm
	 * @return array{ok:bool,bai:int,loi:string[]}
	 */
	public static function dang( int $kho_id, array $nc, string $trang_thai = 'publish' ): array {
		global $wpdb;
		$m = Kho::lay( $kho_id );
		if ( ! $m || 'moi' !== $m->trang_thai || '' === $m->loi || $m->post_id ) {
			return array( 'ok' => false, 'bai' => 0, 'loi' => array( 'Câu này không còn chờ đăng.' ) );
		}
		$the_loai = TheLoai::TU_LOAI_GOC[ (string) $m->loai ] ?? 'ca-dao';
		$tieu_de  = trim( (string) $m->tieu_de ) ?: (string) strtok( (string) $m->loi, "\n" );
		$id       = wp_insert_post(
			wp_slash(
				array(
					'post_type'   => KhaiBao::TAC_PHAM,
					'post_status' => 'draft',
					'post_title'  => $tieu_de,
					'post_name'   => \Phongdao\DichVu\LuuPhan\DuongDan::ten_moi( $tieu_de ),
					'post_author' => (int) $nc['nguoi'],
				)
			),
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return array( 'ok' => false, 'bai' => 0, 'loi' => array( 'Chưa tạo được tác phẩm. Thử lại sau.' ) );
		}
		$gan_loai = wp_set_object_terms( $id, $the_loai, KhaiBao::THE_LOAI );
		if ( is_wp_error( $gan_loai ) || ! $gan_loai ) {
			return array( 'ok' => false, 'bai' => $id, 'loi' => array( 'Chưa gắn được thể loại cho tác phẩm; chưa đăng.' ) );
		}
		$loi = array();
		foreach ( self::phan_tu_kho( $m ) as $phan => $gia_tri ) {
			$kq  = LuuPhan::luu( $id, $phan, $gia_tri, array( 'nguoi' => (int) $nc['nguoi'], 'goi' => KiemPhan::GOI_MAY ) );
			$loi = array_merge( $loi, $kq['loi'] );
		}
		if ( $loi ) { // lời không lưu được thì không đăng; bài nháp giữ để người xem
			return array( 'ok' => false, 'bai' => $id, 'loi' => $loi );
		}
		if ( 1 !== $wpdb->update( Kho::bang(), array( 'trang_thai' => 'da_tao_bai', 'post_id' => $id ), array( 'id' => $kho_id, 'trang_thai' => 'moi', 'post_id' => 0 ) ) ) {
			return array( 'ok' => false, 'bai' => $id, 'loi' => array( 'Chưa ghi được trạng thái câu kho; chưa đăng.' ) );
		}
		ChongTrung::van_tay_kho( $kho_id );
		ChongTrung::van_tay_bai( $id );
		if ( 'publish' === $trang_thai ) {
			$da_dang = wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true );
			if ( is_wp_error( $da_dang ) || ! $da_dang || 'publish' !== get_post_status( $id ) ) {
				return array( 'ok' => false, 'bai' => $id, 'loi' => array( 'Chưa chuyển được tác phẩm sang Đăng; tải lại Hộp việc để kiểm tra.' ) );
			}
		}
		foreach ( Viec::mo( 'kho', $kho_id, self::VIEC ) as $v ) {
			if ( ! Viec::dong( $v['id'], Viec::XONG, 'publish' === $trang_thai ? 'dang' : 'sua_roi_dang', (int) $nc['nguoi'] ) ) {
				return array( 'ok' => false, 'bai' => $id, 'loi' => array( 'Chưa đóng được việc câu mới; chưa xác nhận Đăng.' ) );
			}
		}
		return array( 'ok' => true, 'bai' => $id, 'loi' => array() );
	}

	/** Người bấm Bỏ: câu ở lại kho (trạng thái bỏ qua), không hiện lại trừ khi người bấm "Xem lại". */
	public static function bo( int $kho_id, int $nguoi, string $ly_do = '' ): bool {
		global $wpdb;
		$m = Kho::lay( $kho_id );
		if ( ! $m || 'moi' !== $m->trang_thai ) {
			return false;
		}
		if ( 1 !== $wpdb->update( Kho::bang(), array( 'trang_thai' => 'bo_qua' ), array( 'id' => $kho_id, 'trang_thai' => 'moi' ) ) ) {
			return false;
		}
		ChongTrung::van_tay_kho( $kho_id );
		foreach ( Viec::mo( 'kho', $kho_id, self::VIEC ) as $v ) {
			if ( ! Viec::dong( $v['id'], Viec::BO, 'bo' . ( '' === $ly_do ? '' : ': ' . mb_substr( $ly_do, 0, 20 ) ), $nguoi ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Tác phẩm tạo từ kho: làm lại các phần từ kho (câu đã gộp, dị bản mới…) — chỉ phần còn hoàn toàn do máy chép.
	 * Phần người đã sửa (có mục người viết / người đọc góp, hoặc lời đã sửa tay) giữ nguyên.
	 */
	public static function dong_bo( int $bai_id ): bool {
		global $wpdb;
		$m = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Kho::bang() . ' WHERE post_id = %d ORDER BY id LIMIT 1', $bai_id ) );
		if ( KhaiBao::TAC_PHAM !== get_post_type( $bai_id ) ) {
			return false;
		}
		if ( ! $m ) {
			return true; // bài nhập tay không có phần do máy chép để đồng bộ
		}
		foreach ( self::phan_tu_kho( $m, $bai_id ) as $phan => $gia_tri ) {
			$cu = PhanBai::lay( $bai_id, $phan );
			if ( null !== $cu && ! self::chi_may( $cu ) ) {
				continue;
			}
			if ( ! LuuPhan::luu( $bai_id, $phan, $gia_tri, array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) )['ok'] ) {
				return false;
			}
		}
		return true;
	}

	/** Phần (hoặc mọi mục của phần) do máy chép. */
	private static function chi_may( array $gia_tri ): bool {
		$muc = isset( $gia_tri['muc'] ) ? $gia_tri['muc'] : array( $gia_tri );
		foreach ( $muc as $x ) {
			if ( isset( $x['nguoi_lam'] ) && NguoiLam::MAY_CHEP !== $x['nguoi_lam'] ) {
				return false;
			}
		}
		return true;
	}
}
