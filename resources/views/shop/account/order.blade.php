@extends('layouts.shop')
@section('title', $order->number . ' — HAUS OF TIME')
@php $m = fn ($v) => \App\Models\Watch::money($v, true); $en = app()->getLocale() === 'en'; @endphp
@section('content')
<div class="hs-wrap">
  <h1 class="hs-page-title"><small>{{ __('shop.account.order') }} · {{ $order->created_at->format('d/m/Y') }}</small>{{ $order->number }}
    <span class="hs-pill {{ $order->status_class }}" style="vertical-align:middle;margin-left:10px">{{ $order->status_label }}</span></h1>
  <div class="hs-acc">
    @include('shop.account._nav')
    <div style="display:grid;gap:20px">
      <div class="hs-panel">
        @foreach($order->items as $item)
          @php
            $flow = ['awaiting_shipment' => 1, 'shipped' => 2, 'received' => 3, 'released' => 3];
            $step = $flow[$item->status] ?? 0;
          @endphp
          <div class="hs-order-item">
            @if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->title }}">@else<span class="ph"></span>@endif
            <div>
              <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <h4>{{ $item->title }}</h4>
                <span class="hs-pill {{ $item->status_class }}">{{ $item->status_label }}</span>
              </div>
              <div class="meta">{{ $item->reference ? 'Ref. ' . $item->reference . ' · ' : '' }}{{ $m($item->price - $item->discount) }} · {{ __('shop.checkout.shipping') }} {{ $m($item->shipping + $item->insurance) }}</div>

              @if(in_array($item->status, ['awaiting_shipment', 'shipped', 'received', 'released'], true))
                <div class="hs-steps">
                  <span class="{{ $step >= 1 ? 'done' : '' }} {{ $step === 1 ? 'cur' : '' }}">{{ $en ? 'Paid' : 'Pagado' }}</span>
                  <span class="{{ $step >= 2 ? 'done' : '' }} {{ $step === 2 ? 'cur' : '' }}">{{ $en ? 'Shipped' : 'Enviado' }}</span>
                  <span class="{{ $step >= 3 ? 'done cur' : '' }}">{{ $en ? 'Received' : 'Recibido' }}</span>
                </div>
              @endif

              @if($item->status === 'awaiting_shipment' && $item->ship_by)
                <p class="hs-note">{{ __('shop.account.ship_by', ['date' => $item->ship_by->format('d/m/Y')]) }}</p>
              @endif

              @if($item->tracking_number)
                <p style="font-size:13px;margin:8px 0">{{ __('shop.account.tracking') }}: <strong>{{ strtoupper($item->carrier) }} {{ $item->tracking_number }}</strong>
                  @if($item->tracking_link) · <a class="hs-link" href="{{ $item->tracking_link }}" target="_blank" rel="noopener">{{ __('shop.account.track') }}</a>@endif</p>
              @endif

              @if($item->status === 'disputed')
                <p class="hs-note hs-note--box">{{ __('shop.account.dispute_open') }}</p>
              @endif

              @if($item->canBeConfirmedByBuyer())
                @if($item->release_due_at)<p class="hs-note" style="margin-bottom:12px">{{ __('shop.account.auto_release', ['date' => $item->release_due_at->format('d/m/Y')]) }}</p>@endif
                <form method="POST" action="{{ route('account.order.received', [$order, $item]) }}" data-confirm="{{ __('shop.account.confirm_q') }}" style="display:inline-block;margin-right:12px">
                  @csrf<button class="hs-btn hs-btn--sm">{{ __('shop.account.confirm') }}</button>
                </form>
              @endif

              @if($item->canOpenDispute())
                <details class="hs-details" style="margin-top:10px">
                  <summary>{{ __('shop.account.dispute') }}</summary>
                  <form method="POST" action="{{ route('account.order.dispute', [$order, $item]) }}">
                    @csrf
                    <div class="hs-field"><label for="reason{{ $item->id }}">{{ __('shop.account.dispute_reason') }}</label>
                      <textarea id="reason{{ $item->id }}" name="reason" minlength="10" required>{{ old('reason') }}</textarea></div>
                    <button class="hs-btn hs-btn--ghost hs-btn--sm" style="margin-top:10px">{{ __('shop.account.dispute_send') }}</button>
                  </form>
                </details>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      <div class="hs-two" style="grid-template-columns:1fr 1fr;padding:0">
        <div class="hs-panel">
          <h3 class="hs-section-t">{{ __('shop.account.ship_to') }}</h3>
          <p style="white-space:pre-line;font-size:14px;line-height:1.6">{{ $order->shipping_address_text }}</p>
        </div>
        <div class="hs-panel">
          <h3 class="hs-section-t">{{ __('shop.account.payment') }}</h3>
          <div class="hs-sum">
            <div><span>{{ __('shop.checkout.subtotal') }}</span><span>{{ $m($order->subtotal) }}</span></div>
            @if($order->discount_total > 0)<div class="disc"><span>{{ __('shop.checkout.discount') }} {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</span><span>−{{ $m($order->discount_total) }}</span></div>@endif
            <div><span>{{ __('shop.checkout.shipping') }}</span><span>{{ $order->shipping_total > 0 ? $m($order->shipping_total) : __('shop.checkout.free') }}</span></div>
            <div class="tot"><span>{{ __('shop.checkout.total') }}</span><span>{{ $m($order->total) }}</span></div>
          </div>
        </div>
      </div>
      <p class="hs-note">{{ __('shop.account.help') }} <a class="hs-link" href="{{ route('contact') }}">{{ __('shop.nav.contact') }}</a></p>
    </div>
  </div>
</div>
@endsection
