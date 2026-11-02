<?php
/**
 * Plugin Name:       Phongdao — Thư viện Văn học Dân gian Việt Nam
 * Description:       Bản V2 xây mới (D-083, D-094): mỗi phần của bài lưu trong bài, trang đọc theo thể loại, sửa ngay trên trang đọc + Hộp việc. Đang xây trong wordpress/v2/; máy chủ chưa lấy (docs/26).
 * Version:           0.4.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Author:            Dự án Thư viện Văn học Dân gian Việt Nam
 * Author URI:        https://github.com/truonganhquyen/ca-dao
 * License:           MIT
 * Text Domain:       ca-dao
 *
 * Thiết kế: docs/26-kien-truc-v2.md (kỹ thuật), docs/27 (trang người đọc), docs/28 (biên tập), design/v2/ (hình ảnh).
 * Mã nằm trong src/ theo không gian tên Phongdao\; mỗi mảng chức năng là một module (HaTang\Module).
 */

defined( 'ABSPATH' ) || exit;

/** Tăng ở mỗi PR chạm plugin V2 (AGENTS.md); PR chuyển đổi đặt 1.0.0 (docs/26 mục 2). */
const CADAO_PHIEN_BAN = '0.4.0';

require_once __DIR__ . '/src/tu-nap.php';

\Phongdao\HaTang\KhoiDong::chay( __FILE__ );
