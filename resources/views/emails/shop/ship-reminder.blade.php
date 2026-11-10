@extends('emails.shop._layout')
@php $en = $lang === 'en'; @endphp
@section('body')
<p class="eyebrow">{{ $order->number }}</p>
<h1>{{ $en ? 'Shipping reminder' : 'Recordatorio de envío' }}</h1>
<p>{{ $en ? 'Please ship' : 'Recuerda enviar' }} <strong>{{ $item->title }}</strong> {{ $en ? 'before' : 'antes del' }} <strong>{{ $item->ship_by?->format('d/m/Y') }}</strong> {{ $en ? 'and add the FedEx tracking number.' : 'y añadir el número de seguimiento FedEx.' }}</p>
<div class="btn-wrap"><a class="btn" href="{{ route('seller.sales.show', $item) }}">{{ $en ? 'Add tracking' : 'Añadir seguimiento' }}</a></div>
@endsection
