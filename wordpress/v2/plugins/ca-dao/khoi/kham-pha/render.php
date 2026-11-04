<?php
/**
 * Khối cadao/kham-pha: trang Khám phá, Chủ đề, Mô-típ, Một câu ngẫu nhiên, Hay bị nhầm (theo đường dẫn đang xem).
 */

defined( 'ABSPATH' ) || exit;

echo \Phongdao\GiaoTiep\Khoi\KhamPha::hien( \Phongdao\GiaoTiep\TrangCoDinh\Module::dang_xem() ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong KhamPha.
