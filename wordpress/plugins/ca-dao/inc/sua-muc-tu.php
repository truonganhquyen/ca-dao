<?php
/**
 * Sửa mục từ (T-113): trong trang Ca dao – Tục ngữ → Từ điển → Sửa mục từ, người biên soạn sửa được dòng
 * "Nguồn: …" của lời giải (xóa khi tự viết lại lời giải) và ảnh: chọn ảnh khác từ Thư viện ảnh kèm dòng ghi công,
 * hoặc ẩn ảnh lấy từ Wikidata. Ảnh sai vì máy khớp nhầm thực thể (ví dụ "canh" giờ đêm ↔ "xúp") thì sửa tận gốc ở
 * trang Xác nhận Wikidata (bấm "Sai"). Máy không tự điền nguồn: ô nguồn chỉ giữ giá trị đã có hoặc do người viết.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'tu_kho_edit_form_fields',
	static function ( $t ) {
		$ten   = (string) get_term_meta( $t->term_id, '_cadao_nguon_ten', true );
		$url   = (string) get_term_meta( $t->term_id, '_cadao_nguon_url', true );
		$rieng = (int) get_term_meta( $t->term_id, '_cadao_anh_rieng', true );
		$wd    = cadao_wd_cua_muc_tu( (int) $t->term_id );
		$anh   = $rieng ? wp_get_attachment_image_url( $rieng, 'medium' ) : ( $wd['anh_da_tai']['url'] ?? '' );
		echo '<tr class="form-field"><th scope="row">Nguồn của lời giải</th><td>'
			. '<p><label for="cadao-mt-nguon-ten">Tên nguồn</label><br><input type="text" id="cadao-mt-nguon-ten" name="cadao_mt[nguon_ten]" value="' . esc_attr( $ten ) . '"></p>'
			. '<p><label for="cadao-mt-nguon-url">Đường dẫn</label><br><input type="url" id="cadao-mt-nguon-url" name="cadao_mt[nguon_url]" value="' . esc_attr( $url ) . '"></p>'
			. '<p class="description">Để trống thì trang không có dòng "Nguồn: …" (từ D-068, nguồn của lời giải chép tự động được liệt kê ở trang Chính sách thu thập dữ liệu). Muốn ghi một nguồn cụ thể (sách, trang web) thì điền tên và đường dẫn đúng chỗ đã lấy. Sửa lời giải ở ô Miêu tả thì mục từ được tính là của dự án.</p></td></tr>';
		echo '<tr class="form-field"><th scope="row">Ảnh</th><td>'
			. '<p><img id="cadao-mt-xem" src="' . esc_url( (string) $anh ) . '" alt="" style="max-width:240px;height:auto;border-radius:4px' . ( $anh ? '' : ';display:none' ) . '"></p>'
			. '<p class="description" id="cadao-mt-dang">' . esc_html( $rieng ? 'Đang dùng ảnh bạn chọn.' : ( $anh ? 'Đang dùng ảnh lấy từ Wikidata (Wikimedia Commons).' : 'Chưa có ảnh.' ) ) . '</p>'
			. '<input type="hidden" id="cadao-mt-anh" name="cadao_mt[anh_rieng]" value="' . ( $rieng ?: '' ) . '">'
			. '<p><button type="button" class="button" id="cadao-mt-chon">Chọn ảnh khác…</button> <button type="button" class="button-link" id="cadao-mt-bo"' . ( $rieng ? '' : ' hidden' ) . '>Bỏ ảnh đã chọn</button></p>'
			. '<p><label for="cadao-mt-ghi-cong">Ghi công ảnh (hiện dưới ảnh)</label><br><input type="text" id="cadao-mt-ghi-cong" name="cadao_mt[ghi_cong]" value="' . esc_attr( (string) get_term_meta( $t->term_id, '_cadao_anh_ghi_cong', true ) ) . '" placeholder="Ví dụ: Ảnh: Nguyễn Văn A · CC BY 4.0 — hoặc: Ảnh của dự án"></p>'
			. ( $wd && ! empty( $wd['anh_da_tai'] ) ? '<p><label><input type="checkbox" name="cadao_mt[an_anh]" value="1"' . checked( (bool) get_term_meta( $t->term_id, '_cadao_an_anh', true ), true, false ) . '> Không hiện ảnh lấy từ Wikidata</label></p>' : '' )
			. ( $wd ? '<p class="description">Ảnh, bản đồ, liên kết Wikipedia sai hẳn (máy khớp nhầm thực thể)? Vào <a href="' . esc_url( admin_url( 'edit.php?post_type=' . CADAO_LOAI_BAI . '&page=' . CADAO_TRANG_WIKIDATA . '&tab=dung' ) ) . '">Xác nhận Wikidata → Đúng</a>, tìm "' . esc_html( $t->name ) . '" và bấm <strong>Sai</strong>.</p>' : '' )
			. '</td></tr>';
	}
);

add_action(
	'edited_tu_kho',
	static function ( $term_id ) {
		if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['cadao_mt'] ) ) { // nonce: WordPress đã kiểm ở form sửa
			return;
		}
		$m = (array) wp_unslash( $_POST['cadao_mt'] );
		$t = get_term( $term_id, 'tu_kho' );
		if ( $t && ! is_wp_error( $t ) && cadao_khoa_muc_tu( $t->name, $t->description ) !== (string) get_term_meta( $term_id, '_cadao_khoa', true ) ) {
			delete_term_meta( $term_id, '_cadao_chep' ); // lời giải đã viết lại: của dự án, hết noindex
		}
		update_term_meta( $term_id, '_cadao_nguon_ten', sanitize_text_field( (string) ( $m['nguon_ten'] ?? '' ) ) );
		update_term_meta( $term_id, '_cadao_nguon_url', esc_url_raw( (string) ( $m['nguon_url'] ?? '' ) ) );
		$anh = (int) ( $m['anh_rieng'] ?? 0 );
		if ( $anh && wp_attachment_is_image( $anh ) ) {
			update_term_meta( $term_id, '_cadao_anh_rieng', $anh );
		} else {
			delete_term_meta( $term_id, '_cadao_anh_rieng' );
		}
		update_term_meta( $term_id, '_cadao_anh_ghi_cong', sanitize_text_field( (string) ( $m['ghi_cong'] ?? '' ) ) );
		if ( ! empty( $m['an_anh'] ) ) {
			update_term_meta( $term_id, '_cadao_an_anh', 1 );
		} else {
			delete_term_meta( $term_id, '_cadao_an_anh' );
		}
	}
);

// Nút "Chọn ảnh khác…": hộp Thư viện ảnh của WordPress (chỉ ở trang sửa mục từ)
add_action(
	'admin_enqueue_scripts',
	static function ( $trang ) {
		$man = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( 'term.php' !== $trang || ! $man || 'tu_kho' !== $man->taxonomy ) {
			return;
		}
		wp_enqueue_media();
		wp_add_inline_script(
			'media-editor',
			<<<'JS'
jQuery(function ($) {
	var f;
	$('#cadao-mt-chon').on('click', function (e) {
		e.preventDefault();
		f = f || wp.media({ title: 'Chọn ảnh cho mục từ', button: { text: 'Dùng ảnh này' }, library: { type: 'image' }, multiple: false });
		f.off('select').on('select', function () {
			var a = f.state().get('selection').first().toJSON();
			$('#cadao-mt-anh').val(a.id);
			$('#cadao-mt-xem').attr('src', a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url).show();
			$('#cadao-mt-dang').text('Ảnh bạn vừa chọn — bấm Cập nhật để lưu.');
			$('#cadao-mt-bo').prop('hidden', false);
			if (!$('#cadao-mt-ghi-cong').val() && a.caption) { $('#cadao-mt-ghi-cong').val(a.caption); }
		});
		f.open();
	});
	$('#cadao-mt-bo').on('click', function (e) {
		e.preventDefault();
		$('#cadao-mt-anh').val('');
		$('#cadao-mt-xem').hide();
		$('#cadao-mt-dang').text('Đã bỏ ảnh đã chọn — bấm Cập nhật để lưu.');
		$(this).prop('hidden', true);
	});
});
JS
		);
	}
);
