<?php

namespace Phongdao\MienDuLieu;

/**
 * Kiểm lỗi trình bày (D-119, docs/41 mục 2 điều 9): khi lưu và ở danh sách "Cần sửa", máy chỉ nhắc theo quy tắc viết
 * (`docs/huong-dan/nhap-y-nghia-theo-lo.md`) — không chặn lưu, không tự sửa chữ. Lời không bao giờ bị máy sửa: lời chỉ được
 * nhắc để người kiểm lại theo nguồn. PHP thuần.
 */
final class KiemTrinhBay {

	/** Ý nghĩa: 2–3 câu, khoảng 30–60 chữ. */
	public const Y_NGHIA_TOI_DA = 60;

	/** Mở đầu nói về chính câu thay vì nói nghĩa ("Câu ca dao…", "Bài ca dao…", "Thành ngữ…"). */
	private const MO_DAU = array( 'câu ca dao', 'bài ca dao', 'lời ca dao', 'câu tục ngữ', 'tục ngữ', 'câu thành ngữ', 'thành ngữ', 'câu thơ', 'bài thơ', 'câu đố', 'bài vè', 'bài đồng dao', 'đồng dao', 'câu hát', 'bài hát', 'câu này', 'bài này' );

	/** Số chữ (tiếng) — đếm theo khoảng trắng. */
	public static function so_chu( string $chu ): int {
		$chu = trim( (string) preg_replace( '/\s+/u', ' ', self::thuan( $chu ) ) );
		return '' === $chu ? 0 : count( explode( ' ', $chu ) );
	}

	/**
	 * Nhắc cho một phần đang lưu. Chỉ Ý nghĩa (cả Giải thích đáp án), Nghĩa đen và lời có quy tắc; phần khác trả rỗng.
	 *
	 * @param string[] $loi lời của bài (để nhận ra Ý nghĩa mở đầu nhắc lại câu)
	 * @return list<array{ma:string,chu:string}>
	 */
	public static function nhac( string $phan, ?array $gia_tri, array $loi = array() ): array {
		if ( null === $gia_tri ) {
			return array();
		}
		return match ( $phan ) {
			'y_nghia'   => self::y_nghia( (string) ( $gia_tri['chu'] ?? '' ), $loi ),
			'nghia_den' => array_values( array_filter( self::y_nghia( (string) ( $gia_tri['chu'] ?? '' ), array() ), static fn( array $n ): bool => 'thieu_dau' === $n['ma'] ) ),
			'loi'       => self::loi( (array) ( $gia_tri['dong'] ?? array() ) ),
			default     => array(),
		};
	}

	/**
	 * Ý nghĩa: quá 60 chữ, mở đầu nhắc lại câu, thiếu dấu câu cuối, nhắc tác giả / bài thơ.
	 *
	 * @param string[] $loi
	 * @return list<array{ma:string,chu:string}>
	 */
	public static function y_nghia( string $chu, array $loi = array() ): array {
		$chu = trim( self::thuan( $chu ) );
		if ( '' === $chu ) {
			return array();
		}
		$ra = array();
		$so = self::so_chu( $chu );
		if ( $so > self::Y_NGHIA_TOI_DA ) {
			$ra[] = array( 'ma' => 'qua_dai', 'chu' => 'Dài ' . $so . ' chữ; quy tắc viết 2–3 câu, khoảng 30–' . self::Y_NGHIA_TOI_DA . ' chữ.' );
		}
		if ( self::nhac_cau( $chu, $loi ) ) {
			$ra[] = array( 'ma' => 'nhac_cau', 'chu' => 'Mở đầu nhắc lại câu: nên bắt đầu bằng nghĩa.' );
		}
		if ( ! preg_match( '/[.!?…]["”»’)\]]*$/u', $chu ) ) {
			$ra[] = array( 'ma' => 'thieu_dau', 'chu' => 'Thiếu dấu chấm cuối.' );
		}
		if ( preg_match( '/(^|[^\p{L}])(tác giả|nhà thơ|bài thơ)([^\p{L}]|$)/iu', $chu ) ) {
			$ra[] = array( 'ma' => 'tac_gia', 'chu' => 'Nhắc tác giả, bài thơ: quy tắc viết không nêu tác giả, tên sách.' );
		}
		return $ra;
	}

	/**
	 * Lời (chỉ nhắc, người sửa theo nguồn): dấu cách trước dấu câu, chữ "Ð" (Eth) thay "Đ", viết hoa giữa câu (từ ba chữ viết
	 * hoa trở lên giữa dòng, quá nửa số chữ — tên riêng một hai chữ không bị nhắc).
	 *
	 * @param string[] $dong
	 * @return list<array{ma:string,chu:string}>
	 */
	public static function loi( array $dong ): array {
		$ra   = array();
		$dong = array_values( array_filter( array_map( static fn( $d ): string => trim( (string) $d ), $dong ), 'strlen' ) );
		foreach ( $dong as $i => $d ) {
			if ( preg_match( '/\s[,.;:!?]/u', $d ) ) {
				$ra['cach_dau'] = array( 'ma' => 'cach_dau', 'chu' => 'Dòng ' . ( $i + 1 ) . ' có dấu cách trước dấu câu.' );
			}
			if ( str_contains( $d, 'Ð' ) || str_contains( $d, 'ð' ) ) {
				$ra['d_sai'] = array( 'ma' => 'd_sai', 'chu' => 'Dòng ' . ( $i + 1 ) . ' dùng chữ "Ð" (Eth) thay "Đ".' );
			}
			if ( self::viet_hoa_giua( $d ) ) {
				$ra['viet_hoa'] = array( 'ma' => 'viet_hoa', 'chu' => 'Dòng ' . ( $i + 1 ) . ' viết hoa giữa câu.' );
			}
		}
		return array_values( $ra );
	}

	/** Ý nghĩa mở đầu bằng "Câu ca dao…", hay nhắc lại ba tiếng đầu của lời trong mười hai tiếng đầu. */
	private static function nhac_cau( string $chu, array $loi ): bool {
		$dau = mb_strtolower( $chu );
		foreach ( self::MO_DAU as $m ) {
			if ( str_starts_with( $dau, $m . ' ' ) || str_starts_with( $dau, $m . ',' ) ) {
				return true;
			}
		}
		$tieng = static fn( string $s ): array => array_values( array_filter( explode( ' ', (string) preg_replace( '/[^\p{L}\p{N}]+/u', ' ', mb_strtolower( $s ) ) ), 'strlen' ) );
		$cau   = $tieng( implode( ' ', $loi ) );
		if ( count( $cau ) < 3 ) {
			return false;
		}
		$ba = implode( ' ', array_slice( $cau, 0, 3 ) );
		return str_contains( ' ' . implode( ' ', array_slice( $tieng( $chu ), 0, 12 ) ) . ' ', ' ' . $ba . ' ' );
	}

	private static function viet_hoa_giua( string $d ): bool {
		$chu = array_values( array_filter( explode( ' ', $d ), 'strlen' ) );
		$hoa = 0;
		$sau = 0;
		foreach ( $chu as $k => $c ) {
			if ( 0 === $k || preg_match( '/[.!?:"“]$/u', (string) $chu[ $k - 1 ] ) || ! preg_match( '/^\p{L}/u', $c ) ) {
				continue;
			}
			++$sau;
			if ( preg_match( '/^\p{Lu}/u', $c ) ) {
				++$hoa;
			}
		}
		return $hoa >= 3 && $hoa * 2 > $sau;
	}

	/** Bỏ thẻ HTML mà không cần WordPress (chữ của phần đã là chữ thuần; phòng khi có thẻ lọt vào). */
	private static function thuan( string $s ): string {
		return trim( (string) preg_replace( '/<[^>]*>/', '', $s ) );
	}
}
