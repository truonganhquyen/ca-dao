<?php
/**
 * Plugin Name:       Kho tàng Ca dao – Tục ngữ
 * Description:       Loại bài Ca dao – Tục ngữ, nguồn tự động có cấu hình trong trang quản trị, kho thu thập, tự tạo bài Chờ duyệt, trích dẫn và chia sẻ, lượt đọc không cookie.
 * Version:           0.4.0
 * Requires at least: 6.8
 * Requires PHP:      8.1
 * Author:            Dự án Kho tàng Ca dao Tục ngữ Việt Nam
 * Author URI:        https://github.com/truonganhquyen/ca-dao
 * License:           MIT
 * Text Domain:       ca-dao
 *
 * Thiết kế: docs/24-wordpress.md trong repo ca-dao (D-054, D-055, D-056).
 * Lời chép nguyên văn bằng máy, không qua AI; lời không ghi nguồn, chú thích chép từ nguồn có dòng "Nguồn: …" (D-057).
 */

defined( 'ABSPATH' ) || exit;

const CADAO_PHIEN_BAN = '0.4.0';
define( 'CADAO_THU_MUC', __DIR__ );

require_once CADAO_THU_MUC . '/inc/trich.php';
require_once CADAO_THU_MUC . '/inc/loai-bai.php';
require_once CADAO_THU_MUC . '/inc/kho.php';
require_once CADAO_THU_MUC . '/inc/doc-cadao-me.php';
require_once CADAO_THU_MUC . '/inc/nguon-tu-dong.php';
require_once CADAO_THU_MUC . '/inc/trang-quan-tri.php';
require_once CADAO_THU_MUC . '/inc/trich-dan.php';
require_once CADAO_THU_MUC . '/inc/luot-doc.php';

register_activation_hook( __FILE__, 'cadao_kich_hoat' );
register_deactivation_hook( __FILE__, 'cadao_tat' );

/** Bật plugin: bảng kho và lượt đọc, thể loại mặc định, nguồn Ca dao Mẹ (tắt sẵn), lịch chạy. */
function cadao_kich_hoat() {
	cadao_dang_ky_loai_bai();
	cadao_tao_the_loai_mac_dinh();
	cadao_kho_tao_bang();
	cadao_luot_doc_tao_bang();
	cadao_nguon_tao_mac_dinh();
	cadao_lich_dat();
	flush_rewrite_rules();
}

function cadao_tat() {
	cadao_lich_go();
	flush_rewrite_rules();
}
