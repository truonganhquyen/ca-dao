// Đối chiếu kỹ thuật hai trang đã xác định; không lưu lời hay chú thích nguồn vào repo.
import fs from 'node:fs/promises';import {chromium} from '@playwright/test';
const b=await chromium.launch({channel:'chrome',headless:true});const c=await b.newContext({viewport:{width:390,height:844},serviceWorkers:'block'});let last=0;
await c.route('**/*',async r=>{if(!['GET','HEAD'].includes(r.request().method()))return r.abort();if(r.request().isNavigationRequest()){await new Promise(ok=>setTimeout(ok,Math.max(0,1100-(Date.now()-last))));last=Date.now();}return r.continue();});
const p=await c.newPage();const result=[];try{
for(const [path,source] of [['/dong-dao/con-cong-hay-mua/','https://cadao.me/dong-dao/con-cong-hay-mua/'],['/ca-dao/ai-bao-thuong-ma-anh-khong-noi/','https://cadao.me/the/an-thai/']]){
 await p.goto('https://phongdao.com'+path);const lines=await p.locator('.verse-card .line').evaluateAll(es=>es.map(e=>{const n=e.cloneNode(true);n.querySelectorAll('.ln').forEach(x=>x.remove());return n.textContent.replace(/\s+/g,' ').trim();}));await p.goto(source);const body=(await p.locator('body').innerText()).normalize('NFC').replace(/\s+/g,' ');result.push({path,source,lines:lines.length,matching:lines.filter(l=>body.includes(l.normalize('NFC'))).length});
}
await p.goto('https://phongdao.com/tu-dien/');await Promise.all([p.waitForNavigation(),p.locator('button[name=chu][value=B]').click()]);result.push({dictionaryLetter:p.url(),heading:await p.locator('#tu-dien-kq').innerText()});
await fs.writeFile('test-results/review-v2/source.json',JSON.stringify(result,null,2));console.log(result);
}finally{await b.close();}
