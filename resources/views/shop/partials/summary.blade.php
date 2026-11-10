@php $m = fn ($v) => \App\Models\Watch::money($v, true); @endphp
<div class="hs-sum-items">
  @foreach($quote['lines'] as $l)
    @php $w = $l['watch']; @endphp
    <div class="it">
      @if($w->cover_url)<img src="{{ $w->cover_url }}" alt="">@else<span class="ph"></span>@endif
      <div><strong>{{ $w->title }}</strong><small>{{ $w->reference ? 'Ref. ' . $w->reference : '' }}@if($l['eta']) · {{ __('shop.checkout.eta', ['eta' => $l['eta']]) }}@endif</small></div>
      <em>{{ $m($l['price']) }}</em>
    </div>
  @endforeach
</div>
<div class="hs-sum">
  <div><span>{{ __('shop.checkout.subtotal') }}</span><span>{{ $m($quote['subtotal']) }}</span></div>
  @if($quote['discount'] > 0)
    <div class="disc"><span>{{ __('shop.checkout.discount') }} @if($quote['coupon'])({{ $quote['coupon']->code }})@endif</span><span>−{{ $m($quote['discount']) }}</span></div>
  @endif
  @if($quote['shipping_error'])
    <div><span>{{ __('shop.checkout.shipping') }}</span><span style="color:var(--hs-danger);font-size:12px;text-align:right">{{ $quote['shipping_error'] }}</span></div>
  @elseif($quote['country'])
    <div><span>{{ __('shop.checkout.shipping') }}</span><span>{{ $quote['shipping'] > 0 ? $m($quote['shipping']) : __('shop.checkout.free') }}</span></div>
    @foreach($quote['lines'] as $l)
      @if(count($quote['lines']) > 1 || $l['insurance'] > 0)
        <div class="sub"><span>{{ $l['watch']->model }} · {{ __('shop.checkout.shipping_line', ['rate' => '']) }}{{ $m($l['shipping']) }} + {{ __('shop.checkout.insurance') }} {{ $m($l['insurance']) }}</span></div>
      @endif
    @endforeach
  @endif
  <div class="tot"><span>{{ __('shop.checkout.total') }}</span><span>{{ $m($quote['total']) }}</span></div>
</div>
@if($quote['notice'])<p class="hs-note hs-note--box" style="margin-top:12px">{{ $quote['notice'] }}</p>@endif
