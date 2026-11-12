<?php

namespace Phongdao\MienDuLieu;

/**
 * Danh mục các phần của bài (D-094, docs/26 mục 4.3). Mỗi phần là một khoá post meta TIEN_TO . <phần>.
 *
 * Mỗi định nghĩa:
 * - dang: 'mot' (một giá trị) hoặc 'ds' (danh sách mục, lưu dạng ['muc' => [...]]);
 * - the_loai: null = mọi thể loại, hoặc danh sách mã thể loại được dùng phần này;
 * - nguoi_lam: người làm được phép; null = phần không ghi người làm và nguồn (người viết, hoặc máy với nguon_may);
 * - may: máy (nguồn tự động, lệnh) được ghi phần này hay không — máy chỉ chép, không bao giờ điền nguồn;
 * - truong: tên trường => kiểu (KiemPhan::truong); dấu ! ở cuối = bắt buộc.
 */
final class Phan {

	public const TIEN_TO = '_phongdao_';

	public const MOT = 'mot';
	public const DS  = 'ds';

	private const NGUOI = array( NguoiLam::NGUOI_VIET );
	private const NGHIA = array( NguoiLam::NGUOI_VIET, NguoiLam::AI_DA_DUYET );

	private const DINH_NGHIA = array(
		'tac_pham'  => array(
			'loi'         => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => array( NguoiLam::MAY_CHEP, NguoiLam::NGUOI_VIET ), 'may' => true, 'truong' => array( 'dong' => 'dong!', 'doi_chieu' => 'doi_chieu' ) ),
			'di_ban'      => array( 'dang' => self::DS, 'the_loai' => null, 'nguoi_lam' => array( NguoiLam::MAY_CHEP, NguoiLam::NGUOI_VIET, NguoiLam::DOC_GIA_GOP ), 'may' => true, 'truong' => array( 'dong' => 'dong!', 'noi' => 'chu_ngan', 'nguoi_ke' => 'chu_ngan', 'nam' => 'chu_ngan' ) ),
			'y_nghia'     => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => self::NGHIA, 'may' => false, 'truong' => array( 'chu' => 'chu!', 'ai' => 'bool' ) ),
			'nghia_den'   => array( 'dang' => self::MOT, 'the_loai' => array( 'tuc-ngu' ), 'nguoi_lam' => array( NguoiLam::NGUOI_VIET, NguoiLam::AI_DA_DUYET, NguoiLam::MAY_CHEP ), 'may' => true, 'truong' => array( 'chu' => 'chu!', 'ai' => 'bool' ) ),
			'nghia_bong'  => array( 'dang' => self::MOT, 'the_loai' => array( 'tuc-ngu' ), 'nguoi_lam' => array( NguoiLam::NGUOI_VIET, NguoiLam::AI_DA_DUYET, NguoiLam::MAY_CHEP ), 'may' => true, 'truong' => array( 'chu' => 'chu!', 'ai' => 'bool' ) ),
			'dung_khi'    => array( 'dang' => self::MOT, 'the_loai' => array( 'tuc-ngu' ), 'nguoi_lam' => array( NguoiLam::NGUOI_VIET, NguoiLam::AI_DA_DUYET, NguoiLam::MAY_CHEP ), 'may' => true, 'truong' => array( 'chu' => 'chu!', 'ai' => 'bool' ) ),
			// Ca dao, đồng dao (D-105): mỗi dòng một cặp "hình ảnh → điều được nói tới"; người viết, có AI hỗ trợ thì phải được duyệt
			'hinh_anh'    => array( 'dang' => self::MOT, 'the_loai' => array( 'ca-dao', 'dong-dao' ), 'nguoi_lam' => self::NGHIA, 'may' => false, 'truong' => array( 'chu' => 'chu!', 'ai' => 'bool' ) ),
			'gan_nghia'   => array( 'dang' => self::DS, 'the_loai' => array( 'tuc-ngu' ), 'nguoi_lam' => null, 'may' => false, 'truong' => array( 'tac_pham_id' => 'id!', 'quan_he' => 'chon:gan|trai!' ) ),
			'giai_sach'   => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => array( NguoiLam::MAY_CHEP ), 'may' => true, 'truong' => array( 'chu' => 'chu!' ) ),
			'chu_thich'   => array( 'dang' => self::DS, 'the_loai' => null, 'nguoi_lam' => array( NguoiLam::MAY_CHEP, NguoiLam::NGUOI_VIET ), 'may' => true, 'truong' => array( 'tu' => 'chu_ngan', 'loi_giai' => 'chu!', 'muc_tu_id' => 'id', 'vi_tri' => 'ds_so' ) ),
			'cau_hoi'     => array( 'dang' => self::MOT, 'the_loai' => array( 'cau-do' ), 'nguoi_lam' => array( NguoiLam::MAY_CHEP, NguoiLam::NGUOI_VIET ), 'may' => true, 'truong' => array( 'chu' => 'chu_ngan!' ) ),
			'dap_an'      => array( 'dang' => self::MOT, 'the_loai' => array( 'cau-do' ), 'nguoi_lam' => array( NguoiLam::MAY_CHEP, NguoiLam::NGUOI_VIET ), 'may' => true, 'truong' => array( 'chu' => 'chu!' ) ),
			'goi_y'       => array( 'dang' => self::MOT, 'the_loai' => array( 'cau-do' ), 'nguoi_lam' => self::NGHIA, 'may' => false, 'truong' => array( 'chu' => 'chu!', 'ai' => 'bool' ) ),
			'doan'        => array( 'dang' => self::DS, 'the_loai' => array( 've' ), 'nguoi_lam' => null, 'may' => false, 'truong' => array( 'tieu_de' => 'chu_ngan', 'tu_dong' => 'so!', 'den_dong' => 'so!' ) ),
			'boi_canh'    => array( 'dang' => self::MOT, 'the_loai' => array( 've' ), 'nguoi_lam' => self::NGUOI, 'may' => false, 'truong' => array( 'chu' => 'chu', 'su_kien' => 'chu', 'nam' => 'chu_ngan', 'noi' => 'chu_ngan', 'nhan_vat' => 'ds_id' ) ),
			'media'       => array( 'dang' => self::DS, 'the_loai' => null, 'nguoi_lam' => null, 'may' => false, 'truong' => array( 'attachment_id' => 'id!', 'loai' => 'chon:anh|giong_may|giong_nguoi!' ) ),
			'ban_co_biet' => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => self::NGHIA, 'may' => false, 'truong' => array( 'chu' => 'chu!', 'ai' => 'bool' ) ),
			'co_nghi'     => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => self::NGUOI, 'may' => false, 'truong' => array( 'ly_do' => 'chu!' ) ),
			'sgk'         => array( 'dang' => self::DS, 'the_loai' => null, 'nguoi_lam' => self::NGUOI, 'may' => false, 'truong' => array( 'bo_sach' => 'chu_ngan!', 'lop' => 'so!', 'bai' => 'chu_ngan!', 'trang' => 'chu_ngan' ) ),
			// Nội bộ: trang đã lấy bài; không bao giờ có trong HTML gửi người đọc (D-081)
			'nguon_may'   => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => null, 'may' => true, 'truong' => array( 'nguon_id' => 'chu_ngan!', 'url_goc' => 'url', 'ma_goc' => 'chu_ngan', 'ngay_lay' => 'ngay' ) ),
		),
		'tuyen_tap' => array(
			'loi_dan'   => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => self::NGUOI, 'may' => false, 'truong' => array( 'chu' => 'chu_dai!' ) ),
			'thong_tin' => array( 'dang' => self::MOT, 'the_loai' => null, 'nguoi_lam' => null, 'may' => false, 'truong' => array( 'loai' => 'chon:chu_de|dip|tinh_huong|bai_hoc!', 'doi_tuong' => 'chu_ngan' ) ),
			'muc'       => array( 'dang' => self::DS, 'the_loai' => null, 'nguoi_lam' => null, 'may' => false, 'truong' => array( 'tac_pham_id' => 'id!', 'nhom' => 'chu_ngan', 'ghi_chu' => 'chu' ) ),
		),
	);

	/** Các phần lời và dị bản: không bao giờ nhận người làm là AI (docs/28 mục 6). */
	public const KHONG_AI = array( 'loi', 'di_ban' );

	/** @return string[] tên các phần của một loại bài */
	public static function ds( string $loai_bai ): array {
		return array_keys( self::DINH_NGHIA[ $loai_bai ] ?? array() );
	}

	/** @return string[] các loại bài có phần */
	public static function loai_bai(): array {
		return array_keys( self::DINH_NGHIA );
	}

	public static function co( string $loai_bai, string $phan ): bool {
		return isset( self::DINH_NGHIA[ $loai_bai ][ $phan ] );
	}

	/** @return array{dang:string,the_loai:?array,nguoi_lam:?array,may:bool,truong:array<string,string>}|null */
	public static function lay( string $loai_bai, string $phan ): ?array {
		return self::DINH_NGHIA[ $loai_bai ][ $phan ] ?? null;
	}

	public static function khoa( string $phan ): string {
		return self::TIEN_TO . $phan;
	}
}
