<?php
/**
 * Bộ đọc "cadao_me" — Ca dao Mẹ (cadao.me), qua API WordPress công khai của site (docs/24 mục 4).
 * Bản T-071: ca dao (lời, chủ đề). Dị bản, chú thích, thành ngữ–tục ngữ, đồng dao, vè, câu đố: T-073.
 * Lịch sự với máy chủ: tuần tự, ≥ 3 giây giữa hai yêu cầu, User-Agent ghi tên dự án, gặp 429/5xx thì dừng.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_UA          = 'KhoTangCaDao-ThuThap/1.0 (+https://github.com/truonganhquyen/ca-dao)';
const CADAO_NHIP_GIAY   = 3;
const CADAO_CHU_KY_NGAY = 30; // xong đợt đầu thì 30 ngày cập nhật bài mới/sửa một lần

/** Chủ đề của nguồn cần xem lại trước khi chọn (docs/21 mục 16.3) — chỉ để gắn cờ, không xóa. */
const CADAO_CHU_DE_CAN_XEM = array( 'Trào phúng, phê phán đả kích', 'Đấu tranh, phản kháng', 'Lịch sử' );

class Cadao_Dung_Lai extends Exception {}

/**
 * Một yêu cầu GET tới nguồn, giữ nhịp. 429/5xx/lỗi mạng/hết giờ → Cadao_Dung_Lai (lượt sau làm tiếp).
 *
 * @return array{than: string, dau: array}|null null khi 4xx (bỏ qua mục đó)
 */
function cadao_lay( string $url, float $han ): ?array {
	static $lan_cuoi = 0.0;
	$cho = $lan_cuoi + CADAO_NHIP_GIAY - microtime( true );
	if ( microtime( true ) + max( 0, $cho ) + 5 > $han ) {
		throw new Cadao_Dung_Lai( 'hết thời gian của lượt này' );
	}
	if ( $cho > 0 ) {
		usleep( (int) ( $cho * 1e6 ) );
	}
	$lan_cuoi = microtime( true );
	$tl       = wp_remote_get( $url, array( 'timeout' => 15, 'user-agent' => CADAO_UA, 'redirection' => 3 ) );
	if ( is_wp_error( $tl ) ) {
		throw new Cadao_Dung_Lai( 'lỗi mạng: ' . $tl->get_error_message() );
	}
	$ma = (int) wp_remote_retrieve_response_code( $tl );
	if ( 429 === $ma || $ma >= 500 ) {
		throw new Cadao_Dung_Lai( "máy chủ nguồn trả $ma — dừng, để lượt sau" );
	}
	if ( $ma >= 400 ) {
		return null;
	}
	return array( 'than' => wp_remote_retrieve_body( $tl ), 'dau' => wp_remote_retrieve_headers( $tl ) );
}

/** Cờ gợi ý của một mục (không xóa gì): can_xem, tho_tuc, hien_dai. */
function cadao_gan_co( string $loi, array $chu_de, array $tu_tho_tuc ): array {
	$co = array();
	if ( array_intersect( $chu_de, CADAO_CHU_DE_CAN_XEM ) ) {
		$co[] = 'can_xem';
	}
	$thuong = mb_strtolower( $loi );
	$am_tiet = preg_split( '/[^\p{L}\p{M}]+/u', $thuong, -1, PREG_SPLIT_NO_EMPTY );
	foreach ( $tu_tho_tuc as $tu ) {
		$tu = mb_strtolower( trim( $tu ) );
		if ( '' !== $tu && ( str_contains( $tu, ' ' ) ? str_contains( $thuong, $tu ) : in_array( $tu, $am_tiet, true ) ) ) {
			$co[] = 'tho_tuc';
			break;
		}
	}
	if ( preg_match( '/\b(19|20)\d{2}\b/', $loi ) ) {
		$co[] = 'hien_dai';
	}
	return $co;
}

/**
 * Chạy bộ đọc tới khi hết giờ của lượt. Cập nhật $nguon['trang_thai_doc'] (điểm dừng).
 *
 * @return array{yeu_cau: int, moi: int, cap_nhat: int, ly_do: string}
 */
function cadao_doc_cadao_me( array &$nguon, float $han ): array {
	$goc = untrailingslashit( $nguon['url'] ?: 'https://cadao.me' );
	if ( ! is_array( $nguon['trang_thai_doc'] ?? null ) ) {
		$nguon['trang_thai_doc'] = array();
	}
	$t = &$nguon['trang_thai_doc'];
	$t  += array( 'trang_tiep' => 1, 'xong' => false, 'lan_cuoi' => null, 'moc_sua' => null, 'chu_de' => array(), 'chu_de_ngay' => null );
	$kq  = array( 'yeu_cau' => 0, 'moi' => 0, 'cap_nhat' => 0, 'ly_do' => 'xong việc của lượt này' );

	try {
		// Đã xong đợt đầu và chưa tới hạn cập nhật: không làm gì
		if ( $t['xong'] && $t['lan_cuoi'] && ( time() - strtotime( $t['lan_cuoi'] ) ) < CADAO_CHU_KY_NGAY * DAY_IN_SECONDS ) {
			$kq['ly_do'] = 'đã lấy hết, chờ tới kỳ cập nhật';
			return $kq;
		}
		// Tên chủ đề (để gắn cờ và lưu làm gợi ý nội bộ)
		if ( ! $t['chu_de'] || ! $t['chu_de_ngay'] || ( time() - strtotime( $t['chu_de_ngay'] ) ) > CADAO_CHU_KY_NGAY * DAY_IN_SECONDS ) {
			$r = cadao_lay( "$goc/wp-json/wp/v2/categories?per_page=100&_fields=id,name", $han );
			++$kq['yeu_cau'];
			foreach ( $r ? (array) json_decode( $r['than'], true ) : array() as $c ) {
				$t['chu_de'][ (int) $c['id'] ] = cadao_van_ban( (string) $c['name'] );
			}
			$t['chu_de_ngay'] = gmdate( 'Y-m-d' );
		}
		if ( $t['xong'] ) { // tới kỳ cập nhật: lấy lại từ đầu, chỉ bài sửa sau mốc
			$t['xong']       = false;
			$t['trang_tiep'] = 1;
			$t['moc_dang']   = $t['moc_sua'];
		}
		$loc = ! empty( $t['moc_dang'] ) ? '&modified_after=' . rawurlencode( $t['moc_dang'] ) : '';
		for ( ;; ) {
			$url = "$goc/wp-json/wp/v2/posts?per_page=100&page={$t['trang_tiep']}&orderby=id&order=asc$loc&_fields=id,link,title,content,categories,modified";
			$r   = cadao_lay( $url, $han );
			++$kq['yeu_cau'];
			if ( ! $r ) {
				break;
			}
			foreach ( (array) json_decode( $r['than'], true ) as $p ) {
				$trich  = cadao_trich_loi( (string) ( $p['content']['rendered'] ?? '' ) );
				if ( '' === $trich['loi'] ) {
					continue;
				}
				$chu_de = array_values( array_filter( array_map( static fn( $id ) => $t['chu_de'][ (int) $id ] ?? null, (array) ( $p['categories'] ?? array() ) ) ) );
				$tt     = cadao_kho_luu(
					$nguon['id'],
					array(
						'ma_goc'       => $p['id'],
						'url'          => (string) $p['link'],
						'loai'         => 'ca_dao',
						'tieu_de'      => cadao_van_ban( (string) ( $p['title']['rendered'] ?? '' ) ),
						'loi'          => $trich['loi'],
						'chu_de_goc'   => $chu_de,
						'co'           => cadao_gan_co( $trich['loi'], $chu_de, $nguon['tu_tho_tuc'] ?? array() ),
						'ngay_sua_goc' => substr( (string) $p['modified'], 0, 10 ),
					)
				);
				if ( 'moi' === $tt ) {
					++$kq['moi'];
				} elseif ( 'cap_nhat' === $tt ) {
					++$kq['cap_nhat'];
				}
				if ( empty( $t['moc_moi'] ) || (string) $p['modified'] > $t['moc_moi'] ) {
					$t['moc_moi'] = (string) $p['modified'];
				}
			}
			$tong = (int) ( $r['dau']['x-wp-totalpages'] ?? 1 );
			if ( $t['trang_tiep'] >= $tong ) {
				$t['xong']     = true;
				$t['lan_cuoi'] = gmdate( 'Y-m-d' );
				$t['moc_sua']  = $t['moc_moi'] ?? $t['moc_sua'];
				unset( $t['moc_dang'] );
				$kq['ly_do'] = 'đã lấy hết ca dao qua API';
				break;
			}
			++$t['trang_tiep'];
		}
	} catch ( Cadao_Dung_Lai $e ) {
		$kq['ly_do'] = $e->getMessage();
	}
	return $kq;
}
