<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\NguoiDoc\NhanGui;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\GopNguoiDoc;

defined( 'ABSPATH' ) || exit;

final class GopMotCau {

	public static function moi(): string {
		return '<section class="gop-moi"><div><h2>Bạn còn nhớ câu nào?</h2><p>Một câu hay một đoạn cũng được. Cùng góp thêm vào thư viện.</p></div>' . Ve::lien_ket( 'gop', 'Góp một câu ' . Ve::icon( 'arrow' ), 'btn primary' ) . '</section>';
	}

	public static function hien( int $bai_id, string $gui, string $ma, array $giu ): string {
		$gia = static fn( $k ) => is_scalar( $giu[ $k ] ?? null ) ? (string) $giu[ $k ] : '';
		$head = '<section class="gop-mot-cau">';
		if ( 'da_gui' === $gui ) {
			return $head . '<h1>Cảm ơn bạn đã góp thêm một lời.</h1><p role="status">Đã nhận để người biên soạn xem, chưa đăng lên trang. Đóng góp có thể thành bài mới hoặc bổ sung cho bài đã có.</p><div class="actions">' . Ve::lien_ket( 'gop', 'Góp câu khác', 'btn primary' ) . Ve::lien_ket( 'trang-chu', 'Quay lại đọc', 'btn' ) . '</div></section>';
		}
		$head .= '<h1>Bạn còn nhớ câu nào?</h1><p class="muted">Ca dao, tục ngữ, đồng dao, vè hay câu đố.<br>Một câu, một đoạn cũng được. Không cần tài khoản.</p>';
		if ( 'loi' === $gui ) { $head .= '<p class="notice" id="gop-bao-loi" role="alert" tabindex="-1" autofocus>' . esc_html( GopNguoiDoc::BAO[ $ma ] ?? GopNguoiDoc::BAO['loi'] ) . ' Nội dung bạn nhập vẫn ở bên dưới.</p>'; }
		$bai = $bai_id ? get_post( $bai_id ) : null;
		if ( $bai && ( KhaiBao::TAC_PHAM !== $bai->post_type || 'publish' !== $bai->post_status || '' !== $bai->post_password || \Phongdao\KhoLuu\PhanBai::lay( $bai->ID, 'tho_tuc' ) ) ) { $bai = null; }
		if ( $bai ) { $head .= '<p class="small">Bạn đang góp lời khác hoặc nguồn cho <a href="' . esc_url( get_permalink( $bai ) ) . '">' . esc_html( $bai->post_title ) . '</a>.</p>'; }
		$uuid = wp_generate_uuid4(); $lan = NhanGui::lan_hop_le( $gia( 'lan_gui' ) ) ? $gia( 'lan_gui' ) : $uuid . '.' . wp_hash( $uuid . '|gop' );
		$them = '' !== $gia( 'xuat_xu' ) || '' !== $gia( 'ten' );
		$head .= '<form id="gop-form" class="form-gop gop-loi-form" method="post" action="' . esc_url( Ve::url( 'gop' ) ) . '" data-gan-giong="' . esc_url( rest_url( \Phongdao\GiaoTiep\Rest\Module::KHONG_GIAN . '/gop/gan-giong' ) ) . '">'
			. '<input type="hidden" name="action" value="' . NhanGui::HANH_DONG . '"><input type="hidden" name="loai" value="tac_pham"><input type="hidden" name="bai" value="' . ( $bai ? $bai->ID : 0 ) . '"><input type="hidden" name="lan_gui" value="' . esc_attr( $lan ) . '">' . wp_nonce_field( NhanGui::HANH_DONG, '_wpnonce', true, false )
			. '<label for="gop-noi-dung">Lời bạn nhớ</label><textarea id="gop-noi-dung" name="noi_dung" rows="5" maxlength="4000" required aria-describedby="gop-goi-y">' . esc_textarea( $gia( 'noi_dung' ) ) . '</textarea><p id="gop-goi-y" class="small muted">Giữ cách xuống dòng bạn biết. Không chắc chỗ nào, bạn có thể nói rõ. Tối đa 4.000 ký tự.</p>'
			. '<div id="gop-gan-giong" aria-live="polite"></div><details class="gop-them"' . ( $them ? ' open' : '' ) . '><summary>Thêm nguồn hoặc tên ghi công — tuỳ chọn</summary><label for="gop-xuat-xu">Bạn nghe hoặc đọc ở đâu?</label><textarea id="gop-xuat-xu" name="xuat_xu" rows="2" maxlength="2000">' . esc_textarea( $gia( 'xuat_xu' ) ) . '</textarea><p class="small muted">Tên sách, trang, đường dẫn hoặc cách gọi người kể. Không rõ thì để trống; không cần tên thật hay địa chỉ của người kể. Chỉ gửi tư liệu bạn có thể chia sẻ.</p>'
			. '<label for="gop-ten">Bút danh nếu muốn được ghi công</label><input id="gop-ten" name="ten" maxlength="80" value="' . esc_attr( $gia( 'ten' ) ) . '"><label class="choice gop-hien-ten"><input type="checkbox" name="hien_ten" value="1"' . checked( '1', $gia( 'hien_ten' ), false ) . '> Tôi đồng ý hiển thị bút danh này nếu đóng góp được sử dụng.</label></details>'
			. '<label class="choice"><input type="checkbox" name="dong_y" value="1" required' . checked( '1', $gia( 'dong_y' ), false ) . '> Tôi đồng ý để thư viện dùng nội dung này theo giấy phép CC BY 4.0.</label><p class="gop-bay" aria-hidden="true"><label>Đừng điền ô này <input name="web" tabindex="-1" autocomplete="off"></label></p>';
		$ts = NhanGui::turnstile()['site'];
		$head .= ( '' === $ts ? '' : '<div class="cf-turnstile" data-sitekey="' . esc_attr( $ts ) . '" data-language="vi"></div>' )
			. '<button type="submit" class="btn primary gop-gui">Gửi lời tôi nhớ</button><p class="small muted">Nội dung sẽ được người biên soạn xem trước khi xuất hiện. Nguồn bạn cung cấp chưa được coi là đã đối chiếu.</p></form>'
			. '<details class="gop-loi-khac"><summary>Bạn muốn báo lỗi hoặc góp chú thích?</summary><div class="actions">';
		foreach ( array( 'bao_loi' => 'Báo lỗi', 'chu_thich' => 'Góp chú thích', 'so_trang' => 'Gửi số trang', 'phan_tich' => 'Viết cảm nhận' ) as $k => $ten ) { $head .= '<a href="' . esc_url( add_query_arg( array_filter( array( 'loai' => $k, 'bai' => $bai ? $bai->ID : 0 ) ), Ve::url( 'gop' ) ) ) . '">' . esc_html( $ten ) . '</a>'; }
		return $head . '</div></details><details class="gop-quyen"><summary>Nội dung được xử lý thế nào?</summary><p class="small muted">Chỉ người biên soạn đọc bản gửi trước khi quyết. Không cần thông tin liên hệ; muốn sửa hoặc gỡ nội dung đã gửi, bạn dùng Báo lỗi, nêu ngày gửi và nội dung liên quan. ' . Ve::lien_ket( 'rieng-tu', 'Quyền riêng tư' ) . '</p></details></section>';
	}
}
