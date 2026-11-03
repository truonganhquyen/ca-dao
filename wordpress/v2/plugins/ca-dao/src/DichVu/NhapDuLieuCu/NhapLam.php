<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * Nhập thật và hoàn tác (docs/29 mục 3, 4: `wp phongdao nhap --lam` / `--hoan-tac`).
 *
 * - Mỗi bài MVP thành tác phẩm **giữ ID** (bình luận, lượt đọc, phân tích vẫn nối đúng); các phần lưu qua LuuPhan
 *   (gọi nhap_cu — mỗi phần một dòng nhật ký), đúng kế hoạch của bước nhập thử (NhapThu::ke_hoach).
 * - Mọi thứ đổi tại chỗ (loại bài, trạng thái, tên đường dẫn, nội dung, thể loại, câu kho) ghi giá trị cũ vào bảng tạm
 *   wp_cadao_chuyen_doi; hồ sơ nguồn tạo mới cũng ghi lại để hoàn tác xoá.
 * - Mỗi bài trong một giao dịch: dừng giữa chừng thì bài đang làm trả về như cũ; chạy lại làm tiếp, kết quả như nhau.
 * - Không xoá dữ liệu MVP nào (meta _cadao_*, bảng, phân loại cũ giữ tới khi người sáng lập cho dọn, docs/29 mục 6).
 */
final class NhapLam {

	/** Trạng thái chuyển đổi: cho (V2 đã thay chỗ, chưa nhập) | dang | xong. Khách thấy trang nâng cấp khi chưa xong. */
	public const TRANG_THAI = 'phongdao_chuyen_doi';

	public const NGUOI_TAO = KiemPhan::GOI_NHAP_CU;

	public static function bang(): string {
		global $wpdb;
		return $wpdb->prefix . 'cadao_chuyen_doi';
	}

	public static function tao_bang(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			'CREATE TABLE ' . self::bang() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			loai varchar(16) NOT NULL,
			doi_tuong bigint(20) unsigned NOT NULL DEFAULT 0,
			truoc longtext NULL,
			luc datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY doi_tuong (loai,doi_tuong)
			) " . $wpdb->get_charset_collate() . ';'
		);
	}

	/** Khách chưa đăng nhập thấy trang "Thư viện đang được nâng cấp" (docs/29 mục 5 bước 2). */
	public static function dang_nang_cap(): bool {
		return in_array( get_option( self::TRANG_THAI ), array( 'cho', 'dang' ), true );
	}

	/**
	 * Nhập các bài MVP còn lại.
	 *
	 * @param int $so tối đa bao nhiêu bài lần này (0 = hết)
	 * @return array{nhap:int, khong_mang:int, can_xem:int, loi:string[], con:int}
	 */
	public static function chay( int $so = 0 ): array {
		global $wpdb;
		self::tao_bang();
		update_option( self::TRANG_THAI, 'dang' );
		$bc  = array(
			'nhap'       => 0,
			'khong_mang' => 0,
			'can_xem'    => 0,
			'loi'        => array(),
		);
		$nc  = self::ngu_canh();
		$ten = NhapThu::ten_da_co();
		$lam = 0;
		foreach ( self::con_lai() as $id ) {
			if ( $so && $lam >= $so ) {
				break;
			}
			$bai = DuLieuCu::bai( $id );
			if ( ! $bai ) {
				continue;
			}
			++$lam;
			$kh = NhapThu::ke_hoach( $bai, $nc, $ten );
			$wpdb->query( 'START TRANSACTION' );
			try {
				if ( 'khong_mang' === $kh['cach'] ) {
					self::bo( $bai );
					++$bc['khong_mang'];
				} else {
					$loi = self::nhap_bai( $bai, $kh );
					if ( $loi ) {
						throw new \RuntimeException( implode( ' ', $loi ) );
					}
					++$bc['nhap'];
					$bc['can_xem'] += $kh['can_xem'] ? 1 : 0;
				}
				$wpdb->query( 'COMMIT' );
			} catch ( \Throwable $e ) {
				$wpdb->query( 'ROLLBACK' );
				clean_post_cache( $id );
				$bc['loi'][] = "bài $id: " . $e->getMessage();
				$nc          = self::ngu_canh(); // hồ sơ vừa tạo trong giao dịch hỏng đã bị huỷ theo
			}
		}
		$bc['con'] = count( self::con_lai() );
		// Mọi bài đã nhập: tới mục từ, trò chơi, mô-típ, tình huống, Bạn có biết, Wikidata (một lần)
		if ( ! $bc['con'] && ! $bc['loi'] && ! self::da_co( 'phan_loai_xong' ) ) {
			$wpdb->query( 'START TRANSACTION' );
			try {
				$loi = NhapPhanLoai::lam( $nc['ho_so'], self::quan_tri() );
				if ( $loi ) {
					throw new \RuntimeException( implode( ' · ', $loi ) );
				}
				$wpdb->query( 'COMMIT' );
			} catch ( \Throwable $e ) {
				$wpdb->query( 'ROLLBACK' );
				clean_taxonomy_cache( 'muc_tu' );
				clean_taxonomy_cache( 'tu_kho' );
				$bc['loi'][] = 'phân loại: ' . $e->getMessage();
			}
		}
		if ( ! $bc['con'] && ! $bc['loi'] ) {
			update_option( self::TRANG_THAI, 'xong' );
			\Phongdao\DichVu\GoiY\GoiY::bat_dau(); // gợi ý chủ đề, vùng một lượt cho các bài vừa nhập
		}
		return $bc;
	}

	/** Lúc sửa cuối (GMT) của bài trên MVP, đọc từ giá trị cũ đã ghi khi nhập; rỗng khi bài chưa nhập. */
	public static function sua_luc_mvp( int $id ): string {
		global $wpdb;
		$truoc = json_decode( (string) $wpdb->get_var( $wpdb->prepare( 'SELECT truoc FROM ' . self::bang() . " WHERE loai = 'bai' AND doi_tuong = %d ORDER BY id DESC LIMIT 1", $id ) ), true );
		return (string) ( $truoc['sua_luc'][1] ?? '' );
	}

	private static function da_co( string $loai ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::bang() . ' WHERE loai = %s', $loai ) );
	}

	/** Người ghi nhật ký cho phần không thuộc bài nào: người chạy lệnh, hoặc quản trị đầu tiên. */
	private static function quan_tri(): int {
		$ds = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'fields' => 'ID' ) );
		return get_current_user_id() ?: (int) ( $ds[0] ?? 0 );
	}

	/** Bài MVP chưa nhập (bỏ bài đã ghi là không mang). */
	public static function con_lai(): array {
		global $wpdb;
		$bo = DuLieuCu::co_bang( self::bang() ) ? array_map( 'intval', $wpdb->get_col( 'SELECT doi_tuong FROM ' . self::bang() . " WHERE loai = 'bo'" ) ) : array();
		return array_values( array_diff( DuLieuCu::bai_ids(), $bo ) );
	}

	/** @return string[] lỗi (rỗng = xong) */
	private static function nhap_bai( array $bai, array $kh ): array {
		global $wpdb;
		$id = $bai['id'];
		self::ghi_doi(
			'bai',
			$id,
			array(
				'post_type'    => DuLieuCu::LOAI_BAI,
				'post_status'  => $bai['trang_thai'],
				'post_name'    => $bai['ten'],
				'post_content' => $bai['noi_dung'],
				'post_excerpt' => $bai['tom_tat'],
				'sua_luc'      => $bai['sua_luc'],
				'ngay_tao'     => $bai['ngay_tao'], // WordPress đặt lại ngày của bản nháp khi lưu nội dung
				'the_loai'     => $bai['the_loai'],
				'ten_cu'       => null !== $kh['ten_moi'],
			)
		);
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_type'   => KhaiBao::TAC_PHAM,
				'post_status' => $kh['trang_thai'],
				'post_name'   => $kh['ten_moi'] ?? $bai['ten'],
			),
			array( 'ID' => $id )
		);
		clean_post_cache( $id );
		if ( null !== $kh['ten_moi'] ) {
			add_post_meta( $id, '_wp_old_slug', $bai['ten'] ); // đường dẫn cũ chuyển hướng 301 (WordPress)
		}
		wp_set_object_terms( $id, $kh['the_loai'], KhaiBao::THE_LOAI );

		$nc  = array(
			'nguoi' => $bai['sua_cuoi'] ?: $bai['tac_gia'],
			'goi'   => KiemPhan::GOI_NHAP_CU,
			'luc'   => $bai['ngay'],
		);
		$loi = array();
		foreach ( $kh['phan'] as $phan => $gia_tri ) {
			$kq = LuuPhan::luu( $id, $phan, KiemPhan::noi_dung( $gia_tri ), $nc );
			if ( ! $kq['ok'] ) {
				$loi[] = "$phan: " . implode( ' ', $kq['loi'] );
			}
		}
		if ( $kh['nhap'] && ! Viec::mo( 'post', $id, 'nhap_y_nghia' ) ) {
			Viec::tao(
				array(
					'loai'           => 'nhap_y_nghia',
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'phan'           => 'y_nghia',
					'noi_dung'       => $kh['nhap'],
					'nguoi_tao'      => self::NGUOI_TAO,
					'uu_tien'        => 35,
				)
			);
		}
		// Bài người sáng lập đã duyệt gợi ý vùng, chủ đề trên MVP: việc gợi ý đã quyết cho cả bài, máy không gợi ý lại
		if ( ! empty( $bai['meta']['_cadao_goi_y_xong'] ) && ! Viec::cua( 'post', $id, 'goi_y' ) ) {
			Viec::tao(
				array(
					'loai'           => 'goi_y',
					'trang_thai'     => Viec::XONG,
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'phan'           => 'goi_y',
					'noi_dung'       => array( 'loai' => '*', 'ma' => '*', 'ly_do' => 'đã duyệt gợi ý vùng, chủ đề trên bản cũ' ),
					'nguoi_tao'      => self::NGUOI_TAO,
					'quyet'          => 'da_duyet_mvp',
					'quyet_luc'      => $bai['ngay'],
				)
			);
		}
		if ( $kh['can_xem'] && ! Viec::mo( 'post', $id, 'can_xem' ) ) {
			Viec::tao(
				array(
					'loai'           => 'can_xem',
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'noi_dung'       => array( 'ly_do' => $kh['can_xem'] ),
					'nguoi_tao'      => self::NGUOI_TAO,
					'uu_tien'        => 40,
				)
			);
		}
		return $loi;
	}

	/** Chờ duyệt do máy tạo, chưa ai đụng: không mang bài; câu trong kho trở lại "mới" để thành thẻ Câu mới. */
	private static function bo( array $bai ): void {
		global $wpdb;
		foreach ( $bai['kho'] as $h ) {
			if ( ! $h['chinh'] ) {
				continue;
			}
			$m = Kho::lay( $h['id'] );
			self::ghi_doi(
				'kho',
				$h['id'],
				array(
					'trang_thai' => $m->trang_thai,
					'post_id'    => (int) $m->post_id,
				)
			);
			$wpdb->update( Kho::bang(), array( 'trang_thai' => 'moi', 'post_id' => 0 ), array( 'id' => $h['id'] ) );
			ChongTrung::van_tay_kho( $h['id'] );
		}
		ChongTrung::xoa( 'bai:' . $bai['id'] ); // bài không còn: câu kho không bị báo trùng với nó
		self::ghi_doi( 'bo', $bai['id'], null );
	}

	/**
	 * Hoàn tác mọi bước nhập (mới nhất trước): đặt lại bài, câu kho; xoá phần _phongdao_*, việc và nhật ký do bước nhập
	 * tạo, hồ sơ nguồn tạo mới. Dữ liệu MVP không bị đụng nên trở về đúng như trước khi nhập.
	 *
	 * @return array{bai:int, kho:int, ho_so:int}
	 */
	public static function hoan_tac(): array {
		global $wpdb;
		$bc = array(
			'bai'   => 0,
			'kho'   => 0,
			'ho_so' => 0,
		);
		if ( ! DuLieuCu::co_bang( self::bang() ) ) {
			return $bc;
		}
		foreach ( $wpdb->get_results( 'SELECT * FROM ' . self::bang() . ' ORDER BY id DESC' ) as $r ) {
			$id    = (int) $r->doi_tuong;
			$truoc = json_decode( (string) $r->truoc, true );
			if ( NhapPhanLoai::hoan_tac_dong( $r ) ) {
				$wpdb->delete( self::bang(), array( 'id' => (int) $r->id ) );
				continue;
			}
			if ( 'bai' === $r->loai && is_array( $truoc ) ) {
				$wpdb->update(
					$wpdb->posts,
					array(
						'post_type'    => $truoc['post_type'],
						'post_status'  => $truoc['post_status'],
						'post_name'    => $truoc['post_name'],
						'post_content'      => $truoc['post_content'],
						'post_excerpt'      => $truoc['post_excerpt'],
						'post_modified'     => $truoc['sua_luc'][0],
						'post_modified_gmt' => $truoc['sua_luc'][1],
						'post_date'         => $truoc['ngay_tao'][0],
						'post_date_gmt'     => $truoc['ngay_tao'][1],
					),
					array( 'ID' => $id )
				);
				foreach ( Phan::ds( KhaiBao::TAC_PHAM ) as $phan ) {
					delete_post_meta( $id, Phan::khoa( $phan ) );
				}
				if ( ! empty( $truoc['ten_cu'] ) ) {
					delete_post_meta( $id, '_wp_old_slug', $truoc['post_name'] );
				}
				wp_set_object_terms( $id, $truoc['the_loai'], KhaiBao::THE_LOAI );
				wp_delete_object_term_relationships( $id, 'hinh_thuc' );
				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'post' AND doi_tuong_id = %d AND ( nguoi_tao = %s OR ( loai = 'goi_y' AND nguoi_tao = 'may' ) )", $id, self::NGUOI_TAO ) ); // cả thẻ gợi ý máy tạo sau khi nhập
				clean_post_cache( $id );
				++$bc['bai'];
			} elseif ( 'kho' === $r->loai && is_array( $truoc ) ) {
				$wpdb->update( Kho::bang(), $truoc, array( 'id' => $id ) );
				ChongTrung::xoa( 'kho:' . $id );
				++$bc['kho'];
			} elseif ( 'ho_so' === $r->loai ) {
				wp_delete_post( $id, true );
				++$bc['ho_so'];
			}
			$wpdb->delete( self::bang(), array( 'id' => (int) $r->id ) );
		}
		update_option( self::TRANG_THAI, 'cho' );
		update_option( \Phongdao\DichVu\GoiY\GoiY::SAU, -1, false ); // dừng lượt gợi ý cho bài đã trả về MVP
		return $bc;
	}

	/** Hồ sơ theo mã nguồn tự động; hồ sơ cho nguồn người điền tạo khi cần (ghi lại để hoàn tác). */
	private static function ngu_canh(): array {
		$bo_qua = array( 'ho_so_moi' => array() );
		$nc     = NhapThu::ngu_canh( $bo_qua );
		foreach ( $nc['ho_so_nguon'] as $ma => $hs ) {
			if ( $hs >= 900000000 ) { // nguồn tự động chưa có hồ sơ (site thật chỉ có Ca dao Mẹ, đã có từ bước cài)
				$nc['ho_so_nguon'][ $ma ] = self::ho_so( 'web', $ma, (string) $ma, '' );
			}
		}
		$da          = array();
		$nguon       = $nc;
		$nc['ho_so'] = static function ( string $loai, string $ten, string $url ) use ( &$da, $nguon ): int {
			$khoa = NhapThu::khoa_ho_so( $loai, $ten );
			return $da[ $khoa ] ??= ( NhapThu::hs_theo_ten( $nguon, $ten ) ?: self::ho_so( $loai, $khoa, $ten, $url ) );
		};
		return $nc;
	}

	private static function ho_so( string $loai, string $khoa, string $ten, string $url ): int {
		$co = HoSo::theo_khoa( $khoa );
		if ( $co ) {
			return $co->ID;
		}
		$id = HoSo::tao( $khoa, $ten, array_filter( array( 'loai' => $loai, 'url' => $url ) ) );
		self::ghi_doi( 'ho_so', $id, null );
		return $id;
	}

	public static function ghi_doi( string $loai, int $doi_tuong, ?array $truoc ): void {
		global $wpdb;
		$wpdb->insert(
			self::bang(),
			array(
				'loai'      => $loai,
				'doi_tuong' => $doi_tuong,
				'truoc'     => null === $truoc ? null : wp_json_encode( $truoc, JSON_UNESCAPED_UNICODE ),
				'luc'       => gmdate( 'Y-m-d H:i:s' ),
			)
		);
	}
}
