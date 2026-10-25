// Site Astro cũ đã ngừng (T-076, D-056): mọi đường dẫn ở địa chỉ cũ chuyển hẳn (301) sang site WordPress, giữ nguyên
// đường dẫn (/ca-dao/<tên>/… trùng cách đặt đường dẫn của WordPress). Mã Astro và content/ vẫn ở repo làm lưu trữ.
export default {
	fetch(request) {
		const u = new URL(request.url);
		return Response.redirect(`https://cadao.sdigi.net${u.pathname}${u.search}`, 301);
	},
};
