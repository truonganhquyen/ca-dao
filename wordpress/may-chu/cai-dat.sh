#!/usr/bin/env bash
# Cài phần của dự án lên site WordPress đã tạo bằng FlashPanel (T-075, docs/24 mục 8).
# Chạy bằng root, chạy lại bao nhiêu lần cũng được:
#   ssh root@<vps> 'bash -s' < wordpress/may-chu/cai-dat.sh
# Trước đó, trong FlashPanel: site chạy PHP 8.5, chủ sở hữu `cadao` (cách ly), có SSL, đã cài WordPress.
set -euo pipefail

TK=cadao                                  # tài khoản Linux của site (FlashPanel "Update Owner")
D=/home/$TK/cadao.sdigi.net               # thư mục WordPress
PHP=php8.5
WPCLI_BAN=2.12.0
NHANH=${NHANH:-main}                      # nhánh GitHub lấy plugin (thử một nhánh: NHANH=<nhánh> bash -s)

# Chạy từ thư mục site: WP-CLI mở tiến trình phụ, mà tài khoản cách ly không vào được thư mục hiện tại của root
cd "$D"
wp() { sudo -u "$TK" "$PHP" -d error_reporting='E_ALL & ~E_DEPRECATED' "/home/$TK/bin/wp-cli.phar" --path="$D" "$@"; }

echo "== Cách ly (bổ sung cho FlashPanel)"
# FlashPanel thêm tài khoản cách ly vào nhóm www-data; nhóm đó gửi được lệnh tới pool PHP chung của các site khác
gpasswd -d "$TK" www-data >/dev/null 2>&1 || true
chmod 600 "$D/wp-config.php"              # mật khẩu CSDL: chỉ tài khoản của site đọc được
chmod 755 "$D"
systemctl reload "$PHP-fpm"

echo "== WP-CLI $WPCLI_BAN"
install -d -o "$TK" -g "$TK" "/home/$TK/bin"
if [ "$(sudo -u "$TK" "$PHP" -d error_reporting=0 "/home/$TK/bin/wp-cli.phar" --version 2>/dev/null)" != "WP-CLI $WPCLI_BAN" ]; then
	u=https://github.com/wp-cli/wp-cli/releases/download/v$WPCLI_BAN/wp-cli-$WPCLI_BAN.phar
	curl -fsSL -o /tmp/wp-cli.phar "$u"
	echo "$(curl -fsSL "$u.sha512" | awk '{print $1}')  /tmp/wp-cli.phar" | sha512sum -c --quiet -
	install -o "$TK" -g "$TK" -m 755 /tmp/wp-cli.phar "/home/$TK/bin/wp-cli.phar"
	rm -f /tmp/wp-cli.phar
fi
# Lối tắt cho người vận hành: sudo -u cadao /home/cadao/bin/wp <lệnh>
cat > "/home/$TK/bin/wp" <<SH
#!/bin/sh
exec $PHP -d error_reporting='E_ALL & ~E_DEPRECATED' /home/$TK/bin/wp-cli.phar --path=$D "\$@"
SH
chown "$TK:$TK" "/home/$TK/bin/wp"; chmod 755 "/home/$TK/bin/wp"

echo "== Tiếng Việt, múi giờ, đường dẫn"
wp language core install vi --activate
wp option update timezone_string Asia/Ho_Chi_Minh
wp option update date_format 'd/m/Y'
wp option update time_format 'H:i'
wp option update start_of_week 1
wp rewrite structure '/%postname%/'

echo "== Chặn công cụ tìm kiếm tới khi mở công khai (H5, T-053)"
wp option update blog_public 0

echo "== Cấu hình an toàn"
wp config set DISALLOW_FILE_EDIT true --raw   # không sửa mã plugin/theme trong trang quản trị
wp config set DISABLE_WP_CRON true --raw      # lịch chạy bằng cron của máy chủ (bên dưới)
chmod 600 "$D/wp-config.php"

echo "== Bỏ plugin mẫu không dùng"
for p in akismet hello; do wp plugin is-installed "$p" && wp plugin delete "$p"; done

echo "== Plugin ca-dao từ nhánh $NHANH"
curl -fsSL -o "/home/$TK/bin/cap-nhat-plugin.sh" "https://raw.githubusercontent.com/truonganhquyen/ca-dao/$NHANH/wordpress/may-chu/cap-nhat-plugin.sh"
chown "$TK:$TK" "/home/$TK/bin/cap-nhat-plugin.sh"
sudo -u "$TK" env NHANH="$NHANH" bash "/home/$TK/bin/cap-nhat-plugin.sh"
wp plugin is-active ca-dao || wp plugin activate ca-dao

echo "== Xác thực hai lớp (plugin Two Factor của WordPress.org; mỗi người tự bật trong Hồ sơ)"
wp plugin is-installed two-factor || wp plugin install two-factor
wp plugin is-active two-factor || wp plugin activate two-factor
wp plugin auto-updates enable two-factor >/dev/null 2>&1 || true   # báo lỗi nếu đã bật sẵn
wp rewrite flush

echo "== Cron thật: chạy lịch WordPress mỗi phút (Nguồn tự động cần 5 phút một lượt)"
cat > /etc/cron.d/cadao-wordpress <<CRON
# T-075: lịch WordPress của cadao.sdigi.net (wp-config: DISABLE_WP_CRON)
* * * * * $TK flock -n /home/$TK/.wp-cron.lock $PHP -d error_reporting=0 /home/$TK/bin/wp-cli.phar --path=$D cron event run --due-now --quiet
CRON
chmod 644 /etc/cron.d/cadao-wordpress

echo "== Kết quả"
wp core version
wp option get blog_public
wp plugin list --fields=name,status,version
wp cron event list --fields=hook,next_run_relative
