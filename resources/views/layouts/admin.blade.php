<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ request()->cookie('hot_admin_theme') === 'dark' ? 'dark' : 'light' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin — @yield('title', 'Dashboard') — Haus of Time</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ═══ HAUS OF TIME · Admin — tema chiaro (avorio/oro) e scuro ═══ */
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html[data-theme="light"]{
      --a-bg:#f3ede2;--a-bg2:#fbf8f2;--a-bg3:#f3ecdf;--a-side:#ece3d2;--a-side2:#e5d9c3;
      --a-border:#e1d4bd;--a-border2:#ebe2d2;--a-text:#29231b;--a-muted:#6d6252;--a-subtle:#9d8f78;
      --a-gold:#9c7432;--a-gold2:#85612a;--a-on-gold:#fffaf0;--a-gold-soft:rgba(156,116,50,.11);
      --a-success:#2f7d55;--a-danger:#a8392f;--a-warning:#a76d10;--a-info:#355a8c;
      --a-ok-bg:rgba(47,125,85,.11);--a-err-bg:rgba(168,57,47,.10);--a-warn-bg:rgba(167,109,16,.12);--a-info-bg:rgba(53,90,140,.10);
      --a-shadow:0 1px 2px rgba(60,45,20,.05),0 8px 28px -14px rgba(60,45,20,.22);
      --a-logo-light:none;--a-logo-dark:block;color-scheme:light}
    html[data-theme="dark"]{
      --a-bg:#0e0e0d;--a-bg2:#161614;--a-bg3:#1d1d1b;--a-side:#121211;--a-side2:#1b1a18;
      --a-border:#2a2a28;--a-border2:#222220;--a-text:#ece7dc;--a-muted:#a09d93;--a-subtle:#6a6862;
      --a-gold:#c4a06a;--a-gold2:#b08b54;--a-on-gold:#16130e;--a-gold-soft:rgba(196,160,106,.10);
      --a-success:#5fbf8a;--a-danger:#e08080;--a-warning:#e0b870;--a-info:#8fb0e0;
      --a-ok-bg:rgba(61,138,94,.16);--a-err-bg:rgba(155,51,51,.18);--a-warn-bg:rgba(200,144,42,.16);--a-info-bg:rgba(58,85,128,.2);
      --a-shadow:0 1px 2px rgba(0,0,0,.3),0 10px 30px -16px rgba(0,0,0,.7);
      --a-logo-light:block;--a-logo-dark:none;color-scheme:dark}
    :root{--font:'Poppins',system-ui,sans-serif;--warning:var(--a-warning);--success:var(--a-success);--danger:var(--a-danger);--text-muted:var(--a-muted);--text:var(--a-text);--gold:var(--a-gold);--border:var(--a-border);--bg-border:var(--a-border);--text-secondary:var(--a-muted)}
    html,body{height:100%}
    body{font-family:var(--font);background:var(--a-bg);color:var(--a-text);font-size:14.5px;line-height:1.6;-webkit-font-smoothing:antialiased;transition:background .25s,color .25s}
    a{color:inherit;text-decoration:none}img{max-width:100%;display:block}
    ::selection{background:var(--a-gold);color:var(--a-on-gold)}
    .app-layout{display:grid;grid-template-columns:272px 1fr;min-height:100vh}

    /* Sidebar */
    .sidebar{background:var(--a-side);border-right:1px solid var(--a-border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;scrollbar-width:thin}
    .sidebar__header{padding:28px 26px 22px}
    .sidebar__logo{height:24px;width:auto}
    .sidebar__logo--light{display:var(--a-logo-light)}.sidebar__logo--dark{display:var(--a-logo-dark)}
    .sidebar__tag{margin-top:10px;font-size:9.5px;font-weight:600;letter-spacing:.32em;text-transform:uppercase;color:var(--a-gold)}
    .sidebar__nav{padding:6px 14px 20px;flex:1}
    .sidebar__group{font-size:10px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:var(--a-subtle);padding:20px 12px 8px}
    .sidebar__link{position:relative;display:flex;align-items:center;gap:12px;padding:9px 12px;margin:1px 0;font-size:14px;font-weight:400;color:var(--a-muted);transition:background .18s,color .18s;border-radius:10px;cursor:pointer;background:none;border:0;width:100%;font-family:var(--font);text-align:left}
    .sidebar__link svg{width:18px;height:18px;flex:none;stroke:currentColor;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;opacity:.85}
    .sidebar__link:hover{background:var(--a-side2);color:var(--a-text)}
    .sidebar__link.active{background:var(--a-bg2);color:var(--a-text);font-weight:500;box-shadow:var(--a-shadow)}
    .sidebar__link.active svg{color:var(--a-gold);opacity:1}
    .sidebar__link.active::before{content:"";position:absolute;left:-14px;top:9px;bottom:9px;width:3px;border-radius:0 3px 3px 0;background:var(--a-gold)}
    .sidebar__sub{padding-left:42px;font-size:13.5px}
    .sidebar__sub::after{content:"";position:absolute;left:21px;top:50%;width:5px;height:5px;border-radius:50%;background:currentColor;opacity:.35;transform:translateY(-50%)}
    .sidebar__sub.active::after{background:var(--a-gold);opacity:1}
    .sidebar__count{margin-left:auto;min-width:22px;height:20px;padding:0 7px;border-radius:10px;font-size:11px;font-weight:600;display:grid;place-items:center;background:var(--a-gold);color:var(--a-on-gold)}
    .sidebar__count--danger{background:var(--a-danger);color:#fff}
    .sidebar__footer{padding:16px 22px 22px;border-top:1px solid var(--a-border)}
    .sidebar__user{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    .sidebar__avatar{width:36px;height:36px;border-radius:50%;background:var(--a-gold);color:var(--a-on-gold);display:grid;place-items:center;font-weight:600;font-size:14px;flex:none}
    .sidebar__user-name{font-size:13.5px;font-weight:500;color:var(--a-text);line-height:1.3}
    .sidebar__user-role{font-size:10px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--a-subtle)}

    /* Topbar */
    .main-wrap{min-width:0;display:flex;flex-direction:column}
    .topbar{position:sticky;top:0;z-index:40;display:flex;align-items:center;gap:12px;padding:14px 48px;background:color-mix(in srgb,var(--a-bg) 82%,transparent);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid var(--a-border2)}
    .topbar__burger{display:none;background:none;border:0;color:var(--a-text);cursor:pointer}
    .topbar__mode{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:30px;font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;background:var(--a-warn-bg);color:var(--a-warning)}
    .topbar__mode::before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
    .topbar__mode.is-live{background:var(--a-ok-bg);color:var(--a-success)}
    .topbar__spacer{flex:1}
    .topbar__btn{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 14px;border-radius:10px;border:1px solid var(--a-border);background:var(--a-bg2);color:var(--a-muted);font:500 12.5px var(--font);cursor:pointer;transition:all .18s}
    .topbar__btn:hover{color:var(--a-text);border-color:var(--a-gold)}
    .topbar__btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
    .topbar__btn--icon{width:38px;padding:0;justify-content:center}
    .theme-sun{display:var(--a-logo-light)}.theme-moon{display:var(--a-logo-dark)}

    .main-content{padding:40px 48px 64px;max-width:1680px;width:100%}
    .page-title{font-size:1.95rem;font-weight:600;letter-spacing:-.02em;color:var(--a-text);margin-bottom:6px;line-height:1.2}
    .page-subtitle{font-size:14px;color:var(--a-muted);margin-bottom:34px}
    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:32px}
    .stat-card{background:var(--a-bg2);border:1px solid var(--a-border2);padding:22px 24px;border-radius:16px;box-shadow:var(--a-shadow);transition:transform .2s}
    a.stat-card:hover{transform:translateY(-2px)}
    .stat-card__num{font-size:2rem;font-weight:600;color:var(--a-text);line-height:1.1;margin-bottom:8px;letter-spacing:-.02em}
    .stat-card__label{font-size:10.5px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--a-subtle)}
    .card{background:var(--a-bg2);border:1px solid var(--a-border2);padding:28px;border-radius:16px;box-shadow:var(--a-shadow)}
    .card__num{font-size:10.5px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--a-gold);margin-bottom:14px}
    .table-wrap{background:var(--a-bg2);border:1px solid var(--a-border2);border-radius:16px;overflow:hidden;box-shadow:var(--a-shadow)}
    .data-table{width:100%;border-collapse:collapse;font-size:14px}
    .data-table th{text-align:left;padding:13px 18px;font-size:10.5px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--a-subtle);border-bottom:1px solid var(--a-border);background:var(--a-bg3)}
    .data-table td{padding:15px 18px;border-bottom:1px solid var(--a-border2);color:var(--a-muted);vertical-align:middle}
    .data-table tr:last-child td{border-bottom:0}
    .data-table tr:hover td{background:var(--a-gold-soft)}.data-table td:first-child{color:var(--a-text);font-weight:500}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;font-family:var(--font);font-size:12.5px;font-weight:500;letter-spacing:.02em;padding:10px 20px;border-radius:10px;border:1px solid transparent;cursor:pointer;transition:all .18s;text-decoration:none;white-space:nowrap;line-height:1.2}
    .btn--primary{background:var(--a-gold);color:var(--a-on-gold);border-color:var(--a-gold)}.btn--primary:hover{background:var(--a-gold2);border-color:var(--a-gold2)}
    .btn--ghost{background:var(--a-bg2);color:var(--a-muted);border-color:var(--a-border)}.btn--ghost:hover{border-color:var(--a-gold);color:var(--a-text)}
    .btn--success{background:var(--a-success);color:#fff}.btn--success:hover{filter:brightness(1.08)}
    .btn--danger{background:var(--a-danger);color:#fff}.btn--danger:hover{filter:brightness(1.08)}
    .btn--sm{padding:7px 13px;font-size:12px;border-radius:8px}.btn--lg{padding:14px 30px;font-size:13.5px}
    .form-group{margin-bottom:20px}
    .form-label{display:block;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--a-muted);margin-bottom:8px}
    .form-control,.form-control--select{width:100%;background:var(--a-bg);border:1px solid var(--a-border);border-radius:10px;color:var(--a-text);font-family:var(--font);font-size:14px;padding:11px 14px;outline:none;transition:border-color .18s,box-shadow .18s}
    .form-control:focus{border-color:var(--a-gold);box-shadow:0 0 0 4px var(--a-gold-soft)}.form-control::placeholder{color:var(--a-subtle)}
    select.form-control option{background:var(--a-bg2);color:var(--a-text)}textarea.form-control{min-height:110px;resize:vertical}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    input[type=checkbox],input[type=radio]{accent-color:var(--a-gold)}
    .alert{padding:14px 18px;font-size:13.5px;margin-bottom:20px;border-left:3px solid transparent;border-radius:10px}
    .alert--success{background:var(--a-ok-bg);border-color:var(--a-success);color:var(--a-success)}
    .alert--error{background:var(--a-err-bg);border-color:var(--a-danger);color:var(--a-danger)}
    .alert--warning{background:var(--a-warn-bg);border-color:var(--a-warning);color:var(--a-warning)}
    .badge{display:inline-flex;align-items:center;padding:3px 10px;font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;border-radius:20px;white-space:nowrap}
    .badge-success{background:var(--a-ok-bg);color:var(--a-success)}.badge-warning{background:var(--a-warn-bg);color:var(--a-warning)}
    .badge-danger{background:var(--a-err-bg);color:var(--a-danger)}.badge-info{background:var(--a-info-bg);color:var(--a-info)}
    .badge-gold{background:var(--a-gold-soft);color:var(--a-gold)}.badge-secondary{background:var(--a-bg3);color:var(--a-muted)}
    .tabs{display:flex;gap:4px;background:var(--a-bg3);padding:4px;width:fit-content;max-width:100%;overflow-x:auto;margin-bottom:24px;border-radius:12px;border:1px solid var(--a-border2)}
    .tab{padding:8px 16px;font-family:var(--font);font-size:13px;font-weight:500;color:var(--a-muted);cursor:pointer;transition:all .18s;background:none;border:none;border-radius:9px;white-space:nowrap}
    .tab.active,a.tab.active{background:var(--a-bg2);color:var(--a-text);box-shadow:var(--a-shadow)}.tab:hover:not(.active){color:var(--a-text)}a.tab{text-decoration:none}
    .panel-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
    .filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.filter-bar .form-control{width:auto;min-width:150px}
    .radio-group{display:flex;gap:12px;flex-wrap:wrap}
    .radio-card{flex:1;min-width:180px;border:1px solid var(--a-border);border-radius:12px;padding:18px;cursor:pointer;transition:all .18s;display:flex;align-items:center;gap:12px;background:var(--a-bg)}
    .radio-card input{display:none}.radio-card.selected{border-color:var(--a-gold);background:var(--a-gold-soft)}
    .radio-card__dot{width:16px;height:16px;border-radius:50%;border:1.5px solid var(--a-border);flex-shrink:0;transition:all .18s}
    .radio-card.selected .radio-card__dot{border-color:var(--a-gold);background:var(--a-gold);box-shadow:inset 0 0 0 3px var(--a-bg2)}
    .radio-card__label{font-size:14px;font-weight:500;color:var(--a-text)}
    .watch-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    .watch-card{background:var(--a-bg2);border:1px solid var(--a-border2);border-radius:14px;overflow:hidden;box-shadow:var(--a-shadow)}
    .watch-card__img{aspect-ratio:4/3;overflow:hidden;background:var(--a-bg3)}.watch-card__img img{width:100%;height:100%;object-fit:cover}
    .watch-card__body{padding:16px 18px}.watch-card__brand{font-size:10px;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--a-gold);margin-bottom:3px}
    .watch-card__name{font-size:14px;font-weight:500;color:var(--a-text)}.watch-card__price{font-size:15px;font-weight:600;color:var(--a-text)}
    .watch-card__footer{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid var(--a-border2)}
    .grid-2{display:grid;grid-template-columns:1fr 1fr}
    .site-mode{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;padding:24px 28px;border-radius:16px;margin-bottom:30px;border:1px solid var(--a-border2);background:var(--a-bg2);box-shadow:var(--a-shadow)}
    .site-mode.is-live{border-color:var(--a-success)}
    .site-mode__dot{width:11px;height:11px;border-radius:50%;background:var(--a-warning);box-shadow:0 0 0 5px var(--a-warn-bg)}
    .site-mode.is-live .site-mode__dot{background:var(--a-success);box-shadow:0 0 0 5px var(--a-ok-bg)}
    .switch{position:relative;display:inline-block;width:44px;height:24px;flex:none}.switch input{opacity:0;width:0;height:0}
    .switch span{position:absolute;inset:0;background:var(--a-border);border-radius:24px;transition:.2s;cursor:pointer}
    .switch span::before{content:"";position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
    .switch input:checked+span{background:var(--a-success)}.switch input:checked+span::before{transform:translateX(20px)}
    .kv{display:grid;grid-template-columns:auto 1fr;gap:8px 22px;font-size:14px}.kv dt{color:var(--a-subtle)}.kv dd{color:var(--a-text)}
    .timeline{list-style:none;border-left:1px solid var(--a-border);margin-left:6px}.timeline li{position:relative;padding:0 0 16px 20px;font-size:13.5px;color:var(--a-muted)}
    .timeline li::before{content:"";position:absolute;left:-4px;top:6px;width:7px;height:7px;border-radius:50%;background:var(--a-gold)}
    .timeline time{display:block;font-size:11px;color:var(--a-subtle)}
    details>summary{list-style:none;cursor:pointer}details>summary::-webkit-details-marker{display:none}
    .sidebar-scrim{display:none}
    @media(max-width:1280px){.main-content{padding:32px 32px 56px}.topbar{padding:12px 32px}}
    @media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)!important}.watch-grid{grid-template-columns:repeat(2,1fr)}.grid-2{grid-template-columns:1fr}}
    @media(max-width:900px){
      .app-layout{grid-template-columns:1fr}
      .sidebar{position:fixed;left:0;top:0;z-index:200;width:280px;transform:translateX(-102%);transition:transform .3s}
      body.side-open .sidebar{transform:none;box-shadow:20px 0 60px rgba(0,0,0,.25)}
      body.side-open .sidebar-scrim{display:block;position:fixed;inset:0;z-index:150;background:rgba(0,0,0,.35)}
      .topbar__burger{display:block}.topbar{padding:12px 18px}.main-content{padding:24px 18px 48px}
      .watch-grid,.stats-grid,.form-row{grid-template-columns:1fr!important}
      .topbar__btn span{display:none}
    }
  </style>
  <script>(function(){try{var t=localStorage.getItem('hot_admin_theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
  @stack('styles')
</head>
<body>
<div class="app-layout">
  @php
    $ico = [
      'dash'   => '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>',
      'watch'  => '<circle cx="12" cy="12" r="6"/><path d="M12 9v3l2 1.5M9 3h6l1 3.5M9 21h6l1-3.5M9 3 8 6.5M9 21l-1-3.5"/>',
      'bag'    => '<path d="M5 8h14l-1.2 12.2a1 1 0 0 1-1 .8H7.2a1 1 0 0 1-1-.8L5 8z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
      'users'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.6 3.4-5.5 6.5-5.5s5.7 1.9 6.5 5.5"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18.5 14.8c1.6.8 2.6 2.5 3 5.2"/>',
      'star'   => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
      'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
      'page'   => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
      'globe'  => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9S14.5 18.4 12 21c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>',
      'cog'    => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H9a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V9a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/>',
      'out'    => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h11"/>',
    ];
    $svg = fn ($k) => '<svg viewBox="0 0 24 24" aria-hidden="true">' . $ico[$k] . '</svg>';
    $navToShip = \App\Models\OrderItem::where('status', 'awaiting_shipment')->count();
    $navDisputes = \App\Models\OrderItem::where('status', 'disputed')->count();
    $navPendingWatches = \App\Models\Watch::where('status', 'pending')->count();
    $isLive = \App\Models\Setting::get('site_mode', 'early_access') === 'live';
    $me = auth()->user();
  @endphp

  <aside class="sidebar" id="adminSidebar">
    <div class="sidebar__header">
      <a href="{{ route('admin.dashboard') }}">
        <img src="/images/logo-dark.png" alt="Haus of Time" class="sidebar__logo sidebar__logo--dark">
        <img src="/images/logo.png" alt="Haus of Time" class="sidebar__logo sidebar__logo--light">
      </a>
      <div class="sidebar__tag">Panel de gestión</div>
    </div>

    <nav class="sidebar__nav">
      <a href="{{ route('admin.dashboard') }}" class="sidebar__link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">{!! $svg('dash') !!} Panel</a>

      <div class="sidebar__group">Catálogo</div>
      <a href="{{ route('admin.watches.index') }}" class="sidebar__link {{ request()->routeIs('admin.watches.*') ? 'active' : '' }}">{!! $svg('watch') !!} Relojes
        @if($navPendingWatches)<span class="sidebar__count">{{ $navPendingWatches }}</span>@endif</a>
      <a href="{{ route('admin.brands.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">Marcas</a>
      <a href="{{ route('admin.categories.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Categorías</a>

      <div class="sidebar__group">Tienda</div>
      <a href="{{ route('admin.orders.index') }}" class="sidebar__link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">{!! $svg('bag') !!} Pedidos
        @if($navDisputes)<span class="sidebar__count sidebar__count--danger">{{ $navDisputes }}</span>@elseif($navToShip)<span class="sidebar__count">{{ $navToShip }}</span>@endif</a>
      <a href="{{ route('admin.coupons.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">Cupones</a>
      <a href="{{ route('admin.shipping.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">Envíos</a>
      <a href="{{ route('admin.countries.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.countries.*') ? 'active' : '' }}">Países</a>

      <div class="sidebar__group">Clientes</div>
      <a href="{{ route('admin.sellers.index') }}" class="sidebar__link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">{!! $svg('users') !!} Vendedores</a>
      <a href="{{ route('admin.subscribers.index') }}" class="sidebar__link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">{!! $svg('star') !!} Early Access</a>

      <div class="sidebar__group">Marketing</div>
      <a href="{{ route('admin.newsletter.index') }}" class="sidebar__link {{ request()->routeIs('admin.newsletter.index', 'admin.newsletter.create', 'admin.newsletter.edit') ? 'active' : '' }}">{!! $svg('mail') !!} Newsletter</a>
      <a href="{{ route('admin.newsletter.subscribers') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.newsletter.subscribers') ? 'active' : '' }}">Suscriptores</a>
      <a href="{{ route('admin.newsletter.lists.index') }}" class="sidebar__link sidebar__sub {{ request()->routeIs('admin.newsletter.lists.*') ? 'active' : '' }}">Listas</a>

      <div class="sidebar__group">Contenido y sistema</div>
      <a href="{{ route('admin.pages.index') }}" class="sidebar__link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">{!! $svg('page') !!} Páginas</a>
      <a href="{{ route('admin.translations') }}" class="sidebar__link {{ request()->routeIs('admin.translations*') ? 'active' : '' }}">{!! $svg('globe') !!} Traducciones</a>
      <a href="{{ route('admin.settings.index') }}" class="sidebar__link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">{!! $svg('cog') !!} Configuración</a>
    </nav>

    <div class="sidebar__footer">
      <div class="sidebar__user">
        <div class="sidebar__avatar">{{ mb_strtoupper(mb_substr($me->first_name ?: $me->name, 0, 1)) }}</div>
        <div><div class="sidebar__user-name">{{ $me->name }}</div><div class="sidebar__user-role">Administrador</div></div>
      </div>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" class="sidebar__link">{!! $svg('out') !!} Cerrar sesión</button>
      </form>
    </div>
  </aside>
  <div class="sidebar-scrim" onclick="document.body.classList.remove('side-open')"></div>

  <div class="main-wrap">
    <header class="topbar">
      <button class="topbar__burger" type="button" onclick="document.body.classList.toggle('side-open')" aria-label="Menú">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
      <a href="{{ route('admin.settings.index') }}" class="topbar__mode {{ $isLive ? 'is-live' : '' }}" title="Modo del sitio">{{ $isLive ? 'Live' : 'Early Access' }}</a>
      <div class="topbar__spacer"></div>
      <a href="{{ route('home') }}" target="_blank" class="topbar__btn"><svg viewBox="0 0 24 24"><path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/></svg><span>Ver sitio</span></a>
      @if(!$isLive)
        <a href="{{ route('view.switch', 'live') }}" target="_blank" class="topbar__btn"><svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><span>Vista previa Live</span></a>
      @endif
      <form method="POST" action="{{ route('admin.cache.clear') }}">@csrf
        <button type="submit" class="topbar__btn" onclick="return confirm('¿Vaciar toda la caché?')"><svg viewBox="0 0 24 24"><path d="M4 4v6h6M20 20v-6h-6"/><path d="M20 10A8 8 0 0 0 6 5.3L4 10M4 14a8 8 0 0 0 14 4.7l2-4.7"/></svg><span>Vaciar caché</span></button>
      </form>
      <div style="display:flex;border:1px solid var(--a-border);border-radius:10px;overflow:hidden">
        <a href="{{ route('lang.switch', 'es') }}" class="topbar__btn" style="border:0;border-radius:0;{{ app()->getLocale()==='es' ? 'color:var(--a-gold)' : '' }}">ES</a>
        <a href="{{ route('lang.switch', 'en') }}" class="topbar__btn" style="border:0;border-radius:0;{{ app()->getLocale()==='en' ? 'color:var(--a-gold)' : '' }}">EN</a>
      </div>
      <button type="button" class="topbar__btn topbar__btn--icon" id="themeToggle" aria-label="Cambiar tema">
        <svg class="theme-moon" viewBox="0 0 24 24"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/></svg>
        <svg class="theme-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
      </button>
    </header>

  <main class="main-content">
    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert--error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @yield('admin-content')
  </main>
  </div>
</div>
<script>
document.getElementById('themeToggle').addEventListener('click',function(){
  var h=document.documentElement,t=h.getAttribute('data-theme')==='dark'?'light':'dark';
  h.setAttribute('data-theme',t);
  try{localStorage.setItem('hot_admin_theme',t);}catch(e){}
  document.cookie='hot_admin_theme='+t+';path=/;max-age=31536000;samesite=lax';
});
document.querySelectorAll('.alert').forEach(el=>{el.style.transition='opacity .4s';setTimeout(()=>{el.style.opacity='0';setTimeout(()=>el.remove(),400);},6000);});
document.querySelectorAll('.radio-card').forEach(card=>{const inp=card.querySelector('input');if(inp?.checked)card.classList.add('selected');card.addEventListener('click',()=>{card.closest('.radio-group')?.querySelectorAll('.radio-card').forEach(c=>c.classList.remove('selected'));card.classList.add('selected');});});
</script>
@stack('scripts')
</body>
</html>