@php
  $locale = app()->getLocale();
  $navBrands = \App\Models\WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->get();
  $navCategories = \App\Models\WatchCategory::active()->orderBy('sort_order')->orderBy('name_es')->get();
  $footerPages = \App\Models\Page::active()->orderBy('sort_order')->get();
  $authUser = auth()->user();
  $pubCounts = \App\Models\Watch::published()->selectRaw('brand, category')->get();
  $brandCount = fn ($b) => $pubCounts->whereIn('brand', array_filter([$b->name_es, $b->name_en]))->count();
  $catCount = fn ($c) => $pubCounts->whereIn('category', array_filter([$c->name_es, $c->name_en]))->count();
  $featured = \App\Models\Watch::published()->orderByDesc('is_featured')->latest('approved_at')->first();
  $favCount = $authUser ? $authUser->favorites()->count() : count(session('hot_favorites', []));
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', __('shop.meta.title'))</title>
  <meta name="description" content="@yield('description', __('shop.meta.description'))">
  @hasSection('canonical')<link rel="canonical" href="@yield('canonical')">@endif
  @if(!$siteMode->isLive())<meta name="robots" content="noindex, nofollow">@endif
  <meta property="og:site_name" content="HAUS OF TIME">
  <meta property="og:title" content="@yield('title', __('shop.meta.title'))">
  <meta property="og:description" content="@yield('description', __('shop.meta.description'))">
  <meta property="og:image" content="@yield('og_image', asset('images/shop/gallery.jpg'))">
  <meta name="theme-color" content="#f3f1ee">
  <link rel="icon" type="image/png" href="/images/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="preload" as="image" href="/images/shop/gallery.webp" type="image/webp">
  <link rel="stylesheet" href="/css/shop.css?v={{ filemtime(public_path('css/shop.css')) }}">
  @stack('head')
</head>
<body class="hs @yield('body_class')">
  <div class="hs-bg" aria-hidden="true"></div>

  @if(!$siteMode->isLive() && $siteMode->isPrivileged())
    <div class="hs-preview">
      <b>Preview</b><span>{{ __('shop.preview.bar') }}</span>
      <a href="{{ route('view.switch', 'live') }}" class="{{ $siteMode->showsLive() ? 'on' : '' }}">{{ __('shop.preview.see_live') }}</a>
      <a href="{{ route('view.switch', 'early-access') }}" class="{{ $siteMode->showsLive() ? '' : 'on' }}">{{ __('shop.preview.see_ea') }}</a>
      @if(request()->cookie(\App\Services\SiteMode::COOKIE))<a href="{{ route('preview.leave') }}">{{ __('shop.preview.leave') }}</a>@endif
    </div>
  @endif

  <header class="hs-header" id="hsHeader">
    <div class="hs-header__inner">
      <button class="hs-burger" type="button" aria-label="{{ __('shop.nav.menu') }}" aria-controls="hsDrawer" aria-expanded="false" data-drawer-open>
        <span></span><span></span><span></span>
      </button>
      <a href="{{ route('home') }}" class="hs-logo" aria-label="HAUS OF TIME">
        <img src="/images/logo-dark.png" alt="HAUS OF TIME" width="300" height="37">
      </a>
      <form class="hs-search" action="{{ route('shop.index') }}" method="GET" role="search" autocomplete="off" data-suggest="{{ route('shop.suggest') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('shop.nav.search') }}" aria-label="{{ __('shop.nav.search') }}" maxlength="80">
        <button type="submit" aria-label="{{ __('shop.nav.search') }}">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21"/></svg>
        </button>
        <div class="hs-suggest" role="listbox" data-all="{{ __('shop.nav.search_all') }}"></div>
      </form>
      <div class="hs-actions">
        <a href="{{ route('favorites.index') }}" class="hs-icon-btn fav" aria-label="{{ __('shop.nav.favorites') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.5 8 3.6 4.5 7.1 4.5c2 0 3.4 1.1 4.9 2.9 1.5-1.8 2.9-2.9 4.9-2.9 3.5 0 5.6 3.5 4.4 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>
          <span class="hs-badge" data-fav-count data-count="{{ $favCount }}">{{ $favCount }}</span>
        </a>
        <a href="{{ $authUser ? ($authUser->isAdmin() ? route('admin.dashboard') : route('account.orders')) : route('account.login') }}" class="hs-icon-btn" aria-label="{{ __('shop.nav.account') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.2-4 4.3-6 8-6s6.8 2 8 6"/></svg>
        </a>
        <a href="{{ route('bag.index') }}" class="hs-icon-btn" aria-label="{{ __('shop.nav.bag') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 8h14l-1.2 12.2a1 1 0 0 1-1 .8H7.2a1 1 0 0 1-1-.8L5 8z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/></svg>
          <span class="hs-badge" data-bag-count data-count="{{ $bagCount }}">{{ $bagCount }}</span>
        </a>
        <div class="hs-lang">
          <a href="{{ route('lang.switch', 'es') }}" class="{{ $locale === 'es' ? 'on' : '' }}">ES</a>
          <a href="{{ route('lang.switch', 'en') }}" class="{{ $locale === 'en' ? 'on' : '' }}">EN</a>
        </div>
      </div>
    </div>
  </header>

  {{-- Menu laterale --}}
  <div class="hs-drawer" id="hsDrawer" aria-hidden="true">
    <div class="hs-drawer__scrim" data-drawer-close></div>
    <aside class="hs-drawer__panel" role="dialog" aria-modal="true" aria-label="{{ __('shop.nav.menu') }}">
      <div class="hs-drawer__top">
        <a href="{{ route('home') }}"><img src="/images/logo-dark.png" alt="HAUS OF TIME"></a>
        <button class="hs-close" type="button" data-drawer-close aria-label="{{ __('shop.nav.close') }}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 5l14 14M19 5 5 19"/></svg>
        </button>
      </div>

      <nav class="hs-dnav" aria-label="{{ __('shop.nav.menu') }}">
        @foreach([
          ['01', route('shop.index'), __('shop.nav.watches'), __('shop.nav.all_desc')],
          ['02', route('favorites.index'), __('shop.nav.favorites'), __('shop.nav.fav_desc')],
          ['03', route('sell'), __('shop.nav.sell'), __('shop.nav.sell_desc')],
          ['04', route('evaluation.index'), __('shop.nav.evaluation'), __('shop.nav.eval_desc')],
        ] as [$n, $href, $label, $desc])
          <a class="hs-dnav__item" href="{{ $href }}">
            <span class="hs-dnav__n">{{ $n }}</span>
            <span class="hs-dnav__txt"><span class="hs-dnav__label">{{ $label }}</span><span class="hs-dnav__desc">{{ $desc }}</span></span>
            <svg class="hs-dnav__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M4 12h15M14 7l5 5-5 5"/></svg>
          </a>
        @endforeach
      </nav>

      @if($navBrands->count())
        <div class="hs-dsec">
          <div class="hs-dsec__t">{{ __('shop.nav.brands') }}</div>
          <div class="hs-dlist">
            @foreach($navBrands as $b)
              @php $cnt = $brandCount($b); @endphp
              <a href="{{ route('shop.index', ['marca' => $b->slug]) }}" class="{{ $cnt ? '' : 'is-empty' }}"><span>{{ $b->name }}</span><em>{{ $cnt ?: '—' }}</em></a>
            @endforeach
          </div>
        </div>
      @endif

      @if($navCategories->count())
        <div class="hs-dsec">
          <div class="hs-dsec__t">{{ __('shop.nav.categories') }}</div>
          <div class="hs-dlist">
            @foreach($navCategories as $c)
              @php $cnt = $catCount($c); @endphp
              <a href="{{ route('shop.index', ['categoria' => $c->slug]) }}" class="{{ $cnt ? '' : 'is-empty' }}"><span>{{ $c->name }}</span><em>{{ $cnt ?: '—' }}</em></a>
            @endforeach
          </div>
        </div>
      @endif

      @if($featured && $featured->cover_url)
        <a class="hs-dfeat" href="{{ route('shop.show', $featured->slug) }}">
          <img src="{{ $featured->cover_url }}" alt="{{ $featured->title }}" loading="lazy">
          <span>
            <small>{{ __('shop.nav.featured') }}</small>
            <strong>{{ $featured->brand }} {{ $featured->model }}</strong>
            <em>{{ \App\Models\Watch::display($featured->effective_price) }}</em>
          </span>
          @if($featured->is_certified)<img class="seal" src="/images/shop/seal.png" alt="">@endif
        </a>
      @endif

      <div class="hs-dfoot">
        <div class="hs-dfoot__links">
          @auth
            @if($authUser->isAdmin())<a href="{{ route('admin.dashboard') }}">{{ __('shop.nav.admin') }}</a>@endif
            @if($authUser->isSeller())<a href="{{ route('seller.dashboard') }}">{{ __('shop.nav.seller_area') }}</a>@endif
            <a href="{{ route('account.orders') }}">{{ __('shop.nav.orders') }}</a>
            <a href="{{ route('account.profile') }}">{{ __('shop.nav.account') }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">{{ __('shop.nav.logout') }}</button></form>
          @else
            <a href="{{ route('account.login') }}">{{ __('shop.nav.login') }}</a>
            <a href="{{ route('account.register') }}">{{ __('shop.nav.register') }}</a>
          @endauth
          <a href="{{ route('contact') }}">{{ __('shop.nav.contact') }}</a>
        </div>
        <div class="hs-dfoot__bar">
          <span>{{ __('shop.footer.secure') }}</span>
          <span class="hs-dfoot__lang">
            <a href="{{ route('lang.switch', 'es') }}" class="{{ $locale === 'es' ? 'on' : '' }}">ES</a>
            <a href="{{ route('lang.switch', 'en') }}" class="{{ $locale === 'en' ? 'on' : '' }}">EN</a>
          </span>
        </div>
      </div>
    </aside>
  </div>

  <main class="hs-main" id="main">
    @if(session('success') || session('error'))
      <div class="hs-wrap">
        @if(session('success'))<div class="hs-alert hs-alert--ok" role="status">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="hs-alert hs-alert--err" role="alert">{{ session('error') }}</div>@endif
      </div>
    @endif
    @yield('content')
  </main>

  <footer class="hs-footer">
    <div class="hs-footer__inner">
      <div>
        <img src="/images/logo-dark.png" alt="HAUS OF TIME">
        <p>{{ __('shop.footer.tagline') }}</p>
      </div>
      <div class="hs-footer__cols">
        <div>
          <h4>{{ __('shop.nav.watches') }}</h4>
          <a href="{{ route('shop.index') }}">{{ __('shop.nav.all_watches') }}</a>
          <a href="{{ route('shop.index', ['certificado' => 1]) }}">{{ __('shop.nav.certified_only') }}</a>
          <a href="{{ route('evaluation.index') }}">{{ __('shop.nav.evaluation') }}</a>
          <a href="{{ route('sell') }}">{{ __('shop.nav.sell') }}</a>
        </div>
        <div>
          <h4>{{ __('shop.nav.account') }}</h4>
          <a href="{{ route('account.orders') }}">{{ __('shop.nav.orders') }}</a>
          <a href="{{ route('favorites.index') }}">{{ __('shop.nav.favorites') }}</a>
          <a href="{{ route('contact') }}">{{ __('shop.nav.contact') }}</a>
        </div>
        <div>
          <h4>HAUS OF TIME</h4>
          @foreach($footerPages as $fp)
            <a href="{{ route('page.show', $fp->slug) }}">{{ $locale === 'en' && $fp->title_en ? $fp->title_en : $fp->title_es }}</a>
          @endforeach
        </div>
      </div>
    </div>
    <div class="hs-footer__bottom">
      <span>{{ __('shop.footer.rights', ['year' => date('Y')]) }}</span>
      <span>{{ __('shop.footer.secure') }}</span>
    </div>
  </footer>

  <div class="hs-toast" id="hsToast" role="status" aria-live="polite"></div>

  <script>
    window.HOT = {
      csrf: document.querySelector('meta[name="csrf-token"]').content,
      i18n: {
        fav: @json(__('shop.card.favorite')), unfav: @json(__('shop.card.unfavorite')),
        bag: @json(__('shop.nav.bag')), checkout: @json(__('shop.bag.checkout')), copied: @json(__('shop.product.copied'))
      },
      routes: { bag: @json(route('bag.index')), checkout: @json(route('checkout.index')) }
    };
  </script>
  <script src="/js/shop.js?v={{ filemtime(public_path('js/shop.js')) }}" defer></script>
  @stack('scripts')
</body>
</html>
