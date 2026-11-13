// D-109: bài có 36 mục, sáu cỡ màn hình và màn danh mục. Gọi từ quet:v2.
export async function quetTrangSua({bt,G,axeNguon,THE_WCAG,loi,thuMucAnh,doTran}) {
  await bt.goto(G+'/ve/ux-test-trang-sua-dai/',{waitUntil:'load',timeout:120000});
  const id=await bt.$eval('.dai-bien-tap',e=>e.dataset.bai);
  const url=G+`/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`;
  for(const width of [390,768,1024,1199,1200,1440]) {
    await bt.setViewport({width,height:900});await bt.goto(url,{waitUntil:'load',timeout:120000});await bt.evaluate(()=>document.fonts.ready);
    await bt.$eval('#phan-chu_thich',d=>d.open=true);
    if(await doTran(bt))loi.push(`Trang sửa 36 mục (${width}): tràn ngang`);
    await bt.addScriptTag({content:axeNguon});
    const result=await bt.evaluate(tags=>window.axe.run('.phongdao-admin-sua',{runOnly:{type:'tag',values:tags}}),THE_WCAG);
    for(const v of result.violations)for(const n of v.nodes.slice(0,3))loi.push(`Trang sửa 36 mục (${width}): ${v.id} — ${n.target.join(' ')}`);
    if(thuMucAnh)await bt.screenshot({path:`${thuMucAnh}/sua-36-muc-${width}.png`,fullPage:true});
    await bt.evaluate(()=>{document.querySelector('#phan-chu_thich .phongdao-muc-gon').open=true;document.querySelector('#the-tho').open=true;document.querySelector('#phan-loai-chu_de').open=true;document.querySelector('#phan-loai-chu_de .phongdao-them-muc').open=true;});
    const opened=await bt.evaluate(tags=>window.axe.run('.phongdao-admin-sua',{runOnly:{type:'tag',values:tags}}),THE_WCAG);
    for(const v of opened.violations)for(const n of v.nodes.slice(0,3))loi.push(`Trang sửa đang nhập (${width}): ${v.id} — ${n.target.join(' ')}`);
    if([390,1440].includes(width)){
      await bt.evaluate(()=>{document.getElementById('phan-y_nghia').open=true;document.getElementById('duong-dan').open=true;});
      await bt.waitForSelector('#phan-y_nghia iframe',{visible:true,timeout:30000});
      await bt.$eval('#duong-dan input[name=xac_nhan_url]',e=>e.focus());
      const editor=await bt.evaluate(tags=>window.axe.run('.phongdao-admin-sua',{runOnly:{type:'tag',values:tags}}),THE_WCAG);
      for(const v of editor.violations)for(const n of v.nodes.slice(0,3))loi.push(`Editor và URL (${width}): ${v.id} — ${n.target.join(' ')}`);
      if(await doTran(bt))loi.push(`Editor và URL (${width}): tràn ngang`);
      if(thuMucAnh){await bt.$eval('#phan-y_nghia',e=>e.scrollIntoView({block:'start'}));await bt.screenshot({path:`${thuMucAnh}/editor-url-${width}.png`});}
    }
  }
  for(const width of [390,1440]){
    await bt.setViewport({width,height:900});await bt.goto(G+'/wp-admin/admin.php?page=phongdao-danh-muc',{waitUntil:'load',timeout:120000});
    await bt.addScriptTag({content:axeNguon});const result=await bt.evaluate(tags=>window.axe.run('.phongdao-danh-muc',{runOnly:{type:'tag',values:tags}}),THE_WCAG);
    for(const v of result.violations)loi.push(`Danh mục (${width}): ${v.id} — ${v.help}`);if(await doTran(bt))loi.push(`Danh mục (${width}): tràn ngang`);
  }
}
