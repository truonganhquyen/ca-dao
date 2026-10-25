/**
 * Đồng dao (T-103): "Đọc cùng bé" — mỗi lần một dòng, chữ thật to; chạm vào chữ (hoặc mũi tên, nút) sang dòng.
 * Nút "Vỗ tay theo chữ" tô lần lượt từng chữ theo nhịp để bé vỗ tay theo. Chỉ có hình, không tự phát âm thanh. Esc để thoát.
 */
(function () {
	'use strict';
	var hop = document.querySelector('.cadao-hanh-dong');
	var nut = hop && hop.querySelector('.cadao-doc-cung-be');
	if (!nut || !window.HTMLDialogElement) { return; }
	var dong = (hop.getAttribute('data-loi') || '').split('\n').map(function (d) { return d.trim(); }).filter(Boolean);
	if (!dong.length) { return; }

	var be = document.createElement('dialog');
	be.className = 'cadao-be';
	be.setAttribute('aria-label', 'Đọc cùng bé');
	be.innerHTML = '<p class="cadao-be-dong" aria-live="polite"></p><p class="cadao-be-so"></p>'
		+ '<div class="cadao-be-nut"><button type="button" data-di="-1">← Dòng trước</button>'
		+ '<button type="button" class="cadao-be-vo" aria-pressed="false">Vỗ tay theo chữ</button>'
		+ '<button type="button" data-di="1">Dòng sau →</button>'
		+ '<button type="button" class="cadao-be-thoat">Thoát</button></div>';
	document.body.appendChild(be);
	var chu = be.querySelector('.cadao-be-dong');
	var nutVo = be.querySelector('.cadao-be-vo');
	var vt = 0;
	var hen = null;

	var dungVo = function () {
		clearInterval(hen);
		hen = null;
		nutVo.setAttribute('aria-pressed', 'false');
		chu.querySelectorAll('.cadao-be-nhip').forEach(function (s) { s.classList.remove('cadao-be-nhip'); });
	};
	var hien = function () {
		dungVo();
		chu.textContent = '';
		dong[vt].split(/\s+/).forEach(function (t, i) {
			if (i) { chu.appendChild(document.createTextNode(' ')); }
			var s = document.createElement('span');
			s.textContent = t;
			chu.appendChild(s);
		});
		be.querySelector('.cadao-be-so').textContent = 'Dòng ' + (vt + 1) + ' / ' + dong.length;
	};
	var di = function (b) {
		var moi = Math.max(0, Math.min(dong.length - 1, vt + b));
		if (moi !== vt) { vt = moi; hien(); }
	};

	nutVo.addEventListener('click', function () {
		if (hen) { dungVo(); return; }
		var ds = chu.querySelectorAll('span');
		var i = 0;
		var nhip = function () {
			if (i >= ds.length) { dungVo(); return; }
			ds.forEach(function (s, k) { s.classList.toggle('cadao-be-nhip', k === i); });
			i++;
		};
		nutVo.setAttribute('aria-pressed', 'true');
		nhip();
		hen = setInterval(nhip, 700);
	});
	be.querySelectorAll('[data-di]').forEach(function (n) { n.addEventListener('click', function () { di(Number(n.getAttribute('data-di'))); }); });
	chu.addEventListener('click', function () { di(1); });
	be.querySelector('.cadao-be-thoat').addEventListener('click', function () { be.close(); });
	be.addEventListener('keydown', function (e) {
		if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === 'PageDown') { di(1); e.preventDefault(); }
		if (e.key === 'ArrowLeft' || e.key === 'ArrowUp' || e.key === 'PageUp') { di(-1); e.preventDefault(); }
	});
	be.addEventListener('close', function () {
		dungVo();
		if (document.fullscreenElement && document.exitFullscreen) { document.exitFullscreen().catch(function () { /* đã thoát */ }); }
		nut.focus();
	});
	nut.addEventListener('click', function () {
		vt = 0;
		hien();
		be.showModal();
		if (be.requestFullscreen) { be.requestFullscreen().catch(function () { /* máy không cho toàn màn hình */ }); }
	});
	nut.hidden = false;
}());
