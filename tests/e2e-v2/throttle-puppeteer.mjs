// Nạp với node --import trước scripts/e2e-v2.mjs --site.
// Kịch bản gốc chặn ghi; lớp này giãn mọi điều hướng chính, kể cả click/reload.
import { CdpPage } from 'puppeteer-core';
const original = CdpPage.prototype.setRequestInterception;
let last = 0;
CdpPage.prototype.setRequestInterception = async function (enabled) {
  if (enabled) {
    const on = this.on.bind(this);
    this.on = (event, listener) => on(event, event === 'request' ? async request => {
      if (request.isNavigationRequest() && request.frame() === this.mainFrame()) {
        await new Promise(resolve => setTimeout(resolve, Math.max(0, 1100 - (Date.now() - last))));
        last = Date.now();
      }
      return listener(request);
    } : listener);
  }
  return original.call(this, enabled);
};
