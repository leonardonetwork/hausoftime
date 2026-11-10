<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') — Haus of Time</title>
  <link rel="stylesheet" href="/css/app.css">
  <link rel="icon" type="image/png" href="/images/favicon.png">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    @media(min-width:768px){
      html,body{height:100vh;overflow:hidden;}
      .auth-shell{height:100vh;display:flex;flex-direction:column;overflow:hidden;}
      .auth-shell__main{flex:1;display:flex;align-items:center;justify-content:center;padding:20px;overflow:hidden;}
    }
    @media(max-width:767px){
      .auth-shell{display:flex;flex-direction:column;min-height:100vh;}
      .auth-shell__main{flex:1;display:flex;align-items:center;justify-content:center;padding:48px 20px;}
    }
  </style>
</head>
<body>
<div class="hot-cursor" id="hotCursor" aria-hidden="true">
  <div class="hot-cursor-ring"><div class="hot-cursor-dot"></div></div>
</div>

<div class="auth-shell">

  {{-- NAVBAR identico a layouts.app --}}
  <nav class="navbar scrolled" id="navbar" style="position:relative;background:rgba(0,0,0,.82);">
    <a href="{{ route('home') }}" class="navbar__brand">
      <img src="/images/logo.png" alt="Haus of Time">
    </a>
    <div class="navbar__nav">
      <a href="{{ route('evaluation.index') }}" class="navbar__link">{{ __('messages.nav.evaluation') }}</a>
      @auth
        @if(auth()->user()->isSeller())
          <a href="{{ route('seller.dashboard') }}" class="navbar__link">{{ __('messages.nav.dashboard') }}</a>
        @elseif(auth()->user()->isAdmin())
          <a href="{{ route('admin.dashboard') }}" class="navbar__link">Admin</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf
          <button type="submit" class="navbar__link" style="background:none;cursor:pointer;font-family:inherit;">{{ __('messages.nav.logout') }}</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="navbar__link">{{ __('messages.nav.login') }}</a>
      @endauth
      <div class="navbar__lang">
        <a href="{{ route('lang.switch','es') }}" class="{{ app()->getLocale()==='es'?'active':'' }}">ES</a>
        <span>|</span>
        <a href="{{ route('lang.switch','en') }}" class="{{ app()->getLocale()==='en'?'active':'' }}">EN</a>
      </div>
    </div>
    <button class="navbar__toggle" id="navToggle"><span></span><span></span><span></span></button>
    <div class="mobile-nav" id="mobileNav" style="display:none;">
      <a href="{{ route('evaluation.index') }}">{{ __('messages.nav.evaluation') }}</a>
      @guest
        <a href="{{ route('login') }}">{{ __('messages.nav.login') }}</a>
        <a href="{{ route('early-access.index') }}">{{ __('messages.nav.early_access') }}</a>
      @endguest
      <div class="navbar__lang" style="margin-top:16px">
        <a href="{{ route('lang.switch','es') }}" class="{{ app()->getLocale()==='es'?'active':'' }}">ES</a>
        <span>|</span>
        <a href="{{ route('lang.switch','en') }}" class="{{ app()->getLocale()==='en'?'active':'' }}">EN</a>
      </div>
    </div>
  </nav>

  <main class="auth-shell__main" style="background:var(--light-bg);">
    @yield('content')
  </main>

  @php $footerPages = \App\Models\Page::active()->orderBy('sort_order')->get(); @endphp
  <footer class="footer" style="flex-shrink:0;">
    <div class="footer__inner">
      <a href="{{ route('home') }}" class="footer__brand">
        <img src="/images/logo.png" alt="Haus of Time" style="height:22px;filter:brightness(0) invert(1);opacity:.65;">
      </a>
      <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
        <div class="footer__links">
          @foreach($footerPages as $fp)
            <a href="{{ url('/'.$fp->slug) }}" class="footer__link">{{ app()->getLocale()==='en'&&$fp->title_en?$fp->title_en:$fp->title_es }}</a>
          @endforeach
          <a href="{{ route('contact') }}" class="footer__link">{{ __('messages.footer.contact') }}</a>
        </div>
        <div class="footer__lang">
          <a href="{{ route('lang.switch','es') }}" class="{{ app()->getLocale()==='es'?'active':'' }}">ES</a>
          <span>|</span>
          <a href="{{ route('lang.switch','en') }}" class="{{ app()->getLocale()==='en'?'active':'' }}">EN</a>
        </div>
      </div>
      <p class="footer__copy">{{ __('messages.footer.copyright') }}</p>
    </div>
  </footer>

</div>

<script>
document.getElementById('navToggle')?.addEventListener('click',()=>{
  const m=document.getElementById('mobileNav');
  m.style.display=m.style.display==='block'?'none':'block';
});
(function(){
  const cur=document.getElementById('hotCursor');
  if(!cur)return;
  if(window.matchMedia('(hover:none),(pointer:coarse)').matches){cur.style.display='none';return;}
  document.documentElement.classList.add('hot-cursor-on');
  let mx=-500,my=-500,cx=-500,cy=-500,started=false;
  document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;if(!started){cx=mx;cy=my;started=true;cur.style.opacity='1';}},{passive:true});
  cur.style.opacity='0';cur.style.transition='opacity .2s';
  function animate(){cx+=(mx-cx)*.15;cy+=(my-cy)*.15;cur.style.transform=`translate(${cx}px,${cy}px) translate(-50%,-50%)`;requestAnimationFrame(animate);}
  animate();
})();
</script>
@stack('scripts')
</body>
</html>
