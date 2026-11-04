// Chỉ thử dữ liệu mẫu ở Playground tại máy; tuyệt đối không nhận site thật.
// node tests/e2e-v2/review-playground.mjs http://127.0.0.1:9436
import fs from 'node:fs/promises';
import { chromium } from '@playwright/test';
import { createRequire } from 'node:module';
const base = process.argv[2];
if (!/^http:\/\/127\.0\.0\.1:\d+$/.test(base || '')) throw new Error('Chỉ nhận Playground trên 127.0.0.1');
const root='docs/e2e-review/screenshots/admin/v2';
await fs.mkdir(root,{recursive:true});await fs.mkdir('test-results/review-v2',{recursive:true});
const axe=await fs.readFile(createRequire(import.meta.url).resolve('axe-core/axe.min.js'),'utf8');
const browser=await chromium.launch({channel:'chrome',headless:true});
const ctx=await browser.newContext({viewport:{width:390,height:844},hasTouch:true});
const p=await ctx.newPage();
const guest=await browser.newContext({viewport:{width:390,height:844}});
await guest.addCookies([{name:'playground_auto_login_already_happened',value:'1',url:base}]);
const g=await guest.newPage();
const results=[];
const check=(v,msg)=>{if(!v)throw new Error(msg);};
const go=(path,tab=p)=>tab.goto(base+path,{waitUntil:'networkidle'});
async function run(name,fn){try{const details=await fn();results.push({name,status:'PASS',details});console.log('PASS',name);}catch(e){const shot=`local-${results.length}.png`;await p.screenshot({path:`${root}/${shot}`,fullPage:true}).catch(()=>{});results.push({name,status:'FAIL',error:e.message,screenshot:shot});console.log('FAIL',name,e.message.split('\n')[0]);}await fs.writeFile('test-results/review-v2/playground.json',JSON.stringify(results,null,2));}
const marker='[E2E-TEST] '+Date.now();
const article='/ca-dao/dong-mau-mot-cua-bai/';
async function saveDialog(){await Promise.all([p.waitForNavigation(),p.locator('dialog[open] button[type=submit]').click()]);await p.waitForLoadState('networkidle');}
try{
await go(article);
await run('Mẫu tự đăng nhập quản trị; khách tách phiên không có nút sửa',async()=>{check(await p.locator('[data-action=mo-tam]').count()>0,'Thiếu nút sửa');await go(article,g);check(await g.locator('[data-action=mo-tam]').count()===0,'Khách nhận nút sửa');});
await run('Ý nghĩa lưu chưa duyệt vào Hộp việc, khách chưa thấy',async()=>{
  await p.locator('[data-action=mo-tam][data-tam=y_nghia]').click();
  check(!await p.locator('#tam-y_nghia [name=duyet]').isChecked(),'Ô duyệt đánh sẵn');
  await p.locator('#tam-y_nghia [name=chu]').fill(marker+' Nội dung thử giao diện, không phải diễn giải văn học.');
  await saveDialog();check((await p.locator('body').innerText()).includes('nháp vào Hộp việc'),'Thiếu thông báo nháp');
  await go(article,g);check(!(await g.locator('body').innerText()).includes(marker),'Nháp lộ cho khách');
  await go('/bien-tap/?loai=nhap_y_nghia');check(await p.locator('article.the-viec').filter({hasText:marker}).count()>0,'Không có thẻ nháp');
});
await run('Hộp việc bỏ nháp thử',async()=>{const card=p.locator('article.the-viec').filter({hasText:marker}).first();const buttons=await card.locator('button[name=quyet]').evaluateAll(es=>es.map(e=>({text:e.innerText,value:e.value})));const discard=buttons.find(b=>/Bỏ/.test(b.text));check(discard,'Không có nút bỏ');await Promise.all([p.waitForNavigation(),card.locator(`button[value="${discard.value}"]`).click()]);return buttons;});
await run('Sửa lời mẫu bằng dấu thử, lưu và Hoàn tác trong 10 giây',async()=>{
  await go(article);await p.locator('[data-action=mo-tam][data-tam=loi]').first().click();
  const before=await p.locator('#tam-loi textarea[name=dong]').inputValue();await p.locator('#tam-loi textarea[name=dong]').fill(before+'\n[E2E-TEST]');await saveDialog();
  check((await p.locator('#phongdao-bao').innerText()).includes('Đã lưu'),'Không báo lưu');
  await Promise.all([p.waitForNavigation(),p.locator('#phongdao-bao button').click()]);
  check((await p.locator('#phongdao-bao').innerText()).includes('Đã hoàn tác'),'Hoàn tác không thành công');
});
await run('Mọi tấm sửa mẫu: mở, giữ dữ liệu, lưu; nguồn trống không tự điền',async()=>{
  const entries=[[article,['chu_thich','di_ban']],['/ve/ve-mau/',['doan','boi_canh']],['/cau-do/cau-do-mau/',['cau_hoi','goi_y','dap_an']],['/tuc-ngu/tuc-ngu-mau-ngan/',['nghia_den','nghia_bong','dung_khi']]];const done=[];
  for(const [path,parts] of entries)for(const part of parts){console.log('PANEL',path,part);await go(path);await p.locator(`[data-action=mo-tam][data-tam=${part}]`).first().locator('xpath=ancestor::details').evaluateAll(es=>es.forEach(e=>e.open=true));await p.locator(`[data-action=mo-tam][data-tam=${part}]`).first().click();const fields=await p.locator('dialog[open] input[name*="nguon"],dialog[open] input[name*="doi_chieu"]').evaluateAll(es=>es.map(e=>({name:e.name,value:e.value})));await saveDialog();check(/Đã lưu|Không có gì thay đổi/.test(await p.locator('#phongdao-bao').innerText()),`Không lưu ${part}`);done.push({part,sourceBlank:fields.every(f=>!f.value)});}return done;
});
await run('Lịch sử và Xem như người đọc',async()=>{await go(article);check((await p.locator('body').innerText()).includes('Lịch sử'),'Thiếu lịch sử');const btn=p.locator('[data-action=xem-nhu-doc-gia]');await btn.click();check(await p.locator('[data-action=mo-tam]:visible').count()===0,'Nút sửa chưa ẩn');await btn.click();check(await p.locator('[data-action=mo-tam]:visible').count()>0,'Không trở lại chế độ sửa');return {history:true};});
let created='';
await run('Thêm bài: chép lại lời MẪU, báo trùng, tên dòng đầu, lưu nháp',async()=>{
  await go(article);await p.locator('[data-action=mo-tam][data-tam=loi]').first().click();const words=await p.locator('#tam-loi textarea[name=dong]').inputValue();
  await go('/bien-tap/them/');await p.locator('form[data-them-bai] [name=loi]').fill(words);
  await p.waitForFunction(()=>document.querySelector('#them-phan-tich')?.textContent.includes('Máy nhận'),{},{timeout:20000});
  const analysis=await p.locator('#them-phan-tich').innerText();check(/Máy nhận/.test(analysis),'Chưa nhận thể thơ');
  check((await p.locator('[name=ten]').inputValue())===words.split('\n')[0],'Tên không lấy dòng đầu');
  const duplicate=(await p.locator('body').innerText()).match(/(?:trùng|giống)[^\n]*/gi);
  await p.locator('[name=ten]').fill(marker+' Bài mẫu kiểm giao diện');
  await Promise.all([p.waitForNavigation(),p.locator('form[data-them-bai] button[name=dang][value="0"]').click()]);created=new URL(p.url()).pathname+new URL(p.url()).search;
  check((await p.locator('body').innerText()).includes('Bản nháp'),'Không lưu nháp');return {analysis,duplicate};
});
await run('Đăng rồi Ẩn bài MẪU, khách nhận đúng trạng thái',async()=>{
  check(created,'Chưa có bài mẫu');await Promise.all([p.waitForNavigation(),p.locator('[data-action=trang-thai][data-trang-thai=publish]').click()]);await p.waitForLoadState('networkidle');created='/?p='+await p.locator('.dai-bien-tap').getAttribute('data-bai');await go(created,g);created=new URL(g.url()).pathname;check((await g.locator('body').innerText()).includes(marker),'Khách chưa thấy bài đã đăng');
  await go(created);await Promise.all([p.waitForNavigation(),p.locator('[data-action=trang-thai][data-trang-thai=private]').click()]);await p.waitForLoadState('networkidle');await go(created,g);check(!(await g.locator('main').innerText()).includes(marker),'Khách vẫn thấy bài ẩn');
});
await run('Khách ghi REST không nonce bị từ chối (chỉ Playground)',async()=>{
  const res=await guest.request.post(base+'/wp-json/phongdao/v1/tac-pham/1/phan/y_nghia',{data:{chu:'[E2E-TEST]'}});check([401,403].includes(res.status()),'Không bị từ chối: '+res.status());return {status:res.status()};
});
await run('Sửa mục từ giữ dữ liệu mẫu, lưu thành công',async()=>{await go('/tu-dien/ben-mau-bac/');await p.locator('#sua-muc-tu summary').click();check(await p.locator('#smt-giai').count()>0,'Thiếu lời giải');await Promise.all([p.waitForNavigation(),p.locator('#sua-muc-tu [type=submit]').click()]);check(/Đã lưu mục từ\.|Không có gì thay đổi\./.test(await p.locator('body').innerText()),'Thiếu báo lưu');});
for(const width of [390,1440])for(const path of ['/bien-tap/','/bien-tap/tac-pham/','/bien-tap/them/','/bien-tap/so-lieu/',article])await run(`Trợ năng ${path} ${width}`,async()=>{
  await p.setViewportSize({width,height:900});const res=await go(path);
  if(path===article)await p.locator('[data-action=mo-tam][data-tam=y_nghia]').click();
  await p.evaluate(axe);const a=await p.evaluate(async()=>{const r=await axe.run(document,{runOnly:{type:'tag',values:['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']}});return r.violations.map(v=>({id:v.id,nodes:v.nodes.map(n=>n.target)}));});
  const overflow=await p.evaluate(()=>document.documentElement.scrollWidth-innerWidth);
  await p.screenshot({path:`${root}/audit-${width}-${path.replace(/\W/g,'_')}.png`,fullPage:true});
  if(path===article){await p.keyboard.press('Escape');check(await p.locator('dialog[open]').count()===0,'Esc không đóng');check(await p.evaluate(()=>document.activeElement?.dataset.action==='mo-tam'),'Esc không trả focus');}
  check(!a.length,JSON.stringify(a));check(overflow<=1,'Tràn ngang '+overflow);check(res.headers()['cache-control']?.includes('private, no-store'),'Thiếu private no-store');return {axe:a,overflow};
});
}finally{await browser.close();}
if(results.some(r=>r.status==='FAIL'))process.exitCode=1;
