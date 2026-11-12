@extends('layouts.shop')
@php
  $en = app()->getLocale() === 'en';
  $t = fn ($es, $eng) => $en ? $eng : $es;
  $priceFmt = fn ($v) => \App\Models\Watch::display($v);
  $pick = $featured->take(8);
@endphp
@section('title', 'HAUS OF TIME — ' . $t('La Galería', 'The Gallery'))
@section('body_class', 'hx hx--galeria')
@push('head')
  <meta name="robots" content="noindex, nofollow">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&family=Cormorant+Garamond:ital,wght@1,500&display=swap" rel="stylesheet">
  <link rel="preload" as="image" href="/images/shop/alt/salon.webp" type="image/webp">
  <link rel="stylesheet" href="/css/home-alt.css?v={{ filemtime(public_path('css/home-alt.css')) }}">
@endpush

@section('content')

{{-- 01 · Hero: la casa sin tiempo (pág. 36–37) --}}
<section class="hx-hero2">
  <div class="hx-hero2__bg" style="background-image:url('/images/shop/alt/salon.webp')" aria-hidden="true"></div>
  <div class="hx-hero2__stripes" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
  <div class="hs-wrap hx-hero2__inner">
    <div class="hx-hero2__mark hx-rise">
      @include('shop.partials.isotipo', ['class' => 'hx-iso hx-iso--xl'])
      <h1 class="hx-stack" aria-label="HAUS OF TIME"><span>HAUS</span><span>OF</span><span>TIME</span></h1>
    </div>
    <div class="hx-hero2__copy hx-rise" style="--d:.25s">
      <p class="hx-eyebrow">{{ $t('Marketplace de relojes de lujo', 'Luxury watch marketplace') }}</p>
      <p class="hx-lead">{{ $t('Piezas verificadas una a una, de coleccionistas para coleccionistas. Un lugar donde el tiempo se detiene para revelar su belleza.', 'Pieces verified one by one, from collectors to collectors. A place where time stands still to reveal its beauty.') }}</p>
      <div class="hx-actions">
        <a href="{{ route('shop.index') }}" class="hs-btn">{{ $t('Explorar la colección', 'Explore the collection') }}</a>
        <a href="{{ route('sell') }}" class="hs-btn hs-btn--ghost">{{ __('shop.nav.sell') }}</a>
      </div>
    </div>
  </div>
  <a href="#maisons" class="hx-scroll" aria-label="{{ $t('Bajar', 'Scroll') }}"><span></span></a>
</section>

{{-- 02 · Las maisons (pág. 38) --}}
<section class="hx-sec hx-maisons" id="maisons">
  <div class="hs-wrap">
    <header class="hx-head hx-head--center">
      @include('shop.partials.isotipo', ['class' => 'hx-iso hx-iso--md'])
      <p class="hx-eyebrow">{{ $t('Las maisons', 'The maisons') }}</p>
      <h2 class="hx-title">{{ $t('Las grandes casas relojeras, en un solo lugar', 'The great watch houses, in one place') }}</h2>
    </header>
    <div class="hx-brands">
      @foreach($brands as $b)
        <a href="{{ route('shop.index', ['marca' => $b->slug]) }}" class="hx-brand">
          @if($b->cover)<img class="hx-brand__img" src="{{ $b->cover }}" alt="" loading="lazy" decoding="async">@endif
          <span class="hx-brand__name">@if($b->logo_url)<img src="{{ $b->logo_url }}" alt="{{ $b->name }}">@else{{ $b->name }}@endif</span>
          <span class="hx-brand__meta">{{ trans_choice($en ? '{1} 1 piece|[2,*] :count pieces' : '{1} 1 pieza|[2,*] :count piezas', $b->pieces) }}@if($b->from) · {{ $t('desde', 'from') }} {{ $priceFmt($b->from) }}@endif</span>
        </a>
      @endforeach
    </div>
  </div>
</section>

{{-- 03 · Piezas seleccionadas --}}
<section class="hx-sec">
  <div class="hs-wrap">
    <header class="hx-head hx-head--row">
      <div>
        <p class="hx-eyebrow">{{ $t('Selección de la casa', 'House selection') }}</p>
        <h2 class="hx-title">{{ $t('Piezas que merecen detenerse', 'Pieces worth pausing for') }}</h2>
      </div>
      <a href="{{ route('shop.index') }}" class="hx-more">{{ str_replace(':n', $stats['pieces'], $t('Ver las :n piezas', 'See all :n pieces')) }} →</a>
    </header>
    <div class="hs-grid hx-grid4">@include('shop.partials.cards', ['watches' => $pick, 'brandMap' => $brandMap, 'favoriteIds' => $favoriteIds])</div>
  </div>
</section>

{{-- 04 · Sello de aprobación (pág. 39 y 51) --}}
<section class="hx-sec hx-seal">
  <div class="hs-wrap hx-seal__inner">
    <div class="hx-seal__copy">
      <img src="/images/shop/seal.webp" alt="{{ $t('Sello HAUS OF TIME', 'HAUS OF TIME seal') }}" class="hx-seal__badge" width="150" height="150" loading="lazy">
      <p class="hx-eyebrow">{{ $t('Seal of approval', 'Seal of approval') }}</p>
      <h2 class="hx-title">{{ $t('Cada sello, una verificación', 'Every seal, a verification') }}</h2>
      <p class="hx-text">{{ $t('El sello dorado solo aparece en las piezas que han superado nuestro proceso de validación: identidad del vendedor, prueba de posesión y revisión de la documentación.', 'The gold seal only appears on pieces that have passed our validation process: seller identity, proof of possession and review of the papers.') }}</p>
      <ol class="hx-steps">
        <li><b>01</b><span><strong>{{ $t('Vendedor verificado', 'Verified seller') }}</strong>{{ $t('Identidad y datos de cobro comprobados.', 'Identity and payout details checked.') }}</span></li>
        <li><b>02</b><span><strong>{{ $t('Prueba de posesión', 'Proof of possession') }}</strong>{{ $t('Foto del reloj con código de verificación único.', 'Photo of the watch with a unique verification code.') }}</span></li>
        <li><b>03</b><span><strong>{{ $t('Pago protegido', 'Protected payment') }}</strong>{{ $t('El vendedor cobra cuando confirmas la recepción.', 'The seller is paid once you confirm receipt.') }}</span></li>
      </ol>
      <a href="{{ route('shop.index', ['certificado' => 1]) }}" class="hs-btn">{{ __('shop.nav.certified_only') }}</a>
    </div>
    <div class="hx-seal__media">
      <img src="/images/shop/alt/relojero.webp" alt="" loading="lazy" class="hx-seal__a">
      <img src="/images/shop/alt/evaluacion.webp" alt="" loading="lazy" class="hx-seal__b">
    </div>
  </div>
</section>

{{-- 05 · Manifiesto (pág. 33) --}}
<section class="hx-sec hx-manifest">
  <div class="hs-wrap hx-manifest__inner">
    <figure class="hx-manifest__img"><img src="/images/shop/alt/manifiesto.webp" alt="" loading="lazy"></figure>
    <div class="hx-manifest__copy">
      <p class="hx-eyebrow">{{ $t('Manifiesto', 'Manifesto') }}</p>
      <h2 class="hx-title hx-title--caps">{{ $t('Un lugar de pertenencia', 'A place of belonging') }}</h2>
      @if($en)
        <p class="hx-text">If HAUS OF TIME existed as a physical space, we imagine a timeless house suspended before a vast ocean — perhaps in the Tuscan hills, perhaps on top of a Greek island. Its walls breathe light and calm, white as Mediterranean foam.</p>
        <p class="hx-text">No clocks run here, only the exact pulse of what is essential, inviting us to reflect on the value of time. HAUS OF TIME is not inhabited, it is contemplated: the place where time stands still to reveal its beauty, and gives us calm, security and permanence.</p>
      @else
        <p class="hx-text">Si HAUS OF TIME existiera en un espacio físico, imaginamos una casa sin tiempo, suspendida frente a un vasto océano, con aires de terruño en la Toscana italiana o quizás en la cima de alguna isla griega. Sus muros respiran luz y calma, blancos como la espuma del Mediterráneo.</p>
        <p class="hx-text">No hay relojes que corran en este lugar, solo el pulso exacto de lo esencial, que nos invita a reflexionar sobre el valor del tiempo. HAUS OF TIME no se habita, se contempla: es el lugar donde el tiempo se detiene para revelar su belleza y nos da calma, seguridad y permanencia.</p>
      @endif
      <div class="hx-stats">
        <div><strong>{{ $stats['pieces'] }}</strong><span>{{ $t('piezas', 'pieces') }}</span></div>
        <div><strong>{{ $stats['certified'] }}</strong><span>{{ $t('certificadas', 'certified') }}</span></div>
        <div><strong>{{ $stats['brands'] }}</strong><span>{{ $t('maisons', 'maisons') }}</span></div>
        <div><strong>{{ $stats['countries'] }}</strong><span>{{ $t('países', 'countries') }}</span></div>
      </div>
    </div>
  </div>
</section>

{{-- 06 · Cierre --}}
<section class="hx-sec hx-cta">
  <div class="hs-wrap">
    <div class="hx-cta__box" style="background-image:url('/images/shop/alt/arcos.webp')">
      <div>
        <p class="hx-eyebrow">{{ $t('¿Tienes una pieza especial?', 'Own a special piece?') }}</p>
        <h2 class="hx-title">{{ $t('Dale el escaparate que merece', 'Give it the showcase it deserves') }}</h2>
      </div>
      <div class="hx-actions">
        <a href="{{ route('evaluation.index') }}" class="hs-btn hs-btn--ghost">{{ __('shop.nav.evaluation') }}</a>
        <a href="{{ route('sell') }}" class="hs-btn">{{ __('shop.nav.sell') }}</a>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  // rivela le sezioni allo scroll
  (function () {
    var els = document.querySelectorAll('.hx-sec');
    if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); return; }
    var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } }); }, { rootMargin: '0px 0px -80px 0px' });
    els.forEach(function (e) { io.observe(e); });
  })();
</script>
@endpush
