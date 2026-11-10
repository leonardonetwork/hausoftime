# HAUS OF TIME — note per lo sviluppo
- Laravel 12, niente Filament: admin Blade in **spagnolo** (cliente spagnolo, coerente con l'admin esistente), stile `layouts/admin`.
- NON modificare la grafica/funzioni dell'Early Access (layouts/app, pages/home, early-access*, watch-evaluation*, seller/*).
- Negozio: `layouts/shop`, `public/css/shop.css`, `public/js/shop.js`, viste `resources/views/shop`, testi `resources/lang/{es,en}/shop.php`.
- Servizi: `App\Services\{SiteMode,Cart,Pricing,OrderService,StripeService}`.
- Versione in `VERSION`; tag git `vX.Y.Z` a ogni rilascio.
- Deploy: `deploy/aggiorna.sh` (root su host.leonardonetwork.eu).
