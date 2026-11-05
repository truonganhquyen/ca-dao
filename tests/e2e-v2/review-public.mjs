// Review độc lập: chỉ GET/HEAD, một trang mỗi lượt, không đọc tài khoản.
// node tests/e2e-v2/review-public.mjs [--full]
// Kết quả chi tiết ở test-results/ (không commit); chỉ ảnh công khai vào báo cáo.
import fs from 'node:fs/promises';
import { chromium } from '@playwright/test';
import { createRequire } from 'node:module';
import { gzipSync } from 'node:zlib';
const require = createRequire(import.meta.url);
const axe = await fs.readFile(require.resolve('axe-core/axe.min.js'), 'utf8');
const out = 'test-results/review-v2';
const shots = 'docs/e2e-review/v2/screenshots';
await fs.mkdir(out, { recursive: true });
await fs.mkdir(shots, { recursive: true });
const browser = await chromium.launch({ channel: 'chrome', headless: true });
const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, hasTouch: true, serviceWorkers: 'block' });
const report = { started: new Date().toISOString(), browser: browser.version(), blocked: [], listings: [], pages: [], matrix: [], errors: [] };
let lastNav = 0;
await ctx.route('**/*', async route => {
  const req = route.request();
  if (!['GET', 'HEAD'].includes(req.method())) {
    report.blocked.push({ method: req.method(), path: new URL(req.url()).pathname });
    return route.abort();
  }
  if (req.isNavigationRequest()) {
    await new Promise(r => setTimeout(r, Math.max(0, 1100 - (Date.now() - lastNav))));
    lastNav = Date.now();
  }
  return route.continue();
});
await ctx.addInitScript(() => {
  window.__reviewCsp = [];
  document.addEventListener('securitypolicyviolation', e => window.__reviewCsp.push({ directive: e.violatedDirective, blocked: e.blockedURI }));
});
const page = await ctx.newPage();
let external = new Set(), errors = [], resources = [], resourceJobs = [];
page.on('request', r => { if (/^https?:/.test(r.url()) && new URL(r.url()).hostname !== 'phongdao.com') external.add(new URL(r.url()).hostname); });
page.on('pageerror', e => errors.push(e.message));
page.on('console', m => { if (m.type() === 'error' && !m.text().includes('net::ERR_FAILED')) errors.push(m.text()); });
page.on('response', r => {
  resourceJobs.push((async () => {
    try { const b = await r.body(); resources.push({ type: r.request().resourceType(), bytes: /woff2?|png|jpe?g|webp/.test(r.url()) ? b.length : gzipSync(b).length }); } catch { /* chuyển hướng */ }
  })());
});
const save = () => fs.writeFile(`${out}/public.json`, JSON.stringify(report, null, 2) + '\n');
async function go(path) {
  external = new Set(); errors = []; resources = []; resourceJobs = [];
  const res = await page.goto('https://phongdao.com' + path, { waitUntil: 'load', timeout: 60000 });
  await page.evaluate(() => document.fonts.ready);
  await page.waitForTimeout(200);
  await Promise.allSettled(resourceJobs);
  return res;
}
const articleRE = /^\/(ca-dao|tuc-ngu|ve|dong-dao|cau-do)\/[^/]+\/$/;
const all = new Set();
try {
  // Toàn bộ danh sách công khai; không đoán tên đường dẫn bài cũ.
  let next = '/kho/'; const visited = new Set();
  while (next && !visited.has(next) && visited.size < 30) {
    visited.add(next); await go(next);
    const links = await page.locator('main a[href]').evaluateAll(es => es.map(e => ({ path: new URL(e.href).pathname + new URL(e.href).search, text: e.textContent.trim(), rel: e.rel })));
    links.filter(x => articleRE.test(x.path)).forEach(x => all.add(x.path));
    report.listings.push({ path: next, links: links.filter(x => articleRE.test(x.path)).map(x => x.path), countText: await page.locator('main').innerText().then(t => t.match(/\d+ tác phẩm[^\n]*/)?.[0] || '') });
    next = links.find(x => /next/.test(x.rel) || /Tiếp|Sau|→|›/.test(x.text) && /trang=|page\//.test(x.path))?.path;
    if (!next) next = links.filter(x => /trang=|page\//.test(x.path)).map(x => x.path).find(x => !visited.has(x));
    console.log('DANH SÁCH', visited.size, 'bài', all.size, 'tiếp', next || '-'); await save();
  }
  // Mẫu giả ngẫu nhiên tái lập: 8 bài mỗi thể loại; seed cố định ngày review.
  let seed = 20261104;
  const rand = () => { seed = (Math.imul(seed, 1664525) + 1013904223) >>> 0; return seed / 4294967296; };
  const sample = [];
  for (const genre of ['ca-dao', 'tuc-ngu', 've', 'dong-dao', 'cau-do']) {
    const paths = [...all].filter(p => p.startsWith('/' + genre + '/'));
    for (let i = paths.length - 1; i > 0; i--) { const j = Math.floor(rand() * (i + 1)); [paths[i], paths[j]] = [paths[j], paths[i]]; }
    sample.push(...paths.slice(0, 8));
  }
  report.sample = sample;
  const specials = [28,709,407,409,103,382,1105].map(id => '/?p=' + id);
  const other = ['/', '/kho/', '/kham-pha/', '/tim/', '/tu-dien/', '/ban-do/', '/tuyen-tap/', '/gop/', '/da-luu/', '/do-vui/', '/tro-choi/', '/hay-bi-nham/', '/chu-de/', '/vung/', '/mo-tip/', '/ve-du-an/', '/phuong-phap/', '/nguon/', '/chinh-sach-du-lieu/', '/quyen-rieng-tu/', '/review-v2-khong-ton-tai/'];
  for (const genre of ['ca-dao','tuc-ngu','ve','dong-dao','cau-do']) other.push('/the-loai/' + genre + '/');
  const paths = [...new Set([...sample, ...specials, ...other, ...(process.argv.includes('--full') ? all : [])])];
  for (const [index, path] of paths.entries()) {
    try {
      const res = await go(path);
      const data = await page.evaluate(() => {
        const text = el => el?.textContent?.trim() || '';
        const loi = [...document.querySelectorAll('.verse-card .line')].map(l => text(l).replace(/^\d+\s*/, ''));
        const heads = [...document.querySelectorAll('main h2,main h3,main summary')].map(text);
        const main = document.querySelector('main')?.innerText || '';
        return { canonical: document.querySelector('link[rel=canonical]')?.href, title: document.title,
          description: document.querySelector('meta[name=description]')?.content,
          robots: document.querySelector('meta[name=robots]')?.content,
          ogImage: document.querySelector('meta[property="og:image"]')?.content,
          id: document.querySelector('[data-action=save]')?.dataset.id || document.querySelector('.bai-dai')?.dataset.bai,
          lines: loi.length, heads, meaning: heads.includes('Ý nghĩa'),
          annotations: heads.some(h => /^Từ khó/.test(h)), variants: heads.some(h => /^Dị bản/.test(h)),
          segments: document.querySelectorAll('.bai-dai .doan').length,
          namedSegments: document.querySelectorAll('.bai-dai .doan h2,.bai-dai .doan h3').length,
          visibleLines: [...document.querySelectorAll('.verse-card .line')].filter(e=>e.getClientRects().length).length,
          aiLabel: /Có hỗ trợ của AI/.test(main), suspect: /Nghi không phải dân gian/.test(main),
          anomaly: /\ufffd|&amp;|&nbsp;|<\/?(?:p|div|span|br)[ >]/.test(main),
          editorLeak: !!document.querySelector('[data-sua], .dang-sua, script[src*="bien-tap.js"]'),
          csp: window.__reviewCsp,
          links: [...document.querySelectorAll('main a[href]')].map(a=>a.getAttribute('href')).filter(h=>/^https:\/\/phongdao.com\/(chu-de|vung|mo-tip|tu-dien)\//.test(h)).slice(0,10),
          version: [...document.querySelectorAll('script[src],link[href]')].map(e=>e.src||e.href).filter(s=>/do-luong.js|giao-dien.css/.test(s))
        };
      });
      const row = { path, finalPath: new URL(page.url()).pathname, status: res.status(), cspHeader: !!res.headers()['content-security-policy'], external: [...external], errors: [...errors], budget: { total: resources.reduce((a,r)=>a+r.bytes,0), js: resources.filter(r=>r.type==='script').reduce((a,r)=>a+r.bytes,0) }, ...data };
      report.pages.push(row);
      const matrix = sample.includes(path) || specials.includes(path) || other.includes(path);
      if (matrix) {
        await page.evaluate(axe);
        for (const width of [320,360,390,768,1440]) for (const mode of ['light','dark']) {
          await page.setViewportSize({width,height:900});
          await page.emulateMedia({colorScheme:mode});
          await page.waitForTimeout(100);
          const geometry = await page.evaluate(() => {
            const visible = e=>e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden';
            const lines = e=>{const y=new Set();const w=document.createTreeWalker(e,NodeFilter.SHOW_TEXT);let n;while(n=w.nextNode()){if(!n.textContent.trim()||n.parentElement.closest('.ln'))continue;const r=document.createRange();r.selectNodeContents(n);for(const b of r.getClientRects())if(b.width>1)y.add(Math.round((b.top+b.height/2)/6));}return y.size;};
            return { overflow:document.documentElement.scrollWidth-innerWidth,
              wrapped:[...document.querySelectorAll('[data-fit] .line')].filter(visible).map((e,i)=>({line:i+1,rows:lines(e),font:getComputedStyle(e).fontSize})).filter(e=>e.rows>1),
              small:[...document.querySelectorAll('button,summary,a.btn,input:not([type=hidden]),select')].filter(visible).map(e=>({tag:e.tagName,text:e.textContent.trim().slice(0,55),w:Math.round(e.getBoundingClientRect().width),h:Math.round(e.getBoundingClientRect().height)})).filter(e=>e.w<44||e.h<44),
              theme: getComputedStyle(document.documentElement).getPropertyValue('--giay').trim()
            };
          });
          const a = await page.evaluate(async()=>{const r=await axe.run(document,{runOnly:{type:'tag',values:['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']}});return r.violations.map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>({target:n.target,summary:n.failureSummary})).slice(0,10)}));});
          const m={path,width,mode,...geometry,axe:a}; report.matrix.push(m);
          if ((geometry.overflow>1 || a.length || geometry.wrapped.length) && [320,390].includes(width) || path==='/' && width===1440) {
            const name=`${String(index).padStart(3,'0')}-${width}-${mode}.png`;
            await page.screenshot({path:`${shots}/${name}`,fullPage:false});m.screenshot=name;
          }
        }
      }
      console.log('PAGE',index+1,'/',paths.length,path,'HTTP',row.status,'lignes',row.lines,'Y',row.meaning,'T',row.annotations,'D',row.variants);
      await save();
    } catch(e) {report.errors.push({path,error:String(e.message).slice(0,500)});await save();console.log('ERREUR',path,String(e.message).slice(0,150));}
  }
  report.finished=new Date().toISOString(); await save();
} finally { await browser.close(); }
