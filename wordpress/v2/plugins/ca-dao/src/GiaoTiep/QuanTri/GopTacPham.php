<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\NguoiDoc\DuyetTacPham;
use Phongdao\DichVu\NguoiDoc\SoTrung;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/** Một thẻ có đối chiếu, phân loại và bản xem trước nguồn/ghi công; không tạo hàng chờ khác. */
final class GopTacPham {

	public static function the( array $v, bool $co_form ): string {
		$id = (int) $v['doi_tuong_id']; $p = get_post( $id );
		if ( ! $p ) { return '<p>Không còn bản gửi này.</p>'; }
		$p->post_content = DuyetTacPham::loi( $id );
		$d = get_post_meta( $id, '_phongdao_gop', true ); $d = is_array( $d ) ? $d : array();
		$o = '<pre class="gop-loi-doi-chieu">' . esc_html( $p->post_content ) . '</pre><p>Nguồn người góp cung cấp, chưa đối chiếu: ' . esc_html( $d['xuat_xu'] ?? 'Chưa rõ' ) . '</p><p>Bút danh: ' . esc_html( $d['ten'] ?? '' ) . ( empty( $d['hien_ten'] ) ? ' · không đồng ý công khai tên' : ' · đã đồng ý công khai tên khi được dùng' ) . '</p>';
		try { $near = SoTrung::tim( $p->post_content, false, $id ); } catch ( \Throwable $e ) { return $o . '<p role="alert">Chưa kiểm được trùng. Tải lại trước khi quyết.</p>'; }
		$targets = array();
		foreach ( $near as $r ) {
			$o .= '<details class="pq-khoi"><summary>' . esc_html( $r['ten'] ) . ' · giống chữ ' . $r['diem'] . '%' . ( $r['khop'] ? ' · khớp lời sau chuẩn hoá' : '' ) . '</summary><pre class="gop-loi-doi-chieu">' . esc_html( implode( "\n", $r['dong'] ) ) . '</pre><p>' . esc_html( $r['muc'] ) . '</p></details>';
			if ( $r['bai'] ) { $targets[ $r['bai'] ] = $r['ten']; }
		}
		$context = (int) ( $v['noi_dung']['bai'] ?? 0 ); if ( $context && 'tac_pham' === get_post_type( $context ) ) { $targets[ $context ] = get_the_title( $context ); }
		// Bài đích khác do người chọn qua GET trong chính Hộp việc, form quyết vẫn giữ snapshot của bài đó.
		$khac = absint( $_GET['gop_dich'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ xem trước
		if ( $khac && 'tac_pham' === get_post_type( $khac ) && current_user_can( 'edit_post', $khac ) ) { $targets[ $khac ] = get_the_title( $khac ); }
		if ( $khac && isset( $targets[ $khac ] ) ) {
			$o .= '<details open><summary>Bài đích đang xem: ' . esc_html( $targets[ $khac ] ) . '</summary><pre class="gop-loi-doi-chieu">' . esc_html( implode( "\n", PhanBai::lay( $khac, 'loi' )['dong'] ?? array() ) ) . '</pre>';
			foreach ( PhanBai::lay( $khac, 'di_ban' )['muc'] ?? array() as $db ) { $o .= '<pre class="gop-loi-doi-chieu">' . esc_html( implode( "\n", $db['dong'] ) ) . '</pre>'; }
			$o .= '</details>';
		}
		if ( ! $co_form || ! current_user_can( 'cadao_duyet' ) ) { return $o; }
		$form = ' form="hv-form-' . $v['id'] . '"';
		$o .= '<p class="description">Đọc đối chiếu trước khi quyết; 30% là dấu hiệu gần chữ, trên 70% vẫn có thể là dị bản. Tên/nguồn ở dưới là bản sẽ hiện, chưa phải xác nhận tư liệu. Không tự sửa lời người gửi.</p><input' . $form . ' type="hidden" name="ban_gop" value="' . esc_attr( DuyetTacPham::ban( $id ) ) . '">'
			. '<p><label for="gop-loai-' . $id . '">Thể loại khi đăng tác phẩm mới</label><select' . $form . ' id="gop-loai-' . $id . '" name="gop_the_loai"><option value="">— Chọn thể loại —</option>';
		foreach ( TheLoai::DS as $key => $label ) { $o .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>'; }
		$o .= '</select></p><p><label for="gop-dich-' . $id . '">Bài đích khi thêm dị bản/bổ sung nguồn</label><select' . $form . ' id="gop-dich-' . $id . '" name="gop_dich"><option value="">— Chọn bài —</option>';
		foreach ( $targets as $bai => $ten ) { $o .= '<option value="' . $bai . ':' . esc_attr( DuyetTacPham::ban( $id, $bai ) ) . '"' . selected( $khac ?: $context, $bai, false ) . '>' . esc_html( $ten . ' (#' . $bai . ')' ) . '</option>'; }
		$o .= '</select></p><form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="phongdao-hop-viec"><input type="hidden" name="viec" value="' . $v['id'] . '"><label for="gop-khac-' . $id . '">Xem bài đích khác (số bài)</label> <input id="gop-khac-' . $id . '" type="number" min="1" name="gop_dich" value="' . ( $khac ?: '' ) . '"> <button class="button">Xem để đối chiếu</button></form>'
			. '<p><label for="gop-nguon-' . $id . '">Nguồn sẽ công khai — bỏ thông tin cá nhân, để trống nếu chưa rõ</label><textarea' . $form . ' id="gop-nguon-' . $id . '" name="gop_nguon" rows="2" maxlength="2000">' . esc_textarea( $d['xuat_xu'] ?? '' ) . '</textarea></p>';
		if ( ! empty( $d['hien_ten'] ) && '' !== ( $d['ten'] ?? '' ) ) { $o .= '<p><label><input' . $form . ' type="checkbox" name="gop_hien_ten" value="1"> Công khai bút danh “' . esc_html( $d['ten'] ) . '” trên phần được sử dụng</label></p>'; }
		$o .= '<fieldset><legend>Gợi ý chủ đề — chỉ gắn cho bài mới khi bạn chọn</legend>';
		$dummy = (object) array( 'loi' => $p->post_content, 'tieu_de' => '', 'loai' => '', 'du_lieu' => '{}' );
		foreach ( \Phongdao\DichVu\GoiY\GoiY::cho_kho( $dummy ) as $g ) { $o .= '<label class="choice"><input' . $form . ' type="checkbox" name="gop_pl[]" value="' . esc_attr( $g['loai'] . ':' . $g['term']->term_id ) . '"> ' . esc_html( $g['term']->name . ' — ' . $g['ly_do'] ) . '</label>'; }
		$o .= '</fieldset><details><summary>Chọn thêm chủ đề, vùng cho bài mới</summary><p class="description">Chỉ chọn vùng khi có căn cứ từ tư liệu. Thêm dị bản không thay phân loại bài đích.</p>';
		foreach ( array( 'chu_de' => 'Chủ đề', 'vung' => 'Vùng' ) as $tax => $label ) {
			$o .= '<label for="gop-pl-' . $id . '-' . $tax . '">' . $label . '</label><select' . $form . ' multiple size="5" id="gop-pl-' . $id . '-' . $tax . '" name="gop_pl[]">';
			$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
			foreach ( is_array( $terms ) ? $terms : array() as $term ) { $o .= '<option value="' . esc_attr( $tax . ':' . $term->term_id ) . '">' . esc_html( $term->name ) . '</option>'; }
			$o .= '</select>';
		}
		return $o . '<p class="description">Giữ Ctrl hoặc Command để chọn nhiều mục. Có thể để trống và bổ sung sau tại trang Sửa tác phẩm.</p></details>';
	}
}
