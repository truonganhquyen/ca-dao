// Tương tác của trang người đọc V2 (docs/27 mục 6, D-093). Không cookie, không gửi gì ra ngoài; lưu trên máy bằng localStorage.
// Trang vẫn đọc được khi tắt JavaScript: lời, ý nghĩa, chú thích, dị bản là HTML; khối thu gọn dùng <details>.
(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const doc = k => { try { return JSON.parse(localStorage.getItem(k)); } catch { return null; } };
  const ghi = (k, v) => { try { localStorage.setItem(k, typeof v === 'string' ? v : JSON.stringify(v)); return true; } catch { return false; } };
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

  // Lời của bài: các câu (bỏ số dòng), câu lục thụt vào bằng khoảng trắng không ngắt
  const loiBai = (khung = document) => {
    const v = $('.verse-card .verse, .today-card .verse', khung) || $('.read-title .verse');
    if (!v) return [];
    const luc = v.classList.contains('lucbat');
    return $$('.line', v).map((l, i) => (luc && i % 2 === 0 ? '    ' : '') + [...l.childNodes].filter(n => !(n.classList && n.classList.contains('ln'))).map(n => n.textContent).join('').replace(/\s+/g, ' ').trim());
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

  // Liên kết tới khối thu gọn (#di-ban, #tu-lieu…) thì mở khối
  const mo = id => { const t = id && document.getElementById(id); if (!t) return false; for (let d = t.closest('details'); d; d = d.parentElement.closest('details')) d.open = true; if (t.tagName === 'DETAILS') t.open = true; t.scrollIntoView({ block: 'start' }); return true; };
  if (location.hash) mo(location.hash.slice(1));

  document.addEventListener('click', e => {
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
      const o = $('textarea', d), dat = k => { o.value = trichDan(b, k); $$('[data-kieu]', d).forEach(x => x.setAttribute('aria-pressed', String(x.dataset.kieu === k))); };
      dat('thuong');
      d.addEventListener('click', ev => { const k = ev.target.closest('[data-kieu]'); if (k) dat(k.dataset.kieu); if (ev.target.closest('[data-chep]')) chep(o.value, 'Đã sao chép trích dẫn.'); });
    }
    else if (viec === 'share') {
      const anh = $('#anh-chia-se');
      const d = hop('Chia sẻ bài này', `<div class="share-list"><button type="button" class="btn primary" data-chep>Sao chép liên kết</button>${navigator.share ? '<button type="button" class="btn" data-ung-dung>Chia sẻ qua ứng dụng…</button>' : ''}${anh ? '<a class="btn" href="#anh-chia-se" data-tao-anh>Tạo ảnh để chia sẻ</a>' : ''}</div><label for="chia-se-url">Liên kết</label><textarea id="chia-se-url" readonly>${esc(b.dataset.url)}</textarea>`);
      d.addEventListener('click', async ev => {
        if (ev.target.closest('[data-chep]')) chep(b.dataset.url, 'Đã sao chép liên kết.');
        if (ev.target.closest('[data-ung-dung]')) { try { await navigator.share({ title: b.dataset.tieuDe, url: b.dataset.url }); } catch (err) { if (err.name !== 'AbortError') bao('Máy này chưa cho chia sẻ qua ứng dụng. Hãy sao chép liên kết.'); } }
        if (ev.target.closest('[data-tao-anh]')) { ev.preventDefault(); d.close(); setTimeout(() => { $('input', anh)?.focus({ preventScroll: true }); anh.scrollIntoView({ block: 'start' }); }, 30); }
      });
    }
    else if (viec === 'aa') {
      const d = hop('Cỡ chữ và sáng tối', `<p>Cỡ chữ lời</p><div class="size-group" role="group" aria-label="Cỡ chữ lời">${[[22, 'Vừa'], [28, 'Lớn'], [34, 'Rất lớn']].map(([n, t]) => `<button type="button" data-action="size" data-size="${n}" aria-pressed="${String(getComputedStyle(document.documentElement).getPropertyValue('--reading-size').trim() === n + 'px')}">${t}</button>`).join('')}</div><p class="small muted">Lời luôn giữ mỗi câu một dòng; trên màn hẹp cỡ lớn tự co cho vừa.</p><div class="actions"><button type="button" class="btn" data-action="theme">Đổi sáng / tối</button></div>`);
      d.querySelector('[data-action=size]')?.focus();
    }
    else if (viec === 'download-card') bao('Tải ảnh sẽ có khi máy chủ sinh ảnh PNG theo khổ và nền đã chọn.');
  });

  // Ảnh để chia sẻ: đổi nền (khổ, kèm ý nghĩa đổi bằng CSS, chạy cả khi tắt JavaScript)
  document.addEventListener('change', e => {
    const k = e.target.closest('.acs'); if (!k) return;
    const n = $('input[name^="nen-"]:checked', k); if (n) $('.anh-chia-se', k).dataset.nen = n.value;
    requestAnimationFrame(fit);
  });
})();
