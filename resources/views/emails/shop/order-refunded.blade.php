@extends('emails.shop._layout')
@php $en = $lang === 'en'; $m = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €'; @endphp
@section('body')
<p class="eyebrow">{{ $order->number }}</p>
<h1>{{ $en ? 'Refund issued' : 'Reembolso emitido' }}</h1>
<p>{{ $en ? 'We have refunded' : 'Hemos reembolsado' }} <strong>{{ $m($amount) }}</strong> {{ $en ? 'for' : 'por' }} {{ $item->title }}. {{ $en ? 'Depending on your bank it may take 5–10 days to appear.' : 'Según tu banco, puede tardar entre 5 y 10 días en aparecer.' }}</p>
<div class="btn-wrap"><a class="btn" href="{{ route('account.order', $order) }}">{{ $en ? 'View my order' : 'Ver mi pedido' }}</a></div>
@endsection
