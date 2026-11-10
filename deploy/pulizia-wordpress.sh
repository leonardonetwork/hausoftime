#!/usr/bin/env bash
# HAUS OF TIME — eliminazione definitiva di WordPress (dopo aver verificato il nuovo sito per qualche giorno).
# Il backup fatto in fase di migrazione (file + database) resta in /home/hausoftime/backup-hot/.
#   bash /home/hausoftime/public_html/deploy/pulizia-wordpress.sh            # mostra cosa verrebbe eliminato
#   bash /home/hausoftime/public_html/deploy/pulizia-wordpress.sh --conferma # elimina
set -euo pipefail
[ "$(id -u)" = 0 ] || { echo "Lancia come root"; exit 1; }
source "$(dirname "$(readlink -f "$0")")/common.sh"
OLD=$(cat "$HOME_DIR/.hot-old-public_html" 2>/dev/null || true)
BK=$(cat "$HOME_DIR/.hot-migrazione" 2>/dev/null || true)
WDB=$(cat "$BK/wordpress-db-name.txt" 2>/dev/null || true)
LDB=$(env_get DB_DATABASE "$APP/.env")

echo "Backup migrazione: ${BK:-—}"; ls -lh "$BK" 2>/dev/null || true
echo "Cartella da eliminare: ${OLD:-—} ($(du -sh "$OLD" 2>/dev/null | cut -f1))"
echo "Database WordPress da eliminare: ${WDB:-—}  (database del sito attuale: $LDB)"
[ -n "$WDB" ] && [ "$WDB" = "$LDB" ] && die "Il database WordPress coincide con quello del sito: mi fermo"
ls "$BK"/wordpress-*.sql.gz >/dev/null 2>&1 || die "Non trovo il backup del database WordPress: mi fermo"
[ "${1:-}" = "--conferma" ] || { echo; echo "Nessuna modifica. Per eliminare: $0 --conferma"; exit 0; }

[ -n "$OLD" ] && [ -d "$OLD" ] && rm -rf "$OLD" && ok "Eliminata $OLD"
if [ -n "$WDB" ]; then
  if uapi --user="$USER_NAME" Mysql delete_database name="$WDB" | grep -q "status: 1"; then ok "Database $WDB eliminato"
  else mysql -e "DROP DATABASE \`$WDB\`" && ok "Database $WDB eliminato"; fi
fi
ok "WordPress rimosso. I backup restano in $BACKUPS (la cartella $HOME_DIR/wordpress-backups non è stata toccata)"
