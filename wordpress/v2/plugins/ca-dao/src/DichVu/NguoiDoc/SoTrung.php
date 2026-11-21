<?php

namespace Phongdao\DichVu\NguoiDoc;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\VanTay;

defined( 'ABSPATH' ) || exit;

/** D-123: cùng vân tay, phạm vi khách lọc ngay trong SQL; không gọi đường tự gộp. */
final class SoTrung {
	/** Tra khớp lời có dấu với bài đã nhận đóng góp; dùng trước khi đăng mục nguồn còn chờ. */
	public static function bai_co_ghi_cong( string $loi ): int {
		global $wpdb;
		$bam = VanTay::cua( $loi )['vt1']; $t = ChongTrung::bang( 'van_tay' );
		// Đọc có khoá để thấy lượt duyệt vừa COMMIT, kể cả khi caller đã mở snapshot lúc đọc Kho.
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT p.ID FROM $t t JOIN {$wpdb->posts} p ON t.muc=CONCAT('bai:',p.ID) JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_phongdao_dong_gop' WHERE t.vt1=%s AND p.post_type='tac_pham' AND p.post_status NOT IN ('trash','auto-draft') LIMIT 1 FOR UPDATE", $bam ) );
		if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa đối chiếu được đóng góp đã duyệt.' ); }
		return (int) $id;
	}

	public static function tim( string $loi, bool $cong_khai = true, int $bo_gop = 0 ): array {
		if ( \Phongdao\DichVu\TimMeili\MayTim::bat() ) {
			$ra = array();
			foreach ( \Phongdao\DichVu\TimMeili\Tim::tim( $loi, $cong_khai ) as $d ) {
				if ( 'gop:' . $bo_gop === $d['muc'] ) { continue; }
				[ $loai, $id ] = ChongTrung::tach_muc( $d['muc'] );
				$ra[] = array( 'muc' => $d['muc'], 'bai' => 'bai' === $loai ? $id : 0, 'phan' => $d['phan'], 'khop' => $d['khop'], 'diem' => $d['diem'], 'ten' => $d['ten'], 'url' => $d['url'], 'dong' => $d['dong'] );
			}
			return array_slice( $ra, 0, $cong_khai ? 2 : 6 );
		}
		global $wpdb;
		$v = VanTay::cua( $loi );
		if ( ! $v['so_am'] ) { return array(); }
		$t = ChongTrung::bang( 'van_tay' );
		$dk = $cong_khai
			? "EXISTS (SELECT 1 FROM {$wpdb->posts} p WHERE t.muc = CONCAT('bai:',p.ID) AND p.post_type = 'tac_pham' AND p.post_status = 'publish' AND p.post_password = '' AND NOT EXISTS (SELECT 1 FROM {$wpdb->postmeta} m WHERE m.post_id=p.ID AND m.meta_key='_phongdao_tho_tuc'))"
			: "(t.muc LIKE 'bai:%' OR t.muc LIKE 'kho:%' OR EXISTS (SELECT 1 FROM {$wpdb->posts} p WHERE t.muc=CONCAT('gop:',p.ID) AND p.post_type='" . KhaiBao::GOP_Y . "' AND p.post_status IN ('pending','draft')))";
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, 0 AS chung FROM $t t WHERE ($dk) AND (t.vt1=%s OR t.vt2=%s) ORDER BY t.muc LIKE 'bai:%%' DESC LIMIT 60", $v['vt1'], $v['vt2'] ) );
		if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa tra được bài gần giống.' ); }
		if ( $v['so_am'] >= VanTay::AM_TOI_THIEU && $v['cap'] ) {
			$cap = implode( ',', array_map( 'intval', $v['cap'] ) );
			$near = $wpdb->get_results( 'SELECT t.*, COUNT(*) AS chung FROM ' . ChongTrung::bang( 'cap_am' ) . " c JOIN $t t ON t.id=c.van_tay_id WHERE ($dk) AND c.cap IN ($cap) GROUP BY c.van_tay_id HAVING chung >= 2 ORDER BY chung DESC LIMIT 80" );
			if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa tra được bài gần giống.' ); }
			$rows = array_merge( $rows ?: array(), $near ?: array() );
		}
		$tot = array();
		foreach ( $rows ?: array() as $r ) {
			if ( 'gop:' . $bo_gop === $r->muc ) { continue; }
			$khop = $v['vt1'] === $r->vt1;
			$diem = $khop || $v['vt2'] === $r->vt2 ? 100 : (int) floor( (int) $r->chung * 100 / max( 1, $v['so_cap'] + (int) $r->so_cap - (int) $r->chung ) );
			if ( $diem < 30 || ( ! $khop && $v['vt2'] !== $r->vt2 && min( $v['so_am'], (int) $r->so_am ) < VanTay::AM_TOI_THIEU ) ) { continue; }
			$cu = $tot[ $r->muc ] ?? null;
			if ( $cu && array( $cu['khop'], $cu['diem'] ) >= array( $khop, $diem ) ) { continue; }
			[ $loai, $id ] = ChongTrung::tach_muc( $r->muc );
			$dong = array(); $ten = ''; $url = '';
			if ( 'bai' === $loai ) {
				$p = get_post( $id );
				if ( ! $p || KhaiBao::TAC_PHAM !== $p->post_type || in_array( $p->post_status, array( 'trash', 'auto-draft' ), true ) ) { continue; }
				if ( $cong_khai && ( 'publish' !== $p->post_status || '' !== $p->post_password || PhanBai::lay( $id, 'tho_tuc' ) ) ) { continue; }
				$part = (int) $r->phan;
				$dong = $part ? ( PhanBai::lay( $id, 'di_ban' )['muc'][ $part - 1 ]['dong'] ?? array() ) : ( PhanBai::lay( $id, 'loi' )['dong'] ?? array() );
				$ten = $p->post_title; $url = 'publish' === $p->post_status ? get_permalink( $id ) : '';
			} elseif ( ! $cong_khai && 'kho' === $loai ) {
				$m = Kho::lay( $id ); if ( ! $m || 'moi' !== $m->trang_thai ) { continue; }
				$loi_kho = (int) $r->phan > 0 ? (string) ( Kho::du_lieu( $m )['di_ban'][ (int) $r->phan - 1 ] ?? '' ) : $m->loi;
				$dong = explode( "\n", $loi_kho ); $ten = 'Kho #' . $id;
			} elseif ( ! $cong_khai && 'gop' === $loai ) {
				$p = get_post( $id ); if ( ! $p || ! in_array( $p->post_status, array( 'pending', 'draft' ), true ) ) { continue; }
				$dong = explode( "\n", DuyetTacPham::loi( $id ) ); $ten = 'Đóng góp đang chờ #' . $id;
			} else { continue; }
			// Kiểm lại chữ: chỉ mục cũ không được làm mất bài mới hoặc gộp nhầm.
			$actual = VanTay::cua( implode( "\n", $dong ) );
			if ( $actual['vt1'] !== $r->vt1 ) { continue; }
			$tot[ $r->muc ] = array( 'muc' => (string) $r->muc, 'bai' => 'bai' === $loai ? $id : 0, 'phan' => (int) $r->phan, 'khop' => $khop, 'diem' => $diem, 'ten' => $ten, 'url' => $url, 'dong' => $dong );
		}
		usort( $tot, static fn( $a, $b ) => array( ! $a['khop'], -$a['diem'], $a['muc'] ) <=> array( ! $b['khop'], -$b['diem'], $b['muc'] ) );
		return array_slice( $tot, 0, $cong_khai ? 2 : 6 );
	}

	public static function cong_khai( string $loi ): array {
		return array_map( static fn( $r ) => array( 'ten' => $r['ten'], 'url' => $r['url'], 'khop' => $r['khop'] ), self::tim( $loi ) );
	}
}
