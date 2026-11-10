@extends('layouts.shop')
@section('title', __('shop.checkout.title') . ' — HAUS OF TIME')
@php
  $useNew = old('address_id') === null ? $addresses->isEmpty() : old('address_id') === '';
  $terms = route('page.show', 'terminos-y-condiciones');
  $privacy = route('page.show', 'privacidad');
  $f = fn ($name) => $errors->has($name) ? 'err' : '';
@endphp
@section('content')
<div class="hs-wrap">
  <h1 class="hs-page-title"><small>HAUS OF TIME</small>{{ __('shop.checkout.title') }}</h1>
  @if($removed)<div class="hs-alert hs-alert--err">{{ __('shop.bag.items_removed', ['items' => implode(', ', $removed)]) }}</div>@endif
  @if(!$stripeReady)<div class="hs-alert hs-alert--err">{{ __('shop.checkout.payments_unavailable') }}</div>@endif
  @if($errors->any())<div class="hs-alert hs-alert--err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

  <form method="POST" action="{{ route('checkout.store') }}" id="hsCheckout" class="hs-two" novalidate>
    @csrf
    <div class="hs-panel">
      <h2 class="hs-section-t"><span class="n">1</span>{{ __('shop.checkout.step_shipping') }}</h2>

      @if($addresses->count())
        <div class="hs-addr-list" style="margin-bottom:16px">
          @foreach($addresses as $a)
            <label class="hs-addr">
              <input type="radio" name="address_id" value="{{ $a->id }}" data-country="{{ $a->country_code }}" @checked(!$useNew && (old('address_id', $default?->id) == $a->id))>
              <span>@if($a->label)<strong>{{ $a->label }}</strong>{{ "\n" }}@endif{{ \App\Models\Address::format($a->toSnapshot()) }}</span>
            </label>
          @endforeach
          <label class="hs-addr">
            <input type="radio" name="address_id" value="" data-new @checked($useNew)>
            <span><strong>{{ __('shop.checkout.new_address') }}</strong></span>
          </label>
        </div>
      @else
        <input type="hidden" name="address_id" value="">
      @endif

      <div class="hs-form" id="hsNewAddress" @if(!$useNew) hidden @endif>
        <div class="hs-field {{ $f('first_name') }}"><label for="first_name">{{ __('shop.checkout.first_name') }}</label><input class="hs-input" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name ?: $user->name) }}" autocomplete="given-name"></div>
        <div class="hs-field {{ $f('last_name') }}"><label for="last_name">{{ __('shop.checkout.last_name') }}</label><input class="hs-input" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" autocomplete="family-name"></div>
        <div class="hs-field full {{ $f('line1') }}"><label for="line1">{{ __('shop.checkout.line1') }}</label><input class="hs-input" id="line1" name="line1" value="{{ old('line1') }}" autocomplete="address-line1"></div>
        <div class="hs-field full"><label for="line2">{{ __('shop.checkout.line2') }}</label><input class="hs-input" id="line2" name="line2" value="{{ old('line2') }}" autocomplete="address-line2"></div>
        <div class="hs-field {{ $f('postal_code') }}"><label for="postal_code">{{ __('shop.checkout.postal_code') }}</label><input class="hs-input" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" autocomplete="postal-code"></div>
        <div class="hs-field {{ $f('city') }}"><label for="city">{{ __('shop.checkout.city') }}</label><input class="hs-input" id="city" name="city" value="{{ old('city', $user->city) }}" autocomplete="address-level2"></div>
        <div class="hs-field"><label for="province">{{ __('shop.checkout.province') }}</label><input class="hs-input" id="province" name="province" value="{{ old('province') }}" autocomplete="address-level1"></div>
        <div class="hs-field {{ $f('country_code') }}"><label for="country_code">{{ __('shop.checkout.country') }}</label>
          <select id="country_code" name="country_code" autocomplete="country">
            @foreach($countries as $c)<option value="{{ $c->code }}" @selected($countryCode === $c->code)>{{ $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="hs-field full {{ $f('phone') }}"><label for="phone">{{ __('shop.checkout.phone') }}</label>
          <div class="hs-phone">
            <input class="hs-input" name="phone_prefix" value="{{ old('phone_prefix', $user->phone_prefix ?: '+34') }}" aria-label="Prefijo" autocomplete="tel-country-code">
            <input class="hs-input" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel-national" inputmode="tel">
          </div>
          <div class="hint">{{ __('shop.checkout.phone_hint') }}</div>
        </div>
        <div class="hs-field"><label for="company">{{ __('shop.checkout.company') }}</label><input class="hs-input" id="company" name="company" value="{{ old('company') }}" autocomplete="organization"></div>
        <div class="hs-field"><label for="tax_id">{{ __('shop.checkout.tax_id') }}</label><input class="hs-input" id="tax_id" name="tax_id" value="{{ old('tax_id') }}"></div>
        <label class="hs-check full"><input type="checkbox" name="save_address" value="1" @checked(old('save_address', true))> {{ __('shop.checkout.save_address') }}</label>
      </div>

      <div class="hs-field" style="margin-top:22px"><label for="notes">{{ __('shop.checkout.notes') }}</label><textarea id="notes" name="notes" maxlength="1000">{{ old('notes') }}</textarea></div>

      <h2 class="hs-section-t" style="margin-top:34px"><span class="n">2</span>{{ __('shop.checkout.step_payment') }}</h2>
      <div class="hs-escrow"><img src="/images/shop/seal.png" alt="">{{ __('shop.checkout.escrow', ['days' => \App\Models\Setting::get('release_after_days', 30)]) }}</div>
      <label class="hs-check {{ $errors->has('accept_terms') ? 'err' : '' }}" style="margin:20px 0">
        <input type="checkbox" name="accept_terms" value="1" required @checked(old('accept_terms'))>
        <span>{!! __('shop.checkout.accept', ['terms' => $terms, 'privacy' => $privacy]) !!}</span>
      </label>
      <button type="submit" class="hs-btn hs-btn--block" id="hsPay" @disabled(!$stripeReady)>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
        {{ __('shop.checkout.pay') }} · <span data-total>{{ \App\Models\Watch::money($quote['total'], true) }}</span>
      </button>
      <p class="hs-note" style="text-align:center;margin-top:12px">{{ __('shop.checkout.secure') }}</p>
    </div>

    <aside class="hs-panel hs-sticky">
      <h2 class="hs-section-t">{{ __('shop.checkout.summary') }}</h2>
      <div id="hsSummary">@include('shop.partials.summary', ['quote' => $quote])</div>

      <div style="margin-top:20px" id="hsCoupon">
        <div class="hs-coupon-ok" data-coupon-ok @if(!$quote['coupon']) hidden @endif>
          <span data-coupon-label>{{ $quote['coupon'] ? __('shop.checkout.coupon_applied', ['code' => $quote['coupon']->code]) : '' }}</span>
          <button type="button" data-coupon-remove>{{ __('shop.checkout.remove_coupon') }}</button>
        </div>
        <div data-coupon-form @if($quote['coupon']) hidden @endif>
          <label class="hs-field" style="display:block"><span class="hs-sr">{{ __('shop.checkout.coupon') }}</span></label>
          <div class="hs-coupon">
            <input class="hs-input" type="text" data-coupon-input placeholder="{{ __('shop.checkout.coupon') }}" maxlength="60" autocomplete="off">
            <button type="button" class="hs-btn hs-btn--ghost hs-btn--sm" data-coupon-apply>{{ __('shop.checkout.apply') }}</button>
          </div>
          <div class="hs-field"><div class="err-msg" data-coupon-error></div></div>
        </div>
      </div>
      <p class="hs-note" style="margin-top:16px">{{ __('shop.checkout.vat_note') }}</p>
    </aside>
  </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var form = document.getElementById('hsCheckout'), url = @json(route('checkout.quote'));
  var newBox = document.getElementById('hsNewAddress'), country = document.getElementById('country_code');
  var summary = document.getElementById('hsSummary'), total = document.querySelector('[data-total]');
  function currentCountry() {
    var r = form.querySelector('input[name=address_id]:checked');
    if (r && r.value) return r.dataset.country;
    return country ? country.value : null;
  }
  function refresh(action, coupon) {
    summary.style.opacity = .5;
    return fetch(url, { method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': HOT.csrf },
      body: JSON.stringify({ country_code: currentCountry(), action: action || null, coupon: coupon || null })
    }).then(function (r) { return r.json(); }).then(function (d) {
      summary.innerHTML = d.summary; summary.style.opacity = 1; total.textContent = d.total;
      document.querySelector('[data-coupon-ok]').hidden = !d.coupon;
      document.querySelector('[data-coupon-form]').hidden = !!d.coupon;
      if (d.coupon) document.querySelector('[data-coupon-label]').textContent = @json(__('shop.checkout.coupon_applied', ['code' => '__C__'])).replace('__C__', d.coupon);
      document.querySelector('[data-coupon-error]').textContent = d.coupon_error || '';
      document.getElementById('hsPay').disabled = !!d.shipping_error || {{ $stripeReady ? 'false' : 'true' }};
    }).catch(function () { summary.style.opacity = 1; });
  }
  form.querySelectorAll('input[name=address_id]').forEach(function (r) {
    r.addEventListener('change', function () { newBox.hidden = !!r.value; refresh(); });
  });
  country && country.addEventListener('change', function () { refresh(); });
  document.querySelector('[data-coupon-apply]').addEventListener('click', function () {
    var v = document.querySelector('[data-coupon-input]').value.trim(); if (v) refresh('apply', v);
  });
  document.querySelector('[data-coupon-input]').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); document.querySelector('[data-coupon-apply]').click(); }
  });
  document.querySelector('[data-coupon-remove]').addEventListener('click', function () { refresh('remove'); });
  @if($quote['shipping_error'] && $countryCode) refresh(); @endif
})();
</script>
@endpush
