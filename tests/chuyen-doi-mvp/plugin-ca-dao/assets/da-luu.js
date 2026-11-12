/**
 * Trang "Đã lưu" (T-051): đọc danh sách bài người đọc đã lưu trong bộ nhớ trình duyệt (khóa "cadao-da-luu", do nút
 * ☆ Lưu trên trang bài ghi), cho bỏ lưu từng bài. Không gửi gì về máy chủ.
 */
(function () {
	'use strict';
	var hop = document.querySelector('.cadao-da-luu');
	if (!hop) { return; }
	var KHOA = 'cadao-da-luu';
	var ds = hop.querySelector('.cadao-da-luu-ds');
	var so = hop.querySelector('.cadao-da-luu-so');
	var trong = hop.querySelector('.cadao-da-luu-trong');
	var bai = [];
	try {
		bai = JSON.parse(localStorage.getItem(KHOA) || '[]');
		if (!Array.isArray(bai)) { bai = []; }
	} catch (e) {
		hop.querySelector('.cadao-da-luu-chan').hidden = false;
		return;
	}
	// Chỉ nhận liên kết về chính site này
	bai = bai.filter(function (x) {
		try { return x && x.id && new URL(x.url, location.href).origin === location.origin; } catch (e) { return false; }
	});

	var ghi = function () { try { localStorage.setItem(KHOA, JSON.stringify(bai)); } catch (e) { /* bộ nhớ đầy hoặc bị chặn */ } };
	var dung = function (focusVao) {
		ds.textContent = '';
		so.textContent = bai.length ? bai.length + ' bài đã lưu trên máy này.' : '';
		trong.hidden = bai.length > 0;
		bai.forEach(function (x, i) {
			var li = document.createElement('li');
			var a = document.createElement('a');
			a.href = x.url;
			var dam = document.createElement('strong');
			dam.textContent = x.dong || x.ten;
			a.appendChild(dam);
			li.appendChild(a);
			if (x.loai) {
				var phu = document.createElement('span');
				phu.className = 'cadao-tim-phu';
				phu.textContent = ' · ' + x.loai;
				li.appendChild(phu);
			}
			var bo = document.createElement('button');
			bo.type = 'button';
			bo.className = 'cadao-bo-luu';
			bo.textContent = 'Bỏ lưu';
			bo.setAttribute('aria-label', 'Bỏ lưu: ' + (x.dong || x.ten));
			bo.addEventListener('click', function () {
				bai.splice(i, 1);
				ghi();
				dung(i);
			});
			li.appendChild(document.createTextNode(' '));
			li.appendChild(bo);
			ds.appendChild(li);
		});
		// Bỏ lưu xong: focus sang bài kế (hoặc bài trước nếu vừa bỏ bài cuối), hết bài thì về dòng thông báo
		if (typeof focusVao === 'number') {
			var nut = ds.querySelectorAll('.cadao-bo-luu');
			var toi = nut[Math.min(focusVao, nut.length - 1)];
			if (toi) { toi.focus(); } else { trong.setAttribute('tabindex', '-1'); trong.focus(); }
		}
	};
	dung();
}());
