<?php

namespace Phongdao\MienDuLieu;

/**
 * Địa chỉ mạng của người gửi — chỉ để băm giới hạn số lần gửi mỗi giờ, không bao giờ lưu (chuyển từ MVP luot-doc.php).
 * Chỉ tin header CF-Connecting-IP khi yêu cầu thật sự đến từ Cloudflare; gửi thẳng tới máy chủ kèm header giả thì không
 * né được giới hạn. PHP thuần.
 */
final class DiaChiMang {

	public const DAI_CLOUDFLARE = array(
		'173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
		'190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
		'104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
		'2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
	);

	public static function cua( string $goc, string $cf ): string {
		if ( '' === $cf || false === filter_var( $cf, FILTER_VALIDATE_IP ) ) {
			return $goc;
		}
		foreach ( self::DAI_CLOUDFLARE as $dai ) {
			if ( self::trong_dai( $goc, $dai ) ) {
				return $cf;
			}
		}
		return $goc;
	}

	/** IP có nằm trong dải CIDR không (IPv4 và IPv6). */
	public static function trong_dai( string $ip, string $dai ): bool {
		[ $goc, $bit ] = array_pad( explode( '/', $dai ), 2, '0' );
		$a = filter_var( $ip, FILTER_VALIDATE_IP ) ? inet_pton( $ip ) : false;
		$b = filter_var( $goc, FILTER_VALIDATE_IP ) ? inet_pton( $goc ) : false;
		if ( false === $a || false === $b || strlen( $a ) !== strlen( $b ) ) {
			return false;
		}
		$bit  = (int) $bit;
		$byte = intdiv( $bit, 8 );
		if ( substr( $a, 0, $byte ) !== substr( $b, 0, $byte ) ) {
			return false;
		}
		$du = $bit % 8;
		return 0 === $du || ( ( ord( $a[ $byte ] ) ^ ord( $b[ $byte ] ) ) & ( 0xFF << ( 8 - $du ) ) & 0xFF ) === 0;
	}
}
