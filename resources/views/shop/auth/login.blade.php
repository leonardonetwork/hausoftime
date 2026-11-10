@extends('layouts.shop')
@section('title', __('shop.auth.login_title') . ' — HAUS OF TIME')
@section('content')
<div class="hs-wrap hs-auth">
  <form method="POST" action="{{ route('account.login.submit') }}" class="hs-panel">
    @csrf
    <h1>{{ __('shop.auth.login_title') }}</h1>
    <p class="sub">{{ session('url.intended') && str_contains(session('url.intended'), 'checkout') ? __('shop.checkout.login_required') : __('shop.auth.login_sub') }}</p>
    @if($errors->any())<div class="hs-alert hs-alert--err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div style="display:grid;gap:14px">
      <div class="hs-field"><label for="email">{{ __('shop.auth.email') }}</label><input class="hs-input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"></div>
      <div class="hs-field"><label for="password">{{ __('shop.auth.password') }}</label><input class="hs-input" id="password" type="password" name="password" required autocomplete="current-password"></div>
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
        <label class="hs-check"><input type="checkbox" name="remember" value="1"> {{ __('shop.auth.remember') }}</label>
        <a class="hs-link" style="font-size:12.5px" href="{{ route('password.request') }}">{{ __('shop.auth.forgot') }}</a>
      </div>
      <button class="hs-btn hs-btn--block" style="margin-top:6px">{{ __('shop.auth.submit_login') }}</button>
    </div>
    <p class="alt">{{ __('shop.auth.no_account') }} <a class="hs-link" href="{{ route('account.register') }}">{{ __('shop.auth.submit_register') }}</a></p>
    <p class="alt" style="margin-top:8px">{{ __('shop.auth.seller_q') }} <a class="hs-link" href="{{ route('early-access.index') }}">{{ __('shop.auth.seller_cta') }}</a></p>
  </form>
</div>
@endsection
