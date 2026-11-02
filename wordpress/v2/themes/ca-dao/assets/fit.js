// SINH TỪ design/v2/source — đừng sửa tay. Sửa ở bộ thiết kế rồi chạy `npm run dong-bo:giao-dien-v2`.
// D-093: mỗi câu một dòng. Co cỡ chữ lời vừa khung (trần = cỡ người đọc chọn, sàn 15 px, 14 px khi màn < 360 px, hoặc data-san).
// Dưới sàn: tục ngữ ngắt theo vế (.ve), thể khác xuống dòng có thụt treo (.tran). Cùng data-nhom thì cùng cỡ.
// data-max: số px | "mode" (trình chiếu) | "kid" (Đọc cùng bé) | "w0.06" (tỷ lệ bề rộng khung, ảnh chia sẻ).
// Trong khung có data-fit-h (ảnh chia sẻ), cỡ chữ còn bị chặn theo chiều cao khung.
(()=>{'use strict';
const tran=el=>{const d=el.dataset.max,box=el.closest('.verse-box')||el.parentElement;let t;if(d==='mode')t=Math.min(innerHeight*.09,72);else if(d==='kid')t=Math.min(innerHeight*.12,96);else if(d&&d[0]==='w')t=box.clientWidth*parseFloat(d.slice(1));else if(d)t=+d;else{const g=parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--reading-size'))||22;t=el.classList.contains('proverb')?g+6:g}const h=el.closest('[data-fit-h]');if(h){const n=el.querySelectorAll('.line').length||1,yn=h.querySelector('.acs-y-nghia');t=Math.min(t,h.clientHeight*(yn&&getComputedStyle(yn).display!=='none'?.32:.44)/(n*1.5))}return t};
function doRong(el){const box=el.closest('.verse-box')||el.parentElement,avail=box.clientWidth;if(!avail)return null;el.classList.add('do');el.style.fontSize='100px';const w=el.scrollWidth;el.classList.remove('do');return w?Math.floor(1000*avail/w*.985)/10:null}
function fit(root=document){const groups=new Map();for(const el of root.querySelectorAll('[data-fit]')){el.classList.remove('theo-ve','tran','mot-dong');const san=+(el.dataset.san||(innerWidth<360?14:15));let s=doRong(el);if(s===null)continue;if(s<san&&el.querySelector('.ve')){el.classList.add('theo-ve');s=doRong(el)}const key=el.dataset.nhom||el,g=groups.get(key)||{els:[],size:Infinity,san};g.els.push(el);g.size=Math.min(g.size,tran(el),s);groups.set(key,g)}
for(const {els,size,san} of groups.values())for(const el of els){el.style.fontSize=Math.max(san,size)+'px';el.classList.add(size>=san?'mot-dong':'tran')}}
let hen;const lai=()=>{cancelAnimationFrame(hen);hen=requestAnimationFrame(()=>fit())};
window.PDfit=r=>fit(r||document);fit();document.fonts?.ready.then(lai);
// fonts.ready chỉ xong một lần: phông của lời nạp ở đợt sau (sau phông đầu trang) thì phải co lại sau mỗi đợt
document.fonts?.addEventListener?.('loadingdone',lai);addEventListener('resize',lai);document.addEventListener('toggle',e=>{if(e.target.open)lai()},true);
})();
