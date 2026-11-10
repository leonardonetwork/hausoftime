@extends('layouts.shop')
@section('title', __('shop.account.profile') . ' — HAUS OF TIME')
@section('content')
<div class="hs-wrap">
  <h1 class="hs-page-title"><small>{{ __('shop.account.title') }}</small>{{ __('shop.account.profile') }}</h1>
  <div class="hs-acc">
    @include('shop.account._nav')
    <form method="POST" action="{{ route('account.profile.update') }}" class="hs-panel">
      @csrf @method('PUT')
      @if($errors->any())<div class="hs-alert hs-alert--err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
      <div class="hs-form">
        <div class="hs-field"><label>{{ __('shop.account.first_name') }}</label><input class="hs-input" name="first_name" value="{{ old('first_name', $user->first_name ?: $user->name) }}" required></div>
        <div class="hs-field"><label>{{ __('shop.account.last_name') }}</label><input class="hs-input" name="last_name" value="{{ old('last_name', $user->last_name) }}"></div>
        <div class="hs-field full"><label>{{ __('shop.account.email') }}</label><input class="hs-input" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
        <div class="hs-field full"><label>{{ __('shop.account.phone') }}</label><div class="hs-phone"><input class="hs-input" name="phone_prefix" value="{{ old('phone_prefix', $user->phone_prefix) }}"><input class="hs-input" name="phone" value="{{ old('phone', $user->phone) }}"></div></div>
        <label class="hs-check full"><input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in', $user->marketing_opt_in))> {{ __('shop.account.marketing') }}</label>
      </div>
      <h3 class="hs-section-t" style="margin-top:32px">{{ __('shop.account.change_password') }}</h3>
      <div class="hs-form">
        <div class="hs-field full"><label>{{ __('shop.account.current_password') }}</label><input class="hs-input" type="password" name="current_password" autocomplete="current-password"></div>
        <div class="hs-field"><label>{{ __('shop.account.new_password') }}</label><input class="hs-input" type="password" name="password" autocomplete="new-password"></div>
        <div class="hs-field"><label>{{ __('shop.account.confirm_password') }}</label><input class="hs-input" type="password" name="password_confirmation" autocomplete="new-password"></div>
      </div>
      <button class="hs-btn" style="margin-top:22px">{{ __('shop.account.save') }}</button>
    </form>
  </div>
</div>
@endsection
