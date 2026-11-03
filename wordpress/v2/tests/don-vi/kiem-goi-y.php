<?php
/**
 * Gợi ý vùng, chủ đề (MienDuLieu\GoiY, chuyển quy tắc MVP goi-y.php): mỗi gợi ý kèm lý do; bỏ cái bài đã có hoặc người
 * đã quyết. Dữ liệu tổng hợp.
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\GoiY;

$vao = static fn( array $them ): array => $them + array( 'tieu_de' => 'Bài mẫu', 'loi' => array( 'Dòng mẫu một', 'Dòng mẫu hai' ), 'the_loai' => 'ca-dao', 'chu_thich' => array(), 'chu_de_goc' => array(), 'anh_xa' => GoiY::ANH_XA_DE_XUAT, 'da_co' => array(), 'da_quyet' => array() );
$ma  = static fn( array $ds ): array => array_map( static fn( $g ) => $g['loai'] . ':' . $g['ma'], $ds );

$g = GoiY::cho( $vao( array( 'chu_thich' => array( array( 'tu' => 'bến mẫu', 'loi_giai' => 'Một bến sông ở Cần Thơ.' ), array( 'tu' => 'làng mẫu', 'loi_giai' => 'Địa danh mẫu.', 'toa_do' => array( 21.0, 105.8 ) ) ) ) ) );
kt( 'vùng: từ lời giải của chú thích (tên tỉnh) và từ toạ độ địa danh đã xác nhận, kèm lý do', array( 'vung:nam-bo', 'vung:bac-bo' ) === $ma( $g ) && str_contains( $g[0]['ly_do'], 'Cần Thơ' ) && str_contains( $g[1]['ly_do'], 'đã xác nhận' ), $g );
kt( 'vùng theo toạ độ: Bắc Bộ, Bắc Trung Bộ, Tây Nguyên, Nam Trung Bộ, Nam Bộ', 'bac-bo' === GoiY::vung_theo_toa_do( 21.0, 105.8 ) && 'bac-trung-bo' === GoiY::vung_theo_toa_do( 18.7, 105.7 ) && 'tay-nguyen' === GoiY::vung_theo_toa_do( 12.7, 108.0 ) && 'nam-trung-bo' === GoiY::vung_theo_toa_do( 12.2, 109.2 ) && 'nam-bo' === GoiY::vung_theo_toa_do( 10.0, 105.8 ) );
$g = GoiY::cho( $vao( array( 'chu_de_goc' => array( 'Tình yêu đôi lứa', 'Khác' ), 'loi' => array( 'À ơi con ngủ cho ngoan', 'Dòng mẫu hai' ) ) ) );
kt( 'chủ đề: chủ đề nguồn qua bảng ánh xạ (mục ánh xạ trống bỏ qua); lời ru → Hát ru', array( 'chu_de:tinh-yeu-hon-nhan', 'chu_de:hat-ru' ) === $ma( $g ), $g );
$g = GoiY::cho( $vao( array( 'chu_de_goc' => array( 'Tình yêu đôi lứa' ), 'anh_xa' => array( 'Tình yêu đôi lứa' => 'gia-dinh' ) ) ) );
kt( 'bảng ánh xạ người sáng lập đã lưu thay bảng đề xuất', array( 'chu_de:gia-dinh' ) === $ma( $g ) );
kt( 'lối hát: dòng đầu có "hò" → Hò; tên bài bắt đầu "Lý" → Lý', array( 'chu_de:ho' ) === $ma( GoiY::cho( $vao( array( 'loi' => array( 'Hò ơi dòng mẫu', 'Dòng hai' ) ) ) ) ) && array( 'chu_de:ly' ) === $ma( GoiY::cho( $vao( array( 'tieu_de' => 'Lý mẫu' ) ) ) ) );
$tn = $vao( array( 'loi' => array( 'Mưa mẫu thì cấy lúa' ) ) );
kt( 'kinh nghiệm theo từ khoá chỉ với tục ngữ – thành ngữ', array( 'chu_de:kn-thoi-tiet', 'chu_de:kn-mua-vu' ) === $ma( GoiY::cho( array( 'the_loai' => 'tuc-ngu' ) + $tn ) ) && array() === GoiY::cho( $tn ) );
kt( 'từ khoá chặn chữ cái hai bên ("cá" không khớp trong "các")', array() === GoiY::cho( $vao( array( 'the_loai' => 'tuc-ngu', 'loi' => array( 'Các dòng mẫu' ) ) ) ) );
$g = GoiY::cho( $vao( array( 'chu_de_goc' => array( 'Tình yêu đôi lứa', 'Quê hương đất nước' ), 'loi' => array( 'À ơi mẫu' ), 'da_co' => array( 'chu_de:tinh-yeu-hon-nhan' ), 'da_quyet' => array( 'chu_de:hat-ru' ) ) ) );
kt( 'bỏ gợi ý bài đã có hoặc người đã quyết (đồng ý / bỏ)', array( 'chu_de:que-huong-dat-nuoc' ) === $ma( $g ), $g );
kt( 'tên chủ đề có nhóm: "Lối hát → Hò"', 'Lối hát → Hò' === GoiY::ten_chu_de( 'ho' ) && 'Gia đình' === GoiY::ten_chu_de( 'gia-dinh' ) );
