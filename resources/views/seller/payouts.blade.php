@extends('layouts.seller')
@php $es = app()->getLocale() === 'es'; @endphp
@section('title', __('shop.seller.payouts'))
@section('seller-content')
<h1 class="page-title">{{ __('shop.seller.payouts') }}</h1>
<p class="page-subtitle">{{ $es ? 'Recibe el importe de tus ventas directamente en tu banco a través de Stripe.' : 'Receive your sales directly in your bank account through Stripe.' }}</p>

<div class="card" style="max-width:760px">
  @if(!$configured)
    <p style="color:var(--s-muted)">{{ $es ? 'Los cobros estarán disponibles muy pronto.' : 'Payouts will be available very soon.' }}</p>
  @elseif($user->hasPayoutsReady())
    <div style="display:flex;gap:14px;align-items:center;margin-bottom:16px"><span class="badge badge-success" style="font-size:11px;padding:5px 12px">{{ $es ? 'Cobros activos' : 'Payouts active' }}</span>
      <span style="font-size:12px;color:var(--s-muted)">Stripe · {{ $user->stripe_country }}</span></div>
    <p style="font-size:14px;color:var(--s-muted);line-height:1.7;margin-bottom:20px">{{ $es ? 'Todo listo. Cada venta liberada se transferirá automáticamente a tu cuenta Stripe y desde ahí a tu banco.' : 'All set. Each released sale will be transferred automatically to your Stripe account and then to your bank.' }}</p>
    <a href="{{ route('seller.payouts.dashboard') }}" class="btn btn--ghost">{{ $es ? 'Abrir mi panel de Stripe ↗' : 'Open my Stripe dashboard ↗' }}</a>
  @else
    @if($user->stripe_account_id)
      <div style="margin-bottom:16px"><span class="badge badge-warning" style="font-size:11px;padding:5px 12px">{{ $user->stripe_details_submitted ? ($es ? 'En verificación por Stripe' : 'Being verified by Stripe') : ($es ? 'Configuración incompleta' : 'Setup incomplete') }}</span></div>
    @endif
    <p style="font-size:14px;color:var(--s-muted);line-height:1.7;margin-bottom:20px">
      {{ $es ? 'HAUS OF TIME utiliza Stripe Connect para pagar a los vendedores. El proceso dura unos minutos: datos personales, documento de identidad y cuenta bancaria (IBAN). HAUS OF TIME no ve ni guarda tus datos bancarios.' : 'HAUS OF TIME uses Stripe Connect to pay sellers. It takes a few minutes: personal details, ID document and bank account (IBAN). HAUS OF TIME never sees or stores your bank details.' }}
    </p>
    <form method="POST" action="{{ route('seller.payouts.connect') }}">
      @csrf
      @if(!$user->stripe_account_id)
        <div class="form-group" style="max-width:340px">
          <label class="form-label">{{ $es ? 'País de tu cuenta bancaria' : 'Country of your bank account' }}</label>
          <select name="country" class="form-control">
            @foreach($countries as $c)<option value="{{ $c->code }}" @selected($countryCode === $c->code)>{{ $c->name }}</option>@endforeach
          </select>
        </div>
      @endif
      <button class="btn btn--dark btn--lg">{{ $user->stripe_account_id ? ($es ? 'Continuar configuración' : 'Continue setup') : ($es ? 'Activar cobros con Stripe' : 'Activate payouts with Stripe') }}</button>
    </form>
  @endif
</div>
@endsection
