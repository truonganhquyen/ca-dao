<?php

namespace Phongdao\DichVu\NhapNguon;

use Phongdao\KhoLuu\Kho;
use Phongdao\MienDuLieu\TrichHtml;

defined( 'ABSPATH' ) || exit;

/**
 * Bộ đọc Ca dao Mẹ (chuyển nguyên thuật toán MVP inc/doc-cadao-me.php, kèm kiểm thử):
 *   1. Tên chủ đề (API /categories)                                — nhịp API
 *   2. Ca dao qua API /posts: lời, chủ đề                            — nhịp API
 *   3. Thành ngữ – tục ngữ, đồng dao, vè, câu đố: danh sách từ sitemap — nhịp trang
 *   4. Trang HTML: dị bản, chú thích, câu hỏi / đáp án câu đố; lời của các loại ngoài ca dao — nhịp trang
 * Lịch sự với máy chủ nguồn: tuần tự, ≥ 3 giây (API) / ≥ 5 giây (trang) giữa hai yêu cầu, User-Agent ghi tên dự án,
 * gặp 429 / 5xx thì dừng tới lượt sau, lưu điểm dừng. Không tải ảnh. Không qua AI.
 */
final class CadaoMe {

	public const UA = 'KhoTangCaDao-ThuThap/1.0 (+https://github.com/truonganhquyen/ca-dao)';

	public const NHIP_API   = 3;
	public const NHIP_TRANG = 5;

	/** Xong đợt đầu thì 30 ngày cập nhật bài mới / sửa một lần. */
	public const CHU_KY_NGAY = 30;

	/** Chủ đề của nguồn cần xem lại trước khi chọn (docs/12 mục 6) — chỉ gắn cờ, không xoá. */
	public const CHU_DE_CAN_XEM = array( 'Trào phúng, phê phán đả kích', 'Đấu tranh, phản kháng', 'Lịch sử' );

	public const LOAI = array(
		'ca_dao'            => 'Ca dao',
		'thanh_ngu_tuc_ngu' => 'Thành ngữ – tục ngữ',
		'dong_dao'          => 'Đồng dao',
		've'                => 'Vè',
		'cau_do'            => 'Câu đố',
	);

	/** Loại ngoài ca dao → sitemap liệt kê bài của loại đó. */
	public const SITEMAP = array(
		'thanh_ngu_tuc_ngu' => array( 'thanh-ngu-tuc-ngu-sitemap.xml', 'thanh-ngu-tuc-ngu-sitemap2.xml', 'thanh-ngu-tuc-ngu-sitemap3.xml', 'thanh-ngu-tuc-ngu-sitemap4.xml' ),
		'dong_dao'          => array( 'dong-dao-sitemap.xml' ),
		've'                => array( 've-sitemap.xml' ),
		'cau_do'            => array( 'cau-do-sitemap.xml' ),
	);

	private static float $lan_cuoi = 0.0;

	/**
	 * Một yêu cầu GET tới nguồn, giữ nhịp. 429 / 5xx / lỗi mạng / hết giờ → DungLai.
	 *
	 * @return array{than:string,dau:mixed,url:string}|null null khi 4xx (bỏ qua mục đó)
	 */
	public static function lay( string $url, float $han, int $nhip = self::NHIP_API ): ?array {
		$nhip = (int) apply_filters( 'phongdao_nhip_giay', $nhip ); // kiểm thử đặt 0
		$cho  = self::$lan_cuoi + $nhip - microtime( true );
		if ( microtime( true ) + max( 0, $cho ) + 5 > $han ) {
			throw new DungLai( 'hết thời gian của lượt này' );
		}
		if ( $cho > 0 ) {
			usleep( (int) ( $cho * 1e6 ) );
		}
		self::$lan_cuoi = microtime( true );
		$tl             = wp_remote_get( $url, array( 'timeout' => 15, 'user-agent' => self::UA, 'redirection' => 3 ) );
		if ( is_wp_error( $tl ) ) {
			throw new DungLai( 'lỗi mạng: ' . $tl->get_error_message() );
		}
		$ma = (int) wp_remote_retrieve_response_code( $tl );
		if ( 429 === $ma || $ma >= 500 ) {
			throw new DungLai( "máy chủ nguồn trả $ma — dừng, để lượt sau" );
		}
		if ( $ma >= 400 ) {
			return null;
		}
		return array( 'than' => wp_remote_retrieve_body( $tl ), 'dau' => wp_remote_retrieve_headers( $tl ), 'url' => $url );
	}

	/** Cờ gợi ý của một mục (không xoá gì): can_xem, tho_tuc, hien_dai. */
	public static function gan_co( string $loi, array $chu_de, array $tu_tho_tuc ): array {
		$co = array();
		if ( array_intersect( $chu_de, self::CHU_DE_CAN_XEM ) ) {
			$co[] = 'can_xem';
		}
		$thuong  = mb_strtolower( $loi );
		$am_tiet = preg_split( '/[^\p{L}\p{M}]+/u', $thuong, -1, PREG_SPLIT_NO_EMPTY ) ?: array();
		foreach ( $tu_tho_tuc as $tu ) {
			$tu = mb_strtolower( trim( (string) $tu ) );
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
	 * @return array{yeu_cau:int,moi:int,cap_nhat:int,trang:int,ly_do:string}
	 */
	public static function chay( array &$nguon, float $han ): array {
		$goc = untrailingslashit( $nguon['url'] ?: 'https://cadao.me' );
		if ( ! is_array( $nguon['trang_thai_doc'] ?? null ) ) {
			$nguon['trang_thai_doc'] = array();
		}
		$t   = &$nguon['trang_thai_doc'];
		$t  += array( 'trang_tiep' => 1, 'xong' => false, 'lan_cuoi' => null, 'moc_sua' => null, 'chu_de' => array(), 'chu_de_ngay' => null, 'sitemap_ngay' => null );
		$kq  = array( 'yeu_cau' => 0, 'moi' => 0, 'cap_nhat' => 0, 'trang' => 0, 'ly_do' => 'xong việc của lượt này' );
		$dem = static function ( string $tt ) use ( &$kq ): void {
			if ( 'moi' === $tt ) {
				++$kq['moi'];
			} elseif ( 'cap_nhat' === $tt ) {
				++$kq['cap_nhat'];
			}
		};
		$qua_han = static fn( $ngay ): bool => ! $ngay || ( time() - strtotime( (string) $ngay ) ) > self::CHU_KY_NGAY * DAY_IN_SECONDS;
		$loai    = $nguon['loai_lay'] ?: array( 'ca_dao' );

		try {
			// 1. Tên chủ đề (để gắn cờ và lưu làm gợi ý nội bộ)
			if ( ! $t['chu_de'] || $qua_han( $t['chu_de_ngay'] ) ) {
				$r = self::lay( "$goc/wp-json/wp/v2/categories?per_page=100&_fields=id,name", $han );
				++$kq['yeu_cau'];
				foreach ( $r ? (array) json_decode( $r['than'], true ) : array() as $c ) {
					$t['chu_de'][ (int) $c['id'] ] = TrichHtml::van_ban( (string) $c['name'] );
				}
				$t['chu_de_ngay'] = gmdate( 'Y-m-d' );
			}
			// 2. Ca dao qua API (xong đợt đầu thì 30 ngày lấy lại bài sửa sau mốc một lần)
			if ( in_array( 'ca_dao', $loai, true ) && ! ( $t['xong'] && ! $qua_han( $t['lan_cuoi'] ) ) ) {
				self::api( $nguon, $goc, $han, $kq, $dem );
			}
			// 3. Danh sách các loại khác qua sitemap
			$khac = array_intersect( array_keys( self::SITEMAP ), $loai );
			if ( $khac && $qua_han( $t['sitemap_ngay'] ) ) {
				foreach ( $khac as $l ) {
					foreach ( self::SITEMAP[ $l ] as $tep ) {
						$r = self::lay( "$goc/$tep", $han, self::NHIP_TRANG );
						++$kq['yeu_cau'];
						foreach ( $r ? TrichHtml::sitemap( $r['than'] ) : array() as $u ) {
							$duong = (string) wp_parse_url( $u['url'], PHP_URL_PATH );
							if ( count( array_filter( explode( '/', $duong ), 'strlen' ) ) < 2 ) {
								continue; // trang danh mục, không phải bài
							}
							// Mã gốc của bài ngoài ca dao: đường dẫn trên nguồn (bền, duy nhất)
							$dem( Kho::luu( $nguon['id'], array( 'ma_goc' => untrailingslashit( rawurldecode( $duong ) ), 'url' => $u['url'], 'loai' => $l, 'ngay_sua_goc' => substr( (string) $u['lastmod'], 0, 10 ) ) ) );
						}
					}
				}
				$t['sitemap_ngay'] = gmdate( 'Y-m-d' );
			}
			// 4. Trang HTML: dị bản, chú thích (mục đã thành tác phẩm trước, rồi xen kẽ các loại)
			self::doc_trang( $nguon, $loai, $han, $kq );
		} catch ( DungLai $e ) {
			$kq['ly_do'] = $e->getMessage();
		}
		return $kq;
	}

	/** Bước 2: ca dao qua API, có điểm dừng; bài nguồn chưa sửa thì bỏ qua (giữ dị bản, chú thích đã đọc). */
	private static function api( array &$nguon, string $goc, float $han, array &$kq, callable $dem ): void {
		global $wpdb;
		$t = &$nguon['trang_thai_doc'];
		if ( $t['xong'] ) { // tới kỳ cập nhật: lấy lại từ đầu, chỉ bài sửa sau mốc
			$t['xong']       = false;
			$t['trang_tiep'] = 1;
			$t['moc_dang']   = $t['moc_sua'];
		}
		$loc  = ! empty( $t['moc_dang'] ) ? '&modified_after=' . rawurlencode( (string) $t['moc_dang'] ) : '';
		$bang = Kho::bang();
		for ( ;; ) {
			$r = self::lay( "$goc/wp-json/wp/v2/posts?per_page=100&page={$t['trang_tiep']}&orderby=id&order=asc$loc&_fields=id,link,title,content,categories,modified", $han );
			++$kq['yeu_cau'];
			if ( ! $r ) {
				break;
			}
			foreach ( (array) json_decode( $r['than'], true ) as $p ) {
				$ngay_sua = substr( (string) $p['modified'], 0, 10 );
				$cu       = $wpdb->get_var( $wpdb->prepare( "SELECT ngay_sua_goc FROM $bang WHERE nguon_id = %s AND ma_goc = %s", $nguon['id'], (string) $p['id'] ) );
				if ( null !== $cu && $cu === $ngay_sua ) {
					continue;
				}
				$trich = TrichHtml::loi( (string) ( $p['content']['rendered'] ?? '' ) );
				if ( '' === $trich['loi'] ) {
					continue;
				}
				$chu_de = array_values( array_filter( array_map( static fn( $id ) => $nguon['trang_thai_doc']['chu_de'][ (int) $id ] ?? null, (array) ( $p['categories'] ?? array() ) ) ) );
				$dem(
					Kho::luu(
						$nguon['id'],
						array(
							'ma_goc'       => $p['id'],
							'url'          => (string) $p['link'],
							'loai'         => 'ca_dao',
							'tieu_de'      => TrichHtml::van_ban( (string) ( $p['title']['rendered'] ?? '' ) ),
							'loi'          => $trich['loi'],
							'chu_de_goc'   => $chu_de,
							'co'           => self::gan_co( $trich['loi'], $chu_de, $nguon['tu_tho_tuc'] ?? array() ),
							'ngay_sua_goc' => $ngay_sua,
						)
					)
				);
				if ( empty( $t['moc_moi'] ) || (string) $p['modified'] > $t['moc_moi'] ) {
					$t['moc_moi'] = (string) $p['modified'];
				}
			}
			if ( $t['trang_tiep'] >= (int) ( $r['dau']['x-wp-totalpages'] ?? 1 ) ) {
				$t['xong']     = true;
				$t['lan_cuoi'] = gmdate( 'Y-m-d' );
				$t['moc_sua']  = $t['moc_moi'] ?? $t['moc_sua'];
				unset( $t['moc_dang'] );
				return;
			}
			++$t['trang_tiep'];
		}
	}

	/** Bước 4: đọc trang HTML của các mục chưa đọc. */
	private static function doc_trang( array $nguon, array $loai, float $han, array &$kq ): void {
		global $wpdb;
		$bang = Kho::bang();
		$dk   = implode( ',', array_map( static fn( $l ) => $wpdb->prepare( '%s', $l ), $loai ) );
		for ( ;; ) {
			// Mục đã thành tác phẩm trước (để bài đã đăng sớm có dị bản, chú thích); hết thì xen kẽ các loại
			$hang_doi = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $bang WHERE nguon_id = %s AND doc_trang = '' AND loai IN ($dk) AND post_id > 0 ORDER BY id LIMIT 20", $nguon['id'] ) );
			if ( ! $hang_doi ) {
				$theo_loai = array();
				foreach ( $loai as $l ) {
					$theo_loai[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $bang WHERE nguon_id = %s AND doc_trang = '' AND loai = %s ORDER BY id LIMIT 10", $nguon['id'], $l ) );
				}
				for ( $i = 0; $i < 10; $i++ ) {
					foreach ( $theo_loai as $ds ) {
						if ( isset( $ds[ $i ] ) ) {
							$hang_doi[] = $ds[ $i ];
						}
					}
				}
			}
			if ( ! $hang_doi ) {
				$kq['ly_do'] = 'đã đọc hết trang của các mục';
				return;
			}
			foreach ( $hang_doi as $muc ) {
				if ( '' !== (string) $wpdb->get_var( $wpdb->prepare( "SELECT doc_trang FROM $bang WHERE id = %d", $muc->id ) ) ) {
					continue; // đã đọc nhờ trang trước
				}
				$r = self::lay( $muc->url, $han, self::NHIP_TRANG );
				++$kq['yeu_cau'];
				++$kq['trang'];
				if ( ! $r ) {
					$wpdb->update( $bang, array( 'doc_trang' => 'loi' ), array( 'id' => $muc->id ) );
					continue;
				}
				self::ghi_trang( $nguon, $r['than'], $muc, $han, $kq );
			}
		}
	}

	/** Ghi dị bản, chú thích… của mọi bài nhận được trên một trang. Bài chính không có trên trang → 'khong_thay'. */
	private static function ghi_trang( array $nguon, string $html, object $chinh, float $han, array &$kq ): void {
		global $wpdb;
		$bang  = Kho::bang();
		$trang = TrichHtml::trang( $html );
		$ct    = $trang['chu_thich'];
		$thay  = false;
		foreach ( $trang['bai'] as $x ) {
			$la_chinh = TrichHtml::cung_trang( $x['url'], $chinh->url );
			$hang     = $la_chinh ? $chinh : $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bang WHERE nguon_id = %s AND url = %s", $nguon['id'], $x['url'] ) );
			// Bài liên quan: chỉ ca dao chưa đọc, và lời trên trang trùng lời API (đã hiện đủ, không phải đoạn trích)
			if ( ! $hang || ( ! $la_chinh && ( 'ca_dao' !== $hang->loai || '' !== $hang->doc_trang || $x['loi'] !== $hang->loi ) ) ) {
				continue;
			}
			$thay   = $thay || $la_chinh;
			$tu_api = 'ca_dao' === $hang->loai && '' !== $hang->loi; // ca dao: lời chuẩn lấy từ API
			$loi    = $tu_api ? $hang->loi : $x['loi'];
			// Chú thích theo thứ tự xuất hiện trong lời rồi dị bản; chú thích không có trên trang thì lấy trang chi tiết
			$chu_thich = array();
			foreach ( array_merge( $x['vi_tri_chu_thich'], ...$x['vi_tri_chu_thich_di_ban'] ) as $v ) {
				$ma = (int) $v['ma'];
				if ( isset( $chu_thich[ $ma ] ) ) {
					continue;
				}
				if ( ! isset( $ct[ $ma ] ) ) {
					$r = self::lay( untrailingslashit( $nguon['url'] ?: 'https://cadao.me' ) . "/chu-thich/chi-tiet/$ma", $han, self::NHIP_TRANG );
					++$kq['yeu_cau'];
					$ct += $r ? TrichHtml::chu_thich( $r['than'] ) : array();
				}
				if ( isset( $ct[ $ma ] ) && '' !== $ct[ $ma ]['noi_dung'] ) {
					$chu_thich[ $ma ] = $ct[ $ma ];
				}
			}
			$chu_de = $x['chu_de_goc'] ?: ( Kho::du_lieu( $hang )['chu_de_goc'] ?? array() );
			$co     = self::gan_co( implode( "\n", array_merge( array( $loi ), $x['di_ban'] ) ), $chu_de, $nguon['tu_tho_tuc'] ?? array() );
			if ( $tu_api && $x['loi'] !== $hang->loi ) {
				$co[] = 'lech_api_html'; // lời API khác lời trên trang — cần xem tay
			}
			$muc = array(
				'ma_goc'     => $hang->ma_goc,
				'di_ban'     => $x['di_ban'],
				'chu_thich'  => array_values( $chu_thich ),
				'cau_hoi'    => $x['cau_hoi'],
				'dap_an'     => $x['dap_an'],
				'chu_de_goc' => $chu_de,
				'co'         => $co,
				'doc_trang'  => gmdate( 'Y-m-d' ),
			);
			if ( ! $tu_api ) {
				$muc += array( 'loi' => $x['loi'], 'tieu_de' => $x['tieu_de'] );
			}
			if ( 'cap_nhat' === Kho::luu( $nguon['id'], $muc ) ) {
				++$kq['cap_nhat'];
			}
		}
		if ( ! $thay ) {
			$wpdb->update( $bang, array( 'doc_trang' => 'khong_thay' ), array( 'id' => $chinh->id ) );
		}
	}
}
