<?php

namespace Phongdao\DichVu\XuLyLo;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\DichVu\UngVien\UngVien;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Xử lý lô (docs/30 mục 3; T-171): một thao tác cho nhiều mục, theo hợp đồng chốt phạm vi → xem trước → áp dụng.
 *
 * - Tạo lô chốt tập mục và phiên của từng mục lúc xem trước; người bỏ chọn ngoại lệ trước khi áp dụng.
 * - Áp dụng chạy từng lượt có giới hạn, mỗi mục qua đúng dịch vụ của thao tác đơn (QuyetViec…), kiểm quyền và phiên;
 *   mục đã đổi sau lúc xem trước thì bỏ qua, báo xung đột — không ghi đè. Kết quả từng mục: thành công / bỏ qua / xung
 *   đột / lỗi. Mục đã có kết quả không làm lại, nên chạy lại hay tiếp tục sau gián đoạn không áp dụng trùng.
 * - Một lô là một dòng `wp_cadao_viec` loại "lo" (trạng thái riêng, không vào Hộp việc): mã, người, phạm vi/bộ lọc, tập
 *   mục, thời điểm, kết quả.
 *
 * Máy không tự quyết nội dung: lô chỉ chạy khi người có quyền bấm; không xoá vĩnh viễn. Đăng theo lô (D-099) chỉ có cho
 * thẻ Câu mới, cần quyền duyệt, và mỗi câu qua đúng đường của nút Đăng đơn; câu có cờ của nguồn để sẵn bỏ chọn, câu đang
 * chờ quyết trùng thì bỏ qua.
 */
final class Lo {

	/** Số mục mỗi lượt áp dụng. */
	public const MOI_LUOT = 50;

	/** Một lô tối đa (thu hẹp bộ lọc nếu nhiều hơn). */
	public const TOI_DA = 2000;

	/** Thao tác => [nhãn, đối tượng, quyền cần, việc làm với từng mục]. */
	public const THAO_TAC = array(
		'viec_de_sau'  => array( 'Để sau', 'viec', 'cadao_soan', 'de_sau' ),
		'viec_bo'      => array( 'Bỏ đề xuất', 'viec', 'cadao_soan', 'bo' ),
		'goi_y_dong_y' => array( 'Đồng ý gợi ý', 'viec', 'cadao_duyet', 'dong_y' ),
		'cau_moi_dang' => array( 'Đăng (thẻ Câu mới từ nguồn)', 'viec', 'cadao_duyet', 'dang' ),
		'kho_dua_vao'  => array( 'Đưa vào Hộp việc', 'kho', 'cadao_soan', 'dua_vao' ),
		'pl_them'      => array( 'Thêm phân loại', 'post', 'cadao_soan', 'them' ),
		'pl_go'        => array( 'Gỡ phân loại', 'post', 'cadao_soan', 'go' ),
		'pl_thay'      => array( 'Thay thế phân loại', 'post', 'cadao_soan', 'thay' ),
		'bai_an'       => array( 'Ẩn bài', 'post', 'cadao_soan', 'private' ),
		'thung_rac'    => array( 'Đưa vào thùng rác', 'post', 'cadao_duyet', 'trash' ),
		'lay_ra'       => array( 'Lấy ra khỏi thùng rác', 'post', 'cadao_duyet', 'untrash' ),
		'sua_phan'     => array( 'Sửa một phần theo từng hàng', 'post', 'cadao_soan', 'sua_phan' ),
	);

	/**
	 * Phần chữ sửa được theo từng hàng (mỗi bài một giá trị riêng; không có ô ghi đè một nội dung lên mọi bài). Lời, dị bản,
	 * từ khó không sửa theo lô.
	 */
	public const PHAN_SUA = array(
		'y_nghia'     => 'Ý nghĩa',
		'ban_co_biet' => 'Bạn có biết',
		'nghia_den'   => 'Nghĩa đen (tục ngữ)',
		'nghia_bong'  => 'Nghĩa bóng (tục ngữ)',
		'dung_khi'    => 'Dùng khi (tục ngữ)',
		'cau_hoi'     => 'Câu hỏi (câu đố)',
		'dap_an'      => 'Đáp án (câu đố)',
		'goi_y'       => 'Gợi ý (câu đố)',
	);

	/** Thao tác phân loại cần chọn một mục có sẵn (bộ, mục). */
	public static function la_phan_loai( string $thao_tac ): bool {
		return str_starts_with( $thao_tac, 'pl_' );
	}

	public const KET_QUA = array(
		'thanh_cong' => 'Thành công',
		'bo_qua'     => 'Bỏ qua',
		'xung_dot'   => 'Xung đột',
		'loi'        => 'Lỗi',
	);

	/** Thẻ người đọc gửi không bỏ theo lô: "Bỏ" của chúng đưa nội dung người đọc vào thùng rác — quyết từng thẻ. */
	private const KHONG_BO_THEO_LO = array( 'nguoi_doc' );

	/**
	 * Tạo lô ở bước xem trước.
	 *
	 * @param int[]                $ids     mục đã chọn (hoặc mọi mục khớp bộ lọc, đã tính sẵn)
	 * @param array<string,string> $loc     bộ lọc lúc chọn (để ghi lại phạm vi)
	 * @return array{ok:bool,lo:int,loi:string}
	 */
	public static function tao( string $thao_tac, array $ids, string $pham_vi, array $loc, int $nguoi, array $tham_so = array() ): array {
		if ( ! isset( self::THAO_TAC[ $thao_tac ] ) ) {
			return self::sai( 'Không có thao tác này.' );
		}
		[ $ten, $doi_tuong, $quyen ] = self::THAO_TAC[ $thao_tac ];
		if ( ! user_can( $nguoi, $quyen ) ) {
			return self::sai( 'cadao_duyet' === $quyen ? "Cần quyền duyệt để {$ten} theo lô." : 'Bạn không có quyền làm việc này.' );
		}
		$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ), static fn( int $i ): bool => $i > 0 ) ) );
		if ( ! $ids ) {
			return self::sai( 'Chưa chọn mục nào.' );
		}
		if ( count( $ids ) > self::TOI_DA ) {
			return self::sai( 'Một lô tối đa ' . number_format( self::TOI_DA, 0, ',', '.' ) . ' mục. Thu hẹp bộ lọc rồi thử lại.' );
		}
		if ( 'post' === $doi_tuong ) {
			return self::tao_bai( $thao_tac, $ids, $pham_vi, $loc, $nguoi, $tham_so );
		}
		if ( 'kho' === $doi_tuong ) {
			return self::tao_kho( $thao_tac, $ids, $pham_vi, $loc, $nguoi );
		}
		$muc = self::chot_viec( $ids );
		if ( ! $muc ) {
			return self::sai( 'Các thẻ đã chọn không còn ở Chờ hay Để sau.' );
		}
		$loai = array_values( array_unique( array_column( $muc, 2 ) ) );
		if ( count( $loai ) > 1 ) {
			return self::sai( 'Chọn các thẻ cùng một loại (lọc theo Loại việc trước).' );
		}
		if ( 'goi_y_dong_y' === $thao_tac && 'goi_y' !== $loai[0] ) {
			return self::sai( 'Đồng ý theo lô chỉ dùng cho thẻ "Gợi ý chủ đề, vùng".' );
		}
		if ( 'cau_moi_dang' === $thao_tac && UngVien::VIEC !== $loai[0] ) {
			return self::sai( 'Đăng theo lô chỉ dùng cho thẻ "Câu mới từ nguồn" (lọc Loại việc trước).' );
		}
		// Đăng: câu có cờ của nguồn hay đang chờ quyết trùng để sẵn bỏ chọn — người tự đánh dấu lại nếu muốn đăng
		$bo = 'cau_moi_dang' === $thao_tac ? self::ngoai_le_cau_moi( array_column( $muc, 0 ) ) : array();
		$id = Viec::tao(
			array(
				'loai'           => Viec::LO,
				'trang_thai'     => Viec::LO_XEM,
				'doi_tuong_loai' => Viec::LO,
				'doi_tuong_id'   => 0,
				'phan'           => $thao_tac,
				'noi_dung'       => array(
					'thao_tac'  => $thao_tac,
					'loai_viec' => $loai[0],
					'pham_vi'   => 'loc' === $pham_vi ? 'loc' : 'chon',
					'loc'       => array_map( 'strval', $loc ),
					'muc'       => array_map( static fn( array $m ): array => array( $m[0], $m[1] ), $muc ),
					'bo'        => $bo,
				),
				'sau'            => array( 'ket_qua' => array() ),
				'nguoi_tao'      => (string) $nguoi,
			)
		);
		return $id ? array( 'ok' => true, 'lo' => $id, 'loi' => '' ) : self::sai( 'Chưa tạo được lô. Thử lại sau.' );
	}

	/**
	 * Câu kho đang chờ quyết trùng trong một tập mã câu (một truy vấn mỗi 500 câu).
	 *
	 * @param int[] $kho_ids
	 * @return array<int,bool>
	 */
	public static function kho_cho_trung( array $kho_ids ): array {
		global $wpdb;
		$ra = array();
		foreach ( array_chunk( array_values( array_unique( array_map( 'intval', $kho_ids ) ) ), 500 ) as $phan ) {
			$khoa = array_map( static fn( int $i ): string => 'kho:' . $i, $phan );
			foreach ( $wpdb->get_col( $wpdb->prepare( 'SELECT a FROM ' . ChongTrung::bang( 'trung' ) . " WHERE trang_thai IN ('cho','de_sau') AND a IN (" . implode( ',', array_fill( 0, count( $khoa ), '%s' ) ) . ')', $khoa ) ) ?: array() as $a ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ra[ (int) substr( (string) $a, 4 ) ] = true;
			}
		}
		return $ra;
	}

	/** Thẻ Câu mới có câu kho mang cờ của nguồn hay đang chờ quyết trùng (để sẵn bỏ chọn khi đăng theo lô). @return int[] */
	private static function ngoai_le_cau_moi( array $the_ids ): array {
		global $wpdb;
		$kho = array();
		$co  = array();
		foreach ( array_chunk( array_map( 'intval', $the_ids ), 500 ) as $phan ) {
			$dau = implode( ',', array_fill( 0, count( $phan ), '%d' ) );
			foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT v.id, k.id AS kho, k.du_lieu FROM ' . Viec::bang() . ' v JOIN ' . Kho::bang() . " k ON k.id = v.doi_tuong_id WHERE v.doi_tuong_loai = 'kho' AND v.id IN ($dau)", $phan ), ARRAY_A ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$kho[ (int) $h['id'] ] = (int) $h['kho'];
				if ( ! empty( ( json_decode( (string) $h['du_lieu'], true ) ?: array() )['co'] ) ) {
					$co[ (int) $h['id'] ] = true;
				}
			}
		}
		$trung = self::kho_cho_trung( array_values( $kho ) );
		$bo    = array();
		foreach ( $kho as $the => $k ) {
			if ( isset( $co[ $the ] ) || isset( $trung[ $k ] ) ) {
				$bo[] = $the;
			}
		}
		sort( $bo, SORT_NUMERIC );
		return $bo;
	}

	/**
	 * Lô trên câu kho ("Đưa vào Hộp việc" nhiều câu): chỉ chốt câu còn Chưa dùng, có lời, chưa gắn bài và chưa có thẻ Câu
	 * mới đang mở; bản lúc xem trước là trạng thái câu. Không đăng, không gộp, không sửa lời.
	 */
	private static function tao_kho( string $thao_tac, array $ids, string $pham_vi, array $loc, int $nguoi ): array {
		global $wpdb;
		$co = array();
		foreach ( array_chunk( $ids, 500 ) as $phan ) {
			$dau = implode( ',', array_fill( 0, count( $phan ), '%d' ) );
			$sql = 'SELECT k.id, k.trang_thai FROM ' . Kho::bang() . " k WHERE k.id IN ($dau) AND k.trang_thai = 'moi' AND k.post_id = 0 AND k.loi <> ''"
				. ' AND NOT EXISTS (SELECT 1 FROM ' . Viec::bang() . " v WHERE v.doi_tuong_loai = 'kho' AND v.doi_tuong_id = k.id AND v.loai = %s AND v.trang_thai IN ('cho','de_sau'))";
			foreach ( $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $phan, array( UngVien::VIEC ) ) ), ARRAY_A ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$co[ (int) $h['id'] ] = (string) $h['trang_thai'];
			}
		}
		$muc = array();
		foreach ( $ids as $i ) {
			if ( isset( $co[ $i ] ) ) {
				$muc[] = array( $i, $co[ $i ] );
			}
		}
		if ( ! $muc ) {
			return self::sai( 'Không còn câu nào đưa vào được: chỉ câu Chưa dùng, có lời và chưa có thẻ Câu mới.' );
		}
		$id = Viec::tao(
			array(
				'loai'           => Viec::LO,
				'trang_thai'     => Viec::LO_XEM,
				'doi_tuong_loai' => Viec::LO,
				'doi_tuong_id'   => 0,
				'phan'           => $thao_tac,
				'noi_dung'       => array(
					'thao_tac'  => $thao_tac,
					'doi_tuong' => 'kho',
					'pham_vi'   => 'loc' === $pham_vi ? 'loc' : 'chon',
					'loc'       => array_map( 'strval', $loc ),
					'muc'       => $muc,
					'bo'        => array(),
				),
				'sau'            => array( 'ket_qua' => array() ),
				'nguoi_tao'      => (string) $nguoi,
			)
		);
		return $id ? array( 'ok' => true, 'lo' => $id, 'loi' => '' ) : self::sai( 'Chưa tạo được lô. Thử lại sau.' );
	}

	/** Một câu kho: còn đúng trạng thái lúc xem trước thì đưa vào Hộp việc qua đúng đường của nút đơn (UngVien). @return array{0:string,1:string} */
	private static function mot_kho( int $id, string $ban, int $nguoi ): array {
		$m = Kho::lay( $id );
		if ( ! $m ) {
			return array( 'loi', 'Không còn câu này trong kho.' );
		}
		if ( $ban !== (string) $m->trang_thai || (int) $m->post_id ) {
			return array( 'xung_dot', 'Câu đã đổi trạng thái sau lúc xem trước.' );
		}
		$kq = UngVien::dua_vao_hop_viec( $id, $nguoi );
		if ( $kq['moi'] ) {
			return array( 'thanh_cong', 'Đã tạo thẻ Câu mới #' . $kq['viec'] . '.' );
		}
		return array( $kq['ok'] || ! str_contains( (string) $kq['thong_bao'], 'Thử lại' ) ? 'bo_qua' : 'loi', (string) $kq['thong_bao'] );
	}

	/**
	 * Lô trên tác phẩm: kiểm mục phân loại (bộ sửa tay được, mục có sẵn — không tạo mục mới), chốt bản của từng bài lúc xem
	 * trước (tập mục đang gắn với thao tác phân loại; trạng thái bài với ẩn / thùng rác).
	 */
	private static function tao_bai( string $thao_tac, array $ids, string $pham_vi, array $loc, int $nguoi, array $tham_so ): array {
		global $wpdb;
		$ts = array();
		if ( self::la_phan_loai( $thao_tac ) ) {
			$tax = (string) ( $tham_so['tax'] ?? '' );
			$t   = get_term( (int) ( $tham_so['term'] ?? 0 ), $tax );
			if ( ! array_key_exists( $tax, LuuPhanLoai::SUA_DUOC ) || ! $t instanceof \WP_Term || LuuPhanLoai::may_giu( $tax, $t ) ) {
				return self::sai( 'Chọn một mục phân loại có sẵn (chủ đề, vùng, mô-típ, lối hát, trò chơi).' );
			}
			$ts = array( 'tax' => $tax, 'term' => (int) $t->term_id, 'ten' => $t->name );
		} elseif ( 'sua_phan' === $thao_tac ) {
			$phan = (string) ( $tham_so['phan'] ?? '' );
			if ( ! isset( self::PHAN_SUA[ $phan ] ) ) {
				return self::sai( 'Chọn phần muốn sửa theo từng hàng.' );
			}
			$ts = array( 'phan' => $phan, 'ten' => self::PHAN_SUA[ $phan ], 'duyet' => false, 'ai' => false );
		}
		$co = array();
		foreach ( array_chunk( $ids, 500 ) as $phan ) {
			$dau = implode( ',', array_fill( 0, count( $phan ), '%d' ) );
			foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_status FROM {$wpdb->posts} WHERE ID IN ($dau) AND post_type = %s", array_merge( $phan, array( KhaiBao::TAC_PHAM ) ) ), ARRAY_A ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$co[ (int) $h['ID'] ] = (string) $h['post_status'];
			}
		}
		$muc = array();
		foreach ( $ids as $i ) {
			if ( isset( $co[ $i ] ) ) {
				$ban = isset( $ts['phan'] ) ? (string) wp_json_encode( PhanBai::lay( $i, $ts['phan'] ) ) : ( isset( $ts['tax'] ) ? (string) wp_json_encode( LuuPhanLoai::dang_gan( $i, $ts['tax'] ) ) : $co[ $i ] );
				$muc[] = array( $i, $ban );
			}
		}
		if ( ! $muc ) {
			return self::sai( 'Không còn tác phẩm nào trong các mục đã chọn.' );
		}
		$id = Viec::tao(
			array(
				'loai'           => Viec::LO,
				'trang_thai'     => Viec::LO_XEM,
				'doi_tuong_loai' => Viec::LO,
				'doi_tuong_id'   => 0,
				'phan'           => $thao_tac,
				'noi_dung'       => array(
					'thao_tac'  => $thao_tac,
					'doi_tuong' => 'post',
					'tham_so'   => $ts,
					'pham_vi'   => 'loc' === $pham_vi ? 'loc' : 'chon',
					'loc'       => array_map( 'strval', $loc ),
					'muc'       => $muc,
					'bo'        => array(),
					'gia_tri'   => array(),
				),
				'sau'            => array( 'ket_qua' => array() ),
				'nguoi_tao'      => (string) $nguoi,
			)
		);
		return $id ? array( 'ok' => true, 'lo' => $id, 'loi' => '' ) : self::sai( 'Chưa tạo được lô. Thử lại sau.' );
	}

	/**
	 * Lô "sửa một phần theo từng hàng": lưu giá trị người gõ cho các bài trên một trang xem trước (và bỏ chọn ngoại lệ,
	 * tuỳ chọn duyệt / có hỗ trợ AI do người đánh). Chưa ghi gì vào bài.
	 *
	 * @param array<int|string,string> $gia_tri  mã bài => chữ
	 * @param int[]                    $tren_trang
	 * @param int[]                    $giu
	 * @param array{duyet?:bool,ai?:bool} $tuy_chon
	 * @return array{ok:bool,loi:string,bo:int}
	 */
	public static function luu_gia_tri( int $lo_id, array $gia_tri, array $tren_trang, array $giu, array $tuy_chon, int $nguoi ): array {
		[ $lo, $ly_do ] = self::lay_cua( $lo_id, $nguoi, array( Viec::LO_XEM ) );
		if ( ! $lo || 'sua_phan' !== ( $lo['noi_dung']['thao_tac'] ?? '' ) ) {
			return array( 'ok' => false, 'loi' => $lo ? 'Lô này không sửa theo từng hàng.' : $ly_do, 'bo' => 0 );
		}
		$co  = array_flip( array_map( static fn( array $m ): int => (int) $m[0], $lo['noi_dung']['muc'] ) );
		$nd  = $lo['noi_dung'];
		$gt  = (array) ( $nd['gia_tri'] ?? array() );
		foreach ( $gia_tri as $id => $chu ) {
			if ( isset( $co[ (int) $id ] ) ) {
				$gt[ (string) (int) $id ] = mb_substr( trim( str_replace( "\r", '', (string) $chu ) ), 0, 5000 );
			}
		}
		$tren = array_filter( array_map( 'intval', $tren_trang ), static fn( int $i ): bool => isset( $co[ $i ] ) );
		$bo   = array_values( array_unique( array_merge( array_diff( array_map( 'intval', (array) $nd['bo'] ), $tren ), array_diff( $tren, array_map( 'intval', $giu ) ) ) ) );
		sort( $bo, SORT_NUMERIC );
		$nd['gia_tri']          = $gt;
		$nd['bo']               = $bo;
		$nd['tham_so']['duyet'] = ! empty( $tuy_chon['duyet'] ) && user_can( $nguoi, 'cadao_duyet' );
		$nd['tham_so']['ai']    = ! empty( $tuy_chon['ai'] );
		if ( ! Viec::ghi_lo( $lo_id, (int) $lo['phien'], Viec::LO_XEM, (array) $lo['sau'], $nd ) ) {
			return array( 'ok' => false, 'loi' => 'Lô vừa đổi ở phiên khác. Tải lại trang.', 'bo' => 0 );
		}
		return array( 'ok' => true, 'loi' => '', 'bo' => count( $bo ) );
	}

	/** Một bài của lô sửa theo từng hàng: qua đúng đường lưu của nút Sửa (Ý nghĩa chưa duyệt thành nháp trong Hộp việc). @return array{0:string,1:string} */
	private static function mot_sua_phan( int $id, string $ban, array $ts, string $moi ): array {
		$phan = (string) $ts['phan'];
		$hien = PhanBai::lay( $id, $phan );
		if ( (string) wp_json_encode( $hien ) !== $ban ) {
			return array( 'xung_dot', 'Phần này của bài đã đổi sau lúc xem trước.' );
		}
		if ( '' === $moi ) {
			return array( 'bo_qua', 'Chưa nhập giá trị cho bài này.' );
		}
		if ( $moi === trim( (string) ( $hien['chu'] ?? '' ) ) && ( ! empty( $ts['ai'] ) ) === ! empty( $hien['ai'] ) ) {
			return array( 'bo_qua', 'Không đổi so với bản đang có.' );
		}
		$yc = new \WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $id . '/phan/' . $phan );
		// Thân yêu cầu trước: set_body_params thay cả nhóm tham số POST, đặt sau sẽ xoá mất id / phan
		$yc->set_body_params( array( 'chu' => $moi, 'ai' => empty( $ts['ai'] ) ? '' : '1', 'duyet' => empty( $ts['duyet'] ) ? '' : '1' ) );
		$yc->set_url_params( array( 'id' => $id, 'phan' => $phan ) );
		$kq = \Phongdao\GiaoTiep\BienTap\Module::luu_phan( $yc );
		if ( $kq['ok'] ) {
			return array( 'thanh_cong', (string) ( $kq['thong_bao'] ?? 'Đã lưu.' ) );
		}
		$loi = implode( ' ', (array) ( $kq['loi'] ?? array() ) );
		return array( str_contains( $loi, 'thể loại' ) || str_contains( $loi, 'Không có phần' ) ? 'bo_qua' : 'loi', $loi );
	}

	/** Một tác phẩm: kiểm quyền và bản lúc xem trước, rồi làm qua đúng đường ghi (LuuPhanLoai; trạng thái của WordPress, có dấu vết). @return array{0:string,1:string} */
	private static function mot_bai( string $thao_tac, int $id, string $ban, int $nguoi, array $ts ): array {
		$bai = get_post( $id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type ) {
			return array( 'loi', 'Không còn bài này.' );
		}
		if ( ! user_can( $nguoi, 'edit_post', $id ) ) {
			return array( 'loi', 'Không có quyền sửa bài này.' );
		}
		$viec = self::THAO_TAC[ $thao_tac ][3];
		if ( 'sua_phan' === $viec ) {
			return 'trash' === $bai->post_status ? array( 'bo_qua', 'Bài đang ở thùng rác.' ) : self::mot_sua_phan( $id, $ban, $ts, (string) ( $ts['gia_tri'][ (string) $id ] ?? '' ) );
		}
		if ( self::la_phan_loai( $thao_tac ) ) {
			if ( 'trash' === $bai->post_status ) {
				return array( 'bo_qua', 'Bài đang ở thùng rác.' );
			}
			$hien = LuuPhanLoai::dang_gan( $id, (string) $ts['tax'] );
			if ( null === $hien ) {
				return array( 'loi', 'Chưa đọc được phân loại của bài.' );
			}
			if ( (string) wp_json_encode( $hien ) !== $ban ) {
				return array( 'xung_dot', 'Phân loại của bài đã đổi sau lúc xem trước.' );
			}
			$t   = (int) $ts['term'];
			$moi = 'thay' === $viec ? array( $t ) : ( 'go' === $viec ? array_values( array_diff( $hien, array( $t ) ) ) : array_values( array_unique( array_merge( $hien, array( $t ) ) ) ) );
			sort( $moi, SORT_NUMERIC );
			if ( $moi === $hien ) {
				return array( 'bo_qua', 'Không có gì thay đổi.' );
			}
			$kq = LuuPhanLoai::luu( $id, (string) $ts['tax'], $moi, $hien, $nguoi );
			if ( $kq['ok'] ) {
				return array( 'thanh_cong', 'Đã ' . array( 'them' => 'thêm', 'go' => 'gỡ', 'thay' => 'thay bằng' )[ $viec ] . ' ' . $ts['ten'] . '.' );
			}
			$loi = implode( ' ', $kq['loi'] );
			return array( str_starts_with( $loi, 'Không sửa' ) ? 'bo_qua' : 'loi', $loi );
		}
		if ( $bai->post_status !== $ban ) {
			return array( 'xung_dot', 'Trạng thái bài đã đổi sau lúc xem trước.' );
		}
		if ( 'private' === $viec ) {
			if ( 'publish' !== $bai->post_status ) {
				return array( 'bo_qua', 'Bài không ở trạng thái Đã đăng.' );
			}
			$ok = ! is_wp_error( wp_update_post( array( 'ID' => $id, 'post_status' => 'private' ), true ) ) && 'private' === get_post_status( $id );
			return $ok ? array( 'thanh_cong', 'Đã ẩn khỏi người đọc.' ) : array( 'loi', 'Chưa ẩn được bài.' );
		}
		if ( 'trash' === $viec ) {
			if ( 'trash' === $bai->post_status ) {
				return array( 'bo_qua', 'Bài đã ở thùng rác.' );
			}
			if ( ! user_can( $nguoi, 'delete_post', $id ) ) {
				return array( 'loi', 'Không có quyền đưa bài này vào thùng rác.' );
			}
			return wp_trash_post( $id ) && 'trash' === get_post_status( $id ) ? array( 'thanh_cong', 'Đã đưa vào thùng rác (lấy ra được).' ) : array( 'loi', 'Chưa đưa được vào thùng rác.' );
		}
		if ( 'trash' !== $bai->post_status ) {
			return array( 'bo_qua', 'Bài không ở thùng rác.' );
		}
		return wp_untrash_post( $id ) && 'trash' !== get_post_status( $id ) ? array( 'thanh_cong', 'Đã lấy ra khỏi thùng rác (thành bản nháp).' ) : array( 'loi', 'Chưa lấy ra được.' );
	}

	/** Thẻ còn mở trong tập đã chọn, kèm phiên và loại lúc này. @return list<array{0:int,1:int,2:string}> */
	private static function chot_viec( array $ids ): array {
		global $wpdb;
		$ra = array();
		foreach ( array_chunk( $ids, 500 ) as $phan ) {
			$dau = implode( ',', array_fill( 0, count( $phan ), '%d' ) );
			foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT id, phien, loai FROM ' . Viec::bang() . " WHERE id IN ($dau) AND trang_thai IN ('cho','de_sau') AND loai NOT IN ('sua','lo')", $phan ), ARRAY_A ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ra[ (int) $h['id'] ] = array( (int) $h['id'], (int) $h['phien'], (string) $h['loai'] );
			}
		}
		// Giữ thứ tự người đã thấy
		return array_values( array_filter( array_map( static fn( int $i ): ?array => $ra[ $i ] ?? null, $ids ) ) );
	}

	/** Lô của người này, còn ở bước xem trước / đang chạy; null kèm lý do khi không dùng được. */
	private static function lay_cua( int $lo_id, int $nguoi, array $trang_thai ): array {
		$lo = Viec::lay( $lo_id );
		if ( ! $lo || Viec::LO !== $lo['loai'] ) {
			return array( null, 'Không tìm thấy lô này.' );
		}
		if ( (string) $nguoi !== $lo['nguoi_tao'] ) {
			return array( null, 'Lô này do người khác tạo.' );
		}
		if ( ! in_array( $lo['trang_thai'], $trang_thai, true ) ) {
			return array( null, Viec::LO_XONG === $lo['trang_thai'] ? 'Lô đã chạy xong.' : ( Viec::LO_HUY === $lo['trang_thai'] ? 'Lô đã huỷ.' : 'Lô không còn ở bước này.' ) );
		}
		return array( $lo, '' );
	}

	/**
	 * Bỏ chọn ngoại lệ trên một trang xem trước: mục trên trang không đánh dấu thì bỏ khỏi lô, đánh dấu lại thì giữ.
	 *
	 * @param int[] $tren_trang mục hiện trên trang
	 * @param int[] $giu        mục còn đánh dấu
	 * @return array{ok:bool,loi:string,bo:int}
	 */
	public static function bo_chon( int $lo_id, array $tren_trang, array $giu, int $nguoi ): array {
		[ $lo, $ly_do ] = self::lay_cua( $lo_id, $nguoi, array( Viec::LO_XEM ) );
		if ( ! $lo ) {
			return array( 'ok' => false, 'loi' => $ly_do, 'bo' => 0 );
		}
		$co   = array_flip( array_map( static fn( array $m ): int => (int) $m[0], $lo['noi_dung']['muc'] ) );
		$tren = array_filter( array_map( 'intval', $tren_trang ), static fn( int $i ): bool => isset( $co[ $i ] ) );
		$giu  = array_map( 'intval', $giu );
		$bo   = array_values( array_unique( array_merge( array_diff( array_map( 'intval', (array) $lo['noi_dung']['bo'] ), $tren ), array_diff( $tren, $giu ) ) ) );
		sort( $bo, SORT_NUMERIC );
		$nd       = $lo['noi_dung'];
		$nd['bo'] = $bo;
		if ( ! Viec::ghi_lo( $lo_id, (int) $lo['phien'], Viec::LO_XEM, (array) $lo['sau'], $nd ) ) {
			return array( 'ok' => false, 'loi' => 'Lô vừa đổi ở phiên khác. Tải lại trang.', 'bo' => 0 );
		}
		return array( 'ok' => true, 'loi' => '', 'bo' => count( $bo ) );
	}

	/**
	 * Chạy tiếp một lượt (tối đa MOI_LUOT mục chưa có kết quả).
	 *
	 * @return array{ok:bool,loi:string,con:int,dem:array<string,int>}
	 */
	public static function ap_dung( int $lo_id, int $nguoi, bool $duyet ): array {
		[ $lo, $ly_do ] = self::lay_cua( $lo_id, $nguoi, array( Viec::LO_XEM, Viec::LO_DANG ) );
		if ( ! $lo ) {
			return array( 'ok' => false, 'loi' => $ly_do, 'con' => 0, 'dem' => array() );
		}
		$thao_tac = (string) $lo['noi_dung']['thao_tac'];
		if ( ! isset( self::THAO_TAC[ $thao_tac ] ) || ! user_can( $nguoi, self::THAO_TAC[ $thao_tac ][2] ) ) {
			return array( 'ok' => false, 'loi' => 'Bạn không có quyền chạy lô này.', 'con' => 0, 'dem' => array() );
		}
		if ( ! Viec::nhan_lo( $lo_id, (int) $lo['phien'] ) ) {
			return array( 'ok' => false, 'loi' => 'Lô đang chạy ở phiên khác. Tải lại trang để xem tiến độ.', 'con' => 0, 'dem' => array() );
		}
		$phien = (int) $lo['phien'] + 1;
		$kq    = (array) ( $lo['sau']['ket_qua'] ?? array() );
		$bo    = array_flip( array_map( 'intval', (array) $lo['noi_dung']['bo'] ) );
		$lam   = 0;
		foreach ( $lo['noi_dung']['muc'] as [ $id, $p ] ) {
			$id = (int) $id;
			if ( isset( $kq[ (string) $id ] ) ) {
				continue;
			}
			if ( $lam >= self::MOI_LUOT ) {
				break;
			}
			$dt = (string) ( $lo['noi_dung']['doi_tuong'] ?? 'viec' );
			$kq[ (string) $id ] = isset( $bo[ $id ] ) ? array( 'bo_qua', 'Người bỏ khỏi lô lúc xem trước.' )
				: ( 'post' === $dt ? self::mot_bai( $thao_tac, $id, (string) $p, $nguoi, (array) ( $lo['noi_dung']['tham_so'] ?? array() ) + array( 'gia_tri' => (array) ( $lo['noi_dung']['gia_tri'] ?? array() ) ) )
					: ( 'kho' === $dt ? self::mot_kho( $id, (string) $p, $nguoi ) : self::mot( $thao_tac, $id, (int) $p, $nguoi, $duyet ) ) );
			++$lam;
		}
		$con = count( $lo['noi_dung']['muc'] ) - count( $kq );
		if ( ! Viec::ghi_lo( $lo_id, $phien, $con > 0 ? Viec::LO_DANG : Viec::LO_XONG, array( 'ket_qua' => $kq ) ) ) {
			return array( 'ok' => false, 'loi' => 'Chưa ghi được kết quả lượt này; các mục đã làm vẫn có nhật ký riêng. Bấm Tiếp tục để chạy lại an toàn.', 'con' => $con, 'dem' => self::dem( $kq ) );
		}
		return array( 'ok' => true, 'loi' => '', 'con' => $con, 'dem' => self::dem( $kq ) );
	}

	/** Một mục: kiểm lại phiên lúc xem trước, rồi quyết bằng đúng nghiệp vụ của thẻ. @return array{0:string,1:string} */
	private static function mot( string $thao_tac, int $id, int $phien, int $nguoi, bool $duyet ): array {
		$quyet = self::THAO_TAC[ $thao_tac ][3];
		$v     = Viec::lay( $id );
		if ( ! $v ) {
			return array( 'loi', 'Không còn thẻ này.' );
		}
		if ( ! in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ) {
			return array( 'bo_qua', 'Thẻ đã được quyết.' );
		}
		if ( $v['phien'] !== $phien ) {
			return array( 'xung_dot', 'Thẻ đã đổi sau lúc xem trước.' );
		}
		if ( 'bo' === $quyet && in_array( $v['loai'], self::KHONG_BO_THEO_LO, true ) ) {
			return array( 'bo_qua', 'Nội dung người đọc gửi: quyết từng thẻ.' );
		}
		if ( ! isset( QuyetViec::LOAI[ $v['loai'] ][1][ $quyet ] ) ) {
			return array( 'bo_qua', 'Loại thẻ này không có lựa chọn "' . self::THAO_TAC[ $thao_tac ][0] . '".' );
		}
		if ( 'de_sau' === $quyet && Viec::DE_SAU === $v['trang_thai'] ) {
			return array( 'bo_qua', 'Thẻ đã ở Để sau.' );
		}
		$dang = 'dang' === $quyet && UngVien::VIEC === $v['loai'];
		if ( $dang && ChongTrung::dang_cho( (int) $v['doi_tuong_id'] ) ) {
			return array( 'bo_qua', 'Câu đang chờ quyết trùng: quyết thẻ "Có thể trùng" trước.' );
		}
		$kq = QuyetViec::quyet( $id, $quyet, $nguoi, $duyet, $phien );
		if ( $kq['ok'] ) {
			$bai = $dang ? (int) ( Kho::lay( (int) $v['doi_tuong_id'] )?->post_id ?? 0 ) : 0;
			return array( 'thanh_cong', (string) $kq['thong_bao'] . ( $bai ? ' Bài #' . $bai . '.' : '' ) );
		}
		$loi = implode( ' ', $kq['loi'] );
		return array( str_contains( $loi, 'phiên khác' ) ? 'xung_dot' : ( str_contains( $loi, 'đã được quyết' ) || str_contains( $loi, 'không còn chờ' ) ? 'bo_qua' : 'loi' ), $loi );
	}

	/** Dừng lô: mục chưa làm giữ nguyên; mục đã làm vẫn có kết quả và nhật ký. */
	public static function huy( int $lo_id, int $nguoi ): array {
		[ $lo, $ly_do ] = self::lay_cua( $lo_id, $nguoi, array( Viec::LO_XEM, Viec::LO_DANG ) );
		if ( ! $lo ) {
			return array( 'ok' => false, 'loi' => $ly_do );
		}
		return Viec::ghi_lo( $lo_id, (int) $lo['phien'], Viec::LO_HUY, (array) $lo['sau'] )
			? array( 'ok' => true, 'loi' => '' )
			: array( 'ok' => false, 'loi' => 'Lô vừa đổi ở phiên khác. Tải lại trang.' );
	}

	/** @return array<string,int> số mục theo kết quả */
	public static function dem( array $kq ): array {
		$dem = array_fill_keys( array_keys( self::KET_QUA ), 0 );
		foreach ( $kq as $k ) {
			$ma = (string) ( $k[0] ?? '' );
			if ( isset( $dem[ $ma ] ) ) {
				++$dem[ $ma ];
			}
		}
		return $dem;
	}

	private static function sai( string $loi ): array {
		return array( 'ok' => false, 'lo' => 0, 'loi' => $loi );
	}
}
