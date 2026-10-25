#!/usr/bin/env bash
# Cập nhật plugin ca-dao trên máy chủ từ GitHub. Thường do tu-cap-nhat.sh gọi sau mỗi PR gộp vào main;
# chạy tay được bằng tài khoản của site:
#   ssh root@<vps> 'cd /home/cadao && sudo -u cadao bash /home/cadao/bin/cap-nhat-plugin.sh'
# NHANH = tên nhánh hoặc mã commit (mặc định main).
# An toàn: kiểm cú pháp PHP trước khi thay; thay thư mục một lần (đổi tên), không để site chạy nửa mã cũ nửa mã mới;
# trang chủ không trả 200 sau khi thay thì trả lại bản cũ.
set -euo pipefail

NHANH=${NHANH:-main}
PHP=php8.5
P=/home/cadao/cadao.sdigi.net/wp-content/plugins
T=$(mktemp -d "$P/.tai-XXXXXX")          # WordPress bỏ qua thư mục bắt đầu bằng dấu chấm
trap 'rm -rf "$T" "$P/.ca-dao-cu"' EXIT

if [[ $NHANH =~ ^[0-9a-f]{40}$ ]]; then ref=$NHANH; else ref=refs/heads/$NHANH; fi
curl -fsSL "https://codeload.github.com/truonganhquyen/ca-dao/tar.gz/$ref" |
	tar -xz -C "$T" --strip-components=3 --wildcards '*/wordpress/plugins/ca-dao'
test -f "$T/ca-dao/ca-dao.php"
find "$T/ca-dao" -name '*.php' -print0 | xargs -0 -n1 "$PHP" -l >/dev/null

trang_chu() { curl -s -o /dev/null -w '%{http_code}' -m 30 --resolve cadao.sdigi.net:443:127.0.0.1 https://cadao.sdigi.net/; }

[ -d "$P/ca-dao" ] && mv "$P/ca-dao" "$P/.ca-dao-cu"
mv "$T/ca-dao" "$P/ca-dao"
if [ "$(trang_chu)" != 200 ] && [ -d "$P/.ca-dao-cu" ]; then
	mv "$P/ca-dao" "$T/hong" && mv "$P/.ca-dao-cu" "$P/ca-dao"
	echo "LỖI: trang chủ không trả 200 với bản $NHANH — đã trả lại bản cũ." >&2
	exit 1
fi
echo "Plugin ca-dao từ $NHANH: $(grep -m1 -oE 'Version: *[0-9.]+' "$P/ca-dao/ca-dao.php")"
