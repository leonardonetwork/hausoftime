@extends('layouts.shop')

@php
  $en = app()->getLocale() === 'en';
  $urls = $watch->image_urls;
  $priceFmt = fn ($v) => \App\Models\Watch::display($v);
  $isFav = in_array($watch->id, $favoriteIds, true);
  $user = auth()->user();
  $own = $user && (int) $user->id === (int) $watch->seller_id;
  $sold = $watch->status === 'sold';
  $reserved = !$sold && $watch->isReserved();
  $shipDays = (int) \App\Models\Setting::get('ship_within_days', 2);
  $desc = $watch->localized_description;
  $metaDesc = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $desc ?: $watch->title . ' ' . $watch->reference)), 155);
@endphp

@section('title', $watch->title . ($watch->reference ? ' · Ref. ' . $watch->reference : '') . ' — HAUS OF TIME')
@section('description', $metaDesc)
@section('canonical', route('shop.show', $watch->slug))
@if($urls)@section('og_image', $urls[0])@endif

@push('head')
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org', '@type' => 'Product',
  'name' => $watch->title, 'sku' => (string) $watch->id, 'mpn' => $watch->reference,
  'brand' => ['@type' => 'Brand', 'name' => $watch->brand],
  'image' => $urls, 'description' => $metaDesc,
  'itemCondition' => $watch->condition === 'mint' ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
  'offers' => ['@type' => 'Offer', 'priceCurrency' => 'EUR', 'price' => number_format($watch->effective_price, 2, '.', ''),
               'availability' => $sold ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock', 'url' => route('shop.show', $watch->slug)],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<div class="hs-wrap">
  @if($preview)<div class="hs-alert hs-alert--err">{{ __('shop.product.preview') }} ({{ $watch->status }})</div>@endif

  <div class="hs-product">
    {{-- Card grande (pag. 47) --}}
    <div class="hs-pcard">
      @if($watch->is_certified)
        <img class="hs-seal" src="/images/shop/seal.png" alt="{{ __('shop.card.certified') }}" title="{{ __('shop.card.certified') }}" width="128" height="128">
      @endif
      <div class="hs-gallery" data-gallery aria-label="{{ __('shop.product.gallery') }}">
        <span class="hs-card__brand">@if($brand?->logo_url)<img src="{{ $brand->logo_url }}" alt="{{ $watch->brand }}">@else{{ $brand?->name ?? $watch->brand }}@endif</span>
        @foreach($urls as $i => $u)
          <img src="{{ $u }}" data-full="{{ $u }}" alt="{{ $watch->title }} — {{ $i + 1 }}" class="{{ $i === 0 ? 'on' : '' }}" @if($i > 0) loading="lazy" @endif>
        @endforeach
        @if(count($urls) > 1)
          <button type="button" class="hs-gallery__nav prev" data-gal-prev aria-label="‹"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
          <button type="button" class="hs-gallery__nav next" data-gal-next aria-label="›"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg></button>
        @endif
      </div>
      @if(count($urls) > 1)
        <div class="hs-thumbs">
          @foreach($urls as $i => $u)
            <button type="button" data-thumb class="{{ $i === 0 ? 'on' : '' }}" aria-label="{{ $i + 1 }}"><img src="{{ $u }}" alt="" loading="lazy"></button>
          @endforeach
        </div>
      @endif
      <div class="hs-card__body">
        <div class="hs-card__info">
          <div class="hs-card__title">{{ $watch->model }}@if($watch->reference)<span>Ref. {{ $watch->reference }}</span>@endif</div>
          <div class="hs-card__lines">
            @foreach(array_slice(array_values(array_diff($watch->card_lines, ['Ref. ' . $watch->reference])), 0, 3) as $line)<div>{{ $line }}</div>@endforeach
          </div>
          <div class="hs-card__price">@if($watch->has_discount)<s>{{ $priceFmt($watch->price) }}</s>@endif{{ $priceFmt($watch->effective_price) }}</div>
        </div>
        <div class="hs-card__side">
          <button type="button" class="hs-heart {{ $isFav ? 'on' : '' }}" data-fav="{{ route('favorites.toggle', $watch) }}" aria-pressed="{{ $isFav ? 'true' : 'false' }}" aria-label="{{ $isFav ? __('shop.card.unfavorite') : __('shop.card.favorite') }}">
            <svg viewBox="0 0 28 26"><path d="M14 24.2S2.6 17.4 1.6 9.7C.9 4.6 4.4 1.5 8 1.5c2.6 0 4.6 1.5 6 3.6 1.4-2.1 3.4-3.6 6-3.6 3.6 0 7.1 3.1 6.4 8.2-1 7.7-12.4 14.5-12.4 14.5z"/></svg>
          </button>
          @unless($preview)
          @php $isCmp = in_array($watch->id, \App\Http\Controllers\Shop\CompareController::ids(), true); @endphp
          <button type="button" class="hs-cmpbtn {{ $isCmp ? 'on' : '' }}" data-compare="{{ route('compare.toggle', $watch) }}" aria-pressed="{{ $isCmp ? 'true' : 'false' }}" aria-label="{{ $isCmp ? __('shop.compare.remove') : __('shop.compare.add') }}" title="{{ $isCmp ? __('shop.compare.remove') : __('shop.compare.add') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h15M15 4l4 4-4 4M20 16H5M9 12l-4 4 4 4"/></svg>
      </button>
          @endunless
          @if($watch->location_label)
            <button type="button" class="hs-pin" aria-label="{{ __('shop.card.location') }}: {{ $watch->location_label }}">
              <svg viewBox="0 0 22 28"><path fill="currentColor" d="M11 0C4.9 0 0 4.8 0 10.8 0 18.6 11 28 11 28s11-9.4 11-17.2C22 4.8 17.1 0 11 0zm0 15.2a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9z"/></svg>
              <span class="hs-pin__tip">{{ $watch->location_label }}</span>
            </button>
          @endif
        </div>
      </div>
    </div>

    {{-- About this piece --}}
    <div class="hs-pinfo">
      <p class="hs-ptitle__brand">{{ $brand?->name ?? $watch->brand }}</p>
      <h1 class="hs-ptitle">{{ $watch->model }}</h1>
      <p class="hs-ptitle__sub">{{ implode(' · ', array_filter([$watch->reference ? 'Ref. ' . $watch->reference : null, $watch->manufacture_year, $watch->case_size])) }}</p>
      @if($desc)<div class="hs-desc">{{ $desc }}</div>@endif

      <div class="hs-buy">
        <div class="hs-buy__price">
          <strong>{{ $priceFmt($watch->effective_price) }}</strong>
          @if($watch->has_discount)<s>{{ $priceFmt($watch->price) }}</s>@endif
        </div>
        @if($sold)
          <div class="hs-buy__sold">{{ __('shop.product.sold') }}</div>
        @elseif($own)
          <div class="hs-buy__sold">{{ __('shop.product.own') }}</div>
        @elseif($reserved)
          <div class="hs-buy__sold">{{ __('shop.product.reserved') }}</div>
        @elseif(!$preview)
          <div id="hsInBag" @if(!$inBag) hidden @endif>
            <a href="{{ route('checkout.index') }}" class="hs-btn hs-btn--block">{{ __('shop.product.in_bag') }}</a>
          </div>
          <form method="POST" action="{{ route('bag.add', $watch) }}" data-bag-add data-after-add="#hsInBag" @if($inBag) hidden @endif>
            @csrf
            <div class="hs-buy__actions">
              <button type="submit" name="buy_now" value="1" class="hs-btn">{{ __('shop.product.buy_now') }}</button>
              <button type="submit" class="hs-btn hs-btn--ghost">{{ __('shop.product.add_to_bag') }}</button>
            </div>
          </form>
          <p class="hs-buy__note">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="flex:none;margin-top:1px"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
            {{ __('shop.product.trust_payment_t') }}
          </p>
        @endif
      </div>

      <div class="hs-specs">
        <h3>{{ __('shop.product.details') }}</h3>
        <dl>
          @foreach([
            'brand' => $brand?->name ?? $watch->brand, 'model' => $watch->model, 'reference' => $watch->reference,
            'year' => $watch->manufacture_year, 'condition' => $watch->condition_label, 'box_papers' => $watch->box_papers_label,
            'case_size' => $watch->case_size, 'material' => $watch->material, 'category' => $watch->category,
            'location' => $watch->location_label,
          ] as $k => $v)
            @if(filled($v))<div><dt>{{ __('shop.product.' . $k) }}</dt><dd>{{ $v }}</dd></div>@endif
          @endforeach
        </dl>
      </div>

      @if($watch->is_certified)
        <div class="hs-cert">
          <img src="/images/shop/seal.png" alt="" width="54" height="54">
          <div><strong>{{ __('shop.product.certified_title') }}</strong><p>{{ __('shop.product.certified_text') }}</p></div>
        </div>
      @endif

      <div class="hs-trust">
        <div>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l8 3v6c0 4.6-3.4 8.3-8 9-4.6-.7-8-4.4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></svg>
          <strong>{{ __('shop.product.trust_payment') }}</strong><p>{{ __('shop.product.trust_payment_t') }}</p>
        </div>
        <div>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
          <strong>{{ __('shop.product.trust_shipping') }}</strong><p>{{ __('shop.product.trust_shipping_t', ['days' => $shipDays]) }}</p>
        </div>
        <div>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 5h16v11H8l-4 4z"/></svg>
          <strong>{{ __('shop.product.trust_support') }}</strong><p>{{ __('shop.product.trust_support_t') }}</p>
        </div>
      </div>

      <p class="hs-ask">{{ __('shop.product.ask') }} <a class="hs-link" href="{{ route('contact') }}?reloj={{ urlencode($watch->title . ($watch->reference ? ' Ref. ' . $watch->reference : '')) }}">{{ __('shop.product.ask_cta') }}</a>
        · <button type="button" data-share class="hs-link" style="background:none;border:0;border-bottom:1px solid rgba(61,90,136,.3);font:inherit;padding:0">{{ __('shop.product.share') }}</button></p>
    </div>
  </div>

  @if($related->count())
    <section class="hs-related">
      <h3>{{ __('shop.product.related') }}</h3>
      <div class="hs-grid">@include('shop.partials.cards', ['watches' => $related])</div>
    </section>
  @endif
</div>

<div class="hs-lightbox" id="hsLightbox" role="dialog" aria-modal="true">
  <button type="button" class="hs-close" data-lb-close aria-label="{{ __('shop.nav.close') }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
  @if(count($urls) > 1)
    <button type="button" class="hs-gallery__nav prev" data-gal-prev style="left:20px"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
    <button type="button" class="hs-gallery__nav next" data-gal-next style="right:20px"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg></button>
  @endif
  <img src="" alt="{{ $watch->title }}">
</div>
@endsection
