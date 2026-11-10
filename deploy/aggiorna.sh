#!/usr/bin/env bash
# HAUS OF TIME — aggiornamento dopo un push su GitHub. Come root su host.leonardonetwork.eu:
#   bash /home/hausoftime/public_html/deploy/aggiorna.sh            # ultima versione (main)
#   bash /home/hausoftime/public_html/deploy/aggiorna.sh v2.0.1     # versione precisa (tag)
set -euo pipefail
[ "$(id -u)" = 0 ] || { echo "Lancia come root"; exit 1; }
source "$(dirname "$(readlink -f "$0")")/common.sh"
REF=${1:-main}
[ -f "$APP/.env" ] || die "Manca $APP/.env"
P=$(env_get PHP_BIN "$APP/.env"); [ -n "$P" ] && [ -x "$P" ] && PHP=$P

# file creati da comandi lanciati come root → di nuovo all'utente del sito
chown -R "$USER_NAME:$USER_NAME" "$APP/storage" "$APP/bootstrap/cache"
info "Utente $USER_NAME · PHP $($PHP -r 'echo PHP_VERSION;') · versione richiesta: $REF · attuale: $(run 'git describe --tags --always')"

# 1. backup DB (ultimi 15)
mkdir -p "$BACKUPS" && chmod 700 "$BACKUPS"
DB=$(env_get DB_DATABASE "$APP/.env"); DBU=$(env_get DB_USERNAME "$APP/.env"); DBP=$(env_get DB_PASSWORD "$APP/.env")
mysqldump --single-transaction --quick -u"$DBU" -p"$DBP" "$DB" | gzip > "$BACKUPS/$DB-$(date +%Y%m%d-%H%M%S).sql.gz"
ls -1t "$BACKUPS"/*.sql.gz | tail -n +16 | xargs -r rm -f
ok "Backup database"

# 2. codice (manutenzione breve; admin e chi ha il link continuano a vedere)
run "$PHP artisan down --retry=15 --refresh=15 || true"
trap 'echo "✖ Errore: sito in manutenzione. Correggi e rilancia, oppure: sudo -u $USER_NAME $PHP $APP/artisan up"' ERR
run "git fetch --tags --prune -q origin"
if [ "$REF" = "main" ]; then run "git checkout -q main && git reset -q --hard origin/main"; else run "git checkout -q $REF"; fi
ok "Codice: $(run 'git log -1 --format="%h %s"')"

# 3. dipendenze, database, cache
run "$PHP $COMPOSER install --no-dev --optimize-autoloader --no-interaction -q"
run "$PHP artisan migrate --force"
run "$PHP artisan optimize:clear -q && $PHP artisan cache:clear -q && $PHP artisan optimize -q"
run "$PHP artisan up"
trap - ERR
reload_php

URL=$(env_get APP_URL "$APP/.env")
for u in "$URL/" "$URL/acceso-anticipado" "$URL/seller/login"; do
  echo "  $u → HTTP $(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$u")"
done
ok "Aggiornamento completato: $(run 'git describe --tags --always') ($(cat "$APP/VERSION"))"
