<?php

namespace Phongdao\KhoLuu;

defined( 'ABSPATH' ) || exit;

/** Một ảnh tuỳ chọn; tệp, alt và chú thích vẫn thuộc Thư viện WordPress. */
final class AnhMinhHoa {

	public const GHI_CONG = '_phongdao_anh_ghi_cong';

	public static function id( ?array $media ): int {
		foreach ( $media['muc'] ?? array() as $m ) {
			if ( 'anh' === ( $m['loai'] ?? '' ) ) {
				return (int) ( $m['attachment_id'] ?? 0 );
			}
		}
		return 0;
	}

	public static function hop_le( int $id ): bool {
		if ( ! $id || ! wp_attachment_is_image( $id ) || 'trash' === get_post_status( $id ) ) {
			return false;
		}
		$file = get_attached_file( $id );
		$url = wp_get_attachment_url( $id );
		return $file && is_file( $file ) && wp_getimagesize( $file ) && $url && wp_parse_url( $url, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST )
			&& in_array( get_post_mime_type( $id ), array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif' ), true );
	}

	/** Chỉ đọc ghi công do người điền; không suy đoán tác giả hoặc quyền sử dụng. */
	public static function ghi_cong( int $id ): string {
		$chu = (string) get_post_meta( $id, self::GHI_CONG, true );
		if ( '' !== $chu ) {
			return $chu;
		}
		$cu = get_post_meta( $id, '_cadao_anh_commons', true );
		return is_array( $cu ) ? implode( ' · ', array_filter( array( $cu['tac_gia'] ?? '', $cu['giay_phep'] ?? '', $cu['giay_phep_url'] ?? '', $cu['trang'] ?? '' ) ) ) : '';
	}

}
