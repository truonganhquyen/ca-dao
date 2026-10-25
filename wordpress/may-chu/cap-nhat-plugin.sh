#!/usr/bin/env bash
# Cập nhật plugin ca-dao trên máy chủ từ GitHub (mặc định nhánh main), sau mỗi PR chạm wordpress/plugins/ đã gộp.
# Chạy bằng tài khoản của site (cai-dat.sh đặt sẵn tệp này):
#   ssh root@<vps> 'sudo -u cadao bash /home/cadao/bin/cap-nhat-plugin.sh'
# Thay thư mục plugin một lần (đổi tên), không để site chạy nửa mã cũ nửa mã mới.
set -euo pipefail

NHANH=${NHANH:-main}
P=/home/cadao/cadao.sdigi.net/wp-content/plugins
T=$(mktemp -d "$P/.tai-XXXXXX")          # WordPress bỏ qua thư mục bắt đầu bằng dấu chấm
trap 'rm -rf "$T" "$P/.ca-dao-cu"' EXIT

curl -fsSL "https://codeload.github.com/truonganhquyen/ca-dao/tar.gz/refs/heads/$NHANH" |
	tar -xz -C "$T" --strip-components=3 --wildcards '*/wordpress/plugins/ca-dao'
test -f "$T/ca-dao/ca-dao.php"

[ -d "$P/ca-dao" ] && mv "$P/ca-dao" "$P/.ca-dao-cu"
mv "$T/ca-dao" "$P/ca-dao"
echo "Plugin ca-dao từ nhánh $NHANH: $(grep -m1 -oE 'Version: *[0-9.]+' "$P/ca-dao/ca-dao.php")"
