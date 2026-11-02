<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\ToChoKhac;

/**
 * Dữ liệu trang tác phẩm (cùng hình dạng với DuLieuMau, GiaoTiep\Khoi\TacPham vẽ) dựng từ các phần đã lưu (D-094).
 * Không gọi WordPress: phần lấy dữ liệu ở DocBai. Lằn ranh hiển thị (docs/28 mục 6, D-068, D-081):
 * - địa chỉ gốc máy đã lấy (nguon_may) không bao giờ vào đây, nên không thể ra HTML gửi người đọc;
 * - nguồn của chú thích máy chép ẩn (chính sách mặc định); nguồn người biên soạn điền thì hiện;
 * - "Đã đối chiếu" chỉ khi người biên soạn điền đối chiếu và hồ sơ sách còn.
 */
final class TrangTacPham {

	/**
	 * @param array<string,?array> $phan các phần của bài (PhanBai::tat_ca)
	 * @param array{id:int,tieu_de:string,url:string,the_loai:?string,the_tho?:string,hinh_thuc?:string,chu_de?:array,vung?:array,tuyen_tap?:array,ho_so?:array<int,string>,so_lieu?:array} $bai
	 * @return array<string,mixed>|null null khi bài chưa có lời
	 */
	public static function tu_phan( array $phan, array $bai ): ?array {
		$dong = array_values( array_filter( $phan['loi']['dong'] ?? array(), static fn( $d ): bool => '' !== $d ) );
		if ( ! $dong ) {
			return null;
		}
		$the_loai = TheLoai::co( $bai['the_loai'] ?? null ) ? $bai['the_loai'] : null;
		$ho_so    = $bai['ho_so'] ?? array();

		$doi_chieu = null;
		$dc        = $phan['loi']['doi_chieu'] ?? null;
		if ( $dc && ! empty( $ho_so[ $dc['ho_so'] ?? 0 ] ) ) {
			$doi_chieu = array( 'sach' => $ho_so[ $dc['ho_so'] ], 'trang' => (string) ( $dc['trang'] ?? '' ) );
		}

		$chu_thich = array();
		foreach ( $phan['chu_thich']['muc'] ?? array() as $c ) {
			$may         = NguoiLam::MAY_CHEP === ( $c['nguoi_lam'] ?? '' );
			$chu_thich[] = array(
				'tu'    => (string) ( $c['tu'] ?? '' ),
				'giai'  => (string) ( $c['loi_giai'] ?? '' ),
				'may'   => $may,
				'nguon' => $may ? null : self::nguon( $c['nguon'] ?? null, $ho_so ),
			);
		}

		$di_ban = array();
		foreach ( $phan['di_ban']['muc'] ?? array() as $d ) {
			$loi_db   = array_values( array_filter( $d['dong'] ?? array(), static fn( $x ): bool => '' !== $x ) );
			$di_ban[] = array(
				'loi'      => $loi_db,
				'khac'     => ToChoKhac::tu_khac( $dong, $loi_db ),
				'noi'      => (string) ( $d['noi'] ?? '' ),
				'nguoi_ke' => (string) ( $d['nguoi_ke'] ?? '' ),
				'nam'      => (string) ( $d['nam'] ?? '' ),
			);
		}

		$the_tho = (string) ( $bai['the_tho'] ?? '' );
		return array(
			'ma'           => (string) $bai['id'],
			'the_loai'     => (string) $the_loai,
			'ten_the_loai' => $the_loai ? TheLoai::DS[ $the_loai ] : '',
			'hinh_thuc'    => (string) ( $bai['hinh_thuc'] ?? '' ),
			'kieu'         => 'tuc-ngu' === $the_loai ? 'proverb' : ( 'luc-bat' === $the_tho ? 'lucbat' : '' ),
			'tieu_de'      => (string) $bai['tieu_de'],
			'loi'          => $dong,
			'doi_chieu'    => $doi_chieu,
			'y_nghia'      => empty( $phan['y_nghia']['chu'] ) ? array() : array( 'chu' => $phan['y_nghia']['chu'], 'ai' => ! empty( $phan['y_nghia']['ai'] ) ),
			'chu_thich'    => $chu_thich,
			'di_ban'       => $di_ban,
			'nghia_den'    => (string) ( $phan['nghia_den']['chu'] ?? '' ),
			'nghia_bong'   => (string) ( $phan['nghia_bong']['chu'] ?? '' ),
			'dung_khi'     => array_values( array_filter( array_map( 'trim', explode( "\n", (string) ( $phan['dung_khi']['chu'] ?? '' ) ) ) ) ),
			'dap_an'       => (string) ( $phan['dap_an']['chu'] ?? '' ),
			'goi_y'        => (string) ( $phan['goi_y']['chu'] ?? '' ),
			'cau_hoi'      => (string) ( $phan['cau_hoi']['chu'] ?? '' ),
			'chu_de'       => $bai['chu_de'] ?? array(),
			'vung'         => $bai['vung'] ?? array(),
			'tuyen_tap'    => $bai['tuyen_tap'] ?? array(),
			'so_lieu'      => $bai['so_lieu'] ?? array(),
			'url'          => (string) $bai['url'],
		);
	}

	/**
	 * Dữ liệu thẻ bài trong danh sách: hai dòng đầu, ý nghĩa (nếu có).
	 *
	 * @return array{id:int,tieu_de:string,url:string,the_loai:string,ten_the_loai:string,dong:string[],y_nghia:string,co_nghi:bool}|null
	 */
	public static function the( array $phan, array $bai ): ?array {
		$dong = array_values( array_filter( $phan['loi']['dong'] ?? array(), static fn( $d ): bool => '' !== $d ) );
		if ( ! $dong ) {
			return null;
		}
		$the_loai = TheLoai::co( $bai['the_loai'] ?? null ) ? (string) $bai['the_loai'] : '';
		$y        = (string) ( $phan['y_nghia']['chu'] ?? '' );
		return array(
			'id'           => (int) $bai['id'],
			'tieu_de'      => (string) $bai['tieu_de'],
			'url'          => (string) $bai['url'],
			'the_loai'     => $the_loai,
			'ten_the_loai' => $the_loai ? TheLoai::DS[ $the_loai ] : '',
			'dong'         => array_slice( $dong, 0, 2 ),
			'y_nghia'      => mb_strlen( $y ) > 160 ? rtrim( mb_substr( $y, 0, 159 ) ) . '…' : $y,
			'co_nghi'      => ! empty( $phan['co_nghi'] ),
		);
	}

	/** Nguồn người biên soạn điền: "Tên sách, tr. 12 · ghi chú" + đường dẫn (nếu có). Trống thì null. */
	private static function nguon( ?array $nguon, array $ho_so ): ?array {
		if ( ! $nguon ) {
			return null;
		}
		$chu = array_filter(
			array(
				$ho_so[ $nguon['ho_so'] ?? 0 ] ?? '',
				empty( $nguon['trang'] ) ? '' : 'tr. ' . $nguon['trang'],
				$nguon['ghi_chu'] ?? '',
			)
		);
		$url = (string) ( $nguon['url'] ?? '' );
		if ( ! $chu && '' === $url ) {
			return null;
		}
		return array(
			'chu' => $chu ? implode( ', ', $chu ) : $url,
			'url' => $url,
		);
	}
}
