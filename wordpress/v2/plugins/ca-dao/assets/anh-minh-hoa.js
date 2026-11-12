/* Trình chọn ảnh chỉ ở wp-admin. Không tự lưu phần bài hoặc chọn quyền sử dụng. */
(() => {
  'use strict';
  document.querySelectorAll('[data-anh-minh-hoa]').forEach(box => {
    const input = box.querySelector('[name="attachment_id"]');
    const preview = box.querySelector('[data-anh-xem]');
    const status = box.querySelector('[data-anh-bao]');
    let frame;
    const show = a => {
      preview.replaceChildren();
      if (a) {
        const figure = document.createElement('figure');
        figure.className = 'phongdao-anh-minh-hoa';
        const img = document.createElement('img');
        img.src = a.sizes?.medium?.url || a.url;
        img.alt = a.alt || '';
        const caption = document.createElement('figcaption');
        caption.textContent = [a.caption && new DOMParser().parseFromString(a.caption, 'text/html').body.textContent, a.phongdaoGhiCong].filter(Boolean).join('\n');
        figure.append(img, caption);
        preview.append(figure);
      } else {
        const p = document.createElement('p'); p.textContent = 'Chưa gắn ảnh.'; preview.append(p);
      }
      box.querySelector('[data-anh-sua]').replaceChildren();
      if (a?.editLink) {
        const link = document.createElement('a');
        link.href = a.editLink; link.target = '_blank'; link.rel = 'noopener';
        link.textContent = 'Sửa mô tả, chú thích và ghi công ảnh trong Thư viện';
        box.querySelector('[data-anh-sua]').append(link);
      }
    };
    box.querySelector('[data-anh-chon]').addEventListener('click', () => {
      if (!window.wp?.media) return;
      if (!frame) {
        frame = wp.media({ title: 'Ảnh minh hoạ của bài', button: { text: 'Chọn ảnh này' }, library: { type: 'image' }, multiple: false });
        frame.on('select', () => {
          const a = frame.state().get('selection').first().toJSON();
          input.value = a.id; show(a);
          status.textContent = 'Đã chọn ảnh, chưa gắn vào bài. Bấm Lưu phần này để gắn.';
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        frame.on('open', () => {
          frame.state().get('selection').reset();
          const id = Number(input.value);
          if (id) frame.state().get('selection').add(wp.media.attachment(id));
        });
      }
      frame.open();
    });
    box.querySelector('[data-anh-bo]').addEventListener('click', () => {
      input.value = '0'; show(null);
      status.textContent = 'Đã chọn bỏ liên kết. Bấm Lưu phần này để bỏ ảnh khỏi bài; tệp vẫn ở Thư viện.';
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
    // Bộ đệm có thể phục hồi ID sau tải lại; luôn dựng ảnh theo ID đó, không để ảnh xem trước cũ.
    const restore = () => {
      const id = Number(input.value);
      if (!id) return show(null);
      if (window.wp?.media) wp.media.attachment(id).fetch().then(a => show(a));
    };
    window.addEventListener('load', restore, { once: true });
  });
})();
