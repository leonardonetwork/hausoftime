# HAUS OF TIME

Marketplace di orologi di lusso certificati — **Laravel 12**, Blade, CSS proprio (nessun build Node in produzione).
Cliente: Haus of Time (Madrid). Sviluppo e hosting: Leonardo Network S.r.l.

## Modalità del sito
- **Early Access** (landing attuale, form vendedores/compradores, valutazione IA): grafica e funzioni invariate.
- **Live** (marketplace, pag. 47–48 della presentazione): home a griglia, scheda prodotto, bolsa, checkout, account.
- Commutatore: Admin › Dashboard (“Publicar LIVE”) o Configuración › Lanzamiento.
  In early access il negozio è visibile solo ad admin, email/IP in whitelist e a chi apre il link privato `/preview/{token}`.
  Vista alternata: `/vista/live` e `/vista/early-access`.

## Pagamenti — Stripe Connect (separate charges & transfers)
- L’acquirente paga HOT (Stripe Checkout, `transfer_group` = numero ordine). Fondi trattenuti.
- Il venditore spedisce (FedEx assicurato) entro N giorni lavorativi e inserisce il tracking.
- Liquidazione: alla conferma di ricezione dell’acquirente o automaticamente dopo N giorni dalla spedizione → `Transfer` all’account Express del venditore (prezzo − commissione + spedizione/assicurazione).
- Incidenze e contracargos bloccano la liquidazione; rimborsi e revoca transfer dall’admin.
- Commissioni: standard / founding (Configuración › General). Coupon a carico della piattaforma.
- Chiavi, webhook e modalità test/live: Configuración › Pagos (cifrati nel DB). Webhook: `/stripe/webhook`.

## Spedizioni, paesi, coupon
- Zone e tariffe per pezzo: base + assicurazione % (minimo), gratis sopra soglia, fascia di valore (Admin › Envíos).
- Paesi abilitati e zona (Admin › Países). Coupon % / fisso, marche, minimo, limiti, primo ordine, spedizione gratis.

## Comandi (scheduler: `* * * * * php artisan schedule:run`)
- `hot:expire-reservations` (5 min) · `hot:release-funds` (ogni ora) · `hot:ship-reminders` (09:30) · `queue:work` (ogni minuto)

## Deploy (host.leonardonetwork.eu · cPanel · utente `hausoftime`)
- Prima installazione / migrazione da WordPress: `deploy/installa.sh` (passi: chiave, backup, prepara, attiva, verifica)
- Aggiornamenti: `bash /home/hausoftime/public_html/deploy/aggiorna.sh [tag]`
- Eliminazione definitiva di WordPress: `deploy/pulizia-wordpress.sh --conferma`
