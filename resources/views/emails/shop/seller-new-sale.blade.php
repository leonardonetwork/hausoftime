@extends('emails.shop._layout')
@php $en = $lang === 'en'; $first = $items->first(); @endphp
@section('body')
<p class="eyebrow">{{ $en ? 'New sale' : 'Nueva venta' }} · {{ $order->number }}</p>
<h1>{{ $en ? 'Congratulations, your watch has been sold' : 'Enhorabuena, tu reloj se ha vendido' }}</h1>
<p>{{ $en ? 'The buyer has paid and the funds are held by HAUS OF TIME. Please ship before' : 'El comprador ha pagado y HAUS OF TIME retiene los fondos. Envíalo antes del' }} <strong>{{ $first->ship_by?->format('d/m/Y') }}</strong> {{ $en ? 'with FedEx, insured for the full value.' : 'con FedEx, asegurado por el valor íntegro.' }}</p>
@include('emails.shop._items', ['items' => $items, 'showPayout' => true])
<p class="s" style="font-size:12px;color:#888">{{ $en ? 'Amount shown: your payout (price − commission + shipping and insurance).' : 'Importe indicado: lo que recibirás (precio − comisión + envío y seguro).' }}</p>
<p><strong>{{ $en ? 'Ship to' : 'Dirección de envío' }}</strong></p>
<div class="addr">{{ $order->shipping_address_text }}</div>
<div class="box">{{ $en ? 'Once shipped, enter the FedEx tracking number in your seller area. Funds are released when the buyer confirms receipt, or automatically after the protection period.' : 'Cuando lo envíes, introduce el número de seguimiento FedEx en tu área de vendedor. Los fondos se liberan cuando el comprador confirma la recepción o, automáticamente, al terminar el periodo de protección.' }}</div>
<div class="btn-wrap"><a class="btn" href="{{ route('seller.sales.show', $first) }}">{{ $en ? 'Add tracking' : 'Añadir seguimiento' }}</a></div>
@endsection
