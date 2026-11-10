@extends('layouts.shop')
@section('title', __('shop.auth.register_title') . ' — HAUS OF TIME')
@section('content')
<div class="hs-wrap hs-auth">
  <form method="POST" action="{{ route('account.register.submit') }}" class="hs-panel">
    @csrf
    <h1>{{ __('shop.auth.register_title') }}</h1>
    <p class="sub">{{ __('shop.auth.register_sub') }}</p>
    @if($errors->any())<div class="hs-alert hs-alert--err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="hs-form">
      <div class="hs-field"><label for="first_name">{{ __('shop.account.first_name') }}</label><input class="hs-input" id="first_name" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name"></div>
      <div class="hs-field"><label for="last_name">{{ __('shop.account.last_name') }}</label><input class="hs-input" id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name"></div>
      <div class="hs-field full"><label for="email">{{ __('shop.auth.email') }}</label><input class="hs-input" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div>
      <div class="hs-field"><label for="password">{{ __('shop.auth.password') }}</label><input class="hs-input" id="password" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
      <div class="hs-field"><label for="password_confirmation">{{ __('shop.auth.password_confirmation') }}</label><input class="hs-input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
      <label class="hs-check full"><input type="checkbox" name="accept_terms" value="1" required @checked(old('accept_terms'))> <span>{!! __('shop.auth.accept', ['terms' => route('page.show', 'terminos-y-condiciones'), 'privacy' => route('page.show', 'privacidad')]) !!}</span></label>
      <label class="hs-check full"><input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in'))> {{ __('shop.account.marketing') }}</label>
      <button class="hs-btn hs-btn--block full" style="margin-top:6px">{{ __('shop.auth.submit_register') }}</button>
    </div>
    <p class="alt">{{ __('shop.auth.have_account') }} <a class="hs-link" href="{{ route('account.login') }}">{{ __('shop.auth.submit_login') }}</a></p>
  </form>
</div>
@endsection
