// Khảo sát E2E site thật (docs/e2e-review/REPORT.md). Chạy: npm run e2e  (xem tests/e2e/README.md)
// Dùng Chrome đã cài trên máy (channel 'chrome'), không tải trình duyệt riêng.
import { defineConfig } from '@playwright/test';

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
    channel: 'chrome',
    locale: 'vi-VN',
    trace: 'retain-on-failure',
  },
  projects: [
    { name: 'may-tinh', use: { viewport: { width: 1366, height: 900 } }, testIgnore: /quan-tri/ },
    { name: 'dien-thoai', use: { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 }, testIgnore: /quan-tri|bao-mat|tim-kiem/ },
    { name: 'quan-tri', use: { viewport: { width: 1366, height: 900 } }, testMatch: /quan-tri\.spec/ },
  ],
});
