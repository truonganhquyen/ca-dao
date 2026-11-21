<?php

namespace Phongdao\DichVu\TimMeili;

use Phongdao\MienDuLieu\SoKhopLoi;

defined( 'ABSPATH' ) || exit;

/** Engine chỉ lấy ứng viên. Mọi lời và quyền hiển thị đều đọc lại từ WordPress trước khi trả kết quả. */
final class Tim {
	public static function tim( string $q, bool $cong_khai = true, string $loai = '' ): array {
		if ( ! $cong_khai && ! current_user_can( 'cadao_soan' ) ) { throw new \RuntimeException( 'Không có quyền tìm trong kho riêng.' ); }
		if ( ! ChiMuc::san_sang( $cong_khai ) ) { throw new \RuntimeException( 'Chỉ mục đang cập nhật; chưa kiểm tra được đầy đủ.' ); }
		$q = mb_substr( $q, 0, 20000 ); $nhanh = SoKhopLoi::truy_van( $q );
		if ( ! $nhanh ) { return array(); }
		$tt = get_option( ChiMuc::TT );
		$index = $tt[ $cong_khai ? 'public' : 'staff' ];
		foreach ( $nhanh as &$n ) {
			$n += array( 'indexUid' => $index, 'limit' => 30, 'attributesToRetrieve' => array( 'muc', 'phan', 'ban' ) );
			if ( isset( \Phongdao\MienDuLieu\TheLoai::DS[ $loai ] ) ) { $n['filter'] = ( isset( $n['filter'] ) ? '(' . $n['filter'] . ') AND ' : '' ) . 'the_loai = "' . $loai . '"'; }
		}
		unset( $n );
		$r = MayTim::goi( 'POST', '/multi-search', array( 'queries' => $nhanh ) );
		if ( ! isset( $r['results'] ) || count( $r['results'] ) !== count( $nhanh ) ) { throw new \RuntimeException( 'Chưa nhận đủ kết quả kiểm tra.' ); }
		$ra = array(); $docs = array(); $da_xet = array();
		foreach ( $r['results'] as $nhom ) {
			if ( ! isset( $nhom['hits'] ) || ! is_array( $nhom['hits'] ) ) { throw new \RuntimeException( 'Kết quả máy tìm không hợp lệ.' ); }
			foreach ( $nhom['hits'] as $hit ) {
				$muc = $hit['muc'] ?? ''; $phan = $hit['phan'] ?? -1;
				if ( ! is_string( $muc ) || ! preg_match( '/^(bai|kho|gop):[1-9][0-9]*$/D', $muc ) || ! is_int( $phan ) || $phan < 0 || isset( $da_xet[ $muc . '/' . $phan ] ) ) { continue; }
				$da_xet[ $muc . '/' . $phan ] = true;
				$docs[ $muc ] ??= ChiMuc::doc( $muc, $cong_khai );
				foreach ( $docs[ $muc ] as $d ) {
					if ( $d['phan'] !== $phan ) { continue; }
					if ( ! hash_equals( $d['ban'], (string) ( $hit['ban'] ?? '' ) ) ) {
						ChiMuc::doi( $muc );
						throw new \RuntimeException( 'Chỉ mục cần cập nhật lại; chưa kiểm tra được đầy đủ.' );
					}
					$so = SoKhopLoi::so( $q, implode( "\n", $d['dong'] ) );
					if ( ! $so ) { continue; }
					$d = array_merge( $d, $so );
					if ( ! isset( $ra[ $muc ] ) || array( $d['khop'], $d['diem'] ) > array( $ra[ $muc ]['khop'], $ra[ $muc ]['diem'] ) ) { $ra[ $muc ] = $d; }
				}
			}
		}
		usort( $ra, static fn( $a, $b ) => array( ! $a['khop'], -$a['diem'], $a['muc'] ) <=> array( ! $b['khop'], -$b['diem'], $b['muc'] ) );
		return array_slice( $ra, 0, 10 );
	}
}
