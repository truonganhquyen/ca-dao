/**
 * Trang bài Ca dao – Tục ngữ: trích dẫn, chia sẻ (T-042), đếm lượt đọc (T-081, D-064), Đã lưu (T-051), đo lường (T-052).
 * Không thư viện, không cookie. Tắt JavaScript thì trang vẫn đọc được, hộp Trích dẫn vẫn hiện chữ để chọn tay.
 */
(function () {
	'use strict';
	var cauHinh = window.cadaoCauHinh || {};
	var hai = function (n) { return (n < 10 ? '0' : '') + n; };
	var homNay = new Date();

	function saoChep(chu) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(chu);
		}
		return new Promise(function (ok, hong) {
			var o = document.createElement('textarea');
			o.value = chu;
			o.setAttribute('readonly', '');
			o.style.position = 'fixed';
			o.style.opacity = '0';
			document.body.appendChild(o);
			o.select();
			var duoc = false;
			try { duoc = document.execCommand('copy'); } catch (e) { /* trình duyệt cũ */ }
			document.body.removeChild(o);
			if (duoc) { ok(); } else { hong(); }
		});
	}

	// Đo lường (T-052): mỗi sự kiện gửi một lần mỗi lần xem trang; không cookie, không định danh
	var daGui = {};
	var suKien = function (loai, du) {
		if (!cauHinh.suKien || !window.fetch) { return; }
		du.loai = loai;
		var k = JSON.stringify(du);
		if (daGui[k]) { return; }
		daGui[k] = 1;
		fetch(cauHinh.suKien, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json' }, body: k }).catch(function () { /* bỏ lần này */ });
	};

	// ---------- Trích dẫn, chia sẻ ----------
	var hop = document.querySelector('.cadao-hanh-dong');
	if (hop) {
		var bao = hop.querySelector('.cadao-bao');
		var noiBao = function (chu) { bao.textContent = ''; setTimeout(function () { bao.textContent = chu; }, 50); };
		document.querySelectorAll('.cadao-can-js').forEach(function (o) { o.hidden = false; }); // nút chỉ dùng được khi có JavaScript (khối trích dẫn, góp ý)

		// Ngày truy cập theo ngày bấm (trang có thể được lưu đệm từ hôm trước)
		var ngay = hai(homNay.getDate()) + '/' + hai(homNay.getMonth() + 1) + '/' + homNay.getFullYear();
		hop.querySelectorAll('.cadao-ngay').forEach(function (o) { o.textContent = ngay; });

		var loi = hop.getAttribute('data-loi');
		var noiDung = hop.closest('.wp-block-post-content') || document;

		// Phần chép từ nguồn (đáp án, chú thích) kèm dòng "Nguồn: …" của nó (D-057)
		var phanKem = function () {
			var ra = [];
			noiDung.querySelectorAll('.wp-block-details').forEach(function (d) {
				var p = Array.prototype.map.call(d.querySelectorAll('p'), function (x) { return x.textContent.trim(); }).join('\n');
				if (p) { ra.push('Đáp án: ' + p); }
			});
			var dsChu = noiDung.querySelectorAll('.cadao-chu-thich li');
			if (dsChu.length) {
				ra.push('Chú thích:\n' + Array.prototype.map.call(dsChu, function (li) { return '- ' + li.textContent.trim(); }).join('\n'));
			}
			var nguon = noiDung.querySelector('.cadao-chu-thich-nguon a');
			if (ra.length && nguon) {
				ra.push('Nguồn: ' + nguon.textContent.replace('↗', '').trim() + ' (' + nguon.href + ')');
			}
			return ra.join('\n');
		};

		hop.querySelectorAll('.cadao-nut button').forEach(function (nut) {
			nut.addEventListener('click', function () {
				var chu = loi;
				var kemNguon = nut.getAttribute('data-viec') === 'kem_nguon';
				var kem = hop.querySelector('.cadao-kem');
				if (kem && kem.checked) {
					var them = phanKem();
					if (them) { chu += '\n\n' + them; }
				}
				if (kemNguon) {
					var chon = hop.querySelector('input[name="cadao-kieu"]:checked');
					var mau = hop.querySelector('.cadao-mau[data-kieu="' + (chon ? chon.value : 'thong_dung') + '"] .cadao-mau-chu');
					chu += '\n\n' + mau.textContent;
				}
				saoChep(chu).then(function () {
					noiBao(kemNguon ? 'Đã sao chép. Cảm ơn bạn đã ghi nguồn!' : 'Đã sao chép lời.');
					var kieu = hop.querySelector('input[name="cadao-kieu"]:checked');
					suKien('trich_dan_sao_chep', { bai: hop.getAttribute('data-id'), kieu: kemNguon ? (kieu ? kieu.value : 'thong_dung') : 'chi_loi' });
				}, function () {
					noiBao('Không sao chép được. Bạn chọn và sao chép chữ ở trên nhé.');
				});
			});
		});

		var chiaSe = hop.querySelector('.cadao-chia-se');
		chiaSe.addEventListener('click', function () {
			var url = hop.getAttribute('data-url');
			var ten = hop.getAttribute('data-ten');
			if (navigator.share) {
				navigator.share({
					title: ten,
					text: loi + '\n— ' + ten + ' (' + hop.getAttribute('data-the-loai') + ') · ' + (cauHinh.duAn || ''),
					url: url
				}).catch(function () { /* người đọc đóng hộp chia sẻ */ });
				return;
			}
			saoChep(url).then(function () {
				noiBao('Đã sao chép liên kết. Dán vào Zalo, Facebook… để chia sẻ.');
			}, function () {
				noiBao('Không sao chép được. Liên kết: ' + url);
			});
		});
	}

	// ---------- Công cụ đọc (T-085): cỡ chữ, sáng / tối, trình chiếu, nghe ----------
	var goc = document.documentElement;
	var luu = function (k, v) { try { if (v) { localStorage.setItem(k, v); } else { localStorage.removeItem(k); } } catch (e) { /* chế độ riêng tư */ } };
	var nutCo = hop && hop.querySelector('.cadao-co-chu');
	if (nutCo) {
		var TEN_CO = ['vừa', 'lớn', 'rất lớn'];
		var datCo = function (c) { goc.setAttribute('data-co-chu', String(c)); nutCo.setAttribute('aria-label', 'Cỡ chữ: ' + TEN_CO[c - 1] + '. Bấm để đổi'); };
		datCo(Number(goc.getAttribute('data-co-chu')) || 1);
		nutCo.addEventListener('click', function () {
			var c = (Number(goc.getAttribute('data-co-chu')) || 1) % 3 + 1;
			datCo(c);
			luu('cadao-co-chu', c > 1 ? String(c) : '');
		});
	}
	var nutSt = hop && hop.querySelector('.cadao-sang-toi');
	if (nutSt) {
		var dangToi = function () { var d = goc.getAttribute('data-che-do'); return d ? d === 'toi' : window.matchMedia('(prefers-color-scheme: dark)').matches; };
		var nhanSt = function () { nutSt.setAttribute('aria-label', dangToi() ? 'Đang tối. Bấm để chuyển sang sáng' : 'Đang sáng. Bấm để chuyển sang tối'); };
		nhanSt();
		nutSt.addEventListener('click', function () {
			var d = dangToi() ? 'sang' : 'toi';
			goc.setAttribute('data-che-do', d);
			luu('cadao-che-do', d);
			nhanSt();
		});
	}
	var dongLoi = hop ? (hop.getAttribute('data-loi') || '').split('\n').filter(function (d) { return d.trim(); }) : [];

	// ---------- Đã lưu (T-051): nhớ bài trong bộ nhớ trình duyệt, không gửi đi đâu; bị chặn thì không hiện nút ----------
	var nutLuu = hop && hop.querySelector('.cadao-luu');
	var docLuu = function () { var d = JSON.parse(localStorage.getItem('cadao-da-luu') || '[]'); return Array.isArray(d) ? d : []; };
	var dsLuu = null;
	try { dsLuu = docLuu(); } catch (e) { /* bị chặn */ }
	if (nutLuu && dsLuu) {
		var idBai = Number(hop.getAttribute('data-id'));
		var xemLuu = hop.querySelector('.cadao-xem-da-luu');
		var nhanLuu = function (ds) {
			var co = ds.some(function (x) { return x.id === idBai; });
			nutLuu.setAttribute('aria-pressed', co ? 'true' : 'false');
			nutLuu.querySelector('.cadao-sao').textContent = co ? '★' : '☆';
			if (xemLuu) { xemLuu.hidden = !ds.length; xemLuu.textContent = 'Bài đã lưu (' + ds.length + ')'; }
		};
		nhanLuu(dsLuu);
		nutLuu.hidden = false;
		nutLuu.addEventListener('click', function () {
			var ds = [];
			try { ds = docLuu(); } catch (e) { /* bị chặn giữa chừng */ }
			var bo = ds.some(function (x) { return x.id === idBai; });
			ds = ds.filter(function (x) { return x.id !== idBai; });
			if (!bo) { ds.unshift({ id: idBai, ten: hop.getAttribute('data-ten'), url: hop.getAttribute('data-url'), loai: hop.getAttribute('data-the-loai'), dong: dongLoi[0] || '' }); }
			try { localStorage.setItem('cadao-da-luu', JSON.stringify(ds.slice(0, 500))); } catch (e) { noiBao('Trình duyệt không cho lưu.'); return; }
			nhanLuu(ds);
			noiBao(bo ? 'Đã bỏ lưu bài này.' : 'Đã lưu bài này trên máy của bạn.');
		});
	}
	var nutTc = hop && hop.querySelector('.cadao-trinh-chieu');
	if (nutTc && dongLoi.length && window.HTMLDialogElement) {
		// Trình chiếu cho lớp học: mỗi lần hai dòng, chữ thật lớn; mũi tên / nút để chuyển, Esc để thoát
		var cap = [];
		for (var k = 0; k < dongLoi.length; k += 2) { cap.push(dongLoi.slice(k, k + 2)); }
		var tc = document.createElement('dialog');
		tc.className = 'cadao-tc';
		tc.setAttribute('aria-label', 'Trình chiếu');
		tc.innerHTML = '<p class="cadao-tc-loi"></p><p class="cadao-tc-so"></p><div class="cadao-tc-nut"><button type="button" data-di="-1">← Trước</button><button type="button" data-di="1">Sau →</button><button type="button" class="cadao-tc-thoat">Thoát</button></div>';
		document.body.appendChild(tc);
		var vt = 0;
		var hien = function () {
			var p = tc.querySelector('.cadao-tc-loi');
			p.textContent = '';
			cap[vt].forEach(function (d, i) { if (i) { p.appendChild(document.createElement('br')); } p.appendChild(document.createTextNode(d)); });
			tc.querySelector('.cadao-tc-so').textContent = (vt + 1) + ' / ' + cap.length;
		};
		var di = function (b) { vt = Math.max(0, Math.min(cap.length - 1, vt + b)); hien(); };
		tc.querySelectorAll('[data-di]').forEach(function (n) { n.addEventListener('click', function () { di(Number(n.getAttribute('data-di'))); }); });
		tc.querySelector('.cadao-tc-thoat').addEventListener('click', function () { tc.close(); });
		tc.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === 'PageDown') { di(1); e.preventDefault(); }
			if (e.key === 'ArrowLeft' || e.key === 'ArrowUp' || e.key === 'PageUp') { di(-1); e.preventDefault(); }
		});
		tc.addEventListener('close', function () {
			if (document.fullscreenElement && document.exitFullscreen) { document.exitFullscreen().catch(function () { /* đã thoát */ }); }
			nutTc.focus();
		});
		nutTc.addEventListener('click', function () {
			vt = 0;
			hien();
			tc.showModal();
			if (tc.requestFullscreen) { tc.requestFullscreen().catch(function () { /* máy không cho toàn màn hình: vẫn trình chiếu */ }); }
		});
	}
	var nutNghe = hop && hop.querySelector('.cadao-nghe');
	if (nutNghe && dongLoi.length && 'speechSynthesis' in window && window.SpeechSynthesisUtterance) {
		// Giọng tiếng Việt có sẵn trên máy (Web Speech); không có thì không hiện nút. Không tự phát.
		var giong = null;
		var timGiong = function () {
			var ds = window.speechSynthesis.getVoices();
			for (var i = 0; i < ds.length && !giong; i++) { if (/^vi([-_]|$)/i.test(ds[i].lang)) { giong = ds[i]; } }
			if (giong) { nutNghe.hidden = false; hop.querySelector('.cadao-giong-may').hidden = false; }
		};
		timGiong();
		if (!giong) { window.speechSynthesis.addEventListener('voiceschanged', timGiong); }
		var dangDoc = false;
		var xong = function () { dangDoc = false; nutNghe.textContent = '▶ Nghe'; };
		nutNghe.addEventListener('click', function () {
			if (dangDoc) { window.speechSynthesis.cancel(); xong(); return; }
			dangDoc = true;
			nutNghe.textContent = '■ Dừng';
			dongLoi.forEach(function (d, i) { // mỗi dòng một câu: ngắt nghỉ cuối dòng
				var u = new SpeechSynthesisUtterance(d);
				u.voice = giong;
				u.lang = giong.lang;
				u.rate = 0.9;
				if (i === dongLoi.length - 1) { u.onend = xong; }
				u.onerror = xong;
				window.speechSynthesis.speak(u);
			});
		});
	}

	// ---------- Bài dài (T-090): hiện phần đầu, "Đọc toàn bài", nhớ chỗ đang đọc ----------
	var dai = document.querySelector('.cadao-bai-dai');
	if (dai) {
		var doan = dai.querySelectorAll('.cadao-doan');
		var mo = function () { dai.classList.remove('cadao-thu-gon'); };
		if (dai.querySelector('.cadao-doan[data-sau]')) {
			dai.classList.add('cadao-thu-gon');
			var conLai = dai.getAttribute('data-con-lai');
			var nutDoc = document.createElement('button');
			nutDoc.type = 'button';
			nutDoc.className = 'cadao-doc-toan-bai';
			nutDoc.textContent = 'Đọc toàn bài' + (conLai ? ' (còn ' + conLai + ' dòng)' : '');
			nutDoc.addEventListener('click', function () { mo(); nutDoc.remove(); });
			dai.appendChild(nutDoc);
			dai.querySelectorAll('.cadao-muc-luc a').forEach(function (a) { a.addEventListener('click', function () { mo(); if (nutDoc.parentNode) { nutDoc.remove(); } }); });
		}
		var khoaDoc = 'cadao-doc-den-' + (dai.getAttribute('data-bai') || '');
		var den = 0;
		try { den = Number(localStorage.getItem(khoaDoc)) || 0; } catch (e) { /* không nhớ được */ }
		if (den > 1 && doan[den - 1]) {
			var tiep = document.createElement('a');
			tiep.href = '#' + doan[den - 1].id;
			tiep.className = 'cadao-doc-tiep';
			tiep.textContent = 'Đọc tiếp từ đoạn ' + den;
			tiep.addEventListener('click', function () { mo(); });
			dai.insertBefore(tiep, dai.firstChild);
		}
		if (window.IntersectionObserver) {
			var thay = new IntersectionObserver(function (ds) {
				ds.forEach(function (x) { if (x.isIntersecting) { luu(khoaDoc, x.target.getAttribute('data-so-doan')); } });
			}, { rootMargin: '0px 0px -60% 0px' });
			doan.forEach(function (x) { thay.observe(x); });
		}
	}

	// ---------- Chạm vào từ khó (T-082): ô chú thích; tắt JavaScript thì liên kết nhảy tới chú thích dưới lời ----------
	var tuCt = document.querySelectorAll('a.cadao-tu');
	if (tuCt.length && window.HTMLDialogElement) {
		var o = document.createElement('dialog');
		o.className = 'cadao-o-ct';
		o.setAttribute('aria-labelledby', 'cadao-o-ct-tu');
		o.innerHTML = '<div class="cadao-o-ct-than"><p id="cadao-o-ct-tu" class="cadao-o-ct-tu"></p><div class="cadao-o-ct-nd"></div><button type="button" class="cadao-o-ct-dong">Đóng</button></div>';
		document.body.appendChild(o);
		var tuMo = null;
		var dong = function () { if (o.open) { o.close(); } };
		o.addEventListener('close', function () { if (tuMo) { tuMo.focus(); tuMo.setAttribute('aria-expanded', 'false'); tuMo = null; } });
		o.addEventListener('click', function (e) { if (e.target === o) { dong(); } }); // chạm ra ngoài (vùng nền)
		o.querySelector('.cadao-o-ct-dong').addEventListener('click', dong);
		tuCt.forEach(function (a) {
			a.setAttribute('aria-haspopup', 'dialog');
			a.setAttribute('aria-expanded', 'false');
			a.addEventListener('click', function (e) {
				var li = document.getElementById('cadao-ct-' + a.getAttribute('data-ct'));
				if (!li) { return; }
				e.preventDefault();
				var ten = li.querySelector('strong');
				o.querySelector('.cadao-o-ct-tu').textContent = ten ? ten.textContent : a.textContent;
				var nd = li.cloneNode(true);
				var dau = nd.querySelector('strong');
				if (dau) { dau.remove(); }
				var chu = nd.textContent.replace(/^\s*:\s*/, '');
				var noi = o.querySelector('.cadao-o-ct-nd');
				noi.textContent = '';
				var p = document.createElement('p');
				p.textContent = chu;
				noi.appendChild(p);
				// Dòng "Nguồn: …" của danh sách chứa chú thích này (D-057)
				var ds = li.closest('ul, ol');
				var sau = ds && ds.nextElementSibling;
				if (sau && sau.classList.contains('cadao-chu-thich-nguon')) { noi.appendChild(sau.cloneNode(true)); }
				// Ảnh, bản đồ nhỏ của địa danh đã xác nhận (T-087); "Xem trong từ điển" (T-044)
				var tem = li.querySelector('template.cadao-ct-them');
				if (tem) { noi.appendChild(tem.content.cloneNode(true)); }
				if (li.getAttribute('data-tu-dien')) {
					var lk = document.createElement('a');
					lk.href = li.getAttribute('data-tu-dien');
					var soBai = Number(li.getAttribute('data-so-bai')) || 0;
					lk.textContent = 'Xem trong từ điển' + (soBai > 1 ? ' (' + soBai + ' bài có từ này)' : '');
					var pl = document.createElement('p');
					pl.className = 'cadao-lien-ket-wd';
					pl.appendChild(lk);
					noi.appendChild(pl);
				}
				tuMo = a;
				a.setAttribute('aria-expanded', 'true');
				o.showModal();
				if (li.getAttribute('data-muc')) { suKien('chu_giai_mo', { muc: li.getAttribute('data-muc') }); }
				if (window.matchMedia('(min-width: 640px)').matches) { // máy tính: ô cạnh từ
					var r = a.getBoundingClientRect();
					o.style.top = Math.min(r.bottom + 8, window.innerHeight - o.offsetHeight - 8) + 'px';
					o.style.left = Math.max(8, Math.min(r.left, window.innerWidth - o.offsetWidth - 8)) + 'px';
				} else {
					o.style.top = '';
					o.style.left = '';
				}
				o.querySelector('.cadao-o-ct-dong').focus();
			});
		});
	}

	// ---------- Góp ý (T-045): nút mở form, chọn sẵn loại; Turnstile chỉ tải khi bắt đầu điền form ----------
	document.querySelectorAll('a.cadao-gop-y-mo').forEach(function (a) {
		a.addEventListener('click', function (e) {
			var hopGy = document.querySelector('.cadao-gop-y-hop');
			var r = hopGy && hopGy.querySelector('input[name="loai"][value="' + a.getAttribute('data-loai') + '"]');
			if (!hopGy || !r) { return; }
			e.preventDefault();
			hopGy.open = true;
			r.checked = true;
			hopGy.scrollIntoView({ block: 'start' });
			var nd = hopGy.querySelector('textarea');
			if (nd) { nd.focus(); }
		});
	});
	if (cauHinh.turnstile) {
		var daTai = false;
		document.addEventListener('focusin', function (e) {
			if (daTai || !e.target.closest || !e.target.closest('form.cadao-form, #commentform')) { return; }
			daTai = true;
			var s = document.createElement('script');
			s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
			s.async = true;
			document.head.appendChild(s);
		});
	}

	// ---------- Lượt đọc: hiện 5 giây trên màn hình, mỗi máy một lần mỗi ngày ----------
	var ld = cauHinh.luotDoc;
	if (!ld || !window.fetch) { return; }
	var KHOA = 'cadao-da-doc';
	var ngayDoc = homNay.getFullYear() + '-' + hai(homNay.getMonth() + 1) + '-' + hai(homNay.getDate());
	var da = null;
	try { da = JSON.parse(localStorage.getItem(KHOA)); } catch (e) { /* chế độ riêng tư */ }
	if (!da || da.ngay !== ngayDoc || !Array.isArray(da.bai)) { da = { ngay: ngayDoc, bai: [] }; }
	if (da.bai.indexOf(ld.id) >= 0) { return; }

	var conLai = 5000;
	var moc = 0;
	var hen = null;
	var gui = function () {
		hen = null;
		document.removeEventListener('visibilitychange', doi);
		da.bai.push(ld.id);
		try { localStorage.setItem(KHOA, JSON.stringify(da)); } catch (e) { /* vẫn tính lượt này */ }
		var tu = '';
		try {
			var h = document.referrer ? new URL(document.referrer).hostname : '';
			if (h && h !== location.hostname) { tu = h; }
		} catch (e) { /* referrer lạ */ }
		fetch(ld.url, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ tu: tu }) })
			.catch(function () { /* mất mạng: bỏ lượt này */ });
	};
	var bat = function () {
		if (document.visibilityState === 'visible' && !hen) {
			moc = Date.now();
			hen = setTimeout(gui, conLai);
		}
	};
	var tat = function () {
		if (hen) {
			clearTimeout(hen);
			hen = null;
			conLai -= Date.now() - moc;
		}
	};
	var doi = function () {
		if (document.visibilityState === 'visible') { bat(); } else { tat(); }
	};
	document.addEventListener('visibilitychange', doi);
	bat();
}());
