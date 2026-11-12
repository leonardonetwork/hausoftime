@extends('layouts.shop')
@php
  $en = app()->getLocale() === 'en';
  $t = fn ($es, $eng) => $en ? $eng : $es;
  $std = rtrim(rtrim(number_format((float) \App\Models\Setting::get('commission_standard', 7), 1, ',', '.'), '0'), ',');
  $releaseDays = (int) \App\Models\Setting::get('release_after_days', 30);
  $shipDays = (int) \App\Models\Setting::get('ship_within_days', 2);
  $user = auth()->user();
  $startUrl = $user && ($user->isSeller() || $user->isAdmin()) ? route('seller.watches.create') : route('early-access.index');
@endphp
@section('title', 'HAUS OF TIME — ' . $t('Vende tu reloj', 'Sell your watch'))
@section('description', $t('Vende tu reloj de lujo en HAUS OF TIME: pieza verificada, pago protegido y envío asegurado.', 'Sell your luxury watch on HAUS OF TIME: verified piece, protected payment and insured shipping.'))
@section('canonical', route('sell'))
@section('body_class', 'hx hx--galeria hx--sell')
@push('head')
  <link rel="stylesheet" href="/css/home.css?v={{ filemtime(public_path('css/home.css')) }}">
@endpush

@section('content')

{{-- 01 · Hero --}}
<section class="hx-hero2 hx-hero2--sell">
  <div class="hx-hero2__bg" style="background-image:url('/images/shop/alt/arcos.webp')" aria-hidden="true"></div>
  <div class="hx-hero2__stripes" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
  <div class="hs-wrap hx-hero2__inner">
    <div class="hx-rise">
      @include('shop.partials.isotipo', ['class' => 'hx-iso hx-iso--xl'])
      <p class="hx-eyebrow">{{ $t('Para vendedores', 'For sellers') }}</p>
      <h1 class="hx-sellh">{{ $t('Tu reloj merece', 'Your watch deserves') }}<br><em>{{ $t('otra historia.', 'another story.') }}</em></h1>
    </div>
    <div class="hx-hero2__copy hx-rise" style="--d:.25s">
      <p class="hx-lead">{{ $t('Publica tu pieza en el marketplace de relojes de lujo basado en la confianza. Verificamos cada reloj, protegemos el pago y te lo liquidamos en cuanto el comprador lo recibe.', 'List your piece on the luxury watch marketplace built on trust. We verify every watch, protect the payment and pay you as soon as the buyer receives it.') }}</p>
      <div class="hx-actions">
        <a href="{{ $startUrl }}" class="hs-btn">{{ $t('Empezar a vender', 'Start selling') }}</a>
        <a href="{{ route('evaluation.index') }}" class="hs-btn hs-btn--ghost">{{ __('shop.nav.evaluation') }}</a>
      </div>
      @guest<p class="hx-login">{{ $t('¿Ya vendes con nosotros?', 'Already selling with us?') }} <a href="{{ route('login') }}">{{ $t('Inicia sesión', 'Sign in') }}</a></p>@endguest
    </div>
  </div>
</section>

{{-- 02 · Cómo funciona --}}
<section class="hx-sec" id="como-funciona">
  <div class="hs-wrap">
    <header class="hx-head hx-head--center">
      @include('shop.partials.isotipo', ['class' => 'hx-iso hx-iso--md'])
      <p class="hx-eyebrow">{{ $t('Cómo funciona', 'How it works') }}</p>
      <h2 class="hx-title">{{ $t('De tu caja fuerte a su muñeca, en cuatro pasos', 'From your safe to their wrist, in four steps') }}</h2>
    </header>
    <ol class="hx-flow">
      <li><b>01</b><strong>{{ $t('Publica tu reloj', 'List your watch') }}</strong><span>{{ $t('Fotos, referencia, año, estado y documentación. Te ayudamos con una tasación orientativa.', 'Photos, reference, year, condition and papers. We help you with an indicative valuation.') }}</span></li>
      <li><b>02</b><strong>{{ $t('Verifica la posesión', 'Verify possession') }}</strong><span>{{ $t('Pon el reloj en la hora que te indicamos y súbenos una foto. Así ganas el sello dorado.', 'Set the watch to the time we give you and upload a photo. That earns you the gold seal.') }}</span></li>
      <li><b>03</b><strong>{{ $t('Vende con pago protegido', 'Sell with protected payment') }}</strong><span>{{ str_replace(':d', $shipDays, $t('El comprador paga por adelantado; tú envías en :d días con envío asegurado.', 'The buyer pays upfront; you ship within :d days with insured shipping.')) }}</span></li>
      <li><b>04</b><strong>{{ $t('Cobra', 'Get paid') }}</strong><span>{{ str_replace(':d', $releaseDays, $t('Recibes el importe en tu cuenta cuando el comprador confirma la recepción (o a los :d días del envío).', 'You receive the amount in your account once the buyer confirms receipt (or :d days after shipping).')) }}</span></li>
    </ol>
  </div>
</section>

{{-- 03 · Ventajas + condiciones --}}
<section class="hx-sec hx-seal">
  <div class="hs-wrap hx-seal__inner">
    <div class="hx-seal__copy">
      <p class="hx-eyebrow">{{ $t('Por qué HAUS OF TIME', 'Why HAUS OF TIME') }}</p>
      <h2 class="hx-title">{{ $t('Un escaparate a la altura de tu pieza', 'A showcase worthy of your piece') }}</h2>
      <ol class="hx-steps">
        <li><b>01</b><span><strong>{{ $t('Compradores serios', 'Serious buyers') }}</strong>{{ $t('Un público que busca relojes verificados, no un tablón de anuncios.', 'An audience looking for verified watches, not a classifieds board.') }}</span></li>
        <li><b>02</b><span><strong>{{ $t('Sin riesgo de impago', 'No risk of non-payment') }}</strong>{{ $t('El dinero está cobrado antes de que envíes el reloj.', 'The money is collected before you ship the watch.') }}</span></li>
        <li><b>03</b><span><strong>{{ $t('Sello de aprobación', 'Seal of approval') }}</strong>{{ $t('Las piezas verificadas destacan con el sello dorado de la casa.', 'Verified pieces stand out with the house gold seal.') }}</span></li>
        <li><b>04</b><span><strong>{{ $t('Panel de vendedor', 'Seller dashboard') }}</strong>{{ $t('Tus relojes, ventas, envíos y cobros en un solo lugar.', 'Your watches, sales, shipments and payouts in one place.') }}</span></li>
      </ol>
    </div>
    <div class="hx-terms">
      <div class="hx-terms__head">
        <img src="/images/shop/seal.webp" alt="" width="84" height="84">
        <div><p class="hx-eyebrow" style="margin:0">{{ $t('Condiciones', 'Terms') }}</p><strong>{{ $t('Claras desde el primer día', 'Clear from day one') }}</strong></div>
      </div>
      @foreach([
        [$t('Publicar', 'Listing'), $t('Gratis', 'Free')],
        [$t('Comisión por venta', 'Commission per sale'), $std . ' %'],
        [$t('Pago', 'Payment'), $t('Protegido hasta la entrega', 'Protected until delivery')],
        [$t('Envío', 'Shipping'), $t('Asegurado', 'Insured')],
        [$t('Cobro', 'Payout'), $t('Transferencia a tu cuenta', 'Transfer to your account')],
      ] as [$l, $v])
        <div class="hx-terms__row"><span>{{ $l }}</span><strong>{{ $v }}</strong></div>
      @endforeach
      <a href="{{ $startUrl }}" class="hs-btn hs-btn--block">{{ $t('Empezar a vender', 'Start selling') }}</a>
    </div>
  </div>
</section>

{{-- 04 · Frase --}}
<section class="hx-sec hx-quote">
  <div class="hs-wrap">
    <figure class="hx-quote__box" style="background-image:url('/images/shop/alt/manifiesto.webp')">
      <blockquote>
        <p>{{ $t('Los relojes de lujo son activos.', 'Luxury watches are assets.') }}</p>
        <p><em>{{ $t('Los activos merecen una infraestructura a su altura.', 'Assets deserve infrastructure at their level.') }}</em></p>
      </blockquote>
    </figure>
  </div>
</section>

{{-- 05 · Cierre --}}
<section class="hx-sec hx-cta">
  <div class="hs-wrap">
    <div class="hx-cta__box" style="background-image:url('/images/shop/alt/relojero.webp')">
      <div>
        <p class="hx-eyebrow">{{ $t('¿No sabes cuánto vale?', 'Not sure what it is worth?') }}</p>
        <h2 class="hx-title">{{ $t('Tásalo gratis en un minuto', 'Get a free valuation in a minute') }}</h2>
      </div>
      <div class="hx-actions">
        <a href="{{ route('evaluation.index') }}" class="hs-btn hs-btn--ghost">{{ __('shop.nav.evaluation') }}</a>
        <a href="{{ $startUrl }}" class="hs-btn">{{ $t('Empezar a vender', 'Start selling') }}</a>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  (function () {
    var els = document.querySelectorAll('.hx-sec');
    if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); return; }
    var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } }); }, { rootMargin: '0px 0px -80px 0px' });
    els.forEach(function (e) { io.observe(e); });
  })();
</script>
@endpush
