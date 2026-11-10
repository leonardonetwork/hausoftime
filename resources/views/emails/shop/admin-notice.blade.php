@extends('emails.shop._layout')
@section('body')
<p class="eyebrow">Administración</p>
<h1>{{ $title }}</h1>
@foreach($lines as $l)<p style="margin:0 0 6px">{{ $l }}</p>@endforeach
@if(!empty($url))<div class="btn-wrap"><a class="btn" href="{{ $url }}">Abrir en el panel</a></div>@endif
@endsection
