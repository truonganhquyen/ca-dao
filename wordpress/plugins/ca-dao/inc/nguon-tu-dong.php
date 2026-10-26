<?php
/**
 * Nguồn tự động: cấu hình (lưu trong một tùy chọn WordPress), bộ đọc, lịch chạy (docs/24 mục 4).
 * - Mỗi 5 phút: mỗi nguồn đang bật chạy bộ đọc một lượt ngắn (≈ 20 giây, hợp hosting phổ thông).
 * - Mỗi ngày: mỗi nguồn đang bật tạo "số bài mỗi ngày" bài Chờ duyệt từ kho.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TUY_CHON_NGUON = 'cadao_nguon_tu_dong';
const CADAO_GIAY_MOI_LUOT  = 20;  // chạy trong yêu cầu web (WP-Cron, nút "Lấy ngay"): hợp giới hạn của hosting phổ thông
const CADAO_GIAY_MOI_LUOT_CLI = 120; // chạy bằng cron thật của máy chủ (WP-CLI): đọc được ~20 trang mỗi lượt

function cadao_giay_moi_luot(): int {
	return defined( 'WP_CLI' ) && WP_CLI ? CADAO_GIAY_MOI_LUOT_CLI : CADAO_GIAY_MOI_LUOT;
}

/** Các bộ đọc có sẵn. Site có cấu trúc riêng cần một bộ đọc mới — việc của agent (docs/24 mục 4). */
function cadao_bo_doc(): array {
	return array(
		'cadao_me' => array(
			'ten'   => 'Ca dao Mẹ (API + sitemap + trang)',
			'chay'  => 'cadao_doc_cadao_me',
			'loai'  => array(
				'ca_dao'            => 'Ca dao',
				'thanh_ngu_tuc_ngu' => 'Thành ngữ – tục ngữ',
				'dong_dao'          => 'Đồng dao',
				've'                => 'Vè',
				'cau_do'            => 'Câu đố',
			),
		),
	);
}

/** Cấu hình mặc định của một nguồn — mọi ô người quản trị chỉnh được ở trang Nguồn tự động. */
function cadao_nguon_mac_dinh(): array {
	return array(
		'id'              => '',
		'ten'             => '',
		'kieu'            => 'cadao_me',
		'url'             => '',
		'bat'             => false,
		'loai_lay'        => array( 'ca_dao', 'thanh_ngu_tuc_ngu', 'dong_dao', 've', 'cau_do' ),
		'so_bai_moi_ngay' => 20,
		'dong_ghi_nguon'  => '',
		'chep_chu_thich'  => true,  // D-055: chép chú thích, lời giải (đáp án câu đố) được phép; tắt được bất cứ lúc nào
		'ghi_cong'        => false, // D-057, D-068: dòng "Nguồn: …" dưới chú thích, lời giải đã chép — mặc định tắt; nguồn ở trang Chính sách thu thập dữ liệu
		'bo_qua_co'       => array( 'tho_tuc' ),
		'tu_tho_tuc'      => array( 'đụ', 'địt', 'lồn', 'buồi', 'cặc', 'đéo' ),
		'nguoi_duyet'     => 0,
		'trang_thai_doc'  => array(),
		'nhat_ky'         => array(),
	);
}

function cadao_nguon_tat_ca(): array {
	$ds = get_option( CADAO_TUY_CHON_NGUON, array() );
	return is_array( $ds ) ? $ds : array();
}

function cadao_nguon_lay( string $id ): ?array {
	$ds = cadao_nguon_tat_ca();
	return isset( $ds[ $id ] ) ? $ds[ $id ] + cadao_nguon_mac_dinh() : null;
}

function cadao_nguon_luu( array $nguon ): void {
	$ds                 = cadao_nguon_tat_ca();
	$ds[ $nguon['id'] ] = $nguon;
	update_option( CADAO_TUY_CHON_NGUON, $ds, false );
}

function cadao_nguon_xoa( string $id ): void {
	$ds = cadao_nguon_tat_ca();
	unset( $ds[ $id ] );
	update_option( CADAO_TUY_CHON_NGUON, $ds, false );
}

/** Lần đầu bật plugin: thêm sẵn nguồn Ca dao Mẹ, ở trạng thái TẮT — người quản trị tự bật. */
function cadao_nguon_tao_mac_dinh(): void {
	if ( cadao_nguon_tat_ca() ) {
		return;
	}
	cadao_nguon_luu(
		array(
			'id'             => 'cadao-me',
			'ten'            => 'Ca dao Mẹ',
			'url'            => 'https://cadao.me',
			'dong_ghi_nguon' => 'Ca dao Mẹ',
		) + cadao_nguon_mac_dinh()
	);
}

/** Ghi một dòng nhật ký cho nguồn (giữ 30 dòng gần nhất). */
function cadao_nguon_ghi( array &$nguon, string $viec, array $kq ): void {
	array_unshift( $nguon['nhat_ky'], array( 'luc' => current_time( 'mysql' ), 'viec' => $viec ) + $kq );
	$nguon['nhat_ky'] = array_slice( $nguon['nhat_ky'], 0, 30 );
}

/** Chạy bộ đọc của một nguồn một lượt. */
function cadao_nguon_chay( string $id, ?int $giay = null ): array {
	$giay ??= cadao_giay_moi_luot();
	$nguon = cadao_nguon_lay( $id );
	$bo    = $nguon ? ( cadao_bo_doc()[ $nguon['kieu'] ] ?? null ) : null;
	if ( ! $nguon || ! $bo ) {
		return array( 'yeu_cau' => 0, 'moi' => 0, 'cap_nhat' => 0, 'ly_do' => 'không có nguồn hoặc bộ đọc' );
	}
	// Khóa: hai lượt không chạy chồng nhau
	if ( get_transient( 'cadao_dang_chay_' . $id ) ) {
		return array( 'yeu_cau' => 0, 'moi' => 0, 'cap_nhat' => 0, 'ly_do' => 'lượt trước đang chạy' );
	}
	set_transient( 'cadao_dang_chay_' . $id, 1, $giay + 60 );
	try {
		$kq = call_user_func_array( $bo['chay'], array( &$nguon, microtime( true ) + $giay ) );
	} finally {
		delete_transient( 'cadao_dang_chay_' . $id );
	}
	// Chỉ ghi điểm dừng và nhật ký lên cấu hình MỚI NHẤT: người quản trị có thể đã sửa nguồn trong lúc lượt chạy
	wp_cache_delete( CADAO_TUY_CHON_NGUON, 'options' ); // đọc lại từ cơ sở dữ liệu, không dùng bản nhớ đệm đầu lượt
	wp_cache_delete( 'alloptions', 'options' );
	$moi = cadao_nguon_lay( $id );
	if ( $moi ) {
		$moi['trang_thai_doc'] = $nguon['trang_thai_doc'];
		cadao_nguon_ghi( $moi, 'lấy bài', $kq );
		cadao_nguon_luu( $moi );
	}
	return $kq;
}

// ---------- Lịch ----------
add_filter(
	'cron_schedules',
	static function ( $lich ) {
		$lich['cadao_5_phut'] = array( 'interval' => 5 * MINUTE_IN_SECONDS, 'display' => 'Mỗi 5 phút (Ca dao)' );
		return $lich;
	}
);

function cadao_lich_dat(): void {
	if ( ! wp_next_scheduled( 'cadao_thu_thap' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'cadao_5_phut', 'cadao_thu_thap' );
	}
	if ( ! wp_next_scheduled( 'cadao_tao_bai_hang_ngay' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 06:00' ) ?: time() + DAY_IN_SECONDS, 'daily', 'cadao_tao_bai_hang_ngay' );
	}
}

function cadao_lich_go(): void {
	wp_clear_scheduled_hook( 'cadao_thu_thap' );
	wp_clear_scheduled_hook( 'cadao_tao_bai_hang_ngay' );
}

add_action(
	'cadao_thu_thap',
	static function () {
		foreach ( cadao_nguon_tat_ca() as $id => $n ) {
			if ( ! empty( $n['bat'] ) ) {
				cadao_nguon_chay( (string) $id );
			}
		}
	}
);

add_action(
	'cadao_tao_bai_hang_ngay',
	static function () {
		foreach ( array_keys( cadao_nguon_tat_ca() ) as $id ) {
			$n = cadao_nguon_lay( (string) $id );
			if ( $n && $n['bat'] && $n['so_bai_moi_ngay'] > 0 ) {
				$so = cadao_kho_tao_bai_cho_duyet( $n, (int) $n['so_bai_moi_ngay'] );
				cadao_nguon_ghi( $n, 'tạo bài chờ duyệt', array( 'bai' => $so ) );
				cadao_nguon_luu( $n );
			}
		}
	}
);
