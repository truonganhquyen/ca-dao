<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

use Phongdao\HaTang\KhaiBao;

defined( 'ABSPATH' ) || exit;

/**
 * Đối chiếu trước / sau ngày chuyển (docs/29 mục 5, bước 4; `wp phongdao doi-chieu`): bản kiểm kê MVP lưu lúc bắt đầu
 * nhập; sau khi nhập, đếm lại cùng định nghĩa ở phía V2 để so từng dòng. Chỉ đọc (trừ lần lưu bản "trước").
 */
final class DoiChieu {

	/** Bản kiểm kê lúc bắt đầu nhập: việc => số. */
	public const TRUOC = 'phongdao_kiem_ke_truoc';

	/** Lưu bản kiểm kê MVP một lần, trước khi bài đầu tiên được nhập. */
	public static function luu_truoc(): void {
		if ( false !== get_option( self::TRUOC ) ) {
			return;
		}
		$ra = array();
		foreach ( KiemKe::dem()['dong'] as [ , $viec, $so ] ) {
			$ra[ $viec ] = $so;
		}
		update_option( self::TRUOC, $ra, false );
	}

	/**
	 * Các dòng so: [việc, trước, sau]. Bài không mang (chờ duyệt do máy tạo) vẫn ở loại bài MVP nên "sau" đếm cả hai loại
	 * bài: không mất gì thì trước = sau từng dòng.
	 *
	 * @return list<array{0:string,1:?int,2:int}>
	 */
	public static function so(): array {
		global $wpdb;
		$p     = $wpdb->posts;
		$tp    = array( KhaiBao::TAC_PHAM, DuLieuCu::LOAI_BAI );
		$q     = static fn( string $sql, ...$a ): int => (int) $wpdb->get_var( $a ? $wpdb->prepare( $sql, ...$a ) : $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
		$phan  = static fn( string $ten ): int => $q( "SELECT COUNT(*) FROM {$wpdb->postmeta} m JOIN $p b ON b.ID = m.post_id WHERE b.post_type IN (%s, %s) AND b.post_status <> 'trash' AND m.meta_key = %s", ...array_merge( $tp, array( '_phongdao_' . $ten ) ) );
		$the   = static fn( string $tax ): int => $q( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", $tax );
		$gan   = static fn( string $tax ): int => $q( "SELECT COUNT(DISTINCT r.object_id) FROM {$wpdb->term_relationships} r JOIN {$wpdb->term_taxonomy} t ON t.term_taxonomy_id = r.term_taxonomy_id JOIN $p b ON b.ID = r.object_id WHERE t.taxonomy = %s AND b.post_type IN (%s, %s) AND b.post_status <> 'trash'", $tax, ...$tp );
		$bang  = static fn( string $ten ): int => $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $ten ) ) ? $q( "SELECT COUNT(*) FROM {$wpdb->prefix}$ten" ) : -1;
		$sau   = array(
			'đã đăng'                       => $q( "SELECT COUNT(*) FROM $p WHERE post_type IN (%s, %s) AND post_status = 'publish'", ...$tp ),
			'Hiểu nhanh đã lên trang'       => $phan( 'y_nghia' ),
			'bài gắn thể loại'              => $gan( KhaiBao::THE_LOAI ),
			'chủ đề'                        => $the( 'chu_de' ),
			'bài gắn chủ đề'                => $gan( 'chu_de' ),
			'vùng'                          => $the( 'vung' ),
			'bài gắn vùng'                  => $gan( 'vung' ),
			'mô-típ'                        => $the( 'mo_tip' ),
			'bài gắn mô-típ'                => $gan( 'mo_tip' ),
			'mục từ'                        => $the( 'muc_tu' ),
			'bài gắn mục từ'                => $gan( 'muc_tu' ),
			'trò chơi'                      => $the( 'tro_choi' ),
			'bài gắn trò chơi'              => $gan( 'tro_choi' ),
			'góp ý (gop_y)'                 => $q( "SELECT COUNT(*) FROM $p WHERE post_type = 'gop_y' AND post_status <> 'trash'" ),
			'cảm nghĩ (bình luận trên bài)' => $q( "SELECT COUNT(*) FROM {$wpdb->comments} c JOIN $p b ON b.ID = c.comment_post_ID WHERE b.post_type IN (%s, %s) AND c.comment_approved IN ('0','1')", ...$tp ),
			'bài phân tích'                 => $q( "SELECT COUNT(*) FROM $p WHERE post_type = 'phan_tich' AND post_status <> 'trash'" ),
			'lượt đọc (dòng)'               => $bang( 'cadao_luot_doc' ),
			'sự kiện (dòng)'                => $bang( 'cadao_su_kien' ),
		);
		$truoc = (array) get_option( self::TRUOC, array() );
		$ra    = array();
		foreach ( $sau as $viec => $so ) {
			$ra[] = array( $viec, isset( $truoc[ $viec ] ) ? (int) $truoc[ $viec ] : null, $so );
		}
		return $ra;
	}

	/**
	 * Đường dẫn MVP của các bài đã nhập và chỗ mới mong đợi (bài đổi tên, đổi thể loại, tình huống → tuyển tập) — để thử
	 * 301 trên site thật.
	 *
	 * @return list<array{0:string,1:string}> [đường dẫn cũ, đường dẫn mới]
	 */
	public static function duong_dan( int $so = 20 ): array {
		global $wpdb;
		if ( ! DuLieuCu::co_bang( NhapLam::bang() ) ) {
			return array();
		}
		$ra = array();
		foreach ( $wpdb->get_results( 'SELECT doi_tuong, truoc FROM ' . NhapLam::bang() . " WHERE loai = 'bai' ORDER BY id" ) ?: array() as $r ) {
			$t   = json_decode( (string) $r->truoc, true );
			$moi = (string) get_permalink( (int) $r->doi_tuong );
			if ( ! is_array( $t ) || '' === $moi || 'publish' !== get_post_status( (int) $r->doi_tuong ) ) {
				continue;
			}
			$tl = (string) ( $t['the_loai'][0] ?? 'ca-dao' );
			$cu = home_url( '/' . $tl . '/' . $t['post_name'] . '/' );
			// Bài đổi tên hoặc đổi thể loại (thành ngữ → tục ngữ) trước; bài giữ nguyên đường dẫn thì không cần thử
			if ( untrailingslashit( $cu ) !== untrailingslashit( $moi ) ) {
				$ra[] = array( $cu, $moi );
			}
		}
		foreach ( get_posts( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'publish', 'numberposts' => 5 ) ) as $tt ) {
			$ra[] = array( home_url( '/tinh-huong/' . $tt->post_name . '/' ), (string) get_permalink( $tt ) );
		}
		return array_slice( $ra, 0, $so );
	}
}
