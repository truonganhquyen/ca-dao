// Chỉ gọi trong Playground sau đăng nhập; không chạy với --site.
export async function kiemThungRac({bt,di,bam,datO,bamDoiTrang: chuyenTrang,chac,thu,thuMucAnh}) {
  // Enter gửi form native; không phụ thuộc chuột trong chế độ mô phỏng cảm ứng của Chrome Linux.
  const bamDoiTrang = (tab,sel) => chuyenTrang(tab,sel,{banPhim:!tab.isJavaScriptEnabled()});
  const thuKhongJs = (ten,fn) => thu(ten,async()=>{try{await fn();}finally{await bt.setJavaScriptEnabled(true);}});
  await thuKhongJs('chờ trùng: bàn phím không JavaScript nối thẻ mất và mở đúng thẻ Để sau', async () => {
    await bt.setJavaScriptEnabled(false);
    const kho='/wp-admin/admin.php?page=phongdao-kho&nguon=test207';
    await di(bt,kho);
    await bamDoiTrang(bt,'form:has(input[name=action][value=phongdao_kho_kiem_trung]) button');
    const card=await bt.$eval('article[id^=viec-]',e=>e.id);
    chac(card && bt.url().includes('loai=co_the_trung'),'không mở đúng thẻ trùng');
    await bamDoiTrang(bt,`#${card} button[name=quyet][value=de_sau]`);
    await di(bt,kho);
    await bamDoiTrang(bt,'form:has(input[name=action][value=phongdao_kho_kiem_trung]) button');
    chac(bt.url().includes('tt=de_sau') && !!(await bt.$(`#${card}`)),'không mở lại đúng thẻ Để sau');
    await bt.setJavaScriptEnabled(true);
  });
  const ids=[];
  const ds=tt=>'/wp-admin/admin.php?page=phongdao-tac-pham&tt='+tt+'&q='+encodeURIComponent('E2E Delete 207');
  await thuKhongJs('thùng rác: nút từng bài mở xem trước, Khôi phục về nháp; bàn phím không JavaScript', async () => {
    for(let i=1;i<=2;i++) {
      await di(bt,'/wp-admin/admin.php?page=phongdao-them');
      await datO(bt,'#them-loi','[UX-TEST] technical delete fixture '+i);
      await datO(bt,'#them-ten','[UX-TEST] E2E Delete 207 '+i);
      await bamDoiTrang(bt,'button[type=submit][name=dang][value="0"]');
      ids.push(await bt.$eval('#phan-loi input[name=id]',o=>o.value));
    }
    await bt.setJavaScriptEnabled(false);
    await bt.setViewport({width:390,height:844});
    await di(bt,ds('draft'));
    await bamDoiTrang(bt,`button[name=lam_mot][value="thung_rac:${ids[0]}"]`);
    chac(await bt.$$eval('input[name="giu[]"]',os=>os.length)===1,'nút từng hàng lấy thêm bài khác');
    await bamDoiTrang(bt,'button[name=lam][value=ap_dung]');
    await di(bt,ds('trash'));
    chac(await bt.$$eval('input[name="ids[]"]',os=>os.length)===1,'bài chưa vào rác');
    await bamDoiTrang(bt,`button[name=lam_mot][value="lay_ra:${ids[0]}"]`);
    await bamDoiTrang(bt,'button[name=lam][value=ap_dung]');
    await di(bt,ds('draft'));
    chac(await bt.$$eval('input[name="ids[]"]',os=>os.length)===2,'Khôi phục chưa trả bài về nháp');
    await bt.setJavaScriptEnabled(true);
  });
  await thu('thùng rác: chọn nhiều bài từ thanh thao tác; xoá vĩnh viễn cần xác nhận, không tràn 390/1440 px', async () => {
    await bt.setViewport({width:1440,height:900});
    await di(bt,ds('draft'));
    for(const id of ids) await bam(bt,`input[name="ids[]"][value="${id}"]`);
    await bamDoiTrang(bt,'.phongdao-thanh-noi button[name=thao_tac_nhanh][value=thung_rac]');
    await bamDoiTrang(bt,'button[name=lam][value=ap_dung]');
    await di(bt,ds('trash'));
    for(const id of ids) await bam(bt,`input[name="ids[]"][value="${id}"]`);
    await bamDoiTrang(bt,'.phongdao-thanh-noi button[name=thao_tac_nhanh][value=xoa_vinh_vien]');
    chac(!(await bt.$('input[name=tu_chay]')),'lô xoá còn tự chạy');
    const url=bt.url();
    await bam(bt,'button[name=lam][value=ap_dung]');
    chac(bt.url()===url && await bt.$eval('input[name=xac_nhan_xoa]',e=>e.validity.valueMissing),'thiếu xác nhận mà vẫn gửi form');
    for(const width of [390,1440]) {
      await bt.setViewport({width,height:900});
      chac(await bt.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'xem trước xoá tràn ngang '+width);
      if(thuMucAnh) await bt.screenshot({path:`${thuMucAnh}/thung-rac-${width}.png`,fullPage:true});
    }
    await bam(bt,'input[name=xac_nhan_xoa]');
    await bamDoiTrang(bt,'button[name=lam][value=ap_dung]');
    await di(bt,ds('trash'));
    chac(await bt.$$eval('input[name="ids[]"]',os=>os.length)===0,'bài còn trong thùng rác sau khi xoá');
    await di(bt,ds('tat_ca'));
    chac(await bt.$$eval('input[name="ids[]"]',os=>os.length)===0,'bài bị xoá còn trong danh sách');
  });
  await bt.setJavaScriptEnabled(true);
}
