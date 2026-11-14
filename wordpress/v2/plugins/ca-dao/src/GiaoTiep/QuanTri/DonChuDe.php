<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\HaTang\ChuyenHuong;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\GoiY;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Dọn chủ đề** (D-112, T-205): bảng mọi chủ đề, máy đề xuất cho từng dòng, người duyệt từng dòng. Chỉ mục **ngoài bộ
 * chủ đề của dự án** (MienDuLieu\GoiY::CHU_DE) mới gộp hoặc bỏ được ở đây; mục trong bộ (kể cả nhóm Lối hát, Loại câu đố,
 * Kinh nghiệm) giữ nguyên — đổi bộ chủ đề là việc riêng, máy gợi ý vẫn dùng bộ đó. Gộp / bỏ đi qua đường lưu phân loại của
 * từng bài (LuuPhanLoai: khoá bài, kiểm phiên, Lịch sử), xong mới xoá mục, ghi một dòng nhật ký cho cả mục và chuyển hướng 301
 * đường cũ (HaTang\ChuyenHuong). Máy không tự làm gì: mọi thay đổi chỉ chạy khi người có quyền bấm Đồng ý ở màn xem trước.
 */
final class DonChuDe {

	public const TRANG     = 'phongdao-don-chu-de';
	public const HANH_DONG = 'phongdao_don_chu_de';
	private const TAX      = 'chu_de';

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan' ) );
	}

	public static function url( array $them = array() ): string {
		return add_query_arg( array( 'page' => self::TRANG ) + $them, admin_url( 'admin.php' ) );
	}

	/** Gộp, bỏ mục: người có quyền duyệt và quyền quản lý bộ chủ đề. */
	public static function duoc( int $nguoi = 0 ): bool {
		$t = get_taxonomy( self::TAX );
		$u = $nguoi ?: get_current_user_id();
		return $t && $u && user_can( $u, 'cadao_duyet' ) && user_can( $u, $t->cap->manage_terms );
	}

	/** Nhóm trong bộ chủ đề dự án (GoiY::nhom_cua): '' chủ đề chung, mã nhóm, null = ngoài bộ. */
	public static function nhom( string $slug ): ?string {
		return GoiY::nhom_cua( $slug );
	}

	public static function ten_nhom( ?string $nhom ): string {
		return GoiY::ten_nhom( $nhom );
	}

	/** Khoá so tên: chữ thường, bỏ dấu, chỉ giữ chữ và số. */
	private static function khoa( string $s ): string {
		$s = class_exists( '\Normalizer' ) ? (string) \Normalizer::normalize( $s, \Normalizer::FORM_C ) : $s;
		return (string) preg_replace( '/[^a-z0-9]/', '', strtolower( remove_accents( str_replace( array( 'đ', 'Đ' ), 'd', mb_strtolower( $s ) ) ) ) );
	}

	/**
	 * Đề xuất của máy cho một chủ đề (chỉ đề xuất, không làm).
	 *
	 * @param \WP_Term[] $trong_bo các chủ đề thuộc bộ dự án
	 * @return array{0:string,1:int,2:string} [giu|gop|bo, mục đích khi gộp, lý do]
	 */
	public static function de_xuat( \WP_Term $t, int $so, array $trong_bo ): array {
		$nhom = self::nhom( $t->slug );
		if ( null !== $nhom ) {
			return array( 'giu', 0, $nhom === $t->slug ? 'Mục cha của nhóm “' . self::ten_nhom( $nhom ) . '” trong bộ chủ đề dự án.' : 'Thuộc nhóm “' . self::ten_nhom( $nhom ) . '” của bộ chủ đề dự án.' );
		}
		if ( 0 === $so ) {
			return array( 'bo', 0, 'Không còn tác phẩm đã đăng nào.' );
		}
		$k = self::khoa( $t->name );
		foreach ( $trong_bo as $b ) {
			$kb = self::khoa( $b->name );
			if ( '' !== $k && ( $k === $kb || ( min( strlen( $k ), strlen( $kb ) ) >= 4 && ( str_contains( $k, $kb ) || str_contains( $kb, $k ) ) ) ) ) {
				return array( 'gop', (int) $b->term_id, 'Gần trùng tên với chủ đề “' . $b->name . '” trong bộ.' );
			}
		}
		$ten = mb_strtolower( $t->name );
		foreach ( TheLoai::DS as $ten_tl ) {
			foreach ( array_map( 'trim', explode( '–', mb_strtolower( $ten_tl ) ) ) as $tu ) {
				if ( '' !== $tu && preg_match( '/(?<![\p{L}\p{M}])' . preg_quote( $tu, '/' ) . '(?![\p{L}\p{M}])/u', $ten ) ) {
					return array( 'bo', 0, 'Tên trùng thể loại “' . $ten_tl . '”: bài vẫn ở trang thể loại. Nếu tên còn nói một nội dung khác, có thể chọn Gộp vào một chủ đề trong bộ.' );
				}
			}
		}
		return array( 'giu', 0, 'Ngoài bộ chủ đề của dự án. Giữ, hoặc Gộp vào một chủ đề trong bộ nếu trùng ý.' );
	}

	/**
	 * Bảng mọi chủ đề: mục ngoài bộ lên trước (cần quyết), rồi theo nhóm của bộ, nhiều bài trước.
	 *
	 * @return list<array{term:\WP_Term,nhom:?string,so:int,de_xuat:string,dich:int,ly_do:string}>
	 */
	public static function bang(): array {
		$ds = get_terms( array( 'taxonomy' => self::TAX, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
		$ds = is_array( $ds ) ? array_values( array_filter( $ds, static fn( $t ): bool => $t instanceof \WP_Term ) ) : array();
		$bo = array_values( array_filter( $ds, static fn( \WP_Term $t ): bool => null !== self::nhom( $t->slug ) ) );
		$thu_tu = array_flip( array_map( 'strval', array_keys( GoiY::CHU_DE ) ) );
		$ra = array();
		foreach ( $ds as $t ) {
			$so               = \Phongdao\GiaoTiep\Seo\Module::dem_dang( $t );
			[ $dx, $dich, $ly ] = self::de_xuat( $t, $so, $bo );
			$ra[]             = array( 'term' => $t, 'nhom' => self::nhom( $t->slug ), 'so' => $so, 'de_xuat' => $dx, 'dich' => $dich, 'ly_do' => $ly );
		}
		usort(
			$ra,
			static function ( array $a, array $b ) use ( $thu_tu ): int {
				$na = null === $a['nhom'] ? -1 : (int) ( $thu_tu[ $a['nhom'] ] ?? 99 );
				$nb = null === $b['nhom'] ? -1 : (int) ( $thu_tu[ $b['nhom'] ] ?? 99 );
				return $na <=> $nb ?: $b['so'] <=> $a['so'] ?: strcmp( $a['term']->name, $b['term']->name );
			}
		);
		return $ra;
	}

	/**
	 * Gộp một chủ đề ngoài bộ vào chủ đề khác, hoặc bỏ hẳn. Mỗi bài đổi qua LuuPhanLoai (Lịch sử, kiểm phiên); bài nào chưa
	 * đổi được thì không xoá mục (chạy lại để làm nốt). Xong: xoá mục, chuyển hướng đường cũ, ghi nhật ký của mục.
	 *
	 * @return array{ok:bool,loi:string[],so:int}
	 */
	public static function lam( int $term_id, string $hanh_dong, int $dich, int $nguoi ): array {
		$hong = static fn( string ...$loi ): array => array( 'ok' => false, 'loi' => $loi, 'so' => 0 );
		if ( ! self::duoc( $nguoi ) ) {
			return $hong( 'Bạn không có quyền gộp hoặc bỏ chủ đề.' );
		}
		$t = get_term( $term_id, self::TAX );
		if ( ! $t instanceof \WP_Term ) {
			return $hong( 'Không tìm thấy chủ đề này (có thể đã được gộp hoặc bỏ).' );
		}
		if ( null !== self::nhom( $t->slug ) ) {
			return $hong( 'Chủ đề “' . $t->name . '” thuộc bộ chủ đề của dự án: giữ nguyên. Đổi bộ chủ đề là việc riêng, không làm ở đây.' );
		}
		if ( ! in_array( $hanh_dong, array( 'gop', 'bo' ), true ) ) {
			return $hong( 'Chọn Gộp hoặc Bỏ.' );
		}
		$d = null;
		if ( 'gop' === $hanh_dong ) {
			$d = get_term( $dich, self::TAX );
			if ( ! $d instanceof \WP_Term || (int) $d->term_id === (int) $t->term_id ) {
				return $hong( 'Chọn một chủ đề khác để gộp vào.' );
			}
		}
		$duong = self::duong( $t );
		$loi   = array();
		$so    = 0;
		foreach ( array_map( 'intval', (array) get_objects_in_term( $t->term_id, self::TAX ) ) as $bai ) {
			$p = get_post( $bai );
			if ( ! $p || KhaiBao::TAC_PHAM !== $p->post_type || 'trash' === $p->post_status ) {
				continue; // bài trong thùng rác: xoá mục là đủ
			}
			$hien = LuuPhanLoai::dang_gan( $bai, self::TAX );
			if ( null === $hien ) {
				$loi[] = '“' . $p->post_title . '”: chưa đọc được chủ đề của bài.';
				continue;
			}
			$chon = array_values( array_diff( $hien, array( (int) $t->term_id ) ) );
			if ( $d ) {
				$chon[] = (int) $d->term_id;
			}
			$kq = LuuPhanLoai::luu( $bai, self::TAX, $chon, $hien, $nguoi, 'gop' === $hanh_dong ? 'gop_chu_de' : 'bo_chu_de' );
			if ( empty( $kq['ok'] ) ) {
				$loi[] = '“' . $p->post_title . '”: ' . implode( ' ', (array) ( $kq['loi'] ?? array() ) );
				continue;
			}
			++$so;
		}
		if ( $loi ) {
			array_unshift( $loi, 'Chưa ' . ( 'gop' === $hanh_dong ? 'gộp' : 'bỏ' ) . ' “' . $t->name . '”: ' . count( $loi ) . ' bài chưa đổi được (' . $so . ' bài đã đổi). Bấm lại để làm nốt.' );
			return array( 'ok' => false, 'loi' => $loi, 'so' => $so );
		}
		$xoa = wp_delete_term( $t->term_id, self::TAX );
		if ( true !== $xoa ) {
			return array( 'ok' => false, 'loi' => array( 'Đã gỡ khỏi ' . $so . ' bài nhưng chưa xoá được mục “' . $t->name . '”. Thử lại.' ), 'so' => $so );
		}
		ChuyenHuong::ghi_phan_loai( $duong, self::TAX, (int) $t->term_id, $d ? (int) $d->term_id : 0 );
		$luc = gmdate( 'Y-m-d H:i:s' );
		Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'term',
				'doi_tuong_id'   => (int) $t->term_id,
				'phan'           => self::TAX,
				'nguoi_tao'      => (string) $nguoi,
				'tao_luc'        => $luc,
				'quyet'          => 'gop' === $hanh_dong ? 'gop_chu_de' : 'bo_chu_de',
				'truoc'          => array( 'ten' => $t->name, 'slug' => $t->slug, 'duong' => $duong, 'so_bai' => $so ),
				'sau'            => $d ? array( 'gop_vao' => (int) $d->term_id, 'ten' => $d->name ) : array( 'bo' => true ),
				'nguoi_quyet'    => $nguoi,
				'quyet_luc'      => $luc,
			)
		);
		return array( 'ok' => true, 'loi' => array(), 'so' => $so );
	}

	/** Đường dẫn công khai của chủ đề, dạng "/chu-de/<tên>/". */
	private static function duong( \WP_Term $t ): string {
		$l = get_term_link( $t );
		return '/' . trim( (string) wp_parse_url( is_string( $l ) ? $l : '', PHP_URL_PATH ), '/' ) . '/';
	}

	/** Nhận form Đồng ý ở màn xem trước. */
	public static function nhan(): void {
		$id = absint( $_POST['term'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dòng sau
		check_admin_referer( self::HANH_DONG . '_' . $id );
		$kq = self::lam( $id, sanitize_key( (string) ( $_POST['hanh_dong'] ?? '' ) ), absint( $_POST['dich'] ?? 0 ), get_current_user_id() );
		set_transient( 'phongdao_don_chu_de_' . get_current_user_id(), $kq['ok'] ? array( 'ok' => 'Đã xong: ' . $kq['so'] . ' bài đổi chủ đề, đường cũ chuyển hướng. Lịch sử từng bài có ghi lần đổi.' ) : array( 'loi' => $kq['loi'] ), 120 );
		wp_safe_redirect( self::url() );
		exit;
	}

	/** Lựa chọn "Gộp vào": chủ đề trong bộ (theo nhóm) rồi chủ đề khác, trừ chính mục. */
	private static function chon_dich( array $bang, \WP_Term $t, int $chon ): string {
		$nhom = array();
		foreach ( $bang as $h ) {
			if ( (int) $h['term']->term_id !== (int) $t->term_id ) {
				$nhom[ self::ten_nhom( $h['nhom'] ) ][] = $h['term'];
			}
		}
		$o = '';
		foreach ( $nhom as $ten => $ds ) {
			$o .= '<optgroup label="' . esc_attr( $ten ) . '">';
			foreach ( $ds as $d ) {
				$o .= '<option value="' . (int) $d->term_id . '"' . selected( $chon, (int) $d->term_id, false ) . '>' . esc_html( $d->name ) . '</option>';
			}
			$o .= '</optgroup>';
		}
		return $o;
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để chọn màn xem trước
		$xem = absint( $_GET['xem'] ?? 0 );
		$hd  = sanitize_key( (string) ( $_GET['hanh_dong'] ?? '' ) );
		$di  = absint( $_GET['dich'] ?? 0 );
		// phpcs:enable
		$bang = self::bang();
		if ( $xem ) {
			return self::xem_truoc( $bang, $xem, $hd, $di );
		}
		$bao = get_transient( 'phongdao_don_chu_de_' . get_current_user_id() );
		delete_transient( 'phongdao_don_chu_de_' . get_current_user_id() );
		$h = '<div class="wrap phongdao-ban phongdao-don-chu-de"><p><a href="' . esc_url( admin_url( 'admin.php?page=' . DanhMuc::TRANG ) ) . '">← Danh mục</a></p><h1>Dọn chủ đề</h1>'
			. ( is_array( $bao ) && ! empty( $bao['ok'] ) ? '<div class="notice notice-success"><p>' . esc_html( $bao['ok'] ) . '</p></div>' : '' )
			. ( is_array( $bao ) && ! empty( $bao['loi'] ) ? '<div class="notice notice-error" role="alert"><p>' . implode( '<br>', array_map( 'esc_html', (array) $bao['loi'] ) ) . '</p></div>' : '' )
			. '<p class="description">Máy đề xuất cho từng chủ đề; bạn duyệt từng dòng, bấm Xem trước rồi Đồng ý. Chỉ mục <strong>ngoài bộ chủ đề của dự án</strong> mới gộp hoặc bỏ được ở đây. Mục trong bộ (cả nhóm Lối hát, Loại câu đố, Kinh nghiệm) giữ nguyên: trang Chủ đề xếp chúng theo nhóm. Gộp, bỏ: mỗi bài ghi Lịch sử, đường cũ của mục chuyển hướng về chỗ mới.</p>';
		$hang = '';
		$can  = 0;
		foreach ( $bang as $r ) {
			$t    = $r['term'];
			$link = get_term_link( $t );
			$ten  = is_string( $link ) && $r['so'] ? '<a href="' . esc_url( $link ) . '">' . esc_html( $t->name ) . '</a>' : esc_html( $t->name );
			$dx   = array( 'giu' => 'Giữ', 'gop' => 'Gộp', 'bo' => 'Bỏ' )[ $r['de_xuat'] ];
			$lam  = '—';
			if ( null === $r['nhom'] ) {
				$can += 'giu' === $r['de_xuat'] ? 0 : 1;
				$ma   = 'dcd-' . (int) $t->term_id;
				$lam  = self::duoc()
					? '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '" class="phongdao-dcd-form"><input type="hidden" name="page" value="' . self::TRANG . '"><input type="hidden" name="xem" value="' . (int) $t->term_id . '">'
						. '<label for="' . $ma . '-hd">Việc</label><select id="' . $ma . '-hd" name="hanh_dong"><option value="gop"' . selected( 'gop' === $r['de_xuat'] || 'giu' === $r['de_xuat'], true, false ) . '>Gộp vào chủ đề khác</option><option value="bo"' . selected( 'bo' === $r['de_xuat'], true, false ) . '>Bỏ mục này</option></select>'
						. '<label for="' . $ma . '-dich">Gộp vào</label><select id="' . $ma . '-dich" name="dich">' . self::chon_dich( $bang, $t, $r['dich'] ) . '</select>'
						. '<button type="submit" class="button">Xem trước</button></form>'
					: 'Cần quyền duyệt';
			}
			// Nhãn ô chỉ hiện trên màn hẹp (bảng thành thẻ), để mỗi dòng tự đọc được
			$nh    = static fn( string $chu ): string => '<span class="phongdao-dcd-nhan">' . $chu . ': </span>';
			$hang .= '<tr><th scope="row">' . $ten . '</th><td>' . $nh( 'Nhóm' ) . esc_html( self::ten_nhom( $r['nhom'] ) ) . '</td><td>' . $nh( 'Bài đã đăng' ) . esc_html( number_format_i18n( $r['so'] ) ) . '</td><td>' . $nh( 'Máy đề xuất' ) . '<strong>' . esc_html( $dx ) . '</strong> · ' . esc_html( $r['ly_do'] ) . '</td><td>' . $lam . '</td></tr>';
		}
		return $h . '<p><strong>' . esc_html( number_format_i18n( $can ) ) . '</strong> mục ngoài bộ máy đề xuất gộp hoặc bỏ.</p>'
			. '<table class="widefat striped phongdao-dcd"><caption class="screen-reader-text">Mọi chủ đề, nhóm, số bài, đề xuất của máy và việc bạn chọn</caption><thead><tr><th scope="col">Chủ đề</th><th scope="col">Nhóm</th><th scope="col">Bài đã đăng</th><th scope="col">Máy đề xuất</th><th scope="col">Bạn quyết</th></tr></thead><tbody>' . $hang . '</tbody></table>'
			. '<p class="description">Máy chỉ đề xuất; không gì đổi khi bạn chưa bấm Đồng ý.</p></div>';
	}

	/** Màn xem trước một việc gộp / bỏ: bài bị đổi, đường cũ đi đâu, nút Đồng ý. */
	private static function xem_truoc( array $bang, int $id, string $hd, int $dich ): string {
		$ve = '<p><a href="' . esc_url( self::url() ) . '">← Dọn chủ đề</a></p>';
		$t  = get_term( $id, self::TAX );
		if ( ! $t instanceof \WP_Term || null !== self::nhom( $t->slug ) || ! in_array( $hd, array( 'gop', 'bo' ), true ) ) {
			return '<div class="wrap phongdao-ban phongdao-don-chu-de">' . $ve . '<h1>Dọn chủ đề</h1><p role="alert">Không xem trước được việc này: chủ đề không còn, thuộc bộ chủ đề dự án, hoặc chưa chọn Gộp / Bỏ.</p></div>';
		}
		$d    = 'gop' === $hd ? get_term( $dich, self::TAX ) : null;
		$bai  = array_values( array_filter( array_map( 'get_post', array_map( 'intval', (array) get_objects_in_term( $t->term_id, self::TAX ) ) ), static fn( $p ): bool => $p instanceof \WP_Post && KhaiBao::TAC_PHAM === $p->post_type && 'trash' !== $p->post_status ) );
		$dong = '';
		foreach ( array_slice( $bai, 0, 30 ) as $p ) {
			$dong .= '<tr><th scope="row"><a href="' . esc_url( SuaTacPham::url( $p->ID, self::url() ) ) . '">' . esc_html( $p->post_title ) . '</a></th><td>' . esc_html( get_post_status_object( $p->post_status )->label ?? $p->post_status ) . '</td></tr>';
		}
		$viec = $d instanceof \WP_Term && (int) $d->term_id !== (int) $t->term_id
			? 'Gộp “' . $t->name . '” vào “' . $d->name . '”'
			: ( 'bo' === $hd ? 'Bỏ chủ đề “' . $t->name . '”' : '' );
		if ( '' === $viec ) {
			return '<div class="wrap phongdao-ban phongdao-don-chu-de">' . $ve . '<h1>Dọn chủ đề</h1><p role="alert">Chọn một chủ đề khác để gộp vào.</p></div>';
		}
		$di = $d instanceof \WP_Term ? 'trang chủ đề “' . $d->name . '”' : 'trang Chủ đề';
		return '<div class="wrap phongdao-ban phongdao-don-chu-de">' . $ve . '<h1>Xem trước: ' . esc_html( $viec ) . '</h1>'
			. '<ul class="phongdao-nen-lam"><li>' . esc_html( number_format_i18n( count( $bai ) ) ) . ' tác phẩm đổi chủ đề' . ( $d instanceof \WP_Term ? ' (gắn thêm “' . esc_html( $d->name ) . '” nếu chưa có)' : ' (chỉ gỡ mục này; chủ đề khác của bài giữ nguyên)' ) . '. Mỗi bài ghi một dòng Lịch sử.</li>'
			. '<li>Đường cũ <code>' . esc_html( self::duong( $t ) ) . '</code> chuyển hướng 301 về ' . esc_html( $di ) . '.</li>'
			. '<li>Mục “' . esc_html( $t->name ) . '” bị xoá khỏi bộ phân loại; Lịch sử của bài không khôi phục lại được mục đã xoá.</li></ul>'
			. '<table class="widefat striped phongdao-dcd"><caption>' . esc_html( count( $bai ) > 30 ? '30 / ' . count( $bai ) . ' bài đầu' : 'Các bài bị đổi' ) . '</caption><thead><tr><th scope="col">Tác phẩm</th><th scope="col">Trạng thái</th></tr></thead><tbody>' . ( $dong ?: '<tr><td colspan="2">Không có bài nào đang gắn mục này.</td></tr>' ) . '</tbody></table>'
			. ( self::duoc()
				? '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::HANH_DONG . '"><input type="hidden" name="term" value="' . (int) $t->term_id . '"><input type="hidden" name="hanh_dong" value="' . esc_attr( $hd ) . '"><input type="hidden" name="dich" value="' . ( $d instanceof \WP_Term ? (int) $d->term_id : 0 ) . '">'
					. wp_nonce_field( self::HANH_DONG . '_' . (int) $t->term_id, '_wpnonce', true, false )
					. '<p class="phongdao-dcd-nut"><button type="submit" class="button button-primary">Đồng ý: ' . esc_html( $viec ) . '</button> <a class="button" href="' . esc_url( self::url() ) . '">Quay lại</a></p></form>'
				: '<p>Cần quyền duyệt để làm việc này.</p>' )
			. '</div>';
	}
}
