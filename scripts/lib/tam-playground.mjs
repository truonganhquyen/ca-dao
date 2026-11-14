// Mỗi lượt kiểm có thư mục tạm riêng: không xoá site do một lượt khác vừa tạo.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

export function tamPlayground() {
  const goc = path.resolve(os.tmpdir());
  const dir = fs.mkdtempSync(path.join(goc, 'phongdao-playground-'));
  return {
    env: { TEMP: dir, TMP: dir, TMPDIR: dir },
    don() {
      const dich = path.resolve(dir);
      if (path.dirname(dich) !== goc || !path.basename(dich).startsWith('phongdao-playground-')) {
        throw new Error('Thư mục tạm Playground nằm ngoài phạm vi lượt kiểm');
      }
      fs.rmSync(dich, { recursive: true, force: true, maxRetries: 6, retryDelay: 200 });
    },
  };
}
