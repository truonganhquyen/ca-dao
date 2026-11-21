<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\Phan;
use Phongdao\MienDuLieu\TheTho;

defined( 'ABSPATH' ) || exit;

/**
 * Lấy dữ liệu một tác phẩm từ WordPress (các phần, phân loại, tên hồ sơ nguồn, tuyển tập chứa bài) rồi giao
 * cho TrangTacPham dựng. Không đọc phần nguon_may (nội bộ).
 */
final class DocBai {

	/** @return array<string,mixed>|null dữ liệu cho GiaoTiep\Khoi\TacPham; null khi không phải tác phẩm có lời */
	public static function tac_pham( \WP_Post $bai ): ?array {
		if ( KhaiBao::TAC_PHAM !== $bai->post_type ) {
			return null;
		}
		$phan = self::phan( $bai );
		$ho_so = array();
		foreach ( array_merge( array( $phan['loi']['doi_chieu']['ho_so'] ?? 0 ), array_map( static fn( $c ) => $c['nguon']['ho_so'] ?? 0, array_merge( $phan['chu_thich']['muc'] ?? array(), $phan['chuyen_quanh']['muc'] ?? array() ) ) ) as $id ) {
			$hs = $id ? get_post( (int) $id ) : null;
			if ( $hs && KhaiBao::HO_SO_NGUON === $hs->post_type && 'publish' === $hs->post_status ) {
				$ho_so[ $hs->ID ] = $hs->post_title;
			}
		}
		$hinh_thuc = self::term( $bai, 'hinh_thuc' );
		$tp = TrangTacPham::tu_phan(
			$phan,
			array(
				'id'        => $bai->ID,
				'tieu_de'   => $bai->post_title,
				'url'       => (string) get_permalink( $bai ),
				'the_loai'  => self::the_loai( $bai ),
				'the_tho'   => self::the_tho( $hinh_thuc ),
				'hinh_thuc' => implode( ' · ', array_column( $hinh_thuc, 0 ) ),
				'chu_de'    => self::term( $bai, 'chu_de' ),
				'vung'      => self::term( $bai, 'vung' ),
				'tuyen_tap' => self::tuyen_tap( $bai->ID ),
				'so_lieu'   => \Phongdao\DichVu\DoLuong\DoLuong::so_lieu( $bai->ID ),
				'ho_so'     => $ho_so,
			)
		);
		if ( ! $tp ) {
			return null;
		}
		// Mục từ người đã nối vào bài mà bài chưa có từ khó riêng cho chữ đó (D-126): hiện nghĩa chung, nhãn "Theo Từ điển"
		$tp['chu_thich'] = array_merge( $tp['chu_thich'], self::theo_tu_dien( $bai, $tp['chu_thich'] ) );
		// Từ khó nối mục từ: lối sang Từ điển (số bài), ảnh, bản đồ của địa danh đã xác nhận; đáp án câu đố có ảnh
		foreach ( $tp['chu_thich'] as $i => $c ) {
			$t = $c['muc'] ? get_term( $c['muc'], 'muc_tu' ) : null;
			if ( $t instanceof \WP_Term ) {
				$tp['chu_thich'][ $i ]['muc'] = array( 'url' => (string) get_term_link( $t ), 'so_bai' => (int) $t->count, 'them' => \Phongdao\GiaoTiep\Khoi\DiaDanh::html( \Phongdao\GiaoTiep\Khoi\DiaDanh::cua_muc_tu( (int) $t->term_id ) ?? array(), 'thumbnail' ) );
			} else {
				$tp['chu_thich'][ $i ]['muc'] = null;
			}
		}
		$tp['dap_an_them'] = '' === $tp['dap_an'] ? '' : \Phongdao\GiaoTiep\Khoi\DiaDanh::html( \Phongdao\GiaoTiep\Khoi\DiaDanh::cua_dap_an( $tp['dap_an'] ) ?? array(), 'thumbnail' );
		$tp['anh_minh_hoa_html'] = \Phongdao\GiaoTiep\Khoi\AnhMinhHoa::html( (int) $tp['anh_minh_hoa'] );
		// Đồng dao: trò chơi gắn với bài và cách chơi người biên soạn đã lưu ở trò chơi (D-119, khối Cách chơi)
		$tp['tro_choi'] = 'dong-dao' === $tp['the_loai'] ? self::tro_choi( $bai ) : array();
		// Câu gần nghĩa · trái nghĩa (D-119): chỉ câu đã đăng, không mật khẩu, không bật "Có từ ngữ thô tục"
		$tp['gan_nghia'] = self::gan_nghia( (array) $tp['gan_nghia'] );
		// Mô-típ của bài (D-119): nhãn ở trang bài, chỉ mô-típ có từ hai bài (trang mô-típ một bài không dẫn đi đâu)
		$tp['mo_tip'] = array_values( array_filter( self::term( $bai, 'mo_tip' ), static fn( array $t ): bool => $t['so'] >= 2 ) );
		return array( 'cam_nghi' => self::cam_nghi( $bai->ID ), 'cach_hieu' => self::cam_nghi( $bai->ID, 'cach_hieu' ) ) + $tp;
	}

	/**
	 * Câu gần nghĩa · trái nghĩa người biên soạn đã nối → câu hiện cho người đọc: câu ngắn (một, hai dòng) hiện chính lời trên
	 * một dòng, câu dài hiện tên bài. Bỏ câu chưa đăng, có mật khẩu hay có từ ngữ thô tục.
	 *
	 * @param list<array{id:int,quan_he:string}> $ds
	 * @return list<array{url:string,cau:string,quan_he:string}>
	 */
	private static function gan_nghia( array $ds ): array {
		$ra = array();
		foreach ( $ds as $m ) {
			$b = get_post( (int) $m['id'] );
			if ( ! $b || KhaiBao::TAC_PHAM !== $b->post_type || 'publish' !== $b->post_status || '' !== $b->post_password || null !== PhanBai::lay( $b->ID, 'tho_tuc' ) ) {
				continue;
			}
			$loi  = array_values( array_filter( array_map( 'trim', (array) ( PhanBai::lay( $b->ID, 'loi' )['dong'] ?? array() ) ), 'strlen' ) );
			$ra[] = array(
				'url'     => (string) get_permalink( $b ),
				'cau'     => $loi && count( $loi ) <= 2 ? implode( ' ', \Phongdao\GiaoTiep\Khoi\TacPham::mot_dong( $loi ) ) : (string) $b->post_title,
				'quan_he' => (string) $m['quan_he'],
			);
		}
		return $ra;
	}

	/**
	 * Dữ liệu nhẹ cho ảnh chia sẻ (DichVu\AnhChiaSe): chỉ lời, ý nghĩa, cờ nghi, thể loại, thể thơ — không đọc cảm nghĩ, số liệu,
	 * tuyển tập. @return array<string,mixed>|null như tac_pham()
	 */
	public static function cho_anh( \WP_Post $bai ): ?array {
		if ( KhaiBao::TAC_PHAM !== $bai->post_type ) {
			return null;
		}
		return TrangTacPham::tu_phan(
			array(
				'loi'     => PhanBai::lay( $bai->ID, 'loi' ),
				'y_nghia' => PhanBai::lay( $bai->ID, 'y_nghia' ),
				'co_nghi' => PhanBai::lay( $bai->ID, 'co_nghi' ),
				'tho_tuc' => PhanBai::lay( $bai->ID, 'tho_tuc' ),
			),
			array(
				'id'       => $bai->ID,
				'tieu_de'  => $bai->post_title,
				'url'      => (string) get_permalink( $bai ),
				'the_loai' => self::the_loai( $bai ),
				'the_tho'  => self::the_tho( self::term( $bai, 'hinh_thuc' ) ),
			)
		);
	}

	/** Thể thơ trong các hình thức của bài ('' = không rõ). */
	private static function the_tho( array $hinh_thuc ): string {
		$ra = '';
		foreach ( $hinh_thuc as $t ) {
			if ( isset( TheTho::DS[ $t['ma'] ] ) ) {
				$ra = $t['ma'];
			}
		}
		return $ra;
	}

	/**
	 * Cảm nghĩ (hoặc cách hiểu của bạn đọc, `$loai = 'cach_hieu'`) đã được người bấm Hiện, cũ trước.
	 *
	 * @return list<array{id:int,ten:string,chu:string,ngay:string}>
	 */
	public static function cam_nghi( int $id, string $loai = 'comment' ): array {
		return array_map(
			static fn( \WP_Comment $c ): array => array(
				'id'   => (int) $c->comment_ID,
				'ten'  => (string) $c->comment_author ?: 'Một bạn đọc',
				'chu'  => (string) $c->comment_content,
				'ngay' => (string) mysql2date( 'd/m/Y', $c->comment_date ),
			),
			get_comments( array( 'post_id' => $id, 'status' => 'approve', 'type' => $loai, 'order' => 'ASC', 'number' => 50 ) ) ?: array()
		);
	}

	/** Thẻ bài trong danh sách. */
	public static function the( \WP_Post $bai ): ?array {
		return TrangTacPham::the(
			self::phan( $bai ),
			array(
				'id'       => $bai->ID,
				'tieu_de'  => $bai->post_title,
				'url'      => (string) get_permalink( $bai ),
				'the_loai' => self::the_loai( $bai ),
			)
		);
	}

	public static function the_loai( \WP_Post $bai ): ?string {
		$t = get_the_terms( $bai, KhaiBao::THE_LOAI );
		return is_array( $t ) && $t ? $t[0]->slug : null;
	}

	/** Các phần hiển thị (bỏ nguon_may ngay từ đây). */
	/**
	 * Nghĩa chung của các mục từ bài đang gắn (người nối ở Bổ sung → Gợi ý Từ điển, hay từ dữ liệu cũ) mà không từ khó nào
	 * của bài dùng và bài không có từ khó cùng chữ: mỗi mục một từ khó "theo Từ điển" (lời giải là phần giải thích của mục
	 * từ do người biên soạn viết). Bài đã có từ khó riêng thì chỉ hiện từ khó riêng. Mục từ chưa có giải thích thì bỏ.
	 *
	 * @param list<array> $ct từ khó riêng của bài (muc là số mục từ)
	 * @return list<array>
	 */
	public static function theo_tu_dien( \WP_Post $bai, array $ct ): array {
		$gan = get_the_terms( $bai, 'muc_tu' );
		if ( ! is_array( $gan ) || ! $gan ) {
			return array();
		}
		$dung = array_flip( array_filter( array_map( 'intval', array_column( $ct, 'muc' ) ) ) );
		$ra   = array();
		foreach ( $gan as $t ) {
			$giai = trim( wp_strip_all_tags( (string) $t->description ) );
			if ( '' === $giai || isset( $dung[ (int) $t->term_id ] ) ) {
				continue;
			}
			foreach ( $ct as $c ) {
				if ( \Phongdao\GiaoTiep\BienTap\TamSua::cung_chu( (string) ( $c['tu'] ?? '' ), (string) $t->name ) ) {
					continue 2;
				}
			}
			$ra[] = array( 'tu' => (string) $t->name, 'giai' => $giai, 'dinh_dang' => '', 'muc' => (int) $t->term_id, 'may' => false, 'nguon' => null, 'theo_td' => true );
		}
		return $ra;
	}

	private static function phan( \WP_Post $bai ): array {
		$ra = array();
		foreach ( Phan::ds( $bai->post_type ) as $p ) {
			if ( 'nguon_may' !== $p ) {
				$ra[ $p ] = PhanBai::lay( $bai->ID, $p );
			}
		}
		return $ra;
	}

	/** @return array<int,array{0:string,1:string,ma:string,so:int}> [tên, đường dẫn] mỗi mục phân loại, kèm mã và số bài */
	private static function term( \WP_Post $bai, string $phan_loai ): array {
		$ra = array();
		foreach ( get_the_terms( $bai, $phan_loai ) ?: array() as $t ) {
			if ( $t instanceof \WP_Term ) {
				$url  = get_term_link( $t );
				$ra[] = array( $t->name, is_string( $url ) ? $url : '', 'ma' => $t->slug, 'so' => (int) $t->count );
			}
		}
		return $ra;
	}

	/**
	 * Trò chơi của bài đồng dao kèm cách chơi đã lưu ở trò chơi (`_phongdao_cach_choi`: số người, độ tuổi, đồ dùng, các
	 * bước, nguồn, giấy phép); tên hồ sơ nguồn chỉ lấy khi hồ sơ đã đăng. Trò chơi chưa có cách chơi vẫn có tên, đường dẫn.
	 *
	 * @return list<array{ten:string,url:string,cach_choi:array<string,mixed>}>
	 */
	private static function tro_choi( \WP_Post $bai ): array {
		$ra = array();
		foreach ( get_the_terms( $bai, 'tro_choi' ) ?: array() as $t ) {
			if ( ! $t instanceof \WP_Term ) {
				continue;
			}
			$cc = get_term_meta( $t->term_id, '_phongdao_cach_choi', true );
			$cc = is_array( $cc ) ? $cc : array();
			if ( is_array( $cc['nguon'] ?? null ) ) {
				$hs                        = empty( $cc['nguon']['ho_so'] ) ? null : get_post( (int) $cc['nguon']['ho_so'] );
				$cc['nguon']['ten_ho_so'] = $hs && KhaiBao::HO_SO_NGUON === $hs->post_type && 'publish' === $hs->post_status ? $hs->post_title : '';
			}
			$url  = get_term_link( $t );
			$ra[] = array( 'ten' => $t->name, 'url' => is_string( $url ) ? $url : '', 'cach_choi' => $cc );
		}
		return $ra;
	}

	/** Tuyển tập đã đăng có bài này: [tên, đường dẫn]. */
	private static function tuyen_tap( int $id ): array {
		return array_map( static fn( array $t ): array => array( $t['ten'], $t['url'] ), self::tuyen_tap_co( $id ) );
	}

	/**
	 * Tuyển tập đã đăng có bài này, kèm các bài theo thứ tự trong bộ (Đọc tiếp lấy bài kề). Vài chục tuyển tập, đọc thẳng
	 * meta.
	 *
	 * @return list<array{ten:string,url:string,ids:int[]}>
	 */
	public static function tuyen_tap_co( int $id ): array {
		$ra = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ) as $tt ) {
			$ids = array_values( array_map( 'intval', array_column( PhanBai::lay( $tt->ID, 'muc' )['muc'] ?? array(), 'tac_pham_id' ) ) );
			if ( in_array( $id, $ids, true ) ) {
				$ra[] = array( 'ten' => $tt->post_title, 'url' => (string) get_permalink( $tt ), 'ids' => $ids );
			}
		}
		return $ra;
	}
}
