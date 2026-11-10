<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Haus of Time')</title>
  <link rel="icon" type="image/png" href="/images/favicon.png">
  <meta name="description" content="@yield('description', 'El primer marketplace autenticado para relojes de lujo.')">
  <link rel="stylesheet" href="/css/app.css">
  @stack('styles')
</head>
<body>
  @if(isset($siteMode) && !$siteMode->isLive() && $siteMode->isPrivileged())
    {{-- Visibile solo ad admin / whitelist / link di anteprima: il pubblico non vede nulla --}}
    <div style="position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:9999;display:flex;gap:10px;align-items:center;padding:8px 10px 8px 16px;background:rgba(17,17,17,.92);border:1px solid rgba(255,255,255,.12);border-radius:40px;font:500 11px Poppins,sans-serif;letter-spacing:.06em;color:#e9e4da;box-shadow:0 12px 40px -12px rgba(0,0,0,.6)">
      <span style="color:#d6b77c;font-weight:600;text-transform:uppercase;letter-spacing:.14em;font-size:10px">Preview</span>
      <a href="{{ route('view.switch', 'live') }}" style="padding:5px 12px;border-radius:30px;background:#fff;color:#111">{{ __('shop.preview.see_live') }}</a>
    </div>
  @endif

  {{-- ── NAVBAR ─────────────────────────────────────────── --}}
  <nav class="navbar" id="navbar">
    <a href="{{ route('home') }}" class="navbar__brand">
      <img src="/images/logo.png" alt="Haus of Time">
    </a>

    <div class="navbar__nav">
      <a href="{{ route('evaluation.index') }}" class="navbar__link">
        {{ __('messages.nav.evaluation') }}
      </a>
      @auth
        @if(auth()->user()->isSeller())
          <a href="{{ route('seller.dashboard') }}" class="navbar__link">{{ __('messages.nav.dashboard') }}</a>
        @elseif(auth()->user()->isAdmin())
          <a href="{{ route('admin.dashboard') }}" class="navbar__link">Admin</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button type="submit" class="navbar__link" style="background:none;font-family:inherit">
            {{ __('messages.nav.logout') }}
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="navbar__link">{{ __('messages.nav.login') }}</a>
      @endauth
      <div class="navbar__lang">
        <a href="{{ route('lang.switch', 'es') }}" class="{{ app()->getLocale() === 'es' ? 'active' : '' }}">ES</a>
        <span>|</span>
        <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
      </div>
    </div>

    <button class="navbar__toggle" id="navToggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>

    <div class="mobile-nav" id="mobileNav">
      <a href="{{ route('evaluation.index') }}">{{ __('messages.nav.evaluation') }}</a>
      @guest
        <a href="{{ route('login') }}">{{ __('messages.nav.login') }}</a>
        <a href="{{ route('early-access.index') }}">{{ __('messages.nav.early_access') }}</a>
      @endguest
      @auth
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button type="submit" style="display:block;padding:14px 0;font-family:inherit;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.85);background:none;border:none;border-bottom:1px solid rgba(255,255,255,.08);width:100%;text-align:left;">
            {{ __('messages.nav.logout') }}
          </button>
        </form>
      @endauth
      <div class="navbar__lang" style="margin-top:16px">
        <a href="{{ route('lang.switch', 'es') }}" class="{{ app()->getLocale() === 'es' ? 'active' : '' }}">ES</a>
        <span>|</span>
        <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
      </div>
    </div>
  </nav>

  @yield('content')

  {{-- ── FOOTER con link dinamici dal DB ──────────────── --}}
  <footer class="footer">
    <div class="footer__inner">
      <a href="{{ route('home') }}" class="footer__brand">
        <img src="/images/logo.png" alt="Haus of Time" style="height:22px;filter:brightness(0) invert(1);opacity:.65;">
      </a>
      <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
        <div class="footer__links">
          @php $footerPages = \App\Models\Page::active()->orderBy('sort_order')->get(); @endphp
          @foreach($footerPages as $fp)
            <a href="{{ route('page.show', $fp->slug) }}" class="footer__link">{{ app()->getLocale() === 'en' && $fp->title_en ? $fp->title_en : $fp->title_es }}</a>
          @endforeach
          <a href="{{ route('contact') }}" class="footer__link">{{ __('messages.footer.contact') }}</a>
        </div>
        <div class="footer__lang">
          <a href="{{ route('lang.switch', 'es') }}" class="{{ app()->getLocale() === 'es' ? 'active' : '' }}">ES</a>
          <span>|</span>
          <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
        </div>
      </div>
      <p class="footer__copy">{{ __('messages.footer.copyright') }}</p>
    </div>
  </footer>

  <script>
    // Navbar scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }, { passive: true });

    // Mobile menu
    document.getElementById('navToggle')?.addEventListener('click', () => {
      const m = document.getElementById('mobileNav');
      m.style.display = m.style.display === 'block' ? 'none' : 'block';
    });

    // Alerts auto-dismiss
    document.querySelectorAll('.alert').forEach(el => {
      el.style.transition = 'opacity .4s';
      setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 5000);
    });
</script>
  @stack('scripts')
</body>
</html>
