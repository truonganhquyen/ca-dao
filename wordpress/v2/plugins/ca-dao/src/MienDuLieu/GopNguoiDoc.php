<?php

namespace Phongdao\MienDuLieu;

/**
 * Người đọc góp (docs/27 mục 9, bộ thiết kế R22): các loại góp, kiểm dữ liệu một lần gửi, định dạng bài cảm nhận.
 * PHP thuần. Không trường nào bắt tên thật, email, số điện thoại; mọi thứ gửi vào chờ người biên soạn xem.
 */
final class GopNguoiDoc {

	/** Loại góp => [nhãn, cần bài?]. vi_pham chỉ mở từ liên kết "Báo vi phạm" dưới một cảm nghĩ / bài cảm nhận. */
	public const LOAI = array(
		'di_ban'    => array( 'Ở quê nói khác', false ),
		'chu_thich' => array( 'Góp ý chú thích', false ),
		'bao_loi'   => array( 'Báo lỗi', false ),
		'so_trang'  => array( 'Gửi số trang', false ),
		'cam_nghi'  => array( 'Cảm nghĩ ngắn', true ),
		'cach_hieu' => array( 'Cách hiểu', true ),
		'phan_tich' => array( 'Viết cảm nhận / bài phân tích', false ),
		'vi_pham'   => array( 'Báo vi phạm', false ),
	);

	/** Vai của người viết cảm nhận (giữ khoá MVP để bài cũ nối đúng). */
	public const VAI = array(
		'hoc_sinh'   => 'Học sinh',
		'giao_vien'  => 'Giáo viên',
		'yeu_ca_dao' => 'Người yêu ca dao',
	);

	/** Lời báo khi gửi chưa được — đường dẫn chỉ mang mã (?ma=…), không ai làm trang hiện một câu tuỳ ý. */
	public const BAO = array(
		'cu'        => 'Trang đã cũ, xin tải lại rồi gửi lại.',
		'bai'       => 'Không thấy bài này.',
		'thieu'     => 'Xin chọn loại góp, ghi nội dung và đánh dấu đồng ý.',
		'thieu_pt'  => 'Xin ghi tiêu đề, bài viết (ít nhất 200 chữ cái), và đánh dấu cam kết tự viết, đồng ý giấy phép.',
		'thieu_ch'  => 'Xin viết cách hiểu của bạn, đánh dấu "Tôi tự viết" và đồng ý giấy phép.',
		'bay'       => 'Không gửi được.',
		'nhieu'     => 'Bạn đã gửi nhiều lần trong giờ này. Nội dung vẫn ở trên máy bạn; xin thử lại sau ít phút.',
		'turnstile' => 'Chưa qua bước kiểm tra chống tin rác. Xin thử lại.',
		'loi'       => 'Chưa gửi được, xin thử lại.',
	);

	public const DAI_NOI_DUNG = 4000;

	public const DAI_CAM_NGHI = 2000;

	/** Cách hiểu của bạn đọc (D-105): vài dòng, form nhanh ngay trên trang bài. */
	public const DAI_CACH_HIEU = 1000;

	public const DAI_PHAN_TICH = 20000;

	/**
	 * Kiểm một lần gửi (sau khi đã bỏ dấu \ của WordPress). Chưa kiểm chống rác, bài có tồn tại không.
	 *
	 * @return array{0:?string,1:array} [mã lời báo (null = đạt), dữ liệu đã làm sạch]
	 */
	public static function kiem( array $p ): array {
		$loai = (string) ( $p['loai'] ?? '' );
		if ( ! isset( self::LOAI[ $loai ] ) ) {
			return array( 'thieu', array() );
		}
		$chu  = static fn( string $k, int $dai ): string => mb_substr( trim( (string) preg_replace( '/[\x{0000}-\x{0008}\x{000B}\x{000C}\x{000E}-\x{001F}\x{007F}]/u', '', str_replace( "\r\n", "\n", (string) ( $p[ $k ] ?? '' ) ) ) ), 0, $dai );
		$ngan = static fn( string $k, int $dai = 200 ): string => mb_substr( trim( (string) preg_replace( '/\s+/u', ' ', $chu( $k, $dai * 2 ) ) ), 0, $dai );
		$bai  = (int) ( $p['bai'] ?? 0 );
		$sach = array(
			'loai' => $loai,
			'bai'  => max( 0, $bai ),
		);
		if ( self::LOAI[ $loai ][1] && $bai < 1 ) {
			return array( 'bai', $sach );
		}
		if ( 'phan_tich' === $loai ) {
			$sach += array(
				'tieu_de'  => $ngan( 'tieu_de', 200 ),
				'noi_dung' => $chu( 'noi_dung', self::DAI_PHAN_TICH ),
				'but_danh' => $ngan( 'ten', 80 ),
				'vai'      => isset( self::VAI[ (string) ( $p['vai'] ?? '' ) ] ) ? (string) $p['vai'] : '',
			);
			$du = '' !== $sach['tieu_de'] && mb_strlen( (string) preg_replace( '/[^\p{L}]/u', '', $sach['noi_dung'] ) ) >= 200 && ! empty( $p['tu_viet'] ) && ! empty( $p['dong_y'] );
			return array( $du ? null : 'thieu_pt', $sach );
		}
		if ( 'cach_hieu' === $loai ) {
			// Chỉ chữ và bút danh tuỳ chọn; không có ô tên thật, email, nơi nghe, người kể (D-105)
			$sach += array(
				'noi_dung' => $chu( 'noi_dung', self::DAI_CACH_HIEU ),
				'ten'      => $ngan( 'ten', 80 ),
			);
			$du = '' !== $sach['noi_dung'] && ! empty( $p['tu_viet'] ) && ! empty( $p['dong_y'] );
			return array( $du ? null : 'thieu_ch', $sach );
		}
		$sach += array(
			'noi_dung' => $chu( 'noi_dung', 'cam_nghi' === $loai ? self::DAI_CAM_NGHI : self::DAI_NOI_DUNG ),
			'ten'      => $ngan( 'ten', 80 ),
			'noi_nghe' => $ngan( 'noi_nghe' ),
			'nguoi_ke' => $ngan( 'nguoi_ke' ),
			'sach'     => $ngan( 'sach' ),
			'trang'    => $ngan( 'trang', 40 ),
			'vi_pham'  => max( 0, (int) ( $p['vi_pham'] ?? 0 ) ),
		);
		$co_chu = '' !== $sach['noi_dung'] || ( 'so_trang' === $loai && '' !== $sach['trang'] );
		return array( $co_chu && ! empty( $p['dong_y'] ) ? null : 'thieu', $sach );
	}

	/** Bài cảm nhận người đọc gõ → khối: dòng trống tách đoạn, **đậm**, dòng bắt đầu ">" là trích lời; mọi chữ được thoát. */
	public static function van_ban( string $chu ): string {
		$khoi = array();
		$dam  = static fn( string $s ): string => (string) preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', htmlspecialchars( $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		foreach ( preg_split( "/\n\s*\n/u", str_replace( "\r", '', trim( $chu ) ) ) ?: array() as $doan ) {
			$dong = array_map( 'trim', explode( "\n", $doan ) );
			if ( array() !== array_filter( $dong ) && array_reduce( $dong, static fn( bool $c, string $d ): bool => $c && str_starts_with( $d, '>' ), true ) ) {
				$noi    = implode( '<br>', array_map( static fn( string $d ): string => $dam( ltrim( substr( $d, 1 ) ) ), $dong ) );
				$khoi[] = "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>$noi</p>\n<!-- /wp:paragraph --></blockquote>\n<!-- /wp:quote -->";
			} elseif ( '' !== implode( '', $dong ) ) {
				$khoi[] = "<!-- wp:paragraph -->\n<p>" . implode( '<br>', array_map( $dam, $dong ) ) . "</p>\n<!-- /wp:paragraph -->";
			}
		}
		return implode( "\n\n", $khoi );
	}
}
