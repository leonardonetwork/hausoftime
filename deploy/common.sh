# Funzioni comuni agli script di deploy (host.leonardonetwork.eu · cPanel/WHM · CloudLinux · Apache)
USER_NAME=${USER_NAME:-hausoftime}
HOME_DIR=/home/$USER_NAME
APP=$HOME_DIR/public_html
REPO_SSH=${REPO_SSH:-git@github-hausoftime:leonardonetwork/hausoftime.git}
BACKUPS=$HOME_DIR/backup-hot

# PHP: quello impostato in .env (PHP_BIN) o il primo 8.4/8.3 disponibile
detect_php() {
  for p in "${PHP:-}" /opt/cpanel/ea-php84/root/usr/bin/php /opt/alt/php84/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php /usr/local/bin/php; do
    [ -n "$p" ] && [ -x "$p" ] && { echo "$p"; return; }
  done
  echo php
}
detect_composer() {
  for c in /opt/cpanel/composer/bin/composer /usr/local/bin/composer "$(command -v composer 2>/dev/null)"; do
    [ -n "$c" ] && [ -f "$c" ] && { echo "$c"; return; }
  done
  echo ""
}
PHP=$(detect_php)
COMPOSER=$(detect_composer)

as_user() { sudo -u "$USER_NAME" -H bash -c "$*"; }
run() { as_user "cd $APP && $*"; }
env_get() { grep -E "^$1=" "$2" | tail -1 | cut -d= -f2- | sed 's/^"//;s/"$//'; }
ok() { echo "✔ $*"; }
info() { echo "▶ $*"; }
die() { echo "✖ $*" >&2; exit 1; }

reload_php() {
  # svuota OPcache: PHP-FPM di cPanel o lsapi di CloudLinux, senza toccare gli altri siti
  /scripts/restartsrv_apache_php_fpm >/dev/null 2>&1 || true
  [ -x /usr/sbin/cagefsctl ] && true
  touch "$APP/public/index.php"
}
