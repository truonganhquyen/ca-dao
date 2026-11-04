#!/usr/bin/env bash
# Cập nhật plugin và theme ca-dao trên máy chủ từ GitHub. Thường do tu-cap-nhat.sh gọi sau mỗi PR gộp vào main;
# chạy tay được bằng tài khoản của site:
#   ssh root@<vps> 'cd /home/cadao && sudo -u cadao bash /home/cadao/bin/cap-nhat-plugin.sh'
# NHANH = tên nhánh hoặc mã commit (mặc định main).
# An toàn: kiểm cú pháp PHP trước khi thay; thay thư mục một lần (đổi tên), không để site chạy nửa mã cũ nửa mã mới;
# trang chủ không trả 200 sau khi thay thì trả lại bản cũ. Theme ca-dao được bật MỘT lần (T-072); người quản trị
# đổi sang theme khác sau đó thì giữ theo lựa chọn của họ.
# Nguồn: commit có tệp wordpress/nguon-trien-khai ghi "v2" (từ ngày chuyển, docs/29 mục 5) thì lấy wordpress/v2/plugins|themes/ca-dao;
# không có thì lấy wordpress/plugins|themes/ca-dao (MVP) — quay về một commit cũ bằng NHANH=<mã> vẫn đúng mã của commit đó.
set -euo pipefail

NHANH=${NHANH:-main}
PHP=php8.5
WP=/home/cadao/phongdao.com
T=$(mktemp -d "$WP/wp-content/.tai-XXXXXX")      # WordPress bỏ qua thư mục bắt đầu bằng dấu chấm
trap 'rm -rf "$T" "$WP/wp-content/plugins/.ca-dao-cu" "$WP/wp-content/themes/.ca-dao-cu"' EXIT
wp() { "$PHP" -d error_reporting=0 /home/cadao/bin/wp-cli.phar --path="$WP" "$@"; }

if [[ $NHANH =~ ^[0-9a-f]{40}$ ]]; then ref=$NHANH; else ref=refs/heads/$NHANH; fi
curl -fsSL "https://codeload.github.com/truonganhquyen/ca-dao/tar.gz/$ref" |
	tar -xz -C "$T" --strip-components=1 --wildcards '*/wordpress/*'
N=$T/wordpress
[ "$(tr -d '[:space:]' 2>/dev/null < "$N/nguon-trien-khai")" = v2 ] && N=$N/v2
for loai in plugins themes; do [ -d "$N/$loai/ca-dao" ] && mv "$N/$loai/ca-dao" "$T/$loai-ca-dao"; done
rm -rf "$T/wordpress"
mkdir -p "$T/plugins" "$T/themes"
for loai in plugins themes; do [ -d "$T/$loai-ca-dao" ] && mv "$T/$loai-ca-dao" "$T/$loai/ca-dao"; done
test -f "$T/plugins/ca-dao/ca-dao.php"
test -f "$T/themes/ca-dao/style.css"                  # thiếu theme thì dừng: không để plugin mới chạy với theme cũ
find "$T" -name '*.php' -print0 | xargs -0 -n1 "$PHP" -l >/dev/null

# Quy tắc đường dẫn lưu trong cơ sở dữ liệu: MVP và V2 khác nhau (V2: ca-dao/<tên> → tac_pham), mỗi bên chỉ tự làm mới
# khi số phiên bản quy tắc của chính nó đổi — nên sau mỗi lần thay mã (cả khi trả lại bản cũ) làm mới bằng mã đang chạy.
# Không --hard (không ghi .htaccess); lỗi ở đây không chặn bước kiểm trang chủ.
lam_moi_duong_dan() { wp rewrite flush >/dev/null 2>&1 || echo "NHẮC: chưa làm mới được quy tắc đường dẫn." >&2; }

trang_chu() { curl -s -o "$T/trang-chu.html" -w '%{http_code}' -m 30 --resolve phongdao.com:443:127.0.0.1 https://phongdao.com/; }
# Trang chủ ổn: 200; hoặc V2 vừa thay chỗ MVP mà chưa nhập dữ liệu cũ — khách nhận trang "đang được nâng cấp" (503, đúng ý,
# docs/29 mục 5 bước 2–3), không trả lại bản cũ
trang_on() {
	local ma; ma=$(trang_chu)
	[ "$ma" = 200 ] && return 0
	if [ "$ma" = 503 ] && grep -q 'Thư viện đang được nâng cấp' "$T/trang-chu.html"; then echo "Trang chủ: đang nâng cấp (503) — chờ nhập dữ liệu cũ."; return 0; fi
	return 1
}

thay=()
moi_bat=
for loai in plugins themes; do
	[ -d "$T/$loai/ca-dao" ] || continue
	d=$WP/wp-content/$loai
	[ -d "$d/ca-dao" ] && mv "$d/ca-dao" "$d/.ca-dao-cu"
	mv "$T/$loai/ca-dao" "$d/ca-dao"
	thay+=("$loai")
done
lam_moi_duong_dan
if [ -d "$WP/wp-content/themes/ca-dao" ] && [ -z "$(wp option get cadao_theme_da_bat 2>/dev/null)" ]; then
	wp theme activate ca-dao >/dev/null && wp option update cadao_theme_da_bat "$(date +%F)" >/dev/null && moi_bat=1
	echo "Đã bật theme ca-dao (lần đầu)."
fi
if ! trang_on; then
	for loai in "${thay[@]}"; do
		d=$WP/wp-content/$loai
		[ -d "$d/.ca-dao-cu" ] && mv "$d/ca-dao" "$T/hong-$loai" && mv "$d/.ca-dao-cu" "$d/ca-dao"
	done
	if [ -n "$moi_bat" ]; then wp theme activate twentytwentyfive >/dev/null; wp option delete cadao_theme_da_bat >/dev/null; fi
	lam_moi_duong_dan
	echo "LỖI: trang chủ không trả 200 (hay trang đang nâng cấp) với bản $NHANH — đã trả lại bản cũ." >&2
	exit 1
fi
echo "Plugin ca-dao từ $NHANH: $(grep -m1 -oE 'Version: *[0-9.]+' "$WP/wp-content/plugins/ca-dao/ca-dao.php")" \
	"· theme: $(grep -m1 -oE 'Version: *[0-9.]+' "$WP/wp-content/themes/ca-dao/style.css" 2>/dev/null || echo 'chưa có')"
