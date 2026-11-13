// Trang sửa đầy đủ: cùng form và đường lưu trên máy chủ; chỉ hỗ trợ tìm phần và giữ thao tác rõ ràng.
(() => {
  'use strict';
  const root=document.querySelector('.phongdao-admin-sua');if(!root)return;
  root.classList.add('phongdao-co-js');
  const drafts=()=>window.phongdaoNhapDo?.cacPhan()||[];
  const foldParents=e=>{for(let d=e?.closest('details');d;d=d.parentElement?.closest('details'))d.open=true;};
  const bar=root.querySelector('[data-thanh-luu]');let active=null,sequence=0;
  const refreshBar=()=>{
    if(!active||!bar)return;
    const name=active.dataset.tenPhan||'Phần đang sửa';
    const dirty=window.phongdaoNhapDo?.coDoi(active);
    bar.querySelector('[data-thanh-luu-ten]').textContent=name+(dirty?' · Chưa lưu vào bài':' · Lưu riêng phần này');
  };
  const setActive=f=>{
    if(!f?.matches('form[data-giu-nhap]')||!bar)return;
    if(active!==f){active=f;const buttons=bar.querySelector('[data-thanh-luu-nut]');buttons.replaceChildren();
      f.querySelectorAll('button[type=submit]').forEach(original=>{
        const b=document.createElement('button');b.type='button';b.className=original.className;b.textContent=original.textContent;
        b.addEventListener('click',()=>{foldParents(original);f.requestSubmit(original);});buttons.append(b);
      });
    }
    bar.hidden=false;refreshBar();
  };
  const markNav=()=>{
    root.querySelectorAll('[data-toi-phan]').forEach(a=>{
      const flag=a.querySelector('[data-muc-trang-thai]');if(!flag)return;
      flag.dataset.banDau??=flag.textContent;
      const n=drafts().filter(f=>f.dataset.nav===a.dataset.toiPhan||f.closest('details.phongdao-sua-phan')?.id===a.dataset.toiPhan).length;
      flag.textContent=n?`${n} phần chưa lưu`:flag.dataset.banDau;
    });refreshBar();
  };
  const jump=(id,focus=true)=>{
    const e=document.getElementById(id);if(!e)return;
    foldParents(e);
    e.scrollIntoView({block:'start',behavior:'instant'});
    root.querySelectorAll('[data-toi-phan]').forEach(a=>{if(a.dataset.toiPhan===id)a.setAttribute('aria-current','location');else a.removeAttribute('aria-current');});
    const select=root.querySelector('[data-chon-phan]');if(select&&[...select.options].some(o=>o.value===id))select.value=id;
    const f=e.matches('form')?e:e.querySelector('form[data-giu-nhap]');if(f)setActive(f);else if(bar){active=null;bar.hidden=true;}
    if(focus){const target=e.matches('details')?e.querySelector(':scope>summary'):e;target.tabIndex=-1;target.focus({preventScroll:true});}
  };
  root.querySelectorAll('[data-chi-js]').forEach(e=>e.hidden=false);
  root.addEventListener('click',e=>{
    const link=e.target.closest('[data-toi-phan],[data-mo-phan],[data-mo-hang-muc]');
    if(link){e.preventDefault();const id=link.dataset.toiPhan|| (link.dataset.moPhan?'phan-'+link.dataset.moPhan:link.hash.slice(1));
      const item=document.getElementById(id);if(item?.matches('li')){item.hidden=false;const d=item.querySelector('.phongdao-muc-gon');if(d)d.open=true;}
      jump(id);return;
    }
    const add=e.target.closest('[data-action="them-muc"]');
    if(add){const form=add.closest('form'),template=form?.querySelector('template[data-mau]'),list=form?.querySelector('[data-ds]');if(!template||!list)return;
      const id='m'+Date.now()+(++sequence);list.insertAdjacentHTML('beforeend',template.innerHTML.replaceAll('__i__',id));
      const row=list.lastElementChild;foldParents(row);row.scrollIntoView({block:'center',behavior:'instant'});row.querySelector('textarea,input:not([type=hidden])')?.focus();
      form.dispatchEvent(new Event('change',{bubbles:true}));setActive(form);return;
    }
    const all=e.target.closest('[data-mo-hang],[data-gap-hang]');
    if(all)all.closest('form')?.querySelectorAll('.phongdao-muc-gon').forEach(d=>d.open=all.hasAttribute('data-mo-hang'));
  });
  root.querySelector('[data-chon-phan]')?.addEventListener('change',e=>jump(e.target.value));
  root.addEventListener('focusin',e=>{const f=e.target.closest('form[data-giu-nhap]');if(f)setActive(f);else if(e.target.closest('[data-them-danh-muc]')&&bar){active=null;bar.hidden=true;}});
  root.addEventListener('toggle',e=>{if(e.target.open)setActive(e.target.querySelector('form[data-giu-nhap]'));},true);
  root.addEventListener('invalid',e=>{const row=e.target.closest('li.muc-sua');if(row)row.hidden=false;foldParents(e.target);setActive(e.target.closest('form'));},true);
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
  document.addEventListener('phongdao:nhap-do',markNav);markNav();
  const navSize=()=>root.style.setProperty('--muc-luc-cao',(root.querySelector('.phongdao-muc-luc')?.offsetHeight||100)+'px');navSize();window.addEventListener('resize',navSize);
  // Giữ mọi phần được server/bộ đệm mở. Không gập lại phần khôi phục ở điện thoại.
  if(location.hash)jump(decodeURIComponent(location.hash.slice(1)),false);
  const opened=root.querySelector('.phongdao-sua-chinh>details[open] form[data-giu-nhap]');if(opened)setActive(opened);
  // Đầu vào khôi phục giữ mốc cũ để không ghi đè. Chỉ người đã đối chiếu mới nhận mốc server mới.
  root.querySelectorAll('form[data-phien-server],form[data-moc-server]').forEach(form=>{
    const stamp=form.querySelector('[name=phien_the_tho]');
    const seen=()=>[...form.querySelectorAll('[name="da_thay[]"]')].map(e=>Number(e.value)).sort((a,b)=>a-b);
    const current=form.dataset.mocServer?JSON.parse(form.dataset.mocServer).sort((a,b)=>a-b):null;
    const stale=stamp?stamp.value!==form.dataset.phienServer:JSON.stringify(seen())!==JSON.stringify(current);
    if(!stale)return;
    const p=document.createElement('p');p.textContent='Phân loại đã thay đổi từ lúc nhập. Xem lựa chọn đang lưu ở tiêu đề phần, đối chiếu với phần đang nhập. ';
    const b=document.createElement('button');b.type='button';b.className='button';b.dataset.mocMoi='1';b.textContent='Đã đối chiếu, dùng mốc đang lưu';
    b.addEventListener('click',()=>{if(stamp)stamp.value=form.dataset.phienServer;else{form.querySelectorAll('[name="da_thay[]"]').forEach(e=>e.remove());for(const id of current){const e=document.createElement('input');e.type='hidden';e.name='da_thay[]';e.value=id;form.append(e);}form.dataset.mocDaNhap=form.dataset.mocServer;}p.remove();form.dispatchEvent(new Event('change',{bubbles:true}));});p.append(b);form.prepend(p);foldParents(form);
  });
  const next=document.getElementById('phongdao-sang-tiep');
  if(next){try{next.checked=localStorage.getItem('phongdao-sang-tiep')==='1';}catch(_){}
    next.addEventListener('change',()=>{try{localStorage.setItem('phongdao-sang-tiep',next.checked?'1':'0');}catch(_){}});
    document.addEventListener('submit',e=>{
      const f=e.target;if(!next.checked||!(f instanceof HTMLFormElement)||!f.querySelector('[name=bai_sau]'))return;
      f.querySelectorAll('[name=sang],[name=con_chua_luu]').forEach(e=>e.remove());const others=drafts().filter(x=>x!==f),o=document.createElement('input');o.type='hidden';o.name=others.length?'con_chua_luu':'sang';o.value='1';f.append(o);
    });
  }
  // Phần đối chiếu sách giữ nguyên hành vi đã chốt.
  const book=root.querySelector('#doi-chieu form');
  if(book){const update=()=>{const s=book.querySelector('#dc-sach'),newName=book.querySelector('#dc-ten')?.value.trim();const name=newName||(s&&s.value!=='0'?s.options[s.selectedIndex].text:'');const page=book.querySelector('#dc-trang')?.value.trim();const out=book.querySelector('[data-dc-xem]');if(out)out.textContent=name?`Người đọc sẽ thấy: Lời đã đối chiếu với «${name}»${page?`, tr. ${page}`:''}.`:'Người đọc sẽ thấy: Lời đã đối chiếu với «tên sách», tr. N.';};book.addEventListener('input',update);book.addEventListener('change',update);update();}
  const urlForm=root.querySelector('#duong-dan form');
  if(urlForm){
    const input=urlForm.querySelector('[name=slug]'),out=urlForm.querySelector('[data-url-xem]'),stamp=urlForm.querySelector('[name=phien_duong_dan]');
    const update=()=>{const u=new URL(urlForm.dataset.urlHienTai),slug=input.value.normalize('NFD').replace(/\p{M}/gu,'').replace(/[đĐ]/g,'d').toLowerCase().trim().replace(/[^a-z0-9-]+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');u.pathname=u.pathname.replace(/[^/]+\/?$/,slug+'/');out.textContent=slug?'Dự kiến: '+u.href+' (tên chính thức được kiểm khi lưu).':'Nhập tên cuối đường dẫn.';};
    input.addEventListener('input',update);update();
    if(stamp.value!==urlForm.dataset.phienUrl){const p=document.createElement('p');p.textContent='Đường dẫn đã thay đổi từ lúc nhập. Đối chiếu với URL đang lưu ở trên. ';const b=document.createElement('button');b.type='button';b.className='button';b.textContent='Đã đối chiếu, dùng mốc đang lưu';b.dataset.mocUrlMoi='1';b.addEventListener('click',()=>{stamp.value=urlForm.dataset.phienUrl;p.remove();urlForm.dispatchEvent(new Event('change',{bubbles:true}));});p.append(b);urlForm.prepend(p);foldParents(urlForm);}
  }
  root.querySelectorAll('[data-them-danh-muc]').forEach(box=>{
    const input=box.querySelector('[data-ten-muc]'),button=box.querySelector('[data-tao-muc]'),message=box.querySelector('[data-tao-muc-bao]');
    input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();button.click();}});
    button.addEventListener('click',async()=>{
      const name=input.value.trim();if(!name){message.textContent='Nhập tên mục trước khi thêm.';input.focus();return;}
      button.disabled=true;message.textContent='Đang thêm vào danh sách…';
      let failure='Chưa thêm được mục. Tên vừa nhập vẫn được giữ; kiểm tra kết nối và thử lại.';
      try{const body=new FormData();body.set('action','phongdao_them_muc');body.set('tax',box.dataset.tax);body.set('ten',name);body.set('_ajax_nonce',box.dataset.nonce);
        const response=await fetch(window.ajaxurl,{method:'POST',body,credentials:'same-origin'});const result=await response.json();
        if(!result.success){if(typeof result.data?.thong_bao==='string')failure=result.data.thong_bao;throw Error('catalogue');}
        const data=result.data,list=document.getElementById('pl-'+box.dataset.tax+'-ds');
        if(!data.the_tho&&!list.querySelector(`input[value="${Number(data.id)}"]`)){
          const label=document.createElement('label');label.className='choice';const checkbox=document.createElement('input');checkbox.type='checkbox';checkbox.name='chon[]';checkbox.value=data.id;checkbox.id='pl-'+box.dataset.tax+'-'+data.id;label.htmlFor=checkbox.id;label.append(checkbox,document.createTextNode(' '+data.ten));list.prepend(label);window.phongdaoNhapDo?.themTruong(list.closest('form'));
        }
        list.querySelectorAll('label.choice').forEach(l=>l.hidden=false);const filter=list.closest('form')?.querySelector('[data-loc-muc]');if(filter)filter.value='';
        input.value='';message.textContent=data.the_tho?'Nhãn thể thơ đã có. Sửa lựa chọn trong khối Thể thơ.':(data.trung?'Mục đã có trong danh sách.':'Đã thêm vào danh sách.')+' Chưa gắn vào bài; tự đánh dấu rồi lưu phân loại.';
      }catch(_){message.textContent=failure;}finally{button.disabled=false;}
    });
  });
})();
