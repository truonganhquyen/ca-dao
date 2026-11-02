<?php

namespace Phongdao\KhoLuu;

defined( 'ABSPATH' ) || exit;

/**
 * Kho thu thập `wp_cadao_kho` — giữ nguyên cấu trúc bảng MVP (docs/26 mục 4.4) để sau ngày chuyển máy đọc nguồn chạy
 * tiếp đúng chỗ đang dừng. Dữ liệu riêng tư: không lên trang người đọc, không vào repo công khai (AGENTS.md).
 *
 * doc_trang: '' = chưa đọc trang HTML, 'YYYY-MM-DD' = đã đọc, 'loi' / 'khong_thay' = không đọc được.
 * trang_thai: 'moi' · 'da_tao_bai' (thành tác phẩm, post_id) · 'da_gop' (gop_vao, gop_lam) · 'bo_qua'.
 */
final class Kho {

	public static function bang(): string {
		global $wpdb;
		return $wpdb->prefix . 'cadao_kho';
	}

	public static function sql_tao( string $bang, string $bo_ma ): string {
		return "CREATE TABLE $bang (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		nguon_id varchar(64) NOT NULL,
		ma_goc varchar(64) NOT NULL,
		url varchar(500) NOT NULL DEFAULT '',
		loai varchar(32) NOT NULL DEFAULT '',
		tieu_de text NOT NULL,
		loi longtext NOT NULL,
		du_lieu longtext NOT NULL,
		ngay_sua_goc varchar(32) NOT NULL DEFAULT '',
		ngay_lay datetime NOT NULL,
		trang_thai varchar(16) NOT NULL DEFAULT 'moi',
		doc_trang varchar(16) NOT NULL DEFAULT '',
		post_id bigint(20) unsigned NOT NULL DEFAULT 0,
		gop_vao varchar(32) NOT NULL DEFAULT '',
		gop_lam varchar(16) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		UNIQUE KEY nguon_ma (nguon_id, ma_goc),
		KEY trang_thai (trang_thai),
		KEY doc_trang (nguon_id, doc_trang),
		KEY url (url(191)),
		KEY gop_vao (gop_vao)
		) $bo_ma;";
	}

	/**
	 * Lưu (hoặc cập nhật) một mục. Chỉ các trường có trong $muc được ghi; dị bản, chú thích đã đọc từ trang giữ nguyên
	 * khi API lấy lại. Nguồn sửa bài (ngay_sua_goc đổi) thì đánh dấu đọc lại trang.
	 *
	 * @return string 'moi' | 'cap_nhat' | 'giu_nguyen'
	 */
	public static function luu( string $nguon_id, array $muc ): string {
		global $wpdb;
		$bang   = self::bang();
		$ma_goc = (string) $muc['ma_goc'];
		$cu     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bang WHERE nguon_id = %s AND ma_goc = %s", $nguon_id, $ma_goc ) );
		$d      = $cu ? ( json_decode( (string) $cu->du_lieu, true ) ?: array() ) : array();
		foreach ( array( 'di_ban', 'chu_thich', 'chu_de_goc', 'co' ) as $k ) {
			$d[ $k ] = array_key_exists( $k, $muc ) ? array_values( (array) $muc[ $k ] ) : ( $d[ $k ] ?? array() );
		}
		foreach ( array( 'cau_hoi', 'dap_an' ) as $k ) {
			$d[ $k ] = array_key_exists( $k, $muc ) ? $muc[ $k ] : ( $d[ $k ] ?? null );
		}
		$hang = array(
			'url'          => (string) ( $muc['url'] ?? $cu->url ?? '' ),
			'loai'         => (string) ( $muc['loai'] ?? $cu->loai ?? '' ),
			'tieu_de'      => (string) ( $muc['tieu_de'] ?? $cu->tieu_de ?? '' ),
			'loi'          => (string) ( $muc['loi'] ?? $cu->loi ?? '' ),
			'du_lieu'      => (string) wp_json_encode( $d, JSON_UNESCAPED_UNICODE ),
			'ngay_sua_goc' => (string) ( $muc['ngay_sua_goc'] ?? $cu->ngay_sua_goc ?? '' ),
		);
		if ( array_key_exists( 'doc_trang', $muc ) ) {
			$hang['doc_trang'] = (string) $muc['doc_trang'];
		} elseif ( $cu && $cu->ngay_sua_goc !== $hang['ngay_sua_goc'] ) {
			$hang['doc_trang'] = ''; // nguồn đã sửa bài: đọc lại trang
		}
		if ( ! $cu ) {
			$wpdb->insert( $bang, $hang + array( 'nguon_id' => $nguon_id, 'ma_goc' => $ma_goc, 'ngay_lay' => current_time( 'mysql' ), 'trang_thai' => 'moi' ) );
			/** Sau khi kho có mục mới / đổi: chống trùng, cập nhật tác phẩm tạo từ mục này (DichVu\UngVien). */
			do_action( 'phongdao_kho_da_luu', (int) $wpdb->insert_id, 'moi' );
			return 'moi';
		}
		$giong = true;
		foreach ( $hang as $k => $v ) {
			$giong = $giong && (string) $cu->$k === $v;
		}
		if ( $giong ) {
			return 'giu_nguyen';
		}
		$wpdb->update( $bang, $hang + array( 'ngay_lay' => current_time( 'mysql' ) ), array( 'id' => $cu->id ) );
		do_action( 'phongdao_kho_da_luu', (int) $cu->id, 'cap_nhat' );
		return 'cap_nhat';
	}

	public static function lay( int $id ): ?object {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::bang() . ' WHERE id = %d', $id ) ) ?: null;
	}

	/** Dữ liệu thêm của một mục (dị bản, chú thích, câu hỏi, đáp án, chủ đề nguồn, cờ). */
	public static function du_lieu( object $muc ): array {
		$d = json_decode( (string) $muc->du_lieu, true );
		return is_array( $d ) ? $d : array();
	}

	/** Số mục theo trạng thái; không tính mục chưa có lời (chờ đọc trang). */
	public static function dem( ?string $nguon_id = null ): array {
		global $wpdb;
		$sql = 'SELECT trang_thai, COUNT(*) AS so FROM ' . self::bang() . " WHERE loi <> ''" . ( $nguon_id ? $wpdb->prepare( ' AND nguon_id = %s', $nguon_id ) : '' ) . ' GROUP BY trang_thai';
		$dem = array( 'moi' => 0, 'da_tao_bai' => 0, 'da_gop' => 0, 'bo_qua' => 0 );
		foreach ( $wpdb->get_results( $sql ) ?: array() as $r ) {
			$dem[ $r->trang_thai ] = (int) $r->so;
		}
		return $dem;
	}
}
