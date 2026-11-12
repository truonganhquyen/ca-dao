<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\AnhChiaSe\Module as Anh;
use Phongdao\DichVu\AnhChiaSe\VeAnh;

defined( 'ABSPATH' ) || exit;

/**
 * Khối "Ảnh để chia sẻ" (docs/27 mục 10.1, D-093): xem trước, chọn khổ ngang / vuông / dọc và nền theo thể loại,
 * hoạ tiết SVG của dự án (không ảnh ngoài). Câu đố không lộ đáp án. "Tải ảnh" gửi form GET tới máy chủ, máy chủ vẽ
 * PNG đúng khổ, nền đã chọn (DichVu\AnhChiaSe — chạy cả khi tắt JavaScript); máy không vẽ được thì nút chỉ nhắc.
 */
final class AnhChiaSe {

	private const TEN_NEN = array( 'tim' => 'Trống đồng', 'giay' => 'Giấy dó', 'dem' => 'Đêm sao', 'luc' => 'Đồng lúa', 'nang' => 'Nắng', 'xanh' => 'Sông nước' );
	private const KHO     = array( 'ngang' => 'Ngang', 'vuong' => 'Vuông', 'doc' => 'Dọc' );

	/** Hoạ tiết vẽ bằng mã trong khung 400 × 400 (trống đồng: sao 14 cánh + vòng đồng tâm + vạch quanh vành). */
	public static function hoa_tiet( string $nen ): string {
		$f = static fn( float $x ): string => number_format( $x, 1, '.', '' );
		switch ( $nen ) {
			case 'tim':
				$diem = array();
				for ( $i = 0; $i < 28; $i++ ) {
					$r      = $i % 2 ? 20 : 58;
					$a      = M_PI * $i / 14 - M_PI / 2;
					$diem[] = $f( 200 + $r * cos( $a ) ) . ',' . $f( 200 + $r * sin( $a ) );
				}
				$s = '<polygon points="' . implode( ' ', $diem ) . '"/>';
				foreach ( array( 80, 106, 132, 158, 184 ) as $r ) {
					$s .= '<circle cx="200" cy="200" r="' . $r . '"/>';
				}
				for ( $i = 0; $i < 36; $i++ ) {
					$a  = $i * M_PI / 18;
					$s .= '<path d="M' . $f( 200 + 140 * cos( $a ) ) . ' ' . $f( 200 + 140 * sin( $a ) ) . 'L' . $f( 200 + 150 * cos( $a ) ) . ' ' . $f( 200 + 150 * sin( $a ) ) . '"/>';
				}
				return $s;
			case 'giay':
				$s = '';
				foreach ( array( array( 30, 90 ), array( 190, 60 ), array( 110, 200 ), array( 260, 190 ), array( 40, 300 ), array( 220, 320 ) ) as [ $x, $y ] ) {
					$s .= '<path d="M' . $x . ' ' . $y . 'c0-22 18-40 40-40s40 18 40 40c0-14 11-25 25-25s25 11 25 25M' . ( $x + 20 ) . ' ' . $y . 'c0-11 9-20 20-20s20 9 20 20"/>';
				}
				return $s;
			case 'dem':
				$s = '';
				for ( $i = 0; $i < 40; $i++ ) {
					$s .= '<circle cx="' . ( ( $i * 97 ) % 400 ) . '" cy="' . ( ( $i * 53 ) % 400 ) . '" r="' . ( 1 + $i % 3 ) . '" fill="currentColor" stroke="none"/>';
				}
				return $s . '<path d="M300 80a50 50 0 1 0 40 80 40 40 0 1 1-40-80z" fill="currentColor" stroke="none"/>';
			case 'nang':
				$s = '';
				for ( $i = 0; $i < 30; $i++ ) {
					$s .= '<circle cx="' . ( ( $i * 71 ) % 400 ) . '" cy="' . ( ( $i * 131 ) % 400 ) . '" r="' . ( 6 + ( $i % 5 ) * 5 ) . '"/>';
				}
				return $s;
			case 'luc':
				$s = '';
				for ( $i = 0; $i < 9; $i++ ) {
					$s .= '<path d="M0 ' . ( 60 + $i * 40 ) . 'q25-18 50 0t50 0t50 0t50 0t50 0t50 0t50 0t50 0"/>';
				}
				return $s;
			default:
				$s = '';
				for ( $i = 0; $i < 9; $i++ ) {
					$s .= '<path d="M0 ' . ( 50 + $i * 42 ) . 'c40-30 60 30 100 0s60 30 100 0 60 30 100 0 60 30 100 0"/>';
				}
				return $s;
		}
	}

	/**
	 * @param array{ma:string,the_loai:string,ten_the_loai:string,kieu:string,loi:string[],y_nghia?:array{chu:string},url:string} $tp
	 */
	public static function hien( array $tp ): string {
		$k     = $tp['the_loai'];
		$nen   = VeAnh::nen_cua( $k );
		$cau_do = 'cau-do' === $k;
		$dong  = count( $tp['loi'] ) > 8 ? array_merge( array_slice( $tp['loi'], 0, 4 ), array( '…' ) ) : $tp['loi'];
		$y     = $cau_do ? '' : ( $tp['y_nghia']['chu'] ?? '' );
		$ma    = esc_attr( $tp['ma'] );
		$tai   = ctype_digit( (string) $tp['ma'] ) && VeAnh::duoc(); // bài thật, máy vẽ được: tải PNG từ máy chủ
		$hinh  = '';
		foreach ( $nen as $n ) {
			$hinh .= '<svg class="hoa-tiet m-' . $n . '" viewBox="0 0 400 400" aria-hidden="true" focusable="false">' . self::hoa_tiet( $n ) . '</svg>';
		}
		$kho = '';
		foreach ( self::KHO as $v => $t ) {
			$kho .= '<label class="seg-o"><input type="radio" name="kho-' . $ma . '" value="' . $v . '"' . ( 'ngang' === $v ? ' checked' : '' ) . '><span><b>' . $t . '</b></span></label>';
		}
		$chon_nen = '';
		foreach ( $nen as $i => $n ) {
			$chon_nen .= '<label class="nen-chon"><input type="radio" name="nen-' . $ma . '" value="' . $n . '"' . ( 0 === $i ? ' checked' : '' ) . '><span class="mau nen-' . $n . '" aria-hidden="true"></span><span>' . self::TEN_NEN[ $n ] . '</span></label>';
		}
		return '<section class="block acs" id="anh-chia-se" aria-labelledby="acs-' . $ma . '"><h2 id="acs-' . $ma . '">Ảnh để chia sẻ</h2>'
			. '<p class="small muted">Chọn khổ và nền rồi tải ảnh để đăng Facebook, Zalo. Ảnh luôn kèm tên thư viện và đường dẫn bài' . ( $cau_do ? '; không lộ đáp án' : '' ) . '.</p>'
			. '<div class="acs-khung"><figure class="anh-chia-se" data-nen="' . $nen[0] . '" data-fit-h aria-label="Xem trước ảnh chia sẻ">' . $hinh . ( $cau_do ? '<span class="dau-hoi" aria-hidden="true">?</span>' : '' )
			. '<div class="acs-trong"><span class="acs-the-loai">' . esc_html( $tp['ten_the_loai'] . ( empty( $tp['co_nghi'] ) ? '' : ' · Có thể không phải dân gian' ) ) . '</span>'
			. Ve::loi( $dong, $tp['kieu'], array(), array( 'max' => 'proverb' === $tp['kieu'] ? 'w0.085' : 'w0.06', 'nhom' => 'acs-' . $tp['ma'], 'san' => '8' ) )
			. ( '' !== $y ? '<p class="acs-y-nghia">' . esc_html( $y ) . '</p>' : '' ) . ( $cau_do ? '<p class="acs-y-nghia hien">Lời giải ở phongdao.com</p>' : '' )
			. '<div class="acs-chan"><span class="logo" aria-hidden="true">P</span><span>Thư viện Văn học Dân gian Việt Nam<small>' . esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) . '/' . $k . '/…' ) . '</small></span></div></div></figure>'
			. ( $tai ? '<form class="acs-chon" method="get" action="' . esc_url( home_url( '/' ) ) . '"><input type="hidden" name="' . Anh::THAM_SO . '" value="' . $ma . '">' : '<div class="acs-chon">' ) . '<fieldset><legend>Khổ ảnh</legend><div class="seg">' . $kho . '</div></fieldset><fieldset><legend>Nền</legend><div class="nen-o">' . $chon_nen . '</div></fieldset>'
			. ( '' !== $y ? '<label class="choice"><input type="checkbox" name="yn-' . $ma . '" value="1">Kèm ý nghĩa</label>' : '' )
			. '<div class="actions">' . ( $tai ? '<button type="submit" class="btn primary">' : '<button type="button" class="btn primary" data-action="download-card">' ) . Ve::icon( 'download' ) . 'Tải ảnh</button><button type="button" class="btn" data-action="copy-link" data-url="' . esc_url( $tp['url'] ) . '">' . Ve::icon( 'link' ) . 'Sao chép liên kết</button></div>' . ( $tai ? '</form>' : '</div>' ) . '</div></section>';
	}
}
