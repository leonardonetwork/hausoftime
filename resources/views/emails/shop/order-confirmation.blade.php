@extends('emails.shop._layout')
@php $en = $lang === 'en'; $m = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €'; @endphp
@section('body')
<p class="eyebrow">{{ $en ? 'Order confirmed' : 'Pedido confirmado' }} · {{ $order->number }}</p>
<h1>{{ $en ? 'Thank you for your purchase' : 'Gracias por tu compra' }}</h1>
<p>{{ $en ? 'Your payment has been received and is held securely. The seller has been notified and will ship your watch with FedEx, fully insured.' : 'Hemos recibido tu pago y queda retenido de forma segura. El vendedor ya ha sido avisado y enviará tu reloj con FedEx, totalmente asegurado.' }}</p>
@include('emails.shop._items', ['items' => $order->items])
<table class="sum" role="presentation">
  <tr><td>Subtotal</td><td>{{ $m($order->subtotal) }}</td></tr>
  @if($order->discount_total > 0)<tr><td>{{ $en ? 'Discount' : 'Descuento' }} {{ $order->coupon_code }}</td><td>−{{ $m($order->discount_total) }}</td></tr>@endif
  <tr><td>{{ $en ? 'Insured shipping' : 'Envío asegurado' }}</td><td>{{ $m($order->shipping_total) }}</td></tr>
  <tr class="tot"><td>Total</td><td>{{ $m($order->total) }}</td></tr>
</table>
<p><strong>{{ $en ? 'Shipping to' : 'Envío a' }}</strong></p>
<div class="addr">{{ $order->shipping_address_text }}</div>
<div class="box">{{ $en ? 'When your watch arrives, check it and confirm receipt from your account. Only then are funds released to the seller. If anything is wrong, open an issue before confirming.' : 'Cuando recibas tu reloj, compruébalo y confirma la recepción desde tu cuenta. Solo entonces se liberan los fondos al vendedor. Si algo no es correcto, abre una incidencia antes de confirmar.' }}</div>
<div class="btn-wrap"><a class="btn" href="{{ route('account.order', $order) }}">{{ $en ? 'View my order' : 'Ver mi pedido' }}</a></div>
@endsection
