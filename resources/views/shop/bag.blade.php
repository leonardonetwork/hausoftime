@extends('layouts.shop')
@section('title', __('shop.bag.title') . ' — HAUS OF TIME')
@php $priceFmt = fn ($v) => \App\Models\Watch::money($v); @endphp
@section('content')
<div class="hs-wrap">
  <h1 class="hs-page-title"><small>HAUS OF TIME</small>{{ __('shop.bag.title') }}</h1>
  @if($removed)<div class="hs-alert hs-alert--err">{{ __('shop.bag.items_removed', ['items' => implode(', ', $removed)]) }}</div>@endif

  @if($watches->isEmpty())
    <div class="hs-empty"><p>{{ __('shop.bag.empty') }}</p><a href="{{ route('shop.index') }}" class="hs-btn">{{ __('shop.bag.continue') }}</a></div>
  @else
    <div class="hs-two">
      <div class="hs-panel">
        @foreach($watches as $w)
          <div class="hs-line-item">
            <a href="{{ route('shop.show', $w->slug) }}">@if($w->cover_url)<img src="{{ $w->cover_url }}" alt="{{ $w->title }}">@else<span class="ph"></span>@endif</a>
            <div>
              <h4><a href="{{ route('shop.show', $w->slug) }}">{{ $w->title }}</a></h4>
              <p>{{ implode(' · ', array_filter([$w->reference ? 'Ref. ' . $w->reference : null, $w->manufacture_year, $w->condition_label])) }}</p>
              @if($w->location_label)<p>{{ __('shop.product.location') }}: {{ $w->location_label }}</p>@endif
              @if($w->reserved_by_other)<p class="warn">{{ __('shop.bag.reserved') }}</p>@endif
            </div>
            <div class="price">
              @if($w->has_discount)<s style="color:var(--hs-subtle);font-weight:400;font-size:12px;display:block">{{ $priceFmt($w->price) }}</s>@endif
              {{ $priceFmt($w->effective_price) }}
              <form method="POST" action="{{ route('bag.remove', $w) }}">@csrf @method('DELETE')<button class="rm" type="submit">{{ __('shop.bag.remove') }}</button></form>
            </div>
          </div>
        @endforeach
        <p class="hs-note" style="margin-top:18px">{{ __('shop.bag.each_ships') }}</p>
      </div>
      <aside class="hs-panel hs-sticky">
        <div class="hs-sum">
          <div><span>{{ __('shop.bag.subtotal') }}</span><strong>{{ \App\Models\Watch::money($subtotal, true) }}</strong></div>
        </div>
        <p class="hs-note" style="margin:10px 0 22px">{{ __('shop.bag.shipping_note') }}</p>
        <a href="{{ route('checkout.index') }}" class="hs-btn hs-btn--block">{{ __('shop.bag.checkout') }}</a>
        <a href="{{ route('shop.index') }}" class="hs-btn hs-btn--ghost hs-btn--block" style="margin-top:10px">{{ __('shop.bag.continue') }}</a>
        <div class="hs-escrow" style="margin-top:22px"><img src="/images/shop/seal.png" alt="">{{ __('shop.checkout.escrow', ['days' => \App\Models\Setting::get('release_after_days', 30)]) }}</div>
      </aside>
    </div>
  @endif
</div>
@endsection
