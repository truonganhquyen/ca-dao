<?php

namespace Phongdao\DichVu\DungTrang;

/**
 * Dữ liệu mẫu tổng hợp cho trang người đọc trong lúc chưa có mô hình dữ liệu: cùng chữ mẫu với bộ thiết kế
 * (design/v2/source/data.mjs) và kiểm thử MVP — không có lời, chú thích thật của nguồn (repo công khai, D-050).
 * Khi có dữ liệu thật, phần dựng trang đọc từ bài thay cho lớp này; hình dạng mảng giữ nguyên.
 */
final class DuLieuMau {

	private const BAI = array(
		'ca-dao'  => array(
			'ma'           => 'mau-ca-dao',
			'the_loai'     => 'ca-dao',
			'ten_the_loai' => 'Ca dao',
			'hinh_thuc'    => 'Lục bát',
			'kieu'         => 'lucbat',
			'tieu_de'      => 'Dòng mẫu một của bài',
			'loi'          => array( 'Dòng mẫu một của bài này', 'Dòng mẫu hai dài hơn chút cho vừa', 'Dòng mẫu ba của bài này', 'Dòng mẫu bốn dài hơn chút cho xong' ),
			'doi_chieu'    => null,
			'y_nghia'      => array( 'chu' => 'Một câu giải nghĩa tổng hợp để xem khối Ý nghĩa.', 'ai' => true ),
			'chu_thich'    => array(
				array( 'tu' => 'chút', 'giai' => 'Chú thích tổng hợp để xem giao diện (từ có trong lời: chạm để xem).' ),
				array( 'tu' => '', 'giai' => 'Chú thích không ghi từ.' ),
			),
			'di_ban'       => array(
				array( 'loi' => array( 'Dòng mẫu khác của bài này', 'Dòng mẫu hai khác dài hơn cho vừa' ), 'khac' => array( 'khác' ), 'noi' => '', 'nguoi_ke' => '' ),
			),
			'chu_de'       => array( 'Lao động' ),
			'vung'         => array(),
			'tuyen_tap'    => array( 'Lao động' ),
			'so_lieu'      => array( 'doc' => 1234, 'chia' => 38, 'trich' => 12 ),
		),
		'tuc-ngu' => array(
			'ma'           => 'mau-tuc-ngu',
			'the_loai'     => 'tuc-ngu',
			'ten_the_loai' => 'Tục ngữ – Thành ngữ',
			'hinh_thuc'    => '',
			'kieu'         => 'proverb',
			'tieu_de'      => 'Tục ngữ mẫu ngắn',
			'loi'          => array( 'Tục ngữ mẫu ngắn một dòng' ),
			'doi_chieu'    => null,
			'y_nghia'      => array( 'chu' => 'Một câu giải nghĩa tổng hợp để xem khối Ý nghĩa.', 'ai' => false ),
			'nghia_den'    => 'Nội dung thử để kiểm thứ bậc các lớp nghĩa.',
			'nghia_bong'   => 'Nội dung thử, không phải diễn giải một câu dân gian thật.',
			'dung_khi'     => array( 'Ví dụ mẫu thứ nhất do người biên soạn duyệt.', 'Ví dụ mẫu thứ hai; mẫu này chỉ kiểm bố cục.' ),
			'chu_thich'    => array(),
			'di_ban'       => array(),
			'chu_de'       => array( 'Lao động' ),
			'vung'         => array(),
			'tuyen_tap'    => array(),
			'so_lieu'      => array( 'doc' => 24 ),
		),
	);

	/** @return string[] mã các bài mẫu */
	public static function ds(): array {
		return array_keys( self::BAI );
	}

	/** @return array<string,mixed>|null */
	public static function lay( string $ma ): ?array {
		return self::BAI[ $ma ] ?? null;
	}
}
