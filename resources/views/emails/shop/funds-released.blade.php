@extends('emails.shop._layout')
@php $en = $lang === 'en'; $m = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €'; @endphp
@section('body')
<p class="eyebrow">{{ $en ? 'Payout' : 'Liquidación' }} · {{ $order->number }}</p>
<h1>{{ $en ? 'Funds released' : 'Fondos liberados' }}</h1>
<p>{{ $en ? 'We have transferred' : 'Hemos transferido' }} <strong>{{ $m($item->seller_payout) }}</strong> {{ $en ? 'to your Stripe account for' : 'a tu cuenta Stripe por' }} {{ $item->title }}. {{ $en ? 'Stripe will pay it out to your bank according to your payout schedule.' : 'Stripe lo abonará en tu banco según tu calendario de pagos.' }}</p>
<table class="sum" role="presentation">
  <tr><td>{{ $en ? 'Sale price' : 'Precio de venta' }}</td><td>{{ $m($item->price) }}</td></tr>
  <tr><td>{{ $en ? 'Commission' : 'Comisión' }} ({{ rtrim(rtrim(number_format($item->commission_rate, 2, ',', '.'), '0'), ',') }} %)</td><td>−{{ $m($item->commission_amount) }}</td></tr>
  <tr><td>{{ $en ? 'Shipping & insurance' : 'Envío y seguro' }}</td><td>{{ $m($item->shipping + $item->insurance) }}</td></tr>
  <tr class="tot"><td>{{ $en ? 'Transferred' : 'Transferido' }}</td><td>{{ $m($item->seller_payout) }}</td></tr>
</table>
<div class="btn-wrap"><a class="btn" href="{{ route('seller.sales') }}">{{ $en ? 'My sales' : 'Mis ventas' }}</a></div>
@endsection
