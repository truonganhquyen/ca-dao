#!/usr/bin/env bash
# Diễn tập trên bản sao: lỗi đúng giữa hai lần đổi tên không được làm mất mã đang chạy.
set -euo pipefail
ROOT=$(cd "$(dirname "$0")/.." && pwd)
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
SITE=$TMP/site
mkdir -p "$SITE/wp-content/plugins/ca-dao" "$SITE/wp-content/themes/ca-dao" "$TMP/nguon/wordpress/plugins/ca-dao" "$TMP/nguon/wordpress/themes/ca-dao" "$TMP/bin"
printf 'old plugin\n' > "$SITE/wp-content/plugins/ca-dao/dau.txt"
printf 'old theme\n' > "$SITE/wp-content/themes/ca-dao/dau.txt"
printf '<?php // Version: 9.9.9\n' > "$TMP/nguon/wordpress/plugins/ca-dao/ca-dao.php"
printf '/* Version: 9.9.9 */\n' > "$TMP/nguon/wordpress/themes/ca-dao/style.css"
mkdir -p "$TMP/nguon/repo"
mv "$TMP/nguon/wordpress" "$TMP/nguon/repo/wordpress"
tar -czf "$TMP/goi.tar.gz" -C "$TMP/nguon" repo

cat > "$TMP/bin/curl" <<'MOCK'
#!/usr/bin/env bash
if [[ " $* " == *' -w '* ]]; then
  for ((i=1; i<=$#; i++)); do
    if [[ ${!i} == -o ]]; then j=$((i+1)); printf 'trang chu\n' > "${!j}"; fi
  done
  printf '%s' "${MOCK_HTTP_CODE:-200}"
else
  cat "$MOCK_ARCHIVE"
fi
MOCK
cat > "$TMP/bin/php" <<'MOCK'
#!/usr/bin/env bash
if [[ " $* " == *' -l '* ]]; then exit 0; fi
if [[ " $* " == *' option get '* ]]; then printf '1\n'; fi
exit 0
MOCK
cat > "$TMP/bin/mv" <<'MOCK'
#!/usr/bin/env bash
if [[ ${MOCK_FAIL_NEW_PLUGIN:-0} == 1 && $1 == *"/.tai-"*"/plugins/ca-dao" && $2 == */wp-content/plugins/ca-dao ]]; then
  echo 'Lỗi giả lập khi chuyển plugin mới' >&2
  exit 1
fi
if [[ ${MOCK_FAIL_NEW_THEME:-0} == 1 && $1 == *"/.tai-"*"/themes/ca-dao" && $2 == */wp-content/themes/ca-dao ]]; then
  echo 'Lỗi giả lập khi chuyển theme mới' >&2
  exit 1
fi
exec /usr/bin/mv "$@"
MOCK
chmod +x "$TMP/bin/"*
export PATH="$TMP/bin:$PATH" MOCK_ARCHIVE="$TMP/goi.tar.gz" CADAO_WP="$SITE" CADAO_PHP="$TMP/bin/php" CADAO_WP_CLI="$TMP/wp-cli.phar"

export MOCK_FAIL_NEW_PLUGIN=1
if bash "$ROOT/wordpress/may-chu/cap-nhat-plugin.sh" >"$TMP/loi.log" 2>&1; then
  echo 'LỖI: lần cập nhật giả lập phải thất bại.' >&2; exit 1
fi
grep -q 'Lỗi giả lập khi chuyển plugin mới' "$TMP/loi.log" || { cat "$TMP/loi.log" >&2; echo 'LỖI: chưa chạm tới điểm lỗi cần diễn tập.' >&2; exit 1; }
test "$(cat "$SITE/wp-content/plugins/ca-dao/dau.txt")" = 'old plugin'
test "$(cat "$SITE/wp-content/themes/ca-dao/dau.txt")" = 'old theme'
test ! -e "$SITE/wp-content/plugins/.ca-dao-cu"
test ! -e "$SITE/wp-content/themes/.ca-dao-cu"

export MOCK_FAIL_NEW_PLUGIN=0
bash "$ROOT/wordpress/may-chu/cap-nhat-plugin.sh" >"$TMP/thanh-cong.log" 2>&1 || { cat "$TMP/thanh-cong.log" >&2; exit 1; }
test -f "$SITE/wp-content/plugins/ca-dao/ca-dao.php"
test -f "$SITE/wp-content/themes/ca-dao/style.css"
test ! -e "$SITE/wp-content/plugins/.ca-dao-cu"
test ! -e "$SITE/wp-content/themes/.ca-dao-cu"

export MOCK_FAIL_NEW_THEME=1
if bash "$ROOT/wordpress/may-chu/cap-nhat-plugin.sh" >"$TMP/theme-loi.log" 2>&1; then
  echo 'LỖI: bước chuyển theme giả lập phải thất bại.' >&2; exit 1
fi
grep -q 'Lỗi giả lập khi chuyển theme mới' "$TMP/theme-loi.log" || { cat "$TMP/theme-loi.log" >&2; exit 1; }
test -f "$SITE/wp-content/plugins/ca-dao/ca-dao.php"
test -f "$SITE/wp-content/themes/ca-dao/style.css"
test ! -e "$SITE/wp-content/plugins/.ca-dao-cu"
test ! -e "$SITE/wp-content/themes/.ca-dao-cu"
export MOCK_FAIL_NEW_THEME=0

export MOCK_HTTP_CODE=500
if bash "$ROOT/wordpress/may-chu/cap-nhat-plugin.sh" >"$TMP/trang-loi.log" 2>&1; then
  echo 'LỖI: kiểm trang chủ 500 phải làm cập nhật thất bại.' >&2; exit 1
fi
grep -q 'đang trả lại bản cũ' "$TMP/trang-loi.log" || { cat "$TMP/trang-loi.log" >&2; exit 1; }
test -f "$SITE/wp-content/plugins/ca-dao/ca-dao.php"
test -f "$SITE/wp-content/themes/ca-dao/style.css"
test ! -e "$SITE/wp-content/plugins/.ca-dao-cu"
test ! -e "$SITE/wp-content/themes/.ca-dao-cu"
echo 'ĐẠT: lỗi khi chuyển plugin, theme và trang chủ 500 đều khôi phục bản cũ; lượt cập nhật sau lỗi đầu tiên thành công.'
