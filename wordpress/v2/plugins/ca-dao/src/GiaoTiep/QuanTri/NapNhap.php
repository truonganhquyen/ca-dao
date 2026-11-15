<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\TomTat\TomTat;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * **Nháp Ý nghĩa theo lô** ở màn Bổ sung (D-117, T-210). Hai bước, đều do người bấm:
 *
 * 1. **Tải lô**: tệp CSV gồm bài đã đăng chưa có Ý nghĩa, chưa có nháp chờ (ca dao, đồng dao, vè — tục ngữ dùng Nghĩa đen /
 *    Nghĩa bóng, câu đố dùng Giải thích đáp án nên không vào lô), bài đọc nhiều trước; có cột `y_nghia` để trống cho người
 *    hoặc AI soạn nháp ngoài site.
 * 2. **Nạp tệp nháp**: đọc cột `id` + `y_nghia`, màn xem trước ghi từng dòng sẽ tạo hay bỏ qua (lý do), bấm Tạo thì mỗi dòng
 *    hợp lệ thành một thẻ "Nháp ý nghĩa" trong Hộp việc (`nhap_y_nghia`, đánh dấu có hỗ trợ AI). Người sửa / Duyệt / Bỏ từng
 *    thẻ ở Hộp việc như mọi nháp; máy không ghi gì vào bài, không tự đăng, không đặt dấu duyệt.
 *
 * Lô nhỏ: tối đa TOI_DA nháp đang chờ cùng lúc — duyệt xong lô trước mới nạp được lô mới. Tệp tải về chứa lời và chú thích
 * của site: giữ ngoài repo công khai.
 */
final class NapNhap {

	public const TOI_DA     = 50;
	public const TAI        = 'phongdao_tai_lo_y_nghia';
	public const NAP        = 'phongdao_nap_nhap_y_nghia';
	public const TAO        = 'phongdao_tao_nhap_y_nghia';
	public const THE_LOAI   = array( 'ca-dao', 'dong-dao', 've' );
	/** Độ dài nháp hợp lệ (ký tự): đủ một câu, không quá vài câu (Ý nghĩa 2–3 câu, docs/27). */
	public const NGAN = 20;
	public const DAI  = 700;

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::TAI, array( self::class, 'tai' ) );
		add_action( 'admin_post_' . self::NAP, array( self::class, 'nap' ) );
		add_action( 'admin_post_' . self::TAO, array( self::class, 'tao' ) );
	}

	/** Số nháp ý nghĩa đang chờ (Chờ, Để sau) trong Hộp việc. */
	public static function dang_cho(): int {
		return Viec::dem( Viec::CHO, 'nhap_y_nghia' ) + Viec::dem( Viec::DE_SAU, 'nhap_y_nghia' );
	}

	/** Còn nạp được bao nhiêu nháp ngay lúc này. */
	public static function con_cho(): int {
		return max( 0, self::TOI_DA - self::dang_cho() );
	}

	/**
	 * Bài cho một lô: đã đăng, thể loại hỗ trợ, chưa có Ý nghĩa, chưa có nháp chờ, không có cờ nghi / thô tục; đọc nhiều 7
	 * ngày trước. @return int[]
	 */
	public static function chon( int $so, string $the_loai = '' ): array {
		global $wpdb;
		$so = max( 1, min( $so, self::TOI_DA ) );
		TomTat::xong();
		$tl   = in_array( $the_loai, self::THE_LOAI, true ) ? array( $the_loai ) : self::THE_LOAI;
		$in   = implode( ',', array_fill( 0, count( $tl ), '%s' ) );
		$ung  = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . TomTat::bang() . " WHERE trang_thai = 'publish' AND the_loai IN ($in) AND thieu LIKE %s AND co = 0 ORDER BY doc_7 DESC, post_id ASC LIMIT %d", array_merge( $tl, array( '%,y_nghia,%', $so * 4 ) ) ) ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng, dãy %s
		$ra   = array();
		foreach ( array_map( 'intval', $ung ) as $id ) {
			if ( null !== PhanBai::lay( $id, 'y_nghia' ) || null !== PhanBai::lay( $id, 'tho_tuc' ) || Viec::mo( 'post', $id, 'nhap_y_nghia' ) ) {
				continue;
			}
			$ra[] = $id;
			if ( count( $ra ) >= $so ) {
				break;
			}
		}
		return $ra;
	}

	/** Một dòng CSV của lô: id, tên, thể loại, đường dẫn, lời, từ khó, y_nghia (trống). @return string[] */
	public static function dong( int $id ): array {
		$loi = array_values( array_filter( array_map( 'trim', (array) ( PhanBai::lay( $id, 'loi' )['dong'] ?? array() ) ), 'strlen' ) );
		$tk  = array();
		foreach ( (array) ( PhanBai::lay( $id, 'chu_thich' )['muc'] ?? array() ) as $c ) {
			$tu = trim( (string) ( $c['tu'] ?? '' ) );
			if ( '' !== $tu ) {
				$tk[] = $tu . ': ' . trim( wp_strip_all_tags( (string) ( $c['loi_giai'] ?? '' ) ) );
			}
		}
		$p  = get_post( $id );
		$tl = $p ? (string) \Phongdao\DichVu\DungTrang\DocBai::the_loai( $p ) : '';
		return array( (string) $id, $p ? $p->post_title : '', $tl, (string) get_permalink( $id ), implode( ' / ', $loi ), implode( ' | ', $tk ), '' );
	}

	/** Tải CSV một lô (UTF-8 có BOM để Excel/Google Sheets mở đúng dấu). */
	public static function tai(): void {
		check_admin_referer( self::TAI );
		if ( ! current_user_can( 'cadao_soan' ) ) {
			wp_die( 'Bạn không có quyền tải lô.', 403 );
		}
		$so  = absint( $_POST['so'] ?? 30 );
		$tl  = sanitize_key( (string) wp_unslash( $_POST['the_loai'] ?? '' ) );
		$ids = self::chon( $so ?: 30, str_replace( '_', '-', $tl ) );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="lo-y-nghia-' . gmdate( 'Ymd-His' ) . '.csv"' );
		header( 'X-Robots-Tag: noindex' );
		$ra = fopen( 'php://output', 'w' );
		fwrite( $ra, "\xEF\xBB\xBF" );
		fputcsv( $ra, array( 'id', 'ten', 'the_loai', 'duong_dan', 'loi', 'tu_kho', 'y_nghia' ), ',', '"', '\\' );
		foreach ( $ids as $id ) {
			fputcsv( $ra, self::dong( $id ), ',', '"', '\\' );
		}
		fclose( $ra );
		exit;
	}

	/**
	 * Đọc tệp nháp (CSV có dòng đầu là tên cột; cần cột `id` và `y_nghia`, cột khác bỏ qua) và xét từng dòng.
	 *
	 * @return array{loi:string,dong:list<array{id:int,ten:string,chu:string,tao:bool,ly_do:string}>}
	 */
	public static function doc( string $noi_dung ): array {
		$noi_dung = (string) preg_replace( '/^\xEF\xBB\xBF/', '', str_replace( "\r\n", "\n", $noi_dung ) );
		$fh       = fopen( 'php://temp', 'r+' );
		fwrite( $fh, $noi_dung );
		rewind( $fh );
		$dau = fgetcsv( $fh, 0, ',', '"', '\\' );
		$dau = is_array( $dau ) ? array_map( static fn( $c ): string => strtolower( trim( (string) $c ) ), $dau ) : array();
		$cid = array_search( 'id', $dau, true );
		$cyn = array_search( 'y_nghia', $dau, true );
		if ( false === $cid || false === $cyn ) {
			fclose( $fh );
			return array( 'loi' => 'Tệp cần dòng đầu có cột "id" và cột "y_nghia" (đúng như tệp tải lô).', 'dong' => array() );
		}
		$ra   = array();
		$thay = array();
		$con  = self::con_cho();
		while ( false !== ( $h = fgetcsv( $fh, 0, ',', '"', '\\' ) ) ) {
			if ( ! is_array( $h ) || array( null ) === $h ) {
				continue;
			}
			if ( count( $ra ) >= 200 ) {
				break;
			}
			$id  = absint( $h[ $cid ] ?? 0 );
			$chu = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) ( $h[ $cyn ] ?? '' ) ) ) );
			$p   = $id ? get_post( $id ) : null;
			$ly  = '';
			if ( ! $p || KhaiBao::TAC_PHAM !== $p->post_type ) {
				$ly = 'không tìm thấy tác phẩm';
			} elseif ( 'publish' !== $p->post_status ) {
				$ly = 'bài chưa đăng';
			} elseif ( ! in_array( (string) \Phongdao\DichVu\DungTrang\DocBai::the_loai( $p ), self::THE_LOAI, true ) ) {
				$ly = 'thể loại này không dùng ô Ý nghĩa (tục ngữ: nghĩa đen / bóng; câu đố: giải thích đáp án)';
			} elseif ( null !== PhanBai::lay( $id, 'y_nghia' ) ) {
				$ly = 'bài đã có Ý nghĩa';
			} elseif ( Viec::mo( 'post', $id, 'nhap_y_nghia' ) ) {
				$ly = 'đã có nháp đang chờ trong Hộp việc';
			} elseif ( mb_strlen( $chu ) < self::NGAN ) {
				$ly = '' === $chu ? 'nháp trống' : 'nháp quá ngắn';
			} elseif ( mb_strlen( $chu ) > self::DAI ) {
				$ly = 'nháp quá dài (Ý nghĩa nên 2–3 câu)';
			} elseif ( isset( $thay[ $id ] ) ) {
				$ly = 'trùng dòng trên';
			} elseif ( $con <= 0 ) {
				$ly = 'đủ ' . self::TOI_DA . ' nháp đang chờ — duyệt bớt rồi nạp tiếp';
			}
			$tao = '' === $ly;
			if ( $tao ) {
				$thay[ $id ] = true;
				--$con;
			}
			$ra[] = array( 'id' => $id, 'ten' => $p ? $p->post_title : '', 'chu' => $chu, 'tao' => $tao, 'ly_do' => $ly );
		}
		fclose( $fh );
		return array( 'loi' => $ra ? '' : 'Tệp không có dòng nào.', 'dong' => $ra );
	}

	/** Nhận tệp tải lên, lưu kết quả đọc để xem trước. */
	public static function nap(): void {
		check_admin_referer( self::NAP );
		if ( ! current_user_can( 'cadao_duyet' ) ) {
			wp_die( 'Nạp nháp cần quyền duyệt.', 403 );
		}
		$tep = $_FILES['tep'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- kiểm kích thước, đọc như chữ
		$kq  = array( 'loi' => 'Chọn một tệp CSV.', 'dong' => array() );
		if ( is_array( $tep ) && UPLOAD_ERR_OK === ( $tep['error'] ?? -1 ) && is_uploaded_file( (string) $tep['tmp_name'] ) ) {
			$kq = (int) $tep['size'] > 1024 * 1024 ? array( 'loi' => 'Tệp lớn quá (tối đa 1 MB).', 'dong' => array() ) : self::doc( (string) file_get_contents( (string) $tep['tmp_name'] ) );
		}
		$ma = wp_generate_password( 12, false );
		set_transient( 'phongdao_nap_' . get_current_user_id() . '_' . $ma, $kq, 30 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( array( 'page' => BoSung::TRANG, 'nap' => $ma ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Tạo thẻ nháp cho các dòng hợp lệ (xét lại lúc bấm: bài có thể vừa có Ý nghĩa / nháp ở nơi khác).
	 *
	 * @param list<array{id:int,chu:string,tao:bool}> $dong
	 * @return array{tao:int,bo:int}
	 */
	public static function tao_the( array $dong, int $nguoi ): array {
		$lo  = gmdate( 'Y-m-d H:i' );
		$tao = 0;
		$bo  = 0;
		$con = self::con_cho();
		foreach ( $dong as $d ) {
			$id = (int) ( $d['id'] ?? 0 );
			if ( empty( $d['tao'] ) || $con <= 0 || null !== PhanBai::lay( $id, 'y_nghia' ) || Viec::mo( 'post', $id, 'nhap_y_nghia' ) || 'publish' !== get_post_status( $id ) ) {
				$bo += empty( $d['tao'] ) ? 0 : 1;
				continue;
			}
			$v = Viec::tao(
				array(
					'loai'           => 'nhap_y_nghia',
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'phan'           => 'y_nghia',
					'noi_dung'       => array( 'chu' => (string) $d['chu'], 'ai' => true, 'lo' => $lo, 'nap_boi' => $nguoi ),
					'nguoi_tao'      => 'may',
					'uu_tien'        => 35,
				)
			);
			if ( $v ) {
				++$tao;
				--$con;
			} else {
				++$bo;
			}
		}
		return array( 'tao' => $tao, 'bo' => $bo );
	}

	public static function tao(): void {
		$ma = sanitize_key( (string) wp_unslash( $_POST['ma'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dòng sau
		check_admin_referer( self::TAO . '_' . $ma );
		if ( ! current_user_can( 'cadao_duyet' ) ) {
			wp_die( 'Tạo nháp cần quyền duyệt.', 403 );
		}
		$khoa = 'phongdao_nap_' . get_current_user_id() . '_' . $ma;
		$kq   = get_transient( $khoa );
		delete_transient( $khoa );
		$r    = is_array( $kq ) ? self::tao_the( (array) ( $kq['dong'] ?? array() ), get_current_user_id() ) : array( 'tao' => 0, 'bo' => 0 );
		set_transient( 'phongdao_nap_bao_' . get_current_user_id(), is_array( $kq ) ? 'Đã tạo ' . $r['tao'] . ' thẻ nháp ý nghĩa trong Hộp việc' . ( $r['bo'] ? ' (' . $r['bo'] . ' dòng bỏ qua vì vừa thay đổi)' : '' ) . '. Mở Hộp việc để sửa, Duyệt hoặc Bỏ từng thẻ.' : 'Phiên xem trước đã hết hạn; nạp lại tệp.', 120 );
		wp_safe_redirect( add_query_arg( array( 'page' => BoSung::TRANG ), admin_url( 'admin.php' ) ) . '#nhap-theo-lo' );
		exit;
	}

	/** Khối "Nháp Ý nghĩa theo lô" ở màn Bổ sung. */
	public static function khoi(): string {
		$bao = get_transient( 'phongdao_nap_bao_' . get_current_user_id() );
		delete_transient( 'phongdao_nap_bao_' . get_current_user_id() );
		$cho = self::dang_cho();
		$con = self::con_cho();
		$o   = '<h2 id="nhap-theo-lo">Nháp Ý nghĩa theo lô (có hỗ trợ AI)</h2>'
			. ( is_string( $bao ) && '' !== $bao ? '<div class="notice notice-success"><p>' . esc_html( $bao ) . '</p></div>' : '' )
			. '<p class="description">Để có nhiều bài có Ý nghĩa hơn mà không phải viết từ đầu: (1) tải một lô bài đọc nhiều chưa có Ý nghĩa; (2) soạn nháp ở cột <code>y_nghia</code> (có thể nhờ AI, mỗi bài 2–3 câu, không bịa điển tích, nơi chốn, năm); (3) nạp tệp — mỗi dòng thành một thẻ nháp trong Hộp việc, đánh dấu có hỗ trợ AI. Bạn sửa, Duyệt hoặc Bỏ từng thẻ; máy không ghi gì vào bài. Tệp có lời và chú thích của site: giữ trên máy, không đưa vào kho mã. Ca dao, đồng dao, vè (tục ngữ, câu đố có ô nghĩa riêng).</p>'
			. '<p role="status"><strong>' . esc_html( number_format_i18n( $cho ) ) . '</strong> nháp ý nghĩa đang chờ trong Hộp việc · nạp thêm được <strong>' . esc_html( number_format_i18n( $con ) ) . '</strong> (tối đa ' . self::TOI_DA . ' nháp chờ cùng lúc).'
			. ( $cho ? ' <a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'loai' => 'nhap_y_nghia' ), admin_url( 'admin.php' ) ) ) . '">Mở các nháp đang chờ</a>' : '' ) . '</p>';
		$tl = '<option value="">Ca dao, đồng dao, vè</option>';
		foreach ( self::THE_LOAI as $ma ) {
			$tl .= '<option value="' . esc_attr( $ma ) . '">' . esc_html( \Phongdao\MienDuLieu\TheLoai::DS[ $ma ] ) . '</option>';
		}
		$o .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="phongdao-nap-form"><input type="hidden" name="action" value="' . self::TAI . '">' . wp_nonce_field( self::TAI, '_wpnonce', true, false )
			. '<h3>1. Tải lô</h3><p><label for="nap-so">Số bài</label> <input type="number" id="nap-so" name="so" min="1" max="' . self::TOI_DA . '" value="' . min( 30, self::TOI_DA ) . '"> <label for="nap-tl">Thể loại</label> <select id="nap-tl" name="the_loai">' . $tl . '</select> <button type="submit" class="button">Tải tệp lô (CSV)</button></p></form>';
		if ( current_user_can( 'cadao_duyet' ) ) {
			$o .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data" class="phongdao-nap-form"><input type="hidden" name="action" value="' . self::NAP . '">' . wp_nonce_field( self::NAP, '_wpnonce', true, false )
				. '<h3>2. Nạp tệp nháp</h3><p><label for="nap-tep">Tệp CSV đã điền cột y_nghia</label> <input type="file" id="nap-tep" name="tep" accept=".csv,text/csv" required> <button type="submit" class="button"' . ( $con ? '' : ' disabled' ) . '>Xem trước</button></p>'
				. ( $con ? '' : '<p class="description">Đang đủ ' . self::TOI_DA . ' nháp chờ: duyệt bớt ở Hộp việc rồi nạp lô mới.</p>' ) . '</form>';
		}
		return $o;
	}

	/** Màn xem trước một tệp nháp đã nạp. */
	public static function xem_truoc( string $ma ): string {
		$kq = get_transient( 'phongdao_nap_' . get_current_user_id() . '_' . $ma );
		$ve = '<p><a href="' . esc_url( add_query_arg( array( 'page' => BoSung::TRANG ), admin_url( 'admin.php' ) ) . '#nhap-theo-lo' ) . '">← Bổ sung</a></p>';
		if ( ! is_array( $kq ) ) {
			return '<div class="wrap phongdao-ban phongdao-bo-sung">' . $ve . '<h1>Xem trước nháp Ý nghĩa</h1><p role="alert">Phiên xem trước đã hết hạn hoặc không có. Nạp lại tệp.</p></div>';
		}
		$dong = (array) ( $kq['dong'] ?? array() );
		$so   = count( array_filter( $dong, static fn( array $d ): bool => ! empty( $d['tao'] ) ) );
		$hang = '';
		foreach ( $dong as $d ) {
			$loi   = array_slice( array_values( array_filter( (array) ( PhanBai::lay( (int) $d['id'], 'loi' )['dong'] ?? array() ), 'strlen' ) ), 0, 2 );
			$hang .= '<tr><th scope="row">' . ( '' !== $d['ten'] ? '<a href="' . esc_url( (string) get_permalink( (int) $d['id'] ) ) . '" target="_blank" rel="noopener">' . esc_html( $d['ten'] ) . '</a>' : '#' . (int) $d['id'] ) . '<br><span class="description">' . esc_html( implode( ' / ', $loi ) ) . '</span></th>'
				. '<td>' . esc_html( $d['chu'] ) . '</td><td>' . ( $d['tao'] ? '<strong>Tạo thẻ nháp</strong>' : esc_html( 'Bỏ qua: ' . $d['ly_do'] ) ) . '</td></tr>';
		}
		return '<div class="wrap phongdao-ban phongdao-bo-sung">' . $ve . '<h1>Xem trước nháp Ý nghĩa</h1>'
			. ( '' !== (string) ( $kq['loi'] ?? '' ) ? '<div class="notice notice-error" role="alert"><p>' . esc_html( (string) $kq['loi'] ) . '</p></div>' : '' )
			. '<p>' . esc_html( $so . ' / ' . count( $dong ) ) . ' dòng sẽ thành thẻ nháp trong Hộp việc (đánh dấu có hỗ trợ AI). Chưa có gì ghi vào bài; bạn vẫn sửa, Duyệt hoặc Bỏ từng thẻ.</p>'
			. ( $dong ? '<table class="widefat striped"><caption class="screen-reader-text">Các dòng của tệp nháp: bài, nháp, việc sẽ làm</caption><thead><tr><th scope="col">Bài</th><th scope="col">Nháp Ý nghĩa</th><th scope="col">Sẽ làm</th></tr></thead><tbody>' . $hang . '</tbody></table>' : '' )
			. ( $so && current_user_can( 'cadao_duyet' ) ? '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . self::TAO . '"><input type="hidden" name="ma" value="' . esc_attr( $ma ) . '">' . wp_nonce_field( self::TAO . '_' . $ma, '_wpnonce', true, false ) . '<p><button type="submit" class="button button-primary">Tạo ' . (int) $so . ' thẻ nháp</button> <a class="button" href="' . esc_url( add_query_arg( array( 'page' => BoSung::TRANG ), admin_url( 'admin.php' ) ) . '#nhap-theo-lo' ) . '">Huỷ</a></p></form>' : '' )
			. '</div>';
	}
}
