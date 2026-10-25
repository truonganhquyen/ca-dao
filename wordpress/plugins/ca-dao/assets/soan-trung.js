/**
 * Trình soạn bài Ca dao – Tục ngữ: ô "Có thể trùng" (T-111, D-065). Sau khi dán / gõ lời vào khối Thơ (ngừng gõ một
 * chút) và khi bấm Đăng, liệt kê bài có lời giống, với "Mở bài này" · "Thêm làm dị bản" · "Vẫn đăng bài mới".
 * Chỉ báo, không chặn đăng (D-054). Không thư viện ngoài: chỉ các gói wp.* có sẵn của WordPress.
 */
(function (wp) {
	'use strict';
	if (!wp || !wp.plugins || !wp.element || !wp.data || !wp.apiFetch) { return; }
	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var useSelect = wp.data.useSelect;
	var Button = wp.components.Button;
	var nguon = wp.editor && wp.editor.PluginDocumentSettingPanel ? wp.editor : wp.editPost;
	var Panel = nguon && nguon.PluginDocumentSettingPanel;
	var TruocKhiDang = nguon && nguon.PluginPrePublishPanel;
	if (!Panel) { return; }

	/** HTML của khối Thơ đầu tiên (lời chính). */
	function loiHtml(khoi) {
		for (var i = 0; i < khoi.length; i++) {
			if (khoi[i].name === 'core/verse') {
				var c = khoi[i].attributes.content;
				return typeof c === 'string' ? c : (c && c.toHTMLString ? c.toHTMLString() : String(c || ''));
			}
		}
		return '';
	}

	function DanhSach(p) {
		if (p.loi === '') {
			return el('p', { className: 'description' }, 'Dán hoặc gõ lời vào khối Thơ; máy sẽ so cả lời với các bài đã có.');
		}
		if (p.kq === null) { return el('p', null, 'Đang so…'); }
		if (p.daBoQua) { return el('p', null, 'Đã ghi: đây là bài khác. Bạn cứ đăng như thường.'); }
		if (!p.kq.length) { return el('p', null, 'Chưa thấy bài nào có lời giống.'); }
		var bai = p.kq.filter(function (x) { return x.loai === 'bai' && x.cap_do !== 'lien_quan'; });
		return el('div', null,
			p.kq.map(function (x) {
				return el('div', { key: x.muc, style: { borderTop: '1px solid #ddd', padding: '8px 0' } },
					el('strong', null, x.tieu_de),
					el('div', null, x.nhan + (x.giong ? ' · giống ' + x.giong + '%' : '') + (x.ly_do ? ' · ' + x.ly_do : '')),
					el('div', { className: 'description' }, x.the_loai + ' · ' + x.trang),
					el('div', { style: { display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '6px' } },
						x.sua ? el(Button, { variant: 'secondary', href: x.sua, target: '_blank' }, x.loai === 'kho' ? 'Xem trong kho' : 'Mở bài này') : null,
						x.loai === 'bai' ? el(Button, { variant: 'secondary', isBusy: p.dangThem === x.id, onClick: function () { p.themDiBan(x); } }, 'Thêm làm dị bản') : null
					)
				);
			}),
			bai.length ? el('div', { style: { borderTop: '1px solid #ddd', paddingTop: '8px' } },
				el(Button, { variant: 'tertiary', onClick: p.boQua }, 'Vẫn đăng bài mới'),
				el('p', { className: 'description' }, 'Đây là bài khác: bấm để máy không báo nữa, rồi đăng như thường.')
			) : null,
			p.loi2 ? el('p', { style: { color: '#b32d2e' } }, p.loi2) : null
		);
	}

	function CoTheTrung() {
		var d = useSelect(function (s) {
			var ed = s('core/editor');
			return { khoi: s('core/block-editor').getBlocks(), id: ed.getCurrentPostId(), loai: ed.getCurrentPostType() };
		}, []);
		var loi = loiHtml(d.khoi);
		var kqS = useState(null);
		var boQuaS = useState(false);
		var themS = useState(0);
		var loiS = useState('');
		// Có bài giống thì tự mở ô (WordPress nhớ ô đóng / mở theo người dùng)
		var coKq = !!(kqS[0] && kqS[0].length);
		useEffect(function () {
			var ten = 'cadao-co-the-trung/cadao-co-the-trung';
			var ed = wp.data.select('core/editor');
			if (coKq && ed.isEditorPanelOpened && !ed.isEditorPanelOpened(ten)) {
				wp.data.dispatch('core/editor').toggleEditorPanelOpened(ten);
			}
		}, [coKq]);
		useEffect(function () {
			if (loi.replace(/<[^>]+>/g, '').trim() === '') { kqS[1]([]); return undefined; }
			kqS[1](null);
			var hen = setTimeout(function () {
				wp.apiFetch({ path: '/cadao/v1/trung', method: 'POST', data: { loi_html: loi, bai: d.id } })
					.then(function (r) { kqS[1](r.ket_qua || []); boQuaS[1](false); })
					.catch(function () { kqS[1]([]); });
			}, 1200);
			return function () { clearTimeout(hen); };
		}, [loi]);
		if (d.loai !== 'cdtn') { return null; }

		var p = {
			loi: loi.replace(/<[^>]+>/g, '').trim(),
			kq: kqS[0],
			daBoQua: boQuaS[0],
			dangThem: themS[0],
			loi2: loiS[0],
			themDiBan: function (x) {
				// eslint-disable-next-line no-alert
				if (!window.confirm('Thêm lời bạn vừa gõ làm một dị bản của "' + x.tieu_de + '"? Bản nháp này sẽ vào thùng rác (khôi phục được trong 30 ngày).')) { return; }
				themS[1](x.id);
				wp.apiFetch({ path: '/cadao/v1/them-di-ban', method: 'POST', data: { bai: d.id, dich: x.id, loi_html: loi } })
					.then(function (r) { window.location.href = r.sua; })
					.catch(function (e) { themS[1](0); loiS[1]((e && e.message) || 'Không thêm được dị bản.'); });
			},
			boQua: function () {
				var cac = (kqS[0] || []).map(function (x) { return x.muc; });
				wp.apiFetch({ path: '/cadao/v1/khong-trung', method: 'POST', data: { bai: d.id, cac: cac } })
					.then(function () { boQuaS[1](true); })
					.catch(function () { boQuaS[1](true); });
			}
		};
		var coBai = p.kq && p.kq.length && !p.daBoQua;
		return el(wp.element.Fragment, null,
			el(Panel, { name: 'cadao-co-the-trung', title: 'Có thể trùng' + (coBai ? ' (' + p.kq.length + ')' : '') }, el(DanhSach, p)),
			coBai && TruocKhiDang ? el(TruocKhiDang, { title: 'Có thể trùng (' + p.kq.length + ')', initialOpen: true }, el(DanhSach, p)) : null
		);
	}

	wp.plugins.registerPlugin('cadao-co-the-trung', { render: CoTheTrung });
}(window.wp));
