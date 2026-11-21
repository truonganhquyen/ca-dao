<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Danh sách tác phẩm theo truy vấn chính (bộ thiết kế R02 Tất cả, R03 Thể loại, R04 Chủ đề / vùng / mô-típ / hình thức):
 * thẻ bài, bộ lọc gửi bằng form (tắt JavaScript vẫn lọc được), phân trang 30 bài. Số bài, thứ tự do
 * HaTang\KhaiBao::truy_van_danh_sach đặt.
 */
final class DanhSach {

	/** Phân loại => dòng chữ nhỏ trên tiêu đề. */
	private const DAU = array(
		'the_loai'  => 'Đọc theo thể loại',
		'chu_de'    => 'Chủ đề',
		'vung'      => 'Nơi lưu truyền',
		'mo_tip'    => 'Mô-típ',
		'hinh_thuc' => 'Hình thức',
		'muc_tu'    => 'Từ điển',
		'tro_choi'  => 'Trò chơi',
	);

	/** Bộ lọc của trang Tất cả: tham số đường dẫn (= phân loại) => nhãn. */
	private const LOC = array(
		'the_loai'  => array( 'Thể loại', 'Tất cả' ),
		'chu_de'    => array( 'Chủ đề', 'Tất cả chủ đề' ),
		'hinh_thuc' => array( 'Hình thức', 'Tất cả hình thức' ),
		'vung'      => array( 'Nơi lưu truyền', 'Chưa giới hạn' ),
	);

	public static function hien( \WP_Query $q ): string {
		$tat_ca = $q->is_post_type_archive( KhaiBao::TAC_PHAM );
		$term   = $tat_ca ? null : $q->get_queried_object();
		if ( ! $tat_ca && ! ( $term instanceof \WP_Term && isset( self::DAU[ $term->taxonomy ] ) ) ) {
			return '';
		}
		$the = '';
		$so  = 0;
		$chen = $tat_ca || ! in_array( $term->taxonomy, array( 'muc_tu', 'tro_choi' ), true );
		foreach ( $q->posts as $bai ) {
			$c    = $bai instanceof \WP_Post ? DocBai::the( $bai ) : null;
			if ( $c && $term instanceof \WP_Term && 'muc_tu' === $term->taxonomy ) {
				$c = self::cau_muc_tu( $c, $bai, $term );
			}
			$the .= $c ? self::the( $c ) : '';
			// Cứ một trang từ 10 thẻ: sau thẻ thứ 9 có một ô nổi bật (D-126), để trang 30 thẻ đỡ đều đều
			if ( $c && 9 === ++$so && $chen && count( $q->posts ) >= 10 ) {
				$the .= self::tu_kho_hom_nay();
			}
		}
		$co_nghia = KhaiBao::chi_co_nghia( $q );
		$dang_loc = ( $tat_ca && array_filter( array_map( static fn( string $k ): string => (string) $q->get( $k ), array_keys( self::LOC ) ) ) ) || $co_nghia;

		if ( $tat_ca ) {
			$dau = self::duong_dan( 'Tất cả tác phẩm' ) . '<p class="eyebrow">Kho tác phẩm</p><h1>Mỗi câu, một lối đọc.</h1><p class="muted">Tìm theo thể loại, hình thức, chủ đề hoặc nơi lưu truyền.</p>' . self::thanh_loc( $q, (string) get_post_type_archive_link( KhaiBao::TAC_PHAM ), true );
		} else {
			$mo_ta = trim( wp_strip_all_tags( (string) $term->description ) );
			$dau   = self::duong_dan( $term->name, $term->taxonomy ) . ( KhaiBao::THE_LOAI === $term->taxonomy ? self::dau_the_loai( $term, $mo_ta ) : '<p class="eyebrow">' . esc_html( self::DAU[ $term->taxonomy ] ) . '</p><h1>' . esc_html( $term->name ) . '</h1>'
				. ( '' === $mo_ta ? '' : '<p class="muted">' . esc_html( $mo_ta ) . '</p>' ) )
				. ( 'muc_tu' === $term->taxonomy ? self::nghia_khac( $term ) : '' )
				// Mục từ: ảnh Commons, bản đồ nhỏ, Wikipedia của địa danh đã xác nhận (như MVP)
				. ( 'muc_tu' === $term->taxonomy ? self::nguon_loi_giai( $term ) . DiaDanh::html( DiaDanh::cua_muc_tu( (int) $term->term_id ) ?? array() ) . \Phongdao\GiaoTiep\BienTap\SuaMucTu::form( $term ) : '' );
		}
		if ( ! $tat_ca && ! in_array( $term->taxonomy, array( 'muc_tu', 'tro_choi' ), true ) ) {
			$dau .= self::thanh_loc( $q, (string) get_term_link( $term ), false );
		}
		$dem = (int) $q->found_posts;
		$giua = '<p class="small muted">' . number_format( $dem, 0, ',', '.' ) . ' tác phẩm' . ( $co_nghia ? ' đã có giải nghĩa' : '' ) . ( $q->max_num_pages > 1 ? ' · 30 bài mỗi trang' : '' ) . '</p>';
		if ( '' === $the ) {
			$xoa  = $tat_ca ? (string) get_post_type_archive_link( KhaiBao::TAC_PHAM ) : (string) get_term_link( $term );
			$giua = $dang_loc
				? self::trong( $co_nghia ? 'Chưa có bài đã có giải nghĩa ở đây' : 'Chưa có bài khớp các bộ lọc', $co_nghia ? 'Thư viện đang bổ sung giải nghĩa dần. Bạn có thể đọc các bài khác ở đây.' : 'Thử bỏ một lựa chọn hoặc xoá toàn bộ bộ lọc.', '<a class="btn primary" href="' . esc_url( $xoa ) . '">Xoá lọc</a>' )
				: self::trong( 'Chưa có bài nào ở đây', 'Thư viện đang bổ sung dần. Bạn có thể đọc các bài khác trong kho.', Ve::lien_ket( 'kho', 'Xem tất cả tác phẩm', 'btn primary' ) );
		} else {
			$giua .= '<div class="grid">' . $the . '</div>' . self::phan_trang( $q );
		}
		$h2 = $term instanceof \WP_Term && 'muc_tu' === $term->taxonomy ? '<h2 id="ds-tieu-de">Câu có chữ “' . esc_html( $term->name ) . '”</h2>' : '<h2 class="sr-only" id="ds-tieu-de">Các tác phẩm</h2>';
		return $dau . '<section class="section" aria-labelledby="ds-tieu-de">' . $h2 . $giua . '</section>';
	}

	/**
	 * Trang mục từ (D-126): thẻ bài hiện đúng dòng có chữ, chữ được tô; dòng dưới cho biết bài có chú giải riêng hay hiện
	 * nghĩa theo Từ điển.
	 */
	private static function cau_muc_tu( array $c, \WP_Post $bai, \WP_Term $term ): array {
		$khoa = \Phongdao\DichVu\TuDien\GoiYNoi::khoa( (string) $term->name );
		$dong = array();
		foreach ( (array) ( \Phongdao\KhoLuu\PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array() ) as $d ) {
			$to = \Phongdao\DichVu\TuDien\GoiYNoi::to( (string) $d, $khoa );
			if ( null !== $to ) {
				$dong[] = '<span class="dong">' . $to . '</span>';
			}
			if ( count( $dong ) >= 2 ) {
				break;
			}
		}
		if ( $dong ) {
			$c['tim_chu_html'] = implode( '', $dong );
		}
		$rieng = false;
		$ct    = \Phongdao\KhoLuu\PhanBai::lay( $bai->ID, 'chu_thich' );
		foreach ( (array) ( $ct['muc'] ?? array() ) as $m ) {
			if ( is_array( $m ) && ( (int) ( $m['muc_tu_id'] ?? 0 ) === (int) $term->term_id || \Phongdao\GiaoTiep\BienTap\TamSua::cung_chu( (string) ( $m['tu'] ?? '' ), (string) $term->name ) ) ) {
				$rieng = true;
				break;
			}
		}
		$c['ly_do'] = $rieng ? 'Bài có chú giải riêng' : ( '' === trim( (string) $term->description ) ? '' : 'Hiện nghĩa theo Từ điển' );
		return $c;
	}

	/** Nguồn lời giải của mục từ: người biên soạn điền thì hiện (D-081); lời giải máy chép thì ẩn nguồn theo chính sách (D-068). */
	private static function nguon_loi_giai( \WP_Term $t ): string {
		$lg = get_term_meta( $t->term_id, '_phongdao_loi_giai', true );
		if ( ! is_array( $lg ) || \Phongdao\MienDuLieu\NguoiLam::NGUOI_VIET !== ( $lg['nguoi_lam'] ?? '' ) || empty( $lg['nguon'] ) ) {
			return '';
		}
		$hs  = empty( $lg['nguon']['ho_so'] ) ? null : get_post( (int) $lg['nguon']['ho_so'] );
		$ten = $hs instanceof \WP_Post && 'publish' === $hs->post_status ? $hs->post_title : '';
		$url = (string) ( $lg['nguon']['url'] ?? '' );
		if ( '' === $ten && '' === $url ) {
			return '';
		}
		return '<p class="small muted">Nguồn lời giải: ' . ( '' === $url ? esc_html( $ten ) : '<a href="' . esc_url( $url ) . '" rel="nofollow">' . esc_html( '' === $ten ? $url : $ten ) . '</a>' ) . '</p>';
	}

	/** Thẻ bài (bộ thiết kế: card trong common.mjs). Tên trùng dòng đầu thì không in lại dòng đó (D-119). */
	public static function the( array $c ): string {
		$ds   = (array) $c['dong'];
		if ( $ds && Ve::khoa_cum( (string) $c['tieu_de'] ) === Ve::khoa_cum( (string) $ds[0] ) ) {
			array_shift( $ds );
		}
		$dong = implode( '', array_map( static fn( string $d ): string => '<span class="dong">' . esc_html( $d ) . '</span>', $ds ) );
		$chu  = $c['tim_chu_html'] ?? $dong;
		$than = '<h3><a href="' . esc_url( $c['url'] ) . '">' . ( $c['tim_ten_html'] ?? esc_html( $c['tieu_de'] ) ) . '</a></h3>'
			. ( '' === $chu ? '' : '<p class="serif dong-wrap">' . $chu . '</p>' )
			. ( isset( $c['tim_chu_html'] ) || '' === $c['y_nghia'] ? '' : '<p class="muted y-mot">' . esc_html( $c['y_nghia'] ) . '</p>' );
		// Có từ ngữ thô tục (D-111): tên, lời, ý nghĩa nằm trong khối đóng; nút Đọc không đọc to tên bài
		$tho  = ! empty( $c['tho_tuc'] );
		$ly   = (string) ( $c['ly_do'] ?? '' );
		$ly   = '' !== $ly ? $ly : ( empty( $c['so_tu_kho'] ) ? '' : $c['so_tu_kho'] . ' từ khó' );
		return '<article class="card the-loi g-' . esc_attr( $c['the_loai'] ?: 'ca-dao' ) . '">'
			. ( $c['ten_the_loai'] ? '<span class="tag">' . esc_html( $c['ten_the_loai'] ) . '</span>' : '' )
			. ( $c['co_nghi'] ? '<p class="flag">Có thể không phải dân gian · <a href="' . esc_url( $c['url'] . '#vi-sao' ) . '">Vì sao?</a></p>' : '' )
			. ( $tho ? Ve::che_tho_tuc( $than ) : $than )
			. '<div class="row between"><span class="small muted">' . esc_html( $ly ) . '</span><a class="btn compact ghost" href="' . esc_url( $c['url'] ) . '" aria-label="' . esc_attr( $tho ? 'Đọc bài có từ ngữ thô tục' : 'Đọc: ' . $c['tieu_de'] ) . '">Đọc ' . Ve::icon( 'arrow' ) . '</a></div></article>';
	}

	private static function duong_dan( string $ten, string $tax = '' ): string {
		$giua = 'Tất cả tác phẩm' === $ten ? '' : ( 'muc_tu' === $tax ? Ve::lien_ket( 'tu-dien', 'Từ điển' ) : Ve::lien_ket( 'kho', 'Kho tác phẩm' ) ) . '<span aria-hidden="true">/</span>';
		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>' . $giua . '<span>' . esc_html( $ten ) . '</span></nav>';
	}

	/** Trang mục từ: lối sang các nghĩa khác của cùng một chữ (vd. ba mục "Mai"), mỗi nghĩa một dòng tóm tắt. */
	private static function nghia_khac( \WP_Term $term ): string {
		$khac = TuDien::cung_mat_chu( $term );
		if ( ! $khac ) {
			return '';
		}
		$li = '';
		foreach ( $khac as $t ) {
			$giai = trim( wp_strip_all_tags( (string) $t->description ) );
			$giai = mb_strlen( $giai ) > 90 ? rtrim( mb_substr( $giai, 0, 89 ) ) . '…' : $giai;
			$url  = get_term_link( $t );
			$li  .= '<li><a href="' . esc_url( is_wp_error( $url ) ? '' : $url ) . '">' . esc_html( '' === $giai ? $t->name : $giai ) . '</a> <span class="small muted">· ' . (int) $t->count . ' bài</span></li>';
		}
		return '<div class="tu-dien-khac"><p class="small muted">Chữ “' . esc_html( $term->name ) . '” còn ' . count( $khac ) . ' nghĩa khác trong Từ điển:</p><ul class="tu-dien-nghia">' . $li . '</ul></div>';
	}

	/** Bộ lọc của trang Tất cả: form GET, mỗi ô là một phân loại (WordPress tự lọc theo tham số cùng tên). */
	/**
	 * Thanh lọc dùng chung (D-126) cho trang Tất cả và trang phân loại: một hàng ô chọn (Tất cả: thể loại, chủ đề, hình
	 * thức, nơi lưu truyền) · "Có giải nghĩa" · Sắp xếp · Áp dụng; mục đang chọn hiện thành chip có dấu × và "Xoá lọc".
	 * Điện thoại: một nút "Bộ lọc (n)" mở các ô (thẻ details, không cần JavaScript). Chọn xong bấm Áp dụng — không tự tải
	 * lại trang khi đổi ô chọn (WCAG 3.2.2).
	 */
	private static function thanh_loc( \WP_Query $q, string $url, bool $tat_ca ): string {
		$dang = array_filter( array( 'sap' => is_string( $q->get( 'sap' ) ) ? (string) $q->get( 'sap' ) : '', 'co' => KhaiBao::chi_co_nghia( $q ) ? 'nghia' : '' ) );
		$o    = '';
		$chip = array();
		if ( $tat_ca ) {
			foreach ( self::LOC as $pl => [ $nhan, $tat_ca_ten ] ) {
				$chon = (string) $q->get( $pl );
				$ds   = 'the_loai' === $pl
					? array_map( static fn( string $ma, string $ten ): array => array( $ma, $ten ), array_keys( TheLoai::DS ), TheLoai::DS )
					: array_map( static fn( \WP_Term $t ): array => array( $t->slug, $t->name ), self::muc( $pl ) );
				$opt  = '<option value="">' . esc_html( array( 'the_loai' => 'Mọi thể loại', 'chu_de' => 'Mọi chủ đề', 'hinh_thuc' => 'Mọi hình thức', 'vung' => 'Mọi vùng' )[ $pl ] ?? $tat_ca_ten ) . '</option>';
				foreach ( $ds as [ $ma, $ten ] ) {
					$opt .= '<option value="' . esc_attr( $ma ) . '"' . selected( $chon, $ma, false ) . '>' . esc_html( $ten ) . '</option>';
					if ( $chon === $ma ) {
						$chip[ $pl ] = array( $nhan, $ten );
					}
				}
				if ( '' !== $chon ) {
					$dang[ $pl ] = $chon;
				}
				$o .= '<div class="loc-o"><label class="sr-only" for="loc-' . esc_attr( $pl ) . '">' . esc_html( $nhan ) . '</label><select id="loc-' . esc_attr( $pl ) . '" name="' . esc_attr( $pl ) . '"' . ( '' === $chon ? '' : ' class="co"' ) . '>' . $opt . '</select></div>';
			}
		}
		$co = KhaiBao::chi_co_nghia( $q );
		if ( $co ) {
			$chip['co'] = array( 'Lọc', 'Có giải nghĩa' );
		}
		$o .= '<label class="loc-o loc-hop"><input type="checkbox" name="co" value="nghia"' . checked( $co, true, false ) . '> Có giải nghĩa</label>';
		$ds_sap = array( 'hieu' => 'Có nghĩa trước', 'moi' => 'Mới thêm', 'doc' => 'Đọc nhiều', 'az' => 'A–Z' );
		$sap    = isset( $ds_sap[ $dang['sap'] ?? '' ] ) ? $dang['sap'] : 'hieu';
		$opt    = '';
		foreach ( $ds_sap as $k => $ten ) {
			$opt .= '<option value="' . $k . '"' . selected( $sap, $k, false ) . '>' . esc_html( 'Xếp: ' . mb_strtolower( $ten ) ) . '</option>';
		}
		$o .= '<div class="loc-o loc-sap"><label class="sr-only" for="loc-sap">Sắp xếp</label><select id="loc-sap" name="sap">' . $opt . '</select></div>'
			. '<button type="submit" class="btn primary loc-ap">Áp dụng</button>';
		$ds_chip = '';
		foreach ( $chip as $pl => [ $nhan, $ten ] ) {
			$ds_chip .= '<a class="loc-x" href="' . esc_url( add_query_arg( array_diff_key( $dang, array( $pl => 1 ) ), $url ) ) . '" aria-label="' . esc_attr( 'Bỏ lọc ' . $nhan . ': ' . $ten ) . '">' . esc_html( $ten ) . ' <span aria-hidden="true">×</span></a>';
		}
		$so = count( $chip );
		return '<form class="thanh-loc" method="get" action="' . esc_url( $url ) . '" role="search" aria-label="Lọc danh sách"><details class="loc-mo"><summary>Bộ lọc' . ( $so ? ' (' . $so . ')' : '' ) . '</summary></details>'
			. '<div class="loc-chon">' . $o . '</div></form>'
			. ( $so ? '<p class="loc-ds"><span class="small muted">Đang lọc:</span> ' . $ds_chip . ' <a class="loc-xoa" href="' . esc_url( $url ) . '">Xoá lọc</a></p>' : '' );
	}

	/**
	 * Đầu trang thể loại (D-126): khối màu của thể loại, hoạ tiết sóng ở đáy, số bài và số bài đã có giải nghĩa, các thể
	 * thơ / hình thức nhiều bài nhất (dẫn tới Tất cả đã lọc). Lời giới thiệu dài thu vào "Giới thiệu về …".
	 */
	private static function dau_the_loai( \WP_Term $term, string $mo_ta ): string {
		$nghia = \Phongdao\DichVu\DungTrang\DuLieuTrangChu::so_co_nghia( $term->slug );
		$ht    = '';
		foreach ( self::hinh_thuc_nhieu( $term ) as [ $slug, $ten, $so ] ) {
			$ht .= '<a href="' . esc_url( add_query_arg( array( 'the_loai' => $term->slug, 'hinh_thuc' => $slug ), (string) get_post_type_archive_link( KhaiBao::TAC_PHAM ) ) ) . '">' . esc_html( $ten ) . ' <span class="small">' . esc_html( number_format( $so, 0, ',', '.' ) ) . '</span></a>';
		}
		$gt = '' === $mo_ta ? '' : ( mb_strlen( $mo_ta ) > 220
			? '<details class="gioi-thieu"><summary>Giới thiệu về ' . esc_html( mb_strtolower( $term->name ) ) . '</summary><p>' . esc_html( $mo_ta ) . '</p></details>'
			: '<p class="muted">' . esc_html( $mo_ta ) . '</p>' );
		return '<header class="dau-tl g-' . esc_attr( $term->slug ) . '"><p class="eyebrow">Thể loại</p><h1>' . esc_html( $term->name ) . '</h1>'
			. '<p class="dau-tl-so"><span><b>' . esc_html( number_format( (int) $term->count, 0, ',', '.' ) ) . '</b> bài</span>' . ( $nghia ? '<span><b>' . esc_html( number_format( $nghia, 0, ',', '.' ) ) . '</b> bài đã có giải nghĩa</span>' : '' ) . '</p>'
			. ( '' === $ht ? '' : '<nav class="dau-tl-ht" aria-label="' . esc_attr( 'Hình thức của ' . $term->name ) . '">' . $ht . '</nav>' ) . '</header>' . $gt;
	}

	/** Hình thức có nhiều bài nhất trong một thể loại (tối đa 6, đệm 12 giờ). @return list<array{0:string,1:string,2:int}> */
	private static function hinh_thuc_nhieu( \WP_Term $tl ): array {
		$khoa = 'phongdao_tl_hinh_thuc_' . $tl->term_id;
		$ds   = get_transient( $khoa );
		if ( is_array( $ds ) ) {
			return $ds;
		}
		global $wpdb;
		$hang = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.slug, t.name, COUNT(*) AS so FROM {$wpdb->term_relationships} r1
				JOIN {$wpdb->term_relationships} r2 ON r2.object_id = r1.object_id
				JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = r2.term_taxonomy_id AND tt.taxonomy = 'hinh_thuc'
				JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
				JOIN {$wpdb->posts} p ON p.ID = r1.object_id AND p.post_status = 'publish'
				WHERE r1.term_taxonomy_id = %d GROUP BY t.term_id ORDER BY so DESC LIMIT 6",
				(int) $tl->term_taxonomy_id
			)
		) ?: array();
		$ds = array_map( static fn( $h ): array => array( (string) $h->slug, (string) $h->name, (int) $h->so ), $hang );
		set_transient( $khoa, $ds, 12 * HOUR_IN_SECONDS );
		return $ds;
	}

	/**
	 * Ô nổi bật "Từ khó hôm nay" (D-126): một mục từ có giải thích và từ hai bài, đổi mỗi ngày. Giải thích là của người
	 * biên soạn trong Từ điển. Không có mục nào đủ điều kiện thì không hiện.
	 */
	private static function tu_kho_hom_nay(): string {
		$khoa = 'phongdao_tu_kho_hom_nay_' . wp_date( 'Ymd' );
		$id   = get_transient( $khoa );
		if ( false === $id ) {
			$ds = get_terms( array( 'taxonomy' => 'muc_tu', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 300 ) );
			$ds = array_values( array_filter( is_array( $ds ) ? $ds : array(), static fn( $t ): bool => $t instanceof \WP_Term && $t->count >= 2 && '' !== trim( wp_strip_all_tags( (string) $t->description ) ) ) );
			$id = $ds ? (int) $ds[ crc32( wp_date( 'Y-m-d' ) ) % count( $ds ) ]->term_id : 0;
			set_transient( $khoa, $id, DAY_IN_SECONDS );
		}
		$t = $id ? get_term( (int) $id, 'muc_tu' ) : null;
		if ( ! $t instanceof \WP_Term ) {
			return '';
		}
		$giai = trim( wp_strip_all_tags( (string) $t->description ) );
		$giai = mb_strlen( $giai ) > 140 ? rtrim( mb_substr( $giai, 0, 139 ) ) . '…' : $giai;
		$url  = get_term_link( $t );
		return '<aside class="card o-noi" aria-labelledby="o-noi-h"><p class="eyebrow" id="o-noi-h">Từ khó hôm nay</p><p class="o-noi-ten"><a href="' . esc_url( is_wp_error( $url ) ? '' : $url ) . '">' . esc_html( $t->name ) . '</a></p><p>' . esc_html( $giai ) . '</p><p class="small muted">' . esc_html( number_format( (int) $t->count, 0, ',', '.' ) ) . ' bài có từ này · ' . Ve::lien_ket( 'tu-dien', 'Mở Từ điển' ) . '</p></aside>';
	}

	/** Phân trang: mọi trang khi ≤ 7 trang; nhiều hơn thì trang đầu, cuối và hai trang quanh trang đang xem. */
	private static function phan_trang( \WP_Query $q ): string {
		$tong = (int) $q->max_num_pages;
		if ( $tong < 2 ) {
			return '';
		}
		$dang = max( 1, (int) $q->get( 'paged' ) );
		$o    = '';
		$truoc = 0;
		for ( $i = 1; $i <= $tong; $i++ ) {
			if ( $tong > 7 && 1 !== $i && $tong !== $i && abs( $i - $dang ) > 2 ) {
				continue;
			}
			if ( $truoc && $i - $truoc > 1 ) {
				$o .= '<span class="small muted" aria-hidden="true">…</span>';
			}
			$o    .= $i === $dang
				? '<span class="btn primary" aria-current="page">' . $i . '</span>'
				: '<a class="btn" href="' . esc_url( get_pagenum_link( $i ) ) . '" aria-label="Trang ' . $i . '">' . $i . '</a>';
			$truoc = $i;
		}
		return '<nav class="pagination" aria-label="Phân trang">' . $o . '</nav>';
	}

	/** @return \WP_Term[] các mục đang có bài của một phân loại */
	private static function muc( string $phan_loai ): array {
		$ds = get_terms( array( 'taxonomy' => $phan_loai, 'hide_empty' => true ) );
		return is_array( $ds ) ? $ds : array();
	}

	private static function trong( string $tieu_de, string $than, string $nut ): string {
		return '<div class="empty">' . Ve::icon( 'book' ) . '<h2>' . esc_html( $tieu_de ) . '</h2><p class="muted">' . esc_html( $than ) . '</p>' . $nut . '</div>';
	}
}
