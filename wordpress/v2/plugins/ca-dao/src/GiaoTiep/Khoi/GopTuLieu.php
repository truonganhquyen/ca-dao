<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\NguoiDoc\NhanGui;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\GopNguoiDoc;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Góp tư liệu /gop/ và các lối góp cuối bài. CSS chọn nhóm theo nút radio, dùng được khi tắt JavaScript.
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

	/** Loại => [nhãn ô chữ, hướng dẫn, nút gửi]. Không thêm trường hoặc đổi nghiệp vụ nhận/duyệt. */
	private const CHU = array(
		'di_ban'    => array( 'Lời bạn nghe hoặc đọc được', 'Giữ nguyên lời bạn nghe hoặc đọc được, mỗi câu một dòng. Không chắc chỗ nào, bạn có thể nói rõ chỗ đó.', 'Gửi lời bạn biết' ),
		'chu_thich' => array( 'Bạn muốn góp ý từ hoặc đoạn nào?', 'Ghi từ hoặc đoạn cần chú thích và điều bạn muốn bổ sung; có tài liệu tham khảo thì ghi kèm.', 'Gửi góp ý chú thích' ),
		'bao_loi'   => array( 'Bạn thấy lỗi gì?', 'Ghi chỗ gặp lỗi và điều cần sửa. Nếu góp từ trang này, bạn có thể dán đường dẫn bài vào đây.', 'Gửi báo lỗi' ),
		'so_trang'  => array( 'Ghi chú thêm — không bắt buộc', 'Có thể chỉ gửi số trang. Nếu chưa biết số trang, hãy mô tả điều bạn tìm thấy trong ô này.', 'Gửi số trang' ),
		'cam_nghi'  => array( 'Cảm nghĩ của bạn', 'Vài dòng về bài đang đọc; tối đa 2.000 ký tự.', 'Gửi cảm nghĩ' ),
		'phan_tich' => array( 'Bài viết của bạn', 'Ít nhất 200 chữ cái, tối đa 20.000 ký tự; dòng trống để tách đoạn.', 'Gửi bài viết' ),
		'vi_pham'   => array( 'Nội dung này vi phạm vì sao?', 'Nêu vấn đề để người biên soạn kiểm tra. Không cần chép lại thông tin cá nhân.', 'Gửi báo vi phạm' ),
	);

	/** Các nhãn chỉ hiện theo lựa chọn, kể cả khi tắt JavaScript. */
	private static function chu_theo_loai( int $cot ): string {
		$h = '<span class="gop-du-phong">' . array( 'Nội dung', 'Ghi điều bạn muốn góp; không đưa thông tin cá nhân không cần thiết.', 'Gửi' )[ $cot ] . '</span>';
		foreach ( self::CHU as $loai => $chu ) {
			$h .= '<span data-gop-loai="' . esc_attr( $loai ) . '">' . esc_html( $chu[ $cot ] ) . '</span>';
		}
		return $h;
	}

	/**
	 * @param bool $dau in tiêu đề trang (false: form nằm trong trang tĩnh "Viết cảm nhận" mang từ MVP)
	 */
	public static function hien( string $loai, int $bai_id, string $gui, string $ma, int $vi_pham, bool $dau = true, array $giu = array() ): string {
		$loi = NhanGui::loi_trang();
		if ( null !== $loi ) {
			$giu      = $loi['p'];
			$loai     = (string) ( $giu['loai'] ?? '' );
			$bai_id   = (int) ( $giu['bai'] ?? 0 );
			$vi_pham  = (int) ( $giu['vi_pham'] ?? 0 );
			$gui      = 'loi';
			$ma       = $loi['ma'];
		}
		$bai  = $bai_id ? get_post( $bai_id ) : null;
		$bai  = $bai && KhaiBao::TAC_PHAM === $bai->post_type && 'publish' === $bai->post_status ? $bai : null;
		$loai = isset( self::CHU[ $loai ] ) && ( 'cam_nghi' !== $loai || $bai ) ? $loai : '';
		$gia  = static fn( string $ten ): string => is_scalar( $giu[ $ten ] ?? null ) ? (string) $giu[ $ten ] : '';
		$h    = '';
		if ( $dau ) {
			$h .= '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Góp tư liệu</span></nav>'
				. '<p class="eyebrow">Cùng làm đầy trang sách</p><h1>Bạn muốn góp gì?</h1><p class="muted">Không cần tên thật, email hay số điện thoại. Nội dung gửi sẽ chờ người biên soạn xem.</p>';
		}
		if ( 'da_gui' === $gui ) {
			$h .= '<div class="notice success" role="status"><strong>Cảm ơn bạn!</strong> Nội dung đã tới người biên soạn. Góp ý sẽ được kiểm tra; cảm nghĩ và bài viết chỉ hiện sau khi được duyệt.</div>';
		} elseif ( 'loi' === $gui ) {
			$bao = GopNguoiDoc::BAO[ $ma ] ?? GopNguoiDoc::BAO['loi'];
			if ( 'cu' === $ma && $giu ) {
				$bao = 'Phiên gửi đã hết hạn. Bạn kiểm tra rồi gửi lại bằng form bên dưới nhé.';
			}
			if ( 'thieu' === $ma ) {
				$bao = '' === $loai ? 'Bạn chọn loại đóng góp trước khi gửi nhé.' : ( 'so_trang' === $loai ? 'Bạn điền số trang hoặc ghi chú, rồi đánh dấu đồng ý sử dụng nội dung.' : 'Bạn điền nội dung, rồi đánh dấu đồng ý sử dụng nội dung.' );
			}
			$h .= '<div id="gop-bao-loi" class="notice" role="alert" tabindex="-1" autofocus>' . esc_html( $bao ) . ( $giu ? ' Nội dung bạn vừa nhập vẫn ở bên dưới.' : '' ) . '</div>';
		}
		if ( $bai ) {
			$h .= '<p class="small">Góp cho bài: <a href="' . esc_url( (string) get_permalink( $bai ) ) . '">' . esc_html( $bai->post_title ) . '</a></p>';
		}
		$o    = static fn( string $id, string $ten, string $nhan, int $dai = 200 ): string => '<label for="' . $id . '">' . $nhan . '</label><input id="' . $id . '" name="' . $ten . '" maxlength="' . $dai . '" value="' . esc_attr( $gia( $ten ) ) . '">';
		$ts   = NhanGui::turnstile()['site'];
		$h   .= '<form id="gop-form" class="form-gop gop-rut-gon" method="post" action="' . esc_url( Ve::url( 'gop' ) . '#gop-form' ) . '">'
			. '<input type="hidden" name="action" value="' . NhanGui::HANH_DONG . '">' . wp_nonce_field( NhanGui::HANH_DONG, '_wpnonce', true, false )
			. '<input type="hidden" name="bai" value="' . ( $bai ? (int) $bai->ID : 0 ) . '"><input type="hidden" name="vi_pham" value="' . max( 0, $vi_pham ) . '">'
			. '<details class="gop-chon-loai"' . ( '' === $loai ? ' open' : '' ) . '><summary>Loại góp: <span class="gop-chua-chon">chọn điều bạn muốn gửi</span>';
		foreach ( self::CHU as $k => $chu ) {
			$h .= '<span data-gop-loai="' . esc_attr( $k ) . '">' . esc_html( GopNguoiDoc::LOAI[ $k ][0] ) . '</span>';
		}
		$h .= '</summary><fieldset><legend>Loại đóng góp <span aria-hidden="true">*</span></legend>';
		foreach ( GopNguoiDoc::LOAI as $ma_loai => [ $nhan, $can_bai ] ) {
			// Cách hiểu gửi bằng form nhanh trên trang bài (tầng Hiểu thêm), không qua trang Góp
			if ( 'cach_hieu' === $ma_loai || ( 'vi_pham' === $ma_loai && 'vi_pham' !== $loai ) || ( $can_bai && ! $bai ) ) {
				continue;
			}
			$h .= '<label class="choice"><input type="radio" name="loai" value="' . esc_attr( $ma_loai ) . '" required' . checked( $loai, $ma_loai, false ) . '> ' . esc_html( $nhan ) . '</label>';
		}
		$h .= '</fieldset></details><div class="gop-than">'
			. '<fieldset data-gop-loai="so_trang"><legend>Trang sách bạn tìm thấy</legend>' . $o( 'gop-sach', 'sach', 'Tên sách — để trống nếu chưa biết' ) . $o( 'gop-trang', 'trang', 'Số trang', 40 ) . '</fieldset>'
			. '<div data-gop-loai="phan_tich">' . $o( 'gop-tieu-de', 'tieu_de', 'Tiêu đề bài viết' ) . '</div>'
			. '<label for="gop-noi-dung">' . self::chu_theo_loai( 0 ) . '</label><textarea id="gop-noi-dung" name="noi_dung" rows="4" aria-describedby="gop-goi-y" maxlength="' . GopNguoiDoc::DAI_PHAN_TICH . '">' . esc_textarea( array_key_exists( 'noi_dung', $giu ) ? $gia( 'noi_dung' ) : ( $vi_pham ? 'Cảm nghĩ / bài cảm nhận số ' . $vi_pham . ' vi phạm vì: ' : '' ) ) . '</textarea>'
			. '<p id="gop-goi-y" class="small muted">' . self::chu_theo_loai( 1 ) . '</p>'
			. '<details class="gop-them" data-gop-loai="di_ban"' . ( '' !== $gia( 'noi_nghe' ) || '' !== $gia( 'nguoi_ke' ) ? ' open' : '' ) . '><summary>Thêm nơi nghe, người kể — không bắt buộc</summary>' . $o( 'gop-noi-nghe', 'noi_nghe', 'Bạn nghe ở đâu? Tên làng, xã, tỉnh mà người kể quen gọi' ) . $o( 'gop-nguoi-ke', 'nguoi_ke', 'Ai kể, người đó là gì với bạn? Ví dụ: bà nội tôi — không cần tên' ) . '</details>'
			. '<details class="gop-them"' . ( '' !== $gia( 'ten' ) || '' !== $gia( 'vai' ) ? ' open' : '' ) . '><summary>Thêm thông tin — không bắt buộc</summary>' . $o( 'gop-ten', 'ten', 'Tên hiển thị nếu được ghi công — bút danh cũng được', 80 )
			. '<div data-gop-loai="phan_tich"><label for="gop-vai">Bạn là (không bắt buộc)</label><select id="gop-vai" name="vai"><option value="">Không nói</option>' . implode( '', array_map( static fn( $k, $v ) => '<option value="' . esc_attr( $k ) . '"' . selected( $gia( 'vai' ), $k, false ) . '>' . esc_html( $v ) . '</option>', array_keys( GopNguoiDoc::VAI ), GopNguoiDoc::VAI ) ) . '</select></div></details>'
			. '<div data-gop-loai="phan_tich"><label class="choice"><input type="checkbox" name="tu_viet" value="1"' . checked( '1', $gia( 'tu_viet' ), false ) . '> Tôi tự viết bài này</label></div>'
			. '<label class="choice"><input type="checkbox" name="dong_y" value="1" required' . checked( '1', $gia( 'dong_y' ), false ) . '> Tôi đồng ý để thư viện dùng nội dung này theo giấy phép CC BY 4.0 <span aria-hidden="true">*</span></label>'
			// Ô bẫy: người không thấy, máy tự động hay điền
			. '<p style="position:absolute;left:-9999px" aria-hidden="true"><label>Đừng điền ô này <input type="text" name="web" tabindex="-1" autocomplete="off"></label></p>'
			. ( '' !== $ts ? '<div class="cf-turnstile" data-sitekey="' . esc_attr( $ts ) . '" data-language="vi"></div>' : '' )
			. '<div class="actions"><button type="submit" class="btn primary">' . self::chu_theo_loai( 2 ) . '</button></div>'
			. '<p class="small muted">Người biên soạn sẽ xem nội dung bạn gửi. Không đưa thông tin cá nhân không cần thiết.</p>'
			. '<details class="gop-them"><summary>Nội dung được xử lý thế nào?</summary><p class="small muted">Nội dung chỉ người biên soạn đọc trước, lưu trên máy chủ của thư viện, chỉ dùng để sửa và bổ sung thư viện (Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15). Thư viện không lưu địa chỉ mạng của bạn. Muốn gỡ điều đã gửi: gửi một "Báo lỗi" ghi "xin gỡ" và ngày gửi. ' . ( Ve::co( 'rieng-tu' ) ? Ve::lien_ket( 'rieng-tu', 'Quyền riêng tư' ) : '' ) . '</p></details></div></form>';
		return $h;
	}

	/** Cuối trang bài: các lối góp (cảm nghĩ, bài cảm nhận, cách hiểu đã duyệt nằm ở tầng Hiểu thêm — HieuThem). */
	public static function cuoi_bai( int $bai_id, bool $da_gui ): string {
		$goc = Ve::url( 'gop' );
		$h   = '<section class="section" id="gop" aria-labelledby="gop-tieu-de"><h2 id="gop-tieu-de">Bạn muốn góp gì?</h2>'
			. ( $da_gui ? '<div class="notice success" role="status"><strong>Cảm ơn bạn!</strong> Nội dung đã tới người biên soạn; chỉ hiện sau khi được duyệt.</div>' : '' )
			. '<p class="small muted">Một chi tiết nhỏ cũng giúp trang đọc đầy đủ hơn. Không cần đăng nhập.</p><div class="gop-grid">';
		foreach ( self::LOI as $loai => [ $chu, $icon ] ) {
			$h .= '<a class="gop-nut" href="' . esc_url( add_query_arg( array( 'loai' => $loai, 'bai' => $bai_id ), $goc ) . '#gop-form' ) . '">' . Ve::icon( $icon ) . '<span>' . esc_html( $chu ) . '</span></a>';
		}
		$h .= '</div></section>';
		return $h;
	}
}
