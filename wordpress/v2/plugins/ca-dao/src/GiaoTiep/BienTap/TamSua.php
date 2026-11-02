<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Tấm sửa một phần của bài (D-094, docs/28 mục 4.2; màn mẫu B02): máy chủ dựng form và đọc form gửi lên thành giá trị
 * phần. Mục máy chép mà người không đổi thì giữ nguyên (người làm, nguồn); người đổi chữ thì thành "người viết".
 * Ý nghĩa lưu khi chưa đánh "Tôi đã đọc và duyệt" thì thành nháp trong Hộp việc, người đọc chưa thấy.
 * Lằn ranh vẫn kiểm ở MienDuLieu\KiemPhan khi lưu (DichVu\LuuPhan).
 */
final class TamSua {

	/** Phần có tấm sửa => [tiêu đề tấm, kiểu form]. */
	public const TAM = array(
		'loi'         => array( 'Sửa lời', 'loi' ),
		'y_nghia'     => array( 'Sửa ý nghĩa', 'y_nghia' ),
		'nghia_den'   => array( 'Sửa nghĩa đen', 'chu_ai' ),
		'nghia_bong'  => array( 'Sửa nghĩa bóng', 'chu_ai' ),
		'dung_khi'    => array( 'Sửa "Dùng khi"', 'chu_ai' ),
		'goi_y'       => array( 'Sửa gợi ý', 'chu_ai' ),
		'ban_co_biet' => array( 'Sửa "Bạn có biết"', 'chu_ai' ),
		'cau_hoi'     => array( 'Sửa câu hỏi', 'chu' ),
		'dap_an'      => array( 'Sửa đáp án', 'chu' ),
		'co_nghi'     => array( 'Cờ "Nghi không phải dân gian"', 'co_nghi' ),
		'chu_thich'   => array( 'Sửa từ khó', 'chu_thich' ),
		'di_ban'      => array( 'Sửa dị bản', 'di_ban' ),
	);

	// ---------- Dựng form ----------

	/**
	 * @param array{ho_so:array<int,string>,sach:array<int,string>,duyet:bool} $ctx
	 */
	public static function ve( string $phan, ?array $cu, array $ctx ): string {
		[ $tieu_de, $kieu ] = self::TAM[ $phan ];
		$than               = match ( $kieu ) {
			'loi'       => self::form_loi( $cu, $ctx ),
			'y_nghia'   => self::form_y_nghia( $cu, $ctx ),
			'chu_ai'    => self::vung( "s-$phan", 'chu', 'dung_khi' === $phan ? 'Mỗi dòng một ví dụ' : 'Nội dung', (string) ( $cu['chu'] ?? '' ) ) . self::hop( "s-$phan-ai", 'ai', 'Có hỗ trợ của AI', ! empty( $cu['ai'] ) ) . ( $ctx['duyet'] ? '' : '<p class="small muted">Phần có hỗ trợ AI cần người có quyền duyệt lưu.</p>' ),
			'chu'       => self::vung( "s-$phan", 'chu', 'Nội dung', (string) ( $cu['chu'] ?? '' ), '', 3 ),
			'co_nghi'   => self::vung( "s-$phan", 'ly_do', 'Lý do (để trống = gỡ cờ)', (string) ( $cu['ly_do'] ?? '' ) ) . self::nguon( "s-$phan", 'nguon', $cu['nguon'] ?? array(), $ctx ),
			'chu_thich' => self::form_ds( $phan, $cu, $ctx ),
			'di_ban'    => self::form_ds( $phan, $cu, $ctx ),
		};
		$id = 'tam-' . $phan;
		return '<dialog class="tam-sua" id="' . $id . '" aria-labelledby="' . $id . '-tieu-de"><form method="post" class="tam-khung" data-phan="' . esc_attr( $phan ) . '">'
			. '<header class="tam-dau"><h2 id="' . $id . '-tieu-de">' . esc_html( $tieu_de ) . '</h2><button type="button" class="nut-dong" data-action="dong-tam" aria-label="Đóng"><svg class="icon" aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></svg></button></header>'
			. '<div class="tam-than">' . $than . '<p class="tam-loi" role="alert" hidden></p></div>'
			. '<footer class="tam-chan"><button type="button" class="btn" data-action="dong-tam">Huỷ</button><button type="submit" class="btn primary">Lưu</button></footer></form></dialog>';
	}

	private static function form_loi( ?array $cu, array $ctx ): string {
		$dc   = $cu['doi_chieu'] ?? array();
		$sach = array( 0 => '— Chưa đối chiếu —' ) + $ctx['sach'];
		return self::vung( 's-loi', 'dong', 'Lời · mỗi dòng một câu', implode( "\n", $cu['dong'] ?? array() ), 'dòng trống = ngắt khổ', 8 )
			. ( $cu ? '<div class="notice">Lời đang đăng: sửa sẽ ghi lịch sử, phần lời thành "người viết". Không sửa theo trí nhớ; cần có nguồn.</div>' : '' )
			. '<fieldset class="nhom-o"><legend>Lời đã đối chiếu với <span class="small muted">chỉ người điền, máy không tự điền</span></legend>'
			. self::chon( 's-dc-sach', 'doi_chieu[ho_so]', 'Sách', $sach, (int) ( $dc['ho_so'] ?? 0 ) )
			. ( $ctx['sach'] ? '' : '<p class="small muted">Chưa có hồ sơ sách nào. Thêm ở Nguồn &amp; cài đặt.</p>' )
			. self::o( 's-dc-trang', 'doi_chieu[trang]', 'Trang', (string) ( $dc['trang'] ?? '' ) )
			. self::o( 's-dc-url', 'doi_chieu[url]', 'Đường dẫn bản quét', (string) ( $dc['url'] ?? '' ), 'không bắt buộc', 'url' ) . '</fieldset>';
	}

	private static function form_y_nghia( ?array $cu, array $ctx ): string {
		return self::vung( 's-y-nghia', 'chu', 'Ý nghĩa · 2–3 câu ngắn', (string) ( $cu['chu'] ?? '' ) )
			. self::hop( 's-y-nghia-ai', 'ai', 'Có hỗ trợ của AI', ! empty( $cu['ai'] ) )
			. ( $ctx['duyet'] ? self::hop( 's-y-nghia-duyet', 'duyet', 'Tôi đã đọc và duyệt phần này', false ) : '' )
			. '<p class="small muted">Lưu khi chưa đánh "đã duyệt" thì ý nghĩa ở dạng nháp trong Hộp việc, người đọc chưa thấy. Để trống và đánh "đã duyệt" = gỡ ý nghĩa.</p>';
	}

	/** Danh sách mục (từ khó, dị bản): mỗi mục một khối; thêm bằng mẫu, xoá bằng ô đánh dấu. */
	private static function form_ds( string $phan, ?array $cu, array $ctx ): string {
		$o = '';
		foreach ( array_values( $cu['muc'] ?? array() ) as $i => $m ) {
			$o .= self::muc( $phan, (string) $i, $m, $ctx );
		}
		$mau = self::muc( $phan, '__i__', array(), $ctx );
		return '<ol class="ds-sua" data-ds>' . $o . '</ol><template data-mau>' . $mau . '</template>'
			. '<button type="button" class="btn" data-action="them-muc">+ Thêm ' . ( 'di_ban' === $phan ? 'dị bản' : 'từ khó' ) . '</button>';
	}

	private static function muc( string $phan, string $i, array $m, array $ctx ): string {
		$p     = "muc[$i]";
		$id    = 's-' . $phan . '-' . $i;
		$goc   = '__i__' === $i ? '' : $i;
		$nguoi = isset( $m['nguoi_lam'] ) ? array( NguoiLam::MAY_CHEP => 'máy chép', NguoiLam::NGUOI_VIET => 'người viết', NguoiLam::DOC_GIA_GOP => 'người đọc góp' )[ $m['nguoi_lam'] ] ?? '' : '';
		$than  = 'di_ban' === $phan
			? self::vung( "$id-dong", $p . '[dong]', 'Lời', implode( "\n", $m['dong'] ?? array() ), '', 4 )
				. '<div class="field-grid">' . self::o( "$id-noi", $p . '[noi]', 'Nơi', (string) ( $m['noi'] ?? '' ), 'không bắt buộc' ) . self::o( "$id-ke", $p . '[nguoi_ke]', 'Người kể', (string) ( $m['nguoi_ke'] ?? '' ), 'cần đồng ý' ) . self::o( "$id-nam", $p . '[nam]', 'Năm', (string) ( $m['nam'] ?? '' ) ) . '</div>'
			: '<div class="field-grid">' . self::o( "$id-tu", $p . '[tu]', 'Từ trong lời', (string) ( $m['tu'] ?? '' ), 'để trống = chú thích chung' ) . '</div>'
				. self::vung( "$id-giai", $p . '[loi_giai]', 'Lời giải', (string) ( $m['loi_giai'] ?? '' ), '', 3 );
		return '<li class="muc-sua"><input type="hidden" name="' . $p . '[goc]" value="' . esc_attr( $goc ) . '">'
			. ( '' === $nguoi ? '' : '<p class="small muted">' . esc_html( 'Hiện: ' . $nguoi ) . '</p>' )
			. $than . self::nguon( $id, $p . '[nguon]', $m['nguon'] ?? array(), $ctx )
			. self::hop( "$id-xoa", $p . '[xoa]', 'Xoá mục này', false ) . '</li>';
	}

	/** Nguồn của một mục: hồ sơ nguồn, trang, đường dẫn — trống = chưa rõ (không điền sẵn gì). */
	private static function nguon( string $id, string $ten, array $n, array $ctx ): string {
		return '<details class="disclosure"><summary>Nguồn' . ( $n ? ' · ' . esc_html( $ctx['ho_so'][ (int) ( $n['ho_so'] ?? 0 ) ] ?? 'đã ghi' ) : '' ) . '</summary><div class="detail-body field-grid">'
			. self::chon( "$id-hs", $ten . '[ho_so]', 'Hồ sơ nguồn', array( 0 => '— Chưa rõ —' ) + $ctx['ho_so'], (int) ( $n['ho_so'] ?? 0 ) )
			. self::o( "$id-tr", $ten . '[trang]', 'Trang', (string) ( $n['trang'] ?? '' ) )
			. self::o( "$id-url", $ten . '[url]', 'Đường dẫn', (string) ( $n['url'] ?? '' ), '', 'url' ) . '</div></details>';
	}

	private static function o( string $id, string $ten, string $nhan, string $gt, string $goi_y = '', string $kieu = 'text' ): string {
		return '<div class="o-nhap"><label for="' . esc_attr( $id ) . '">' . esc_html( $nhan ) . ( '' === $goi_y ? '' : ' <span class="small muted">' . esc_html( $goi_y ) . '</span>' ) . '</label><input id="' . esc_attr( $id ) . '" name="' . esc_attr( $ten ) . '" type="' . esc_attr( $kieu ) . '" value="' . esc_attr( $gt ) . '"></div>';
	}

	private static function vung( string $id, string $ten, string $nhan, string $gt, string $goi_y = '', int $dong = 5 ): string {
		return '<div class="o-nhap"><label for="' . esc_attr( $id ) . '">' . esc_html( $nhan ) . ( '' === $goi_y ? '' : ' <span class="small muted">' . esc_html( $goi_y ) . '</span>' ) . '</label><textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $ten ) . '" rows="' . $dong . '">' . esc_textarea( $gt ) . '</textarea></div>';
	}

	private static function chon( string $id, string $ten, string $nhan, array $ds, int $chon ): string {
		$o = '';
		foreach ( $ds as $gt => $chu ) {
			$o .= '<option value="' . (int) $gt . '"' . selected( (int) $gt, $chon, false ) . '>' . esc_html( $chu ) . '</option>';
		}
		return '<div class="o-nhap"><label for="' . esc_attr( $id ) . '">' . esc_html( $nhan ) . '</label><select id="' . esc_attr( $id ) . '" name="' . esc_attr( $ten ) . '">' . $o . '</select></div>';
	}

	private static function hop( string $id, string $ten, string $nhan, bool $chon ): string {
		return '<label class="choice" for="' . esc_attr( $id ) . '"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $ten ) . '" value="1"' . checked( $chon, true, false ) . '>' . esc_html( $nhan ) . '</label>';
	}

	// ---------- Đọc form ----------

	/**
	 * Form gửi lên → giá trị phần (null = xoá) hoặc nháp (ý nghĩa chưa duyệt).
	 *
	 * @return array{gia_tri:?array,nhap:?array}
	 */
	public static function doc( string $phan, array $f, ?array $cu ): array {
		$kieu = self::TAM[ $phan ][1] ?? '';
		$chu  = static fn( $v ): string => trim( str_replace( "\r", '', (string) $v ) );
		switch ( $kieu ) {
			case 'loi':
				$dong = explode( "\n", $chu( $f['dong'] ?? '' ) );
				$doi  = array_map( 'trim', $dong ) !== array_map( 'trim', $cu['dong'] ?? array() );
				$v    = array_diff_key( $cu ?? array(), array( 'doi_chieu' => 1 ) );
				$v['dong']      = $dong;
				$v['nguoi_lam'] = $doi || empty( $cu['nguoi_lam'] ) ? NguoiLam::NGUOI_VIET : $cu['nguoi_lam'];
				$hs             = (int) ( $f['doi_chieu']['ho_so'] ?? 0 );
				if ( $hs > 0 ) {
					$v['doi_chieu'] = array_filter( array( 'ho_so' => $hs, 'trang' => $chu( $f['doi_chieu']['trang'] ?? '' ), 'url' => $chu( $f['doi_chieu']['url'] ?? '' ) ) );
				}
				return array( 'gia_tri' => $v, 'nhap' => null );
			case 'y_nghia':
				$y = array( 'chu' => $chu( $f['chu'] ?? '' ), 'ai' => ! empty( $f['ai'] ) );
				if ( empty( $f['duyet'] ) ) {
					return array( 'gia_tri' => $cu, 'nhap' => '' === $y['chu'] ? null : $y );
				}
				return array( 'gia_tri' => '' === $y['chu'] ? null : $y + array( 'nguoi_lam' => $y['ai'] ? NguoiLam::AI_DA_DUYET : NguoiLam::NGUOI_VIET ), 'nhap' => null );
			case 'chu_ai':
				$v = $chu( $f['chu'] ?? '' );
				if ( '' === $v ) {
					return array( 'gia_tri' => null, 'nhap' => null );
				}
				$ai = ! empty( $f['ai'] );
				return array( 'gia_tri' => array( 'chu' => $v, 'ai' => $ai, 'nguoi_lam' => $ai ? NguoiLam::AI_DA_DUYET : ( $v === ( $cu['chu'] ?? null ) && empty( $cu['ai'] ) ? ( $cu['nguoi_lam'] ?? NguoiLam::NGUOI_VIET ) : NguoiLam::NGUOI_VIET ) ) + ( $v === ( $cu['chu'] ?? null ) && isset( $cu['nguon'] ) ? array( 'nguon' => $cu['nguon'] ) : array() ), 'nhap' => null );
			case 'chu':
				$v = $chu( $f['chu'] ?? '' );
				if ( '' === $v ) {
					return array( 'gia_tri' => null, 'nhap' => null );
				}
				return array( 'gia_tri' => $v === ( $cu['chu'] ?? null ) ? $cu : array( 'chu' => $v, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), 'nhap' => null );
			case 'co_nghi':
				$v = $chu( $f['ly_do'] ?? '' );
				return array( 'gia_tri' => '' === $v ? null : array_filter( array( 'ly_do' => $v, 'nguoi_lam' => NguoiLam::NGUOI_VIET, 'nguon' => self::doc_nguon( $f['nguon'] ?? array() ) ) ), 'nhap' => null );
			case 'chu_thich':
			case 'di_ban':
				return array( 'gia_tri' => self::doc_ds( $phan, (array) ( $f['muc'] ?? array() ), $cu ), 'nhap' => null );
		}
		return array( 'gia_tri' => $cu, 'nhap' => null );
	}

	private static function doc_ds( string $phan, array $hang, ?array $cu ): ?array {
		$cu_muc = array_values( $cu['muc'] ?? array() );
		$ra     = array();
		foreach ( $hang as $k => $h ) {
			if ( '__i__' === (string) $k || ! is_array( $h ) || ! empty( $h['xoa'] ) ) {
				continue;
			}
			$goc   = '' === (string) ( $h['goc'] ?? '' ) ? null : ( $cu_muc[ (int) $h['goc'] ] ?? null );
			$nguon = self::doc_nguon( $h['nguon'] ?? array() );
			// So nguồn theo đúng các ô có trên form (hồ sơ, trang, đường dẫn)
			$nguon_goc = isset( $goc['nguon'] ) ? self::doc_nguon( array_intersect_key( $goc['nguon'], array( 'ho_so' => 1, 'trang' => 1, 'url' => 1 ) ) ) : null;
			$goc_so = null;
			if ( $goc ) {
				$goc_so          = $goc;
				$goc_so['nguon'] = $nguon_goc;
			}
			if ( 'di_ban' === $phan ) {
				$dong = array_values( array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", '', (string) ( $h['dong'] ?? '' ) ) ) ), 'strlen' ) );
				if ( ! $dong ) {
					continue;
				}
				$moi = array_filter( array( 'dong' => $dong, 'noi' => trim( (string) ( $h['noi'] ?? '' ) ), 'nguoi_ke' => trim( (string) ( $h['nguoi_ke'] ?? '' ) ), 'nam' => trim( (string) ( $h['nam'] ?? '' ) ), 'nguon' => $nguon ) );
				$so  = array_filter( array( 'dong' => array_values( array_filter( array_map( 'trim', $goc['dong'] ?? array() ), 'strlen' ) ), 'noi' => $goc['noi'] ?? '', 'nguoi_ke' => $goc['nguoi_ke'] ?? '', 'nam' => $goc['nam'] ?? '', 'nguon' => $goc_so['nguon'] ?? null ) );
			} else {
				$giai = trim( str_replace( "\r", '', (string) ( $h['loi_giai'] ?? '' ) ) );
				if ( '' === $giai ) {
					continue;
				}
				$moi = array_filter( array( 'tu' => trim( (string) ( $h['tu'] ?? '' ) ), 'loi_giai' => $giai, 'nguon' => $nguon ) );
				$so  = array_filter( array( 'tu' => trim( (string) ( $goc['tu'] ?? '' ) ), 'loi_giai' => trim( (string) ( $goc['loi_giai'] ?? '' ) ), 'nguon' => $goc_so['nguon'] ?? null ) );
			}
			// Không đổi gì → giữ nguyên mục cũ (máy chép giữ người làm, nguồn); đổi → người viết
			$ra[] = $goc && $moi == $so ? $goc : $moi + array( 'nguoi_lam' => NguoiLam::NGUOI_VIET );
		}
		return $ra ?: null;
	}

	/** @return array<string,mixed>|null */
	private static function doc_nguon( mixed $n ): ?array {
		if ( ! is_array( $n ) ) {
			return null;
		}
		$ra = array_filter(
			array(
				'ho_so' => (int) ( $n['ho_so'] ?? 0 ),
				'trang' => trim( (string) ( $n['trang'] ?? '' ) ),
				'url'   => trim( (string) ( $n['url'] ?? '' ) ),
			)
		);
		return $ra ?: null;
	}
}
