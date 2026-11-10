@extends('emails.shop._layout')
@php $en = $lang === 'en'; @endphp
@section('body')
<p class="eyebrow">{{ $order->number }}</p>
<h1>{{ $en ? 'The buyer has opened an issue' : 'El comprador ha abierto una incidencia' }}</h1>
<p>{{ $en ? 'Funds for' : 'Los fondos de' }} <strong>{{ $item->title }}</strong> {{ $en ? 'remain on hold while our team reviews the case. We will contact you shortly.' : 'permanecen retenidos mientras nuestro equipo revisa el caso. Te contactaremos en breve.' }}</p>
<div class="box">{{ $item->dispute_reason }}</div>
@endsection
