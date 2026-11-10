{{-- resources/views/seller/watches/index.blade.php --}}
@extends('layouts.seller')
@section('title', __('messages.seller.my_watches'))

@section('seller-content')
<div class="panel-header">
  <div>
    <div class="page-title">{{ __('messages.seller.my_watches') }}</div>
    <p class="page-subtitle">{{ $watches->total() }} {{ app()->getLocale() === 'es' ? 'relojes' : 'watches' }}</p>
  </div>
  <a href="{{ route('seller.watches.create') }}" class="btn btn--primary">+ {{ __('messages.seller.upload_watch') }}</a>
</div>

@if($watches->isEmpty())
  <div class="card" style="text-align:center;padding:60px;">
    <p style="color:var(--text-muted);margin-bottom:20px;">{{ __('messages.seller.no_watches') }}</p>
    <a href="{{ route('seller.watches.create') }}" class="btn btn--primary">{{ __('messages.seller.upload_first') }}</a>
  </div>
@else
  <div class="watch-grid">
    @foreach($watches as $watch)
    <div class="watch-card">
      <div class="watch-card__img">
        @if(!empty($watch->images[0]))
          <img src="{{ asset('storage/' . $watch->images[0]) }}" alt="{{ $watch->model }}">
        @else
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);font-size:2rem;">⌚</div>
        @endif
      </div>
      <div class="watch-card__body">
        <div class="watch-card__brand">{{ $watch->brand }}</div>
        <div class="watch-card__name">{{ $watch->model }}</div>
        <div class="watch-card__price">{{ $watch->formatted_price }}</div>
      </div>
      <div class="watch-card__footer">
        @php $b = $watch->status_badge; @endphp
        <span class="badge badge-{{ str_replace('badge-','',$b['class']) }}">{{ $b['label'] }}</span>
        <a href="{{ route('seller.watches.show', $watch) }}" class="btn btn--ghost btn--sm">Ver</a>
      </div>
    </div>
    @endforeach
  </div>

  <div style="margin-top:32px;">
    {{ $watches->links() }}
  </div>
@endif
@endsection
