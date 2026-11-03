// Trình chiếu và Đọc cùng bé của trang người đọc V2 (docs/27 mục 6, 6.4, 7.1; như MVP): doc.js tải tệp này khi người đọc
// bấm lần đầu. Không cookie, không gửi gì ra ngoài, không âm thanh.
(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
  const fit = () => window.PDfit && window.PDfit();
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
  // Tuyển tập: mỗi câu một trang
  const trangBo = () => $$('[data-collection] .collection-item').map(m => { const v = $('.verse', m); return v ? { dong: dongCo(v), nhan: (($('.tag', m) || {}).textContent || '').replace(/^\d+\s*·\s*/, ''), luc: v.classList.contains('lucbat') } : null; }).filter(Boolean);
  window.PDche = viec => moChe('kids' === viec, 'present-set' === viec ? trangBo() : trangBai('kids' === viec ? 1 : 2, 'kids' === viec));
})();
