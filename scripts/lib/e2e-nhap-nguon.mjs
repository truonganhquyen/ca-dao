// D-122: đi đúng form nhập/xem trước/lọc/đăng, chỉ dùng trên Playground kỹ thuật.
export async function kiemNhapNguon({bt,k,di,datO,bamDoiTrang,thu,chac,coChu,doi}) {
  const csv='ma_muc,loi,the_loai,trang,url\nfirst,"[UX-TEST-223] North223 amber willow cypress pine",ca_dao,iv,https://example.org/fixture-north\nsecond,"[UX-TEST-223] South223 cobalt nickel copper silver",tuc-ngu,9,https://example.org/fixture-south\n';
  let lan='';
  await thu('nhập nguồn: CSV xem trước đúng từng thể loại/vị trí, chưa có mục Kho; áp dụng rồi giữ đúng lần nhập',async()=>{
    await di(bt,'/wp-admin/admin.php?page=phongdao-kho&nhap=1');
    await bt.$eval('#nhap-tep details',d=>{d.open=true;});
    await datO(bt,'#nhap-chu',csv);
    await bt.select('#nhap-kieu','csv');
    await bamDoiTrang(bt,'.phongdao-kho-nhap button[type=submit]');
    chac(await coChu(bt,'Xem trước nhập'),'thiếu bước xem trước');
    const preview=bt.url();
    chac((await bt.$$('[name="giu[]"]')).length===2,'CSV không có đúng hai mục');
    const genres=await bt.$$eval('.phongdao-nhap-bang select',ss=>ss.map(s=>s.value));
    chac(genres.join(',')==='ca_dao,thanh_ngu_tuc_ngu','thể loại riêng từng mục không đúng');
    await di(bt,'/wp-admin/admin.php?page=phongdao-kho&tt=tat_ca&q=UX-TEST-223');
    chac(await coChu(bt,'0 câu khớp'),'xem trước đã ghi mục vào Kho');
    await bt.goto(preview,{waitUntil:'load',timeout:120000});
    await bamDoiTrang(bt,'button[name=lam][value=nhap]');
    lan=new URL(bt.url()).searchParams.get('lan_nhap');
    chac(lan && await coChu(bt,'2 câu khớp') && await coChu(bt,'Hiện tại: đã đăng 0 bài'),'nhập không trả đúng tập hai mục');
  });
  await thu('nhập lại CSV mở cùng biên nhận, không ghi đè hoặc tạo thêm mục',async()=>{
    chac(lan,'bước nhập trước chưa có biên nhận');
    await di(bt,'/wp-admin/admin.php?page=phongdao-kho&nhap=1');
    await bt.$eval('#nhap-tep details',d=>{d.open=true;});
    await datO(bt,'#nhap-chu',csv);
    await bt.select('#nhap-kieu','csv');
    await bamDoiTrang(bt,'.phongdao-kho-nhap button[type=submit]');
    chac(new URL(bt.url()).searchParams.get('xem_nhap')===lan && await coChu(bt,'mở lại lần nhập cũ'),'không nhận diện lần nhập lặp');
    await bamDoiTrang(bt,'a[href*="lan_nhap="]');
    chac(await coChu(bt,'2 câu khớp'),'nạp lại tạo thêm mục hoặc làm mất phạm vi');
  });
  await thu('đăng từ lần nhập: đúng hai bài, thông báo kết quả thật, thẻ đóng và hai trang đọc mở được',async()=>{
    chac(lan,'bước nhập trước chưa có biên nhận');
    await di(bt,`/wp-admin/admin.php?page=phongdao-kho&tt=tat_ca&lan_nhap=${lan}`);
    await bt.$$eval('input[name="ids[]"]',ss=>ss.forEach(s=>s.click()));
    await bamDoiTrang(bt,'.phongdao-thanh-noi button[name=lam][value=dang]');
    chac((await bt.$$('input[name="giu[]"]')).length===2,'lô lấy nhầm mục ngoài lần nhập');
    await bamDoiTrang(bt,'button[name=lam][value=ap_dung]');
    await doi(bt,()=>document.body.innerText.includes('Đã xử lý hết'),null,'lô chưa hoàn tất',120000);
    chac(await coChu(bt,'Đã đăng 2/2 bài.') && await coChu(bt,'Lỗi 0'),'kết quả không báo đúng hai bài');
    const urls=await bt.$$eval('.phongdao-lo-lui .phongdao-hv-lui a',aa=>aa.map(a=>a.href));
    chac(new Set(urls).size===2,'thiếu hai bài riêng biệt');
    for(const url of urls){const r=await k.goto(url,{waitUntil:'load',timeout:120000});chac(r.status()===200,'bài vừa đăng không đọc được');}
    await di(bt,`/wp-admin/admin.php?page=phongdao-kho&tt=tat_ca&lan_nhap=${lan}`);
    chac((await bt.$$('.phongdao-tp-hanh a[href*="xem=nhanh"]')).length===0,'mục đã đăng vẫn có hành động chờ đăng');
  });
  await thu('mục nghi trùng mở đúng thẻ, quyết khác nhau rồi trở về đúng lần nhập và giữ kết quả',async()=>{
    await di(bt,'/wp-admin/admin.php?page=phongdao-kho&nhap=1');
    await bt.$eval('#nhap-tep details',d=>{d.open=true;});
    await datO(bt,'#nhap-chu','[UX-TEST-223] North223 amber willow cypress píne');
    await bt.select('#nhap-loai','ca_dao');
    await bamDoiTrang(bt,'.phongdao-kho-nhap button[type=submit]');
    await bamDoiTrang(bt,'button[name=lam][value=nhap]');
    const trung=new URL(bt.url()).searchParams.get('lan_nhap');
    chac(trung && await coChu(bt,'Đang chờ quyết trùng'),'mục khác dấu không qua đối chiếu');
    await bamDoiTrang(bt,'.phongdao-tp-hanh a[href*="viec="]');
    await bamDoiTrang(bt,'button[name=quyet][value=khac]');
    chac(new URL(bt.url()).searchParams.get('page')==='phongdao-kho' && new URL(bt.url()).searchParams.get('lan_nhap')===trung && await coChu(bt,'Đã xác nhận là bài khác.'),'quyết thẻ không trở về đúng lần nhập/kết quả');
  });
  await thu('nhập không JavaScript: thiếu thể loại giữ xem trước/lựa chọn; sửa rồi nhập và đăng qua form thường',async()=>{
    await bt.setJavaScriptEnabled(false);
    try {
      await di(bt,'/wp-admin/admin.php?page=phongdao-kho&nhap=1');
      await bt.click('#nhap-tep details summary');
      await datO(bt,'#nhap-chu','[UX-TEST-223-NOJS] East223 coral kelp reef ocean');
      await bamDoiTrang(bt,'.phongdao-kho-nhap button[type=submit]');
      await bamDoiTrang(bt,'button[name=lam][value=nhap]');
      chac(await coChu(bt,'cần có lời và chọn thể loại') && await coChu(bt,'East223 coral'),'lỗi nhập làm mất bản xem trước');
      await bt.select('#nhap-loai-0','ca_dao');
      await bamDoiTrang(bt,'button[name=lam][value=nhap]');
      const nojs=new URL(bt.url()).searchParams.get('lan_nhap');
      chac(nojs && await coChu(bt,'1 câu khớp'),'nhập không JS không giữ đúng phạm vi');
      await bamDoiTrang(bt,'.phongdao-tp-hanh a[href*="xem=nhanh"]');
      await bamDoiTrang(bt,'button[name=lam][value=dang]');
      chac(await coChu(bt,'Đã đăng'),'đăng không JS không thành công');
      await di(bt,`/wp-admin/admin.php?page=phongdao-kho&tt=tat_ca&lan_nhap=${nojs}`);
      chac(await coChu(bt,'Đã thành tác phẩm'),'thẻ không chuyển sang đã đăng');
    } finally {await bt.setJavaScriptEnabled(true);}
  });
}
