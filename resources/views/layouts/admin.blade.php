<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin — @yield('title', 'Dashboard') — Haus of Time</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--a-bg:#0e0e0e;--a-bg2:#161614;--a-bg3:#1d1d1b;--a-border:#2a2a28;--a-border2:#222220;--a-text:#e8e3d8;--a-muted:#9a9a90;--a-subtle:#555550;--a-gold:#b89064;--a-gold2:#a07850;--a-success:#3d8a5e;--a-danger:#9b3333;--a-warning:#c8902a;--font:'Poppins',sans-serif;--warning:var(--a-warning);--success:var(--a-success);--danger:var(--a-danger);--text-muted:var(--a-muted);--text:var(--a-text);--gold:var(--a-gold);--border:var(--a-border)}
    .grid-2{display:grid;grid-template-columns:1fr 1fr}@media(max-width:1024px){.grid-2{grid-template-columns:1fr}}
    .site-mode{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;padding:20px 24px;border-radius:4px;margin-bottom:28px;border:1px solid var(--a-border);background:var(--a-bg2)}
    .site-mode.is-live{border-color:var(--a-success);background:rgba(61,138,94,.08)}
    .site-mode__dot{width:10px;height:10px;border-radius:50%;background:var(--a-warning);box-shadow:0 0 0 4px rgba(200,144,42,.18)}
    .site-mode.is-live .site-mode__dot{background:#4cc483;box-shadow:0 0 0 4px rgba(76,196,131,.2)}
    .switch{position:relative;display:inline-block;width:44px;height:24px;flex:none}.switch input{opacity:0;width:0;height:0}
    .switch span{position:absolute;inset:0;background:var(--a-border);border-radius:24px;transition:.2s;cursor:pointer}
    .switch span::before{content:"";position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
    .switch input:checked+span{background:var(--a-success)}.switch input:checked+span::before{transform:translateX(20px)}
    .kv{display:grid;grid-template-columns:auto 1fr;gap:6px 18px;font-size:13px}.kv dt{color:var(--a-subtle)}.kv dd{color:var(--a-text)}
    .timeline{list-style:none;border-left:1px solid var(--a-border);margin-left:6px}.timeline li{position:relative;padding:0 0 14px 18px;font-size:12px;color:var(--a-muted)}
    .timeline li::before{content:"";position:absolute;left:-4px;top:5px;width:7px;height:7px;border-radius:50%;background:var(--a-gold)}
    .timeline time{display:block;font-size:10px;color:var(--a-subtle)}
    html,body{height:100%}body{font-family:var(--font);background:var(--a-bg);color:var(--a-text);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
    a{color:inherit;text-decoration:none}img{max-width:100%;display:block}
    .app-layout{display:grid;grid-template-columns:220px 1fr;min-height:100vh}
    .sidebar{background:var(--a-bg2);border-right:1px solid var(--a-border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto}
    .sidebar__header{padding:22px 20px 16px;border-bottom:1px solid var(--a-border)}
    .sidebar__logo{height:22px;width:auto;filter:brightness(0) invert(1);opacity:.8}
    .sidebar__user{margin-top:14px}.sidebar__user-name{font-size:13px;font-weight:500;color:var(--a-text)}
    .sidebar__user-role{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--a-gold)}
    .sidebar__nav{padding:12px 0;flex:1}
    .sidebar__group{font-size:9px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--a-subtle);padding:12px 20px 4px}
    .sidebar__link{display:flex;align-items:center;gap:10px;padding:9px 20px;font-size:12px;font-weight:400;color:var(--a-muted);transition:all .2s;border-left:2px solid transparent;cursor:pointer;background:none;border-top:none;border-right:none;border-bottom:none;width:100%;font-family:var(--font);text-align:left}
    .sidebar__link:hover{background:var(--a-bg3);color:var(--a-text)}
    .sidebar__link.active{color:var(--a-gold);background:rgba(184,144,100,.08);border-left-color:var(--a-gold);font-weight:500}
    .sidebar__sub{padding-left:32px;font-size:11px}
    .sidebar__footer{padding:16px 20px;border-top:1px solid var(--a-border)}
    .main-content{padding:36px 44px;overflow-y:auto;background:var(--a-bg)}
    .page-title{font-size:1.5rem;font-weight:600;color:var(--a-text);margin-bottom:4px}
    .page-subtitle{font-size:12px;color:var(--a-muted);margin-bottom:32px}
    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px}
    .stat-card{background:var(--a-bg2);border:1px solid var(--a-border);padding:20px;border-radius:4px}
    .stat-card__num{font-size:1.8rem;font-weight:600;color:var(--a-gold);line-height:1;margin-bottom:4px}
    .stat-card__label{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--a-subtle)}
    .card{background:var(--a-bg2);border:1px solid var(--a-border);padding:24px;border-radius:4px}
    .card__num{font-size:10px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--a-gold);margin-bottom:12px}
    .table-wrap{background:var(--a-bg2);border:1px solid var(--a-border);border-radius:4px;overflow:hidden}
    .data-table{width:100%;border-collapse:collapse;font-size:13px}
    .data-table th{text-align:left;padding:11px 14px;font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--a-subtle);border-bottom:1px solid var(--a-border);background:var(--a-bg3)}
    .data-table td{padding:12px 14px;border-bottom:1px solid var(--a-border2);color:var(--a-muted);vertical-align:middle}
    .data-table tr:hover td{background:var(--a-bg3)}.data-table td:first-child{color:var(--a-text);font-weight:500}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;font-family:var(--font);font-size:11px;font-weight:500;letter-spacing:.06em;padding:8px 18px;border-radius:3px;border:1px solid transparent;cursor:pointer;transition:all .2s;text-decoration:none;white-space:nowrap}
    .btn--primary{background:var(--a-gold);color:#000;border-color:var(--a-gold)}.btn--primary:hover{background:var(--a-gold2)}
    .btn--ghost{background:transparent;color:var(--a-muted);border-color:var(--a-border)}.btn--ghost:hover{border-color:var(--a-muted);color:var(--a-text)}
    .btn--success{background:var(--a-success);color:#fff}.btn--danger{background:var(--a-danger);color:#fff}
    .btn--sm{padding:5px 12px;font-size:10px}.btn--lg{padding:12px 28px;font-size:12px}
    .form-group{margin-bottom:18px}
    .form-label{display:block;font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--a-muted);margin-bottom:6px}
    .form-control{width:100%;background:var(--a-bg3);border:1px solid var(--a-border);border-radius:3px;color:var(--a-text);font-family:var(--font);font-size:13px;padding:9px 12px;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:var(--a-gold)}.form-control::placeholder{color:var(--a-subtle)}
    select.form-control option{background:var(--a-bg3)}textarea.form-control{min-height:100px;resize:vertical}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .alert{padding:11px 14px;font-size:12px;margin-bottom:16px;border-left:3px solid transparent;border-radius:2px}
    .alert--success{background:rgba(61,138,94,.12);border-color:var(--a-success);color:#7fd4a5}
    .alert--error{background:rgba(155,51,51,.12);border-color:var(--a-danger);color:#e08080}
    .alert--warning{background:rgba(200,144,42,.12);border-color:var(--a-warning);color:#e0b870}
    .badge{display:inline-flex;align-items:center;padding:2px 8px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;border-radius:2px}
    .badge-success{background:rgba(61,138,94,.18);color:#7fd4a5}.badge-warning{background:rgba(200,144,42,.18);color:#e0b870}
    .badge-danger{background:rgba(155,51,51,.18);color:#e08080}.badge-info{background:rgba(58,85,128,.18);color:#80a0d4}
    .badge-gold{background:rgba(184,144,100,.18);color:var(--a-gold)}.badge-secondary{background:var(--a-border);color:var(--a-muted)}
    .tabs{display:flex;gap:2px;background:var(--a-bg3);padding:3px;width:fit-content;margin-bottom:20px;border-radius:4px}
    .tab{padding:6px 14px;font-family:var(--font);font-size:11px;font-weight:500;color:var(--a-muted);cursor:pointer;transition:all .2s;background:none;border:none;border-radius:3px}
    .tab.active,a.tab.active{background:var(--a-bg2);color:var(--a-gold)}.tab:hover:not(.active){color:var(--a-text)}a.tab{text-decoration:none}
    .panel-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px}
    .filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.filter-bar .form-control{width:auto;min-width:130px}
    .radio-group{display:flex;gap:10px;flex-wrap:wrap}
    .radio-card{flex:1;min-width:160px;border:1px solid var(--a-border);border-radius:4px;padding:16px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:10px;background:var(--a-bg3)}
    .radio-card input{display:none}.radio-card.selected{border-color:var(--a-gold);background:rgba(184,144,100,.08)}
    .radio-card__dot{width:14px;height:14px;border-radius:50%;border:1px solid var(--a-border);flex-shrink:0;transition:all .2s}
    .radio-card.selected .radio-card__dot{border-color:var(--a-gold);background:var(--a-gold)}
    .radio-card__label{font-size:13px;font-weight:400;color:var(--a-text)}
    .watch-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .watch-card{background:var(--a-bg2);border:1px solid var(--a-border);border-radius:4px;overflow:hidden}
    .watch-card__img{aspect-ratio:4/3;overflow:hidden;background:var(--a-bg3)}.watch-card__img img{width:100%;height:100%;object-fit:cover}
    .watch-card__body{padding:14px 16px}.watch-card__brand{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--a-gold);margin-bottom:3px}
    .watch-card__name{font-size:13px;font-weight:500;color:var(--a-text)}.watch-card__price{font-size:14px;font-weight:600;color:var(--a-text)}
    .watch-card__footer{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid var(--a-border)}
    @media(max-width:1024px){.stats-grid{grid-template-columns:repeat(2,1fr)}.watch-grid{grid-template-columns:repeat(2,1fr)}.app-layout{grid-template-columns:180px 1fr}.main-content{padding:24px}}
    @media(max-width:768px){.app-layout{grid-template-columns:1fr}.sidebar{position:fixed;left:-220px;z-index:200;height:100vh;transition:left .3s}.main-content{padding:20px 16px}.watch-grid,.stats-grid,.form-row{grid-template-columns:1fr}}
  </style>
  @stack('styles')
</head>
<body>
<div class="app-layout">

  <aside class="sidebar">
    <div class="sidebar__header">
      <a href="{{ route('home') }}"><img src="/images/logo.png" alt="Haus of Time" class="sidebar__logo"></a>
      <div class="sidebar__user">
        <div class="sidebar__user-name">{{ auth()->user()->name }}</div>
        <div class="sidebar__user-role">Administrator</div>
      </div>
    </div>

    <nav class="sidebar__nav">
      <div class="sidebar__group">Overview</div>
      <a href="{{ route('admin.dashboard') }}" class="sidebar__link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span>&#9638;</span> Dashboard
      </a>

      <div class="sidebar__group">Cat&aacute;logo</div>
      <a href="{{ route('admin.watches.index') }}" class="sidebar__link {{ request()->routeIs('admin.watches.*') ? 'active' : '' }}">
        <span>&#8987;</span> Productos
      </a>
      <a href="{{ route('admin.brands.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
        <span>&#9685;</span> Marcas
      </a>
      <a href="{{ route('admin.categories.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <span>&#9685;</span> Categor&iacute;as
      </a>

      @php
        $navToShip = \App\Models\OrderItem::whereIn('status', ['awaiting_shipment'])->count();
        $navDisputes = \App\Models\OrderItem::where('status', 'disputed')->count();
      @endphp
      <div class="sidebar__group">Tienda</div>
      <a href="{{ route('admin.orders.index') }}" class="sidebar__link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
        <span>&#128722;</span> Pedidos
        @if($navDisputes)<span class="badge badge-danger" style="margin-left:auto">{{ $navDisputes }}</span>@elseif($navToShip)<span class="badge badge-gold" style="margin-left:auto">{{ $navToShip }}</span>@endif
      </a>
      <a href="{{ route('admin.coupons.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
        <span>&#9685;</span> Cupones
      </a>
      <a href="{{ route('admin.shipping.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
        <span>&#9685;</span> Envíos
      </a>
      <a href="{{ route('admin.countries.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.countries.*') ? 'active' : '' }}">
        <span>&#9685;</span> Países
      </a>

      <div class="sidebar__group">Clientes</div>
      <a href="{{ route('admin.sellers.index') }}" class="sidebar__link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
        <span>&#128100;</span> Vendedores
      </a>
      <a href="{{ route('admin.subscribers.index') }}" class="sidebar__link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">
        <span>&#9900;</span> Early Access
      </a>

      <div class="sidebar__group">Marketing</div>
      <a href="{{ route('admin.newsletter.index') }}" class="sidebar__link {{ request()->routeIs('admin.newsletter.index') ? 'active' : '' }}">
        <span>&#9993;</span> Newsletter
      </a>
      <a href="{{ route('admin.newsletter.subscribers') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.newsletter.subscribers') ? 'active' : '' }}">
        <span>&#9685;</span> Suscriptores
      </a>
      <a href="{{ route('admin.newsletter.lists.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.newsletter.lists.*') ? 'active' : '' }}">
        <span>&#10070;</span> Listas
      </a>

      <div class="sidebar__group">Contenido</div>
      <a href="{{ route('admin.pages.index') }}" class="sidebar__link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
        <span>&#128196;</span> P&aacute;ginas
      </a>

      <div class="sidebar__group">Sistema</div>
      <a href="{{ route('admin.translations') }}" class="sidebar__link {{ request()->routeIs('admin.translations*') ? 'active' : '' }}">
        <span>&#127760;</span> Traducciones
      </a>
      <a href="{{ route('admin.settings.index') }}" class="sidebar__link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <span>&#9881;</span> Configuraci&oacute;n
      </a>
    </nav>

    <div class="sidebar__footer">
      <div style="display:flex;align-items:center;gap:8px;padding:8px 0 14px;border-bottom:1px solid var(--a-border);margin-bottom:12px;">
        <span style="font-size:9px;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--a-subtle);">Idioma</span>
        <a href="{{ route('lang.switch', 'es') }}" style="font-size:11px;color:{{ app()->getLocale()==='es'?'var(--a-gold)':' var(--a-muted)' }};font-weight:{{ app()->getLocale()==='es'?'600':'400' }}">ES</a>
        <span style="color:var(--a-border);font-size:10px">|</span>
        <a href="{{ route('lang.switch', 'en') }}" style="font-size:11px;color:{{ app()->getLocale()==='en'?'var(--a-gold)':'var(--a-muted)' }};font-weight:{{ app()->getLocale()==='en'?'600':'400' }}">EN</a>
      </div>
      <a href="{{ route('home') }}" class="sidebar__link" style="padding:0;margin-bottom:10px;"><span>&#8599;</span> Ver sitio</a>
      @if(\App\Models\Setting::get('site_mode', 'early_access') !== 'live')
      <a href="{{ route('view.switch', 'live') }}" class="sidebar__link" style="padding:0;margin-bottom:10px;"><span>&#9673;</span> Vista previa Live</a>
      @endif
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" class="sidebar__link" style="width:100%;"><span>&#8683;</span> Cerrar sesi&oacute;n</button>
      </form>
    </div>
  </aside>

  <main class="main-content">
    <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
      <form method="POST" action="{{ route('admin.cache.clear') }}">@csrf
        <button type="submit" onclick="return confirm('Limpiar toda la cache?')" style="display:inline-flex;align-items:center;gap:6px;background:transparent;border:1px solid var(--a-border);color:var(--a-muted);font-family:var(--font);font-size:10px;font-weight:500;letter-spacing:.08em;text-transform:uppercase;padding:6px 14px;border-radius:3px;cursor:pointer;transition:all .2s;">
          &#129529; Limpiar cach&eacute;
        </button>
      </form>
    </div>
    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert--error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @yield('admin-content')
  </main>
</div>
<script>
document.querySelectorAll('.alert').forEach(el=>{el.style.transition='opacity .4s';setTimeout(()=>{el.style.opacity='0';setTimeout(()=>el.remove(),400);},6000);});
document.querySelectorAll('.radio-card').forEach(card=>{const inp=card.querySelector('input');if(inp?.checked)card.classList.add('selected');card.addEventListener('click',()=>{card.closest('.radio-group')?.querySelectorAll('.radio-card').forEach(c=>c.classList.remove('selected'));card.classList.add('selected');});});
</script>
@stack('scripts')
</body>
</html>