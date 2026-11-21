// Tương tác trang người đọc V2 (docs/27 mục 6). Không cookie, không gửi gì ra ngoài; lưu bằng localStorage.
// Tắt JavaScript vẫn đọc được: nội dung là HTML, khối thu gọn dùng <details>.
(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const doc = k => { try { return JSON.parse(localStorage.getItem(k)); } catch { return null; } };
  const ghi = (k, v) => { try { localStorage.setItem(k, typeof v === 'string' ? v : JSON.stringify(v)); return true; } catch { return false; } };
  // Sự kiện đo lường: phát trong trang; mã đo lường của plugin nhận và gửi
  const su = (loai, du) => document.dispatchEvent(new CustomEvent('phongdao:su-kien', { detail: { loai, ...du } }));
  const fit = () => window.PDfit && window.PDfit();

  let hen;
  const bao = (chu) => {
    let t = $('#phongdao-bao');
    if (!t) { t = document.createElement('div'); t.id = 'phongdao-bao'; t.className = 'toast'; t.setAttribute('role', 'status'); document.body.append(t); }
    const chu_ = $('dialog[open]'); if (chu_ && t.parentElement !== chu_) chu_.append(t); else if (!chu_ && t.parentElement !== document.body) document.body.append(t);
    t.textContent = chu; t.classList.add('show'); clearTimeout(hen); hen = setTimeout(() => t.classList.remove('show'), 4000);
  };
  const chep = async (chu, xong) => {
    try { await navigator.clipboard.writeText(chu); bao(xong); }
    catch { const o = $('dialog[open] textarea'); if (o) { o.focus(); o.select(); } bao('Chưa sao chép được. Hãy chọn chữ rồi sao chép bằng tay.'); }
  };
  const hop = (tieuDe, than) => {
    const d = document.createElement('dialog');
    d.innerHTML = `<h2>${tieuDe}</h2><button type="button" class="dialog-close" aria-label="Đóng"><svg class="icon" aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></svg></button>${than}`;
    const truoc = document.activeElement;
    $('.dialog-close', d).onclick = () => d.close();
    d.addEventListener('click', e => { if (e.target === d) d.close(); });
    d.addEventListener('close', () => { d.remove(); truoc && truoc.focus(); });
    document.body.append(d); d.showModal(); return d;
  };
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);

  // Lời: bỏ số dòng, câu lục thụt bằng khoảng trắng không ngắt, khổ cách một dòng trống
  const loiBai = (khung = document) => {
    const v = $('.verse-card .verse, .today-card .verse', khung) || $('.read-title .verse');
    if (!v) return [];
    const luc = v.classList.contains('lucbat');
    const cau = n => $$('.line', n).map((l, i) => (luc && i % 2 === 0 ? '    ' : '') + [...l.childNodes].filter(x => !(x.classList && x.classList.contains('ln'))).map(x => x.textContent).join('').replace(/\s+/g, ' ').trim());
    const kho = $$('.kho', v);
    return kho.length ? kho.flatMap((k, i) => i ? ['', ...cau(k)] : cau(k)) : cau(v);
  };
  const ngay = new Date();
  const trichDan = (b, kieu) => {
    const t = b.dataset.tieuDe, l = b.dataset.theLoai, u = b.dataset.url;
    if (kieu === 'loi') return loiBai().join('\n');
    if (kieu === 'apa') return `Thư viện Văn học Dân gian Việt Nam. (k.n.). ${t} [${l}]. Truy cập ngày ${ngay.getDate()} tháng ${ngay.getMonth() + 1}, ${ngay.getFullYear()}, từ ${u}`;
    return `“${t}”. ${l}. Thư viện Văn học Dân gian Việt Nam. ${u} (truy cập ${ngay.toLocaleDateString('vi-VN')}).`;
  };

  // Sáng / tối: người đọc bấm thì nhớ trên máy; chưa bấm thì theo cài đặt máy (CSS)
  const dangToi = () => (document.documentElement.dataset.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark';
  const datGiaoDien = t => { document.documentElement.dataset.theme = t; ghi('phongdao-giao-dien', t); };
  // Cỡ chữ lời: trần của fit.js (mỗi câu vẫn một dòng)
  const coChu = n => {
    document.documentElement.style.setProperty('--reading-size', n + 'px');
    $$('[data-action=size]').forEach(x => x.setAttribute('aria-pressed', String(x.dataset.size === String(n))));
    fit();
  };
  const daChon = doc('phongdao-co-chu'); if ([22, 28, 34].includes(daChon)) coChu(daChon);
  // Đã lưu: trên máy người đọc, cùng khoá và dạng với MVP
  const daLuu = () => { const d = doc('cadao-da-luu'); return Array.isArray(d) ? d : []; };
  const coLuu = id => daLuu().some(x => x && String(x.id) === id);
  $$('[data-action=save]').forEach(b => { if (coLuu(b.dataset.id)) { b.setAttribute('aria-pressed', 'true'); $('.nhan', b).textContent = 'Đã lưu'; } });

  // Nghe (D-095): giọng tiếng Việt có sẵn trên máy; không có thì ẩn nút; không tự phát
  const nutNghe = $('[data-action=nghe]');
  let giong = null;
  if (nutNghe && window.speechSynthesis && window.SpeechSynthesisUtterance) {
    const tim = () => { giong = speechSynthesis.getVoices().find(v => /^vi([-_]|$)/i.test(v.lang)) || null; if (giong) nutNghe.hidden = false; };
    tim(); if (!giong) speechSynthesis.addEventListener('voiceschanged', tim);
    addEventListener('pagehide', () => speechSynthesis.cancel());
  }
  const nghe = b => {
    if (speechSynthesis.speaking) { speechSynthesis.cancel(); return; } // bấm lại để dừng
    const xong = () => { b.setAttribute('aria-pressed', 'false'); $('.nhan', b).textContent = 'Nghe'; };
    const dong = loiBai().map(d => d.trim()).filter(Boolean);
    b.setAttribute('aria-pressed', 'true'); $('.nhan', b).textContent = 'Dừng';
    dong.forEach((d, i) => { const u = new SpeechSynthesisUtterance(d); u.voice = giong; u.lang = giong.lang; u.rate = 0.9; if (i === dong.length - 1) u.onend = xong; u.onerror = xong; speechSynthesis.speak(u); });
  };

  // Nút chỉ có tác dụng khi có JavaScript ("Mong có câu này", Trình chiếu, Đọc cùng bé): HTML để ẩn
  $$('[data-action=mong-co-cau][hidden], [data-chi-js][hidden]').forEach(b => { b.hidden = false; });

  // Trình chiếu, Đọc cùng bé: che.js tải khi người đọc bấm lần đầu (không tính vào JavaScript mỗi trang bài)
  const goc = (document.currentScript || {}).src || '';
  const che = viec => {
    if (window.PDche) { window.PDche(viec); return; }
    const t = document.createElement('script');
    t.src = goc.replace(/doc\.js(?=\?|$)/, 'che.js'); t.onload = () => window.PDche && window.PDche(viec);
    t.onerror = () => bao('Chưa mở được, xin thử lại.'); document.head.append(t);
  };

  // Bài dài: đoạn sau dòng 30 thu sau "Đọc toàn bài"; nhớ đoạn đang đọc
  const dai = $('.bai-dai');
  const moDai = () => {
    if (!dai || !dai.classList.contains('thu-gon')) return;
    dai.classList.remove('thu-gon'); $('.doc-toan-bai', dai)?.remove(); fit();
  };
  if (dai) {
    const doan = $$('.doan', dai);
    if ($('.doan[data-sau]', dai)) {
      dai.classList.add('thu-gon');
      const nut = document.createElement('button');
      nut.type = 'button'; nut.className = 'btn doc-toan-bai';
      nut.textContent = 'Đọc toàn bài' + (+dai.dataset.conLai ? ` (còn ${dai.dataset.conLai} dòng)` : '');
      nut.addEventListener('click', () => { const dau = $('.doan[data-sau]', dai); moDai(); if (dau) { dau.tabIndex = -1; dau.focus(); } });
      dai.append(nut);
    }
    const khoa = 'cadao-doc-den-' + (dai.dataset.bai || '');
    const den = Number(doc(khoa)) || 0;
    if (den > 1 && doan[den - 1]) {
      const a = document.createElement('a');
      a.href = '#' + doan[den - 1].id; a.className = 'doc-tiep-doan';
      a.textContent = 'Đọc tiếp từ đoạn ' + den;
      dai.prepend(a);
    }
    if (window.IntersectionObserver) {
      const thay = new IntersectionObserver(ds => ds.forEach(x => { if (x.isIntersecting) ghi(khoa, x.target.dataset.soDoan); }), { rootMargin: '0px 0px -60% 0px' });
      doan.forEach(x => thay.observe(x));
    }
  }

  // Liên kết tới khối thu gọn (#di-ban, #tu-lieu…, đoạn của bài dài) thì mở khối
  const mo = id => { const t = id && document.getElementById(id); if (!t) return false; if (t.closest('.thu-gon')) moDai(); for (let d = t.closest('details'); d; d = d.parentElement.closest('details')) d.open = true; if (t.tagName === 'DETAILS') t.open = true; t.scrollIntoView({ block: 'start' }); return true; };
  if (location.hash) mo(location.hash.slice(1));

  document.addEventListener('click', e => {
    const w = e.target.closest('a.word');
    if (w) {
      su('chu_giai_mo', { muc: w.textContent });
      // Ô chú thích ngay chỗ đọc; tắt JavaScript thì nhảy tới mục chú thích
      const tu = w.textContent.trim().toLowerCase(), m = $$('#chu-thich .term').find(t => ($('dt', t) || {}).textContent.trim().toLowerCase() === tu);
      if (m && window.HTMLDialogElement) { e.preventDefault(); const d = hop(esc(w.textContent.trim()), `<div class="small">${$('dd', m).innerHTML.replace(/<button.*?\/button>/, '')}</div>`); d.classList.add('word-dialog'); return; }
    }
    const a = e.target.closest('a[href^="#"]');
    if (a && a.getAttribute('href').length > 1 && mo(a.getAttribute('href').slice(1))) { e.preventDefault(); const v = a.closest('details.vi-sao'); if (v) v.open = false; return; }
    const b = e.target.closest('[data-action]');
    if (!b) return;
    const viec = b.dataset.action;
    if (viec === 'theme') datGiaoDien(dangToi() ? 'light' : 'dark');
    else if (viec === 'size') { coChu(+b.dataset.size); if (!ghi('phongdao-co-chu', +b.dataset.size)) bao('Không lưu được cỡ chữ trên trình duyệt này.'); }
    else if (viec === 'copy-loi') {
      const tl = b.dataset.theLoai || (($('.work-head .tag-pill') || {}).textContent || '').split('·')[0].trim();
      chep(loiBai(b.closest('.verse-card, .today-card') || document).join('\n') + '\n— ' + (tl ? tl + ' · ' : '') + 'Thư viện Văn học Dân gian Việt Nam\n' + b.dataset.url, 'Đã sao chép lời và đường dẫn bài.');
    }
    else if (viec === 'copy-link') chep(b.dataset.url, 'Đã sao chép liên kết.');
    else if (viec === 'save') {
      const id = b.dataset.id, co = coLuu(id), ds = daLuu().filter(x => x && String(x.id) !== id);
      const moi = co ? ds : [{ id, ten: b.dataset.tieuDe, url: b.dataset.url, loai: b.dataset.theLoai, dong: loiBai(document)[0] || '' }, ...ds].slice(0, 500);
      if (!ghi('cadao-da-luu', moi)) { bao('Trình duyệt đang chặn bộ nhớ nên chưa lưu được.'); return; }
      b.setAttribute('aria-pressed', String(!co)); $('.nhan', b).textContent = co ? 'Lưu' : 'Đã lưu';
      bao(co ? 'Đã bỏ lưu.' : 'Đã lưu trên máy này. Xem ở mục Đã lưu.');
    }
    else if (viec === 'cite') {
      const d = hop('Trích dẫn', `<div class="tabs" role="group" aria-label="Kiểu trích dẫn"><button type="button" class="chip" data-kieu="thuong" aria-pressed="true">Thông dụng</button><button type="button" class="chip" data-kieu="apa" aria-pressed="false">APA 7</button><button type="button" class="chip" data-kieu="loi" aria-pressed="false">Chỉ lời</button></div><label for="trich-dan-chu">Chữ để sao chép</label><textarea id="trich-dan-chu" class="citation-preview" readonly></textarea><div class="actions"><button type="button" class="btn primary" data-chep>Sao chép</button></div>`);
      let kieu = 'thuong';
      const o = $('textarea', d), dat = k => { kieu = k; o.value = trichDan(b, k); $$('[data-kieu]', d).forEach(x => x.setAttribute('aria-pressed', String(x.dataset.kieu === k))); };
      dat('thuong');
      d.addEventListener('click', ev => {
        const k = ev.target.closest('[data-kieu]'); if (k) dat(k.dataset.kieu);
        if (ev.target.closest('[data-chep]')) { chep(o.value, 'Đã sao chép trích dẫn.'); su('trich_dan_sao_chep', { bai: b.dataset.id, kieu: { thuong: 'thong_dung', apa: 'apa', loi: 'chi_loi' }[kieu] }); }
      });
    }
    else if (viec === 'share') {
      const anh = $('#anh-chia-se');
      const d = hop('Chia sẻ bài này', `<div class="share-list"><button type="button" class="btn primary" data-chep>Sao chép liên kết</button>${navigator.share ? '<button type="button" class="btn" data-ung-dung>Chia sẻ qua ứng dụng…</button>' : ''}${anh ? '<a class="btn" href="#anh-chia-se" data-tao-anh>Tạo ảnh để chia sẻ</a>' : ''}</div><label for="chia-se-url">Liên kết</label><textarea id="chia-se-url" readonly>${esc(b.dataset.url)}</textarea>`);
      d.addEventListener('click', async ev => {
        if (ev.target.closest('[data-chep]')) { chep(b.dataset.url, 'Đã sao chép liên kết.'); su('chia_se', { bai: b.dataset.id, kieu: 'sao_chep_lien_ket' }); }
        if (ev.target.closest('[data-ung-dung]')) { try { await navigator.share({ title: b.dataset.tieuDe, url: b.dataset.url }); su('chia_se', { bai: b.dataset.id, kieu: 'web_share' }); } catch (err) { if (err.name !== 'AbortError') bao('Máy này chưa cho chia sẻ qua ứng dụng. Hãy sao chép liên kết.'); } }
        if (ev.target.closest('[data-tao-anh]')) { ev.preventDefault(); d.close(); anh.open = true; setTimeout(() => { $('input', anh)?.focus({ preventScroll: true }); anh.scrollIntoView({ block: 'start' }); }, 30); }
      });
    }
    else if (viec === 'aa') {
      const d = hop('Cỡ chữ và sáng tối', `<p>Cỡ chữ lời</p><div class="size-group" role="group" aria-label="Cỡ chữ lời">${[[22, 'Vừa'], [28, 'Lớn'], [34, 'Rất lớn']].map(([n, t]) => `<button type="button" data-action="size" data-size="${n}" aria-pressed="${String(getComputedStyle(document.documentElement).getPropertyValue('--reading-size').trim() === n + 'px')}">${t}</button>`).join('')}</div><p class="small muted">Lời luôn giữ mỗi câu một dòng; trên màn hẹp cỡ lớn tự co cho vừa.</p><div class="actions"><button type="button" class="btn" data-action="theme">Đổi sáng / tối</button>${$('.verse-card') ? '<button type="button" class="btn" data-action="present">Trình chiếu</button>' : ''}</div>`);
      d.querySelector('[data-action=size]')?.focus();
    }
    else if (['present', 'present-set', 'kids'].includes(viec)) { const d = $('dialog[open]'); if (d) d.close(); che(viec); }
    else if (viec === 'nghe') nghe(b);
    else if (viec === 'mong-co-cau') { su('mong_co_cau', { kho: b.dataset.kho }); b.disabled = true; b.textContent = 'Đã ghi nhận, cảm ơn bạn'; }
    else if (viec === 'download-card') bao('Máy chủ chưa vẽ được ảnh. Hãy chụp màn hình phần xem trước.');
  });

  // Tải ảnh: form gửi máy chủ (chạy cả khi tắt JavaScript); tính vào chia_se, không thêm sự kiện mới
  document.addEventListener('submit', e => { const f = e.target.closest('form.acs-chon'); if (f) su('chia_se', { bai: f.elements.phongdao_anh?.value, kieu: 'tai_anh' }); });

  // Ảnh để chia sẻ: đổi nền (khổ, kèm ý nghĩa đổi bằng CSS, chạy cả khi tắt JavaScript)
  document.addEventListener('change', e => {
    const k = e.target.closest('.acs'); if (!k) return;
    const n = $('input[name^="nen-"]:checked', k); if (n) $('.anh-chia-se', k).dataset.nen = n.value;
    requestAnimationFrame(fit);
  });
  // Hiểu thêm (D-105): ba thẻ, mở sẵn phần nhiều bài nhất hoặc phần được dẫn tới
  const ht = $('[data-hieu-them]');
  if (ht) {
    const nut = $$('[role=tab]', ht), pan = $$('.ht-phan', ht);
    const mo = (i, f) => { nut.forEach((n, j) => { n.setAttribute('aria-selected', i === j); n.tabIndex = i === j ? 0 : -1; pan[j].hidden = i !== j; }); if (f) nut[i].focus(); };
    pan.forEach((p, j) => { p.setAttribute('role', 'tabpanel'); p.setAttribute('aria-labelledby', nut[j].id); $('.ht-ten', p).classList.add('sr-only'); });
    const theo = h => pan.findIndex(p => h && (p.id === h || p.querySelector('#' + CSS.escape(h))));
    const tu = theo(location.hash.slice(1));
    mo(tu < 0 ? +ht.dataset.mo || 0 : tu);
	const moForm = h => { if (h === 'cach-hieu-form') { const d = $('[data-form-cach-hieu]', ht); if (d) d.open = true; } };
	moForm(location.hash.slice(1));
    nut.forEach((n, j) => n.addEventListener('click', () => mo(j)));
    $('[role=tablist]', ht).addEventListener('keydown', e => {
      const i = nut.indexOf(document.activeElement), k = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: nut.length - 1 }[e.key];
      if (i >= 0 && k !== undefined) { e.preventDefault(); mo((k + nut.length) % nut.length, true); }
    });
    document.addEventListener('click', e => { const a = e.target.closest('a[href^="#"]'); const h = a?.getAttribute('href').slice(1); const i = a ? theo(h) : -1; if (i >= 0) { mo(i); moForm(h); } });
  }
  // D-119: chú thích, Ý nghĩa dài thu gọn khi có JavaScript
  const gon = () => {
    $$('.ct-ds .term:not(.mo)').forEach(t => { t.classList.add('gon'); t.scrollHeight - t.clientHeight < 3 ? t.classList.remove('gon') : $('.ct-them', t) || $('dd', t).insertAdjacentHTML('beforeend', '<button class="ct-them" type="button" aria-expanded="false">thêm</button>'); });
    $$('.hieu-gon .y-nghia-chu:not(.gon,.mo)').forEach(p => p.scrollHeight > 14 * parseFloat(getComputedStyle(p).fontSize) && (p.classList.add('gon'), p.insertAdjacentHTML('afterend', '<button class="ct-them y-het" type="button" aria-expanded="false">Đọc hết</button>')));
  };
  document.addEventListener('click', e => { const b = e.target.closest('.ct-them'); if (!b) return; const t = b.closest('.term') || b.previousElementSibling, m = t.classList.toggle('mo'); t.classList.toggle('gon', !m); b.textContent = m ? 'thu gọn' : b.matches('.y-het') ? 'Đọc hết' : 'thêm'; b.setAttribute('aria-expanded', m); });
  if ($('.ct-ds,.hieu-gon')) { gon(); addEventListener('resize', gon); document.addEventListener('toggle', gon, true); }
})();
