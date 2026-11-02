<?php

namespace Phongdao\MienDuLieu;

/**
 * Bản chữ đơn giản của tác phẩm ghi vào post_content sau mỗi lần lưu (docs/26 mục 4.5): cho RSS và công cụ ngoài.
 * Trang người đọc dựng từ các phần, không đọc post_content. Không bao giờ có nguồn ẩn hay địa chỉ gốc máy đã lấy.
 */
final class BanChu {

	/** Các phần có trong bản chữ; lưu phần khác thì không phải ghi lại. */
	public const PHAN = array( 'loi', 'di_ban', 'y_nghia' );

	/** @param array<string,?array> $phan */
	public static function tu_phan( array $phan ): string {
		$ra = self::kho( $phan['loi']['dong'] ?? array() );
		foreach ( $phan['di_ban']['muc'] ?? array() as $db ) {
			$ra .= self::kho( $db['dong'] ?? array() );
		}
		if ( ! empty( $phan['y_nghia']['chu'] ) ) {
			$ra .= '<p>' . nl2br( self::thoat( $phan['y_nghia']['chu'] ), false ) . "</p>\n";
		}
		return $ra;
	}

	/** Mỗi khổ một đoạn, mỗi câu một dòng. */
	private static function kho( array $dong ): string {
		$ra = '';
		foreach ( explode( "\n\n", implode( "\n", $dong ) ) as $kho ) {
			if ( '' !== trim( $kho ) ) {
				$ra .= '<p>' . str_replace( "\n", '<br>', self::thoat( $kho ) ) . "</p>\n";
			}
		}
		return $ra;
	}

	private static function thoat( string $s ): string {
		return htmlspecialchars( $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}
}
