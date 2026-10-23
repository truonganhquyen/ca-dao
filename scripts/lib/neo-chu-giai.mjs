// Phân giải neo chú giải — dùng chung cho công cụ kiểm tra và cho build Astro.
// Nguyên tắc: con người khai thứ dễ nhớ (cụm từ, khi cần thì dòng/lần); máy suy ra vị trí chính xác.
// Đặc tả: docs/10-dac-ta-du-lieu.md mục 4.3.
//
// Người biên soạn khai:   { di_ban: v1, cum_tu: "Thái Sơn" }               (đủ khi cụm từ chỉ xuất hiện 1 lần trong dị bản)
//                          { di_ban: v1, dong: 3, cum_tu: "cò" }           (khi cần chỉ rõ dòng)
//                          { di_ban: v1, dong: 3, cum_tu: "cò", lan: 2 }   (khi cụm từ lặp trong cùng dòng)
// Máy trả về:             { ok: true, dong, am_tiet_dau, so_am_tiet }     (vị trí tính theo âm tiết, từ 0)
//                          hoặc { ok: false, ma, thong_bao }

const PUNCT = /^[\s.,;:!?"'“”‘’()\[\]«»…\-–—]+|[\s.,;:!?"'“”‘’()\[\]«»…\-–—]+$/g;

export const chuanHoaAmTiet = t => t.normalize('NFC').toLocaleLowerCase('vi').replace(PUNCT, '');

/** Tách một dòng thành âm tiết, giữ chỉ số theo âm tiết gốc (kể cả âm tiết chỉ có dấu câu). */
export function tachAmTiet(dong) {
  return dong.split(/\s+/).filter(Boolean).map((goc, i) => ({ goc, chuan: chuanHoaAmTiet(goc), i }));
}

export const tachDong = loi => String(loi ?? '').replace(/\n+$/, '').split('\n');

/** Mọi vị trí xuất hiện trọn âm tiết của cụm từ trong một dòng. */
function timTrongDong(dong, cum) {
  const at = tachAmTiet(dong).filter(x => x.chuan);
  const out = [];
  for (let k = 0; k + cum.length <= at.length; k++) {
    if (cum.every((t, j) => at[k + j].chuan === t)) out.push(at[k].i);
  }
  return out;
}

/**
 * @param {string} loi   lời của dị bản
 * @param {{dong?: number, cum_tu: string, lan?: number}} neo
 */
export function phanGiaiNeo(loi, neo) {
  const cum = String(neo.cum_tu ?? '').split(/\s+/).map(chuanHoaAmTiet).filter(Boolean);
  if (!cum.length) return { ok: false, ma: 'cum_tu_rong', thong_bao: 'cum_tu rỗng' };
  const dongs = tachDong(loi);

  let viTri; // [{dong, am_tiet_dau}]
  if (neo.dong !== undefined && neo.dong !== null) {
    if (!Number.isInteger(neo.dong) || neo.dong < 1 || neo.dong > dongs.length) {
      return { ok: false, ma: 'vuot_dong', thong_bao: `dị bản chỉ có ${dongs.length} dòng, không có dòng ${neo.dong}` };
    }
    viTri = timTrongDong(dongs[neo.dong - 1], cum).map(a => ({ dong: neo.dong, am_tiet_dau: a }));
  } else {
    viTri = dongs.flatMap((d, i) => timTrongDong(d, cum).map(a => ({ dong: i + 1, am_tiet_dau: a })));
  }

  const pham_vi = neo.dong ? `dòng ${neo.dong}` : 'toàn dị bản';
  if (!viTri.length) return { ok: false, ma: 'khong_thay', thong_bao: `không tìm thấy "${neo.cum_tu}" (trọn âm tiết) trong ${pham_vi}` };
  if (neo.lan === undefined || neo.lan === null) {
    if (viTri.length > 1) {
      const noi = viTri.map(v => `dòng ${v.dong}`).join(', ');
      return { ok: false, ma: 'mo_ho', thong_bao: `"${neo.cum_tu}" xuất hiện ${viTri.length} lần trong ${pham_vi} (${noi}); thêm "dong" hoặc "lan" để chỉ rõ` };
    }
    return { ok: true, ...viTri[0], so_am_tiet: cum.length };
  }
  if (!Number.isInteger(neo.lan) || neo.lan < 1 || neo.lan > viTri.length) {
    return { ok: false, ma: 'khong_du_lan', thong_bao: `"${neo.cum_tu}" chỉ xuất hiện ${viTri.length} lần trong ${pham_vi}, không có lần ${neo.lan}` };
  }
  return { ok: true, ...viTri[neo.lan - 1], so_am_tiet: cum.length };
}

/** Cụm từ có tương ứng với mục từ không (so với `tu` và `bien_the`, không phân biệt hoa thường). */
export function khopMucTu(cum_tu, mucTu) {
  const n = s => String(s ?? '').split(/\s+/).map(chuanHoaAmTiet).filter(Boolean).join(' ');
  const c = n(cum_tu);
  const dang = [mucTu?.tu, ...(mucTu?.bien_the || [])].map(n).filter(Boolean);
  return dang.some(d => d === c || c.includes(d) || d.includes(c));
}
