<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\KhoLuu\AnhMinhHoa as Anh;

defined( 'ABSPATH' ) || exit;

/** Ảnh tại site, kích thước đáp ứng và ghi công ở đúng chỗ; không tải mã media cho độc giả. */
final class AnhMinhHoa {
	public static function html( int $id ): string {
		if ( ! Anh::hop_le( $id ) ) {
			return '';
		}
		$anh = wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 780px) 92vw, 740px' ) );
		$caption = wp_kses( (string) wp_get_attachment_caption( $id ), array( 'a' => array( 'href' => true, 'rel' => true, 'title' => true ), 'em' => array(), 'strong' => array(), 'br' => array() ) );
		$chu = array_filter( array( $caption, make_clickable( esc_html( Anh::ghi_cong( $id ) ) ) ) );
		return '<figure class="phongdao-anh-minh-hoa">' . $anh . ( $chu ? '<figcaption>' . nl2br( implode( "\n", $chu ) ) . '</figcaption>' : '' ) . '</figure>';
	}
}
