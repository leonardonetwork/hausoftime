#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
#  HAUS OF TIME — prima installazione del repository in /home/hausoftime/public_html
#  Da lanciare come root su host.leonardonetwork.eu, UN PASSO ALLA VOLTA:
#
#    bash installa-hausoftime-vX.Y.Z.sh chiave      # 1. crea la deploy key e la mostra
#    bash installa-hausoftime-vX.Y.Z.sh backup      # 2. backup completo (file + DB Laravel + DB WordPress)
#    bash installa-hausoftime-vX.Y.Z.sh prepara     # 3. clona il repo in public_html.new, .env, uploads, composer, migrazioni
#    bash installa-hausoftime-vX.Y.Z.sh attiva      # 4. scambia public_html ↔ public_html.new, docroot ea., cron
#    bash installa-hausoftime-vX.Y.Z.sh verifica    # 5. controlli HTTP
#    bash installa-hausoftime-vX.Y.Z.sh rollback    # (solo in caso di problemi) torna alla situazione precedente
#
#  La cancellazione definitiva di WordPress è in deploy/pulizia-wordpress.sh (dopo qualche giorno).
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail
[ "$(id -u)" = 0 ] || { echo "Lancia come root"; exit 1; }

USER_NAME=${USER_NAME:-hausoftime}
HOME_DIR=/home/$USER_NAME
APP=$HOME_DIR/public_html
NEW=$HOME_DIR/public_html.new
OLD_EA=$APP/ea.hausoftime.com
STAMP_FILE=$HOME_DIR/.hot-migrazione
REPO_SSH=git@github-hausoftime:leonardonetwork/hausoftime.git
KEY=$HOME_DIR/.ssh/hausoftime_deploy
BACKUPS=$HOME_DIR/backup-hot

for p in "${PHP:-}" /opt/cpanel/ea-php84/root/usr/bin/php /opt/alt/php84/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php /usr/local/bin/php; do
  [ -n "$p" ] && [ -x "$p" ] && { PHP=$p; break; }
done
COMPOSER=""
for c in /opt/cpanel/composer/bin/composer /usr/local/bin/composer "$(command -v composer 2>/dev/null || true)"; do
  [ -n "$c" ] && [ -f "$c" ] && { COMPOSER=$c; break; }
done

as_user() { sudo -u "$USER_NAME" -H bash -c "$*"; }
env_get() { grep -E "^$1=" "$2" | tail -1 | cut -d= -f2- | sed 's/^"//;s/"$//'; }
env_set() { # env_set FILE KEY VALUE
  local f=$1 k=$2 v=$3
  sed -i "/^$k=/d" "$f"; printf '%s=%s\n' "$k" "$v" >> "$f"
}
ok() { echo "✔ $*"; }
info() { echo "▶ $*"; }
die() { echo "✖ $*" >&2; exit 1; }

id "$USER_NAME" >/dev/null 2>&1 || die "Utente $USER_NAME inesistente"
info "PHP: $PHP ($($PHP -r 'echo PHP_VERSION;'))  ·  Composer: ${COMPOSER:-NON TROVATO}"

step=${1:-}
case "$step" in

# ── 1. Deploy key ────────────────────────────────────────────────────────────
chiave)
  as_user "mkdir -p ~/.ssh && chmod 700 ~/.ssh"
  if [ ! -f "$KEY" ]; then
    as_user "ssh-keygen -t ed25519 -N '' -C 'hausoftime@host.leonardonetwork.eu' -f $KEY -q"
  fi
  if ! grep -q "Host github-hausoftime" "$HOME_DIR/.ssh/config" 2>/dev/null; then
    cat >> "$HOME_DIR/.ssh/config" <<CFG
Host github-hausoftime
  HostName github.com
  User git
  IdentityFile $KEY
  IdentitiesOnly yes
CFG
    chown "$USER_NAME:$USER_NAME" "$HOME_DIR/.ssh/config"; chmod 600 "$HOME_DIR/.ssh/config"
  fi
  as_user "ssh-keyscan -t ed25519 github.com >> ~/.ssh/known_hosts 2>/dev/null; sort -u -o ~/.ssh/known_hosts ~/.ssh/known_hosts"
  echo
  echo "Aggiungi questa chiave su GitHub › leonardonetwork/hausoftime › Settings › Deploy keys (sola lettura):"
  echo; cat "$KEY.pub"; echo
  if as_user "git ls-remote $REPO_SSH HEAD" >/dev/null 2>&1; then ok "Accesso al repository già funzionante"; else echo "(poi rilancia: bash $0 chiave  → deve dire 'Accesso al repository funzionante')"; fi
  ;;

# ── 2. Backup completo ──────────────────────────────────────────────────────
backup)
  as_user "git ls-remote $REPO_SSH HEAD" >/dev/null 2>&1 || die "La deploy key non funziona ancora (passo 'chiave')"
  D=$BACKUPS/migrazione-$(date +%Y%m%d-%H%M%S); mkdir -p "$D"; chmod 700 "$BACKUPS" "$D"
  info "Archivio file di public_html (WordPress + ea.hausoftime.com)…"
  tar -C "$HOME_DIR" -czf "$D/public_html.tar.gz" public_html
  if [ -f "$OLD_EA/.env" ]; then
    DB=$(env_get DB_DATABASE "$OLD_EA/.env"); DBU=$(env_get DB_USERNAME "$OLD_EA/.env"); DBP=$(env_get DB_PASSWORD "$OLD_EA/.env")
    mysqldump --single-transaction --quick --routines -u"$DBU" -p"$DBP" "$DB" | gzip > "$D/laravel-$DB.sql.gz"; ok "DB Laravel $DB"
    cp "$OLD_EA/.env" "$D/ea.env"
  fi
  if [ -f "$APP/wp-config.php" ]; then
    WDB=$(grep -oP "DB_NAME['\"]\s*,\s*['\"]\K[^'\"]+" "$APP/wp-config.php")
    mysqldump --single-transaction --quick "$WDB" | gzip > "$D/wordpress-$WDB.sql.gz"; ok "DB WordPress $WDB"
    echo "$WDB" > "$D/wordpress-db-name.txt"
  fi
  echo "$D" > "$STAMP_FILE"
  ls -lh "$D"; ok "Backup in $D"
  ;;

# ── 3. Preparazione della nuova versione (il sito attuale resta online) ─────
prepara)
  [ -f "$STAMP_FILE" ] || die "Prima esegui il passo 'backup'"
  [ -f "$OLD_EA/.env" ] || die "Non trovo $OLD_EA/.env"
  [ -n "$COMPOSER" ] || die "Composer non trovato"
  rm -rf "$NEW"
  as_user "git clone -q $REPO_SSH $NEW"; ok "Clonato: $(as_user "cd $NEW && git log -1 --format='%h %s'")"

  # .env dall'installazione attuale, con i valori di produzione
  cp "$OLD_EA/.env" "$NEW/.env"; chown "$USER_NAME:$USER_NAME" "$NEW/.env"; chmod 600 "$NEW/.env"
  URL=${APP_URL_NEW:-}
  if [ -z "$URL" ] && [ -f "$APP/wp-config.php" ]; then
    WDB=$(grep -oP "DB_NAME['\"]\s*,\s*['\"]\K[^'\"]+" "$APP/wp-config.php")
    PFX=$(grep -oP "\\\$table_prefix\s*=\s*'\K[^']+" "$APP/wp-config.php" || echo wp_)
    URL=$(mysql -N -B "$WDB" -e "SELECT option_value FROM ${PFX}options WHERE option_name='home' LIMIT 1" 2>/dev/null || true)
  fi
  URL=${URL:-https://hausoftime.com}; URL=${URL%/}; URL=${URL/http:/https:}
  env_set "$NEW/.env" APP_URL "$URL"
  env_set "$NEW/.env" APP_ENV production
  env_set "$NEW/.env" APP_DEBUG false
  env_set "$NEW/.env" APP_TIMEZONE Europe/Madrid
  env_set "$NEW/.env" LOG_LEVEL warning
  env_set "$NEW/.env" SESSION_SECURE_COOKIE true
  env_set "$NEW/.env" CACHE_PREFIX hausoftime_cache
  env_set "$NEW/.env" PHP_BIN "$PHP"
  ok "APP_URL = $URL (canonico: ea.hausoftime.com e l'altra variante www/non-www → 301 qui)"

  # Upload (orologi, verifiche, documenti)
  mkdir -p "$NEW/storage/app/public"
  if [ -d "$OLD_EA/storage/app/public" ]; then
    rsync -a --exclude .gitignore "$OLD_EA/storage/app/public/" "$NEW/storage/app/public/"
  fi
  [ -d "$OLD_EA/storage/app/private" ] && rsync -a --exclude .gitignore "$OLD_EA/storage/app/private/" "$NEW/storage/app/private/" || true
  chown -R "$USER_NAME:$USER_NAME" "$NEW"
  ok "Upload copiati: $(find "$NEW/storage/app/public" -type f | wc -l) file"

  as_user "cd $NEW && $PHP $COMPOSER install --no-dev --optimize-autoloader --no-interaction -q"; ok "Dipendenze"
  as_user "cd $NEW && $PHP artisan migrate --force"; ok "Migrazioni (stesso database: nuove tabelle negozio)"
  # link relativo: resta valido dopo lo spostamento della cartella
  as_user "cd $NEW && rm -rf public/storage && ln -s ../storage/app/public public/storage"
  # niente config:cache qui: i percorsi assoluti verrebbero da public_html.new (si fa in 'attiva')
  as_user "cd $NEW && $PHP artisan optimize:clear -q && $PHP artisan cache:clear -q"
  find "$NEW/storage" "$NEW/bootstrap/cache" -type d -exec chmod 775 {} +
  ok "Pronto in $NEW — il sito attuale è ancora online. Prossimo passo: attiva"
  ;;

# ── 4. Attivazione: scambio cartelle, sottodominio ea., cron ─────────────────
attiva)
  [ -f "$NEW/artisan" ] || die "Prima esegui 'prepara'"
  TS=$(date +%Y%m%d-%H%M%S)
  OLD=$HOME_DIR/public_html.old-$TS
  GROUP=$(stat -c '%G' "$APP"); MODE=$(stat -c '%a' "$APP")
  mv "$APP" "$OLD"
  mv "$NEW" "$APP"
  chown "$USER_NAME:$GROUP" "$APP"; chmod "$MODE" "$APP"
  [ -d "$OLD/.well-known" ] && cp -a "$OLD/.well-known" "$APP/" || true
  [ -d "$OLD/cgi-bin" ] && cp -a "$OLD/cgi-bin" "$APP/" || true
  echo "$OLD" > "$HOME_DIR/.hot-old-public_html"
  ok "public_html attivo (precedente in $OLD)"

  # ea.hausoftime.com: document root = public_html (l'app lo reindirizza al dominio principale)
  if cpapi2 --user="$USER_NAME" SubDomain changedocroot subdomain=ea rootdomain=hausoftime.com dir=public_html 2>/dev/null | grep -q "status: 1"; then
    ok "Sottodominio ea.hausoftime.com → public_html"
  else
    echo "⚠ Cambia a mano: cPanel › Domini › ea.hausoftime.com › Document root = public_html"
  fi

  # Cron dello scheduler Laravel
  CRON="* * * * * cd $APP && $PHP artisan schedule:run >> /dev/null 2>&1"
  CUR=$(crontab -u "$USER_NAME" -l 2>/dev/null | grep -v 'artisan schedule:run' || true)
  printf '%s\n%s\n' "$CUR" "$CRON" | sed '/^$/d' | crontab -u "$USER_NAME" -
  ok "Cron: $CRON"

  sudo -u "$USER_NAME" -H bash -c "cd $APP && $PHP artisan optimize -q"
  /scripts/restartsrv_apache_php_fpm >/dev/null 2>&1 || true
  ok "Attivato. Prossimo passo: verifica"
  ;;

# ── 5. Verifica ─────────────────────────────────────────────────────────────
verifica)
  URL=$(env_get APP_URL "$APP/.env"); H=${URL#https://}
  for u in "$URL/" "$URL/acceso-anticipado" "$URL/evaluacion-reloj" "$URL/seller/login" "$URL/admin" "$URL/relojes" "https://ea.hausoftime.com/" "$URL/.env" "$URL/.git/config" "$URL/composer.json"; do
    printf '  %-48s HTTP %s\n' "$u" "$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$u")"
  done
  echo "  Attesi: 200 · 200 · 200 · 200 · 302(login) · 302(early access) · 301 · 403/404 · 403/404 · 404"
  sudo -u "$USER_NAME" -H bash -c "cd $APP && $PHP artisan about --only=environment 2>/dev/null | head -12"
  tail -n 5 "$APP/storage/logs/laravel.log" 2>/dev/null || true
  ;;

# ── Rollback ────────────────────────────────────────────────────────────────
rollback)
  OLD=$(cat "$HOME_DIR/.hot-old-public_html" 2>/dev/null || true)
  [ -n "$OLD" ] && [ -d "$OLD" ] || die "Nessuna cartella precedente registrata"
  mv "$APP" "$HOME_DIR/public_html.failed-$(date +%Y%m%d-%H%M%S)"
  mv "$OLD" "$APP"
  cpapi2 --user="$USER_NAME" SubDomain changedocroot subdomain=ea rootdomain=hausoftime.com dir=public_html/ea.hausoftime.com >/dev/null 2>&1 || \
    echo "⚠ Ripristina a mano la document root di ea.hausoftime.com = public_html/ea.hausoftime.com"
  CUR=$(crontab -u "$USER_NAME" -l 2>/dev/null | grep -v 'artisan schedule:run' || true)
  printf '%s\n' "$CUR" | sed '/^$/d' | crontab -u "$USER_NAME" - || true
  echo "⚠ Il database contiene le nuove tabelle del negozio: non danno fastidio alla versione precedente."
  ok "Ripristinata la situazione precedente"
  ;;

*)
  sed -n '2,16p' "$0"; exit 1 ;;
esac
