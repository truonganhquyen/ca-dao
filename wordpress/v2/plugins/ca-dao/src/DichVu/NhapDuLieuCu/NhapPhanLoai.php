<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Phần nhập sau các bài (docs/29 mục 2): mục từ, trò chơi, mô-típ, tình huống → tuyển tập, "Bạn có biết" đã duyệt,
 * quyết định Wikidata. Chạy một lần khi mọi bài đã nhập; mọi thứ ghi vào wp_cadao_chuyen_doi để hoàn tác.
 *
 * - Mục từ: phân loại tu_kho đổi tên tại chỗ thành muc_tu (giữ ID, đường dẫn /tu-dien/<tên>/ như cũ); lời giải vẫn
 *   là mô tả của term; term meta MVP chép sang khoá V2 (_cadao_* giữ nguyên tới khi dọn).
 * - Trò chơi, mô-típ: cùng tên phân loại ở V2; term meta chép sang khoá V2.
 * - Tình huống: mỗi tình huống có bài thành một tuyển tập loại tinh_huong (cùng tên đường dẫn), mục = các bài, ghi chú
 *   = "Hợp khi…" của bài; đăng khi đủ bài như MVP hiện trang (≥ 2), không thì để nháp.
 * - "Bạn có biết" người sáng lập đã duyệt → phần ban_co_biet của bài (người viết: máy ghép theo khuôn từ lời giải có
 *   sẵn, người sáng lập duyệt; không dùng AI nên không gắn dấu AI). Câu chờ duyệt không mang (máy ghép lại).
 * - Quyết định Wikidata (Đúng / Sai / Bỏ) → việc wikidata đã quyết trong bảng việc; xác nhận Đúng của mục từ còn nằm
 *   ở term meta.
 */
final class NhapPhanLoai {

	/** Ngưỡng MVP hiện trang tình huống (CADAO_NGUONG_TINH_HUONG). */
	private const NGUONG_TINH_HUONG = 2;

	/**
	 * Kế hoạch — chỉ đọc (bước nhập thử dùng để in, bước nhập thật làm đúng theo đây).
	 *
	 * @return array{muc_tu:int[], tro_choi:int[], mo_tip:int[], tinh_huong:list<array>, ban_co_biet:list<array>, wikidata:list<array>, ghi_chu:string[]}
	 */
	public static function ke_hoach(): array {
		global $wpdb;
		$ids = static fn( string $tax ): array => array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s ORDER BY term_id", $tax ) ) );
		$kh  = array(
			'muc_tu'      => $ids( 'tu_kho' ),
			'tro_choi'    => array_values( array_filter( $ids( 'tro_choi' ), static fn( $t ) => '' !== self::meta_the( $t, '_cadao_cc_buoc' ) . self::meta_the( $t, '_cadao_cc_so_nguoi' ) . self::meta_the( $t, '_cadao_cc_do_tuoi' ) . self::meta_the( $t, '_cadao_cc_do_dung' ) ) ),
			'mo_tip'      => array_values( array_filter( $ids( 'mo_tip' ), static fn( $t ) => '' !== self::meta_the( $t, '_cadao_mo_tip_dau' ) ) ),
			'tinh_huong'  => array(),
			'ban_co_biet' => array(),
			'wikidata'    => array(),
			'ghi_chu'     => array(),
		);
		foreach ( $wpdb->get_results( "SELECT t.term_id, t.name, t.slug, tt.description, tt.term_taxonomy_id FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'tinh_huong' ORDER BY t.term_id" ) as $t ) {
			$bai = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->term_relationships} r ON r.object_id = p.ID WHERE r.term_taxonomy_id = %d AND p.post_type IN (%s, %s) AND p.post_status = 'publish' ORDER BY p.post_title, p.ID",
					$t->term_taxonomy_id,
					DuLieuCu::LOAI_BAI,
					KhaiBao::TAC_PHAM
				)
			);
			if ( $bai ) {
				$kh['tinh_huong'][] = array(
					'ten'    => (string) $t->name,
					'slug'   => (string) $t->slug,
					'mo_dau' => (string) $t->description,
					'bai'    => array_map( 'intval', $bai ),
				);
			}
		}
		$co_bai = array();
		foreach ( (array) get_option( 'cadao_ban_co_biet', array() ) as $x ) {
			if ( 'duyet' !== ( $x['trang_thai'] ?? '' ) || '' === trim( (string) ( $x['chu'] ?? '' ) ) || empty( $x['bai'] ) ) {
				continue;
			}
			$b = (int) $x['bai'];
			if ( isset( $co_bai[ $b ] ) ) {
				$kh['ghi_chu'][] = "bài $b có hơn một câu \"Bạn có biết\" đã duyệt: mang câu đầu, câu sau giữ ở dữ liệu MVP";
				continue;
			}
			$co_bai[ $b ]          = true;
			$kh['ban_co_biet'][] = array(
				'bai' => $b,
				'chu' => trim( (string) $x['chu'] ),
				'luc' => (string) ( $x['luc'] ?? '' ),
			);
		}
		$wd = $wpdb->prefix . 'cadao_xac_nhan';
		if ( DuLieuCu::co_bang( $wd ) ) {
			foreach ( $wpdb->get_results( "SELECT * FROM $wd WHERE trang_thai IN ('dung','sai','bo') ORDER BY id" ) as $r ) {
				[ $loai, $id ] = array_pad( explode( ':', (string) $r->khoa, 2 ), 2, '' );
				$kh['wikidata'][] = array(
					'doi_tuong_loai' => 'tu' === $loai ? 'term' : 'post',
					'doi_tuong_id'   => (int) $id,
					'quyet'          => (string) $r->trang_thai,
					'noi_dung'       => array(
						'khoa'    => (string) $r->khoa,
						'nhan'    => (string) $r->nhan,
						'wd'      => (string) $r->wd,
						'du_lieu' => json_decode( (string) $r->du_lieu, true ) ?: array(),
					),
					'luc'            => (string) $r->ngay,
				);
			}
		}
		return $kh;
	}

	/**
	 * Làm theo kế hoạch. $ho_so: bộ tìm / tạo hồ sơ của bước nhập (NhapLam).
	 *
	 * @return string[] lỗi
	 */
	public static function lam( callable $ho_so, int $nguoi ): array {
		global $wpdb;
		$kh  = self::ke_hoach();
		$loi = array();

		// Mục từ: đổi tên phân loại tại chỗ
		if ( $kh['muc_tu'] ) {
			if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'muc_tu'" ) ) {
				return array( 'đã có mục từ V2: không đổi tên phân loại tu_kho để tránh trùng tên đường dẫn' );
			}
			NhapLam::ghi_doi( 'phan_loai', 0, array( 'tu' => 'tu_kho', 'den' => 'muc_tu', 'the' => $kh['muc_tu'] ) );
			$wpdb->query( "UPDATE {$wpdb->term_taxonomy} SET taxonomy = 'muc_tu' WHERE taxonomy = 'tu_kho'" );
			foreach ( $kh['muc_tu'] as $t ) {
				$chep = '' !== self::meta_the( $t, '_cadao_chep' );
				$ten  = self::meta_the( $t, '_cadao_nguon_ten' );
				$url  = self::meta_the( $t, '_cadao_nguon_url' );
				self::ghi_meta(
					$t,
					array(
						'_phongdao_khoa'     => self::meta_the( $t, '_cadao_khoa' ),
						'_phongdao_loi_giai' => array_filter(
							array(
								'nguoi_lam' => $chep ? NguoiLam::MAY_CHEP : NguoiLam::NGUOI_VIET,
								'nguon'     => '' === $ten ? null : array_filter( array( 'ho_so' => $ho_so( 'web', $ten, $url ), 'url' => $url ) ),
							)
						),
						'_phongdao_wikidata' => get_term_meta( $t, '_cadao_wd', true ) ?: null,
						'_phongdao_anh'      => array_filter(
							array(
								'rieng'    => (int) self::meta_the( $t, '_cadao_anh_rieng' ),
								'ghi_cong' => self::meta_the( $t, '_cadao_anh_ghi_cong' ),
								'an'       => '' !== self::meta_the( $t, '_cadao_an_anh' ),
							)
						) ?: null,
					)
				);
			}
			clean_taxonomy_cache( 'muc_tu' );
		}

		// Trò chơi: cách chơi
		foreach ( $kh['tro_choi'] as $t ) {
			$ten  = self::meta_the( $t, '_cadao_cc_nguon_ten' );
			$url  = self::meta_the( $t, '_cadao_cc_nguon_url' );
			$wiki = str_starts_with( $ten, 'Wikipedia' ) || str_contains( $url, 'wikipedia.org' );
			self::ghi_meta(
				$t,
				array(
					'_phongdao_cach_choi' => array_filter(
						array(
							'so_nguoi'  => self::meta_the( $t, '_cadao_cc_so_nguoi' ),
							'do_tuoi'   => self::meta_the( $t, '_cadao_cc_do_tuoi' ),
							'do_dung'   => self::meta_the( $t, '_cadao_cc_do_dung' ),
							'buoc'      => array_values( array_filter( array_map( 'trim', explode( "\n", self::meta_the( $t, '_cadao_cc_buoc' ) ) ), 'strlen' ) ),
							// Bấm nút chép từ Wikipedia (MVP ghi tên "Wikipedia — <bài>") = máy chép; còn lại người viết
							'nguoi_lam' => $wiki ? NguoiLam::MAY_CHEP : NguoiLam::NGUOI_VIET,
							'nguon'     => '' === $ten ? null : array_filter(
								array(
									'ho_so'   => $wiki ? $ho_so( 'wiki', 'Wikipedia tiếng Việt', 'https://vi.wikipedia.org' ) : $ho_so( 'web', $ten, $url ),
									'url'     => $url,
									'ghi_chu' => $wiki ? trim( (string) preg_replace( '/^Wikipedia\s*—\s*/u', '', $ten ) ) : '',
								)
							),
							'giay_phep' => self::meta_the( $t, '_cadao_cc_giay_phep' ),
							'wiki'      => self::meta_the( $t, '_cadao_cc_wiki' ),
						)
					),
				)
			);
		}

		// Mô-típ: dấu nhận nhóm của máy (tên do người đặt nằm ở tên term)
		foreach ( $kh['mo_tip'] as $t ) {
			self::ghi_meta( $t, array( '_phongdao_mo_tip_dau' => get_term_meta( $t, '_cadao_mo_tip_dau', true ) ?: null ) );
		}

		// Tình huống → tuyển tập
		$nc = array(
			'nguoi' => $nguoi,
			'goi'   => KiemPhan::GOI_NHAP_CU,
		);
		foreach ( $kh['tinh_huong'] as $th ) {
			$id = wp_insert_post(
				wp_slash(
					array(
						'post_type'   => KhaiBao::TUYEN_TAP,
						'post_status' => count( $th['bai'] ) >= self::NGUONG_TINH_HUONG ? 'publish' : 'draft',
						'post_title'  => $th['ten'],
						'post_name'   => $th['slug'],
						'post_author' => $nguoi,
					)
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				$loi[] = "tình huống {$th['slug']}: " . $id->get_error_message();
				continue;
			}
			NhapLam::ghi_doi( 'tuyen_tap', (int) $id, null );
			$phan = array(
				'thong_tin' => array( 'loai' => 'tinh_huong' ),
				'muc'       => array_map(
					static fn( int $b ): array => array_filter(
						array(
							'tac_pham_id' => $b,
							'ghi_chu'     => trim( (string) get_post_meta( $b, 'hop_khi', true ) ),
						)
					),
					$th['bai']
				),
			);
			if ( '' !== trim( $th['mo_dau'] ) ) {
				$phan['loi_dan'] = array( 'chu' => $th['mo_dau'], 'nguoi_lam' => NguoiLam::NGUOI_VIET );
			}
			// Giờ nhật ký: lúc sửa cuối trên MVP mới nhất trong các bài của tình huống (nhập lại sau hoàn tác cho cùng kết quả)
			$luc = (string) max( array_map( array( NhapLam::class, 'sua_luc_mvp' ), $th['bai'] ) );
			foreach ( $phan as $p => $v ) {
				$kq = LuuPhan::luu( (int) $id, $p, $v, $nc + array( 'luc' => $luc ) );
				if ( ! $kq['ok'] ) {
					$loi[] = "tuyển tập {$th['slug']} phần $p: " . implode( ' ', $kq['loi'] );
				}
			}
		}

		// Bạn có biết đã duyệt → phần của tác phẩm
		foreach ( $kh['ban_co_biet'] as $x ) {
			if ( KhaiBao::TAC_PHAM !== get_post_type( $x['bai'] ) ) {
				continue; // bài không mang (thùng rác, chờ duyệt máy tạo)
			}
			$kq = LuuPhan::luu(
				$x['bai'],
				'ban_co_biet',
				array(
					'chu'       => $x['chu'],
					'ai'        => false,
					'nguoi_lam' => NguoiLam::NGUOI_VIET,
				),
				$nc + array( 'luc' => '' === $x['luc'] ? '' : get_gmt_from_date( $x['luc'] ) ) // lúc câu được ghép trên MVP
			);
			if ( ! $kq['ok'] ) {
				$loi[] = "Bạn có biết của bài {$x['bai']}: " . implode( ' ', $kq['loi'] );
			}
		}

		// Quyết định Wikidata → việc đã quyết
		foreach ( $kh['wikidata'] as $w ) {
			Viec::tao(
				array(
					'loai'           => 'wikidata',
					'trang_thai'     => Viec::XONG,
					'doi_tuong_loai' => $w['doi_tuong_loai'],
					'doi_tuong_id'   => $w['doi_tuong_id'],
					'noi_dung'       => $w['noi_dung'],
					'nguoi_tao'      => NhapLam::NGUOI_TAO,
					'tao_luc'        => get_gmt_from_date( $w['luc'] ),
					'quyet'          => $w['quyet'],
					'nguoi_quyet'    => $nguoi,
					'quyet_luc'      => get_gmt_from_date( $w['luc'] ),
				)
			);
		}
		NhapLam::ghi_doi( 'phan_loai_xong', 0, null );
		return $loi;
	}

	/** Hoàn tác một dòng của bảng chuyển đổi thuộc phần này; false = không phải dòng của phần này. */
	public static function hoan_tac_dong( object $r ): bool {
		global $wpdb;
		$truoc = json_decode( (string) $r->truoc, true );
		switch ( $r->loai ) {
			case 'phan_loai':
				$the = array_map( 'intval', (array) ( $truoc['the'] ?? array() ) );
				if ( $the ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s AND term_id IN (" . implode( ',', $the ) . ')', $truoc['tu'], $truoc['den'] ) );
					clean_taxonomy_cache( $truoc['den'] );
				}
				return true;
			case 'the_meta':
				foreach ( (array) $truoc as $khoa ) {
					delete_term_meta( (int) $r->doi_tuong, (string) $khoa );
				}
				return true;
			case 'tuyen_tap':
				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'post' AND doi_tuong_id = %d AND nguoi_tao = %s", (int) $r->doi_tuong, NhapLam::NGUOI_TAO ) );
				wp_delete_post( (int) $r->doi_tuong, true );
				return true;
			case 'phan_loai_xong':
				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Viec::bang() . " WHERE loai = 'wikidata' AND nguoi_tao = %s", NhapLam::NGUOI_TAO ) );
				return true;
		}
		return false;
	}

	/** Ghi term meta V2 (bỏ giá trị rỗng), nhớ các khoá đã ghi để hoàn tác xoá. */
	private static function ghi_meta( int $t, array $meta ): void {
		$meta = array_filter( $meta, static fn( $v ) => null !== $v && '' !== $v && array() !== $v );
		if ( ! $meta ) {
			return;
		}
		NhapLam::ghi_doi( 'the_meta', $t, array_keys( $meta ) );
		foreach ( $meta as $k => $v ) {
			update_term_meta( $t, $k, wp_slash( $v ) );
		}
	}

	/** Term meta đọc thẳng bảng: phân loại cũ có thể không còn được khai báo. */
	private static function meta_the( int $t, string $khoa ): string {
		$v = get_term_meta( $t, $khoa, true );
		return is_scalar( $v ) ? trim( (string) $v ) : '';
	}
}
