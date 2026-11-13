// Hồi quy thao tác editor/URL trên WordPress mẫu, trong cổng e2e:v2.
export async function kiemEditorUrl({bt,G,di,bam,datO,bamDoiTrang,chac,thu,thuMucAnh}) {
  const accept=d=>d.accept().catch(()=>{});bt.on('dialog',accept);
  const clear=()=>bt.evaluate(()=>document.querySelectorAll('form[data-giu-nhap]').forEach(f=>window.phongdaoNhapDo?.daLuu(f)));
  let url,id;
  const open=async part=>{if(await bt.evaluate(()=>innerWidth)<1200)await bt.select('[data-chon-phan]',part);else await bam(bt,`[data-toi-phan="${part}"]`);};
  const ready=async()=>{await bt.waitForFunction(()=>{const f=document.querySelector('#phan-y_nghia iframe');return f&&window.tinymce?.get(f.id.replace(/_ifr$/,''))?.initialized;},{timeout:30000});};
  try {
    await di(bt,'/ve/ux-test-trang-sua-dai/');id=await bt.$eval('.dai-bien-tap',e=>e.dataset.bai);url=`/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`;
    await thu('Editor: gõ bằng bàn phím, đậm/link/màu, lưu và người đọc thấy đúng',async()=>{
      await bt.setViewport({width:390,height:900});await di(bt,url);await open('phan-y_nghia');await ready();
      const handle=await bt.$('#phan-y_nghia iframe'),frame=await handle.contentFrame();await frame.click('body');
      await bt.keyboard.down('Control');await bt.keyboard.press('A');await bt.keyboard.up('Control');await bt.keyboard.type('[EDITOR-TEST] formatted body');
      await bt.keyboard.down('Control');await bt.keyboard.press('A');await bt.keyboard.press('B');await bt.keyboard.up('Control');
      await bt.evaluate(()=>{const e=tinymce.get(document.querySelector('#phan-y_nghia iframe').id.replace(/_ifr$/,''));e.execCommand('mceInsertLink',false,{href:'/ca-dao/dong-mau-mot-cua-bai/'});e.fire('change');});
      const bold=await bt.$('#phan-y_nghia button[aria-label^="Đậm"],#phan-y_nghia button[aria-label^="Bold"]');chac(bold,'thiếu nút đậm có tên');await bold.focus();await bt.keyboard.press('Space');
      chac(await bt.$eval('#phan-y_nghia [data-editor-html]',e=>!e.value.includes('<strong>')),'Space không bỏ đậm đúng một lần');await bold.focus();await bt.keyboard.press('Space');
      chac(await bold.evaluate(e=>e.getAttribute('aria-pressed')==='true'),'nút thật không cập nhật trạng thái đậm');
      // Chọn màu qua menu thật đã cấu hình, không tự đăng ký formatter trong phép kiểm.
      const chooseColor=async name=>{await bam(bt,'#phan-y_nghia .mce-menubtn button');const options=await bt.$$('.mce-menu-item');let color=null;for(const item of options)if((await item.evaluate(e=>e.textContent)).includes(name)){color=item;break;}chac(color,'thiếu lựa chọn '+name);await color.click();};
      await chooseColor('Màu tím');await chooseColor('Màu xanh');
      chac(await bt.$eval('#phan-y_nghia [data-editor-html]',e=>e.value.includes('pd-chu-xanh')&&!e.value.includes('pd-chu-tim')),'đổi màu chồng lớp cũ hoặc không đổi được');
      await chooseColor('Màu tím');
      chac(await bt.$eval('#phan-y_nghia [data-editor-html]',e=>e.value.includes('strong')&&e.value.includes('href=')&&e.value.includes('pd-chu-tim')),'editor không đồng bộ định dạng về form');
      chac(await bt.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'editor tràn ngang điện thoại');
      if(thuMucAnh)await bt.screenshot({path:`${thuMucAnh}/editor-don-gian-390.png`,fullPage:true});
      await bamDoiTrang(bt,'#phan-y_nghia button[name=duyet][value="1"]');
      chac(await bt.$eval('#phan-y_nghia [data-editor-chu]',e=>e.value==='[EDITOR-TEST] formatted body'),'sau lưu chữ thuần bị lẫn markup');
      const ctx=await bt.browser().createBrowserContext();try{const guest=await ctx.newPage();await di(guest,'/ve/ux-test-trang-sua-dai/');chac(await guest.$('.y-nghia-chu strong a, .y-nghia-chu a strong, .y-nghia-chu strong'),'khách không thấy đậm');chac(await guest.$('.y-nghia-chu a[href="/ca-dao/dong-mau-mot-cua-bai/"]'),'khách không thấy link');chac(await guest.$('.y-nghia-chu .pd-chu-tim'),'khách không thấy màu');}finally{await ctx.close();}
      await clear();
    });
    await thu('Editor: phần chưa lưu khôi phục cả chữ và link sau khi tải lại',async()=>{
      await di(bt,url);await open('phan-y_nghia');await ready();
      const frame=await (await bt.$('#phan-y_nghia iframe')).contentFrame();await frame.click('body');await bt.keyboard.press('End');await bt.keyboard.type(' draft');
      const expected=await bt.$eval('#phan-y_nghia [data-editor-chu]',e=>e.value);const format=await bt.$eval('#phan-y_nghia [data-editor-html]',e=>e.value);
      await di(bt,url);await ready();chac(await bt.$eval('#phan-y_nghia [data-editor-chu]',e=>e.value)===expected,'mất chữ chưa lưu');chac(await bt.$eval('#phan-y_nghia [data-editor-html]',e=>e.value)===format,'mất định dạng chưa lưu');chac(await bt.$('#phan-y_nghia [data-nhap-do-bao]'),'không báo khôi phục');await clear();
    });
    await thu('Editor: định dạng khôi phục từ bộ đệm không thực thi ảnh/mã lạ',async()=>{
      await di(bt,url);
      await bt.evaluate(()=>{const f=document.querySelector('#phan-y_nghia form');f.querySelector('[data-editor-chu]').value='[EDITOR-TEST] safe';f.querySelector('[data-editor-html]').value='<strong>[EDITOR-TEST] safe</strong><img src="/missing-editor-image" onerror="top.__pdEditorXss=1">';f.dispatchEvent(new Event('change',{bubbles:true}));});
      await di(bt,url);await ready();
      chac(await bt.evaluate(()=>!window.__pdEditorXss),'mã lạ trong bản khôi phục được thực thi');
      chac(await bt.evaluate(()=>!tinymce.get(document.querySelector('#phan-y_nghia iframe').id.replace(/_ifr$/,'')).getBody().querySelector('img,script,iframe')),'editor giữ lại nội dung nhúng ngoài phạm vi');await clear();
    });
    await thu('URL: xác nhận, đổi tên, đường cũ 301, giữ bài và chặn nonce/bản mở cũ',async()=>{
      await di(bt,url);await open('duong-dan');const old=await bt.$eval('#pd-url-slug',e=>e.value),stamp=await bt.$eval('#duong-dan [name=phien_duong_dan]',e=>e.value),slug=old+'-editor';
      const bad=await bt.$eval('#duong-dan form',async f=>{const b=new FormData(f);b.set('_wpnonce','invalid');b.set('xac_nhan_url','1');return (await fetch(f.getAttribute('action'),{method:'POST',body:b})).status;});chac(bad===403,'nonce sai vẫn đổi được URL');
      await datO(bt,'#pd-url-slug',slug);chac(await bt.$eval('[data-url-xem]',e=>e.textContent.includes('-editor/')),'không xem trước URL');await bam(bt,'#duong-dan [name=xac_nhan_url]');await bamDoiTrang(bt,'#duong-dan button[type=submit]');
      chac(await bt.$eval('#pd-url-slug',e=>e.value)===slug,'không lưu URL mới');chac(new URL(bt.url()).searchParams.get('id')===id,'đổi ID bài');
      const response=await fetch(G+'/ve/'+old+'/',{redirect:'manual'});chac(response.status===301&&new URL(response.headers.get('location'),G).pathname==='/ve/'+slug+'/','đường cũ không 301 đúng đích');
      const stale=await bt.$eval('#duong-dan form',async(f,stamp)=>{const b=new FormData(f);b.set('phien_duong_dan',stamp);b.set('slug','ux-test-stale-url');b.set('xac_nhan_url','1');return (await (await fetch(f.getAttribute('action'),{method:'POST',body:b})).text()).includes('Đường dẫn hoặc trạng thái bài đã đổi');},stamp);chac(stale,'bản mở cũ vẫn đổi được');
      await di(bt,url);chac(await bt.$eval('#pd-url-slug',e=>e.value)===slug,'URL mới bị bản cũ ghi đè');await open('duong-dan');await datO(bt,'#pd-url-slug',old);await bam(bt,'#duong-dan [name=xac_nhan_url]');await bamDoiTrang(bt,'#duong-dan button[type=submit]');await clear();
    });
    await thu('URL không JavaScript: sửa, xác nhận, lưu và chuyển hướng được',async()=>{
      const p=await bt.browserContext().newPage();try{await p.setJavaScriptEnabled(false);await p.setViewport({width:390,height:900});await di(p,url);await bam(p,'#duong-dan>summary');const old=await p.$eval('#pd-url-slug',e=>e.value);
        await p.focus('#pd-url-slug');await p.keyboard.down('Control');await p.keyboard.press('A');await p.keyboard.up('Control');await p.type('#pd-url-slug',old+'-nojs');await bam(p,'#duong-dan [name=xac_nhan_url]');await bamDoiTrang(p,'#duong-dan button[type=submit]');chac(await p.$eval('#pd-url-slug',e=>e.value)===old+'-nojs','no-JS không lưu URL');
        const response=await fetch(G+'/ve/'+old+'/',{redirect:'manual'});chac(response.status===301,'no-JS thiếu 301');
        await p.focus('#pd-url-slug');await p.keyboard.down('Control');await p.keyboard.press('A');await p.keyboard.up('Control');await p.type('#pd-url-slug',old);await bam(p,'#duong-dan [name=xac_nhan_url]');await bamDoiTrang(p,'#duong-dan button[type=submit]');
      }finally{await p.close();}
    });
  }finally{await clear().catch(()=>{});bt.off('dialog',accept);}
}
