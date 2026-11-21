<?php

namespace Phongdao\DichVu\TimMeili;

defined( 'ABSPATH' ) || exit;

/** API chỉ phía máy chủ, mặc định tắt. Không nhận endpoint, khoá hay bộ lọc từ người đọc. */
final class MayTim {
	public static function bat(): bool {
		return defined( 'PHONGDAO_MEILI_BAT' ) && true === PHONGDAO_MEILI_BAT;
	}

	public static function goi( string $method, string $path, ?array $data = null, int $timeout = 3 ): array {
		$url = defined( 'PHONGDAO_MEILI_URL' ) ? rtrim( PHONGDAO_MEILI_URL, '/' ) : '';
		$key = defined( 'PHONGDAO_MEILI_KEY' ) ? PHONGDAO_MEILI_KEY : '';
		// Bản đầu chỉ tự lưu trữ trên cùng máy; không gửi lời hay truy vấn tới dịch vụ ngoài.
		if ( ! preg_match( '~^http://127\.0\.0\.1:[0-9]{2,5}$~D', $url ) || ! is_string( $key ) || strlen( $key ) < 16 ) { throw new \RuntimeException( 'Chưa cấu hình máy tìm nội bộ.' ); }
		$args = array( 'method' => $method, 'timeout' => $timeout, 'redirection' => 0, 'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ), 'limit_response_size' => 8 * 1024 * 1024 );
		if ( null !== $data ) { $args['body'] = wp_json_encode( $data, JSON_UNESCAPED_UNICODE ); }
		$r = wp_remote_request( $url . $path, $args );
		if ( is_wp_error( $r ) || wp_remote_retrieve_response_code( $r ) < 200 || wp_remote_retrieve_response_code( $r ) >= 300 ) { throw new \RuntimeException( 'Chưa kết nối được máy tìm.' ); }
		$ra = json_decode( wp_remote_retrieve_body( $r ), true );
		if ( ! is_array( $ra ) ) { throw new \RuntimeException( 'Máy tìm trả dữ liệu không hợp lệ.' ); }
		return $ra;
	}

	/** HTTP 202 chưa phải đã ghi xong; chỉ xác nhận khi task thành công. */
	public static function ghi( string $method, string $path, ?array $data = null ): void {
		$r = self::goi( $method, $path, $data, 15 );
		if ( ! isset( $r['taskUid'] ) || ! is_int( $r['taskUid'] ) ) { throw new \RuntimeException( 'Chưa nhận được biên nhận chỉ mục.' ); }
		$het = microtime( true ) + 20;
		do {
			$kq = self::goi( 'GET', '/tasks/' . $r['taskUid'] );
			if ( 'succeeded' === ( $kq['status'] ?? '' ) ) { return; }
			if ( in_array( $kq['status'] ?? '', array( 'failed', 'canceled' ), true ) ) { break; }
			usleep( 100000 );
		} while ( microtime( true ) < $het );
		throw new \RuntimeException( 'Chưa ghi xong chỉ mục; sẽ thử lại.' );
	}
}
