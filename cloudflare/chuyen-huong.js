// Địa chỉ cũ của site Astro (T-076, D-056, D-070): mọi đường dẫn chuyển hẳn (301) sang site WordPress ở phongdao.com,
// giữ nguyên đường dẫn và chuỗi tham số. Site Astro đã gỡ khỏi repo (T-117).
export default {
	fetch(request) {
		const u = new URL(request.url);
		return Response.redirect(`https://phongdao.com${u.pathname}${u.search}`, 301);
	},
};
