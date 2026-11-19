/* Chỉ trang Góp: tra bài công khai, không lưu chữ, không ngắt đường gửi PHP. */
(() => {
  const f = document.querySelector('.gop-loi-form');
  if (!f) return;
  const words = f.querySelector('[name=noi_dung]'), box = document.querySelector('#gop-gan-giong');
  const name = f.querySelector('[name=ten]'), credit = f.querySelector('.gop-hien-ten');
  const setName = () => { credit.hidden = !name.value.trim(); if (credit.hidden) credit.querySelector('input').checked = false; };
  name.addEventListener('input', setName); setName();
  let timer, seq = 0, abort;
  words.addEventListener('input', () => {
    clearTimeout(timer); seq++; abort?.abort(); box.replaceChildren();
    const text = words.value.trim(), version = seq;
    if (text.length < 3) return;
    timer = setTimeout(async () => {
      abort = new AbortController();
      try {
        const r = await fetch(f.dataset.ganGiong, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({loi:text}), signal:abort.signal, credentials:'omit', cache:'no-store'});
        if (!r.ok) throw new Error('lookup');
        const data = await r.json(); if (version !== seq) return;
        if (!data.bai?.length) return;
        const d = document.createElement('details'), s = document.createElement('summary');
        const exact = data.bai.some(b => b.khop);
        s.textContent = exact ? 'Câu này đã có trên Phong Dao — xem bài' : 'Có bài gần giống — xem'; d.append(s);
        const p = document.createElement('p'); p.textContent = exact ? 'Cảm ơn bạn vẫn nhớ và chia sẻ! Bạn có lời khác hoặc biết thêm nguồn không? Bạn vẫn có thể gửi để người biên soạn xem.' : 'Bạn xem thử nhé; nếu lời của bạn có điểm khác, cứ gửi để người biên soạn đối chiếu.'; d.append(p);
        const ul = document.createElement('ul');
        for (const b of data.bai.slice(0,2)) {
          const url = new URL(b.url, location.href); if (url.origin !== location.origin) continue;
          const a = document.createElement('a'); a.href = url.href; a.target = '_blank'; a.rel = 'noopener'; a.textContent = b.ten;
          const li = document.createElement('li'); li.append(a); ul.append(li);
        }
        d.append(ul); box.append(d);
      } catch (e) {
        if (e.name === 'AbortError' || version !== seq) return;
        const p = document.createElement('p'); p.className = 'small muted'; p.textContent = 'Chưa tra được bài gần giống. Bạn vẫn có thể gửi; người biên soạn sẽ đối chiếu.'; box.append(p);
      }
    }, 900);
  });
})();
