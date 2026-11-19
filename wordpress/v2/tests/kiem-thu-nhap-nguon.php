<?php
// Được bộ WordPress kiem-thu.php gọi; chỉ dữ liệu kỹ thuật, không chứa lời từ Kho thật.
use Phongdao\DichVu\UngVien\NhapTep as Tep222;
use Phongdao\DichVu\UngVien\LanNhap as Lan222;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\DichVu\UngVien\UngVien;
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\DichVu\XuLyLo\Lo;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\KiemPhan;

$v222_0 = (int) $wpdb->get_var( 'SELECT MAX(id) FROM ' . Viec::bang() );
$nguon222_0 = NguonTuDong::tat_ca();
$hs222a = wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => '[TEST] Source fixture 222 A' ) );
$hs222b = wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => '[TEST] Source fixture 222 B' ) );
wp_set_current_user( $ai_ql );
$ct222 = static fn(): int => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Kho::bang() );
$b222 = static fn(): int => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='tac_pham'" );
$csv222 = "\xEF\xBB\xBFma_muc,loi,the_loai,trang,url\nA,\"Orbit222 maple oak\n\nLunar222 birch cedar\",ca_dao,iv,https://example.org/fixture222\nB,\"Quartz222 iron copper tin\",tuc-ngu,9,\n";
$doc222 = Tep222::doc( $csv222, 'csv' );
kt( 'Nhập CSV giữ dòng trống/ngắt khổ, nguồn/trang từng mục và thể loại riêng; BOM chỉ bỏ đầu tệp', $doc222['ok'] && 2 === count( $doc222['muc'] ) && "Orbit222 maple oak\n\nLunar222 birch cedar" === $doc222['muc'][0]['loi'] && 'iv' === $doc222['muc'][0]['trang'] && 'thanh_ngu_tuc_ngu' === $doc222['muc'][1]['loai'], $doc222 );
kt( 'Tệp lỗi UTF-8, quá dung lượng, sai số cột hoặc cột lạ đều bị chặn trước khi nhập', ! Tep222::doc( "bad\xFF", 'txt' )['ok'] && ! Tep222::doc( str_repeat( 'a', 1048577 ), 'txt' )['ok'] && ! Tep222::doc( "loi,trang\nx,1,2", 'csv' )['ok'] && ! Tep222::doc( "loi,ai\nx,1", 'csv' )['ok'] && ! Tep222::doc( implode( "\n\n", array_fill( 0, 501, 'Technical fixture' ) ), 'txt' )['ok'] );
$k222_0 = $ct222(); $p222_0 = $b222(); $cho222_0 = Viec::dem_hop_viec();
$lan222 = Lan222::tao( $doc222['muc'], $hs222a, 'fixture.csv', hash( 'sha256', $csv222 ), $ai_ql );
$lap222 = Lan222::tao( $doc222['muc'], $hs222a, 'renamed.csv', hash( 'sha256', $csv222 ), $ai_ql );
$v222 = Lan222::lay( $lan222['lan'] );
$_GET = array( 'xem_nhap' => $v222['id'] );
$html222 = $DSK::hien();
$_GET = array();
kt( 'Xem trước và so trùng không ghi Kho/bài/thẻ; đổi tên tệp mở lại cùng lần nhập', $lan222['ok'] && $lap222['da_co'] && $lan222['lan'] === $lap222['lan'] && $k222_0 === $ct222() && $p222_0 === $b222() && $cho222_0 === Viec::dem_hop_viec() && str_contains( $html222, 'name="phien"' ) && str_contains( $html222, 'name="giu[]"' ), $lan222 );
kt( 'Người không có quyền không tạo hoặc áp dụng lần nhập', ! Lan222::tao( $doc222['muc'], $hs222a, 'x.csv', 'x', 0 )['ok'] && ! Lan222::ap_dung( $v222['id'], 0, 0, array( 0 ) )['ok'] );
$ap222 = Lan222::ap_dung( $v222['id'], 0, $ai_ql, array( 0, 1 ) );
$v222 = Lan222::lay( $v222['id'] ); $ids222 = Lan222::ids( $v222['id'] );
kt( 'Áp dụng CSV: đúng tập mục, từng thể loại, chứng cứ nguồn; chưa có bài công khai', $ap222['ok'] && 0 === $ap222['con'] && 2 === count( $ids222 ) && $p222_0 === $b222() && 'thanh_ngu_tuc_ngu' === Kho::lay( $ids222[1] )->loai && 'iv' === Kho::du_lieu( Kho::lay( $ids222[0] ) )['trang'] && ! Lan222::ap_dung( $v222['id'], 0, $ai_ql, array( 0, 1 ) )['ok'], $ap222 );
$_GET = array( 'lan_nhap' => $v222['id'], 'tt' => 'tat_ca' );
$html222 = $DSK::hien();
$_GET = array();
kt( 'Bài mới lọc cố định lần nhập, có mở lại lịch sử và giữ bộ lọc trong form lô/phân trang/Đăng nhanh', str_contains( $html222, '<strong>2</strong> câu khớp' ) && str_contains( $html222, 'name="loc_lan_nhap" value="' . $v222['id'] . '"' ) && str_contains( $html222, 'Các lần nhập gần đây' ) && str_contains( $DSK::url_nhanh( array( 'lan_nhap' => $v222['id'], 'tt' => 'tat_ca' ), 2 ), 'lan_nhap=' . $v222['id'] ), substr( $html222, 0, 500 ) );
$r222 = array( 'loi' => 'Zircon222 amber birch cedar elm fir', 'loai' => 'ca_dao' );
$a222 = Tep222::nhap_muc( $r222, $hs222a, $ai_ql, 'digest-a' );
$old222 = serialize( Kho::lay( $a222['id'] ) );
$a222lap = Tep222::nhap_muc( $r222, $hs222a, $ai_ql, 'digest-renamed' );
$b222same = Tep222::nhap_muc( $r222, $hs222b, $ai_ql, 'digest-b' );
kt( 'Cùng lời hai hồ sơ giữ hai chứng cứ; nhập lại không đổi dòng Kho hoặc hồ sơ cũ', 'da_co' === $a222lap['tt'] && $a222lap['id'] === $a222['id'] && $b222same['id'] !== $a222['id'] && $old222 === serialize( Kho::lay( $a222['id'] ) ) && $hs222b === Kho::du_lieu( Kho::lay( $b222same['id'] ) )['ho_so'], array( $a222, $a222lap, $b222same ) );
$doi222 = Tep222::nhap_muc( array_replace( $r222, array( 'loai' => 've' ) ), $hs222a, $ai_ql, 'digest-a' );
$r222ma = array( 'ma_muc' => 'stable-222', 'loi' => 'Lantern222 violet willow xylem yellow', 'loai' => 'ca_dao' );
$ma222 = Tep222::nhap_muc( $r222ma, $hs222a, $ai_ql, 'digest-a' );
$ma222doi = Tep222::nhap_muc( array_replace( $r222ma, array( 'loi' => 'Lantern222 changed content' ) ), $hs222a, $ai_ql, 'digest-a' );
kt( 'Cùng nhận diện mục đổi thể loại/lời báo xung đột, không cập nhật đè', 'xung_dot' === $doi222['tt'] && 'xung_dot' === $ma222doi['tt'] && $r222ma['loi'] === Kho::lay( $ma222['id'] )->loi && 'ca_dao' === Kho::lay( $a222['id'] )->loai );
$pun222 = Tep222::nhap_muc( array_replace( $r222, array( 'loi' => $r222['loi'] . '!' ) ), $hs222a, $ai_ql, 'digest-a' );
kt( 'Lời khác dấu câu vẫn giữ bản đã nhận; vân tay không ghi đè văn bản', $pun222['id'] !== $a222['id'] && $r222['loi'] . '!' === Kho::lay( $pun222['id'] )->loi && $r222['loi'] === Kho::lay( $a222['id'] )->loi );
kt( 'Lời máy trích cần URL và giữ đúng người làm, không dùng đuôi tệp để đổi nguồn gốc', 'loi' === Tep222::nhap_muc( array( 'loi' => 'Machine222 cobalt nickel', 'loai' => 'ca_dao' ), $hs222a, $ai_ql, 'm', NguoiLam::MAY_CHEP )['tt'] && 'moi' === ( $may222 = Tep222::nhap_muc( array( 'loi' => 'Machine222 cobalt nickel', 'loai' => 'ca_dao', 'url' => 'https://example.org/fixture222-machine' ), $hs222a, $ai_ql, 'm', NguoiLam::MAY_CHEP ) )['tt'] && NguoiLam::MAY_CHEP === UngVien::phan_tu_kho( Kho::lay( $may222['id'] ) )['loi']['nguoi_lam'] );

// Ghi lỗi từng mục và ghi lỗi biên nhận: kiểm rollback thật bằng bộ lọc truy vấn.
$loi222 = static fn( $sql ) => preg_match( '/^INSERT\s+INTO\s+`?' . preg_quote( Kho::bang(), '/' ) . '`?/i', $sql ) && str_contains( $sql, 'Fail222' ) ? 'TEST_NHAP_222_INSERT_ERROR' : $sql;
$docfail222 = Tep222::doc( "Works222 opal jade ruby\n\nFail222 gold silver platinum", 'txt', 'ca_dao' );
$lf222 = Lan222::tao( $docfail222['muc'], $hs222a, 'failure.txt', 'failure-222', $ai_ql );
$old_errors222 = $wpdb->suppress_errors( true );
add_filter( 'query', $loi222 );
$af222 = Lan222::ap_dung( $lf222['lan'], 0, $ai_ql, array( 0, 1 ) );
remove_filter( 'query', $loi222 );
$wpdb->suppress_errors( $old_errors222 );
$vf222 = Lan222::lay( $lf222['lan'] );
kt( 'Lỗi ghi một mục được báo thật; mục lỗi rollback, mục thành công có biên nhận cùng giao dịch', $af222['ok'] && 1 === Lan222::dem( $vf222 )['moi'] && 1 === Lan222::dem( $vf222 )['loi'] && 1 === count( Lan222::ids( $vf222['id'] ) ), $vf222['sau'] );
$retry222 = Lan222::thu_lai( $vf222['id'], $vf222['phien'], $ai_ql );
$rv222 = Lan222::lay( $retry222['lan'] );
$before_retry222 = $ct222();
$ar222 = Lan222::ap_dung( $rv222['id'], 0, $ai_ql, array( 0 ) );
kt( 'Thử lại nhập chỉ lấy mục lỗi qua xem trước con; giữ biên nhận gốc và không chạy mục đã thành công', $retry222['ok'] && 1 === count( $rv222['noi_dung']['muc'] ) && $rv222['noi_dung']['cha'] === $vf222['id'] && $ar222['ok'] && $before_retry222 + 1 === $ct222() && $vf222['sau'] === Lan222::lay( $vf222['id'] )['sau'], $retry222 );
$atomic222 = Lan222::tao( array( array( 'loi' => 'Atomic222 coral kelp reef', 'loai' => 'ca_dao' ) ), $hs222a, 'atomic.txt', 'atomic-222', $ai_ql );
$before_atomic222 = $ct222();
$chan_receipt222 = static fn( $sql ) => preg_match( '/^UPDATE\s+`?' . preg_quote( Viec::bang(), '/' ) . '`?/i', $sql ) && str_contains( $sql, 'nhap_xong' ) ? 'TEST_NHAP_222_RECEIPT_ERROR' : $sql;
$old_errors222 = $wpdb->suppress_errors( true ); add_filter( 'query', $chan_receipt222 );
$atomic_fail222 = Lan222::ap_dung( $atomic222['lan'], 0, $ai_ql, array( 0 ) );
remove_filter( 'query', $chan_receipt222 ); $wpdb->suppress_errors( $old_errors222 );
kt( 'Lỗi chốt biên nhận huỷ cả dữ liệu trong lượt, giữ nguyên phiên để tiếp tục an toàn', ! $atomic_fail222['ok'] && $before_atomic222 === $ct222() && 0 === Lan222::lay( $atomic222['lan'] )['phien'], $atomic_fail222 );
kt( 'Tiếp tục sau lỗi biên nhận nhập đúng một mục', Lan222::ap_dung( $atomic222['lan'], 0, $ai_ql, array( 0 ) )['ok'] && $before_atomic222 + 1 === $ct222() );

// Câu cũ có thẻ vẫn đăng theo lô được; snapshot phát hiện cả thay lời/nguồn chứ không chỉ trạng thái.
$t222 = UngVien::dua_vao_hop_viec( $ids222[0], $ai_ql );
$lo222 = Lo::tao( 'kho_dang', array( $ids222[0] ), 'chon', array( 'lan_nhap' => (string) $v222['id'] ), $ai_ql );
$publish222 = Lo::ap_dung( $lo222['lo'], $ai_ql, true );
$p222id = (int) Kho::lay( $ids222[0] )->post_id;
$saved222 = PhanBai::lay( $p222id, 'loi' );
kt( 'Đăng lô Kho dùng lại thẻ đang mở, giữ ngắt khổ/trang/URL CSV và đóng đúng thẻ', $publish222['ok'] && 1 === $publish222['dem']['thanh_cong'] && Viec::XONG === Viec::lay( $t222['viec'] )['trang_thai'] && "Orbit222 maple oak\n\nLunar222 birch cedar" === implode( "\n", $saved222['dong'] ) && 'iv' === $saved222['nguon']['trang'] && 'https://example.org/fixture222' === $saved222['nguon']['url'], $publish222 );
$stale222 = Lo::tao( 'kho_dang', array( $ids222[1] ), 'chon', array(), $ai_ql );
$old_loi222 = Kho::lay( $ids222[1] )->loi;
$wpdb->update( Kho::bang(), array( 'loi' => $old_loi222 . ' changed' ), array( 'id' => $ids222[1] ) );
$stale_ap222 = Lo::ap_dung( $stale222['lo'], $ai_ql, true );
kt( 'Lời/nguồn đổi sau xem trước lô báo xung đột dù trạng thái vẫn mới', $stale_ap222['ok'] && 1 === $stale_ap222['dem']['xung_dot'] && 0 === (int) Kho::lay( $ids222[1] )->post_id, $stale_ap222 );
$wpdb->update( Kho::bang(), array( 'loi' => $old_loi222 ), array( 'id' => $ids222[1] ) );
$lofail222 = Lo::tao( 'kho_dang', array( $ids222[1] ), 'chon', array( 'lan_nhap' => (string) $v222['id'] ), $ai_ql );
$chanloi222 = static fn( $v, $pid, $key ) => \Phongdao\MienDuLieu\Phan::khoa( 'loi' ) === $key ? false : $v;
add_filter( 'update_post_metadata', $chanloi222, 10, 3 );
$lfap222 = Lo::ap_dung( $lofail222['lo'], $ai_ql, true );
remove_filter( 'update_post_metadata', $chanloi222, 10 );
$lfv222 = Viec::lay( $lofail222['lo'] );
$msg222 = \Phongdao\GiaoTiep\QuanTri\TrangLo::ket_qua_ngan( $lfv222 );
$lr222 = Lo::thu_lai( $lfv222['id'], $lfv222['phien'], $ai_ql );
$lrap222 = Lo::ap_dung( $lr222['lo'], $ai_ql, true );
kt( 'Lô xử lý hết vẫn báo đúng Đã đăng 0/1 và lỗi; thử lại lỗi giữ phạm vi lần nhập, thành công một bài', 1 === $lfap222['dem']['loi'] && str_contains( $msg222, 'Đã đăng 0/1 bài.' ) && str_contains( $msg222, 'Lỗi 1' ) && $lr222['ok'] && 1 === $lrap222['dem']['thanh_cong'] && (string) $v222['id'] === Viec::lay( $lr222['lo'] )['noi_dung']['loc']['lan_nhap'] && $lfv222['sau'] === Viec::lay( $lfv222['id'] )['sau'], array( $msg222, $lr222, $lrap222 ) );

// Nạp lời khớp chỉ ghi chứng cứ; một lần đồng bộ máy về sau không đưa chứng cứ chưa duyệt vào bài.
NguonTuDong::luu( array( 'id' => 'fixture222-source', 'ten' => 'Fixture source', 'ho_so' => $hs222a, 'chep_chu_thich' => true ) + NguonTuDong::mac_dinh() );
Kho::luu( 'fixture222-source', array( 'ma_goc' => 'root', 'loai' => 'ca_dao', 'loi' => "Primary222 acacia cedar oak elm\nSecondary222 bamboo cypress willow", 'doc_trang' => '2026-11-19', 'chu_thich' => array( array( 'tu' => 'oak', 'noi_dung' => 'Technical explanation fixture.' ) ) ) );
$root222 = (int) $wpdb->get_var( "SELECT id FROM " . Kho::bang() . " WHERE nguon_id='fixture222-source' AND ma_goc='root'" );
$rt222 = UngVien::dua_vao_hop_viec( $root222, $ai_ql );
QuyetViec::quyet( $rt222['viec'], 'dang', $ai_ql, true, 0 );
$pub222 = (int) Kho::lay( $root222 )->post_id;
$term222 = wp_insert_term( 'Fixture222 oak term', 'muc_tu' );
$ct_saved222 = PhanBai::lay( $pub222, 'chu_thich' )['muc'];
$ct_saved222[0]['muc_tu_id'] = $term222['term_id'];
LuuPhan::luu( $pub222, 'chu_thich', $ct_saved222, array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI ) );
$pub_before222 = get_post_meta( $pub222 );
$same_pub222 = Tep222::nhap_muc( array( 'loi' => Kho::lay( $root222 )->loi, 'loai' => 'ca_dao', 'trang' => '12' ), $hs222b, $ai_ql, 'pub-same' );
kt( 'Nhập lời khớp bài cũ chỉ giữ chứng cứ Kho, không sửa phần bài đã đăng', 'da_gop' === $same_pub222['tt'] && $pub_before222 === get_post_meta( $pub222 ), $same_pub222 );
UngVien::dong_bo( $pub222 );
kt( 'Đồng bộ máy sau lần nhập không lộ chứng cứ chưa quyết và không mất mục từ đã nối', $term222['term_id'] === PhanBai::lay( $pub222, 'chu_thich' )['muc'][0]['muc_tu_id'] && ! PhanBai::lay( $pub222, 'di_ban' ) );
$variant222 = Tep222::nhap_muc( array( 'loi' => "Primary222 acacia cedar oak elm\nSecondary222 bamboo cypress violet", 'loai' => 'ca_dao', 'trang' => '17', 'url' => 'https://example.org/fixture222-variant' ), $hs222b, $ai_ql, 'pub-variant' );
ChongTrung::ghi_cho( 'kho:' . $variant222['id'], 'bai:' . $pub222, 'di_ban', 90, 'Technical comparison fixture' );
$vvar222 = Viec::mo( 'kho', $variant222['id'], 'co_the_trung' );
$gvar222 = QuyetViec::quyet( $vvar222[0]['id'], 'di_ban', $ai_ql, true, $vvar222[0]['phien'] );
$variants222 = PhanBai::lay( $pub222, 'di_ban' )['muc'] ?? array();
kt( 'Người quyết gộp dị bản tệp: giữ đúng người làm/hồ sơ/trang/URL từng mục và liên kết từ khó cũ', $gvar222['ok'] && 1 === count( $variants222 ) && NguoiLam::NGUOI_VIET === $variants222[0]['nguoi_lam'] && $hs222b === $variants222[0]['nguon']['ho_so'] && '17' === $variants222[0]['nguon']['trang'] && 'https://example.org/fixture222-variant' === $variants222[0]['nguon']['url'] && $term222['term_id'] === PhanBai::lay( $pub222, 'chu_thich' )['muc'][0]['muc_tu_id'], array( $gvar222, $variants222 ) );

// Dọn đúng dữ liệu kỹ thuật của khối, không thay các bộ kiểm sau.
foreach ( array( $p222id, (int) Kho::lay( $ids222[1] )->post_id, $pub222, $hs222a, $hs222b ) as $id222 ) { if ( $id222 ) { wp_delete_post( $id222, true ); } }
wp_delete_term( $term222['term_id'], 'muc_tu' );
foreach ( $wpdb->get_col( "SELECT id FROM " . Kho::bang() . " WHERE nguon_id IN ('nhap-tay','fixture222-source')" ) as $id222 ) { ChongTrung::xoa( 'kho:' . $id222 ); }
$wpdb->query( "DELETE FROM " . Kho::bang() . " WHERE nguon_id IN ('nhap-tay','fixture222-source')" );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Viec::bang() . ' WHERE id > %d', $v222_0 ) );
update_option( NguonTuDong::TUY_CHON, $nguon222_0, false );
$_GET = array();
