@extends('emails.shop._layout')
@php $en = $lang === 'en'; @endphp
@section('body')
<p class="eyebrow">{{ $en ? 'Shipped' : 'Enviado' }} · {{ $order->number }}</p>
<h1>{{ $en ? 'Your watch is on its way' : 'Tu reloj está en camino' }}</h1>
@include('emails.shop._items', ['items' => collect([$item])])
<p>{{ $en ? 'Carrier' : 'Transportista' }}: <strong>{{ strtoupper($item->carrier) }}</strong><br>{{ $en ? 'Tracking number' : 'Número de seguimiento' }}: <strong>{{ $item->tracking_number }}</strong></p>
@if($item->tracking_link)<div class="btn-wrap"><a class="btn" href="{{ $item->tracking_link }}">{{ $en ? 'Track shipment' : 'Seguir envío' }}</a></div>@endif
<div class="box">{{ $en ? 'When you receive it, confirm receipt from your account. If you do not confirm or open an issue, funds will be released to the seller on' : 'Cuando lo recibas, confirma la recepción desde tu cuenta. Si no confirmas ni abres una incidencia, los fondos se liberarán al vendedor el' }} {{ $item->release_due_at?->format('d/m/Y') }}.</div>
<p style="text-align:center"><a href="{{ route('account.order', $order) }}" style="color:#3d5a88">{{ $en ? 'View my order' : 'Ver mi pedido' }}</a></p>
@endsection
