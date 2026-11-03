// Tương tác của trang người đọc V2 (docs/27 mục 6, D-093). Không cookie, không gửi gì ra ngoài; lưu trên máy bằng localStorage.
// Trang vẫn đọc được khi tắt JavaScript: lời, ý nghĩa, chú thích, dị bản là HTML; khối thu gọn dùng <details>.
(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const doc = k => { try { return JSON.parse(localStorage.getItem(k)); } catch { return null; } };
  const ghi = (k, v) => { try { localStorage.setItem(k, typeof v === 'string' ? v : JSON.stringify(v)); return true; } catch { return false; } };
  // Sự kiện đo lường: chỉ phát trong trang; mã đo lường của plugin (nếu có, chỉ cho khách) nhận và gửi
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

  // Lời của bài: các câu (bỏ số dòng), câu lục thụt vào bằng khoảng trắng không ngắt; bài chia đoạn: các khổ cách một dòng trống
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
  // Đã lưu: trên máy người đọc, cùng khoá và dạng {id, ten, url, loai, dong} với MVP — bài lưu trước khi chuyển vẫn còn
  const daLuu = () => { const d = doc('cadao-da-luu'); return Array.isArray(d) ? d : []; };
  const coLuu = id => daLuu().some(x => x && String(x.id) === id);
  $$('[data-action=save]').forEach(b => { if (coLuu(b.dataset.id)) { b.setAttribute('aria-pressed', 'true'); $('.nhan', b).textContent = 'Đã lưu'; } });

  // Bạn là: chỉ đổi thứ tự khối trang chủ, nhớ trên máy (D-087); tắt JavaScript thì thanh này ẩn sẵn
  const THU_TU = { 'Phụ huynh': [4, 7, 5, 6, 8], 'Giáo viên': [5, 4, 7, 6, 8], 'Yêu văn hoá': [5, 7, 4, 6, 8] };
  const vai = v => {
    const goc = $('#role-sections'); if (!goc) return;
    (THU_TU[v] || [4, 5, 6, 7, 8]).forEach(n => { const k = $(`[data-home-block="${n}"]`, goc); if (k) goc.append(k); });
    $$('[data-action=role]').forEach(x => x.setAttribute('aria-pressed', String(x.dataset.value === v)));
  };
  const thanhVai = $('.role-bar'); if (thanhVai) { thanhVai.hidden = false; vai((doc('phongdao-vai') || {}).vai); }

  // Nút chỉ có tác dụng khi có JavaScript ("Mong có câu này", Trình chiếu, Đọc cùng bé): HTML để ẩn
  $$('[data-action=mong-co-cau][hidden], [data-chi-js][hidden]').forEach(b => { b.hidden = false; });

  // Trình chiếu (lớp học) và Đọc cùng bé (đồng dao, như MVP): toàn màn hình, chữ rất lớn; nút, mũi tên để chuyển, Esc thoát.
  // Đọc cùng bé: mỗi lần một dòng, chạm chữ sang dòng, "Vỗ tay theo chữ" tô từng chữ theo nhịp; không âm thanh.
  const dongCo = n => $$('.line', n).map(l => [...l.childNodes].filter(x => !(x.classList && x.classList.contains('ln'))).map(x => x.textContent).join('').replace(/\s+/g, ' ').trim()).filter(Boolean);
  const moChe = (be, trang) => {
    if (!trang.length) return;
    const truoc = document.activeElement, giam = matchMedia('(prefers-reduced-motion: reduce)').matches;
    let i = 0, hen = 0;
    const che = document.createElement('div');
    che.className = 'fullscreen-mode'; che.setAttribute('role', 'dialog'); che.setAttribute('aria-modal', 'true'); che.setAttribute('aria-label', be ? 'Đọc cùng bé' : 'Trình chiếu');
    che.innerHTML = `<div class="mode-top"><strong>Phongdao</strong><div class="row">${be ? '<button type="button" class="btn compact mode-vo" aria-pressed="false">Vỗ tay theo chữ</button>' : ''}<button type="button" class="btn compact" data-action="theme">Sáng / tối</button><button type="button" class="btn compact mode-exit">Thoát</button></div></div><div class="mode-content"></div><div class="mode-bottom"><button type="button" class="btn mode-prev">← Trước</button><span class="mode-progress" aria-live="polite"></span><button type="button" class="btn primary mode-next">Tiếp →</button></div>`;
    const vo = $('.mode-vo', che), nd = $('.mode-content', che);
    const dungVo = () => { clearInterval(hen); hen = 0; if (vo) vo.setAttribute('aria-pressed', 'false'); $$('.beat', che).forEach(s => s.classList.remove('beat', 'moving')); };
    const ve = () => {
      dungVo();
      const t = trang[i];
      nd.innerHTML = be
        ? `<p class="tag">Đọc cùng bé · không âm thanh</p><div class="verse-box"><p class="kid-line" data-fit data-max="kid">${t.dong[0].split(' ').map(w => `<span>${esc(w)}</span>`).join(' ')}</p></div>`
        : `<span class="eyebrow">${esc(t.nhan)}</span><div class="verse-box"><div class="verse${t.luc ? ' lucbat' : ''}" data-fit data-max="mode">${t.dong.map(d => `<span class="line">${esc(d)}</span>`).join('')}</div></div><p class="small muted">Thư viện Văn học Dân gian Việt Nam · ${esc(location.host)}</p>`;
      $('.mode-progress', che).textContent = (i + 1) + ' / ' + trang.length;
      $('.mode-prev', che).disabled = i === 0; $('.mode-next', che).disabled = i >= trang.length - 1;
      fit();
    };
    const di = b => { const m = Math.max(0, Math.min(trang.length - 1, i + b)); if (m !== i) { i = m; ve(); } };
    const phim = e => {
      if (e.key === 'Escape') { dong(); e.preventDefault(); }
      else if (['ArrowRight', 'ArrowDown', 'PageDown'].includes(e.key)) { di(1); e.preventDefault(); }
      else if (['ArrowLeft', 'ArrowUp', 'PageUp'].includes(e.key)) { di(-1); e.preventDefault(); }
      else if (e.key === 'Tab') { // giữ phím Tab trong khung
        const ds = $$('button:not([disabled])', che), dau = ds[0], cuoi = ds[ds.length - 1];
        if (e.shiftKey && document.activeElement === dau) { cuoi.focus(); e.preventDefault(); } else if (!e.shiftKey && document.activeElement === cuoi) { dau.focus(); e.preventDefault(); }
      }
    };
    const dong = () => { dungVo(); document.removeEventListener('keydown', phim, true); che.remove(); document.body.classList.remove('locked'); if (document.fullscreenElement) document.exitFullscreen().catch(() => {}); if (truoc) truoc.focus(); };
    $('.mode-exit', che).onclick = dong; $('.mode-prev', che).onclick = () => di(-1); $('.mode-next', che).onclick = () => di(1);
    if (be) {
      nd.addEventListener('click', () => di(1));
      vo.onclick = () => {
        if (hen) { dungVo(); return; }
        const ds = $$('.kid-line span', che); let k = 0;
        const nhip = () => { if (k >= ds.length) { dungVo(); return; } ds.forEach((s, j) => s.classList.toggle('beat', j === k)); if (!giam) ds[k].classList.add('moving'); k++; };
        vo.setAttribute('aria-pressed', 'true'); nhip(); hen = setInterval(nhip, 700);
      };
    }
    document.addEventListener('keydown', phim, true);
    document.body.append(che); document.body.classList.add('locked'); ve(); $('.mode-exit', che).focus();
    if (che.requestFullscreen) che.requestFullscreen().catch(() => { /* máy không cho toàn màn hình: vẫn chiếu */ });
  };
  // Trang của bài: mỗi lần hai câu (như MVP; câu lục bát đi đôi); đọc cùng bé: mỗi lần một câu
  const trangBai = (moi, be) => {
    const v = $('.verse-card .verse') || $('.read-title .verse'); if (!v) return [];
    const dong = dongCo(v), ra = [], nhan = (($('[data-action=cite]') || {}).dataset || {}).theLoai || '';
    for (let k = 0; k < dong.length; k += moi) ra.push({ dong: dong.slice(k, k + moi), nhan, luc: !be && v.classList.contains('lucbat') });
    return ra;
  };

  // Bài dài (như MVP): đoạn sau dòng 30 thu gọn sau "Đọc toàn bài"; nhớ đoạn đang đọc trên máy, cùng khoá với MVP
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
    const w = e.target.closest('a.word'); if (w) su('chu_giai_mo', { muc: w.textContent });
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
    else if (viec === 'role') { ghi('phongdao-vai', { vai: b.dataset.value }); vai(b.dataset.value); }
    else if (viec === 'role-reset') { ghi('phongdao-vai', {}); vai(null); }
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
        if (ev.target.closest('[data-tao-anh]')) { ev.preventDefault(); d.close(); setTimeout(() => { $('input', anh)?.focus({ preventScroll: true }); anh.scrollIntoView({ block: 'start' }); }, 30); }
      });
    }
    else if (viec === 'aa') {
      const d = hop('Cỡ chữ và sáng tối', `<p>Cỡ chữ lời</p><div class="size-group" role="group" aria-label="Cỡ chữ lời">${[[22, 'Vừa'], [28, 'Lớn'], [34, 'Rất lớn']].map(([n, t]) => `<button type="button" data-action="size" data-size="${n}" aria-pressed="${String(getComputedStyle(document.documentElement).getPropertyValue('--reading-size').trim() === n + 'px')}">${t}</button>`).join('')}</div><p class="small muted">Lời luôn giữ mỗi câu một dòng; trên màn hẹp cỡ lớn tự co cho vừa.</p><div class="actions"><button type="button" class="btn" data-action="theme">Đổi sáng / tối</button>${$('.verse-card') ? '<button type="button" class="btn" data-action="present">Trình chiếu</button>' : ''}</div>`);
      d.querySelector('[data-action=size]')?.focus();
    }
    else if (viec === 'present') { const d = $('dialog[open]'); if (d) d.close(); moChe(false, trangBai(2, false)); }
    else if (viec === 'kids') moChe(true, trangBai(1, true));
    else if (viec === 'present-set') moChe(false, $$('[data-collection] .collection-item').map(m => { const v = $('.verse', m); return v ? { dong: dongCo(v), nhan: (($('.tag', m) || {}).textContent || '').replace(/^\d+\s*·\s*/, ''), luc: v.classList.contains('lucbat') } : null; }).filter(Boolean));
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
})();
