// Khảo sát E2E site thật (docs/e2e-review/REPORT.md). Chạy: npm run e2e  (xem tests/e2e/README.md)
// Dùng Chrome đã cài trên máy (channel 'chrome'), không tải trình duyệt riêng. Máy không có Chrome (ví dụ môi trường agent):
// E2E_TRINH_DUYET=<đường dẫn Chromium>; mạng phải qua proxy: E2E_PROXY=<địa chỉ proxy> (cả trình duyệt lẫn yêu cầu API) — T-130.
import { defineConfig } from '@playwright/test';

const trinhDuyet = process.env.E2E_TRINH_DUYET ? { launchOptions: { executablePath: process.env.E2E_TRINH_DUYET } } : { channel: 'chrome' };
const proxy = process.env.E2E_PROXY ? { proxy: { server: process.env.E2E_PROXY } } : {};

export default defineConfig({
  testDir: '.',
  timeout: 90_000,
  workers: 1, // máy chủ thật, một người dùng: đi chậm, lần lượt
  fullyParallel: false,
  retries: 0,
  reporter: [['list'], ['json', { outputFile: '../../test-results/e2e/ket-qua.json' }]],
  outputDir: '../../test-results/e2e',
  use: {
    baseURL: process.env.E2E_BASE_URL || 'https://phongdao.com',
    ...trinhDuyet,
    ...proxy,
    locale: 'vi-VN',
    trace: 'retain-on-failure',
  },
  projects: [
    { name: 'may-tinh', use: { viewport: { width: 1366, height: 900 } }, testIgnore: /quan-tri/ },
    { name: 'dien-thoai', use: { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 }, testIgnore: /quan-tri|bao-mat|tim-kiem/ },
    { name: 'quan-tri', use: { viewport: { width: 1366, height: 900 } }, testMatch: /quan-tri\.spec/ },
  ],
});
