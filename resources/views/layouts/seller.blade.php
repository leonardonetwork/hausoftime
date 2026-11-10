<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', __('messages.seller.dashboard')) — Haus of Time</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      /* ── Core palette ── */
      --s-bg:#141310;--s-bg2:#1d1b17;--s-bg3:#26241f;
      --s-border:#35322a;--s-border2:#2b2924;
      --s-text:#ede8dd;--s-muted:#a8a196;--s-subtle:#7d776d;
      --s-gold:#c69c6d;--s-gold2:#b08a5c;
      --s-success:#4e9b76;--s-danger:#c05555;--s-warning:#d4a24a;
      --s-on-gold:#141310;
      --font:'Poppins',sans-serif;
      /* ── Aliases for legacy views ── */
      --light-text:var(--s-text);--light-muted:var(--s-muted);
      --light-subtle:var(--s-subtle);--light-border:var(--s-border);
      --font-body:var(--font);--font-display:var(--font);
    }
    html,body{height:100%}
    body{font-family:var(--font);background:var(--s-bg);color:var(--s-text);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
    a{color:inherit;text-decoration:none}img{max-width:100%;display:block}

    /* Layout */
    .app-layout{display:grid;grid-template-columns:220px 1fr;min-height:100vh}

    /* Sidebar */
    .sidebar{background:var(--s-bg2);border-right:1px solid var(--s-border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;transition:transform .3s ease}
    .sidebar__header{padding:22px 20px 16px;border-bottom:1px solid var(--s-border)}
    .sidebar__logo{height:22px;width:auto;opacity:.9;filter:brightness(0) invert(1)}
    .sidebar__user{margin-top:0}
    .sidebar__user-name{font-size:13px;font-weight:500;color:var(--s-text)}
    .sidebar__user-role{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--s-gold)}
    .sidebar__pending{margin-top:8px;font-size:.7rem;color:var(--s-warning);background:rgba(200,144,42,.1);padding:4px 8px;border-radius:3px;border-left:2px solid var(--s-warning)}
    .sidebar__nav{padding:12px 0;flex:1}
    .sidebar__group{font-size:9px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--s-subtle);padding:12px 20px 4px}
    .sidebar__link{display:flex;align-items:center;gap:10px;padding:9px 20px;font-size:12px;font-weight:400;color:var(--s-muted);transition:all .2s;border-left:2px solid transparent;cursor:pointer;background:none;border-top:none;border-right:none;border-bottom:none;width:100%;font-family:var(--font);text-align:left}
    .sidebar__link:hover{background:var(--s-bg3);color:var(--s-text)}
    .sidebar__link.active{color:var(--s-gold);background:rgba(184,144,100,.08);border-left-color:var(--s-gold);font-weight:500}
    .sidebar__footer{padding:16px 20px;border-top:1px solid var(--s-border)}

    /* Main */
    .main-content{padding:36px 44px;overflow-y:auto;position:relative}
    .main-content::before{content:'';position:absolute;inset:0;background:url('/images/hero-bg.jpg') center/cover fixed no-repeat;opacity:.13;pointer-events:none;z-index:0}
    .main-content>*{position:relative;z-index:1}

    /* Typography */
    .page-title{font-size:1.5rem;font-weight:600;color:var(--s-text);margin-bottom:4px}
    .page-subtitle{font-size:12px;color:var(--s-muted);margin-bottom:32px}

    /* Stats */
    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px}
    .stat-card{background:var(--s-bg2);border:1px solid var(--s-border);padding:20px;border-radius:4px}
    .stat-card__num{font-size:1.8rem;font-weight:600;color:var(--s-text);line-height:1;margin-bottom:4px}
    .stat-card__num--gold{color:var(--s-gold)}
    .stat-card__num--success{color:var(--s-success)}
    .stat-card__num--warning{color:var(--s-warning)}
    .stat-card__label{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--s-subtle)}

    /* Card */
    .card{background:var(--s-bg2);border:1px solid var(--s-border);padding:24px;border-radius:4px;margin-bottom:20px}
    .card:last-child{margin-bottom:0}

    /* Table */
    .table-wrap{background:var(--s-bg2);border:1px solid var(--s-border);border-radius:4px;overflow:hidden}
    .table-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--s-border)}
    .table-title{font-size:11px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--s-text)}
    .table-link{font-size:11px;color:var(--s-subtle);letter-spacing:.08em;text-transform:uppercase;transition:color .2s}
    .table-link:hover{color:var(--s-gold)}
    .data-table{width:100%;border-collapse:collapse;font-size:13px}
    .data-table th{text-align:left;padding:10px 16px;font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--s-subtle);border-bottom:1px solid var(--s-border);background:var(--s-bg3)}
    .data-table td{padding:13px 16px;border-bottom:1px solid var(--s-border2);color:var(--s-muted);vertical-align:middle}
    .data-table tr:last-child td{border-bottom:none}
    .data-table tr:hover td{background:var(--s-bg3)}
    .data-table td.td-strong{color:var(--s-text);font-weight:500}

    /* Buttons */
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;font-family:var(--font);font-size:11px;font-weight:500;letter-spacing:.06em;padding:9px 20px;border-radius:3px;border:1px solid transparent;cursor:pointer;transition:all .2s;text-decoration:none;white-space:nowrap}
    .btn--dark{background:var(--s-gold);color:var(--s-on-gold);border-color:var(--s-gold)}.btn--dark:hover{background:var(--s-gold2);border-color:var(--s-gold2)}
    .btn--primary{background:var(--s-gold);color:var(--s-on-gold);border-color:var(--s-gold)}.btn--primary:hover{background:var(--s-gold2);border-color:var(--s-gold2)}
    .btn--gold{background:var(--s-gold);color:var(--s-on-gold);border-color:var(--s-gold)}.btn--gold:hover{background:var(--s-gold2)}
    .btn--ghost{background:transparent;color:var(--s-muted);border-color:var(--s-border)}.btn--ghost:hover{border-color:var(--s-muted);color:var(--s-text)}
    .btn--success{background:var(--s-success);color:#fff}.btn--danger{background:var(--s-danger);color:#fff}
    .btn--sm{padding:5px 12px;font-size:10px}
    .btn--lg{padding:13px 30px;font-size:11px;letter-spacing:.2em;text-transform:uppercase}

    /* Forms */
    .form-group{margin-bottom:18px}
    .form-label,.form-label-s{display:block;font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--s-muted);margin-bottom:6px}
    .form-control,.form-control--select{width:100%;background:var(--s-bg3);border:1px solid var(--s-border);border-radius:3px;color:var(--s-text);font-family:var(--font);font-size:13px;padding:10px 12px;outline:none;transition:border-color .2s;-webkit-appearance:none;appearance:none}
    input[type=number]::-webkit-outer-spin-button,input[type=number]::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
    input[type=number]{-moz-appearance:textfield}
    .form-control:focus,.form-control--select:focus{border-color:var(--s-gold)}
    .form-control::placeholder{color:var(--s-subtle)}
    select.form-control,select.form-control--select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23a09a90' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
    textarea.form-control{min-height:110px;resize:vertical}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .field-error{font-size:11px;color:var(--s-danger);margin-top:4px}

    /* Alerts */
    .alert{padding:11px 16px;font-size:12px;margin-bottom:16px;border-left:3px solid transparent;border-radius:3px}
    .alert--success{background:rgba(78,155,118,.14);border-color:var(--s-success);color:#7fd4a5}
    .alert--error{background:rgba(192,85,85,.14);border-color:var(--s-danger);color:#e79494}
    .alert--warning{background:rgba(212,162,74,.14);border-color:var(--s-warning);color:#e0b870}

    /* Badges */
    .badge{display:inline-flex;align-items:center;padding:2px 9px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;border-radius:2px}
    .badge-success{background:rgba(61,122,94,.12);color:#3d7a5e}
    .badge-warning{background:rgba(200,144,42,.14);color:#a07010}
    .badge-danger{background:rgba(155,51,51,.12);color:#9b3333}
    .badge-info{background:rgba(58,85,128,.12);color:#3a5580}
    .badge-gold{background:rgba(184,144,100,.14);color:var(--s-gold)}
    .badge-secondary{background:var(--s-bg3);color:var(--s-subtle)}

    /* Watch grid */
    .watch-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .watch-card{background:var(--s-bg2);border:1px solid var(--s-border);border-radius:4px;overflow:hidden}
    .watch-card__img{aspect-ratio:4/3;overflow:hidden;background:var(--s-bg3)}
    .watch-card__img img{width:100%;height:100%;object-fit:cover}
    .watch-card__body{padding:14px 16px}
    .watch-card__brand{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--s-gold);margin-bottom:3px}
    .watch-card__name{font-size:13px;font-weight:500;color:var(--s-text)}
    .watch-card__price{font-size:14px;font-weight:600;color:var(--s-text);margin-top:4px}
    .watch-card__footer{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid var(--s-border)}

    /* Panel header */
    .panel-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}

    /* Empty state */
    .empty-state{text-align:center;padding:64px 24px}
    .empty-state__icon{font-size:2.2rem;margin-bottom:14px;opacity:.4}
    .empty-state__text{font-size:14px;font-weight:300;color:var(--s-muted);margin-bottom:22px}

    /* Mobile topbar */
    .topbar{display:none;align-items:center;justify-content:space-between;padding:0 16px;height:56px;background:var(--s-bg2);border-bottom:1px solid var(--s-border);position:sticky;top:0;z-index:300}
    .topbar__logo{height:18px;opacity:.9;filter:brightness(0) invert(1)}
    .topbar__hamburger{background:none;border:none;cursor:pointer;padding:8px;display:flex;flex-direction:column;gap:5px;position:relative;z-index:301}
    .topbar__hamburger span{display:block;width:22px;height:1.5px;background:var(--s-text);border-radius:2px;transition:all .3s}
    .topbar__hamburger.open span:nth-child(1){transform:translateY(6.5px) rotate(45deg)}
    .topbar__hamburger.open span:nth-child(2){opacity:0}
    .topbar__hamburger.open span:nth-child(3){transform:translateY(-6.5px) rotate(-45deg)}

    /* Overlay */
    .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:250;opacity:0;transition:opacity .3s;pointer-events:none}
    .sidebar-overlay.visible{opacity:1;pointer-events:all}

    /* Responsive */
    @media(max-width:1100px){
      .app-layout{grid-template-columns:190px 1fr}
      .main-content{padding:28px 28px}
    }
    @media(max-width:900px){
      .stats-grid{grid-template-columns:repeat(2,1fr)}
      .watch-grid{grid-template-columns:repeat(2,1fr)}
    }
    @media(max-width:768px){
      .topbar{display:flex}
      .app-layout{display:block}
      .sidebar{
        position:fixed;left:0;top:0;z-index:280;
        width:260px;height:100vh;
        transform:translateX(-100%);
        box-shadow:4px 0 24px rgba(0,0,0,.12)
      }
      .sidebar.open{transform:translateX(0)}
      .sidebar-overlay{display:block}
      .main-content{padding:20px 16px}
      .form-row{grid-template-columns:1fr}
      .stats-grid{grid-template-columns:repeat(2,1fr);gap:10px}
      .watch-grid{grid-template-columns:1fr}
    }
    @media(max-width:420px){
      .stats-grid{grid-template-columns:1fr}
    }
  </style>
  @stack('styles')
</head>
<body>

<div class="topbar" id="topbar">
  <button class="topbar__hamburger" id="hamburger" type="button" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
  <a href="{{ route('home') }}">
    <img src="{{ asset('images/logo-dark.png') }}" alt="Haus of Time" class="topbar__logo">
  </a>
  <div style="width:38px"></div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-layout">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar__header">
      <a href="{{ route('home') }}">
        <img src="{{ asset('images/logo-dark.png') }}" alt="Haus of Time" class="sidebar__logo">
      </a>
      <hr style="border:none;border-top:1px solid var(--s-border);margin:16px -20px 14px;">
      <div class="sidebar__user">
        <div class="sidebar__user-name">{{ auth()->user()->first_name ?: auth()->user()->name }}</div>
        <div class="sidebar__user-role">
          @if(auth()->user()->is_founding_seller)
            {{ __('messages.seller.founding_badge') }}
          @else
            Seller
          @endif
        </div>
        @if(!auth()->user()->isApproved() && !auth()->user()->is_founding_seller)
          <div class="sidebar__pending">{{ __('messages.seller.pending_approval') }}</div>
        @endif
      </div>
    </div>

    <nav class="sidebar__nav">
      <div class="sidebar__group">Overview</div>
      <a href="{{ route('seller.dashboard') }}" class="sidebar__link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        {{ __('messages.seller.dashboard') }}
      </a>

      <div class="sidebar__group">{{ app()->getLocale() === 'es' ? 'Catálogo' : 'Catalogue' }}</div>
      <a href="{{ route('seller.watches.create') }}" class="sidebar__link {{ request()->routeIs('seller.watches.create') ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        {{ __('messages.seller.upload_watch') }}
      </a>
      <a href="{{ route('seller.watches') }}" class="sidebar__link {{ (request()->routeIs('seller.watches') && !request()->routeIs('seller.watches.create')) || request()->routeIs('seller.watches.show') || request()->routeIs('seller.watches.edit') ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        {{ __('messages.seller.my_watches') }}
      </a>

      @if($shopOpen ?? false)
      <div class="sidebar__group">{{ app()->getLocale() === 'es' ? 'Marketplace' : 'Marketplace' }}</div>
      <a href="{{ route('seller.sales') }}" class="sidebar__link {{ request()->routeIs('seller.sales*') ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M5 8h14l-1.2 12.2a1 1 0 0 1-1 .8H7.2a1 1 0 0 1-1-.8L5 8z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/></svg>
        {{ __('shop.seller.sales') }}
        @php $toShip = \App\Models\OrderItem::where('seller_id', auth()->id())->where('status', 'awaiting_shipment')->count(); @endphp
        @if($toShip)<span style="margin-left:auto;background:var(--s-gold);color:var(--s-on-gold);font-size:10px;font-weight:700;border-radius:10px;padding:1px 7px">{{ $toShip }}</span>@endif
      </a>
      <a href="{{ route('seller.payouts') }}" class="sidebar__link {{ request()->routeIs('seller.payouts*') ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>
        {{ __('shop.seller.payouts') }}
      </a>
      @endif

      <div class="sidebar__group">{{ app()->getLocale() === 'es' ? 'Cuenta' : 'Account' }}</div>
      <a href="{{ route('seller.contacts') }}" class="sidebar__link {{ request()->routeIs('seller.contacts') ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        {{ __('messages.seller.contacts') }}
      </a>
    </nav>

    <div class="sidebar__footer">
      <div style="display:flex;align-items:center;gap:8px;padding:8px 0 14px;border-bottom:1px solid var(--s-border);margin-bottom:12px;">
        <span style="font-size:9px;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--s-subtle);">{{ app()->getLocale() === 'es' ? 'Idioma' : 'Language' }}</span>
        <a href="{{ route('lang.switch', 'es') }}" style="font-size:11px;color:{{ app()->getLocale()==='es' ? 'var(--s-gold)' : 'var(--s-muted)' }};font-weight:{{ app()->getLocale()==='es' ? '600' : '400' }}">ES</a>
        <span style="color:var(--s-border)">|</span>
        <a href="{{ route('lang.switch', 'en') }}" style="font-size:11px;color:{{ app()->getLocale()==='en' ? 'var(--s-gold)' : 'var(--s-muted)' }};font-weight:{{ app()->getLocale()==='en' ? '600' : '400' }}">EN</a>
      </div>
      <a href="{{ route('evaluation.index') }}" class="sidebar__link" style="padding:0;margin-bottom:10px;gap:8px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 16 14"/></svg>
        {{ __('messages.nav.evaluation') }}
      </a>
      <a href="{{ route('home') }}" class="sidebar__link" style="padding:0;margin-bottom:10px;gap:8px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        {{ app()->getLocale() === 'es' ? 'Ver sitio' : 'View site' }}
      </a>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" class="sidebar__link" style="width:100%;padding:0;gap:8px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          {{ __('messages.seller.logout') }}
        </button>
      </form>
    </div>
  </aside>

  <main class="main-content">
    @if(session('success'))
      <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert--error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert--error">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
      </div>
    @endif
    @yield('seller-content')
  </main>

</div>

<script>
(function() {
  var sidebar   = document.getElementById('sidebar');
  var overlay   = document.getElementById('sidebarOverlay');
  var hamburger = document.getElementById('hamburger');

  if (!sidebar || !hamburger) return;

  function openSidebar() {
    sidebar.classList.add('open');
    if (overlay) { overlay.classList.add('visible'); }
    hamburger.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sidebar.classList.remove('open');
    if (overlay) { overlay.classList.remove('visible'); }
    hamburger.classList.remove('open');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', function(e) {
    e.stopPropagation();
    if (sidebar.classList.contains('open')) { closeSidebar(); } else { openSidebar(); }
  });

  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  sidebar.querySelectorAll('a.sidebar__link').forEach(function(link) {
    link.addEventListener('click', function() {
      if (window.innerWidth <= 768) closeSidebar();
    });
  });

  // Alerts
  document.querySelectorAll('.alert').forEach(function(el) {
    el.style.transition = 'opacity .4s';
    setTimeout(function() {
      el.style.opacity = '0';
      setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 400);
    }, 6000);
  });
})();
</script>
@stack('scripts')
</body>
</html>
