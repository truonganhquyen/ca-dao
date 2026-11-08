<?php

namespace Phongdao\DichVu\XepViec;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\UngVien\UngVien;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Quyết một thẻ trong Hộp việc (docs/28 mục 4.1). Mỗi lần bấm ghi lại ai, lúc nào, quyết gì (bảng việc). Máy không bao giờ
 * tự quyết: mọi hàm ở đây chỉ chạy khi người có quyền bấm (kiểm ở lớp gọi).
 */
final class QuyetViec {

	/** Loại việc => [nhãn thẻ, các nút: mã => [chữ, kiểu nút]]. */
	public const LOAI = array(
		'cau_moi'      => array( 'Câu mới từ nguồn', array( 'dang' => array( 'Đăng', 'primary' ), 'sua' => array( 'Sửa rồi đăng', '' ), 'de_sau' => array( 'Để sau', 'ghost' ), 'bo' => array( 'Bỏ', 'ghost' ) ) ),
		'nhap_y_nghia' => array( 'Nháp ý nghĩa', array( 'duyet' => array( 'Duyệt', 'primary' ), 'bo' => array( 'Bỏ nháp', 'ghost' ) ) ),
		'co_the_trung' => array( 'Có thể trùng', array( 'cung' => array( 'Cùng một bài', 'primary' ), 'di_ban' => array( 'Gộp làm dị bản', '' ), 'khac' => array( 'Là bài khác', '' ), 'de_sau' => array( 'Để sau', 'ghost' ) ) ),
		// Bài nhập từ bản cũ có chỗ máy không tách chắc chắn (docs/29 mục 3): người sửa trên trang bài rồi đánh dấu xong
		'can_xem'      => array( 'Cần xem', array( 'xong' => array( 'Đã xem, ổn rồi', 'primary' ), 'de_sau' => array( 'Để sau', 'ghost' ) ) ),
		// Người đọc gửi: cảm nghĩ, bài cảm nhận thì Hiện (cần quyền duyệt); góp ý thì người biên soạn sửa bài rồi đánh dấu xong
		'goi_y'        => array( 'Gợi ý chủ đề, vùng', array( 'dong_y' => array( 'Đồng ý', 'primary' ), 'de_sau' => array( 'Để sau', 'ghost' ), 'bo' => array( 'Bỏ', 'ghost' ) ) ),
		'nguoi_doc'    => array( 'Người đọc gửi', array( 'duyet' => array( 'Hiện trên trang', 'primary' ), 'xong' => array( 'Đã xử lý', 'primary' ), 'de_sau' => array( 'Để sau', 'ghost' ), 'bo' => array( 'Bỏ', 'ghost' ) ) ),
	);

	/** Nút hợp với một việc "Người đọc gửi": cảm nghĩ, bài cảm nhận có Hiện; góp ý có Đã xử lý. */
	public static function nut_nguoi_doc( array $v ): array {
		$hien = 'comment' === $v['doi_tuong_loai'] || 'phan_tich' === ( $v['noi_dung']['loai'] ?? '' );
		return array_diff_key( self::LOAI['nguoi_doc'][1], array( $hien ? 'xong' : 'duyet' => 1 ) );
	}

	/**
	 * @return array{ok:bool,loi:string[],thong_bao:string,chuyen:string} chuyen: đường dẫn nên mở tiếp (rỗng = về Hộp việc)
	 */
	public static function quyet( int $id, string $quyet, int $nguoi, bool $duyet, int $phien = -1 ): array {
		global $wpdb;
		$v = Viec::lay( $id );
		if ( ! $v || ! in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ) {
			return self::kq( false, 'Việc này đã được quyết rồi.' );
		}
		if ( $phien >= 0 && $phien !== $v['phien'] ) {
			return self::kq( false, 'Thẻ đã đổi ở phiên khác. Tải lại Hộp việc để xem quyết định mới.' );
		}
		if ( ! isset( self::LOAI[ $v['loai'] ][1][ $quyet ] ) ) {
			return self::kq( false, 'Không có lựa chọn này cho việc này.' );
		}
		if ( Viec::DE_SAU === $v['trang_thai'] && 'de_sau' === $quyet ) {
			return self::kq( false, 'Thẻ đã ở mục Để sau.' );
		}
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return self::kq( false, 'Chưa mở được lượt quyết việc. Thử lại sau.' );
		}
		if ( ! Viec::giu( $id, $v['phien'] ) ) {
			$wpdb->query( 'ROLLBACK' );
			return self::kq( false, 'Thẻ đã đổi ở phiên khác. Tải lại Hộp việc để xem quyết định mới.' );
		}
		try {
			$kq = self::thuc_hien( $v, $quyet, $nguoi, $duyet );
		} catch ( \Throwable $e ) {
			$kq = self::kq( false, 'Chưa lưu được quyết định. Thử lại sau.' );
		}
		$sau = Viec::lay( $id );
		if ( ! $kq['ok'] || ! $sau || $sau['phien'] !== $v['phien'] + 1 || 'dang_lam' === $sau['trang_thai'] ) {
			$wpdb->query( 'ROLLBACK' );
			if ( 'post' === $v['doi_tuong_loai'] ) {
				clean_post_cache( (int) $v['doi_tuong_id'] );
			}
			return $kq['ok'] ? self::kq( false, 'Chưa ghi được trạng thái việc; mọi thay đổi đã được huỷ.' ) : $kq;
		}
		if ( false === $wpdb->query( 'COMMIT' ) ) {
			$wpdb->query( 'ROLLBACK' );
			return self::kq( false, 'Chưa xác nhận được quyết định. Kiểm tra lại Hộp việc trước khi thử tiếp.' );
		}
		return $kq;
	}

	/** Hoàn tác nhanh: chỉ nhận tác động có bản trước/sau và chưa bị sửa tiếp. */
	public static function co_the_hoan_tac( ?array $v, int $nguoi, int $phien ): bool {
		if ( ! $v || $v['phien'] !== $phien || $v['nguoi_quyet'] !== $nguoi || $nguoi < 1 ) {
			return false;
		}
		$truoc = $v['truoc']['trang_thai'] ?? '';
		$doi = ( 'de_sau' === $v['quyet'] && 'co_the_trung' !== $v['loai'] && Viec::DE_SAU === $v['trang_thai'] && Viec::CHO === $truoc )
			|| ( 'can_xem' === $v['loai'] && 'xong' === $v['quyet'] && Viec::XONG === $v['trang_thai'] && in_array( $truoc, array( Viec::CHO, Viec::DE_SAU ), true ) )
			|| ( in_array( $v['loai'], array( 'nhap_y_nghia', 'goi_y' ), true ) && 'bo' === $v['quyet'] && Viec::BO === $v['trang_thai'] && in_array( $truoc, array( Viec::CHO, Viec::DE_SAU ), true ) )
			|| ( 'nhap_y_nghia' === $v['loai'] && 'duyet' === $v['quyet'] && Viec::XONG === $v['trang_thai'] && user_can( $nguoi, 'cadao_duyet' ) && in_array( $truoc, array( Viec::CHO, Viec::DE_SAU ), true ) && self::phan_chua_doi( $v ) )
			|| ( 'goi_y' === $v['loai'] && 'dong_y' === $v['quyet'] && Viec::XONG === $v['trang_thai'] && in_array( $truoc, array( Viec::CHO, Viec::DE_SAU ), true ) && self::phan_loai_chua_doi( $v ) );
		$luc = empty( $v['quyet_luc'] ) ? false : strtotime( (string) $v['quyet_luc'] . ' UTC' );
		return $doi && false !== $luc && time() >= $luc && time() - $luc <= 10;
	}

	/** Ghi một sự kiện hoàn tác mới và trả thẻ về đúng trạng thái trước, trong một giao dịch có kiểm phiên. */
	public static function hoan_tac( int $id, int $phien, int $nguoi ): array {
		global $wpdb;
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return self::kq( false, 'Chưa mở được lượt hoàn tác. Thử lại sau.' );
		}
		$v = Viec::lay( $id );
		if ( ! self::co_the_hoan_tac( $v, $nguoi, $phien ) ) {
			$wpdb->query( 'ROLLBACK' );
			return self::kq( false, 'Không thể hoàn tác thẻ này: đã hết 10 giây, thẻ đã đổi hoặc quyết định cần sửa thủ công.' );
		}
		$truoc = $v['truoc']['trang_thai'];
		$phan_viec = 0;
		if ( 'nhap_y_nghia' === $v['loai'] && 'duyet' === $v['quyet'] ) {
			$kq = LuuPhan::khoi_phuc( (int) $v['truoc']['phan_viec'], array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
			if ( ! $kq['ok'] || ! $kq['doi'] ) {
				$wpdb->query( 'ROLLBACK' );
				clean_post_cache( (int) $v['doi_tuong_id'] );
				return self::kq( false, 'Chưa khôi phục được ý nghĩa; thẻ vẫn giữ quyết định cũ.' );
			}
			$phan_viec = $kq['viec'];
		} elseif ( 'goi_y' === $v['loai'] && 'dong_y' === $v['quyet'] ) {
			$tax = (string) $v['truoc']['taxonomy'];
			$ve = wp_set_object_terms( (int) $v['doi_tuong_id'], array_map( 'intval', $v['truoc']['term_ids_truoc'] ), $tax, false );
			if ( is_wp_error( $ve ) || self::term_ids( (int) $v['doi_tuong_id'], $tax ) !== $v['truoc']['term_ids_truoc'] ) {
				$wpdb->query( 'ROLLBACK' );
				clean_object_term_cache( (int) $v['doi_tuong_id'], get_post_type( (int) $v['doi_tuong_id'] ) );
				return self::kq( false, 'Chưa khôi phục được phân loại; thẻ vẫn giữ quyết định cũ.' );
			}
		}
		$truoc_ky = array( 'trang_thai' => $v['trang_thai'], 'quyet' => $v['quyet'], 'phien' => $phien );
		$sau_ky = array( 'trang_thai' => $truoc, 'phien' => $phien + 1 );
		if ( $phan_viec ) {
			$truoc_ky['phan_viec'] = (int) $v['truoc']['phan_viec'];
			$sau_ky['phan_viec'] = $phan_viec;
		} elseif ( 'goi_y' === $v['loai'] && 'dong_y' === $v['quyet'] ) {
			$truoc_ky['taxonomy'] = $sau_ky['taxonomy'] = $v['truoc']['taxonomy'];
			$truoc_ky['term_ids'] = $v['truoc']['term_ids_sau'];
			$sau_ky['term_ids'] = $v['truoc']['term_ids_truoc'];
		}
		$nhat_ky = Viec::tao( array(
			'loai'           => Viec::SUA,
			'trang_thai'     => Viec::XONG,
			'doi_tuong_loai' => 'viec',
			'doi_tuong_id'   => $id,
			'phan'           => 'trang_thai',
			'nguoi_tao'      => (string) $nguoi,
			'quyet'          => 'hoan_tac',
			'truoc'          => $truoc_ky,
			'sau'            => $sau_ky,
			'nguoi_quyet'    => $nguoi,
			'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
		) );
		if ( ! $nhat_ky || ! Viec::hoan_tac_quyet( $id, $phien, $v['trang_thai'], $nguoi, $truoc ) ) {
			$wpdb->query( 'ROLLBACK' );
			self::don_dem_sau_huy( $v );
			return self::kq( false, 'Thẻ đã đổi hoặc chưa ghi được nhật ký; chưa hoàn tác.' );
		}
		if ( false === $wpdb->query( 'COMMIT' ) ) {
			$wpdb->query( 'ROLLBACK' );
			self::don_dem_sau_huy( $v );
			return self::kq( false, 'Chưa xác nhận được hoàn tác. Tải lại Hộp việc để kiểm tra.' );
		}
		return self::kq( true, 'Đã hoàn tác quyết định thẻ.' );
	}

	/** Chạy trong giao dịch đã giữ đúng phiên thẻ. */
	private static function thuc_hien( array $v, string $quyet, int $nguoi, bool $duyet ): array {
		$id = $v['id'];
		if ( Viec::DE_SAU === $quyet || 'de_sau' === $quyet ) {
			if ( 'co_the_trung' === $v['loai'] ) {
				if ( ! ChongTrung::dat( (string) $v['noi_dung']['a'], (string) $v['noi_dung']['b'], 'de_sau' ) ) {
					return self::kq( false, 'Chưa lưu được quyết định về cặp câu. Thử lại sau.' );
				}
			}
			Viec::dong( $id, Viec::DE_SAU, 'de_sau', $nguoi, $v['trang_thai'] );
			return self::kq( true, 'Đã để sau.' );
		}
		switch ( $v['loai'] ) {
			case 'cau_moi':
				if ( 'bo' === $quyet ) {
					if ( ! UngVien::bo( (int) $v['doi_tuong_id'], $nguoi ) ) {
						return self::kq( false, 'Câu này không còn chờ xử lý.' );
					}
					Viec::dong( $id, Viec::BO, 'bo', $nguoi );
					return self::kq( true, 'Đã bỏ câu này (vẫn giữ trong kho).' );
				}
				if ( 'dang' === $quyet && ! $duyet ) {
					return self::kq( false, 'Cần quyền duyệt để đăng.' );
				}
				$kq = UngVien::dang( (int) $v['doi_tuong_id'], array( 'nguoi' => $nguoi ), 'dang' === $quyet ? 'publish' : 'draft' );
				if ( ! $kq['ok'] ) {
					return self::kq( false, implode( ' ', $kq['loi'] ) );
				}
				Viec::dong( $id, Viec::XONG, $quyet, $nguoi );
				return self::kq( true, 'dang' === $quyet ? 'Đã đăng.' : 'Đã tạo bản nháp; sửa tiếp trên trang bài.', 'sua' === $quyet ? (string) get_preview_post_link( $kq['bai'] ) : '' );
			case 'nhap_y_nghia':
				if ( 'bo' === $quyet ) {
					Viec::dong( $id, Viec::BO, 'bo', $nguoi, $v['trang_thai'] );
					return self::kq( true, 'Đã bỏ nháp.' );
				}
				if ( ! $duyet ) {
					return self::kq( false, 'Cần quyền duyệt.' );
				}
				$ai = ! empty( $v['noi_dung']['ai'] );
				$kq = LuuPhan::luu(
					(int) $v['doi_tuong_id'],
					'y_nghia',
					array( 'chu' => (string) ( $v['noi_dung']['chu'] ?? '' ), 'ai' => $ai, 'nguoi_lam' => $ai ? NguoiLam::AI_DA_DUYET : NguoiLam::NGUOI_VIET ),
					array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true )
				);
				if ( ! $kq['ok'] ) {
					return self::kq( false, implode( ' ', $kq['loi'] ) );
				}
				Viec::dong( $id, Viec::XONG, 'duyet', $nguoi, $v['trang_thai'], array( 'phan_viec' => $kq['viec'] ) );
				return self::kq( true, 'Đã duyệt; người đọc thấy ý nghĩa ngay.' );
			case 'can_xem':
				Viec::dong( $id, Viec::XONG, 'xong', $nguoi, $v['trang_thai'] );
				return self::kq( true, 'Đã đánh dấu xem xong.' );
			case 'goi_y':
				if ( 'bo' === $quyet ) {
					Viec::dong( $id, Viec::BO, 'bo', $nguoi, $v['trang_thai'] );
					return self::kq( true, 'Đã bỏ gợi ý; máy không gợi ý lại.' );
				}
				$tax = (string) ( $v['noi_dung']['loai'] ?? '' );
				$truoc = self::term_ids( (int) $v['doi_tuong_id'], $tax );
				if ( null === $truoc || ! \Phongdao\DichVu\GoiY\GoiY::dong_y( $v ) ) {
					return self::kq( false, 'Chưa gắn được (bài hoặc mục phân loại không còn).' );
				}
				$sau = self::term_ids( (int) $v['doi_tuong_id'], $tax );
				if ( null === $sau ) {
					return self::kq( false, 'Chưa xác nhận được phân loại vừa gắn.' );
				}
				Viec::dong( $id, Viec::XONG, 'dong_y', $nguoi, $v['trang_thai'], array( 'taxonomy' => $tax, 'term_ids_truoc' => $truoc, 'term_ids_sau' => $sau ) );
				return self::kq( true, 'Đã gắn ' . mb_strtolower( \Phongdao\DichVu\GoiY\GoiY::ten( $v['noi_dung'] ) ) . '.' );
			case 'nguoi_doc':
				if ( ! isset( self::nut_nguoi_doc( $v )[ $quyet ] ) ) {
					return self::kq( false, 'Không có lựa chọn này cho việc này.' );
				}
				$dt = (int) $v['doi_tuong_id'];
				if ( 'duyet' === $quyet ) {
					if ( ! $duyet ) {
						return self::kq( false, 'Cần quyền duyệt để hiện nội dung người đọc gửi.' );
					}
					$ok = 'comment' === $v['doi_tuong_loai'] ? (bool) wp_set_comment_status( $dt, 'approve' ) : ! is_wp_error( wp_update_post( array( 'ID' => $dt, 'post_status' => 'publish' ), true ) );
				} elseif ( 'bo' === $quyet ) {
					$ok = 'comment' === $v['doi_tuong_loai'] ? (bool) wp_set_comment_status( $dt, 'trash' ) : (bool) wp_trash_post( $dt );
				} else {
					$ok = ! is_wp_error( wp_update_post( array( 'ID' => $dt, 'post_status' => 'private' ), true ) ); // góp ý đã xử lý: giữ lại, không công khai
				}
				if ( ! $ok ) {
					return self::kq( false, 'Không làm được; nội dung này có thể đã bị xoá.' );
				}
				Viec::dong( $id, 'bo' === $quyet ? Viec::BO : Viec::XONG, $quyet, $nguoi );
				return self::kq( true, 'duyet' === $quyet ? 'Đã hiện trên trang.' : ( 'bo' === $quyet ? 'Đã bỏ.' : 'Đã đánh dấu xử lý xong.' ) );
			case 'co_the_trung':
				$a = (string) $v['noi_dung']['a'];
				$b = (string) $v['noi_dung']['b'];
				if ( 'khac' === $quyet ) {
					if ( ! ChongTrung::dat( $a, $b, 'khac' ) ) {
						return self::kq( false, 'Chưa lưu được quyết định về cặp câu. Thử lại sau.' );
					}
				} elseif ( ! ChongTrung::gop( $a, $b, 'cung' === $quyet ? 'trung' : 'di_ban' ) ) {
					return self::kq( false, 'Chưa gộp được (câu đã thành tác phẩm hoặc đã gộp).' );
				}
				Viec::dong( $id, Viec::XONG, $quyet, $nguoi );
				return self::kq( true, 'khac' === $quyet ? 'Đã ghi: là bài khác.' : 'Đã gộp.' );
		}
		return self::kq( false, 'Chưa xử lý được việc này.' );
	}

	/** Chỉ khôi phục nếu phần bài còn đúng bản của lần Duyệt và chưa có lượt sửa mới hơn. */
	private static function phan_chua_doi( array $v ): bool {
		global $wpdb;
		$viec_id = (int) ( $v['truoc']['phan_viec'] ?? 0 );
		$su = $viec_id ? Viec::lay( $viec_id ) : null;
		if ( ! $su || Viec::SUA !== $su['loai'] || 'post' !== $su['doi_tuong_loai'] || (int) $su['doi_tuong_id'] !== (int) $v['doi_tuong_id'] || 'y_nghia' !== $su['phan'] ) {
			return false;
		}
		$moi_nhat = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(id) FROM ' . Viec::bang() . ' WHERE loai = %s AND doi_tuong_loai = %s AND doi_tuong_id = %d AND phan = %s', Viec::SUA, 'post', (int) $v['doi_tuong_id'], 'y_nghia' ) );
		return $moi_nhat === $viec_id && PhanBai::lay( (int) $v['doi_tuong_id'], 'y_nghia' ) === $su['sau'];
	}

	/** Phân loại phải còn đúng tập sau lần Đồng ý; một sửa đổi khác sẽ chặn hoàn tác nhanh. */
	private static function phan_loai_chua_doi( array $v ): bool {
		$tax = (string) ( $v['truoc']['taxonomy'] ?? '' );
		$truoc = $v['truoc']['term_ids_truoc'] ?? null;
		$sau = $v['truoc']['term_ids_sau'] ?? null;
		$hien_tai = self::term_ids( (int) $v['doi_tuong_id'], $tax );
		return in_array( $tax, array( 'vung', 'chu_de' ), true ) && is_array( $truoc ) && is_array( $sau )
			&& null !== $hien_tai && $hien_tai === $sau;
	}

	/** @return int[]|null */
	private static function term_ids( int $bai, string $tax ): ?array {
		if ( ! in_array( $tax, array( 'vung', 'chu_de' ), true ) || ! get_post( $bai ) ) {
			return null;
		}
		$ids = wp_get_object_terms( $bai, $tax, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $ids ) ) {
			return null;
		}
		$ids = array_map( 'intval', $ids );
		sort( $ids, SORT_NUMERIC );
		return $ids;
	}

	/** Giao dịch đã huỷ nhưng WordPress có thể còn đệm phần bài hoặc phân loại. */
	private static function don_dem_sau_huy( array $v ): void {
		if ( 'nhap_y_nghia' === $v['loai'] && 'duyet' === $v['quyet'] ) {
			clean_post_cache( (int) $v['doi_tuong_id'] );
		} elseif ( 'goi_y' === $v['loai'] && 'dong_y' === $v['quyet'] ) {
			clean_object_term_cache( (int) $v['doi_tuong_id'], (string) get_post_type( (int) $v['doi_tuong_id'] ) );
		}
	}

	private static function kq( bool $ok, string $chu, string $chuyen = '' ): array {
		return array(
			'ok'        => $ok,
			'loi'       => $ok ? array() : array( $chu ),
			'thong_bao' => $ok ? $chu : '',
			'chuyen'    => $chuyen,
		);
	}
}
