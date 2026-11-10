@extends('layouts.shop')
@section('title', __('shop.checkout.success_title') . ' — HAUS OF TIME')
@section('content')
<div class="hs-wrap">
  <div class="hs-panel hs-success">
    <img class="seal" src="/images/shop/seal.png" alt="">
    @if($order->paid_at)
      <h1>{{ __('shop.checkout.success_title') }}</h1>
      <p class="lead">{{ __('shop.checkout.success_text', ['number' => $order->number, 'email' => $order->email]) }}</p>
    @else
      <h1>{{ __('shop.checkout.pending_title') }}</h1>
      <p class="lead">{{ __('shop.checkout.pending_text') }}</p>
    @endif
    <div class="hs-sum-items" style="text-align:left">
      @foreach($order->items as $i)
        <div class="it">@if($i->image_url)<img src="{{ $i->image_url }}" alt="">@else<span class="ph"></span>@endif
          <div><strong>{{ $i->title }}</strong><small>{{ $i->reference ? 'Ref. ' . $i->reference : '' }}</small></div>
          <em>{{ \App\Models\Watch::money($i->price - $i->discount, true) }}</em></div>
      @endforeach
    </div>
    <div class="hs-sum" style="text-align:left"><div class="tot" style="border:0;margin:0;padding-top:0"><span>{{ __('shop.checkout.total') }}</span><span>{{ \App\Models\Watch::money($order->total, true) }}</span></div></div>
    <div class="hs-next">
      <h3 class="hs-section-t" style="margin:0">{{ __('shop.checkout.next_title') }}</h3>
      <div><b>1</b>{{ __('shop.checkout.next_1', ['days' => \App\Models\Setting::get('ship_within_days', 2)]) }}</div>
      <div><b>2</b>{{ __('shop.checkout.next_2') }}</div>
      <div><b>3</b>{{ __('shop.checkout.next_3') }}</div>
    </div>
    <a href="{{ route('account.order', $order) }}" class="hs-btn">{{ __('shop.checkout.view_order') }}</a>
    <a href="{{ route('shop.index') }}" class="hs-btn hs-btn--ghost" style="margin-left:8px">{{ __('shop.bag.continue') }}</a>
  </div>
</div>
@endsection
