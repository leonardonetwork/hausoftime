{{-- Pagine pubbliche nate per l'Early Access, rese con la grafica del negozio (sito live). --}}
@extends('layouts.shop')
@section('body_class', 'hs-lp')
@push('head')
  <link rel="stylesheet" href="/css/live-pages.css?v={{ filemtime(public_path('css/live-pages.css')) }}">
@endpush
