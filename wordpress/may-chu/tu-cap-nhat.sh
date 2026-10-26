#!/usr/bin/env bash
# Tự cập nhật site từ nhánh main (T-078): cron chạy mỗi 5 phút bằng tài khoản cadao (cai-dat.sh đặt lịch).
# main chỉ nhận thay đổi qua PR đã qua CI, nên gộp PR là lên site trong vài phút. Nhật ký: /home/cadao/cap-nhat.log
set -euo pipefail

H=/home/cadao
B=$H/bin
cd "$H"
sha=$(git ls-remote https://github.com/truonganhquyen/ca-dao refs/heads/main 2>/dev/null | cut -f1)
[[ $sha =~ ^[0-9a-f]{40}$ ]] || exit 0                           # mạng hỏng: thử lại lượt sau
[ "$sha" = "$(cat "$H/.cap-nhat-sha" 2>/dev/null)" ] && exit 0    # đã ở bản này
[ "$sha" = "$(cat "$H/.cap-nhat-loi" 2>/dev/null)" ] && exit 0    # bản này đã hỏng một lần: chờ commit sửa

echo "== $(date '+%F %T') main = ${sha:0:7}"
# Lấy chính các script của đúng commit này (thay tệp bằng mv: bash đang chạy vẫn đọc bản cũ)
for f in cap-nhat-plugin.sh tu-cap-nhat.sh; do
	curl -fsSL -o "$B/.$f.moi" "https://raw.githubusercontent.com/truonganhquyen/ca-dao/$sha/wordpress/may-chu/$f" && mv "$B/.$f.moi" "$B/$f"
done
if NHANH=$sha bash "$B/cap-nhat-plugin.sh"; then
	echo "$sha" > "$H/.cap-nhat-sha"
	# Lịch sử các bản đã lên site (mới nhất ở cuối) — để quay lại bản trước: docs/huong-dan/quay-lai-ban-truoc.md
	echo "$(date '+%F %T') $sha" >> "$H/.cap-nhat-lich-su"
	tail -n 50 "$H/.cap-nhat-lich-su" > "$H/.cap-nhat-lich-su.moi" && mv "$H/.cap-nhat-lich-su.moi" "$H/.cap-nhat-lich-su"
else
	echo "$sha" > "$H/.cap-nhat-loi"
fi
# Giữ nhật ký gọn
if [ -f "$H/cap-nhat.log" ]; then tail -n 500 "$H/cap-nhat.log" > "$H/.cap-nhat.log.moi" && mv "$H/.cap-nhat.log.moi" "$H/cap-nhat.log"; fi
