import fs from 'node:fs/promises';
import {chromium} from '@playwright/test';
const base=process.argv[2];if(!/^http:\/\/127\.0\.0\.1:\d+$/.test(base||''))throw Error('Chỉ Playground tại máy');
const b=await chromium.launch({channel:'chrome',headless:true});const p=await b.newPage({viewport:{width:1440,height:900}});const out={};
try{
await p.goto(base+'/bien-tap/?tung=1');out.nextAvailable=await p.getByText('Thẻ sau',{exact:true}).count();out.before=p.url();await p.keyboard.press('j');await p.waitForTimeout(300);out.afterJ=p.url();await p.keyboard.press('1');await p.waitForTimeout(300);out.after1=p.url();await p.screenshot({path:'docs/e2e-review/screenshots/admin/v2/queue-shortcuts-1440.png'});
await p.goto(base+'/bien-tap/');const first=p.locator('article.the-viec').filter({has:p.locator('button[value=de_sau]')}).first();out.cardId=await first.getAttribute('id');await Promise.all([p.waitForNavigation(),first.locator('button[value=de_sau]').click()]);out.message=await p.locator('[role=status]').allTextContents();out.undoButtons=await p.getByRole('button',{name:'Hoàn tác',exact:true}).count();await p.setViewportSize({width:390,height:844});await p.screenshot({path:'docs/e2e-review/screenshots/admin/v2/queue-no-undo-390.png'});
await fs.writeFile('test-results/review-v2/queue-shortcuts.json',JSON.stringify(out,null,2));console.log(out);
}finally{await b.close();}
