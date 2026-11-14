// Dựng WordPress Playground với plugin, theme V2 và dữ liệu mẫu (wordpress/v2/blueprint.json) cho quet:v2 và e2e:v2.
// Cần Node ≥ 24.18 và Chrome/Chromium; thiếu thì trên CI là lỗi, trên máy là nhắc rồi bỏ qua.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawn, spawnSync } from 'node:child_process';
import { tamPlayground } from './tam-playground.mjs';

const CI = Boolean(process.env.CI);

/** Đường dẫn Chrome/Chromium; thoát (lỗi trên CI, bỏ qua trên máy) khi không có. */
export const timChrome = (ten) => {
  const chrome = [
    process.env.CHROME_PATH,
    '/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium', '/usr/bin/chromium-browser',
    'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
  ].find(p => p && fs.existsSync(p));
  if (!chrome) {
    console.log(`${CI ? 'LỖI ' : 'NHẮC'}     [${ten}] Không tìm thấy Chrome/Chromium (đặt CHROME_PATH).`);
    process.exit(CI ? 1 : 0);
  }
  return chrome;
};

/** Thoát (lỗi trên CI, bỏ qua trên máy) khi Node chưa đủ mới cho Playground. */
export const kiemNode = (ten) => {
  const [chinh, phu] = process.versions.node.split('.').map(Number);
  if (chinh < 24 || (chinh === 24 && phu < 18)) {
    console.log(`${CI ? 'LỖI ' : 'NHẮC'}     [${ten}] Cần Node ≥ 24.18 để chạy Playground (đang dùng ${process.versions.node}) — bỏ qua.`);
    process.exit(CI ? 1 : 0);
  }
};

/**
 * Mở Playground ở cổng `cong`. Blueprint bỏ đăng nhập sẵn (người dùng như khách; ai cần biên tập thì đăng nhập ở
 * /wp-login.php). Trả { G: địa chỉ gốc, san: Promise khi sẵn sàng, dong(): tắt máy chủ và dọn thư mục tạm }.
 */
export const moPlayground = (cong, ten) => {
  const tam = tamPlayground();
  const goc = path.resolve('wordpress', 'v2');
  // Bản sao blueprint không đăng nhập (thanh quản trị thêm JavaScript, làm sai ngân sách; khách thì không có)
  const bp = JSON.parse(fs.readFileSync(path.join(goc, 'blueprint.json'), 'utf8'));
  delete bp.login;
  // Kiểm thử không cần việc nền của WordPress (WP-Cron: kiểm cập nhật, sức khoẻ site, việc nền của plugin) hay mạng ngoài.
  // Trên máy CI chậm mạng, các việc đó giữ máy chủ Playground hàng phút đúng lúc bước kiểm bấm "Áp dụng" một lô
  // (hết giờ chuyển trang 120 giây ngẫu nhiên ở các bước lô, #141–#143). Tắt chúng chỉ trong bản dựng để kiểm; site thật,
  // bản xem trước không đổi.
  bp.steps.unshift({ step: 'defineWpConfigConsts', consts: { DISABLE_WP_CRON: true, WP_HTTP_BLOCK_EXTERNAL: true } });
  const tepBp = path.join(os.tmpdir(), `phongdao-v2-${ten.replace(/\W+/g, '-')}-${process.pid}.json`);
  fs.writeFileSync(tepBp, JSON.stringify(bp));
  const mayChu = spawn('npx', [
    '-y', '@wp-playground/cli@3.1.57', 'server', `--port=${cong}`,
    `--blueprint=${tepBp}`,
    '--mount-dir', path.join(goc, 'plugins', 'ca-dao'), '/wordpress/wp-content/plugins/ca-dao',
    '--mount-dir', path.join(goc, 'themes', 'ca-dao'), '/wordpress/wp-content/themes/ca-dao',
  ], { shell: process.platform === 'win32', detached: process.platform !== 'win32', env: { ...process.env, ...tam.env, MSYS_NO_PATHCONV: '1' } });
  let nhatKy = '';
  const san = new Promise((ok, hong) => {
    const nghe = d => { nhatKy += d; if (/Ready!/.test(nhatKy)) ok(); };
    mayChu.stdout.on('data', nghe);
    mayChu.stderr.on('data', nghe);
    mayChu.on('exit', ma => hong(new Error(`Playground dừng (mã ${ma}):\n${nhatKy.slice(-2000)}`)));
    setTimeout(() => hong(new Error('Playground không sẵn sàng sau 5 phút')), 300_000);
  });
  // Tắt cả cây tiến trình (npx → node Playground); đồng bộ để không để lại máy chủ giữ cổng
  const tat = () => { try { process.platform === 'win32' ? spawnSync('taskkill', ['/pid', String(mayChu.pid), '/T', '/F']) : process.kill(-mayChu.pid); } catch { mayChu.kill(); } };
  const dong = async () => {
    tat();
    await new Promise(ok => setTimeout(ok, 1000)); // chờ tiến trình nhả tệp
    try { tam.don(); } catch { /* tệp còn bị giữ: lần sau dọn */ }
    fs.rmSync(tepBp, { force: true });
  };
  return { G: `http://127.0.0.1:${cong}`, san, dong };
};

/** Đăng nhập admin/password (blueprint); đôi khi hụt ở lần đầu (Playground vừa khởi động) nên thử lại tối đa 3 lần. */
export const dangNhap = async (tab, G, ten) => {
  for (let lan = 0; lan < 3; lan++) {
    await tab.goto(G + '/wp-login.php', { waitUntil: 'networkidle0', timeout: 120_000 });
    await tab.$eval('#user_login', o => { o.value = ''; });
    await tab.type('#user_login', 'admin');
    await tab.type('#user_pass', 'password');
    await Promise.all([tab.waitForNavigation({ waitUntil: 'load', timeout: 120_000 }), tab.click('#wp-submit')]);
    if ((await tab.cookies()).some(c => c.name.startsWith('wordpress_logged_in'))) return true;
    console.log(`NHẮC     [${ten}] đăng nhập lần ${lan + 1} chưa được, thử lại`);
  }
  return false;
};
