<?php
// Chỉ chép vào thư mục TẠM của Playground ở máy; không đưa vào plugin hoặc máy chủ.
if ( ! str_starts_with( $_SERVER['HTTP_HOST'] ?? '', '127.0.0.1:' ) ) { http_response_code(403); exit; }
require __DIR__ . '/wp-load.php';
use Phongdao\KhoLuu\Viec;
use Phongdao\KhoLuu\Kho;
use Phongdao\HaTang\KhaiBao;
global $wpdb;
$bai = get_page_by_path('dong-mau-mot-cua-bai', OBJECT, KhaiBao::TAC_PHAM)->ID;
$made = array();
foreach (array('cau_moi'=>4, 'nhap_y_nghia'=>2, 'co_the_trung'=>4, 'can_xem'=>2, 'goi_y'=>3, 'nguoi_doc'=>4) as $loai=>$n) {
 for($i=0;$i<$n;$i++) {
  $dt=$bai; $kind='post'; $nd=array('chu'=>'[E2E-TEST] Nháp kiểm giao diện, không phải nội dung văn học.', 'ai'=>false, 'ly_do'=>array('[E2E-TEST] Kiểm thao tác'));
  if($loai==='cau_moi'||$loai==='co_the_trung') {
   $ids=array();
   for($j=0;$j<($loai==='co_the_trung'?2:1);$j++) {
    $ma="$loai-$i-$j";
    Kho::luu('review-mau',array('ma_goc'=>$ma,'loai'=>'ca_dao','tieu_de'=>'[E2E-TEST] Mẫu '.$ma,'loi'=>"[E2E-TEST] Dòng mẫu $ma\nDòng kiểm thao tác, không phải văn học",'doc_trang'=>gmdate('Y-m-d')));
    $ids[]=(int)$wpdb->get_var($wpdb->prepare('SELECT id FROM '.Kho::bang().' WHERE nguon_id=%s AND ma_goc=%s','review-mau',$ma));
   }
   $dt=$ids[0];$kind='kho';$nd=$loai==='co_the_trung'?array('a'=>'kho:'.$ids[0],'b'=>'kho:'.$ids[1],'giong'=>80):array();
  } elseif($loai==='goi_y') {
   $nd=array('loai'=>'chu_de','ma'=>'lao-dong','ly_do'=>'[E2E-TEST] Kiểm thao tác trên bài mẫu');
  } elseif($loai==='nguoi_doc') {
   $dt=wp_insert_comment(array('comment_post_ID'=>$bai,'comment_author'=>'[E2E-TEST]','comment_content'=>'[E2E-TEST] Cảm nghĩ thử giao diện','comment_approved'=>0));$kind='comment';$nd=array('loai'=>'cam_nghi','bai'=>$bai);
  }
  $made[]=array('loai'=>$loai,'id'=>Viec::tao(array('loai'=>$loai,'doi_tuong_loai'=>$kind,'doi_tuong_id'=>$dt,'noi_dung'=>$nd,'nguoi_tao'=>'review_fixture')));
 }
}
header('Content-Type: application/json');echo json_encode($made);
