<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Chế độ sửa trên trang bài (D-094, docs/28 mục 4.2; màn mẫu B02): dải "Đang sửa", nút Sửa trên từng khối, dòng nguồn
 * gốc của từng phần, khối Lịch sử, các tấm sửa. Chỉ dựng khi người xem có quyền `cadao_soan` — khách không bao giờ nhận
 * các phần này trong HTML.
 */
final class CheDoSua {

	public static function co_quyen(): bool {
		return current_user_can( 'cadao_soan' );
	}

	/** Dữ liệu chế độ sửa của một tác phẩm (null khi người xem không có quyền). */
	public static function ngu_canh( \WP_Post $bai ): ?array {
		if ( ! self::co_quyen() || ! current_user_can( 'edit_post', $bai->ID ) ) {
			return null;
		}
		$ho_so = array();
		$sach  = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) ) as $hs ) {
			$ho_so[ $hs->ID ] = $hs->post_title;
			if ( 'sach' === ( HoSo::truong( $hs->ID )['loai'] ?? '' ) ) {
				$sach[ $hs->ID ] = $hs->post_title;
			}
		}
		$phan = PhanBai::tat_ca( $bai->ID, $bai->post_type );
		return array(
			'id'         => $bai->ID,
			'trang_thai' => $bai->post_status,
			'the_loai'   => (string) \Phongdao\DichVu\DungTrang\DocBai::the_loai( $bai ),
			// Đối chiếu sách là nút riêng (D-105, docs/32 mục 4.4): ô Sửa lời không còn phần đối chiếu
			'an_doi_chieu' => true,
			'phan'       => $phan,
			'so_dong'    => count( array_filter( $phan['loi']['dong'] ?? array(), static fn( $d ): bool => '' !== trim( (string) $d ) ) ),
			'ho_so'      => $ho_so,
			'sach'       => $sach,
			'duyet'      => current_user_can( 'cadao_duyet' ),
			'lich_su'    => Viec::lich_su( 'post', $bai->ID, 15 ),
			'nhap'       => Viec::mo( 'post', $bai->ID, 'nhap_y_nghia' ),
		);
	}

	public static function nut( string $tam, string $nhan ): string {
		return '<button type="button" class="nut-sua no-print" data-action="mo-tam" data-tam="' . esc_attr( $tam ) . '">' . Ve::icon( 'edit' ) . '<span>' . esc_html( $nhan ) . '</span></button>';
	}

	/** Dải "Đang sửa": trạng thái, Xem như người đọc, Đăng / Ẩn bài. */
	public static function dai( array $sua ): string {
		$tt   = array( 'publish' => 'Đã đăng', 'draft' => 'Bản nháp', 'private' => 'Đã ẩn', 'pending' => 'Chờ' )[ $sua['trang_thai'] ] ?? $sua['trang_thai'];
		$cuoi = $sua['lich_su'][0]['quyet_luc'] ?? '';
		$nut  = 'publish' === $sua['trang_thai']
			? '<button type="button" class="btn compact ghost" data-action="trang-thai" data-trang-thai="private">Ẩn bài</button>'
			: ( $sua['duyet'] ? '<button type="button" class="btn compact primary" data-action="trang-thai" data-trang-thai="publish">Đăng</button>' : '' );
		return '<div class="dai-bien-tap no-print" role="region" aria-label="Chế độ sửa" data-bai="' . (int) $sua['id'] . '" data-trang-thai="' . esc_attr( $sua['trang_thai'] ) . '"><span class="dbt-trai">' . Ve::icon( 'edit' ) . '<strong>Đang sửa</strong><span class="tag-pill plain">' . esc_html( $tt ) . '</span>'
			. ( '' === $cuoi ? '' : '<span class="small muted">sửa lần cuối ' . esc_html( self::ngay( $cuoi ) ) . '</span>' ) . '</span>'
			. '<span class="dbt-phai"><a class="btn compact ghost" href="' . esc_url( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( (int) $sua['id'] ) ) . '">Mở trang sửa đầy đủ</a><button type="button" class="btn compact" data-action="xem-nhu-doc-gia" aria-pressed="false">Xem như người đọc</button>' . $nut . '</span></div>';
	}

	/** Dòng nguồn gốc nhỏ dưới một phần (chỉ người biên soạn thấy): ai làm, từ đâu. */
	public static function goc( string $nhan, ?array $gt, array $sua ): string {
		if ( ! $gt ) {
			return '';
		}
		$muc = isset( $gt['muc'] ) ? $gt['muc'] : array( $gt );
		$may = count( array_filter( $muc, static fn( $m ) => NguoiLam::MAY_CHEP === ( $m['nguoi_lam'] ?? '' ) ) );
		$ai  = count( array_filter( $muc, static fn( $m ) => ! empty( $m['ai'] ) ) );
		$hs  = $sua['ho_so'][ (int) ( $muc[0]['nguon']['ho_so'] ?? 0 ) ] ?? '';
		$o   = array();
		if ( $may ) {
			$o[] = ( count( $muc ) > 1 ? $may . ' mục ' : '' ) . 'máy chép' . ( '' === $hs ? '' : ' từ ' . $hs ) . ( 'Lời' === $nhan && ! empty( $sua['phan']['nguon_may']['ngay_lay'] ) ? ', ' . self::ngay( $sua['phan']['nguon_may']['ngay_lay'] ) . ' · chưa sửa tay' : '' );
		}
		if ( count( $muc ) - $may > 0 ) {
			$o[] = ( count( $muc ) > 1 ? ( count( $muc ) - $may ) . ' mục ' : '' ) . 'người viết' . ( $ai ? ', có hỗ trợ AI đã duyệt' : '' );
		}
		return '<p class="goc-nguon no-print">' . Ve::icon( 'info' ) . '<span>' . esc_html( $nhan . ': ' . implode( ' · ', $o ) ) . '</span></p>';
	}

	/** Khối Lịch sử (chỉ người biên soạn thấy). */
	public static function lich_su( array $sua ): string {
		$o = '';
		foreach ( $sua['lich_su'] as $r ) {
			$o .= '<li><strong>' . esc_html( self::ngay( (string) $r['quyet_luc'] ) ) . '</strong> · ' . esc_html( self::mo_ta( $r ) ) . '</li>';
		}
		return '<section class="block tint-dat no-print" id="lich-su"><div class="block-dau"><h2>Lịch sử</h2></div>' . ( '' === $o ? '<p class="small muted">Chưa có lần sửa nào được ghi.</p>' : '<ol class="lich-su">' . $o . '</ol>' ) . '</section>';
	}

	/** Một dòng lịch sử bằng chữ: phần, việc, người; sửa phân loại tay thì kèm mục thêm / gỡ. */
	public static function mo_ta( array $r ): string {
		$ten  = array( 'media' => 'Ảnh minh hoạ', 'loi' => 'Lời', 'y_nghia' => 'Ý nghĩa', 'chu_thich' => 'Từ khó', 'di_ban' => 'Dị bản', 'nghia_den' => 'Nghĩa đen', 'nghia_bong' => 'Nghĩa bóng', 'dung_khi' => 'Dùng khi', 'hinh_anh' => 'Hình ảnh trong bài', 'cau_hoi' => 'Câu hỏi', 'dap_an' => 'Đáp án', 'co_nghi' => 'Cờ nghi', 'boi_canh' => 'Bối cảnh', 'doan' => 'Đoạn', 'chu_de' => 'Chủ đề', 'vung' => 'Vùng', 'mo_tip' => 'Mô-típ', 'hinh_thuc' => 'Hình thức', 'tro_choi' => 'Trò chơi', 'goi_y' => 'Gợi ý', 'nguon_may' => 'Địa chỉ gốc', 'trang_thai' => 'Trạng thái', 'ten' => 'Tên bài', 'duong_dan' => 'Đường dẫn' );
		$viec = array( 'luu' => 'sửa', 'xoa' => 'gỡ', 'khoi_phuc' => 'khôi phục bản trước', 'dang' => 'đăng', 'an' => 'ẩn', 'nhap' => 'về nháp', 'cho' => 'chuyển sang Chờ', 'hen' => 'hẹn giờ đăng', 'thung_rac' => 'đưa vào thùng rác', 'ra_thung_rac' => 'lấy ra khỏi thùng rác', 'xoa_vinh_vien' => 'xoá vĩnh viễn', 'dong_y' => 'đồng ý gợi ý', 'da_co' => 'đã có', 'da_duyet_mvp' => 'đã duyệt trên bản cũ', 'duyet' => 'duyệt', 'bo' => 'bỏ', 'xong' => 'xong', 'sua' => 'sửa rồi đăng', 'cung' => 'cùng một bài', 'di_ban' => 'gộp làm dị bản', 'khac' => 'là bài khác', 'tach_duong_dan' => 'cho đường dẫn riêng' );
		$nd   = (int) $r['nguoi_quyet'] ? get_userdata( (int) $r['nguoi_quyet'] ) : null;
		$ai   = (int) $r['nguoi_quyet'] ? ( $nd ? $nd->display_name : '' ) : 'máy';
		// Dòng sửa ghi phần; thẻ Hộp việc đã quyết thì lấy tên loại thẻ
		$phan = '' !== (string) $r['phan'] ? ( $ten[ $r['phan'] ] ?? $r['phan'] ) : ( \Phongdao\DichVu\XepViec\QuyetViec::LOAI[ $r['loai'] ][0] ?? $r['loai'] );
		$chu  = $phan . ': ' . ( $viec[ $r['quyet'] ] ?? $r['quyet'] ) . ( '' === $ai ? '' : " ($ai)" );
		$truoc = $r['truoc']['ten'] ?? null;
		$sau   = $r['sau']['ten'] ?? null;
		if ( is_array( $truoc ) && is_array( $sau ) ) {
			$them = array_diff( $sau, $truoc );
			$go   = array_diff( $truoc, $sau );
			$chu .= ( $them ? ' · thêm ' . implode( ', ', $them ) : '' ) . ( $go ? ' · gỡ ' . implode( ', ', $go ) : '' );
		}
		return $chu;
	}

	/** Mọi tấm sửa hợp thể loại của bài. */
	public static function tam( array $sua, string $the_loai ): string {
		$o = '';
		foreach ( array_keys( TamSua::TAM ) as $phan ) {
			if ( 'media' === $phan ) {
				continue; // trình chọn WordPress chỉ dùng trong wp-admin
			}
			$rieng = array( 'nghia_den' => 'tuc-ngu', 'nghia_bong' => 'tuc-ngu', 'dung_khi' => 'tuc-ngu', 'hinh_anh' => 'ca-dao|dong-dao', 'cau_hoi' => 'cau-do', 'dap_an' => 'cau-do', 'goi_y' => 'cau-do', 'boi_canh' => 've', 'doan' => 've' )[ $phan ] ?? null;
			if ( null === $rieng || in_array( $the_loai, explode( '|', $rieng ), true ) ) {
				$o .= TamSua::ve( $phan, $sua['phan'][ $phan ] ?? null, $sua );
			}
		}
		return $o;
	}

	/**
	 * Tấm "Sửa phân loại" và "Đổi thể loại" trên trang đọc (D-105, docs/32 mục 4.7): gửi thẳng cùng đường lưu với wp-admin
	 * (LuuPhanLoai, đổi thể loại kèm 301 và nhật ký), có lịch sử; xong quay lại trang đọc. Chủ đề, nơi lưu truyền, mô-típ
	 * chọn bằng chip từ mục có sẵn; gợi ý của máy đứng đầu, ghi "máy gợi ý" — chạm để chọn, máy không tự gắn.
	 */
	public static function tam_phan_loai( \WP_Post $bai, string $the_loai ): string {
		$id    = $bai->ID;
		$goi_y = array();
		foreach ( Viec::mo( 'post', $id, 'goi_y' ) as $v ) {
			$goi_y[ (string) ( $v['noi_dung']['loai'] ?? '' ) ][] = (string) ( $v['noi_dung']['ma'] ?? '' );
		}
		$dau  = static fn( string $ma, string $ten ): string => '<header class="tam-dau"><h2 id="tam-' . $ma . '-tieu-de">' . esc_html( $ten ) . '</h2><button type="button" class="nut-dong" data-action="dong-tam" aria-label="Đóng"><svg class="icon" aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></svg></button></header>';
		$than = '';
		foreach ( array( 'chu_de', 'vung', 'mo_tip' ) as $tax ) {
			if ( ! taxonomy_exists( $tax ) || ! \Phongdao\DichVu\LuuPhan\LuuPhanLoai::sua_duoc( $tax, $the_loai ) ) {
				continue;
			}
			$chon = \Phongdao\DichVu\LuuPhan\LuuPhanLoai::lua_chon( $tax );
			$nhan = KhaiBao::PHAN_LOAI[ $tax ][1] ?? $tax;
			$gan  = wp_get_object_terms( $id, $tax, array( 'fields' => 'ids' ) );
			$gan  = array_map( 'intval', is_array( $gan ) ? $gan : array() );
			$may  = $goi_y[ $tax ] ?? array();
			// Gợi ý của máy đứng đầu danh sách
			usort( $chon, static fn( \WP_Term $a, \WP_Term $b ): int => (int) in_array( $b->slug, $may, true ) <=> (int) in_array( $a->slug, $may, true ) );
			$than .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="tam-pl" data-gui-thuong><fieldset><legend>' . esc_html( 'vung' === $tax ? 'Nơi lưu truyền' : $nhan ) . '</legend>'
				. '<input type="hidden" name="action" value="phongdao_sua_phan_loai"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="tax" value="' . esc_attr( $tax ) . '"><input type="hidden" name="trang_doc" value="1">'
				. wp_nonce_field( 'phongdao_sua_phan_loai_' . $id . '_' . $tax, '_wpnonce', true, false );
			foreach ( $gan as $tid ) {
				$than .= '<input type="hidden" name="da_thay[]" value="' . $tid . '">';
			}
			if ( ! $chon ) {
				$than .= '<p class="small muted">Chưa có mục nào để chọn. Chỉ chọn mục có sẵn, không tạo mục mới.</p></fieldset></form>';
				continue;
			}
			$than .= '<div class="chip-chon-ds">';
			foreach ( $chon as $t ) {
				$tid   = (int) $t->term_id;
				$o_id  = 'tpl-' . $tax . '-' . $tid;
				$than .= '<label class="chip-chon" for="' . $o_id . '"><input type="checkbox" id="' . $o_id . '" name="chon[]" value="' . $tid . '"' . checked( in_array( $tid, $gan, true ), true, false ) . '><span>' . esc_html( $t->name )
					. ( in_array( $t->slug, $may, true ) && ! in_array( $tid, $gan, true ) ? '&nbsp;· máy gợi ý' : '' ) . '</span></label>';
			}
			$than .= '</div><p class="tam-pl-nut"><button type="submit" class="btn primary">Lưu ' . esc_html( mb_strtolower( 'vung' === $tax ? 'Nơi lưu truyền' : $nhan ) ) . '</button></p></fieldset></form>';
		}
		$ra = '<dialog class="tam-sua" id="tam-phan-loai" aria-labelledby="tam-phan-loai-tieu-de"><div class="tam-khung">' . $dau( 'phan-loai', 'Sửa phân loại' )
			. '<div class="tam-than"><p class="small muted">Đánh dấu để gắn, bỏ dấu để gỡ; mỗi nhóm lưu riêng, ghi vào Lịch sử. Gợi ý của máy chỉ là đề xuất.</p>' . $than . '</div>'
			. '<footer class="tam-chan"><button type="button" class="btn" data-action="dong-tam">Đóng</button></footer></div></dialog>';
		if ( current_user_can( 'cadao_duyet' ) ) {
			$o = '';
			foreach ( \Phongdao\MienDuLieu\TheLoai::DS as $ma => $ten ) {
				$o .= '<option value="' . esc_attr( $ma ) . '"' . selected( $ma, $the_loai, false ) . '>' . esc_html( $ten ) . '</option>';
			}
			$ra .= '<dialog class="tam-sua" id="tam-the-loai" aria-labelledby="tam-the-loai-tieu-de"><form method="post" class="tam-khung" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-gui-thuong>' . $dau( 'the-loai', 'Đổi thể loại' )
				. '<div class="tam-than"><input type="hidden" name="action" value="phongdao_doi_the_loai"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="trang_doc" value="1">' . wp_nonce_field( 'phongdao_doi_the_loai_' . $id, '_wpnonce', true, false )
				. '<label for="tam-the-loai-moi">Thể loại mới</label><select id="tam-the-loai-moi" name="the_loai">' . $o . '</select>'
				. '<p class="notice">Đường dẫn của bài đổi theo thể loại mới; đường cũ tự chuyển sang đường mới (người đọc và Google không mất lối). Phần riêng của thể loại cũ vẫn giữ trong dữ liệu. Lịch sử ghi lại lần đổi.</p></div>'
				. '<footer class="tam-chan"><button type="button" class="btn" data-action="dong-tam">Huỷ</button><button type="submit" class="btn primary">Đổi thể loại</button></footer></form></dialog>';
		}
		return $ra;
	}

	/**
	 * Tấm "Đối chiếu sách" trên trang đọc (D-105, docs/32 mục 4.4): chọn sách hoặc "+ Sách mới", trang, bản quét; lời trong
	 * sách giống / khác (sửa lời theo sách hoặc ghi làm dị bản). Cùng đường lưu với trang Sửa tác phẩm
	 * (`phongdao_doi_chieu_sach`), có Lịch sử; xong quay lại trang đọc. Chỉ người điền, máy không tự điền sách, trang.
	 */
	public static function tam_doi_chieu( array $sua ): string {
		$id   = (int) $sua['id'];
		$loi  = $sua['phan']['loi'] ?? null;
		$dc   = (array) ( $loi['doi_chieu'] ?? array() );
		$sach = HoSo::sach();
		$hien = ! empty( $dc['ho_so'] ) ? 'Lời đã đối chiếu với «' . ( $sach[ (int) $dc['ho_so'] ] ?? (string) get_post_field( 'post_title', (int) $dc['ho_so'] ) ) . '»' . ( empty( $dc['trang'] ) ? '' : ', tr. ' . $dc['trang'] ) : 'Chưa đối chiếu sách.';
		$chon = '<option value="0">— Chọn sách —</option>';
		foreach ( $sach as $sid => $ten ) {
			$chon .= '<option value="' . (int) $sid . '"' . selected( (int) ( $dc['ho_so'] ?? 0 ), $sid, false ) . '>' . esc_html( $ten ) . '</option>';
		}
		$moi = current_user_can( 'cadao_duyet' ) || current_user_can( \Phongdao\GiaoTiep\QuanTri\NguonCaiDat::QUYEN )
			? '<details class="disclosure"><summary>+ Sách mới</summary><div class="detail-body"><p class="small muted">Tạo hồ sơ nguồn loại sách (giấy phép, xin phép do Quản trị điền sau).</p>'
				. '<label for="tdc-ten">Tên sách</label><input id="tdc-ten" name="sach_moi[ten]"><label for="tdc-tg">Người biên soạn</label><input id="tdc-tg" name="sach_moi[tac_gia]">'
				. '<label for="tdc-nxb">Nhà xuất bản</label><input id="tdc-nxb" name="sach_moi[nha_xuat_ban]"><label for="tdc-nam">Năm</label><input id="tdc-nam" name="sach_moi[nam]" inputmode="numeric"></div></details>'
			: '';
		return '<dialog class="tam-sua" id="tam-doi-chieu" aria-labelledby="tam-doi-chieu-tieu-de"><form method="post" class="tam-khung" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-gui-thuong>'
			. '<header class="tam-dau"><h2 id="tam-doi-chieu-tieu-de">Đối chiếu sách</h2><button type="button" class="nut-dong" data-action="dong-tam" aria-label="Đóng"><svg class="icon" aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></svg></button></header>'
			. '<div class="tam-than"><p class="small" role="status">' . esc_html( $hien ) . '</p>'
			. '<input type="hidden" name="action" value="phongdao_doi_chieu_sach"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="trang_doc" value="1">' . wp_nonce_field( 'phongdao_doi_chieu_' . $id, '_wpnonce', true, false )
			. '<label for="tdc-sach">Sách</label><select id="tdc-sach" name="ho_so">' . $chon . '</select>' . $moi
			. '<label for="tdc-trang">Trang</label><input id="tdc-trang" name="trang" value="' . esc_attr( (string) ( $dc['trang'] ?? '' ) ) . '">'
			. '<label for="tdc-url">Đường dẫn bản quét (không bắt buộc)</label><input id="tdc-url" name="url" type="url" value="' . esc_attr( (string) ( $dc['url'] ?? '' ) ) . '">'
			. '<fieldset><legend>Lời trong sách</legend><label class="choice"><input type="radio" name="lam" value="giu" checked> Giống bản đang đăng</label><label class="choice"><input type="radio" name="lam" value="sua"> Khác: sửa lời theo sách</label><label class="choice"><input type="radio" name="lam" value="di_ban"> Khác: ghi bản trong sách làm dị bản</label></fieldset>'
			. '<label for="tdc-loi">Lời trong sách (chỉ cần khi khác; gõ đúng như trong sách, mỗi dòng một câu)</label><textarea id="tdc-loi" name="loi_sach" rows="5">' . esc_textarea( implode( "\n", (array) ( $loi['dong'] ?? array() ) ) ) . '</textarea>'
			. '<p class="small muted">Người đọc sẽ thấy: Lời đã đối chiếu với «tên sách», tr. N.</p></div>'
			. '<footer class="tam-chan"><button type="button" class="btn" data-action="dong-tam">Huỷ</button><button type="submit" class="btn primary">Lưu đối chiếu</button></footer></form></dialog>';
	}

	/** Giờ UTC trong cơ sở dữ liệu → ngày theo giờ của site (dd/mm). */
	public static function ngay( string $utc ): string {
		$t = strtotime( $utc . ( strlen( $utc ) > 10 ? ' UTC' : '' ) );
		return $t ? wp_date( 'd/m', $t ) : $utc;
	}
}
