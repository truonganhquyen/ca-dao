#!/usr/bin/env node
// Kiểm thử vẽ ảnh chia sẻ (T-050) bằng PHP thật có GD + FreeType: `npm run test:anh`.
// WordPress Playground không vẽ được chữ bằng GD nên phần này chạy ngoài Playground. PHP lấy từ biến CADAO_PHP (đường
// dẫn tới php), mặc định `php` trong PATH; thêm tham số qua CADAO_PHP_THAM (ví dụ "-d extension=gd").
// Máy không có PHP: NHẮC và bỏ qua; trên CI thì báo lỗi.
import { spawnSync } from 'node:child_process';

const php = process.env.CADAO_PHP || 'php';
const tham = (process.env.CADAO_PHP_THAM || '').split(' ').filter(Boolean);
const co = spawnSync(php, [...tham, '-r', 'echo PHP_VERSION;'], { encoding: 'utf8' });
if (co.status !== 0) {
  console.log(`NHẮC     [ảnh chia sẻ] Không tìm thấy PHP (${php}) — bỏ qua. Đặt CADAO_PHP để chạy tại máy; CI luôn chạy.`);
  process.exit(process.env.CI ? 1 : 0);
}
const r = spawnSync(php, [...tham, 'wordpress/tests/anh-chia-se.php'], { encoding: 'utf8' });
process.stdout.write(r.stdout || '');
process.stderr.write(r.stderr || '');
process.exit(r.status === 0 && (r.stdout || '').includes('KẾT QUẢ: ĐẠT') ? 0 : 1);
