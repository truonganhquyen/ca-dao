// Màn Sửa tác phẩm (D-121): cùng form và đường lưu trên máy chủ cho từng phần. JavaScript thêm một nút "Lưu thay đổi"
// gửi lần lượt mọi phần đã sửa (mỗi phần một lượt lưu, Lịch sử ghi từng phần), thanh lưu nổi trên điện thoại, lối nhảy
// tới phần. Không có JavaScript: mỗi phần vẫn có nút lưu riêng.
(() => {
  'use strict';
  const root=document.querySelector('.phongdao-admin-sua');if(!root)return;
  root.classList.add('phongdao-co-js');
  const drafts=()=>window.phongdaoNhapDo?.cacPhan()||[];
  const foldParents=e=>{for(let d=e?.closest('details');d;d=d.parentElement?.closest('details'))d.open=true;};
  let sequence=0,busy=false;
  const jump=(id,focus=true)=>{
    const e=document.getElementById(id);if(!e)return;
    foldParents(e);
    e.scrollIntoView({block:'start',behavior:'instant'});
    if(focus){const target=e.matches('details')?e.querySelector(':scope>summary'):e;target.tabIndex=-1;target.focus({preventScroll:true});}
  };
  root.querySelectorAll('[data-chi-js]').forEach(e=>e.hidden=false);
  root.addEventListener('click',e=>{
    const link=e.target.closest('[data-toi-phan],[data-mo-hang-muc]');
    if(link){e.preventDefault();const id=link.dataset.toiPhan||link.hash.slice(1);
      const item=document.getElementById(id);if(item?.matches('li')){item.hidden=false;const d=item.querySelector('.phongdao-muc-gon');if(d)d.open=true;}
      history.replaceState(null,'','#'+id);jump(id);return;
    }
    const add=e.target.closest('[data-action="them-muc"]');
    if(add){const form=add.closest('form'),template=form?.querySelector('template[data-mau]'),list=form?.querySelector('[data-ds]');if(!template||!list)return;
      const id='m'+Date.now()+(++sequence);list.insertAdjacentHTML('beforeend',template.innerHTML.replaceAll('__i__',id));
      const row=list.lastElementChild;foldParents(row);row.scrollIntoView({block:'center',behavior:'instant'});row.querySelector('textarea,input:not([type=hidden])')?.focus();
      form.dispatchEvent(new Event('change',{bubbles:true}));return;
    }
    const all=e.target.closest('[data-mo-hang],[data-gap-hang]');
    if(all)all.closest('form')?.querySelectorAll('.phongdao-muc-gon').forEach(d=>d.open=all.hasAttribute('data-mo-hang'));
  });
  window.addEventListener('hashchange',()=>jump(decodeURIComponent(location.hash.slice(1))));
  root.addEventListener('invalid',e=>{const row=e.target.closest('li.muc-sua');if(row)row.hidden=false;foldParents(e.target);},true);
  const withoutAccents=s=>s.normalize('NFD').replace(/\p{M}/gu,'').replace(/[đĐ]/g,'d').toLowerCase();
  const summarize=row=>{const summary=row?.querySelector('[data-tom-tat-muc]');if(!summary)return;const word=row.querySelector('input[name$="[tu]"]')?.value,text=row.querySelector('textarea')?.value||'';summary.textContent=((word?word+' · ':'')+text).slice(0,120)||'Mục mới · nhập nội dung';};
  root.querySelectorAll('li.muc-sua').forEach(summarize);
  root.addEventListener('input',e=>{
    const filter=e.target.closest('[data-loc-muc]');
    if(filter){const list=document.getElementById(filter.dataset.locMuc),q=withoutAccents(filter.value.trim());list?.querySelectorAll('label.choice').forEach(l=>l.hidden=!!q&&!withoutAccents(l.textContent).includes(q));}
    const rows=e.target.closest('[data-loc-hang]');
    if(rows){const q=withoutAccents(rows.value.trim());rows.closest('form').querySelectorAll('li.muc-sua').forEach(row=>{const text=[...row.querySelectorAll('input:not([type=hidden]),textarea')].map(i=>i.value).join(' ');row.hidden=!!q&&!withoutAccents(text).includes(q);});}
    summarize(e.target.closest('li.muc-sua'));
  });
  if(location.hash)jump(decodeURIComponent(location.hash.slice(1)),false);

  // ---------- Một nút "Lưu thay đổi" ----------
  const box=root.querySelector('[data-hop-dang]');
  const saveButtons=[...root.querySelectorAll('[data-luu-tat-ca]')];
  const bar=root.querySelector('[data-thanh-luu]');
  const report=root.querySelector('[data-luu-bao]');
  const next=document.getElementById('phongdao-sang-tiep');
  const SHARED=['phongdao_sua_phan','phongdao_sua_phan_loai','phongdao_sua_the_tho'];
  const shared=f=>SHARED.includes(f.querySelector('input[name=action]')?.value);
  const partOf=f=>f.querySelector('input[name=phan]')?.value||f.querySelector('input[name=tax]')?.value||'the_tho';
  const titleOf=f=>f.dataset.tenPhan||f.closest('[data-ten-phan]')?.dataset.tenPhan||'Phần đang sửa';
  const anchorOf=f=>f.closest('[id^="phan-"],#the-tho')?.id||'';
  const pending=()=>{const order=[...root.querySelectorAll('form[data-giu-nhap]')];return drafts().filter(shared).sort((a,b)=>order.indexOf(a)-order.indexOf(b));};
  const refresh=()=>{
    const n=pending().length;
    saveButtons.forEach(b=>{b.disabled=busy||!n;b.textContent=busy?'Đang lưu…':'Lưu thay đổi';});
    if(bar){bar.hidden=!n&&!busy;const t=bar.querySelector('[data-thanh-luu-chu]');if(t)t.textContent=n?`${n} phần chưa lưu`:'';}
    root.classList.toggle('pq-co-thanh-luu',!!n);
  };
  const show=(items,text)=>{
    if(!report)return;report.replaceChildren();report.hidden=!text;if(!text)return;
    const p=document.createElement('p');p.textContent=text;report.append(p);
    if(items?.length){const ul=document.createElement('ul');for(const x of items){const li=document.createElement('li'),a=document.createElement('a');a.href='#'+x.neo;a.dataset.toiPhan=x.neo;a.textContent=x.ten;li.append(a,document.createTextNode(': '+x.loi));ul.append(li);}report.append(ul);}
  };
  const saveAll=async()=>{
    const list=pending();if(!list.length||busy)return;
    // Kiểm ô bắt buộc trước khi gửi phần nào (không lưu dở)
    for(const f of list){if(!f.checkValidity()){foldParents(f);f.reportValidity();return;}}
    busy=true;refresh();show(null,`Đang lưu ${list.length} phần…`);
    const ok=[],draft=[],fail=[];
    for(const f of list){
      const submitter=f.querySelector('.phongdao-sua-nut button[type=submit].button-primary')||f.querySelector('button[type=submit]');
      let data;try{data=new FormData(f,submitter);}catch(_){data=new FormData(f);if(submitter?.name)data.set(submitter.name,submitter.value);}
      data.set('tra_json','1');data.delete('sang');data.delete('con_chua_luu');
      try{
        const r=await fetch(f.getAttribute('action'),{method:'POST',body:data,credentials:'same-origin'});
        const j=await r.json();
        if(j&&j.ok){(j.ket_qua==='nhap'?draft:ok).push(partOf(f));window.phongdaoNhapDo?.daLuu(f);}
        else fail.push({phan:partOf(f),ten:titleOf(f),neo:anchorOf(f),loi:(j&&j.loi)||'Chưa lưu được.'});
      }catch(_){fail.push({phan:partOf(f),ten:titleOf(f),neo:anchorOf(f),loi:'Chưa lưu được (mất kết nối hoặc phiên đăng nhập đã hết). Chữ đang nhập vẫn giữ.'});}
    }
    const kept=window.phongdaoNhapDo?.giuDuoc?.()!==false;
    if(fail.length&&!kept){busy=false;refresh();show(fail,`Chưa lưu được ${fail.length} phần. Bộ nhớ tab không dùng được nên vẫn ở lại trang; sửa rồi bấm Lưu thay đổi lần nữa.`);return;}
    window.phongdaoNhapDo?.roiTrang?.();
    if(!fail.length&&next?.checked&&box?.dataset.urlSau){const u=new URL(box.dataset.urlSau,location.href);u.searchParams.set('ket_qua','da_luu_sang');u.searchParams.set('da_luu_bai',box.dataset.bai);location.assign(u.href);return;}
    const u=new URL(box?.dataset.urlBai||location.href,location.href);u.hash='';
    ['ket_qua','cac_phan','nhap','chua','phan','phan_loai','nhap_do','con_chua_luu','da_luu_bai','da_luu_phan'].forEach(k=>u.searchParams.delete(k));
    u.searchParams.set('ket_qua',fail.length?'luu_chua_het':'da_luu_nhieu');
    if(ok.length)u.searchParams.set('cac_phan',ok.join(','));if(draft.length)u.searchParams.set('nhap',draft.join(','));
    if(fail.length){u.searchParams.set('chua',fail.map(x=>x.phan).join(','));try{sessionStorage.setItem('phongdao-loi-luu:'+box?.dataset.bai,JSON.stringify({time:Date.now(),ds:fail}));}catch(_){}}
    location.assign(u.href);
  };
  saveButtons.forEach(b=>b.addEventListener('click',saveAll));
  // Ctrl/⌘ + S: lưu mọi thay đổi
  document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&!e.altKey&&e.key.toLowerCase()==='s'&&pending().length){e.preventDefault();saveAll();}});
  document.addEventListener('phongdao:nhap-do',refresh);refresh();
  // Sau "Lưu thay đổi" còn phần chưa lưu được: lời báo ở khung Đăng và ngay trong từng phần (chữ đã được giữ lại trên tab)
  try{
    const k='phongdao-loi-luu:'+box?.dataset.bai,d=JSON.parse(sessionStorage.getItem(k)||'null');sessionStorage.removeItem(k);
    if(d&&Date.now()-d.time<5*60*1000&&Array.isArray(d.ds)&&new URLSearchParams(location.search).get('ket_qua')==='luu_chua_het'){
      show(d.ds,'Chưa lưu được:');
      for(const x of d.ds){const part=x.neo&&document.getElementById(x.neo);if(!part)continue;foldParents(part);if(part.matches('details'))part.open=true;const p=document.createElement('p');p.className='pq-loi-phan';p.setAttribute('role','alert');p.textContent='Chưa lưu được: '+x.loi;(part.querySelector('form')||part).prepend(p);}
      if(d.ds[0]?.neo)jump(d.ds[0].neo,false);
    }
  }catch(_){}

  // Đến từ danh sách đã lọc: lưu một phần riêng (không JavaScript, hay "Lưu nháp vào Hộp việc") chỉ tự sang bài kế khi hết phần chưa lưu
  if(next){try{next.checked=localStorage.getItem('phongdao-sang-tiep')==='1';}catch(_){}
    next.addEventListener('change',()=>{try{localStorage.setItem('phongdao-sang-tiep',next.checked?'1':'0');}catch(_){}});
    document.addEventListener('submit',e=>{
      const f=e.target;if(!next.checked||!(f instanceof HTMLFormElement)||!f.querySelector('[name=bai_sau]'))return;
      f.querySelectorAll('[name=sang],[name=con_chua_luu]').forEach(e=>e.remove());const others=drafts().filter(x=>x!==f),o=document.createElement('input');o.type='hidden';o.name=others.length?'con_chua_luu':'sang';o.value='1';f.append(o);
    });
  }

  // Đầu vào khôi phục giữ mốc cũ để không ghi đè. Chỉ người đã đối chiếu mới nhận mốc server mới.
  root.querySelectorAll('form[data-phien-server],form[data-moc-server]').forEach(form=>{
    const stamp=form.querySelector('[name=phien_the_tho]');
    const seen=()=>[...form.querySelectorAll('[name="da_thay[]"]')].map(e=>Number(e.value)).sort((a,b)=>a-b);
    const current=form.dataset.mocServer?JSON.parse(form.dataset.mocServer).sort((a,b)=>a-b):null;
    const stale=stamp?stamp.value!==form.dataset.phienServer:JSON.stringify(seen())!==JSON.stringify(current);
    if(!stale)return;
    const p=document.createElement('p');p.className='pq-loi-phan';p.textContent='Phân loại đã thay đổi từ lúc nhập. Xem lựa chọn đang lưu, đối chiếu với phần đang nhập. ';
    const b=document.createElement('button');b.type='button';b.className='button';b.dataset.mocMoi='1';b.textContent='Đã đối chiếu, dùng mốc đang lưu';
    b.addEventListener('click',()=>{if(stamp)stamp.value=form.dataset.phienServer;else{form.querySelectorAll('[name="da_thay[]"]').forEach(e=>e.remove());for(const id of current){const e=document.createElement('input');e.type='hidden';e.name='da_thay[]';e.value=id;form.append(e);}form.dataset.mocDaNhap=form.dataset.mocServer;}p.remove();form.dispatchEvent(new Event('change',{bubbles:true}));});p.append(b);form.prepend(p);foldParents(form);
  });
  // Đối chiếu sách: dòng "Người đọc sẽ thấy"
  const book=root.querySelector('#doi-chieu form');
  if(book){const update=()=>{const s=book.querySelector('#dc-sach'),newName=book.querySelector('#dc-ten')?.value.trim();const name=newName||(s&&s.value!=='0'?s.options[s.selectedIndex].text:'');const page=book.querySelector('#dc-trang')?.value.trim();const out=book.querySelector('[data-dc-xem]');if(out)out.textContent=name?`Người đọc sẽ thấy: Lời đã đối chiếu với «${name}»${page?`, tr. ${page}`:''}.`:'Người đọc sẽ thấy: Lời đã đối chiếu với «tên sách», tr. N.';};book.addEventListener('input',update);book.addEventListener('change',update);update();}
  // Đường dẫn: xem trước tên mới, đối chiếu mốc
  const urlForm=root.querySelector('#duong-dan form');
  if(urlForm){
    const input=urlForm.querySelector('[name=slug]'),out=urlForm.querySelector('[data-url-xem]'),stamp=urlForm.querySelector('[name=phien_duong_dan]');
    const update=()=>{const u=new URL(urlForm.dataset.urlHienTai),slug=input.value.normalize('NFD').replace(/\p{M}/gu,'').replace(/[đĐ]/g,'d').toLowerCase().trim().replace(/[^a-z0-9-]+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');u.pathname=u.pathname.replace(/[^/]+\/?$/,slug+'/');out.textContent=slug?'Dự kiến: '+u.href+' (tên chính thức được kiểm khi lưu).':'Nhập tên cuối đường dẫn.';};
    input.addEventListener('input',update);update();
    if(stamp.value!==urlForm.dataset.phienUrl){const p=document.createElement('p');p.textContent='Đường dẫn đã thay đổi từ lúc nhập. Đối chiếu với URL đang lưu ở trên. ';const b=document.createElement('button');b.type='button';b.className='button';b.textContent='Đã đối chiếu, dùng mốc đang lưu';b.dataset.mocUrlMoi='1';b.addEventListener('click',()=>{stamp.value=urlForm.dataset.phienUrl;p.remove();urlForm.dispatchEvent(new Event('change',{bubbles:true}));});p.append(b);urlForm.prepend(p);foldParents(urlForm);}
  }
  // Thêm mục mới vào danh mục (không tự gắn vào bài)
  root.querySelectorAll('[data-them-danh-muc]').forEach(hop=>{
    const input=hop.querySelector('[data-ten-muc]'),button=hop.querySelector('[data-tao-muc]'),message=hop.querySelector('[data-tao-muc-bao]');
    input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();button.click();}});
    button.addEventListener('click',async()=>{
      const name=input.value.trim();if(!name){message.textContent='Nhập tên mục trước khi thêm.';input.focus();return;}
      button.disabled=true;message.textContent='Đang thêm vào danh sách…';
      let failure='Chưa thêm được mục. Tên vừa nhập vẫn được giữ; kiểm tra kết nối và thử lại.';
      try{const body=new FormData();body.set('action','phongdao_them_muc');body.set('tax',hop.dataset.tax);body.set('ten',name);body.set('_ajax_nonce',hop.dataset.nonce);
        const response=await fetch(window.ajaxurl,{method:'POST',body,credentials:'same-origin'});const result=await response.json();
        if(!result.success){if(typeof result.data?.thong_bao==='string')failure=result.data.thong_bao;throw Error('catalogue');}
        const data=result.data,all=document.getElementById('pl-'+hop.dataset.tax+'-ds'),list=all?.querySelector('[data-pl-them]')||all;
        if(!data.the_tho&&list&&!all.querySelector(`input[value="${Number(data.id)}"]`)){
          const label=document.createElement('label');label.className='choice';const checkbox=document.createElement('input');checkbox.type='checkbox';checkbox.name='chon[]';checkbox.value=data.id;checkbox.id='pl-'+hop.dataset.tax+'-'+data.id;label.htmlFor=checkbox.id;label.append(checkbox,document.createTextNode(' '+data.ten));list.prepend(label);window.phongdaoNhapDo?.themTruong(list.closest('form'));
        }
        list?.querySelectorAll('label.choice').forEach(l=>l.hidden=false);const filter=list?.closest('form')?.querySelector('[data-loc-muc]');if(filter)filter.value='';
        input.value='';message.textContent=data.the_tho?'Nhãn thể thơ đã có. Sửa lựa chọn trong khối Thể thơ.':(data.trung?'Mục đã có trong danh sách.':'Đã thêm vào danh sách.')+' Chưa gắn vào bài; tự đánh dấu rồi bấm Lưu thay đổi.';
      }catch(_){message.textContent=failure;}finally{button.disabled=false;}
    });
  });
})();
