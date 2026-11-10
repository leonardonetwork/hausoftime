@section('title', 'Haus of Time — Luxury Watch Marketplace')
@extends('layouts.app')
@section('title', 'Haus of Time')

@section('content')

{{-- HERO --}}
<section class="hero">
  <div class="hero__bg"></div>
  <div class="hero__overlay"></div>
  <div class="hero__content">
    <span class="hero__eyebrow">{{ app()->getLocale() === 'es' ? 'El Marketplace de Relojes de Lujo Basado en Confianza' : 'The Luxury Watch Marketplace Built on Trust' }}</span>
    <div class="hero__logo-img">
      <img src="/images/index02.png" alt="Haus of Time">
    </div>
    <p class="hero__subtitle">{{ __('messages.home.hero_subtitle') }}</p>
    <div class="hero__actions">
      <a href="{{ route('early-access.index') }}" class="btn btn--primary">{{ __('messages.home.hero_cta_1') }}</a>
      <a href="#proceso" class="btn btn--outline">{{ __('messages.home.hero_cta_2') }}</a>
    </div>
  </div>
</section>

{{-- TICKER --}}
@php $brands = ['RICHARD MILLE','VACHERON CONSTANTIN','A. LANGE & SÖHNE','CARTIER','IWC','JAEGER-LECOULTRE','HUBLOT','OMEGA','BREITLING','ROLEX','PATEK PHILIPPE']; @endphp
<div class="ticker">
  <div class="ticker__track">
    @foreach(array_merge($brands,$brands) as $b)
      <span class="ticker__item">{{ $b }}</span><span class="ticker__dot"> · </span>
    @endforeach
  </div>
</div>

{{-- ═══════════════════════════════════════════════════
     FOUNDING SELLERS
     - fs-left: label + title + body + tag (NO disclaimer)
     - fs-right: benefits 01-04 + disclaimer in fondo
     - fs-wrap background-image: orologio bottom-left
═══════════════════════════════════════════════════ --}}
<section class="fs-wrap" id="founding">

  {{-- COLONNA SINISTRA: testo + tag. L'orologio appare sotto --}}
  <div class="fs-left">
    <p class="fs-label fs-r">{{ app()->getLocale() === 'es' ? 'Programa' : 'Program' }}</p>
    <h2 class="fs-title fs-r">Founding<br>Sellers</h2>
    <p class="fs-body fs-r">
      {{ app()->getLocale() === 'es'
        ? 'Un número limitado de vendedores puede incorporarse antes del lanzamiento público. Los vendedores fundadores acceden a condiciones preferentes y visibilidad prioritaria desde el primer día.'
        : 'A limited number of sellers can join before the public launch. Founding sellers get preferential terms and priority visibility from day one.' }}
    </p>
    <div class="fs-tag fs-r">
      <span class="fs-dot"></span>{{ __('messages.home.founding_badge') }}
    </div>
    {{-- L'orologio appare qui sotto come background della sezione --}}
  </div>

  {{-- COLONNA DESTRA: benefits + disclaimer in fondo --}}
  <div class="fs-right">
    <div class="fs-benefits">
      @foreach([
        ['01', __('messages.home.benefit_1_title'), __('messages.home.benefit_1_desc')],
        ['02', __('messages.home.benefit_2_title'), __('messages.home.benefit_2_desc')],
        ['03', __('messages.home.benefit_3_title'), __('messages.home.benefit_3_desc')],
        ['04', __('messages.home.benefit_4_title'), __('messages.home.benefit_4_desc')],
      ] as [$n, $t, $d])
      <div class="fs-benefit fs-r">
        <div class="fs-num">{{ $n }}</div>
        <div>
          <div class="fs-benefit-title">{{ $t }}</div>
          <p class="fs-benefit-desc">{{ $d }}</p>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Disclaimer in fondo alla colonna DESTRA, con bordino sinistro --}}
    <div class="fs-disclaimer fs-r">
      {{ __('messages.home.founding_note') }}
    </div>
  </div>

</section>

{{-- TRUST --}}
<section class="ht-trust" id="trust">
  <div class="ht-trust-header">
    <div>
      <span class="ht-eyebrow">{{ app()->getLocale() === 'es' ? 'Estructura' : 'Structure' }}</span>
      <h2 class="ht-trust-title ht-r">
        {{ app()->getLocale() === 'es' ? 'Infraestructura' : 'Trust' }}<br>
        {{ app()->getLocale() === 'es' ? 'de confianza' : 'Infrastructure' }}
      </h2>
    </div>
    <p class="ht-trust-intro ht-r ht-d1">{{ __('messages.home.trust_subtitle') }}</p>
  </div>
  <div class="ht-trust-grid">
    <div class="ht-trust-card ht-r">
      <svg class="ht-tc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <div class="ht-tc-num">01</div>
      <div class="ht-tc-title">{{ __('messages.home.pillar_1_title') }}</div>
      <p class="ht-tc-body">{{ __('messages.home.pillar_1_desc') }}</p>
      <div class="ht-tc-tag">{{ __('messages.home.pillar_1_badge') }}</div>
    </div>
    <div class="ht-trust-card ht-r ht-d1">
      <svg class="ht-tc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <div class="ht-tc-num">02</div>
      <div class="ht-tc-title">{{ __('messages.home.pillar_2_title') }}</div>
      <p class="ht-tc-body">{{ __('messages.home.pillar_2_desc') }}</p>
      <div class="ht-tc-tag">{{ __('messages.home.pillar_2_badge') }}</div>
    </div>
    <div class="ht-trust-card ht-r ht-d2">
      <svg class="ht-tc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      <div class="ht-tc-num">03</div>
      <div class="ht-tc-title">{{ __('messages.home.pillar_3_title') }}</div>
      <p class="ht-tc-body">{{ __('messages.home.pillar_3_desc') }}</p>
      <div class="ht-tc-tag">{{ __('messages.home.pillar_3_badge') }}</div>
    </div>
    <div class="ht-trust-card ht-r ht-d3">
      <svg class="ht-tc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      <div class="ht-tc-num">04</div>
      <div class="ht-tc-title">{{ __('messages.home.pillar_4_title') }}</div>
      <p class="ht-tc-body">{{ __('messages.home.pillar_4_desc') }}</p>
      <div class="ht-tc-tag">{{ __('messages.home.pillar_4_badge') }}</div>
    </div>
  </div>
</section>

{{-- PROCESS --}}
<section class="hp-process" id="proceso">
  <div class="hp-process-bg"></div>
  <div class="hp-process-inner">
    <div class="hp-process-header hp-r">
      <span class="hp-eyebrow">{{ __('messages.home.process_title') }}</span>
      <h2 class="hp-process-title">
        {{ app()->getLocale() === 'es' ? 'Proceso simple.' : 'Simple process.' }}<br>
        <em>{{ app()->getLocale() === 'es' ? 'Seguridad total.' : 'Total security.' }}</em>
      </h2>
      <p class="hp-process-sub">{{ __('messages.home.process_desc') }}</p>
    </div>
    <div class="hp-flow hp-r hp-d1">
      <div class="hp-flow-node">
        <div class="hp-flow-circle">{{ app()->getLocale() === 'es' ? 'VENDEDOR' : 'SELLER' }}</div>
        <span class="hp-flow-label">{{ app()->getLocale() === 'es' ? 'ORIGEN' : 'ORIGIN' }}</span>
      </div>
      <div class="hp-flow-arrow">→</div>
      <div class="hp-flow-node">
        <div class="hp-flow-circle hp-hub">AUTENT.</div>
        <span class="hp-flow-label">HUB MADRID</span>
      </div>
      <div class="hp-flow-arrow">→</div>
      <div class="hp-flow-node">
        <div class="hp-flow-circle">{{ app()->getLocale() === 'es' ? 'COMPRADOR' : 'BUYER' }}</div>
        <span class="hp-flow-label">{{ app()->getLocale() === 'es' ? 'DESTINO' : 'DESTINATION' }}</span>
      </div>
    </div>
    <div class="hp-cards">
      <div class="hp-card hp-r"><div class="hp-card-num">01</div><div class="hp-card-title">{{ __('messages.home.step_1_title') }}</div><p class="hp-card-body">{{ __('messages.home.step_1_desc') }}</p><div class="hp-card-tag">{{ __('messages.home.step_1_badge') }}</div></div>
      <div class="hp-card hp-r hp-d1"><div class="hp-card-num">02 <span>{{ __('messages.home.step_2_included') }}</span></div><div class="hp-card-title">{{ __('messages.home.step_2_title') }}</div><p class="hp-card-body">{{ __('messages.home.step_2_desc') }}</p><div class="hp-card-tag">{{ __('messages.home.step_2_badge') }}</div></div>
      <div class="hp-card hp-r hp-d2"><div class="hp-card-num">03</div><div class="hp-card-title">{{ __('messages.home.step_3_title') }}</div><p class="hp-card-body">{{ __('messages.home.step_3_desc') }}</p><div class="hp-card-tag">{{ __('messages.home.step_3_badge') }}</div></div>
    </div>
  </div>
</section>

{{-- STATEMENT --}}
<div class="fs-statement">
  <div class="fs-statement-bg"></div>
  <div class="fs-statement-overlay"></div>
  <div class="fs-statement-content">
    <p class="fs-statement-line1">{{ app()->getLocale() === 'es' ? 'Los relojes de lujo son activos.' : 'Luxury watches are assets.' }}</p>
    <p class="fs-statement-line2">{{ app()->getLocale() === 'es' ? 'Los activos merecen infraestructura a su altura.' : 'Assets deserve infrastructure at their level.' }}</p>
  </div>
</div>

{{-- EARLY ACCESS CTA --}}
<section class="fs-access6" id="cta">
  <div class="fs-access6-inner">
    <div>
      <p class="fs-access6-eyebrow">{{ app()->getLocale() === 'es' ? 'Vendedores Fundadores' : 'Founding Sellers' }}</p>
      <h2 class="fs-access6-title">{{ __('messages.home.cta_title') }}</h2>
      <p class="fs-access6-body">{{ __('messages.home.cta_subtitle') }}</p>
      <div class="fs-access6-note">{{ __('messages.home.cta_note_1') }}<br>{{ __('messages.home.cta_note_2') }}</div>
    </div>
    <div>
      <div class="fs-access6-table">
        @foreach([
          [__('messages.home.cta_table_commission'), __('messages.home.cta_val_commission')],
          [__('messages.home.cta_table_visibility'), __('messages.home.cta_val_visibility')],
          [__('messages.home.cta_table_onboarding'), __('messages.home.cta_val_onboarding')],
          [__('messages.home.cta_table_auth'),       __('messages.home.cta_val_auth')],
          [__('messages.home.cta_table_logistics'),  __('messages.home.cta_val_logistics')],
        ] as [$l, $v])
        <div class="fs-access6-row">
          <p class="fs-access6-label">{{ $l }}</p>
          <p class="fs-access6-value">{{ $v }}</p>
        </div>
        @endforeach
      </div>
      <div class="fs-access6-cta-wrap">
        <a href="{{ route('early-access.index') }}" class="btn btn--dark">{{ __('messages.home.cta_button') }}</a>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('ht-in','hp-in','fs-in'); });
}, { threshold: 0.1 });
document.querySelectorAll('.ht-r,.hp-r,.fs-r').forEach(el => obs.observe(el));

(function(){
  const cur = document.getElementById('hotCursor');
  if(!cur || window.matchMedia('(hover:none)').matches) return;
  document.documentElement.classList.add('hot-cursor-on');
  let mx=0,my=0,cx=0,cy=0;
  document.addEventListener('mousemove', e => { mx=e.clientX; my=e.clientY; }, {passive:true});
  (function anim(){ cx+=(mx-cx)*.15; cy+=(my-cy)*.15; cur.style.transform=`translate(${cx}px,${cy}px) translate(-50%,-50%)`; requestAnimationFrame(anim); })();
  document.querySelectorAll('a,button').forEach(el => {
    el.addEventListener('mouseenter', () => document.documentElement.classList.add('hot-cursor-hover'));
    el.addEventListener('mouseleave', () => document.documentElement.classList.remove('hot-cursor-hover'));
  });
})();
</script>
@endpush
