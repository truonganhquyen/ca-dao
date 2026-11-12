<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\NguoiDoc\NhanGui;
use Phongdao\MienDuLieu\GopNguoiDoc;

defined( 'ABSPATH' ) || exit;

/**
 * Tầng "Hiểu thêm" của trang bài (D-105, docs/32 mục 4.1, 4.8): một khối ba phần — Cách hiểu của bạn đọc · Bài cảm
 * nhận · Cảm nghĩ — mỗi phần có số và nút viết của loại đó. Tắt JavaScript: ba phần nằm liền nhau, mỗi phần một tiêu
 * đề; bật JavaScript (doc.js) thành ba thẻ, mở sẵn phần có nhiều bài nhất.
 *
 * Cách hiểu của bạn đọc: form nhanh ngay trên trang (một ô chữ, bút danh tuỳ chọn, cam kết tự viết, đồng ý CC BY 4.0,
 * chống rác như trang Góp); vào Hộp việc, chỉ hiện sau khi người có quyền bấm Hiện; ghi bút danh và ngày, tách rõ với
 * Ý nghĩa của ban biên tập. Không thu tên thật, email, số điện thoại.
 */
final class HieuThem {

	/** Ba phần: mã => [tiêu đề, id]. */
	public const PHAN = array(
		'cach_hieu' => array( 'Cách hiểu của bạn đọc', 'cach-hieu' ),
		'cam_nhan'  => array( 'Bài cảm nhận', 'cam-nhan' ),
		'cam_nghi'  => array( 'Cảm nghĩ', 'cam-nghi' ),
	);

	/**
	 * @param array{gui?:string,ma?:string} $bao lời báo sau khi gửi cách hiểu (đã gửi / mã lỗi)
	 */
	public static function hien( int $bai_id, array $tp, array $bao = array() ): string {
		$goc  = Ve::url( 'gop' );
		$viet = static fn( string $loai, string $chu, string $icon ): string => '<p class="ht-viet"><a class="btn" href="' . esc_url( add_query_arg( array( 'loai' => $loai, 'bai' => $bai_id ), $goc ) . '#gop-form' ) . '">' . Ve::icon( $icon ) . esc_html( $chu ) . '</a></p>';
		$vi   = static fn( int $id ): string => '<a href="' . esc_url( add_query_arg( array( 'loai' => 'vi_pham', 'bai' => $bai_id, 'vi_pham' => $id ), $goc ) . '#gop-form' ) . '">Báo vi phạm</a>';

		$ch = (array) ( $tp['cach_hieu'] ?? array() );
		$pt = PhanTich::cua_bai( $bai_id );
		$cn = (array) ( $tp['cam_nghi'] ?? array() );
		$so = array( 'cach_hieu' => count( $ch ), 'cam_nhan' => count( $pt ), 'cam_nghi' => count( $cn ) );

		// Cách hiểu của bạn đọc
		$o = '<p class="small muted">Bạn đọc viết theo cách mình hiểu — không phải giải nghĩa của ban biên tập.</p>';
		foreach ( $ch as $c ) {
			$o .= '<article class="card ht-muc"><p>' . nl2br( esc_html( $c['chu'] ) ) . '</p><p class="small muted">' . esc_html( $c['ten'] . ' · ' . $c['ngay'] ) . ' · ' . $vi( (int) $c['id'] ) . '</p></article>';
		}
		if ( ! $ch ) {
			$o .= '<p class="small">Chưa có cách hiểu nào được hiện.</p>';
		}
		$phan = array( 'cach_hieu' => $o . self::form( $bai_id, $bao ) );

		// Bài cảm nhận: tên, hai dòng đầu, người viết; bài dài đọc ở trang riêng
		$o = '';
		foreach ( $pt as $p ) {
			$dau = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( str_replace( array( '</p>', '<br>' ), "\n", (string) $p->post_content ) ) ) );
			$o  .= '<article class="ht-muc ht-bai"><h4><a href="' . esc_url( (string) get_permalink( $p ) ) . '">' . esc_html( $p->post_title ) . '</a></h4>'
				. ( '' !== $dau ? '<p class="ht-tom">' . esc_html( mb_strlen( $dau ) > 220 ? mb_substr( $dau, 0, 219 ) . '…' : $dau ) . '</p>' : '' )
				. '<p class="small muted">' . esc_html( PhanTich::nguoi_viet( $p ) ) . '</p></article>';
		}
		$phan['cam_nhan'] = ( $o ?: '<p class="small">Chưa có bài cảm nhận nào về bài này.</p>' ) . $viet( 'phan_tich', 'Viết cảm nhận', 'pen' );

		// Cảm nghĩ của bạn đọc
		$o = '';
		foreach ( $cn as $c ) {
			$o .= '<article class="card ht-muc"><p>' . nl2br( esc_html( $c['chu'] ) ) . '</p><p class="small muted">' . esc_html( $c['ten'] . ' · ' . $c['ngay'] ) . ' · ' . $vi( (int) $c['id'] ) . '</p></article>';
		}
		$phan['cam_nghi'] = ( $o ?: '<p class="small">Chưa có cảm nghĩ nào.</p>' ) . $viet( 'cam_nghi', 'Viết cảm nghĩ', 'heart' );

		// Mở sẵn phần nhiều bài nhất (bằng nhau: Cách hiểu trước); vừa gửi cách hiểu thì mở phần đó
		$mo = (int) array_search( max( $so ), array_values( $so ), true );
		if ( ! empty( $bao['gui'] ) ) {
			$mo = 0;
		}
		$nut = '';
		$than = '';
		$i    = 0;
		foreach ( self::PHAN as $ma => [ $ten, $id ] ) {
			$nhan  = esc_html( $ten ) . ' <span class="ht-so">' . (int) $so[ $ma ] . '</span>';
			$nut  .= '<button type="button" role="tab" id="ht-nut-' . $id . '" aria-controls="' . $id . '" aria-selected="' . ( $i === $mo ? 'true' : 'false' ) . '"' . ( $i === $mo ? '' : ' tabindex="-1"' ) . '>' . $nhan . '</button>';
			$than .= '<div class="ht-phan" id="' . $id . '"><h3 class="ht-ten">' . $nhan . '</h3>' . $phan[ $ma ] . '</div>';
			++$i;
		}
		return '<section class="tang" id="hieu-them" aria-labelledby="hieu-them-tieu-de" data-hieu-them data-mo="' . $mo . '"><h2 class="tang-dau" id="hieu-them-tieu-de">Hiểu thêm</h2>'
			. '<div class="ht-nut" role="tablist" aria-label="Hiểu thêm" data-chi-js hidden>' . $nut . '</div>' . $than . '</section>';
	}

	/** Form nhanh "Chia sẻ cách hiểu": gửi admin-post như trang Góp (nonce, ô bẫy, Turnstile khi có khoá, 5 lần mỗi giờ). */
	public static function form( int $bai_id, array $bao = array() ): string {
		$gui = (string) ( $bao['gui'] ?? '' );
		$h   = '';
		if ( 'da_gui' === $gui ) {
			$h .= '<div class="notice success" role="status"><strong>Cảm ơn bạn!</strong> Cách hiểu đã tới người biên soạn; chỉ hiện trên trang sau khi được duyệt.</div>';
		} elseif ( 'loi' === $gui ) {
			$h .= '<div class="notice" role="alert">' . esc_html( GopNguoiDoc::BAO[ (string) ( $bao['ma'] ?? '' ) ] ?? GopNguoiDoc::BAO['loi'] ) . '</div>';
		}
		$ts = NhanGui::turnstile()['site'];
		return $h . '<form id="cach-hieu-form" class="form-gop form-cach-hieu" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" aria-labelledby="cach-hieu-form-tieu-de">'
			. '<h4 id="cach-hieu-form-tieu-de">Chia sẻ cách hiểu của bạn</h4>'
			. '<input type="hidden" name="action" value="' . NhanGui::HANH_DONG . '">' . wp_nonce_field( NhanGui::HANH_DONG, '_wpnonce', true, false )
			. '<input type="hidden" name="loai" value="cach_hieu"><input type="hidden" name="bai" value="' . $bai_id . '">'
			. '<label for="cach-hieu-chu">Bạn hiểu câu này thế nào?</label><textarea id="cach-hieu-chu" name="noi_dung" rows="4" maxlength="' . GopNguoiDoc::DAI_CACH_HIEU . '" required></textarea>'
			. '<p class="small muted">Vài dòng theo cách bạn hiểu, tối đa ' . number_format( GopNguoiDoc::DAI_CACH_HIEU, 0, ',', '.' ) . ' ký tự. Không đưa thông tin cá nhân.</p>'
			. '<label for="cach-hieu-ten">Bút danh (không bắt buộc)</label><input id="cach-hieu-ten" name="ten" maxlength="80" autocomplete="off">'
			. '<label class="choice"><input type="checkbox" name="tu_viet" value="1" required> Tôi tự viết những dòng này <span aria-hidden="true">*</span></label>'
			. '<label class="choice"><input type="checkbox" name="dong_y" value="1" required> Tôi đồng ý để thư viện dùng theo giấy phép CC BY 4.0 <span aria-hidden="true">*</span></label>'
			// Ô bẫy: người không thấy, máy tự động hay điền
			. '<p style="position:absolute;left:-9999px" aria-hidden="true"><label>Đừng điền ô này <input type="text" name="web" tabindex="-1" autocomplete="off"></label></p>'
			. ( '' !== $ts ? '<div class="cf-turnstile" data-sitekey="' . esc_attr( $ts ) . '" data-language="vi"></div>' : '' )
			. '<p class="small muted">Không cần tên thật, email hay số điện thoại. Người biên soạn đọc trước; chỉ hiện trên trang sau khi được duyệt. Thư viện không lưu địa chỉ mạng của bạn.</p>'
			. '<div class="actions"><button type="submit" class="btn primary">Gửi cách hiểu</button></div></form>';
	}
}
