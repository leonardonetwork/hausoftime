@extends('layouts.shop')
@php
  $en = app()->getLocale() === 'en';
  $t = fn ($es, $eng) => $en ? $eng : $es;
  $priceFmt = fn ($v) => \App\Models\Watch::display($v);
  $heroBrand = $hero ? (($brandMap[$hero->brand] ?? null)?->name ?? $hero->brand) : null;
  $arrivals = $latest->take(12);
@endphp
@section('title', 'HAUS OF TIME — ' . $t('Editorial', 'Editorial'))
@section('body_class', 'hx hx--editorial')
@push('head')
  <meta name="robots" content="noindex, nofollow">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&family=Cormorant+Garamond:ital,wght@1,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/home-alt.css?v={{ filemtime(public_path('css/home-alt.css')) }}">
@endpush

@section('content')

{{-- 01 · Hero editoriale: la pieza de la semana --}}
<section class="hx-hero3">
  <div class="hs-wrap hx-hero3__inner">
    <div class="hx-hero3__copy hx-rise">
      <p class="hx-eyebrow">HAUS OF TIME · {{ $t('Edición', 'Issue') }} {{ now()->translatedFormat('F Y') }}</p>
      <h1 class="hx-display">{{ $t('El tiempo,', 'Time,') }}<br><em>{{ $t('certificado.', 'certified.') }}</em></h1>
      <p class="hx-lead">{{ $t('Relojes de lujo verificados uno a uno. Compra con pago protegido y recibe tu pieza con envío asegurado.', 'Luxury watches verified one by one. Buy with protected payment and receive your piece with insured shipping.') }}</p>
      <form class="hx-search" action="{{ route('shop.index') }}" method="GET" role="search">
        <input type="search" name="q" placeholder="{{ __('shop.nav.search') }}" aria-label="{{ __('shop.nav.search') }}">
        <button type="submit" class="hs-btn hs-btn--sm">{{ $t('Buscar', 'Search') }}</button>
      </form>
      <div class="hx-chips">
        @foreach($brands->take(6) as $b)<a href="{{ route('shop.index', ['marca' => $b->slug]) }}">{{ $b->name }}</a>@endforeach
      </div>
    </div>

    <div class="hx-hero3__stage hx-rise" style="--d:.2s">
      <div class="hx-hero3__photo" style="background-image:url('/images/shop/alt/ovalos.webp')" aria-hidden="true"></div>
      @if($hero)
        <a href="{{ route('shop.show', $hero->slug) }}" class="hx-feature">
          <span class="hx-feature__tag">{{ $t('Pieza de la semana', 'Piece of the week') }}</span>
          @if($hero->is_certified)<img class="hx-feature__seal" src="/images/shop/seal.webp" alt="{{ __('shop.card.certified') }}" width="64" height="64">@endif
          <img class="hx-feature__img" src="{{ $hero->cover_url }}" alt="{{ $hero->title }}">
          <span class="hx-feature__brand">{{ $heroBrand }}</span>
          <span class="hx-feature__model">{{ $hero->model }}</span>
          <span class="hx-feature__row">
            <span>{{ implode(' · ', array_filter([$hero->reference ? 'Ref. ' . $hero->reference : null, $hero->manufacture_year])) }}</span>
            <strong>{{ $priceFmt($hero->effective_price) }}</strong>
          </span>
        </a>
      @endif
    </div>
  </div>
</section>

{{-- 02 · Marquee maisons --}}
<div class="hx-marquee" aria-label="{{ $t('Marcas', 'Brands') }}">
  <div class="hx-marquee__track">
    @for($i = 0; $i < 2; $i++)
      @foreach($brands as $b)
        <a href="{{ route('shop.index', ['marca' => $b->slug]) }}" @if($i) aria-hidden="true" tabindex="-1" @endif>{{ $b->name }}</a>
        @include('shop.partials.isotipo', ['class' => 'hx-iso hx-iso--xs', 'stroke' => 5])
      @endforeach
    @endfor
  </div>
</div>

{{-- 03 · Colecciones --}}
<section class="hx-sec">
  <div class="hs-wrap">
    <header class="hx-head hx-head--row">
      <div>
        <p class="hx-eyebrow">{{ $t('Colecciones', 'Collections') }}</p>
        <h2 class="hx-title">{{ $t('Encuentra tu forma de medir el tiempo', 'Find your way of keeping time') }}</h2>
      </div>
      <a href="{{ route('shop.index') }}" class="hx-more">{{ __('shop.nav.all_watches') }} →</a>
    </header>
    <div class="hx-colls">
      @php $nc = $categories->count(); $tail = $nc > 5 ? ($nc - 5) % 4 : 0; @endphp
      @foreach($categories as $i => $c)
        <a href="{{ route('shop.index', ['categoria' => $c->slug]) }}" class="hx-coll {{ $i === 0 ? 'hx-coll--big' : '' }}"
           @if($tail && $i >= $nc - $tail) style="--span:{{ intdiv(4, $tail) }}" @endif>
          <span class="hx-coll__imgs">
            @foreach($c->covers as $k => $img)<img src="{{ $img }}" alt="" loading="lazy" style="--k:{{ $k }}">@endforeach
          </span>
          <span class="hx-coll__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
          <span class="hx-coll__name">{{ $c->name }}</span>
          <span class="hx-coll__meta">{{ trans_choice($en ? '{1} 1 piece|[2,*] :count pieces' : '{1} 1 pieza|[2,*] :count piezas', $c->pieces) }}</span>
        </a>
      @endforeach
    </div>
  </div>
</section>

{{-- 04 · Recién llegados (carrusel) --}}
<section class="hx-sec">
  <div class="hs-wrap">
    <header class="hx-head hx-head--row">
      <div>
        <p class="hx-eyebrow">{{ $t('Recién llegados', 'New arrivals') }}</p>
        <h2 class="hx-title">{{ $t('Lo último en la casa', 'Latest in the house') }}</h2>
      </div>
      <div class="hx-arrows">
        <button type="button" data-rail="-1" aria-label="{{ $t('Anterior', 'Previous') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 5l-7 7 7 7"/></svg></button>
        <button type="button" data-rail="1" aria-label="{{ $t('Siguiente', 'Next') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5l7 7-7 7"/></svg></button>
      </div>
    </header>
  </div>
  <div class="hx-rail" id="hxRail">
    @include('shop.partials.cards', ['watches' => $arrivals, 'brandMap' => $brandMap, 'favoriteIds' => $favoriteIds])
  </div>
</section>

{{-- 05 · Numeri --}}
<section class="hx-sec">
  <div class="hs-wrap">
    <div class="hx-band">
      <div><strong>{{ $stats['pieces'] }}</strong><span>{{ $t('piezas en venta', 'pieces for sale') }}</span></div>
      <div><strong>{{ $stats['certified'] }}</strong><span>{{ $t('con sello HAUS OF TIME', 'with HAUS OF TIME seal') }}</span></div>
      <div><strong>{{ $stats['brands'] }}</strong><span>{{ $t('maisons relojeras', 'watch maisons') }}</span></div>
      <div><strong>{{ $stats['countries'] }}</strong><span>{{ $t('países de origen', 'countries of origin') }}</span></div>
    </div>
  </div>
</section>

{{-- 06 · Proceso de evaluación (pág. 51) --}}
<section class="hx-sec hx-process">
  <div class="hs-wrap hx-process__inner">
    <div class="hx-process__media">
      <img src="/images/shop/alt/relojero.webp" alt="" loading="lazy" class="hx-process__a">
      <img src="/images/shop/alt/evaluacion.webp" alt="" loading="lazy" class="hx-process__b">
      <img src="/images/shop/seal.webp" alt="" class="hx-process__seal" width="110" height="110" loading="lazy">
    </div>
    <div class="hx-process__copy">
      <p class="hx-eyebrow">{{ $t('Nuestro proceso', 'Our process') }}</p>
      <h2 class="hx-title hx-title--caps">{{ $t('Evaluación de calidad', 'Quality evaluation') }}</h2>
      <p class="hx-text">{{ $t('Antes de mostrar el sello dorado, cada pieza pasa por cuatro controles. Si algo no encaja, no se publica.', 'Before showing the gold seal, each piece goes through four checks. If something does not fit, it is not published.') }}</p>
      <div class="hx-checks">
        <div><b>I</b><strong>{{ $t('Vendedor', 'Seller') }}</strong><span>{{ $t('Identidad y cuenta de cobro verificadas.', 'Identity and payout account verified.') }}</span></div>
        <div><b>II</b><strong>{{ $t('Posesión', 'Possession') }}</strong><span>{{ $t('Foto con código único escrito a mano.', 'Photo with a unique handwritten code.') }}</span></div>
        <div><b>III</b><strong>{{ $t('Documentación', 'Papers') }}</strong><span>{{ $t('Estuche, garantía y facturas revisados.', 'Box, warranty and invoices reviewed.') }}</span></div>
        <div><b>IV</b><strong>{{ $t('Entrega', 'Delivery') }}</strong><span>{{ $t('Pago retenido hasta que confirmas la recepción.', 'Payment held until you confirm receipt.') }}</span></div>
      </div>
    </div>
  </div>
</section>

{{-- 07 · Photo style (pág. 26–28) --}}
<section class="hx-sec">
  <div class="hs-wrap">
    <div class="hx-mosaic">
      <figure class="hx-mosaic__a"><img src="/images/shop/alt/pieza-mano.webp" alt="" loading="lazy"></figure>
      <figure class="hx-mosaic__b"><img src="/images/shop/alt/detalle.webp" alt="" loading="lazy"></figure>
      <figure class="hx-mosaic__c"><img src="/images/shop/alt/complicacion.webp" alt="" loading="lazy"></figure>
      <blockquote class="hx-mosaic__q">
        @include('shop.partials.isotipo', ['class' => 'hx-iso hx-iso--md'])
        <p>{{ $t('“HAUS OF TIME no se habita, se contempla.”', '“HAUS OF TIME is not inhabited, it is contemplated.”') }}</p>
        <cite>{{ $t('Manifiesto', 'Manifesto') }}</cite>
      </blockquote>
    </div>
  </div>
</section>

{{-- 08 · Vende --}}
<section class="hx-sec hx-cta3">
  <div class="hs-wrap">
    <div class="hx-cta3__box">
      @include('shop.partials.isotipo', ['class' => 'hx-iso hx-iso--bg', 'stroke' => 1.2])
      <p class="hx-eyebrow">{{ $t('Para vendedores', 'For sellers') }}</p>
      <h2 class="hx-display hx-display--sm">{{ $t('Tu reloj merece', 'Your watch deserves') }}<br><em>{{ $t('otra historia.', 'another story.') }}</em></h2>
      <div class="hx-actions hx-actions--center">
        <a href="{{ route('sell') }}" class="hs-btn hs-btn--light">{{ __('shop.nav.sell') }}</a>
        <a href="{{ route('evaluation.index') }}" class="hs-btn hs-btn--line">{{ __('shop.nav.evaluation') }}</a>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  (function () {
    var els = document.querySelectorAll('.hx-sec');
    if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); }
    else {
      var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } }); }, { rootMargin: '0px 0px -80px 0px' });
      els.forEach(function (e) { io.observe(e); });
    }
    var rail = document.getElementById('hxRail');
    document.querySelectorAll('[data-rail]').forEach(function (b) {
      b.addEventListener('click', function () { rail.scrollBy({ left: (+b.dataset.rail) * rail.clientWidth * .8, behavior: 'smooth' }); });
    });
  })();
</script>
@endpush
