/**
 * Trang bài Ca dao – Tục ngữ: trích dẫn, chia sẻ (T-042) và đếm lượt đọc (T-081, D-064).
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

	// ---------- Trích dẫn, chia sẻ ----------
	var hop = document.querySelector('.cadao-hanh-dong');
	if (hop) {
		var bao = hop.querySelector('.cadao-bao');
		var noiBao = function (chu) { bao.textContent = ''; setTimeout(function () { bao.textContent = chu; }, 50); };
		hop.querySelectorAll('.cadao-can-js').forEach(function (o) { o.hidden = false; });

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
				tuMo = a;
				a.setAttribute('aria-expanded', 'true');
				o.showModal();
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
