<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\Khoi\TuyenTap;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Màn **Tuyển tập** trong wp-admin (D-116, T-208): danh sách tuyển tập và màn soạn một bộ — tên, loại, người đọc dự định,
 * lời dẫn (người viết), các câu (thêm bằng đường dẫn / số ID / tên đúng hoặc tìm, sắp lên xuống, nhóm, ghi chú, bỏ khỏi bộ).
 * Dùng đúng ba phần của tuyển tập (thong_tin, loi_dan, muc) qua LuuPhan — mỗi lần lưu có Lịch sử; không thêm bảng hay loại
 * bài. Mọi nút nằm trong một form: bấm nút nào cũng lưu chữ đang có rồi mới làm việc của nút (chạy khi tắt JavaScript).
 * Người soạn lưu nháp; chỉ người có quyền duyệt bấm Đăng / Gỡ đăng. Máy không viết lời dẫn, không tự chọn câu, không tự đăng.
 */
final class SuaTuyenTap {

	public const TRANG     = 'phongdao-tuyen-tap';
	public const HANH_DONG = 'phongdao_luu_tuyen_tap';
	/** Số câu tối đa một bộ (tuyển tập 30 câu ≤ 300 KB, docs/27 mục 9.4; để dư cho bộ dài). */
	public const TOI_DA = 60;

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan' ) );
	}

	public static function url( array $them = array() ): string {
		return add_query_arg( array( 'page' => self::TRANG ) + $them, admin_url( 'admin.php' ) );
	}

	/** Dấu của bản đang mở (tên, trạng thái, ba phần) để không ghi đè khi bộ vừa được sửa ở nơi khác. */
	public static function phien( int $id ): string {
		$p = get_post( $id );
		return $p ? md5( (string) wp_json_encode( array( $p->post_title, $p->post_status, PhanBai::lay( $id, 'thong_tin' ), PhanBai::lay( $id, 'loi_dan' ), PhanBai::lay( $id, 'muc' ) ) ) ) : '';
	}

	/**
	 * Một dòng người dán thành ID tác phẩm: số ID, đường dẫn bài (/<thể loại>/<tên>/), hoặc đúng tên bài (chỉ khi một bài
	 * duy nhất mang tên đó). Không đoán: không chắc thì null.
	 */
	public static function tim_bai( string $dong ): ?int {
		$dong = trim( $dong );
		if ( '' === $dong ) {
			return null;
		}
		if ( ctype_digit( $dong ) ) {
			return KhaiBao::TAC_PHAM === get_post_type( (int) $dong ) ? (int) $dong : null;
		}
		if ( preg_match( '~^(https?://|/)~i', $dong ) ) {
			$duong = trim( (string) wp_parse_url( $dong, PHP_URL_PATH ), '/' );
			$phan  = '' === $duong ? array() : explode( '/', $duong );
			$ten   = sanitize_title( rawurldecode( (string) end( $phan ) ) );
			$ds    = '' === $ten ? array() : get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'name' => $ten, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'numberposts' => 5 ) );
			if ( count( $ds ) > 1 && count( $phan ) > 1 ) {
				$ds = array_values( array_filter( $ds, static fn( \WP_Post $p ): bool => DocBai::the_loai( $p ) === $phan[0] ) );
			}
			return 1 === count( $ds ) ? (int) $ds[0]->ID : null;
		}
		$ds = get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'title' => $dong, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'numberposts' => 2 ) );
		return 1 === count( $ds ) ? (int) $ds[0]->ID : null;
	}

	/** Tác phẩm đã đăng khớp chữ tìm (tên, lời), tối đa 20. @return \WP_Post[] */
	public static function tim( string $q ): array {
		$q = trim( $q );
		if ( '' === $q ) {
			return array();
		}
		return get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 's' => $q, 'numberposts' => 20 ) );
	}

	/** @return list<array{tac_pham_id:int,nhom:string,ghi_chu:string}> các mục đang lưu (kể cả bài chưa đăng) */
	public static function muc( int $id ): array {
		return array_values(
			array_map(
				static fn( array $m ): array => array( 'tac_pham_id' => (int) ( $m['tac_pham_id'] ?? 0 ), 'nhom' => (string) ( $m['nhom'] ?? '' ), 'ghi_chu' => (string) ( $m['ghi_chu'] ?? '' ) ),
				array_filter( (array) ( PhanBai::lay( $id, 'muc' )['muc'] ?? array() ), 'is_array' )
			)
		);
	}

	/**
	 * Lưu mọi ô của form rồi làm việc của nút ($vao['hanh']): luu · tim · them · them_nhanh:<ID> · len:<i> · xuong:<i> ·
	 * dang · go. Trả về ID bộ (bộ mới được tạo ở trạng thái nháp), lời báo, lỗi và dòng "Thêm bài" chưa tìm được (giữ lại
	 * để người sửa).
	 *
	 * @param array<string,mixed> $vao dữ liệu form (đã wp_unslash)
	 * @return array{ok:bool,id:int,loi:string[],bao:string[],con:string}
	 */
	public static function luu( int $id, array $vao, int $nguoi ): array {
		$kq   = array( 'ok' => false, 'id' => $id, 'loi' => array(), 'bao' => array(), 'con' => '' );
		$hanh = sanitize_text_field( (string) ( $vao['hanh'] ?? 'luu' ) );
		if ( ! user_can( $nguoi, 'cadao_soan' ) ) {
			$kq['loi'][] = 'Bạn không có quyền soạn tuyển tập.';
			return $kq;
		}
		if ( $id ) {
			$p = get_post( $id );
			if ( ! $p || KhaiBao::TUYEN_TAP !== $p->post_type || 'trash' === $p->post_status || ! user_can( $nguoi, 'edit_post', $id ) ) {
				$kq['loi'][] = 'Không tìm thấy tuyển tập này, hoặc bạn không được sửa.';
				return $kq;
			}
			if ( ! hash_equals( self::phien( $id ), (string) ( $vao['phien'] ?? '' ) ) ) {
				$kq['loi'][] = 'Tuyển tập vừa được sửa ở nơi khác nên chưa lưu. Chữ bạn vừa gõ vẫn còn ở dưới; xem bản mới rồi lưu lại.';
				$kq['giu']   = $vao;
				return $kq;
			}
		}
		$ten = trim( sanitize_text_field( (string) ( $vao['ten'] ?? '' ) ) );
		if ( '' === $ten || mb_strlen( $ten ) > 200 ) {
			$kq['loi'][] = 'Nhập tên tuyển tập (tối đa 200 ký tự).';
			$kq['giu']   = $vao;
			return $kq;
		}
		if ( ! $id ) {
			$id = (int) wp_insert_post( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'draft', 'post_title' => $ten, 'post_author' => $nguoi ), false );
			if ( ! $id ) {
				$kq['loi'][] = 'Chưa tạo được tuyển tập. Thử lại sau.';
				$kq['giu']   = $vao;
				return $kq;
			}
			$kq['id']    = $id;
			$kq['bao'][] = 'Đã tạo tuyển tập ở trạng thái nháp.';
		} elseif ( get_the_title( $id ) !== $ten ) {
			wp_update_post( array( 'ID' => $id, 'post_title' => $ten ) );
		}
		$nc = array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => user_can( $nguoi, 'cadao_duyet' ) );

		// Thông tin: loại, người đọc dự định
		$loai = sanitize_key( (string) ( $vao['loai'] ?? 'chu_de' ) );
		$doi  = trim( sanitize_text_field( (string) ( $vao['doi_tuong'] ?? '' ) ) );
		$r    = LuuPhan::luu( $id, 'thong_tin', array_filter( array( 'loai' => isset( TuyenTap::LOAI[ $loai ] ) ? $loai : 'chu_de', 'doi_tuong' => mb_substr( $doi, 0, 200 ) ), 'strlen' ), $nc );
		$kq['loi'] = array_merge( $kq['loi'], $r['ok'] ? array() : (array) $r['loi'] );

		// Lời dẫn: chữ người viết; để trống thì bỏ phần
		$dan = trim( str_replace( "\r\n", "\n", sanitize_textarea_field( (string) ( $vao['loi_dan'] ?? '' ) ) ) );
		$r   = LuuPhan::luu( $id, 'loi_dan', '' === $dan ? null : array( 'chu' => $dan, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc );
		$kq['loi'] = array_merge( $kq['loi'], $r['ok'] ? array() : (array) $r['loi'] );

		// Các câu theo thứ tự trên form; bỏ những câu đánh dấu Bỏ, bỏ trùng
		$muc = array();
		foreach ( array_values( (array) ( $vao['muc'] ?? array() ) ) as $m ) {
			$bid = absint( $m['id'] ?? 0 );
			if ( ! $bid || ! empty( $m['bo'] ) || isset( $muc[ $bid ] ) || KhaiBao::TAC_PHAM !== get_post_type( $bid ) ) {
				continue;
			}
			$muc[ $bid ] = array_filter( array( 'tac_pham_id' => $bid, 'nhom' => mb_substr( trim( sanitize_text_field( (string) ( $m['nhom'] ?? '' ) ) ), 0, 200 ), 'ghi_chu' => trim( sanitize_textarea_field( (string) ( $m['ghi_chu'] ?? '' ) ) ) ), static fn( $v ): bool => '' !== $v );
		}
		$muc = array_values( $muc );
		// Lên / xuống
		if ( preg_match( '/^(len|xuong):(\d+)$/', $hanh, $kh ) ) {
			$i = (int) $kh[2];
			$j = 'len' === $kh[1] ? $i - 1 : $i + 1;
			if ( isset( $muc[ $i ], $muc[ $j ] ) ) {
				[ $muc[ $i ], $muc[ $j ] ] = array( $muc[ $j ], $muc[ $i ] );
			}
		}
		// Thêm: dòng dán (đường dẫn / ID / tên đúng) và nút Thêm ở kết quả tìm
		$co    = array_flip( array_column( $muc, 'tac_pham_id' ) );
		$them  = preg_match( '/^them_nhanh:(\d+)$/', $hanh, $kn ) ? array( $kn[1] ) : array();
		$chua  = array();
		$dong  = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", '', (string) ( $vao['them'] ?? '' ) ) ) ), 'strlen' );
		foreach ( array_merge( $dong, $them ) as $d ) {
			$bid = self::tim_bai( (string) $d );
			if ( null === $bid ) {
				$chua[] = (string) $d;
				continue;
			}
			if ( isset( $co[ $bid ] ) ) {
				continue;
			}
			if ( count( $muc ) >= self::TOI_DA ) {
				$kq['loi'][] = 'Một tuyển tập có tối đa ' . self::TOI_DA . ' câu; các câu sau chưa được thêm.';
				break;
			}
			$muc[]       = array( 'tac_pham_id' => $bid );
			$co[ $bid ]  = true;
			$kq['bao'][] = 'Đã thêm “' . get_the_title( $bid ) . '”.';
		}
		if ( $chua ) {
			$kq['loi'][] = 'Không tìm thấy bài cho ' . count( $chua ) . ' dòng (dán đúng đường dẫn bài, số ID, hoặc tên bài đúng từng chữ): ' . implode( ' · ', array_map( static fn( string $s ): string => '“' . mb_substr( $s, 0, 80 ) . '”', $chua ) ) . '.';
			$kq['con']   = implode( "\n", $chua );
		}
		$r = LuuPhan::luu( $id, 'muc', $muc ?: null, $nc );
		$kq['loi'] = array_merge( $kq['loi'], $r['ok'] ? array() : (array) $r['loi'] );

		// Đăng / gỡ đăng: chỉ người có quyền duyệt, do người bấm
		if ( in_array( $hanh, array( 'dang', 'go' ), true ) ) {
			if ( ! user_can( $nguoi, 'cadao_duyet' ) ) {
				$kq['loi'][] = 'Đăng hay gỡ đăng tuyển tập cần quyền duyệt. Đã lưu nháp.';
			} else {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'dang' === $hanh ? 'publish' : 'draft' ) );
				$tt          = get_post( $id );
				$kq['bao'][] = 'dang' === $hanh
					? ( $tt && TuyenTap::cong_khai( $tt ) ? 'Đã đăng: người đọc thấy tuyển tập này ở trang Tuyển tập.' : 'Đã đăng, nhưng người đọc chưa thấy: ' . implode( '; ', self::thieu( $id ) ) . '.' )
					: 'Đã gỡ đăng: tuyển tập về nháp, người đọc không thấy.';
			}
		}
		if ( ! $kq['loi'] && ! $kq['bao'] ) {
			$kq['bao'][] = 'Đã lưu.';
		}
		$kq['ok'] = ! $kq['loi'];
		return $kq;
	}

	/** Điều còn thiếu để người đọc thấy bộ (rỗng = đủ). @return string[] */
	public static function thieu( int $id ): array {
		$tt = get_post( $id );
		if ( ! $tt ) {
			return array();
		}
		$ra = array();
		if ( 'publish' !== $tt->post_status ) {
			$ra[] = 'chưa đăng';
		}
		if ( '' === trim( (string) ( PhanBai::lay( $id, 'loi_dan' )['chu'] ?? '' ) ) ) {
			$ra[] = 'chưa có lời dẫn';
		}
		$can = TuyenTap::nguong( TuyenTap::loai( $tt ) );
		$co  = count( TuyenTap::muc( $tt ) );
		if ( $co < $can ) {
			$ra[] = 'mới có ' . $co . ' / ' . $can . ' câu đã đăng';
		}
		return $ra;
	}

	/** Nhận form, lưu, rồi về lại màn soạn (giữ chữ tìm, dòng chưa tìm được). */
	public static function nhan(): void {
		$id = absint( $_POST['id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dòng sau
		check_admin_referer( self::HANH_DONG . '_' . $id );
		$vao = wp_unslash( $_POST );
		$kq  = self::luu( $id, is_array( $vao ) ? $vao : array(), get_current_user_id() );
		set_transient( 'phongdao_tuyen_tap_' . get_current_user_id(), array( 'loi' => $kq['loi'], 'bao' => $kq['bao'], 'con' => $kq['con'], 'giu' => $kq['giu'] ?? null ), 300 );
		$hanh = (string) ( $vao['hanh'] ?? '' );
		$q    = 'tim' === $hanh || str_starts_with( $hanh, 'them_nhanh:' ) ? trim( sanitize_text_field( (string) ( $vao['q'] ?? '' ) ) ) : '';
		wp_safe_redirect( $kq['id'] ? self::url( array_filter( array( 'id' => $kq['id'], 'q' => $q ) ) ) . ( '' !== $q ? '#tim-bai' : '' ) : self::url( array( 'moi' => 1 ) ) );
		exit;
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để chọn màn
		$id  = absint( $_GET['id'] ?? 0 );
		$moi = ! empty( $_GET['moi'] );
		$q   = trim( sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ) );
		// phpcs:enable
		$bao = get_transient( 'phongdao_tuyen_tap_' . get_current_user_id() );
		delete_transient( 'phongdao_tuyen_tap_' . get_current_user_id() );
		$bao = is_array( $bao ) ? $bao : array();
		if ( $id || $moi ) {
			$tt = $id ? get_post( $id ) : null;
			if ( $id && ( ! $tt || KhaiBao::TUYEN_TAP !== $tt->post_type ) ) {
				return '<div class="wrap phongdao-ban phongdao-tuyen-tap"><p><a href="' . esc_url( self::url() ) . '">← Tuyển tập</a></p><h1>Tuyển tập</h1><p role="alert">Không tìm thấy tuyển tập này.</p></div>';
			}
			return self::form( $tt, $q, $bao );
		}
		return self::danh_sach( $bao );
	}

	private static function thong_bao( array $bao ): string {
		$o = '';
		if ( ! empty( $bao['bao'] ) ) {
			$o .= '<div class="notice notice-success"><p>' . implode( '<br>', array_map( 'esc_html', (array) $bao['bao'] ) ) . '</p></div>';
		}
		if ( ! empty( $bao['loi'] ) ) {
			$o .= '<div class="notice notice-error" role="alert"><p>' . implode( '<br>', array_map( 'esc_html', (array) $bao['loi'] ) ) . '</p></div>';
		}
		return $o;
	}

	private static function danh_sach( array $bao ): string {
		$ds   = get_posts( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'numberposts' => 200, 'orderby' => 'modified', 'order' => 'DESC' ) );
		$hang = '';
		foreach ( $ds as $tt ) {
			$thieu = self::thieu( $tt->ID );
			$tt_ch = $thieu ? ( 'publish' === $tt->post_status ? 'Đã đăng, người đọc chưa thấy: ' : 'Nháp: ' ) . implode( '; ', $thieu ) : 'Người đọc đang thấy';
			$hang .= '<tr><th scope="row"><a href="' . esc_url( self::url( array( 'id' => $tt->ID ) ) ) . '">' . esc_html( $tt->post_title ?: '(chưa có tên)' ) . '</a></th>'
				. '<td><span class="phongdao-dcd-nhan">Loại: </span>' . esc_html( TuyenTap::LOAI[ TuyenTap::loai( $tt ) ] ) . '</td>'
				. '<td><span class="phongdao-dcd-nhan">Câu đã đăng: </span>' . count( TuyenTap::muc( $tt ) ) . ' / ' . count( self::muc( $tt->ID ) ) . '</td>'
				. '<td><span class="phongdao-dcd-nhan">Trạng thái: </span>' . esc_html( $tt_ch ) . '</td>'
				. '<td><a class="button" href="' . esc_url( self::url( array( 'id' => $tt->ID ) ) ) . '">Sửa</a> <a class="button" href="' . esc_url( 'publish' === $tt->post_status ? (string) get_permalink( $tt ) : (string) get_preview_post_link( $tt ) ) . '">Xem</a></td></tr>';
		}
		return '<div class="wrap phongdao-ban phongdao-tuyen-tap"><h1>Tuyển tập</h1>' . self::thong_bao( $bao )
			. '<p class="description">Một tuyển tập gom nhiều câu theo một mạch (chủ đề, dịp, tình huống, bài học), có lời dẫn của bạn. Người đọc thấy bộ khi đã đăng, có lời dẫn và đủ ' . TuyenTap::nguong( 'chu_de' ) . ' câu đã đăng (loại Tình huống: ' . TuyenTap::nguong( 'tinh_huong' ) . '). Hướng dẫn: docs/huong-dan/lam-tuyen-tap.md.</p>'
			. '<p><a class="button button-primary phongdao-tt-nut" href="' . esc_url( self::url( array( 'moi' => 1 ) ) ) . '">+ Tuyển tập mới</a></p>'
			. ( $hang
				? '<table class="widefat striped phongdao-dcd"><caption class="screen-reader-text">Các tuyển tập, loại, số câu đã đăng trên tổng số câu, trạng thái</caption><thead><tr><th scope="col">Tên</th><th scope="col">Loại</th><th scope="col">Câu đã đăng</th><th scope="col">Trạng thái</th><th scope="col">Làm</th></tr></thead><tbody>' . $hang . '</tbody></table>'
				: '<table class="widefat phongdao-dcd"><caption class="screen-reader-text">Các tuyển tập</caption><tbody><tr><td>Chưa có tuyển tập nào. Bấm “+ Tuyển tập mới” để bắt đầu.</td></tr></tbody></table>' )
			. '</div>';
	}

	/** Màn soạn một tuyển tập (mới hoặc đang có). */
	private static function form( ?\WP_Post $tt, string $q, array $bao ): string {
		$id   = $tt ? (int) $tt->ID : 0;
		$giu  = is_array( $bao['giu'] ?? null ) ? $bao['giu'] : null; // chữ chưa lưu được (xung đột, thiếu tên)
		$tt_t = $id ? PhanBai::lay( $id, 'thong_tin' ) : null;
		$ten  = (string) ( $giu['ten'] ?? ( $tt ? $tt->post_title : '' ) );
		$loai = (string) ( $giu['loai'] ?? ( $tt_t['loai'] ?? 'chu_de' ) );
		$doi  = (string) ( $giu['doi_tuong'] ?? ( $tt_t['doi_tuong'] ?? '' ) );
		$dan  = (string) ( $giu['loi_dan'] ?? ( $id ? ( PhanBai::lay( $id, 'loi_dan' )['chu'] ?? '' ) : '' ) );
		$muc  = $id ? self::muc( $id ) : array();
		$dang = $tt && 'publish' === $tt->post_status;
		$tinh = 'tinh_huong' === $loai;

		$o_loai = '';
		foreach ( TuyenTap::LOAI as $ma => $nhan ) {
			$o_loai .= '<option value="' . esc_attr( $ma ) . '"' . selected( $loai, $ma, false ) . '>' . esc_html( $nhan ) . '</option>';
		}
		$dong = '';
		$n    = count( $muc );
		foreach ( $muc as $i => $m ) {
			$b    = get_post( $m['tac_pham_id'] );
			if ( ! $b ) {
				continue;
			}
			$nhan = array();
			if ( 'publish' !== $b->post_status ) {
				$nhan[] = 'Chưa đăng — người đọc không thấy câu này';
			}
			if ( null === PhanBai::lay( $b->ID, 'y_nghia' ) && null === PhanBai::lay( $b->ID, 'nghia_bong' ) ) {
				$nhan[] = 'Chưa có Ý nghĩa';
			}
			if ( null !== PhanBai::lay( $b->ID, 'co_nghi' ) ) {
				$nhan[] = 'Có cờ “Có thể không phải dân gian”';
			}
			if ( null !== PhanBai::lay( $b->ID, 'tho_tuc' ) ) {
				$nhan[] = 'Có từ ngữ thô tục';
			}
			$tl    = DocBai::the_loai( $b );
			$ma    = 'tt-muc-' . $i;
			$dong .= '<li class="phongdao-tt-muc"><div class="phongdao-tt-dau"><span class="phongdao-tt-so">' . sprintf( '%02d', $i + 1 ) . '</span> <a href="' . esc_url( (string) get_permalink( $b ) ) . '" target="_blank" rel="noopener">' . esc_html( $b->post_title ) . '</a>'
				. ( $tl ? ' <span class="description">· ' . esc_html( \Phongdao\MienDuLieu\TheLoai::DS[ $tl ] ) . '</span>' : '' ) . '</div>'
				. ( $nhan ? '<p class="description">' . esc_html( implode( ' · ', $nhan ) ) . '</p>' : '' )
				. '<input type="hidden" name="muc[' . $i . '][id]" value="' . (int) $b->ID . '">'
				. '<p><label for="' . $ma . '-nhom">Nhóm (tuỳ chọn)</label><input type="text" id="' . $ma . '-nhom" name="muc[' . $i . '][nhom]" value="' . esc_attr( $m['nhom'] ) . '" maxlength="200"></p>'
				. '<p><label for="' . $ma . '-gc">' . ( $tinh ? 'Hợp khi… (tuỳ chọn)' : 'Ghi chú (tuỳ chọn)' ) . '</label><input type="text" id="' . $ma . '-gc" name="muc[' . $i . '][ghi_chu]" value="' . esc_attr( $m['ghi_chu'] ) . '"></p>'
				. '<p class="phongdao-tt-hang">'
				. ( $i > 0 ? '<button type="submit" class="button" name="hanh" value="len:' . $i . '" aria-label="' . esc_attr( 'Đưa “' . $b->post_title . '” lên một bậc' ) . '">↑ Lên</button>' : '' )
				. ( $i < $n - 1 ? '<button type="submit" class="button" name="hanh" value="xuong:' . $i . '" aria-label="' . esc_attr( 'Đưa “' . $b->post_title . '” xuống một bậc' ) . '">↓ Xuống</button>' : '' )
				. '<label class="phongdao-tt-bo"><input type="checkbox" name="muc[' . $i . '][bo]" value="1"> Bỏ khỏi bộ khi lưu</label></p></li>';
		}

		// Kết quả tìm: mỗi bài một nút Thêm (nút cùng form, lưu chữ đang có rồi thêm)
		$kq_tim = '';
		if ( '' !== $q ) {
			$co = array_flip( array_column( $muc, 'tac_pham_id' ) );
			foreach ( self::tim( $q ) as $b ) {
				$tl      = DocBai::the_loai( $b );
				$kq_tim .= '<li>' . esc_html( $b->post_title ) . ( $tl ? ' <span class="description">· ' . esc_html( \Phongdao\MienDuLieu\TheLoai::DS[ $tl ] ) . ( null === PhanBai::lay( $b->ID, 'y_nghia' ) && null === PhanBai::lay( $b->ID, 'nghia_bong' ) ? ' · chưa có Ý nghĩa' : ' · có Ý nghĩa' ) . '</span>' : '' ) . ' '
					. ( isset( $co[ $b->ID ] ) ? '<span class="description">Đã có trong bộ</span>' : '<button type="submit" class="button" name="hanh" value="them_nhanh:' . (int) $b->ID . '" aria-label="' . esc_attr( 'Thêm “' . $b->post_title . '” vào bộ' ) . '">Thêm</button>' ) . '</li>';
			}
			$kq_tim = $kq_tim ? '<ul class="phongdao-tt-tim">' . $kq_tim . '</ul>' : '<p role="status">Không thấy bài đã đăng nào khớp “' . esc_html( $q ) . '”.</p>';
		}
		$thieu = $id ? self::thieu( $id ) : array( 'chưa lưu' );
		$xem   = $tt ? ( $dang ? (string) get_permalink( $tt ) : (string) get_preview_post_link( $tt ) ) : '';
		return '<div class="wrap phongdao-ban phongdao-tuyen-tap"><p><a href="' . esc_url( self::url() ) . '">← Tuyển tập</a></p><h1>' . ( $tt ? 'Sửa tuyển tập' : 'Tuyển tập mới' ) . '</h1>' . self::thong_bao( $bao )
			. ( $tt ? '<p class="phongdao-tt-trang-thai" role="status"><strong>' . esc_html( $thieu ? ( $dang ? 'Đã đăng, người đọc chưa thấy' : 'Nháp' ) : 'Người đọc đang thấy bộ này' ) . '</strong>' . ( $thieu ? ' · Còn thiếu: ' . esc_html( implode( '; ', array_diff( $thieu, array( 'chưa đăng' ) ) ) ?: 'bấm Lưu và đăng' ) : '' ) . ( '' !== $xem ? ' · <a href="' . esc_url( $xem ) . '" target="_blank" rel="noopener">Xem trang tuyển tập</a>' : '' ) . '</p>' : '' )
			. '<p class="description">Mọi nút trên màn này đều lưu chữ bạn đang gõ trước, rồi mới làm việc của nút. Người đọc thấy bộ khi đã đăng, có lời dẫn và đủ ' . TuyenTap::nguong( 'chu_de' ) . ' câu đã đăng (Tình huống: ' . TuyenTap::nguong( 'tinh_huong' ) . ').</p>'
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="phongdao-tt-form"><input type="hidden" name="action" value="' . self::HANH_DONG . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="phien" value="' . esc_attr( $id ? self::phien( $id ) : '' ) . '">'
			. wp_nonce_field( self::HANH_DONG . '_' . $id, '_wpnonce', true, false )
			. '<h2>1. Tên và loại</h2>'
			. '<p><label for="tt-ten">Tên tuyển tập</label><input type="text" id="tt-ten" name="ten" value="' . esc_attr( $ten ) . '" maxlength="200" required></p>'
			. '<p><label for="tt-loai">Loại</label><select id="tt-loai" name="loai">' . $o_loai . '</select></p>'
			. '<p><label for="tt-doi">Người đọc dự định (tuỳ chọn)</label><input type="text" id="tt-doi" name="doi_tuong" value="' . esc_attr( $doi ) . '" maxlength="200" aria-describedby="tt-doi-ghi"><span class="description" id="tt-doi-ghi">Ví dụ: học sinh tiểu học, giáo viên, gia đình.</span></p>'
			. '<h2>2. Lời dẫn</h2>'
			. '<p><label for="tt-dan">Lời dẫn (2–4 câu, bạn tự viết)</label><textarea id="tt-dan" name="loi_dan" rows="5" aria-describedby="tt-dan-ghi">' . esc_textarea( $dan ) . '</textarea><span class="description" id="tt-dan-ghi">Bộ này gom gì, đọc để làm gì. Để một dòng trống giữa hai đoạn.</span></p>'
			. '<h2>3. Các câu (' . $n . ')</h2>'
			. ( $dong ? '<ol class="phongdao-tt-ds">' . $dong . '</ol>' : '<p>Chưa có câu nào. Thêm ở bên dưới.</p>' )
			. '<h3 id="tim-bai">Thêm câu</h3>'
			. '<p><label for="tt-q">Tìm bài đã đăng (gõ vài chữ trong tên hoặc lời)</label><input type="search" id="tt-q" name="q" value="' . esc_attr( $q ) . '"> <button type="submit" class="button" name="hanh" value="tim">Tìm</button></p>'
			. $kq_tim
			. '<p><label for="tt-them">Hoặc dán: mỗi dòng một đường dẫn bài, số ID, hoặc tên bài đúng từng chữ</label><textarea id="tt-them" name="them" rows="3">' . esc_textarea( (string) ( $bao['con'] ?? '' ) ) . '</textarea> <button type="submit" class="button" name="hanh" value="them">Thêm vào bộ</button></p>'
			. '<p class="phongdao-tt-nut-luu"><button type="submit" class="button button-primary" name="hanh" value="luu">' . ( $tt ? 'Lưu' : 'Tạo tuyển tập (nháp)' ) . '</button>'
			. ( $tt && current_user_can( 'cadao_duyet' ) ? ( $dang ? ' <button type="submit" class="button" name="hanh" value="go">Gỡ đăng (về nháp)</button>' : ' <button type="submit" class="button" name="hanh" value="dang">Lưu và đăng</button>' ) : '' )
			. '</p></form></div>';
	}
}
