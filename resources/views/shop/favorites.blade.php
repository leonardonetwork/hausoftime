@extends('layouts.shop')
@section('title', __('shop.nav.favorites') . ' — HAUS OF TIME')
@section('content')
<div class="hs-wrap">
  <h1 class="hs-page-title"><small>HAUS OF TIME</small>{{ __('shop.nav.favorites') }}</h1>
  @if($watches->count())
    <div class="hs-grid" id="hsGrid">@include('shop.partials.cards', ['watches' => $watches, 'favoriteIds' => $favoriteIds])</div>
  @else
    <div class="hs-empty"><p>{{ app()->getLocale() === 'en' ? 'You have not saved any pieces yet. Tap the heart on any watch to keep it here.' : 'Aún no has guardado ninguna pieza. Pulsa el corazón de cualquier reloj para guardarlo aquí.' }}</p>
      <a href="{{ route('shop.index') }}" class="hs-btn">{{ __('shop.bag.continue') }}</a></div>
  @endif
</div>
@endsection
