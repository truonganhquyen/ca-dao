/**
 * Trang Tìm (T-041): kết quả hiện khi gõ (150 ms), tô phần khớp (không phân biệt dấu), nhớ vài lần tìm gần đây trên
 * máy (bộ nhớ trình duyệt, không cookie). Tắt JavaScript: ô tìm vẫn gửi lên máy chủ như thường.
 * Đo lường (T-052): câu tìm không ra, đứng yên 2 giây (không tính lúc đang gõ dở), gửi một lần.
 */
(function () {
	'use strict';
	var o = document.getElementById('cadao-tim-o');
	var kq = document.querySelector('.cadao-tim-kq');
	var ganDay = document.querySelector('.cadao-tim-gan-day');
	if (!o || !kq || !window.fetch) { return; }
	var api = (window.cadaoTim || {}).api;
	var suKien = (window.cadaoTim || {}).suKien;
	var daBao = {};
	var henBao = null;
	var baoKhongRa = function (q) {
		if (!suKien || daBao[q]) { return; }
		henBao = setTimeout(function () {
			if (q !== lanCuoi || daBao[q]) { return; }
			daBao[q] = 1;
			fetch(suKien, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ loai: 'tim_khong_ket_qua', q: q }) }).catch(function () { /* bỏ lần này */ });
		}, 2000);
	};
	var KHOA = 'cadao-tim-gan-day';
	var boDau = function (s) { return s.normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase(); };
	var e = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

	/** Tô các từ của truy vấn trong một chuỗi (so khi đã bỏ dấu, giữ nguyên chữ gốc). */
	var to = function (chu, q) {
		var tu = boDau(q).split(/[^a-z0-9]+/).filter(function (t) { return t.length > 1; });
		if (!tu.length) { return e(chu); }
		var goc = chu.split(/(\s+)/);
		return goc.map(function (p) {
			var k = boDau(p).replace(/[^a-z0-9]/g, '');
			return k && tu.some(function (t) { return k.indexOf(t) === 0; }) ? '<mark>' + e(p) + '</mark>' : e(p);
		}).join('');
	};

	var dung = function (d) {
		if (!d.q.trim()) { kq.innerHTML = ''; return; }
		if (!d.bai.length && !d.muc_tu.length) {
			baoKhongRa(d.q);
			kq.innerHTML = '<div class="cadao-tim-khong"><p><strong>Chưa tìm thấy câu này trong kho.</strong> Bạn thử gõ ít chữ hơn (vài chữ trong câu là đủ), hoặc xem theo <a href="/kho/">kho bài</a>, <a href="/tu-dien/">từ điển</a>.</p></div>';
			return;
		}
		var h = '';
		if (d.muc_tu.length) {
			h += '<h2 class="cadao-tim-nhom">Từ điển</h2><ul class="cadao-tim-ds">' + d.muc_tu.map(function (m) {
				return '<li><a href="' + e(m.url) + '"><strong>' + e(m.ten) + '</strong></a> — ' + e(m.giai) + ' <span class="cadao-tim-phu">(' + m.so_bai + ' bài)</span></li>';
			}).join('') + '</ul>';
		}
		if (d.bai.length) {
			h += '<h2 class="cadao-tim-nhom">' + d.bai.length + ' bài</h2><ul class="cadao-tim-ds">' + d.bai.map(function (b) {
				return '<li><a href="' + e(b.url) + '"><strong>' + to(b.dong_dau || b.tieu_de, d.q) + '</strong></a> <span class="cadao-tim-phu">· ' + e(b.the_loai) + '</span>' + (b.so_lieu || '')
					+ (b.dong_khop ? '<br><span class="cadao-tim-khop">… ' + to(b.dong_khop, d.q) + '</span>' : '')
					+ (b.hieu_nhanh ? '<br><span class="cadao-tim-phu">' + e(b.hieu_nhanh) + '</span>' : '') + '</li>';
			}).join('') + '</ul>';
		}
		kq.innerHTML = h;
	};

	var docGanDay = function () { try { return JSON.parse(localStorage.getItem(KHOA)) || []; } catch (x) { return []; } };
	var nhoGanDay = function (q) {
		var ds = docGanDay().filter(function (x) { return x !== q; });
		ds.unshift(q);
		try { localStorage.setItem(KHOA, JSON.stringify(ds.slice(0, 5))); } catch (x) { /* chế độ riêng tư */ }
	};
	var hienGanDay = function () {
		var ds = docGanDay();
		if (!ds.length || o.value.trim()) { ganDay.hidden = true; return; }
		ganDay.innerHTML = '<span class="cadao-tim-phu">Tìm gần đây:</span> ' + ds.map(function (q) { return '<button type="button">' + e(q) + '</button>'; }).join(' ');
		ganDay.hidden = false;
		ganDay.querySelectorAll('button').forEach(function (n) { n.addEventListener('click', function () { o.value = n.textContent; tim(); o.focus(); }); });
	};

	var hen = null;
	var lanCuoi = '';
	var tim = function () {
		var q = o.value.trim();
		if (q === lanCuoi) { return; }
		clearTimeout(henBao);
		lanCuoi = q;
		hienGanDay();
		var u = new URL(window.location.href);
		if (q) { u.searchParams.set('q', q); } else { u.searchParams.delete('q'); }
		window.history.replaceState(null, '', u);
		if (!q) { kq.innerHTML = ''; return; }
		fetch(api + (api.indexOf('?') < 0 ? '?' : '&') + 'q=' + encodeURIComponent(q))
			.then(function (r) { return r.json(); })
			.then(function (d) { if (d.q === lanCuoi) { dung(d); } })
			.catch(function () { /* mất mạng: giữ kết quả cũ */ });
	};
	o.addEventListener('input', function () { clearTimeout(hen); hen = setTimeout(tim, 150); });
	o.form.addEventListener('submit', function (x) { x.preventDefault(); clearTimeout(hen); tim(); if (o.value.trim()) { nhoGanDay(o.value.trim()); } });
	kq.addEventListener('click', function (x) { if (x.target.closest('a') && o.value.trim()) { nhoGanDay(o.value.trim()); } });
	lanCuoi = o.value.trim();
	if (lanCuoi && kq.querySelector('.cadao-tim-khong')) { baoKhongRa(lanCuoi); } // mở /tim/?q=… (ô tìm ở trang khác) mà không ra
	hienGanDay();
	if (!o.value) { o.focus(); }
}());
