<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscribersController;
use App\Http\Controllers\Admin\WatchesController as AdminWatchesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EarlyAccessController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\WatchEvaluationController;
use App\Http\Controllers\Shop;
use App\Http\Controllers\Seller\PayoutsController;
use App\Http\Controllers\Seller\SalesController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\CouponsController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\ShippingController;
use Illuminate\Support\Facades\Route;

Route::get('/contacto', [\App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/contacto', [\App\Http\Controllers\ContactController::class, 'send'])->name('contact.send')->middleware('throttle:5,10');

// ── Language switcher ─────────────────────────────────────────────────────────
Route::get('/lang/{locale}', [HomeController::class, 'setLocale'])->name('lang.switch');

// ── Public ───────────────────────────────────────────────────────────────────
Route::get('/',                    [HomeController::class, 'index'])->name('home');
// Landing Founding Sellers (la home early access) sempre raggiungibile, anche a sito live
Route::get('/vender',              [HomeController::class, 'sell'])->name('sell');

// Early Access
Route::prefix('acceso-anticipado')->name('early-access.')->group(function () {
    Route::get('/',          [EarlyAccessController::class, 'index'])->name('index');
    Route::post('/step-1',   [EarlyAccessController::class, 'step1'])->name('step1')->middleware('throttle:10,10');
    Route::get('/vendedor',  [EarlyAccessController::class, 'sellerStep2'])->name('seller-step2');
    Route::post('/vendedor', [EarlyAccessController::class, 'sellerStep2Submit'])->name('seller-step2-submit')->middleware('throttle:10,10');
    Route::get('/gracias',   [EarlyAccessController::class, 'success'])->name('success');
});

// Watch Evaluation
Route::prefix('evaluacion-reloj')->name('evaluation.')->group(function () {
    Route::get('/',         [WatchEvaluationController::class, 'index'])->name('index');
    Route::post('/step-1',  [WatchEvaluationController::class, 'step1'])->name('step1')->middleware('throttle:10,10');
    Route::get('/detalles', [WatchEvaluationController::class, 'step2View'])->name('step2-view');
    Route::post('/evaluar', [WatchEvaluationController::class, 'step2'])->name('step2')->middleware('throttle:6,10'); // chiamate IA a pagamento
});

// Newsletter unsubscribe
Route::get('/newsletter/baja/{token}', function (string $token) {
    $sub = \App\Models\NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();
    $sub->update(['unsubscribed_at' => now()]);
    return view('pages.unsubscribed');
})->name('newsletter.unsubscribe');

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/seller/login',   [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/seller/login',  [LoginController::class, 'login'])->name('login.submit')->middleware('throttle:8,5');
    
    Route::post('/seller/recuperar-contrasena', [\App\Http\Controllers\Auth\LoginController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,10');
Route::get('/seller/reset-contrasena/{token}', [\App\Http\Controllers\Auth\LoginController::class, 'showResetForm'])->name('password.reset');
Route::post('/seller/reset-contrasena', [\App\Http\Controllers\Auth\LoginController::class, 'resetPassword'])->name('password.update');
Route::get('/seller/recuperar-contrasena', [LoginController::class, 'showForgotPassword'])->name('password.request');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Seller Area ───────────────────────────────────────────────────────────────
Route::prefix('seller')->name('seller.')->middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard',       [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/mis-relojes',     [SellerController::class, 'watches'])->name('watches');
    Route::get('/sube-tu-reloj',   [SellerController::class, 'watchCreate'])->name('watches.create');
    Route::post('/sube-tu-reloj',  [SellerController::class, 'watchStore'])->name('watches.store');
    Route::get('/reloj/{watch}/verificacion',  [SellerController::class, 'watchVerification'])->name('watches.verification');
    Route::post('/reloj/{watch}/verificacion', [SellerController::class, 'watchVerificationStore'])->name('watches.verification.store');
    Route::get('/reloj/{watch}',   [SellerController::class, 'watchShow'])->name('watches.show');
    Route::get('/reloj/{watch}/editar', [SellerController::class, 'watchEdit'])->name('watches.edit');
    Route::put('/reloj/{watch}',   [SellerController::class, 'watchUpdate'])->name('watches.update');
    Route::delete('/reloj/{watch}', [SellerController::class, 'watchDestroy'])->name('watches.destroy');
    Route::get('/contactos',       [SellerController::class, 'contacts'])->name('contacts');
    Route::put('/contactos',       [SellerController::class, 'contactsUpdate'])->name('contacts.update');

    // Marketplace: vendite e cobros (Stripe Connect)
    Route::get('/ventas',                    [SalesController::class, 'index'])->name('sales');
    Route::get('/ventas/{item}',             [SalesController::class, 'show'])->name('sales.show');
    Route::post('/ventas/{item}/enviar',     [SalesController::class, 'ship'])->name('sales.ship');
    Route::get('/cobros',                    [PayoutsController::class, 'index'])->name('payouts');
    Route::post('/cobros/conectar',          [PayoutsController::class, 'connect'])->name('payouts.connect');
    Route::get('/cobros/conectar/renovar',   [PayoutsController::class, 'refresh'])->name('payouts.connect.refresh');
    Route::get('/cobros/retorno',            [PayoutsController::class, 'return'])->name('payouts.return');
    Route::get('/cobros/panel-stripe',       [PayoutsController::class, 'dashboard'])->name('payouts.dashboard');
});

// ── Admin Area ────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/',                [DashboardController::class, 'index'])->name('dashboard');

    // Products / Watches
    Route::prefix('productos')->name('watches.')->group(function () {
        Route::get('/',                       [AdminWatchesController::class, 'index'])->name('index');
        Route::get('/{watch}',                [AdminWatchesController::class, 'show'])->name('show');
        Route::get('/{watch}/editar',         [AdminWatchesController::class, 'edit'])->name('edit');
        Route::put('/{watch}',                [AdminWatchesController::class, 'update'])->name('update');
        Route::post('/{watch}/aprobar',       [AdminWatchesController::class, 'approve'])->name('approve');
        Route::post('/{watch}/rechazar',      [AdminWatchesController::class, 'reject'])->name('reject');
        Route::delete('/{watch}',             [AdminWatchesController::class, 'destroy'])->name('destroy');
    });

    // Subscribers / Early Access
    Route::prefix('suscriptores')->name('subscribers.')->group(function () {
        Route::get('/',                           [SubscribersController::class, 'index'])->name('index');
        Route::post('/{earlyAccessRequest}/aprobar',  [SubscribersController::class, 'approveSeller'])->name('approve');
        Route::post('/{earlyAccessRequest}/rechazar', [SubscribersController::class, 'reject'])->name('reject');
    });

    // Newsletter
    Route::prefix('newsletter')->name('newsletter.')->group(function () {
        Route::get('/',                        [NewsletterController::class, 'index'])->name('index');
        Route::get('/crear',                   [NewsletterController::class, 'create'])->name('create');
        Route::post('/',                       [NewsletterController::class, 'store'])->name('store');
        Route::get('/{campaign}/editar',       [NewsletterController::class, 'edit'])->name('edit');
        Route::put('/{campaign}',              [NewsletterController::class, 'update'])->name('update');
        Route::post('/{campaign}/enviar',      [NewsletterController::class, 'send'])->name('send');
        Route::get('/{campaign}/preview',      [NewsletterController::class, 'preview'])->name('preview');
        Route::get('/lista-suscriptores',      [NewsletterController::class, 'subscribersList'])->name('subscribers');
    // Newsletter Lists
    Route::prefix('listas')->name('lists.')->group(function () {
        Route::get('/',                              [\App\Http\Controllers\Admin\NewsletterListsController::class, 'index'])->name('index');
        Route::post('/',                             [\App\Http\Controllers\Admin\NewsletterListsController::class, 'store'])->name('store');
        Route::put('/{newsletterList}',              [\App\Http\Controllers\Admin\NewsletterListsController::class, 'update'])->name('update');
        Route::delete('/{newsletterList}',           [\App\Http\Controllers\Admin\NewsletterListsController::class, 'destroy'])->name('destroy');
        Route::get('/{newsletterList}/suscriptores', [\App\Http\Controllers\Admin\NewsletterListsController::class, 'subscribers'])->name('subscribers');
    });

    });


    // Catalog — Brands
    Route::prefix('marcas')->name('brands.')->group(function () {
        Route::post('/',              [\App\Http\Controllers\Admin\BrandsController::class, 'store'])->name('store');
        Route::put('/{brand}',        [\App\Http\Controllers\Admin\BrandsController::class, 'update'])->name('update');
        Route::delete('/{brand}',     [\App\Http\Controllers\Admin\BrandsController::class, 'destroy'])->name('destroy');
        Route::get('/',               [\App\Http\Controllers\Admin\BrandsController::class, 'index'])->name('index');
    });

    // Catalog — Categories
    Route::prefix('categorias')->name('categories.')->group(function () {
        Route::post('/',               [\App\Http\Controllers\Admin\CategoriesController::class, 'store'])->name('store');
        Route::put('/{category}',      [\App\Http\Controllers\Admin\CategoriesController::class, 'update'])->name('update');
        Route::delete('/{category}',   [\App\Http\Controllers\Admin\CategoriesController::class, 'destroy'])->name('destroy');
        Route::get('/',                [\App\Http\Controllers\Admin\CategoriesController::class, 'index'])->name('index');
    });

    // Sellers
    Route::prefix('vendedores')->name('sellers.')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\Admin\SellersController::class, 'index'])->name('index');
        Route::post('/{user}/aprobar',     [\App\Http\Controllers\Admin\SellersController::class, 'approve'])->name('approve');
        Route::post('/{user}/rechazar',    [\App\Http\Controllers\Admin\SellersController::class, 'reject'])->name('reject');
    });


    Route::prefix('paginas')->name('pages.')->group(function () {
        Route::get('/',                 [\App\Http\Controllers\Admin\PagesController::class, 'index'])->name('index');
        Route::get('/crear',            [\App\Http\Controllers\Admin\PagesController::class, 'create'])->name('create');
        Route::post('/',                [\App\Http\Controllers\Admin\PagesController::class, 'store'])->name('store');
        Route::get('/{page}/editar',    [\App\Http\Controllers\Admin\PagesController::class, 'edit'])->name('edit');
        Route::put('/{page}',           [\App\Http\Controllers\Admin\PagesController::class, 'update'])->name('update');
        Route::delete('/{page}',        [\App\Http\Controllers\Admin\PagesController::class, 'destroy'])->name('destroy');
        Route::post('/{page}/traducir', [\App\Http\Controllers\Admin\PagesController::class, 'translateAi'])->name('translate');
    });
    // Translations
    Route::get('/traducciones',            [\App\Http\Controllers\Admin\TranslationsController::class, 'index'])->name('translations');
    Route::post('/traducciones/traducir',  [\App\Http\Controllers\Admin\TranslationsController::class, 'translateAll'])->name('translations.translate-all');
    Route::post('/traducciones/borrar',    [\App\Http\Controllers\Admin\TranslationsController::class, 'clearTranslations'])->name('translations.clear');


    Route::post('/cache-clear', [\App\Http\Controllers\Admin\CacheController::class, 'clear'])->name('cache.clear');

    // Lanzamiento
    Route::post('/modo-sitio', [SettingsController::class, 'toggleSiteMode'])->name('site-mode');

    // Tienda
    Route::prefix('pedidos')->name('orders.')->group(function () {
        Route::get('/',                                   [OrdersController::class, 'index'])->name('index');
        Route::get('/{order}',                            [OrdersController::class, 'show'])->name('show');
        Route::put('/{order}/notas',                      [OrdersController::class, 'notes'])->name('notes');
        Route::post('/{order}/cancelar',                  [OrdersController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/articulo/{item}/enviar',    [OrdersController::class, 'ship'])->name('ship');
        Route::post('/{order}/articulo/{item}/liberar',   [OrdersController::class, 'release'])->name('release');
        Route::post('/{order}/articulo/{item}/reembolsar',[OrdersController::class, 'refund'])->name('refund');
        Route::post('/{order}/articulo/{item}/cerrar-incidencia', [OrdersController::class, 'closeDispute'])->name('close-dispute');
    });
    Route::resource('cupones', CouponsController::class)->except('show')->parameters(['cupones' => 'coupon'])->names('coupons');
    Route::prefix('envios')->name('shipping.')->group(function () {
        Route::get('/',                         [ShippingController::class, 'index'])->name('index');
        Route::post('/zonas',                   [ShippingController::class, 'zoneStore'])->name('zones.store');
        Route::put('/zonas/{zone}',             [ShippingController::class, 'zoneUpdate'])->name('zones.update');
        Route::delete('/zonas/{zone}',          [ShippingController::class, 'zoneDestroy'])->name('zones.destroy');
        Route::post('/zonas/{zone}/tarifas',    [ShippingController::class, 'rateStore'])->name('rates.store');
        Route::put('/tarifas/{rate}',           [ShippingController::class, 'rateUpdate'])->name('rates.update');
        Route::delete('/tarifas/{rate}',        [ShippingController::class, 'rateDestroy'])->name('rates.destroy');
    });
    Route::get('/paises',              [CountriesController::class, 'index'])->name('countries.index');
    Route::put('/paises/{country}',    [CountriesController::class, 'update'])->name('countries.update');
    Route::post('/paises/masivo',      [CountriesController::class, 'bulk'])->name('countries.bulk');
    // Settings
    Route::prefix('configuracion')->name('settings.')->group(function () {
        Route::get('/',                [SettingsController::class, 'index'])->name('index');
        Route::put('/general',         [SettingsController::class, 'updateGeneral'])->name('general');
        Route::put('/correo',          [SettingsController::class, 'updateMail'])->name('mail');
        Route::put('/ia',              [SettingsController::class, 'updateAi'])->name('ai');
        Route::post('/test-correo',    [SettingsController::class, 'testMail'])->name('test-mail');
        Route::put('/listas',          [SettingsController::class, 'updateLists'])->name('lists');
        Route::put('/lanzamiento',     [SettingsController::class, 'updateLaunch'])->name('launch');
        Route::post('/lanzamiento/token', [SettingsController::class, 'regeneratePreviewToken'])->name('preview-token');
        Route::put('/pagos',           [SettingsController::class, 'updatePayments'])->name('payments');
        Route::post('/pagos/probar',   [SettingsController::class, 'testStripe'])->name('payments.test');
        Route::put('/tienda',          [SettingsController::class, 'updateShop'])->name('shop');
    });
});

// ── Marketplace (live) ───────────────────────────────────────────────────────
Route::post('/stripe/webhook', Shop\StripeWebhookController::class)->name('stripe.webhook');
Route::get('/preview/salir',   [Shop\PreviewController::class, 'leave'])->name('preview.leave');
Route::get('/preview/{token}', [Shop\PreviewController::class, 'enter'])->name('preview.enter')->middleware('throttle:20,1');
Route::get('/vista/{mode}',    [Shop\PreviewController::class, 'switch'])->name('view.switch')->whereIn('mode', ['live', 'early-access']);

Route::middleware('shop')->group(function () {
    Route::get('/relojes',              [Shop\ShopController::class, 'index'])->name('shop.index');
    Route::get('/reloj/{slug}',         [Shop\ShopController::class, 'show'])->name('shop.show');
    Route::get('/buscar',               [Shop\ShopController::class, 'suggest'])->name('shop.suggest')->middleware('throttle:60,1');

    Route::get('/favoritos',            [Shop\FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favoritos/{watch}',   [Shop\FavoriteController::class, 'toggle'])->name('favorites.toggle')->middleware('throttle:60,1');

    // Home alternative per il cliente (noindex)
    Route::get('/home-{variant}',       [Shop\AltHomeController::class, 'show'])->name('home.alt')->whereIn('variant', ['2', '3']);

    Route::get('/comparar',             [Shop\CompareController::class, 'index'])->name('compare.index');
    Route::post('/comparar/{watch}',    [Shop\CompareController::class, 'toggle'])->name('compare.toggle')->middleware('throttle:60,1');
    Route::delete('/comparar',          [Shop\CompareController::class, 'clear'])->name('compare.clear');

    Route::get('/cesta',                [Shop\BagController::class, 'index'])->name('bag.index');
    Route::post('/cesta/{watch}',       [Shop\BagController::class, 'add'])->name('bag.add');
    Route::delete('/cesta/{watch}',     [Shop\BagController::class, 'remove'])->name('bag.remove');
    Route::redirect('/bolsa', '/cesta', 301);

    Route::middleware('guest')->group(function () {
        Route::get('/cuenta/acceso',    [Shop\AuthController::class, 'showLogin'])->name('account.login');
        Route::post('/cuenta/acceso',   [Shop\AuthController::class, 'login'])->name('account.login.submit')->middleware('throttle:8,5');
        Route::get('/cuenta/registro',  [Shop\AuthController::class, 'showRegister'])->name('account.register');
        Route::post('/cuenta/registro', [Shop\AuthController::class, 'register'])->name('account.register.submit')->middleware('throttle:5,10');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/checkout',                 [Shop\CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout/calcular',       [Shop\CheckoutController::class, 'quote'])->name('checkout.quote')->middleware('throttle:30,1');
        Route::post('/checkout',                [Shop\CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:10,5');
        Route::get('/checkout/completado',      [Shop\CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/cancelado/{order}', [Shop\CheckoutController::class, 'cancel'])->name('checkout.cancel');

        Route::prefix('cuenta')->name('account.')->group(function () {
            Route::get('/',                                        [Shop\AccountController::class, 'index'])->name('orders');
            Route::get('/pedidos/{order}',                         [Shop\AccountController::class, 'order'])->name('order');
            Route::post('/pedidos/{order}/articulo/{item}/recibido', [Shop\AccountController::class, 'confirmReceived'])->name('order.received');
            Route::post('/pedidos/{order}/articulo/{item}/incidencia', [Shop\AccountController::class, 'dispute'])->name('order.dispute');
            Route::get('/direcciones',                             [Shop\AccountController::class, 'addresses'])->name('addresses');
            Route::post('/direcciones',                            [Shop\AccountController::class, 'addressStore'])->name('addresses.store');
            Route::put('/direcciones/{address}',                   [Shop\AccountController::class, 'addressUpdate'])->name('addresses.update');
            Route::delete('/direcciones/{address}',                [Shop\AccountController::class, 'addressDestroy'])->name('addresses.destroy');
            Route::get('/datos',                                   [Shop\AccountController::class, 'profile'])->name('profile');
            Route::put('/datos',                                   [Shop\AccountController::class, 'profileUpdate'])->name('profile.update');
        });
    });
});

// ── Static pages ──────────────────────────────────────────────────────────────
// Alias brevi per backward compatibility
Route::get('/terminos',    fn() => redirect('/terminos-y-condiciones', 301))->name('terms');
Route::get('/privacidad',  [\App\Http\Controllers\PageController::class, 'show'])->defaults('slug','privacidad')->name('privacy');
Route::get('/aviso-legal', [\App\Http\Controllers\PageController::class, 'show'])->defaults('slug','aviso-legal')->name('legal');

// Catch-all dinamico per tutte le pagine create dall'admin
// DEVE stare in fondo — viene dopo tutte le route specifiche
Route::get('/{slug}', [\App\Http\Controllers\PageController::class, 'show'])
    ->name('page.show')
    ->where('slug', '[a-zA-Z0-9][a-zA-Z0-9\-]*');
