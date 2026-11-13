// Chỉ nạp trong trang sửa đầy đủ. Chữ và định dạng cùng một form, cùng lịch sử/kiểm phiên.
(() => {
  'use strict';
  const root=document.querySelector('.phongdao-admin-sua');if(!root||!window.wp?.editor)return;
  document.body.classList.add('pd-editor-admin');
  let serial=0;
  // TinyMCE 4 trong WordPress đặt role=button ở div ngoài một button trình bày.
  // Dùng chính button thật làm điều khiển: chuyển tên/trạng thái/focus, giữ handler của widget.
  const fixed=new WeakSet();
  const fixButtons=()=>document.querySelectorAll('.pd-editor-admin .mce-btn').forEach(widget=>{
    const button=widget.querySelector(':scope>button');if(!button||fixed.has(widget))return;fixed.add(widget);
    const states=['aria-label','aria-pressed','aria-expanded','aria-haspopup','aria-controls','aria-owns'];
    const sync=()=>{
      const name=widget.getAttribute('aria-label')||button.getAttribute('aria-label')||widget.getAttribute('title')||button.textContent.trim();
      if(name&&button.getAttribute('aria-label')!==name)button.setAttribute('aria-label',name);
      for(const key of states){if(widget.hasAttribute(key)){const value=widget.getAttribute(key);if(button.getAttribute(key)!==value)button.setAttribute(key,value);widget.removeAttribute(key);}}
      const disabled=widget.classList.contains('mce-disabled');if(button.disabled!==disabled)button.disabled=disabled;
      if(button.getAttribute('aria-disabled')!==String(disabled))button.setAttribute('aria-disabled',String(disabled));
      widget.removeAttribute('aria-disabled');widget.removeAttribute('role');widget.removeAttribute('tabindex');button.removeAttribute('role');button.tabIndex=0;
    };
    sync();widget.focus=options=>button.focus(options);
    button.addEventListener('keydown',e=>{if(e.key===' '||e.key==='Enter')e.stopPropagation();});
    new MutationObserver(sync).observe(widget,{attributes:true,attributeFilter:[...states,'aria-disabled','role','tabindex','class']});
  });
  new MutationObserver(fixButtons).observe(document.body,{childList:true,subtree:true});
  const plain=html=>{
    const d=new DOMParser().parseFromString(html,'text/html').body;
    d.querySelectorAll('br').forEach(e=>e.replaceWith('\n'));
    d.querySelectorAll('p,div,li').forEach(e=>e.append('\n\n'));
    return d.textContent.replace(/\r\n?/g,'\n').normalize('NFC').replace(/[ \t\u00a0]+/g,' ').replace(/ ?\n ?/g,'\n').replace(/\n{3,}/g,'\n\n').trim();
  };
  const editors=new Set();
  const start=box=>{
    if(!window.tinymce||!window.wp.editor.getDefaultSettings?.()?.tinymce||box.dataset.editorReady||!box.getClientRects().length)return;
    const source=box.querySelector('[data-editor-chu]'),format=box.querySelector('[data-editor-html]'),enabled=box.querySelector('[data-editor-bat]');
    if(!source?.getClientRects().length)return;
    box.dataset.editorReady='1';
    const target=document.createElement('textarea');target.id='pd-editor-'+(++serial);target.setAttribute('aria-label',box.querySelector('label')?.textContent||'Nội dung');
    const escape=s=>{const e=document.createElement('div');e.textContent=s;return e.innerHTML;};
    target.value=(format.value&&plain(format.value)===source.value.trim()?format.value:escape(source.value)).replace(/\n/g,'<br>');
    source.after(target);
    window.wp.editor.initialize(target.id,{quicktags:false,mediaButtons:false,tinymce:{
      wpautop:false,forced_root_block:false,force_br_newlines:true,force_p_newlines:false,
      valid_elements:'strong/b,em/i,u,a[href],span[class],br,p,div',valid_classes:{span:'pd-chu-do pd-chu-xanh pd-chu-tim pd-chu-nhe'},allow_script_urls:false,
      menubar:false,branding:false,statusbar:true,resize:true,height:220,
      toolbar1:'bold italic underline | link unlink | styleselect | removeformat | undo redo',toolbar2:'',
      style_formats:[
        {title:'Màu đỏ',inline:'span',attributes:{class:'pd-chu-do'}},
        {title:'Màu xanh',inline:'span',attributes:{class:'pd-chu-xanh'}},
        {title:'Màu tím',inline:'span',attributes:{class:'pd-chu-tim'}},
        {title:'Chữ nhẹ',inline:'span',attributes:{class:'pd-chu-nhe'}}
      ],
      content_style:'body{font:16px/1.7 Arial,sans-serif;margin:12px;color:#14120f}a{color:#5e3aae}.pd-chu-do{color:#a1201a}.pd-chu-xanh{color:#1f6b4a}.pd-chu-tim{color:#5e3aae}.pd-chu-nhe{color:#5a5652}',
      setup:editor=>{
        const sync=()=>{
          if(!editor.initialized)return;
          const html=editor.getContent(),text=plain(html),rich=/<(?:strong|b|em|i|u|a|span)\b/i.test(html)?html:'';
          if(source.value===text&&format.value===rich)return;
          source.value=text;format.value=rich;source.dispatchEvent(new Event('input',{bubbles:true}));
        };
        editor.on('init',()=>{const focused=document.activeElement===source,label=box.querySelector('label');editor.setContent((format.value&&plain(format.value)===source.value.trim()?format.value:escape(source.value)).replace(/\n/g,'<br>'));source.hidden=true;enabled.value='1';editors.add(sync);editor.iframeElement?.setAttribute('title',label.textContent);label.addEventListener('click',e=>{e.preventDefault();editor.focus();});fixButtons();if(focused)editor.focus();});
        editor.on('input change Undo Redo blur',sync);
				source.addEventListener('input',()=>{if(editor.initialized&&plain(editor.getContent())!==source.value){format.value='';editor.setContent(escape(source.value).replace(/\n/g,'<br>'));}});
        editor.on('focus',()=>source.dispatchEvent(new FocusEvent('focusin',{bubbles:true})));
      }
    }});
  };
  const scan=()=>root.querySelectorAll('[data-editor]').forEach(box=>{if(!box.hasAttribute('data-editor-hang')||box.contains(document.activeElement))start(box);});
  root.addEventListener('toggle',e=>{if(e.target.open)scan();},true);
  root.addEventListener('focusin',scan);
  // Cập nhật textarea trước FormData, giữ nháp và gửi cả khi bấm thanh lưu dính.
  document.addEventListener('submit',()=>editors.forEach(sync=>sync()),true);
  // WordPress in TinyMCE/defaultSettings sau các script footer thông thường.
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',scan,{once:true});else scan();
})();
