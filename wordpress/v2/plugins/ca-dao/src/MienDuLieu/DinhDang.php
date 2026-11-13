<?php

namespace Phongdao\MienDuLieu;

/** Định dạng nhỏ đi kèm chữ thuần; không diễn giải HTML nằm trong dữ liệu cũ. */
final class DinhDang {

	public const PHAN = array( 'y_nghia', 'nghia_den', 'nghia_bong', 'dap_an', 'goi_y', 'boi_canh', 'chu_thich' );
	public static function truong( string $phan ): ?string {
		return in_array( $phan, self::PHAN, true ) ? ( 'chu_thich' === $phan ? 'loi_giai' : 'chu' ) : null;
	}

	public static function tu_chu( string $chu ): string {
		return htmlspecialchars( $chu, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}

	/** Dựng lại từng thẻ/thuộc tính được phép; không trả lại mẩu HTML đầu vào. */
	public static function sach( string $html ): string {
		if ( '' === trim( $html ) ) { return ''; }
		$dom = new \DOMDocument();
		$cu = libxml_use_internal_errors( true );
		try {
			$dom->loadHTML( '<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
			$body = $dom->getElementsByTagName( 'body' )->item( 0 );
			return trim( BoDau::nfc( $body ? self::con( $body ) : '' ) );
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors( $cu );
		}
	}

	private static function con( \DOMNode $node ): string {
		$ra = '';
		foreach ( $node->childNodes as $n ) {
			if ( XML_TEXT_NODE === $n->nodeType ) { $ra .= self::tu_chu( $n->nodeValue ?? '' ); continue; }
			if ( ! $n instanceof \DOMElement ) { continue; }
			$tag = strtolower( $n->tagName );
			if ( in_array( $tag, array( 'script', 'style', 'iframe', 'object', 'svg', 'math', 'template' ), true ) ) { continue; }
			if ( 'br' === $tag ) { $ra .= "\n"; continue; }
			$chu = self::con( $n );
			if ( in_array( $tag, array( 'b', 'strong', 'i', 'em', 'u' ), true ) ) {
				$tag = array( 'b' => 'strong', 'i' => 'em' )[ $tag ] ?? $tag;
				$ra .= '<' . $tag . '>' . $chu . '</' . $tag . '>';
			} elseif ( 'a' === $tag && preg_match( '~^(?:https?://|mailto:|/(?!/)|\#)~i', trim( $n->getAttribute( 'href' ) ) ) && ! preg_match( '/[\x00-\x20\x7f]/', $n->getAttribute( 'href' ) ) ) {
				$ra .= '<a href="' . self::tu_chu( trim( $n->getAttribute( 'href' ) ) ) . '">' . $chu . '</a>';
			} elseif ( 'span' === $tag && in_array( $n->getAttribute( 'class' ), array( 'pd-chu-do', 'pd-chu-xanh', 'pd-chu-tim', 'pd-chu-nhe' ), true ) ) {
				$ra .= '<span class="' . $n->getAttribute( 'class' ) . '">' . $chu . '</span>';
			} else {
				$ra .= $chu . ( in_array( $tag, array( 'p', 'div', 'li', 'h1', 'h2', 'h3', 'blockquote' ), true ) ? "\n\n" : '' );
			}
		}
		return $ra;
	}

	public static function chu( string $html ): string {
		$chu = html_entity_decode( strip_tags( self::sach( $html ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$chu = str_replace( array( "\r\n", "\r" ), "\n", $chu );
		return trim( (string) preg_replace( array( '/[ \t\x{00A0}]+/u', '/ ?\n ?/u', '/\n{3,}/u' ), array( ' ', "\n", "\n\n" ), $chu ) );
	}

	/** Chỉ dùng định dạng còn khớp chữ; dữ liệu cũ luôn được escape. */
	public static function hien( string $chu, string $html = '' ): string {
		$hop = '' !== $html && self::chu( $html ) === self::chu( self::tu_chu( $chu ) );
		return nl2br( $hop ? self::sach( $html ) : self::tu_chu( $chu ), false );
	}
}
