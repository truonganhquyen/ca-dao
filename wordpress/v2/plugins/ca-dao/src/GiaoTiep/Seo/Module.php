<?php

namespace Phongdao\GiaoTiep\Seo;

use Phongdao\DichVu\AnhChiaSe\Module as AnhChiaSe;
use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\GiaoTiep\Khoi\TuyenTap;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\CapChatLuong;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * SEO (docs/26 mục 10, docs/27 mục 10; chuyển từ MVP seo.php). Không đổi blog_public: mở cho Google là việc của người
 * sáng lập (H5); khi còn chặn, WordPress tự noindex mọi trang và tắt sơ đồ trang.
 * Lập chỉ mục (D-106): mọi tác phẩm đã đăng — không giấu theo độ đầy đủ; cấp L0–L3 chỉ dùng nội bộ (xếp việc, bài nổi
 * bật, Câu hôm nay) và để đặt tiêu đề "— nghĩa và cách dùng" khi bài có Ý nghĩa. Tuyển tập đã công khai, trang thể loại,
 * trang chủ đề / vùng / mô-típ / hình thức có ≥ 3 tác phẩm đã đăng, mục từ có lời giải do dự án viết (D-068). Bài nháp,
 * ẩn, trang lọc, trang tìm: noindex. Công tắc chặn Google toàn site (`blog_public`, H5) không đổi ở đây.
 */
final class Module implements \Phongdao\HaTang\Module {

	/** Phân loại cần đủ tác phẩm đã đăng mới lập chỉ mục. */
	public const PHAN_LOAI_DEM = array( 'chu_de', 'vung', 'mo_tip', 'hinh_thuc' );

	public const IT_NHAT = 3;

	public function dang_ky(): void {
		remove_action( 'wp_head', 'wp_generator' ); // không lộ phiên bản WordPress
		add_filter( 'wp_robots', array( self::class, 'robots' ) );
		add_filter( 'document_title_parts', array( self::class, 'tieu_de' ) );
		add_action( 'wp_head', array( self::class, 'dau_trang' ), 3 );
		add_filter( 'wp_sitemaps_add_provider', static fn( $nha, $ten ) => 'users' === $ten ? false : $nha, 10, 2 );
		add_filter( 'wp_sitemaps_post_types', static fn( array $ds ): array => array_intersect_key( $ds, array_flip( array( KhaiBao::TAC_PHAM, KhaiBao::TUYEN_TAP, KhaiBao::PHAN_TICH, 'page' ) ) ) );
		add_filter( 'wp_sitemaps_posts_query_args', array( self::class, 'so_do_bai' ), 10, 2 );
		add_filter( 'wp_sitemaps_taxonomies', static fn( array $ds ): array => array_intersect_key( $ds, array_flip( array_merge( array( KhaiBao::THE_LOAI, 'muc_tu', 'tro_choi' ), self::PHAN_LOAI_DEM ) ) ) );
		add_filter( 'wp_sitemaps_taxonomies_query_args', array( self::class, 'so_do_phan_loai' ), 10, 2 );
	}

	// ---------- Quy tắc ----------

	/** Bài có Ý nghĩa (tục ngữ: nghĩa bóng) — cấp L2+: dùng cho tiêu đề "— nghĩa và cách dùng", không quyết lập chỉ mục. */
	public static function l2( int $id ): bool {
		$p = get_post( $id );
		return $p && KhaiBao::TAC_PHAM === $p->post_type && ( CapChatLuong::tinh( PhanBai::tat_ca( $id, KhaiBao::TAC_PHAM ), DocBai::the_loai( $p ) ) ?? 0 ) >= 2;
	}

	/** Tác phẩm được lập chỉ mục: đã đăng (D-106). */
	public static function lap_bai( int $id ): bool {
		$p = get_post( $id );
		return $p && KhaiBao::TAC_PHAM === $p->post_type && 'publish' === $p->post_status && '' === (string) $p->post_password;
	}

	/** Số tác phẩm đã đăng gắn một phân loại (một truy vấn, không tính nháp, ẩn, thùng rác). */
	public static function dem_dang( \WP_Term $t ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->term_relationships} r JOIN {$wpdb->posts} p ON p.ID = r.object_id WHERE r.term_taxonomy_id = %d AND p.post_type = %s AND p.post_status = 'publish'", $t->term_taxonomy_id, KhaiBao::TAC_PHAM ) );
	}

	/** Tác phẩm thuộc thể loại câu đố (đáp án, giải thích đáp án không ra mô tả, dữ liệu có cấu trúc — D-114). */
	public static function cau_do( int $id ): bool {
		$p = get_post( $id );
		return $p && 'cau-do' === DocBai::the_loai( $p );
	}

	/** Phân loại có đáng lập chỉ mục không. */
	public static function term_lap( \WP_Term $t ): bool {
		if ( KhaiBao::THE_LOAI === $t->taxonomy || 'tro_choi' === $t->taxonomy ) {
			return $t->count > 0;
		}
		if ( 'muc_tu' === $t->taxonomy ) {
			// Lời giải chép từ nguồn: không lập chỉ mục (như MVP, D-068); thiếu thông tin người làm thì coi như chép
			$lg = get_term_meta( $t->term_id, '_phongdao_loi_giai', true );
			return $t->count > 0 && is_array( $lg ) && NguoiLam::NGUOI_VIET === ( $lg['nguoi_lam'] ?? '' );
		}
		if ( in_array( $t->taxonomy, self::PHAN_LOAI_DEM, true ) ) {
			return self::dem_dang( $t ) >= self::IT_NHAT;
		}
		return false;
	}

	private static function dang_loc(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		return (bool) array_intersect( array_keys( $_GET ), array_merge( array_keys( KhaiBao::PHAN_LOAI ), array( 'sap', 'loai', 'chu', 'q' ) ) );
	}

	public static function robots( array $r ): array {
		$ko = false;
		if ( is_singular( KhaiBao::TAC_PHAM ) ) {
			$ko = ! self::lap_bai( get_queried_object_id() );
		} elseif ( is_tax() ) {
			$t  = get_queried_object();
			$ko = ! ( $t instanceof \WP_Term && self::term_lap( $t ) ) || self::dang_loc();
		} elseif ( is_post_type_archive( array( KhaiBao::TAC_PHAM, KhaiBao::TUYEN_TAP ) ) ) {
			$ko = self::dang_loc();
		}
		if ( $ko ) {
			$r['noindex'] = true;
			// Còn chặn Google toàn site (H5) thì WordPress đã đặt nofollow: không thêm follow mâu thuẫn ("nofollow, follow")
			if ( empty( $r['nofollow'] ) ) {
				$r['follow'] = true;
			}
		}
		return $r;
	}

	public static function tieu_de( array $phan ): array {
		// Câu đố có "Giải thích đáp án" không phải "nghĩa và cách dùng": giữ tên bài (không hứa điều trang không có)
		if ( is_singular( KhaiBao::TAC_PHAM ) && self::l2( get_queried_object_id() ) && ! self::cau_do( get_queried_object_id() ) ) {
			$phan['title'] = get_the_title( get_queried_object_id() ) . ' — nghĩa và cách dùng';
		} elseif ( is_singular( KhaiBao::TUYEN_TAP ) ) {
			$tt  = get_queried_object();
			$muc = TuyenTap::muc( $tt );
			if ( $muc && ! array_filter( $muc, static fn( $m ) => null === PhanBai::lay( $m['bai']->ID, 'y_nghia' ) ) ) {
				$phan['title'] = $tt->post_title . ': ' . count( $muc ) . ' câu có giải nghĩa';
			}
		}
		return $phan;
	}

	// ---------- Mô tả, thẻ chia sẻ, dữ liệu có cấu trúc ----------

	/** Cắt ≤ $toi_da ký tự ở ranh giới từ. */
	public static function cat( string $s, int $toi_da = 160 ): string {
		$s = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $s ) ) );
		if ( mb_strlen( $s ) <= $toi_da ) {
			return $s;
		}
		$s = mb_substr( $s, 0, $toi_da - 1 );
		$c = mb_strrpos( $s, ' ' );
		return rtrim( false !== $c && $c > $toi_da / 2 ? mb_substr( $s, 0, $c ) : $s, " ,;:.-—" ) . '…';
	}

	/**
	 * Mô tả tác phẩm: từ ý nghĩa; chưa có thì hai dòng đầu + thể loại (không hứa điều trang không có). Bài có từ ngữ thô tục
	 * (D-111): mô tả trung tính, không trích lời hay ý nghĩa (mô tả này hiện trong ô xem trước khi chia sẻ link).
	 */
	public static function mo_ta_tac_pham( int $id ): string {
		$p = get_post( $id );
		if ( ! $p ) {
			return '';
		}
		if ( null !== PhanBai::lay( $id, 'tho_tuc' ) ) {
			$tl = DocBai::the_loai( $p );
			return ( $tl ? TheLoai::DS[ $tl ] : 'Bài' ) . ' có từ ngữ thô tục được truyền miệng trong đời sống. Mở trang để xem lời.';
		}
		// Câu đố: phần y_nghia là "Giải thích đáp án" — mô tả hiện ở Google, khung xem trước Zalo/Facebook nên lấy từ lời (D-114)
		$y = self::cau_do( $id ) ? '' : (string) ( PhanBai::lay( $id, 'y_nghia' )['chu'] ?? PhanBai::lay( $id, 'nghia_bong' )['chu'] ?? '' );
		if ( '' !== $y ) {
			return self::cat( $y );
		}
		$dong = array_values( array_filter( PhanBai::lay( $id, 'loi' )['dong'] ?? array(), 'strlen' ) );
		$tl   = DocBai::the_loai( $p );
		return self::cat( implode( ' / ', array_slice( $dong, 0, 2 ) ) . ( count( $dong ) > 2 ? '…' : '' ) . ( $tl ? ' — ' . TheLoai::DS[ $tl ] . '.' : '' ) );
	}

	private static function mo_ta(): string {
		if ( is_singular( KhaiBao::TAC_PHAM ) ) {
			return self::mo_ta_tac_pham( get_queried_object_id() );
		}
		if ( is_front_page() ) {
			return 'Thư viện Văn học Dân gian Việt Nam: ca dao, tục ngữ, đồng dao, vè, câu đố — lời giữ nguyên văn, ý nghĩa, chú thích từ khó, dị bản. Phi lợi nhuận, không quảng cáo.';
		}
		if ( is_singular( KhaiBao::TUYEN_TAP ) ) {
			return self::cat( (string) ( PhanBai::lay( get_queried_object_id(), 'loi_dan' )['chu'] ?? '' ) );
		}
		if ( is_tax() ) {
			$t   = get_queried_object();
			$dau = trim( (string) ( preg_split( '/(?<=[.!?])\s/u', trim( wp_strip_all_tags( (string) $t->description ) ), 2 )[0] ?? '' ) );
			return self::cat( '' !== $dau ? $dau : $t->name . ' — ' . (int) $t->count . ' tác phẩm.' );
		}
		if ( is_singular() ) {
			$p = get_queried_object();
			return self::cat( '' !== trim( $p->post_excerpt ) ? $p->post_excerpt : wp_trim_words( strip_shortcodes( $p->post_content ), 40, '' ) );
		}
		return '';
	}

	/** Dữ liệu có cấu trúc của tác phẩm (CreativeWork; isBasedOn chỉ khi đã đối chiếu sách). */
	public static function json_tac_pham( int $id ): array {
		$p   = get_post( $id );
		$tl  = $p ? DocBai::the_loai( $p ) : null;
		$loi = PhanBai::lay( $id, 'loi' );
		$cw  = array(
			'@type'      => 'CreativeWork',
			'name'       => $p ? $p->post_title : '',
			'url'        => (string) get_permalink( $id ),
			'inLanguage' => 'vi',
			'genre'      => $tl ? TheLoai::DS[ $tl ] : null,
			'text'       => implode( "\n", $loi['dong'] ?? array() ),
			'abstract'   => 'cau-do' === $tl ? null : ( (string) ( PhanBai::lay( $id, 'y_nghia' )['chu'] ?? '' ) ?: null ),
			'publisher'  => array( '@type' => 'Organization', 'name' => 'Thư viện Văn học Dân gian Việt Nam', 'url' => home_url( '/' ) ),
		);
		$hs = (int) ( $loi['doi_chieu']['ho_so'] ?? 0 );
		if ( $hs && get_post( $hs ) ) {
			$cw['isBasedOn'] = array( '@type' => 'Book', 'name' => get_the_title( $hs ) );
		}
		return array_filter( $cw, static fn( $v ) => null !== $v && '' !== $v );
	}

	private static function json_ld(): array {
		$vet = static function ( array $cap ): array {
			$ds = array( array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => home_url( '/' ) ) );
			foreach ( array_values( $cap ) as $i => [ $ten, $url ] ) {
				$ds[] = array_filter( array( '@type' => 'ListItem', 'position' => $i + 2, 'name' => $ten, 'item' => $url ) );
			}
			return array( '@type' => 'BreadcrumbList', 'itemListElement' => $ds );
		};
		$ra = array();
		if ( is_front_page() ) {
			$ra[] = array(
				'@type'           => 'WebSite',
				'name'            => get_bloginfo( 'name' ),
				'url'             => home_url( '/' ),
				'inLanguage'      => 'vi',
				'potentialAction' => array( '@type' => 'SearchAction', 'target' => home_url( '/tim/?q={q}' ), 'query-input' => 'required name=q' ),
			);
			$ra[] = array( '@type' => 'Organization', 'name' => 'Thư viện Văn học Dân gian Việt Nam', 'url' => home_url( '/' ) );
		} elseif ( is_singular( KhaiBao::TAC_PHAM ) ) {
			$id   = get_queried_object_id();
			$tl   = DocBai::the_loai( get_post( $id ) );
			$ra[] = self::json_tac_pham( $id );
			$ra[] = $vet( array_filter( array( $tl ? array( TheLoai::DS[ $tl ], (string) get_term_link( $tl, KhaiBao::THE_LOAI ) ) : null, array( get_the_title( $id ), '' ) ) ) );
		} elseif ( is_tax( 'muc_tu' ) ) {
			$t    = get_queried_object();
			$ra[] = array( '@type' => 'DefinedTerm', 'name' => $t->name, 'description' => self::cat( (string) $t->description, 300 ), 'url' => (string) get_term_link( $t ), 'inDefinedTermSet' => home_url( '/tu-dien/' ) );
			$ra[] = $vet( array( array( 'Từ điển', home_url( '/tu-dien/' ) ), array( $t->name, '' ) ) );
		} elseif ( is_singular( KhaiBao::TUYEN_TAP ) || is_tax() ) {
			$o    = get_queried_object();
			$ten  = $o instanceof \WP_Term ? $o->name : $o->post_title;
			$ra[] = array( '@type' => 'CollectionPage', 'name' => $ten, 'url' => $o instanceof \WP_Term ? (string) get_term_link( $o ) : (string) get_permalink( $o ), 'inLanguage' => 'vi' );
			$ra[] = $vet( array( array( $ten, '' ) ) );
		}
		return $ra ? array( '@context' => 'https://schema.org', '@graph' => $ra ) : array();
	}

	public static function dau_trang(): void {
		$mo_ta = self::mo_ta();
		$url   = is_singular() ? (string) get_permalink( get_queried_object() ) : ( is_tax() ? (string) get_term_link( get_queried_object() ) : ( is_front_page() ? home_url( '/' ) : '' ) );
		$the   = array(
			array( 'property', 'og:type', is_singular( KhaiBao::TAC_PHAM ) ? 'article' : 'website' ),
			array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
			array( 'property', 'og:locale', 'vi_VN' ),
			array( 'property', 'og:title', wp_get_document_title() ),
		);
		if ( '' !== $url ) {
			$the[] = array( 'property', 'og:url', $url );
		}
		if ( '' !== $mo_ta ) {
			array_unshift( $the, array( 'name', 'description', $mo_ta ) );
			$the[] = array( 'property', 'og:description', $mo_ta );
		}
		// Ảnh chia sẻ (Zalo, Facebook): ảnh lời của bài; trang khác ảnh chung. Máy không vẽ được thì không có thẻ
		$bai = is_singular( KhaiBao::TAC_PHAM ) ? get_queried_object() : null;
		$anh = $bai instanceof \WP_Post ? AnhChiaSe::og( $bai ) : ( is_singular() || is_404() ? null : AnhChiaSe::og_chung() );
		if ( $anh ) {
			array_push(
				$the,
				array( 'property', 'og:image', $anh['url'] ),
				array( 'property', 'og:image:width', (string) $anh['rong'] ),
				array( 'property', 'og:image:height', (string) $anh['cao'] ),
				array( 'property', 'og:image:alt', $bai instanceof \WP_Post && null === PhanBai::lay( $bai->ID, 'tho_tuc' ) ? 'Lời “' . $bai->post_title . '” — ' . get_bloginfo( 'name' ) : get_bloginfo( 'name' ) ),
				array( 'name', 'twitter:card', 'summary_large_image' )
			);
		}
		foreach ( $the as [ $k, $ten, $gia_tri ] ) {
			echo '<meta ' . esc_attr( $k ) . '="' . esc_attr( $ten ) . '" content="' . esc_attr( $gia_tri ) . '">' . "\n";
		}
		// Canonical của trang danh sách (bài, trang tĩnh: WordPress tự in): trang lọc trỏ về danh sách không lọc
		if ( ! is_singular() && ! is_404() && ( is_tax() || is_post_type_archive( array( KhaiBao::TAC_PHAM, KhaiBao::TUYEN_TAP ) ) ) ) {
			$goc   = is_tax() ? get_term_link( get_queried_object() ) : get_post_type_archive_link( (string) get_query_var( 'post_type' ) ?: KhaiBao::TAC_PHAM );
			$trang = (int) get_query_var( 'paged' );
			if ( $goc && ! is_wp_error( $goc ) ) {
				echo '<link rel="canonical" href="' . esc_url( $trang > 1 && ! self::dang_loc() ? trailingslashit( $goc ) . user_trailingslashit( 'page/' . $trang, 'paged' ) : $goc ) . '">' . "\n";
			}
		}
		$ld = self::json_ld();
		if ( $ld ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON đã mã hoá, JSON_HEX_TAG chặn </script>
		}
	}

	// ---------- Sơ đồ trang: chỉ trang được lập chỉ mục ----------

	public static function so_do_bai( array $args, string $loai ): array {
		if ( KhaiBao::TAC_PHAM === $loai ) {
			// Mọi tác phẩm đã đăng (D-106): WordPress chỉ lấy bài đã đăng; bỏ bài có mật khẩu như trang bài
			$args['post_status']  = 'publish';
			$args['has_password'] = false;
		} elseif ( KhaiBao::TUYEN_TAP === $loai ) {
			$args['post__in'] = array_map( static fn( \WP_Post $t ) => $t->ID, TuyenTap::cong_khai_ds() ) ?: array( 0 );
		} elseif ( 'page' === $loai ) {
			// Trang MVP cùng tên trang cố định không lập chỉ mục (Đã lưu, Viết cảm nhận, Tìm)
			$an = array_filter( array_map( static fn( string $ten ) => get_page_by_path( $ten )?->ID, array( 'da-luu', 'viet-cam-nhan', 'tim' ) ) );
			if ( $an ) {
				$args['post__not_in'] = array_values( $an );
			}
		}
		return $args;
	}

	public static function so_do_phan_loai( array $args, string $tax ): array {
		$ds              = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
		$args['include'] = array_map( static fn( \WP_Term $t ) => $t->term_id, array_filter( is_array( $ds ) ? $ds : array(), array( self::class, 'term_lap' ) ) ) ?: array( 0 );
		return $args;
	}
}
