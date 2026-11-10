@extends('layouts.shop')
@section('title', __('shop.account.addresses') . ' — HAUS OF TIME')
@section('content')
<div class="hs-wrap">
  <h1 class="hs-page-title"><small>{{ __('shop.account.title') }}</small>{{ __('shop.account.addresses') }}</h1>
  <div class="hs-acc">
    @include('shop.account._nav')
    <div style="display:grid;gap:20px">
      @if($errors->any())<div class="hs-alert hs-alert--err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
      @forelse($addresses as $a)
        <div class="hs-panel">
          <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div style="white-space:pre-line;font-size:14px;line-height:1.6">@if($a->label)<strong>{{ $a->label }}</strong>@endif @if($a->is_default)<span class="hs-pill badge-gold">{{ __('shop.account.default') }}</span>@endif
{{ \App\Models\Address::format($a->toSnapshot()) }}</div>
            <form method="POST" action="{{ route('account.addresses.destroy', $a) }}" data-confirm="{{ __('shop.account.delete') }}?">@csrf @method('DELETE')<button class="hs-btn hs-btn--ghost hs-btn--sm">{{ __('shop.account.delete') }}</button></form>
          </div>
          <details class="hs-details" style="margin-top:14px"><summary>{{ __('shop.account.edit') }}</summary>
            <form method="POST" action="{{ route('account.addresses.update', $a) }}">@csrf @method('PUT')
              @include('shop.account._address_form', ['address' => $a])
              <button class="hs-btn hs-btn--sm" style="margin-top:16px">{{ __('shop.account.save') }}</button>
            </form>
          </details>
        </div>
      @empty
        <div class="hs-panel"><p style="color:var(--hs-muted)">{{ __('shop.account.no_addresses') }}</p></div>
      @endforelse
      <div class="hs-panel">
        <h3 class="hs-section-t">{{ __('shop.account.add_address') }}</h3>
        <form method="POST" action="{{ route('account.addresses.store') }}">@csrf
          @include('shop.account._address_form', ['address' => null])
          <button class="hs-btn" style="margin-top:18px">{{ __('shop.account.save') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
