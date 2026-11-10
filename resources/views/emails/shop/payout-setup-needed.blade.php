@extends('emails.shop._layout')
@php $en = $lang === 'en'; @endphp
@section('body')
<p class="eyebrow">{{ $order->number }}</p>
<h1>{{ $en ? 'Activate your payouts' : 'Activa tus cobros' }}</h1>
<p>{{ $en ? 'Your sale of' : 'Tu venta de' }} <strong>{{ $item->title }}</strong> {{ $en ? 'is ready to be paid out, but your Stripe payout account is not active yet. Complete it in a few minutes and we will transfer the funds automatically.' : 'está lista para liquidarse, pero tu cuenta de cobros Stripe aún no está activa. Complétala en pocos minutos y transferiremos los fondos automáticamente.' }}</p>
<div class="btn-wrap"><a class="btn" href="{{ route('seller.payouts') }}">{{ $en ? 'Activate payouts' : 'Activar cobros' }}</a></div>
@endsection
