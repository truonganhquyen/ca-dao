// Lô thao tác (T-171): đang tự chạy thì tự bấm "Tiếp tục" sau mỗi lượt; tắt JavaScript thì người bấm từng lượt.
document.querySelectorAll('.phongdao-admin-lo [data-chi-js]').forEach(e => { e.hidden = false; });
const lo = document.querySelector('.phongdao-admin-lo form[data-tu-chay]');
if (lo) setTimeout(() => lo.querySelector('[data-tiep]')?.click(), 400);
