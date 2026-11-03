<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\NguoiDoc\NhanGui;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\GopNguoiDoc;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Góp tư liệu /gop/ (bộ thiết kế R22) và khối "Bạn muốn góp gì?" cuối trang bài. Một form cho mọi loại góp,
 * chạy không cần JavaScript (chọn loại bằng nút radio; ô riêng của từng loại nằm trong nhóm có tên rõ).
 */
final class GopTuLieu {

	/** Sáu lối góp cuối trang bài: loại => [chữ, biểu tượng]. */
	private const LOI = array(
		'di_ban'    => array( 'Ở quê nói khác', 'map' ),
		'chu_thich' => array( 'Góp ý chú thích', 'edit' ),
		'bao_loi'   => array( 'Báo lỗi', 'info' ),
		'so_trang'  => array( 'Gửi số trang', 'book' ),
		'cam_nghi'  => array( 'Cảm nghĩ', 'heart' ),
		'phan_tich' => array( 'Viết cảm nhận', 'pen' ),
	);

	/**
	 * @param bool $dau in tiêu đề trang (false: form nằm trong trang tĩnh "Viết cảm nhận" mang từ MVP)
	 */
	public static function hien( string $loai, int $bai_id, string $gui, string $ma, int $vi_pham, bool $dau = true ): string {
		$bai  = $bai_id ? get_post( $bai_id ) : null;
		$bai  = $bai && KhaiBao::TAC_PHAM === $bai->post_type && 'publish' === $bai->post_status ? $bai : null;
		$loai = isset( GopNguoiDoc::LOAI[ $loai ] ) ? $loai : '';
		$h    = '';
		if ( $dau ) {
			$h .= '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Góp tư liệu</span></nav>'
				. '<p class="eyebrow">Cùng làm đầy trang sách</p><h1>Bạn muốn góp gì?</h1><p class="muted">Không cần tên thật, email hay số điện thoại. Nội dung gửi sẽ chờ người biên soạn xem.</p>';
		}
		if ( 'da_gui' === $gui ) {
			$h .= '<div class="notice success" role="status"><strong>Cảm ơn bạn!</strong> Nội dung đã tới người biên soạn; chỉ hiện trên trang sau khi được duyệt.</div>';
		} elseif ( 'loi' === $gui ) {
			$h .= '<div class="notice" role="alert">' . esc_html( GopNguoiDoc::BAO[ $ma ] ?? GopNguoiDoc::BAO['loi'] ) . '</div>';
		}
		if ( $bai ) {
			$h .= '<p class="small">Góp cho bài: <a href="' . esc_url( (string) get_permalink( $bai ) ) . '">' . esc_html( $bai->post_title ) . '</a></p>';
		}
		$o    = static fn( string $id, string $ten, string $nhan, int $dai = 200 ): string => '<label for="' . $id . '">' . $nhan . '</label><input id="' . $id . '" name="' . $ten . '" maxlength="' . $dai . '">';
		$ts   = NhanGui::turnstile()['site'];
		$h   .= '<form id="gop-form" class="form-gop" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
			. '<input type="hidden" name="action" value="' . NhanGui::HANH_DONG . '">' . wp_nonce_field( NhanGui::HANH_DONG, '_wpnonce', true, false )
			. '<input type="hidden" name="bai" value="' . ( $bai ? (int) $bai->ID : 0 ) . '"><input type="hidden" name="vi_pham" value="' . max( 0, $vi_pham ) . '">'
			. '<fieldset><legend>Loại đóng góp <span aria-hidden="true">*</span></legend>';
		foreach ( GopNguoiDoc::LOAI as $ma_loai => [ $nhan, $can_bai ] ) {
			if ( ( 'vi_pham' === $ma_loai && 'vi_pham' !== $loai ) || ( $can_bai && ! $bai ) ) {
				continue;
			}
			$h .= '<label class="choice"><input type="radio" name="loai" value="' . esc_attr( $ma_loai ) . '" required' . checked( $loai ?: 'di_ban', $ma_loai, false ) . '> ' . esc_html( $nhan ) . '</label>';
		}
		$h .= '</fieldset>'
			. '<label for="gop-noi-dung">Nội dung</label><textarea id="gop-noi-dung" name="noi_dung" rows="7" maxlength="' . GopNguoiDoc::DAI_PHAN_TICH . '">' . ( $vi_pham ? esc_textarea( 'Cảm nghĩ / bài cảm nhận số ' . $vi_pham . ' vi phạm vì: ' ) : '' ) . '</textarea>'
			. '<p class="small muted">Giữ nguyên lời bạn nghe, đọc được; ở quê nói khác thì mỗi câu một dòng. Báo lỗi: chữ nào, dòng nào, đúng ra là gì. Bài cảm nhận: ít nhất 200 chữ cái, dòng trống tách đoạn. Không đưa thông tin cá nhân không cần thiết.</p>'
			. '<fieldset><legend>Khi gửi số trang</legend>' . $o( 'gop-sach', 'sach', 'Tên sách — để trống nếu chưa biết' ) . $o( 'gop-trang', 'trang', 'Số trang', 40 ) . '</fieldset>'
			. '<fieldset><legend>Khi viết cảm nhận</legend>' . $o( 'gop-tieu-de', 'tieu_de', 'Tiêu đề bài viết' )
			. '<label for="gop-vai">Bạn là (không bắt buộc)</label><select id="gop-vai" name="vai"><option value="">Không nói</option>' . implode( '', array_map( static fn( $k, $v ) => '<option value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>', array_keys( GopNguoiDoc::VAI ), GopNguoiDoc::VAI ) ) . '</select>'
			. '<label class="choice"><input type="checkbox" name="tu_viet" value="1"> Tôi tự viết bài này</label></fieldset>'
			. '<fieldset><legend>Thêm nếu bạn muốn (không bắt buộc)</legend>' . $o( 'gop-noi-nghe', 'noi_nghe', 'Bạn nghe ở đâu? Tên làng, xã, tỉnh mà người kể quen gọi' )
			. $o( 'gop-nguoi-ke', 'nguoi_ke', 'Ai kể, người đó là gì với bạn? Ví dụ: bà nội tôi — không cần tên' ) . $o( 'gop-ten', 'ten', 'Tên hiển thị nếu được ghi công — bút danh cũng được', 80 ) . '</fieldset>'
			. '<label class="choice"><input type="checkbox" name="dong_y" value="1" required> Tôi đồng ý để thư viện dùng nội dung này theo giấy phép CC BY 4.0 <span aria-hidden="true">*</span></label>'
			// Ô bẫy: người không thấy, máy tự động hay điền
			. '<p style="position:absolute;left:-9999px" aria-hidden="true"><label>Đừng điền ô này <input type="text" name="web" tabindex="-1" autocomplete="off"></label></p>'
			. ( '' !== $ts ? '<div class="cf-turnstile" data-sitekey="' . esc_attr( $ts ) . '" data-language="vi"></div>' : '' )
			. '<p class="small muted">Nội dung chỉ người biên soạn đọc trước, lưu trên máy chủ của thư viện, chỉ dùng để sửa và bổ sung thư viện (Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15). Thư viện không lưu địa chỉ mạng của bạn. Muốn gỡ điều đã gửi: gửi một "Báo lỗi" ghi "xin gỡ" và ngày gửi. ' . Ve::lien_ket( 'rieng-tu', 'Quyền riêng tư' ) . '</p>'
			. '<div class="actions"><button type="submit" class="btn primary">Gửi</button></div></form>';
		return $h;
	}

	/**
	 * Cuối trang bài: lối góp và các cảm nghĩ đã duyệt (mỗi cảm nghĩ có liên kết Báo vi phạm).
	 *
	 * @param list<array{id:int,ten:string,chu:string,ngay:string}> $cam_nghi
	 */
	public static function cuoi_bai( int $bai_id, array $cam_nghi, bool $da_gui ): string {
		$goc = Ve::url( 'gop' );
		$h   = '<section class="section" id="gop" aria-labelledby="gop-tieu-de"><h2 id="gop-tieu-de">Bạn muốn góp gì?</h2>'
			. ( $da_gui ? '<div class="notice success" role="status"><strong>Cảm ơn bạn!</strong> Nội dung đã tới người biên soạn; chỉ hiện sau khi được duyệt.</div>' : '' )
			. '<p class="small muted">Một chi tiết nhỏ cũng giúp trang đọc đầy đủ hơn. Không cần đăng nhập.</p><div class="gop-grid">';
		foreach ( self::LOI as $loai => [ $chu, $icon ] ) {
			$h .= '<a class="gop-nut" href="' . esc_url( add_query_arg( array( 'loai' => $loai, 'bai' => $bai_id ), $goc ) . '#gop-form' ) . '">' . Ve::icon( $icon ) . '<span>' . esc_html( $chu ) . '</span></a>';
		}
		$h .= '</div></section>';
		if ( $cam_nghi ) {
			$h .= '<section class="section" id="cam-nghi" aria-labelledby="cam-nghi-tieu-de"><h2 id="cam-nghi-tieu-de">Cảm nghĩ của bạn đọc</h2>';
			foreach ( $cam_nghi as $c ) {
				$h .= '<article class="card"><p>' . nl2br( esc_html( $c['chu'] ) ) . '</p><p class="small muted">' . esc_html( $c['ten'] . ' · ' . $c['ngay'] ) . ' · <a href="'
					. esc_url( add_query_arg( array( 'loai' => 'vi_pham', 'bai' => $bai_id, 'vi_pham' => $c['id'] ), $goc ) . '#gop-form' ) . '">Báo vi phạm</a></p></article>';
			}
			$h .= '</section>';
		}
		return $h;
	}
}
