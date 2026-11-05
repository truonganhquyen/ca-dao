<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\KhoiDong;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Bản đồ /ban-do/ (bộ thiết kế R15; chuyển từ MVP ban-do.php): bản đồ Việt Nam tự lưu (assets/ban-do-vn.svg, có
 * Hoàng Sa, Trường Sa) với điểm các địa danh người biên soạn đã xác nhận trên Wikidata, kèm danh sách Bắc → Nam. Không
 * gọi dịch vụ bản đồ nào, không cần JavaScript (điểm là liên kết tới mục trong danh sách). Địa danh là nơi được
 * nhắc đến trong lời, không phải nơi lưu truyền.
 */
final class BanDo {

	/** Khung bản đồ: kinh độ tây, đông; vĩ độ bắc, nam (như MVP CADAO_BAN_DO_KHUNG). */
	public const KHUNG = array( 102.0, 117.6, 23.6, 8.0 );

	/** 18% của ảnh rộng tối thiểu 256 px ở màn 320 px: hai vùng chạm 44 px không đè nhau. */
	private const GAN = 18.0;

	/**
	 * Địa danh đã xác nhận có toạ độ trong khung và có bài đang đăng; từ Bắc vào Nam.
	 *
	 * @return list<array{muc:\WP_Term,mo_ta:string,lat:float,x:float,y:float,bai:list<\WP_Post>}>
	 */
	public static function diem(): array {
		global $wpdb;
		[ $tay, $dong, $bac, $nam ] = self::KHUNG;
		$ds = array();
		foreach ( $wpdb->get_results( "SELECT term_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = '_phongdao_wikidata'" ) as $r ) {
			$wd = maybe_unserialize( $r->meta_value );
			if ( ! is_array( $wd ) || empty( $wd['toa_do'] ) || ! is_array( $wd['toa_do'] ) ) {
				continue;
			}
			[ $lat, $lon ] = array_map( 'floatval', array_values( $wd['toa_do'] ) + array( 0, 0 ) );
			$muc           = get_term( (int) $r->term_id, 'muc_tu' );
			if ( $lon < $tay || $lon > $dong || $lat > $bac || $lat < $nam || ! $muc instanceof \WP_Term ) {
				continue;
			}
			$bai = get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'numberposts' => 30, 'orderby' => 'title', 'order' => 'ASC', 'tax_query' => array( array( 'taxonomy' => 'muc_tu', 'terms' => $muc->term_id ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery -- vài trăm địa danh, mỗi địa danh vài bài
			if ( $bai ) {
				// Mô tả Wikidata chỉ dùng khi là tiếng Việt (thiếu bản tiếng Việt thì Wikidata trả tiếng Anh)
				$mo_ta = (string) ( $wd['mo_ta'] ?? '' );
				$ds[]  = array(
					'muc'   => $muc,
					'mo_ta' => preg_match( '/[^\x00-\x7F]/', $mo_ta ) ? $mo_ta : '',
					'lat'   => $lat,
					'x'     => round( ( $lon - $tay ) / ( $dong - $tay ) * 100, 2 ),
					'y'     => round( ( $bac - $lat ) / ( $bac - $nam ) * 100, 2 ),
					'bai'   => $bai,
				);
			}
		}
		usort( $ds, static fn( $a, $b ) => $b['lat'] <=> $a['lat'] );
		return $ds;
	}

	/** Gom các điểm theo vùng chạm 44 px; giữ điểm trong mép ảnh ở màn hẹp. */
	public static function nhom_diem( array $diem ): array {
		$nhom = array();
		foreach ( $diem as $d ) {
			$x = min( 90.0, max( 10.0, (float) $d['x'] ) );
			$y = min( 90.0, max( 10.0, (float) $d['y'] ) );
			foreach ( array_keys( $nhom ) as $i ) {
				if ( abs( $nhom[ $i ]['x'] - $x ) < self::GAN && abs( $nhom[ $i ]['y'] - $y ) < self::GAN ) {
					$nhom[ $i ]['ds'][] = $d;
					continue 2;
				}
			}
			$nhom[] = array( 'x' => $x, 'y' => $y, 'ds' => array( $d ) );
		}
		return $nhom;
	}

	public static function hien(): string {
		$h    = '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Bản đồ</span></nav>'
			. '<p class="eyebrow">Vùng &amp; địa danh</p><h1>Lần theo những địa danh.</h1>';
		$diem = self::diem();
		if ( ! $diem ) {
			return $h . '<div class="empty"><h2>Chưa có địa danh nào được xác nhận</h2><p>Bản đồ có điểm khi người biên soạn xác nhận các địa danh được chú thích trong bài.</p>' . Ve::lien_ket( 'tu-dien', 'Mở Từ điển', 'btn primary' ) . '</div>';
		}
		$so_bai = count( array_unique( array_merge( ...array_map( static fn( $d ) => wp_list_pluck( $d['bai'], 'ID' ), $diem ) ) ) );
		$h     .= '<p class="muted">' . count( $diem ) . ' địa danh trong ' . $so_bai . ' bài, xếp Bắc → Nam. Mỗi địa danh mở các bài <strong>nhắc đến</strong> nơi đó — không đồng nghĩa là nơi lưu truyền.</p>';

		$ds = '';
		foreach ( $diem as $d ) {
			$bai = implode( '', array_map( static fn( \WP_Post $p ): string => '<li><a href="' . esc_url( (string) get_permalink( $p ) ) . '">' . esc_html( $p->post_title ) . '</a></li>', $d['bai'] ) );
			$url = get_term_link( $d['muc'] );
			$ds .= '<div class="list-row" id="dd-' . (int) $d['muc']->term_id . '" tabindex="-1"><div><span class="tag">Được nhắc đến · ' . count( $d['bai'] ) . ' bài</span>'
				. '<h2><a href="' . esc_url( is_wp_error( $url ) ? '' : $url ) . '">' . esc_html( $d['muc']->name ) . '</a></h2>'
				. ( '' === $d['mo_ta'] ? '' : '<p class="small muted">' . esc_html( $d['mo_ta'] ) . '</p>' ) . '<ul>' . $bai . '</ul></div></div>';
		}
		// Điểm trên bản đồ: vị trí theo phần trăm khung ảnh; điểm gần nhau gộp, ghi số địa danh
		$nhom = self::nhom_diem( $diem );
		$cham = '';
		foreach ( $nhom as $n ) {
			$ten   = implode( ', ', array_map( static fn( $d ) => $d['muc']->name, $n['ds'] ) );
			$so    = array_sum( array_map( static fn( $d ) => count( $d['bai'] ), $n['ds'] ) );
			$cham .= '<a href="#dd-' . (int) $n['ds'][0]['muc']->term_id . '" style="position:absolute;left:' . $n['x'] . '%;top:' . $n['y'] . '%;width:44px;height:44px;margin:-22px 0 0 -22px;border-radius:50%;display:grid;place-items:center;text-decoration:none">'
				. '<span aria-hidden="true" style="width:28px;height:28px;border-radius:50%;background:var(--tim);border:3px solid var(--white);color:var(--white);font-size:12px;font-weight:700;display:grid;place-items:center">' . ( count( $n['ds'] ) > 1 ? count( $n['ds'] ) : '' ) . '</span>'
				. '<span class="sr-only">' . esc_html( $ten . ' — ' . $so . ' bài' ) . '</span></a>';
		}
		$anh = plugins_url( 'assets/ban-do-vn.svg', KhoiDong::thu_muc() . '/ca-dao.php' );
		return $h . '<div class="grid two map-grid section"><div>' . $ds . '</div>'
			. '<figure class="map-area" style="padding:12px;display:block;margin:0"><div style="position:relative;max-width:460px;margin:0 auto"><img src="' . esc_url( $anh ) . '" width="460" height="460" style="width:100%;height:auto;display:block" alt="Bản đồ Việt Nam, có quần đảo Hoàng Sa và Trường Sa">' . $cham . '</div>'
			. '<figcaption class="small muted">Chạm một điểm để tới địa danh trong danh sách.</figcaption></figure></div>';
	}
}
