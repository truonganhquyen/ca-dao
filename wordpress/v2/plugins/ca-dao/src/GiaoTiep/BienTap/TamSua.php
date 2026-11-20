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
		'hinh_anh'    => array( 'Sửa "Hình ảnh trong bài"', 'chu_ai' ),
		'media'       => array( 'Ảnh minh hoạ', 'media' ),
		'goi_y'       => array( 'Sửa gợi ý', 'chu_ai' ),
		'ban_co_biet' => array( 'Sửa "Bạn có biết"', 'chu_ai' ),
		'cau_hoi'     => array( 'Sửa câu hỏi', 'chu' ),
		'dap_an'      => array( 'Sửa đáp án', 'chu' ),
		'co_nghi'     => array( 'Cờ "Có thể không phải dân gian"', 'co_nghi' ),
		'tho_tuc'     => array( 'Từ ngữ thô tục', 'tho_tuc' ),
		'chu_thich'   => array( 'Sửa từ khó', 'chu_thich' ),
		'chuyen_quanh' => array( 'Sửa chuyện quanh bài', 'chuyen_quanh' ),
		'gan_nghia'   => array( 'Câu gần nghĩa · trái nghĩa', 'gan_nghia' ),
		'di_ban'      => array( 'Sửa dị bản', 'di_ban' ),
		'dong_gop'    => array( 'Ghi công và nguồn bạn đọc', 'ghi_cong' ),
		'boi_canh'    => array( 'Bối cảnh của bài vè', 'boi_canh' ),
		'doan'        => array( 'Chia đoạn', 'doan' ),
	);

	/**
	 * Tên tấm sửa theo thể loại: giải nghĩa của câu đố là "Giải thích đáp án" (cùng chỗ lưu với Ý nghĩa, D-105); Chuyện quanh
	 * bài của tục ngữ gọi là "Nguồn gốc" (D-119).
	 */
	public static function ten( string $phan, string $the_loai = '' ): string {
		if ( 'chuyen_quanh' === $phan && 'tuc-ngu' === $the_loai ) {
			return 'Sửa nguồn gốc';
		}
		return 'y_nghia' === $phan && 'cau-do' === $the_loai ? 'Sửa giải thích đáp án' : (string) ( self::TAM[ $phan ][0] ?? $phan );
	}

	/** Tên phần Chuyện quanh bài cho người đọc: tục ngữ là "Nguồn gốc". */
	public static function ten_chuyen_quanh( string $the_loai ): string {
		return 'tuc-ngu' === $the_loai ? 'Nguồn gốc' : 'Chuyện quanh bài';
	}

	public static function nhan( string $phan, string $the_loai = '' ): string {
		$ten = self::ten( $phan, $the_loai );
		$ten = str_starts_with( $ten, 'Sửa ' ) ? mb_substr( $ten, 4 ) : $ten;
		return mb_strtoupper( mb_substr( $ten, 0, 1 ) ) . mb_substr( $ten, 1 );
	}

	/** Chỉ đổi nhãn của ô nội dung đang sửa, giữ nguyên quy tắc kiểm và các trường nguồn. */
	public static function nhan_loi( string $phan, array $loi, string $the_loai ): array {
		$ten = self::nhan( $phan, $the_loai );
		return array_map( static fn( string $l ): string => strtr( $l, array( $phan . '.chu' => $ten, $phan . '.dong' => $ten ) ), $loi );
	}

	// ---------- Dựng form ----------

	/**
	 * @param array{ho_so:array<int,string>,sach:array<int,string>,duyet:bool} $ctx
	 */
	public static function ve( string $phan, ?array $cu, array $ctx ): string {
		$tieu_de = self::ten( $phan, (string) ( $ctx['the_loai'] ?? '' ) );
		$than    = self::than( $phan, $cu, $ctx );
		$id = 'tam-' . $phan;
		return '<dialog class="tam-sua" id="' . $id . '" aria-labelledby="' . $id . '-tieu-de"><form method="post" class="tam-khung" data-phan="' . esc_attr( $phan ) . '" data-giu-nhap="' . (int) ( $ctx['id'] ?? 0 ) . ':' . esc_attr( $phan ) . '" data-phien-soan="' . esc_attr( NhapDo::phien() ) . '">'
			. '<input type="hidden" name="phien_phan" value="' . esc_attr( \Phongdao\DichVu\LuuPhan\LuuPhan::phien( (int) ( $ctx['id'] ?? 0 ), $phan, $cu ) ) . '">'
			. '<header class="tam-dau"><h2 id="' . $id . '-tieu-de">' . esc_html( $tieu_de ) . '</h2><button type="button" class="nut-dong" data-action="dong-tam" aria-label="Đóng"><svg class="icon" aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></svg></button></header>'
			. '<div class="tam-than">' . $than . '<p class="tam-loi" role="alert" hidden></p></div>'
			. '<footer class="tam-chan"><button type="button" class="btn" data-action="dong-tam">Huỷ</button>' . self::nut_luu( $phan, $ctx, 'btn primary', 'btn' ) . '</footer></form></dialog>';
	}

	/**
	 * Nút lưu của một phần (D-102). Ý nghĩa: "Lưu nháp" (vào Hộp việc, người đọc chưa thấy) và — chỉ người có quyền
	 * duyệt — "Lưu và hiện cho người đọc". Người vẫn là người bấm; không còn ô "đã duyệt" dễ quên.
	 */
	public static function nut_luu( string $phan, array $ctx, string $lop_chinh, string $lop_phu, string $nhan = 'Lưu' ): string {
		if ( 'y_nghia' === ( self::TAM[ $phan ][1] ?? '' ) ) {
			$tt = $ctx['trang_thai'] ?? 'publish';
			$hien = 'publish' === $tt ? 'Lưu và hiện cho người đọc' : ( 'draft' === $tt ? 'Lưu phần đã duyệt vào bài nháp' : 'Lưu phần đã duyệt vào bài' );
			return ! empty( $ctx['duyet'] )
				? '<button type="submit" name="duyet" value="" class="' . esc_attr( $lop_phu ) . '">Lưu nháp vào Hộp việc</button><button type="submit" name="duyet" value="1" class="' . esc_attr( $lop_chinh ) . '">' . esc_html( $hien ) . '</button>'
				: '<button type="submit" class="' . esc_attr( $lop_chinh ) . '">Lưu nháp</button>';
		}
		return '<button type="submit" class="' . esc_attr( $lop_chinh ) . '">' . esc_html( $nhan ) . '</button>';
	}

	/** Cùng trường nhập của tấm sửa trang đọc và biểu mẫu wp-admin. */
	public static function than( string $phan, ?array $cu, array $ctx ): string {
		$kieu = self::TAM[ $phan ][1];
		$rich = ! empty( $ctx['sua_day_du'] ) && null !== \Phongdao\MienDuLieu\DinhDang::truong( $phan ) ? ( $cu ?? array() ) : null;
		return match ( $kieu ) {
			'ghi_cong'  => self::form_ghi_cong( $cu ),
			'media'     => AnhMinhHoa::form( $cu ),
			'loi'       => self::form_loi( $cu, $ctx ),
			'y_nghia'   => self::form_y_nghia( $cu, $ctx ),
			'chu_ai'    => self::vung( "s-$phan", 'chu', array( 'dung_khi' => 'Mỗi dòng một ví dụ', 'hinh_anh' => 'Mỗi dòng một cặp: hình ảnh → điều được nói tới (vd. tấm lụa đào → thân phận người con gái)' )[ $phan ] ?? 'Nội dung', (string) ( $cu['chu'] ?? '' ), '', 5, $rich ) . self::hop( "s-$phan-ai", 'ai', 'Có hỗ trợ của AI', ! empty( $cu['ai'] ) ) . ( $ctx['duyet'] ? '' : '<p class="small muted">Phần có hỗ trợ AI cần người có quyền duyệt lưu.</p>' ),
			'chu'       => self::vung( "s-$phan", 'chu', 'Nội dung', (string) ( $cu['chu'] ?? '' ), '', 3, $rich ),
			'co_nghi'   => self::vung( "s-$phan", 'ly_do', 'Lý do (để trống = gỡ cờ)', (string) ( $cu['ly_do'] ?? '' ) ) . self::nguon( "s-$phan", 'nguon', $cu['nguon'] ?? array(), $ctx ),
			'tho_tuc'   => self::hop( "s-$phan", 'bat', 'Bài có từ ngữ thô tục', null !== $cu ),
			'chu_thich' => self::form_ds( $phan, $cu, $ctx ),
			'chuyen_quanh' => self::form_ds( $phan, $cu, $ctx ),
			'gan_nghia' => self::form_gan_nghia( $cu, $ctx ),
			'di_ban'    => self::form_ds( $phan, $cu, $ctx ),
			'boi_canh'  => self::vung( 's-bc-chu', 'chu', 'Lời giới thiệu', (string) ( $cu['chu'] ?? '' ), 'hiện ngay trên lời', 3, $rich )
				. '<div class="field-grid">' . self::o( 's-bc-su-kien', 'su_kien', 'Sự kiện', (string) ( $cu['su_kien'] ?? '' ), 'không bắt buộc' ) . self::o( 's-bc-nam', 'nam', 'Năm', (string) ( $cu['nam'] ?? '' ) ) . self::o( 's-bc-noi', 'noi', 'Nơi', (string) ( $cu['noi'] ?? '' ) ) . '</div>'
				. self::nguon( 's-bc', 'nguon', $cu['nguon'] ?? array(), $ctx ) . '<p class="small muted">Để trống hết = gỡ bối cảnh.</p>',
			'doan'      => self::form_doan( $cu, $ctx ),
		};
	}

	private static function form_ghi_cong( ?array $cu ): string {
		$o = '<p class="small muted">Chọn hiện hoặc ẩn bút danh người gửi đã đồng ý; có thể sửa nguồn công khai hoặc gỡ dòng ghi công. Nguồn chưa đối chiếu, bản gửi riêng tư và lịch sử được giữ. Không chuyển chứng cứ sang lời khác.</p>';
		foreach ( $cu['muc'] ?? array() as $i => $m ) {
			$id = 'gop-credit-' . $i; $p = 'muc[' . $i . ']';
			$ten = \Phongdao\DichVu\NguoiDoc\DuyetTacPham::ten_da_dong_y( $m );
			$o .= '<fieldset><legend>' . esc_html( ( 'loi' === $m['phan'] ? 'Lời chính' : 'Dị bản' ) . ' · ' . ( ( $m['ten'] ?? '' ) ?: 'Bạn đọc ẩn danh' ) ) . '</legend>'
				. self::vung( $id . '-nguon', $p . '[xuat_xu]', 'Nguồn công khai — không ghi thông tin cá nhân', $m['xuat_xu'] ?? '', '', 2 )
				. ( '' === $ten ? '<p class="small muted">Không có bút danh được đồng ý trong bản gửi để bật ghi công.</p>' : '<input type="hidden" name="' . $p . '[hien_ten]" value="0">' . self::hop( $id . '-ten', $p . '[hien_ten]', 'Hiện bút danh “' . $ten . '” — người gửi đã đồng ý', ! empty( $m['ten'] ) ) )
				. ( '' === $ten && ! empty( $m['ten'] ) ? self::hop( $id . '-an-ten', $p . '[an_ten]', 'Ẩn bút danh trên dòng này', false ) : '' )
				. self::hop( $id . '-xoa', $p . '[xoa]', 'Gỡ dòng ghi công khỏi trang đọc', false ) . '</fieldset>';
		}
		return $o;
	}

	private static function form_loi( ?array $cu, array $ctx ): string {
		$dc   = $cu['doi_chieu'] ?? array();
		$sach = array( 0 => '— Chưa đối chiếu —' ) + $ctx['sach'];
		$tren = self::vung( 's-loi', 'dong', 'Lời · mỗi dòng một câu', implode( "\n", $cu['dong'] ?? array() ), 'dòng trống = ngắt khổ', 8 )
			. ( $cu ? '<div class="notice inline"><p>' . ( 'publish' === ( $ctx['trang_thai'] ?? '' ) ? 'Lời của bài đã đăng' : 'Lời đang lưu trong bài chưa công khai' ) . ': sửa sẽ ghi lịch sử, phần lời thành "người viết". Không sửa theo trí nhớ; cần có nguồn.</p></div>' : '' )
			. ( empty( $ctx['phan']['doan'] ) ? '' : '<p class="small muted">Bài đã chia ' . count( $ctx['phan']['doan']['muc'] ?? array() ) . ' đoạn theo số dòng: thêm hay bớt dòng thì sửa lại ở "Sửa đoạn".</p>' );
		// Trang Sửa tác phẩm có nút riêng "Đối chiếu sách" (D-104 ý 6): ô Sửa lời không còn phần đối chiếu (giữ nguyên khi lưu lời)
		if ( ! empty( $ctx['an_doi_chieu'] ) ) {
			return $tren;
		}
		return $tren
			. '<fieldset class="nhom-o"><legend>Lời đã đối chiếu với <span class="small muted">chỉ người điền, máy không tự điền</span></legend>'
			. self::chon( 's-dc-sach', 'doi_chieu[ho_so]', 'Sách', $sach, (int) ( $dc['ho_so'] ?? 0 ) )
			. ( $ctx['sach'] ? '' : '<p class="small muted">Chưa có hồ sơ sách nào. Thêm ở Nguồn &amp; cài đặt.</p>' )
			. self::o( 's-dc-trang', 'doi_chieu[trang]', 'Trang', (string) ( $dc['trang'] ?? '' ) )
			. self::o( 's-dc-url', 'doi_chieu[url]', 'Đường dẫn bản quét', (string) ( $dc['url'] ?? '' ), 'không bắt buộc', 'url' ) . '</fieldset>';
	}

	private static function form_y_nghia( ?array $cu, array $ctx ): string {
		$ten = 'cau-do' === ( $ctx['the_loai'] ?? '' ) ? 'Giải thích đáp án' : 'Ý nghĩa';
		return self::vung( 's-y-nghia', 'chu', $ten . ' · 2–3 câu ngắn', (string) ( $cu['chu'] ?? '' ), '', 5, ! empty( $ctx['sua_day_du'] ) ? ( $cu ?? array() ) : null )
			. self::hop( 's-y-nghia-ai', 'ai', 'Có hỗ trợ của AI', ! empty( $cu['ai'] ) )
			. ( $ctx['duyet']
				? '<p class="small muted">"Lưu nháp": phần này vào Hộp việc, người đọc chưa thấy. "Lưu và hiện cho người đọc": cập nhật phần này; khách thấy khi bài đã đăng. Để trống rồi bấm "Lưu và hiện cho người đọc" = gỡ phần này.</p>'
				: '<p class="small muted">Phần này lưu thành nháp trong Hộp việc; người có quyền duyệt xem rồi mới hiện cho người đọc.</p>' );
	}

	/** Đoạn của bài vè dài: tiêu đề + từ dòng, đến dòng (đếm dòng có chữ). */
	private static function form_doan( ?array $cu, array $ctx ): string {
		$o = '';
		foreach ( array_values( $cu['muc'] ?? array() ) as $i => $m ) {
			$o .= self::muc_doan( (string) $i, $m );
		}
		return '<p class="small muted">Dòng đếm từ 1, chỉ đếm dòng có chữ — lời hiện có ' . (int) ( $ctx['so_dong'] ?? 0 ) . ' dòng. Dòng nằm ngoài mọi đoạn vẫn hiện (thành đoạn không tiêu đề). Bài từ 41 dòng mà không chia thì tự chia theo khổ.</p>'
			. '<ol class="ds-sua" data-ds>' . $o . '</ol><template data-mau>' . self::muc_doan( '__i__', array() ) . '</template>'
			. '<button type="button" class="btn" data-action="them-muc">+ Thêm đoạn</button>';
	}

	private static function muc_doan( string $i, array $m ): string {
		$p  = "muc[$i]";
		$id = 's-doan-' . $i;
		return '<li class="muc-sua">' . self::o( "$id-ten", $p . '[tieu_de]', 'Tiêu đề đoạn', (string) ( $m['tieu_de'] ?? '' ), 'không bắt buộc' )
			. '<div class="field-grid">' . self::o( "$id-tu", $p . '[tu_dong]', 'Từ dòng', (string) ( $m['tu_dong'] ?? '' ), '', 'number' ) . self::o( "$id-den", $p . '[den_dong]', 'Đến dòng', (string) ( $m['den_dong'] ?? '' ), '', 'number' ) . '</div>'
			. self::hop( "$id-xoa", $p . '[xoa]', 'Xoá đoạn này', ! empty( $m['xoa'] ) ) . '</li>';
	}

	/** Danh sách mục (từ khó, dị bản): mỗi mục một khối; thêm bằng mẫu, xoá bằng ô đánh dấu. */
	private static function form_ds( string $phan, ?array $cu, array $ctx ): string {
		$o = '';
		foreach ( array_values( $cu['muc'] ?? array() ) as $i => $m ) {
			$o .= self::muc( $phan, (string) $i, $m, $ctx );
		}
		$mau = self::muc( $phan, '__i__', array(), $ctx );
		$them = '<button type="button" class="btn" data-action="them-muc">+ Thêm ' . ( array( 'di_ban' => 'dị bản', 'chuyen_quanh' => 'mục' )[ $phan ] ?? 'từ khó' ) . '</button>';
		$dau  = 'chuyen_quanh' === $phan ? '<p class="small muted">Giai thoại, điển tích, chuyện về bài. Chú thích sẵn có chuyển sang ở "Sửa từ khó" (giữ người làm và nguồn của mục).</p>' : '';
		$loc = ! empty( $ctx['sua_day_du'] ) ? '<div class="phongdao-ds-cong-cu">' . $them . '<label data-chi-js hidden>Lọc mục trong phần này <input type="search" data-loc-hang autocomplete="off"></label><button type="button" class="button" data-mo-hang data-chi-js hidden>Mở tất cả</button><button type="button" class="button" data-gap-hang data-chi-js hidden>Gập tất cả</button></div>' : '';
		$khong_js = ! empty( $ctx['sua_day_du'] ) ? '<noscript><p>Thêm một mục mới bên dưới rồi lưu; tải lại có thể thêm mục tiếp theo.</p><ol class="ds-sua">' . self::muc( $phan, (string) count( $cu['muc'] ?? array() ), array( '_goc_form' => '' ), $ctx ) . '</ol></noscript>' : '';
		return $dau . $loc . '<ol class="ds-sua" data-ds>' . $o . '</ol><template data-mau>' . $mau . '</template>' . $khong_js . $them;
	}

	/**
	 * Câu gần nghĩa · trái nghĩa (D-119): câu đã nối hiện tên và đường dẫn; câu mới dán đường dẫn bài, số bài hoặc tên bài
	 * đúng từng chữ (cùng cách với màn Tuyển tập). Máy không tự nối. "Ghi cả ở câu kia" thêm chiều ngược lại ở câu tục ngữ kia.
	 */
	private static function form_gan_nghia( ?array $cu, array $ctx ): string {
		$o = '';
		foreach ( array_values( $cu['muc'] ?? array() ) as $i => $m ) {
			$o .= self::muc_gan_nghia( (string) $i, $m );
		}
		$khong_js = ! empty( $ctx['sua_day_du'] ) ? '<noscript><p>Thêm một câu bên dưới rồi lưu; tải lại có thể thêm câu tiếp theo.</p><ol class="ds-sua">' . self::muc_gan_nghia( (string) count( $cu['muc'] ?? array() ), array() ) . '</ol></noscript>' : '';
		return '<p class="small muted">Chọn câu gần nghĩa hoặc trái nghĩa với câu này; máy không tự nối. Câu mới: dán đường dẫn bài, số bài, hoặc tên bài đúng từng chữ.</p>'
			. '<ol class="ds-sua" data-ds>' . $o . '</ol><template data-mau>' . self::muc_gan_nghia( '__i__', array() ) . '</template>' . $khong_js
			. '<button type="button" class="btn" data-action="them-muc">+ Thêm câu</button>'
			. self::hop( 's-gn-hai-chieu', 'hai_chieu', 'Ghi cả ở câu kia (hai câu trỏ nhau; bỏ nối chỉ bỏ ở câu này)', true );
	}

	private static function muc_gan_nghia( string $i, array $m ): string {
		$p   = "muc[$i]";
		$id  = 's-gan_nghia-' . $i;
		$bai = (int) ( $m['tac_pham_id'] ?? 0 );
		$cau = $bai
			? '<p class="gn-cau">' . esc_html( (string) get_the_title( $bai ) ) . ' <span class="small muted">· #' . $bai . ( 'publish' === get_post_status( $bai ) ? '' : ' · chưa đăng' ) . '</span></p><input type="hidden" name="' . $p . '[tac_pham_id]" value="' . $bai . '">'
			: self::o( "$id-cau", $p . '[cau]', 'Câu', (string) ( $m['cau'] ?? '' ), 'đường dẫn, số bài hoặc tên bài' );
		return '<li class="muc-sua">' . $cau
			. self::chon_chu( "$id-qh", $p . '[quan_he]', 'Quan hệ', array( 'gan' => 'Gần nghĩa', 'trai' => 'Trái nghĩa' ), (string) ( $m['quan_he'] ?? 'gan' ) )
			. ( $bai ? self::hop( "$id-xoa", $p . '[xoa]', 'Bỏ nối câu này', ! empty( $m['xoa'] ) ) : '' ) . '</li>';
	}

	private static function chon_chu( string $id, string $ten, string $nhan, array $ds, string $chon ): string {
		$o = '';
		foreach ( $ds as $gt => $chu ) {
			$o .= '<option value="' . esc_attr( (string) $gt ) . '"' . selected( (string) $gt, $chon, false ) . '>' . esc_html( $chu ) . '</option>';
		}
		return '<div class="o-nhap"><label for="' . esc_attr( $id ) . '">' . esc_html( $nhan ) . '</label><select id="' . esc_attr( $id ) . '" name="' . esc_attr( $ten ) . '">' . $o . '</select></div>';
	}

	private static function muc( string $phan, string $i, array $m, array $ctx ): string {
		$p     = "muc[$i]";
		$id    = 's-' . $phan . '-' . $i;
		$goc   = (string) ( $m['_goc_form'] ?? ( '__i__' === $i ? '' : $i ) );
		$nguoi = isset( $m['nguoi_lam'] ) ? array( NguoiLam::MAY_CHEP => 'máy chép', NguoiLam::NGUOI_VIET => 'người viết', NguoiLam::DOC_GIA_GOP => 'người đọc góp' )[ $m['nguoi_lam'] ] ?? '' : '';
		$than  = match ( $phan ) {
			'di_ban'       => self::vung( "$id-dong", $p . '[dong]', 'Lời', implode( "\n", $m['dong'] ?? array() ), '', 4 )
				. '<div class="field-grid">' . self::o( "$id-noi", $p . '[noi]', 'Nơi', (string) ( $m['noi'] ?? '' ), 'không bắt buộc' ) . self::o( "$id-ke", $p . '[nguoi_ke]', 'Người kể', (string) ( $m['nguoi_ke'] ?? '' ), 'cần đồng ý' ) . self::o( "$id-nam", $p . '[nam]', 'Năm', (string) ( $m['nam'] ?? '' ) ) . '</div>',
			'chuyen_quanh' => '<div class="field-grid">' . self::o( "$id-ten", $p . '[tieu_de]', 'Tên chuyện', (string) ( $m['tieu_de'] ?? '' ), 'không bắt buộc; vd. tên điển tích' ) . '</div>'
				. ( ! empty( $ctx['sua_day_du'] ) ? \Phongdao\GiaoTiep\QuanTri\EditorDonGian::o( "$id-chu", $p . '[chu]', 'Nội dung', $m, 'chu', $p ) : self::vung( "$id-chu", $p . '[chu]', 'Nội dung', (string) ( $m['chu'] ?? '' ), '', 4 ) ),
			default        => '<div class="field-grid">' . self::o( "$id-tu", $p . '[tu]', 'Từ trong lời', (string) ( $m['tu'] ?? '' ), 'để trống = chú thích chung' ) . '</div>'
				. ( ! empty( $ctx['sua_day_du'] ) ? \Phongdao\GiaoTiep\QuanTri\EditorDonGian::o( "$id-giai", $p . '[loi_giai]', 'Lời giải', $m, 'loi_giai', $p ) : self::vung( "$id-giai", $p . '[loi_giai]', 'Lời giải', (string) ( $m['loi_giai'] ?? '' ), '', 3 ) )
				. ( '' === $goc ? '' : self::chuyen( $id, $p, $m, $ctx ) ),
		};
		$tom = match ( $phan ) {
			'di_ban'       => implode( ' ', $m['dong'] ?? array() ),
			'chuyen_quanh' => ( (string) ( $m['tieu_de'] ?? '' ) ?: 'Chuyện' ) . ' · ' . (string) ( $m['chu'] ?? '' ),
			default        => ( (string) ( $m['tu'] ?? '' ) ?: 'Chú thích chung' ) . ' · ' . (string) ( $m['loi_giai'] ?? '' ),
		};
		$gon = ! empty( $ctx['sua_day_du'] );
		return '<li class="muc-sua" id="hang-' . esc_attr( $id ) . '">' . ( $gon ? '<details class="phongdao-muc-gon"' . ( ! $m || ! empty( $ctx['form_loi'] ) ? ' open' : '' ) . '><summary><span data-tom-tat-muc>' . esc_html( $tom ? mb_strimwidth( $tom, 0, 120, '…' ) : 'Mục mới · nhập nội dung' ) . '</span></summary><div class="phongdao-muc-than">' : '' ) . '<input type="hidden" name="' . $p . '[goc]" value="' . esc_attr( $goc ) . '">'
			. ( '' === $nguoi ? '' : '<p class="small muted">' . esc_html( 'Hiện: ' . $nguoi ) . '</p>' )
			. $than . self::nguon( $id, $p . '[nguon]', $m['nguon'] ?? array(), $ctx )
			. self::hop( "$id-xoa", $p . '[xoa]', 'Xoá mục này', ! empty( $m['xoa'] ) ) . ( $gon ? '</div></details>' : '' ) . '</li>';
	}

	/**
	 * Ô "Chuyển sang Chuyện quanh bài" (tục ngữ: Nguồn gốc) của một mục từ khó đã lưu (D-119). Mục chung (không gắn từ) hay
	 * mục trùng cả câu tục ngữ thì máy ghi một dòng gợi ý; người đánh dấu mới chuyển, máy không tự chuyển chữ giữa các phần.
	 */
	private static function chuyen( string $id, string $p, array $m, array $ctx ): string {
		$the_loai = (string) ( $ctx['the_loai'] ?? '' );
		$ten      = self::ten_chuyen_quanh( $the_loai );
		$tu       = trim( (string) ( $m['tu'] ?? '' ) );
		$ca_cau   = \Phongdao\GiaoTiep\Khoi\Ve::khoa_cum( implode( ' ', (array) ( $ctx['phan']['loi']['dong'] ?? array() ) ) );
		$goi_y    = '' === $tu ? 'mục chung, không gắn từ nào trong lời' : ( 'tuc-ngu' === $the_loai && '' !== $ca_cau && \Phongdao\GiaoTiep\Khoi\Ve::khoa_cum( $tu ) === $ca_cau ? 'mục trùng cả câu, có thể là điển tích' : '' );
		return ( '' === $goi_y ? '' : '<p class="small muted goi-y-chuyen">' . esc_html( 'Máy gợi ý: ' . $goi_y . '. Chuyển sang ' . $ten . ' nếu đúng.' ) . '</p>' )
			. self::hop( "$id-chuyen", $p . '[chuyen]', 'Chuyển sang ' . $ten . ' khi lưu (giữ người làm và nguồn)', ! empty( $m['chuyen'] ) );
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

	private static function vung( string $id, string $ten, string $nhan, string $gt, string $goi_y = '', int $dong = 5, ?array $rich = null ): string {
		if ( null !== $rich ) {
			return \Phongdao\GiaoTiep\QuanTri\EditorDonGian::o( $id, $ten, $nhan . ( $goi_y ? ' · ' . $goi_y : '' ), $rich );
		}
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

	/** Chỉ người đang sửa nhận bản đối chiếu; chọn checkbox là quyết định dùng đầu vào của mình trên bản mới. */
	public static function doi_chieu_phien( ?array $cu, string $phien ): string {
		$dong = \Phongdao\MienDuLieu\SoSanh::dong( $cu );
		return '<details class="disclosure" data-phien-moi><summary>Đối chiếu bản đang lưu</summary><pre>'
			. esc_html( $dong ? implode( "\n", $dong ) : '(Phần này đang trống)' ) . '</pre><label class="choice"><input type="checkbox" name="phien_phan" value="' . esc_attr( $phien ) . '">Tôi đã đối chiếu; dùng phần đang nhập rồi bấm nút Lưu phù hợp.</label></details>';
	}

	/** Hàng trống có thể bỏ; hàng đã điền một phần phải được giữ và báo thiếu. */
	public static function loi_form( string $phan, array $f ): array {
		$loi = array();
		if ( 'media' === $phan && ( ! isset( $f['attachment_id'] ) || false === filter_var( $f['attachment_id'], FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0 ) ) ) ) ) {
			return array( 'Chưa nhận được ảnh đã chọn. Chọn ảnh trong Thư viện hoặc bấm Bỏ ảnh khỏi bài rồi lưu.' );
		}
		if ( 'chu_thich' !== $phan ) {
			return $loi;
		}
		$i = 0;
		foreach ( (array) ( $f['muc'] ?? array() ) as $k => $m ) {
			if ( '__i__' === (string) $k || ! is_array( $m ) || ! empty( $m['xoa'] ) ) {
				continue;
			}
			++$i;
			if ( '' !== trim( (string) ( $m['tu'] ?? '' ) ) && '' === trim( (string) ( $m['loi_giai'] ?? '' ) ) ) {
				$loi[] = 'Từ khó ' . $i . ': cần điền Lời giải. Hàng đang nhập được giữ lại.';
			}
		}
		return $loi;
	}

	/**
	 * Form gửi lên → giá trị phần (null = xoá) hoặc nháp (ý nghĩa chưa duyệt).
	 *
	 * @return array{gia_tri:?array,nhap:?array}
	 */
	public static function doc( string $phan, array $f, ?array $cu ): array {
		$doc = self::doc_chu( $phan, $f, $cu );
		if ( null !== \Phongdao\MienDuLieu\DinhDang::truong( $phan ) && 'chu_thich' !== $phan ) {
			$rich = \Phongdao\GiaoTiep\QuanTri\EditorDonGian::doc( $f, $cu );
			foreach ( array( 'gia_tri', 'nhap' ) as $k ) {
				if ( is_array( $doc[ $k ] ) ) {
					unset( $doc[ $k ]['dinh_dang'] );
					if ( null !== $rich ) { $doc[ $k ]['dinh_dang'] = $rich; }
				}
			}
		}
		return $doc;
	}

	private static function doc_chu( string $phan, array $f, ?array $cu ): array {
		$kieu = self::TAM[ $phan ][1] ?? '';
		$chu  = static fn( $v ): string => trim( str_replace( "\r", '', (string) $v ) );
		switch ( $kieu ) {
			case 'ghi_cong':
				$ds = array();
				foreach ( $cu['muc'] ?? array() as $i => $m ) {
					$f_m = is_array( $f['muc'][ $i ] ?? null ) ? $f['muc'][ $i ] : array();
					if ( ! empty( $f_m['xoa'] ) ) { continue; }
					if ( ! empty( $f_m['an_ten'] ) ) { $m['ten'] = ''; }
					elseif ( array_key_exists( 'hien_ten', $f_m ) ) { $m['ten'] = ! empty( $f_m['hien_ten'] ) ? \Phongdao\DichVu\NguoiDoc\DuyetTacPham::ten_da_dong_y( $m ) : ''; }
					if ( isset( $f_m['xuat_xu'] ) && is_string( $f_m['xuat_xu'] ) ) { $m['xuat_xu'] = $chu( $f_m['xuat_xu'] ); }
					$ds[] = $m;
				}
				return array( 'gia_tri' => $ds ?: null, 'nhap' => null );
			case 'media':
				$muc = array_values( array_filter( $cu['muc'] ?? array(), static fn( array $m ): bool => 'anh' !== ( $m['loai'] ?? '' ) ) );
				$id = absint( $f['attachment_id'] ?? 0 );
				if ( $id ) {
					$muc[] = array( 'attachment_id' => $id, 'loai' => 'anh' );
				}
				return array( 'gia_tri' => $muc ?: null, 'nhap' => null );
			case 'loi':
				$dong = explode( "\n", $chu( $f['dong'] ?? '' ) );
				$doi  = array_map( 'trim', $dong ) !== array_map( 'trim', $cu['dong'] ?? array() );
				// Form không gửi phần đối chiếu (ô Sửa lời ở trang Sửa tác phẩm): giữ đối chiếu đang có
				$v    = array_key_exists( 'doi_chieu', $f ) ? array_diff_key( $cu ?? array(), array( 'doi_chieu' => 1 ) ) : ( $cu ?? array() );
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
			case 'tho_tuc':
				// Không có ô "bat" = tắt (gỡ phần); bật giữ nguyên bản cũ để lần bấm lại không ghi lịch sử giả
				return array( 'gia_tri' => empty( $f['bat'] ) ? null : ( $cu ?? array( 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), 'nhap' => null );
			case 'chu_thich':
				$chuyen       = array();
				self::$bo_noi = array();
				return array( 'gia_tri' => self::doc_ds( $phan, (array) ( $f['muc'] ?? array() ), $cu, $chuyen ), 'nhap' => null, 'chuyen' => array_map( array( self::class, 'sang_chuyen_quanh' ), $chuyen ) );
			case 'chuyen_quanh':
			case 'di_ban':
				return array( 'gia_tri' => self::doc_ds( $phan, (array) ( $f['muc'] ?? array() ), $cu ), 'nhap' => null );
			case 'gan_nghia':
				$ra    = array();
				$thay  = array();
				$khong = array();
				foreach ( (array) ( $f['muc'] ?? array() ) as $k => $h ) {
					if ( '__i__' === (string) $k || ! is_array( $h ) || ! empty( $h['xoa'] ) ) {
						continue;
					}
					$bai = absint( $h['tac_pham_id'] ?? 0 );
					if ( ! $bai ) {
						$dong = $chu( $h['cau'] ?? '' );
						if ( '' === $dong ) {
							continue;
						}
						$bai = (int) \Phongdao\GiaoTiep\QuanTri\SuaTuyenTap::tim_bai( $dong );
						if ( ! $bai ) {
							$khong[] = $dong;
							continue;
						}
					}
					if ( isset( $thay[ $bai ] ) ) {
						continue;
					}
					$thay[ $bai ] = true;
					$ra[]         = array( 'tac_pham_id' => $bai, 'quan_he' => 'trai' === ( $h['quan_he'] ?? '' ) ? 'trai' : 'gan' );
				}
				return array( 'gia_tri' => $ra ?: null, 'nhap' => null, 'khong_thay' => $khong );
			case 'boi_canh':
				$v = array_filter( array( 'chu' => $chu( $f['chu'] ?? '' ), 'su_kien' => $chu( $f['su_kien'] ?? '' ), 'nam' => $chu( $f['nam'] ?? '' ), 'noi' => $chu( $f['noi'] ?? '' ) ), 'strlen' );
				if ( ! $v ) {
					return array( 'gia_tri' => null, 'nhap' => null );
				}
				$v += array_filter( array( 'nhan_vat' => $cu['nhan_vat'] ?? null, 'nguon' => self::doc_nguon( $f['nguon'] ?? array() ) ) );
				$so = array_filter( array_intersect_key( $cu ?? array(), array_flip( array( 'chu', 'su_kien', 'nam', 'noi', 'nhan_vat' ) ) ) ) + array_filter( array( 'nguon' => isset( $cu['nguon'] ) ? self::doc_nguon( array_intersect_key( $cu['nguon'], array( 'ho_so' => 1, 'trang' => 1, 'url' => 1 ) ) ) : null ) );
				return array( 'gia_tri' => $cu && $v == $so ? $cu : $v + array( 'nguoi_lam' => NguoiLam::NGUOI_VIET ), 'nhap' => null ); // phpcs:ignore Universal.Operators.StrictComparisons -- thứ tự khoá không quan trọng
			case 'doan':
				$ra = array();
				foreach ( (array) ( $f['muc'] ?? array() ) as $k => $h ) {
					if ( '__i__' === (string) $k || ! is_array( $h ) || ! empty( $h['xoa'] ) ) {
						continue;
					}
					$m = array_filter( array( 'tieu_de' => $chu( $h['tieu_de'] ?? '' ), 'tu_dong' => $chu( $h['tu_dong'] ?? '' ), 'den_dong' => $chu( $h['den_dong'] ?? '' ) ), 'strlen' );
					if ( $m ) {
						$ra[] = $m;
					}
				}
				usort( $ra, static fn( $a, $b ): int => (int) ( $a['tu_dong'] ?? 0 ) <=> (int) ( $b['tu_dong'] ?? 0 ) );
				return array( 'gia_tri' => $ra ?: null, 'nhap' => null );
		}
		return array( 'gia_tri' => $cu, 'nhap' => null );
	}

	/**
	 * Mục từ khó → mục Chuyện quanh bài (D-119): từ thành tên chuyện, lời giải thành nội dung (kèm định dạng); người làm và
	 * nguồn của mục giữ nguyên. Liên kết Từ điển, vị trí trong lời không áp dụng nên bỏ.
	 */
	public static function sang_chuyen_quanh( array $c ): array {
		return array_filter(
			array(
				'tieu_de'   => trim( (string) ( $c['tu'] ?? '' ) ),
				'chu'       => (string) ( $c['loi_giai'] ?? '' ),
				'dinh_dang' => $c['dinh_dang'] ?? null,
				'nguoi_lam' => (string) ( $c['nguoi_lam'] ?? NguoiLam::NGUOI_VIET ),
				'nguon'     => $c['nguon'] ?? null,
			),
			static fn( $v ): bool => null !== $v && '' !== $v && array() !== $v
		);
	}

	/** Từ khó vừa bị bỏ nối Từ điển vì người sửa đổi chữ (lần đọc form gần nhất) — để lời báo sau khi lưu nhắc lại. */
	public static array $bo_noi = array();

	/** Hai cách viết là cùng một chữ: bỏ khoảng trắng thừa, không phân biệt hoa thường (dấu vẫn phải giống). */
	public static function cung_chu( string $a, string $b ): bool {
		$chuan = static fn( string $s ): string => mb_strtolower( \Phongdao\MienDuLieu\BoDau::nfc( trim( (string) preg_replace( '/\s+/u', ' ', $s ) ) ) );
		return '' !== $chuan( $a ) && $chuan( $a ) === $chuan( $b );
	}

	/** @param list<array> $chuyen (từ khó) mục người đánh dấu "Chuyển sang Chuyện quanh bài": không giữ ở phần này, trả ra đây */
	private static function doc_ds( string $phan, array $hang, ?array $cu, array &$chuyen = array() ): ?array {
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
			} elseif ( 'chuyen_quanh' === $phan ) {
				$noi = trim( str_replace( "\r", '', (string) ( $h['chu'] ?? '' ) ) );
				if ( '' === $noi ) {
					continue;
				}
				$moi = array_filter( array( 'tieu_de' => trim( (string) ( $h['tieu_de'] ?? '' ) ), 'chu' => $noi, 'nguon' => $nguon ) );
				$so  = array_filter( array( 'tieu_de' => trim( (string) ( $goc['tieu_de'] ?? '' ) ), 'chu' => trim( (string) ( $goc['chu'] ?? '' ) ), 'nguon' => $goc_so['nguon'] ?? null ) );
			} else {
				$giai = trim( str_replace( "\r", '', (string) ( $h['loi_giai'] ?? '' ) ) );
				if ( '' === $giai ) {
					continue;
				}
				$moi = array_filter( array( 'tu' => trim( (string) ( $h['tu'] ?? '' ) ), 'loi_giai' => $giai, 'nguon' => $nguon ) );
				$so  = array_filter( array( 'tu' => trim( (string) ( $goc['tu'] ?? '' ) ), 'loi_giai' => trim( (string) ( $goc['loi_giai'] ?? '' ) ), 'nguon' => $goc_so['nguon'] ?? null ) );
			}
			// Không đổi gì → giữ nguyên mục cũ (máy chép giữ người làm, nguồn); đổi → người viết
			$muc = $goc && $moi == $so ? $goc : $moi + array( 'nguoi_lam' => NguoiLam::NGUOI_VIET ); // phpcs:ignore Universal.Operators.StrictComparisons -- thứ tự khoá không quan trọng
			// Từ khó đã nối Từ điển: sửa lời giải, nguồn mà chữ giữ nguyên thì giữ nối (mục từ, vị trí trong lời); đổi chữ thì
			// bỏ nối (mục từ cũ có thể không còn đúng) và báo cho người sửa
			if ( 'chu_thich' === $phan && $goc && $muc !== $goc ) {
				if ( self::cung_chu( (string) ( $moi['tu'] ?? '' ), (string) ( $goc['tu'] ?? '' ) ) ) {
					foreach ( array( 'muc_tu_id', 'vi_tri' ) as $giu ) {
						if ( ! empty( $goc[ $giu ] ) ) {
							$muc[ $giu ] = $goc[ $giu ];
						}
					}
				} elseif ( ! empty( $goc['muc_tu_id'] ) ) {
					self::$bo_noi[] = (string) ( $goc['tu'] ?? '' );
				}
			}
			if ( 'chu_thich' === $phan || 'chuyen_quanh' === $phan ) {
				$truong = 'chu_thich' === $phan ? 'loi_giai' : 'chu';
				unset( $muc['dinh_dang'] );
				$rich = \Phongdao\GiaoTiep\QuanTri\EditorDonGian::doc( $h, $goc, $truong );
				if ( null !== $rich ) { $muc['dinh_dang'] = $rich; }
			}
			if ( 'chu_thich' === $phan && $goc && ! empty( $h['chuyen'] ) ) {
				$chuyen[] = $muc; // người đánh dấu chuyển: mục rời Từ khó, sang Chuyện quanh bài (Module::doc_va_luu ghi)
				continue;
			}
			$ra[] = $muc;
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
