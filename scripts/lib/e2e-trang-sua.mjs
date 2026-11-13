// Hồi quy giao diện D-109, chạy trong chính e2e:v2 trên WordPress mẫu.
export async function kiemTrangSua({bt,G,di,bam,datO,bamDoiTrang,chac,thu,thuMucAnh}) {
  const dialog=d=>d.accept().catch(()=>{});bt.on('dialog',dialog);
  let url,id;
  const clear=()=>bt.evaluate(()=>document.querySelectorAll('form[data-giu-nhap]').forEach(f=>window.phongdaoNhapDo?.daLuu(f)));
  const open=async part=>{const width=await bt.evaluate(()=>innerWidth);if(width<1200)await bt.select('[data-chon-phan]',part);else await bam(bt,`[data-toi-phan="${part}"]`);};
  try {
    await di(bt,'/ve/ux-test-trang-sua-dai/');id=await bt.$eval('.dai-bien-tap',e=>e.dataset.bai);url=`/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`;
    await thu('Sửa đầy đủ: 36 mục gọn, mục lục tới phân loại/ảnh/lịch sử ở sáu cỡ màn hình',async()=>{
      for(const width of [390,768,1024,1199,1200,1440]){
        await bt.setViewport({width,height:900});await di(bt,url);await open('phan-chu_thich');
        chac(await bt.$$eval('#phan-chu_thich [data-ds]>li',a=>a.length)===36,'thiếu mục mẫu');
        chac(await bt.$$eval('#phan-chu_thich .phongdao-muc-gon[open]',a=>a.length)===0,'36 form bị bung sẵn');
        chac(await bt.$eval('#phan-chu_thich',e=>e.getBoundingClientRect().height<6000),'phần từ khó vẫn quá dài');
        for(const part of ['phan-loai','phan-media','lich-su']){
          await open(part);chac(await bt.$eval('#'+part,e=>e.getBoundingClientRect().top>=0&&e.getBoundingClientRect().top<innerHeight),`${width}: không tới ${part}`);
        }
        chac(await bt.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${width}: tràn ngang`);
        if(thuMucAnh){await open('phan-chu_thich');await bt.screenshot({path:`${thuMucAnh}/trang-sua-36-muc-${width}.png`,fullPage:true});}
      }
      await open('phan-media');chac(await bt.$eval('[data-anh-minh-hoa]',e=>e.textContent.includes('Tệp tối đa')&&e.querySelector('[data-anh-bo]').disabled),'không báo giới hạn sớm hoặc bỏ ảnh bật khi trống');
    });
    await thu('Sửa đầy đủ: gập/lọc/tải lại giữ lời giải và nguồn của mục, tóm tắt khôi phục đúng chữ',async()=>{
      await di(bt,url);await open('phan-chu_thich');const first='#phan-chu_thich [data-ds]>li:first-child';await bam(bt,first+' .phongdao-muc-gon>summary');await datO(bt,first+' textarea','[UX-TEST] preserved row 01');
      await bam(bt,first+' .disclosure>summary');await datO(bt,first+' input[name$="[url]"]','https://example.invalid/ux-test');await bam(bt,first+' .phongdao-muc-gon>summary');await datO(bt,'#phan-chu_thich [data-loc-hang]','item 2');
      chac(await bt.$eval(first,e=>e.hidden),'lọc không thu gọn danh sách');await di(bt,url);
      chac(await bt.$eval(first,e=>e.querySelector('textarea').value==='[UX-TEST] preserved row 01'&&e.querySelector('input[name$="[url]"]').value==='https://example.invalid/ux-test'&&e.querySelector('[data-tom-tat-muc]').textContent.includes('preserved row 01')),'khôi phục làm mất lời giải/nguồn hoặc tóm tắt rỗng');
      chac(await bt.$$eval('#phan-chu_thich [data-ds]>li',es=>es.length)===36,'lọc đã loại mục khỏi biểu mẫu');await clear();
    });
    await thu('Sửa đầy đủ: khôi phục mở phần trên điện thoại, giữ đúng nhiều checkbox và không tự sang bài khi còn chữ',async()=>{
      await bt.setViewport({width:390,height:900});await di(bt,url);await open('phan-y_nghia');await datO(bt,'#phan-y_nghia textarea','[UX-TEST] recovery');
      await open('phan-boi_canh');await datO(bt,'#s-bc-chu','[UX-TEST] another part');
      await open('phan-loai');await bam(bt,'#phan-loai-chu_de>summary');
      const selected=await bt.$$eval('#pl-chu_de-ds input',es=>es.slice(0,2).map(e=>{e.checked=true;e.dispatchEvent(new Event('change',{bubbles:true}));return e.value;}));
      await di(bt,url);
      chac(await bt.$eval('#phan-y_nghia',d=>d.open&&d.getBoundingClientRect().height>0&&d.querySelector('textarea').value==='[UX-TEST] recovery'),'phần khôi phục bị ẩn hoặc mất chữ');
      chac(await bt.$$eval('#pl-chu_de-ds input:checked',es=>es.map(e=>e.value).sort().join(','))===selected.sort().join(','),'khôi phục checkbox chọn nhầm toàn bộ mục');
      chac(await bt.$eval('[data-tong-chua-luu]',e=>e.textContent.includes('3 phần chưa lưu')),'thiếu tổng phần chưa lưu');
      // Lấy một ID bài có thật để kiểm guard chuyển bài, không dựa vào ID liên tiếp.
      const href=await bt.$eval('.phongdao-sua-lien a',a=>a.href);const extra=await bt.browserContext().newPage();await extra.goto(href,{waitUntil:'load'});const nextId=await extra.$$eval('a[href*="page=phongdao-sua-tac-pham"]',(as,id)=>as.map(a=>new URL(a.href).searchParams.get('id')).find(x=>x!==id),id);await extra.close();chac(nextId,'không có bài kế trong mẫu');
      await di(bt,url+'&bai_sau='+nextId);await bam(bt,'#phongdao-sang-tiep');await open('phan-y_nghia');await bamDoiTrang(bt,'#phan-y_nghia button[name=duyet][value=""]');
      chac(new URL(bt.url()).searchParams.get('id')===id,'tự sang bài dù còn phần chưa lưu');
      chac(await bt.$eval('#s-bc-chu',e=>e.value==='[UX-TEST] another part'),'lưu một phần làm mất chữ phần khác');
      chac(await bt.$eval('[data-tong-chua-luu]',e=>e.textContent.includes('2 phần chưa lưu')),'phần vừa lưu chưa được dọn đúng bộ đệm');await clear();
    });
    await thu('Sửa đầy đủ: bộ phân loại trống đổi ở tab khác không bị bộ đệm ghi đè; đối chiếu rồi mới lưu tiếp',async()=>{
      await di(bt,url);await open('phan-loai');await bam(bt,'#phan-loai-chu_de>summary');const ids=await bt.$$eval('#pl-chu_de-ds input',es=>es.slice(0,2).map(e=>e.value));chac(ids.length===2,'mẫu thiếu hai chủ đề');await bam(bt,`#pl-chu_de-${ids[0]}`);
      const other=await bt.browserContext().newPage();try{await di(other,url);await bam(other,'#phan-loai-chu_de>summary');await bam(other,`#pl-chu_de-${ids[1]}`);await bamDoiTrang(other,'#phan-loai-chu_de button[type=submit]');}finally{await other.close();}
      await di(bt,url);chac(await bt.$('#phan-loai-chu_de [data-moc-moi]'),'không nhận ra mốc trống cũ sau khôi phục');await bamDoiTrang(bt,'#phan-loai-chu_de button[type=submit]');chac(new URL(bt.url()).searchParams.get('ket_qua')==='loi','bộ đệm dùng mốc mới âm thầm ghi đè');
      const verify=await bt.browserContext().newPage();try{await di(verify,url);chac(await verify.$$eval('#pl-chu_de-ds input:checked',es=>es.map(e=>e.value).join(','))===ids[1],'bài đã bị ghi đè trước khi đối chiếu');}finally{await verify.close();}
      await bam(bt,'#phan-loai-chu_de [data-moc-moi]');await bamDoiTrang(bt,'#phan-loai-chu_de button[type=submit]');chac(await bt.$$eval('#pl-chu_de-ds input:checked',es=>es.map(e=>e.value).join(','))===ids[0],'đối chiếu xong không lưu tiếp được');await clear();
    });
    await thu('Sửa đầy đủ: thêm/trùng mục không mất chữ, không tự chọn, nonce sai bị chặn',async()=>{
      await di(bt,url);await open('phan-boi_canh');await datO(bt,'#s-bc-chu','[UX-TEST] retained while adding');await open('phan-loai');await bam(bt,'#phan-loai-chu_de>summary');await bam(bt,'#phan-loai-chu_de .phongdao-them-muc>summary');
      const name='[UX-TEST] New category '+Date.now(),box='#phan-loai-chu_de [data-them-danh-muc]';
      await datO(bt,box+' input',name);await bam(bt,box+' button');await bt.waitForFunction(s=>document.querySelector(s).textContent.includes('Đã thêm'),{},box+' [data-tao-muc-bao]');
      const item=await bt.$$eval('#pl-chu_de-ds label',(ls,name)=>{const l=ls.find(l=>l.textContent.includes(name));return l?{id:l.querySelector('input').value,checked:l.querySelector('input').checked}:null;},name);
      chac(item&&!item.checked,'thêm mục tự chọn hoặc không xuất hiện');chac(await bt.$eval('#s-bc-chu',e=>e.value==='[UX-TEST] retained while adding'),'thêm mục làm mất chữ');
      chac(await bt.evaluate(()=>window.phongdaoNhapDo.cacPhan().length)===1,'mục mới chưa chọn bị tính thành thay đổi phân loại');
      await datO(bt,box+' input',name);await bam(bt,box+' button');await bt.waitForFunction(s=>document.querySelector(s).textContent.includes('Mục đã có'),{},box+' [data-tao-muc-bao]');
      chac(await bt.$$eval('#pl-chu_de-ds input',(es,id)=>es.filter(e=>e.value===id).length,item.id)===1,'trùng tên sinh checkbox trùng');
      const status=await bt.evaluate(async()=>{const b=new FormData();b.set('action','phongdao_them_muc');b.set('tax','chu_de');b.set('ten','[UX-TEST] bad nonce');b.set('_ajax_nonce','invalid');return (await fetch(ajaxurl,{method:'POST',body:b})).status;});chac(status===403,'nonce sai vẫn tạo mục');
      await bam(bt,`#pl-chu_de-${item.id}`);await bamDoiTrang(bt,'#phan-loai-chu_de button[type=submit]');chac(await bt.$eval(`#pl-chu_de-${item.id}`,e=>e.checked),'mục mới không lưu được');await clear();
    });
    await thu('Sửa đầy đủ: người chọn/bỏ/quay lại thể thơ máy, sửa lời giữ lựa chọn, POST phiên cũ bị chặn',async()=>{
      await di(bt,url);await open('phan-loai');await bam(bt,'#the-tho>summary');const old=await bt.$eval('#the-tho [name=phien_the_tho]',e=>e.value);const choice=await bt.$eval('#the-tho-ma',e=>[...e.options].find(o=>o.value).value);
      await bam(bt,'#the-tho [name=che_do][value=nguoi]');await bt.select('#the-tho-ma',choice);await bamDoiTrang(bt,'#the-tho button[type=submit]');
      await open('phan-loi');await bt.$eval('#phan-loi textarea',e=>{e.value+='\n[UX-TEST] changed technical line';e.dispatchEvent(new Event('input',{bubbles:true}));});await bamDoiTrang(bt,'#phan-loi button[type=submit]');
      chac(await bt.$eval('#the-tho-ma',e=>e.value)===choice&&await bt.$eval('#the-tho [value=nguoi]',e=>e.checked),'máy ghi đè lựa chọn người sau sửa lời');
      const blocked=await bt.$eval('#the-tho form',async(f,old)=>{const data=new FormData(f);data.set('che_do','may');data.set('phien_the_tho',old);const r=await fetch(f.getAttribute('action'),{method:'POST',body:data});return (await r.text()).includes('Lựa chọn hoặc phân loại vừa thay đổi');},old);chac(blocked,'phiên cũ không bị chặn');
      await di(bt,url);await open('phan-loai');await bam(bt,'#the-tho>summary');await bt.select('#the-tho-ma','');await bamDoiTrang(bt,'#the-tho button[type=submit]');
      chac(await bt.$eval('#the-tho>summary',e=>e.textContent.includes('không gắn nhãn')),'không bỏ được nhãn thể thơ');
      await bam(bt,'#the-tho [name=che_do][value=may]');await bamDoiTrang(bt,'#the-tho button[type=submit]');chac(await bt.$eval('#the-tho [value=may]',e=>e.checked),'không quay lại dùng máy');await clear();
    });
    await thu('Sửa đầy đủ không JavaScript: mục lục, mở một mục, sửa và lưu được',async()=>{
      const p=await bt.browserContext().newPage();try{await p.setJavaScriptEnabled(false);await p.setViewport({width:390,height:900});await di(p,url);await bam(p,'[data-toi-phan="phan-chu_thich"]');await bam(p,'#phan-chu_thich>summary');await bam(p,'#phan-chu_thich [data-ds]>li:first-child .phongdao-muc-gon>summary');
        const selector='#phan-chu_thich [data-ds]>li:first-child textarea';await p.focus(selector);await p.keyboard.down('Control');await p.keyboard.press('A');await p.keyboard.up('Control');await p.type(selector,'[UX-TEST] saved without JavaScript');await bamDoiTrang(p,'#phan-chu_thich button[type=submit]');
        chac(await p.$eval(selector,e=>e.value==='[UX-TEST] saved without JavaScript'),'no-JS không lưu được từ khó');chac(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'no-JS tràn ngang');
      }catch(error){
        const diagnostic=await p.evaluate(()=>({url:location.href,navPosition:getComputedStyle(document.querySelector('.phongdao-muc-luc')).position,invalid:[...document.querySelectorAll(':invalid')].map(e=>({tag:e.tagName,name:e.name,message:e.validationMessage})),submit:[...document.querySelectorAll('#phan-chu_thich button[type=submit]')].map(b=>{const r=b.getBoundingClientRect();const top=document.elementFromPoint(r.x+r.width/2,r.y+r.height/2);return {form:!!b.form,action:b.form?.getAttribute('action'),rect:{x:r.x,y:r.y,w:r.width,h:r.height},coveredBy:top?.tagName+' '+top?.className};})})).catch(()=>null);
        console.log('NO-JS chẩn đoán: '+JSON.stringify(diagnostic));
        if(thuMucAnh)await p.screenshot({path:`${thuMucAnh}/trang-sua-nojs-failure.png`,fullPage:true});throw error;
      }finally{await p.close();}
    });
  } finally {await clear().catch(()=>{});bt.off('dialog',dialog);}
}
