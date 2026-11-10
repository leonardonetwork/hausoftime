@php $a = $address ?? null; @endphp
<div class="hs-form">
  <div class="hs-field full"><label>{{ __('shop.account.label') }}</label><input class="hs-input" name="label" value="{{ $a?->label }}" maxlength="60"></div>
  <div class="hs-field"><label>{{ __('shop.checkout.first_name') }}</label><input class="hs-input" name="first_name" value="{{ $a?->first_name ?? auth()->user()->first_name }}" required></div>
  <div class="hs-field"><label>{{ __('shop.checkout.last_name') }}</label><input class="hs-input" name="last_name" value="{{ $a?->last_name ?? auth()->user()->last_name }}" required></div>
  <div class="hs-field full"><label>{{ __('shop.checkout.line1') }}</label><input class="hs-input" name="line1" value="{{ $a?->line1 }}" required></div>
  <div class="hs-field full"><label>{{ __('shop.checkout.line2') }}</label><input class="hs-input" name="line2" value="{{ $a?->line2 }}"></div>
  <div class="hs-field"><label>{{ __('shop.checkout.postal_code') }}</label><input class="hs-input" name="postal_code" value="{{ $a?->postal_code }}" required></div>
  <div class="hs-field"><label>{{ __('shop.checkout.city') }}</label><input class="hs-input" name="city" value="{{ $a?->city }}" required></div>
  <div class="hs-field"><label>{{ __('shop.checkout.province') }}</label><input class="hs-input" name="province" value="{{ $a?->province }}"></div>
  <div class="hs-field"><label>{{ __('shop.checkout.country') }}</label>
    <select name="country_code" required>@foreach($countries as $c)<option value="{{ $c->code }}" @selected(($a?->country_code ?? 'ES') === $c->code)>{{ $c->name }}</option>@endforeach</select></div>
  <div class="hs-field full"><label>{{ __('shop.checkout.phone') }}</label>
    <div class="hs-phone"><input class="hs-input" name="phone_prefix" value="{{ $a?->phone_prefix ?? '+34' }}"><input class="hs-input" name="phone" value="{{ $a?->phone }}" required></div></div>
  <div class="hs-field"><label>{{ __('shop.checkout.company') }}</label><input class="hs-input" name="company" value="{{ $a?->company }}"></div>
  <div class="hs-field"><label>{{ __('shop.checkout.tax_id') }}</label><input class="hs-input" name="tax_id" value="{{ $a?->tax_id }}"></div>
  <label class="hs-check full"><input type="checkbox" name="is_default" value="1" @checked($a?->is_default)> {{ __('shop.account.make_default') }}</label>
</div>
