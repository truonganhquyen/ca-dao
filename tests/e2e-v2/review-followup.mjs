// Bổ sung sau khi bản 1.0.3 được triển khai trong lúc review. Chỉ đọc, tuần tự.
import fs from 'node:fs/promises';
import { chromium } from '@playwright/test';
import { createRequire } from 'node:module';
import { gzipSync } from 'node:zlib';
const old=JSON.parse(await fs.readFile('test-results/review-v2/public.json','utf8'));
const axe=await fs.readFile(createRequire(import.meta.url).resolve('axe-core/axe.min.js'),'utf8');
const browser=await chromium.launch({channel:'chrome',headless:true});
const out={started:new Date().toISOString(),matrix:[],nojs:[],long:[],budgets:[],redirects:[],blocked:[]};
const save=()=>fs.writeFile('test-results/review-v2/followup.json',JSON.stringify(out,null,2));
let last=0;
async function context(js=true){const c=await browser.newContext({viewport:{width:390,height:844},hasTouch:true,javaScriptEnabled:js,serviceWorkers:'block'});await c.route('**/*',async r=>{if(!['GET','HEAD'].includes(r.request().method())){out.blocked.push(new URL(r.request().url()).pathname);return r.abort();}if(r.request().isNavigationRequest()){await new Promise(res=>setTimeout(res,Math.max(0,1100-(Date.now()-last))));last=Date.now();}return r.continue();});return c;}
const ctx=await context();const p=await ctx.newPage();
const go=async(path)=>{const r=await p.goto('https://phongdao.com'+path);await p.evaluate(()=>document.fonts.ready);return r;};
try{
for(const path of ['/','/kham-pha/','/chu-de/','/mo-tip/','/ban-do/','/ve-du-an/','/phuong-phap/','/nguon/','/chinh-sach-du-lieu/']){
 const r=await go(path);await p.evaluate(axe);
 for(const width of [320,360,390,768,1440])for(const mode of ['light','dark']){
  await p.setViewportSize({width,height:900});await p.emulateMedia({colorScheme:mode});await p.waitForTimeout(120);
  const d=await p.evaluate(async()=>({overflow:document.documentElement.scrollWidth-innerWidth,axe:(await axe.run(document,{runOnly:{type:'tag',values:['wcag2a','wcag2aa','wcag21aa','wcag22aa']}})).violations.map(v=>({id:v.id,nodes:v.nodes.map(n=>n.target)}))}));
  const row={path,width,mode,status:r.status(),...d};out.matrix.push(row);
  if((d.overflow>1||d.axe.length)&&width===320||path==='/kham-pha/'&&width===390){row.screenshot=`new-${path.replace(/\W/g,'')}-${width}-${mode}.png`;await p.screenshot({path:'docs/e2e-review/v2/screenshots/'+row.screenshot,fullPage:false});}
 }
 console.log('MATRIX',path);await save();
}
for(const path of [...new Set(old.pages.filter(p=>p.namedSegments).map(p=>p.finalPath))]){
 await go(path);const visible=()=>p.locator('.verse-card .line:visible').count();const initial=await visible();
 const toggles=p.locator('.bai-dai button').filter({hasText:'Đọc toàn bài'});const canExpand=await toggles.count();if(canExpand)await toggles.first().click();
 const after=await visible();const toc=await p.locator('.muc-luc a').count();
 out.long.push({path,initial,after,canExpand,toc});console.log('LONG',path,initial,after);await save();
}
for(const path of ['/ca-dao/rong-dong-mac-suc-chim-bay/','/ca-dao/em-toi-buon-ngu-buon-nghe/','/dong-dao/con-cong-hay-mua/']){
 const c=await context();const tab=await c.newPage();const assets=[];const jobs=[];
 tab.on('response',r=>jobs.push((async()=>{try{const b=await r.body();assets.push({path:new URL(r.url()).pathname,type:r.request().resourceType(),gzip:/font|image/.test(r.request().resourceType())?b.length:gzipSync(b).length});}catch{}})()));
 await tab.goto('https://phongdao.com'+path);await tab.evaluate(()=>document.fonts.ready);await tab.waitForTimeout(1200);await Promise.allSettled(jobs);
 out.budgets.push({path,width:390,total:assets.reduce((a,b)=>a+b.gzip,0),js:assets.filter(a=>a.type==='script').reduce((a,b)=>a+b.gzip,0),assets});await c.close();await save();
}
for(const path of ['/ca-dao/cong-cha-nhu-nui-thai-son-2/','/tinh-huong/hat-ru/','/thanh-ngu/an-qua-nho-ke-trong-cay/','/bai/khong-ton-tai-review/']){
 const r=await go(path);const chain=[];let req=r.request();while(req){const response=await req.response();chain.unshift({path:new URL(req.url()).pathname,status:response?.status()});req=req.redirectedFrom();}
 out.redirects.push({path,chain,hasMain:!!await p.locator('main').innerText()});await save();
}
const nojs=await context(false);const n=await nojs.newPage();
for(const path of [...old.sample,...[28,709,407,409,103,382,1105].map(id=>'/?p='+id),'/','/kho/','/tim/?q=cong+cha','/tu-dien/','/ban-do/','/tuyen-tap/','/gop/','/kham-pha/']){
 const r=await n.goto('https://phongdao.com'+path);out.nojs.push({path,status:r.status(),mainLength:(await n.locator('main').innerText()).length,overflow:await n.evaluate(()=>document.documentElement.scrollWidth-innerWidth),visibleLines:await n.locator('.verse-card .line:visible').count()});await save();
}
out.finished=new Date().toISOString();await save();
}finally{await browser.close();}
