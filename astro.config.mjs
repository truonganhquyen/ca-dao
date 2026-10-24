// @ts-check
// Cấu hình Astro (docs/21 mục 1): xuất trang tĩnh, không adapter.
// Triển khai: Cloudflare Workers Static Assets phục vụ thư mục dist/ (wrangler.jsonc, D-049).
import { defineConfig, fontProviders } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  output: 'static',
  // Phông tự lưu trữ lúc build, chỉ bộ Latin + tiếng Việt (docs/20 mục 1.2).
  fonts: [
    {
      provider: fontProviders.google(),
      name: 'Be Vietnam Pro',
      cssVariable: '--phong-giao-dien',
      weights: [400, 600],
      styles: ['normal'],
      subsets: ['latin', 'vietnamese'],
      fallbacks: ['system-ui', 'sans-serif'],
    },
    {
      provider: fontProviders.google(),
      name: 'Literata',
      cssVariable: '--phong-loi',
      weights: [400],
      styles: ['normal'],
      subsets: ['latin', 'vietnamese'],
      fallbacks: ['Georgia', 'serif'],
    },
  ],
  vite: {
    plugins: [tailwindcss()],
  },
});
