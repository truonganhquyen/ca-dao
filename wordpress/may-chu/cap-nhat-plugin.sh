#!/usr/bin/env bash
# Cập nhật plugin và theme ca-dao trên máy chủ từ GitHub. Thường do tu-cap-nhat.sh gọi sau mỗi PR gộp vào main;
# chạy tay được bằng tài khoản của site:
#   ssh root@<vps> 'cd /home/cadao && sudo -u cadao bash /home/cadao/bin/cap-nhat-plugin.sh'
# NHANH = tên nhánh hoặc mã commit (mặc định main).
# An toàn: kiểm cú pháp PHP trước khi thay; thay thư mục một lần (đổi tên), không để site chạy nửa mã cũ nửa mã mới;
# trang chủ không trả 200 sau khi thay thì trả lại bản cũ. Theme ca-dao được bật MỘT lần (T-072); người quản trị
# đổi sang theme khác sau đó thì giữ theo lựa chọn của họ.
set -euo pipefail

NHANH=${NHANH:-main}
PHP=php8.5
WP=/home/cadao/cadao.sdigi.net
T=$(mktemp -d "$WP/wp-content/.tai-XXXXXX")      # WordPress bỏ qua thư mục bắt đầu bằng dấu chấm
trap 'rm -rf "$T" "$WP/wp-content/plugins/.ca-dao-cu" "$WP/wp-content/themes/.ca-dao-cu"' EXIT
wp() { "$PHP" -d error_reporting=0 /home/cadao/bin/wp-cli.phar --path="$WP" "$@"; }

if [[ $NHANH =~ ^[0-9a-f]{40}$ ]]; then ref=$NHANH; else ref=refs/heads/$NHANH; fi
curl -fsSL "https://codeload.github.com/truonganhquyen/ca-dao/tar.gz/$ref" |
	tar -xz -C "$T" --strip-components=2 --wildcards '*/wordpress/plugins/ca-dao' '*/wordpress/themes/ca-dao'
test -f "$T/plugins/ca-dao/ca-dao.php"
find "$T" -name '*.php' -print0 | xargs -0 -n1 "$PHP" -l >/dev/null

trang_chu() { curl -s -o /dev/null -w '%{http_code}' -m 30 --resolve cadao.sdigi.net:443:127.0.0.1 https://cadao.sdigi.net/; }

thay=()
moi_bat=
for loai in plugins themes; do
	[ -d "$T/$loai/ca-dao" ] || continue
	d=$WP/wp-content/$loai
	[ -d "$d/ca-dao" ] && mv "$d/ca-dao" "$d/.ca-dao-cu"
	mv "$T/$loai/ca-dao" "$d/ca-dao"
	thay+=("$loai")
done
if [ -d "$WP/wp-content/themes/ca-dao" ] && [ -z "$(wp option get cadao_theme_da_bat 2>/dev/null)" ]; then
	wp theme activate ca-dao >/dev/null && wp option update cadao_theme_da_bat "$(date +%F)" >/dev/null && moi_bat=1
	echo "Đã bật theme ca-dao (lần đầu)."
fi
if [ "$(trang_chu)" != 200 ]; then
	for loai in "${thay[@]}"; do
		d=$WP/wp-content/$loai
		[ -d "$d/.ca-dao-cu" ] && mv "$d/ca-dao" "$T/hong-$loai" && mv "$d/.ca-dao-cu" "$d/ca-dao"
	done
	if [ -n "$moi_bat" ]; then wp theme activate twentytwentyfive >/dev/null; wp option delete cadao_theme_da_bat >/dev/null; fi
	echo "LỖI: trang chủ không trả 200 với bản $NHANH — đã trả lại bản cũ." >&2
	exit 1
fi
echo "Plugin ca-dao từ $NHANH: $(grep -m1 -oE 'Version: *[0-9.]+' "$WP/wp-content/plugins/ca-dao/ca-dao.php")" \
	"· theme: $(grep -m1 -oE 'Version: *[0-9.]+' "$WP/wp-content/themes/ca-dao/style.css" 2>/dev/null || echo 'chưa có')"
