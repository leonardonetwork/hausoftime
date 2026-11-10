@extends('layouts.seller')
@section('title', __('messages.seller.dashboard'))

@section('seller-content')

{{-- Page header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:16px;">
  <div>
    <h1 class="page-title">
      {{ __('messages.seller.welcome') }} {{ auth()->user()->first_name ?: auth()->user()->name }}
    </h1>
    <p class="page-subtitle" style="margin-bottom:0;">
      {{ now()->translatedFormat('l, d F Y') }}
      @if(auth()->user()->is_founding_seller)
        &nbsp;·&nbsp;<span style="color:var(--s-gold);font-weight:500;">Founding Seller</span>
      @endif
    </p>
  </div>
  <a href="{{ route('seller.watches.create') }}" class="btn btn--dark btn--lg">
    + {{ __('messages.seller.upload_watch') }}
  </a>
</div>

{{-- Stats --}}
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-card__num">{{ $stats['watches'] ?? 0 }}</div>
    <div class="stat-card__label">{{ __('messages.seller.stats_total') }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-card__num stat-card__num--warning">{{ $stats['pending'] ?? 0 }}</div>
    <div class="stat-card__label">{{ __('messages.seller.stats_pending') }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-card__num stat-card__num--success">{{ $stats['approved'] ?? 0 }}</div>
    <div class="stat-card__label">{{ __('messages.seller.stats_approved') }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-card__num stat-card__num--gold">{{ $stats['sold'] ?? 0 }}</div>
    <div class="stat-card__label">{{ __('messages.seller.stats_sold') }}</div>
  </div>
</div>

{{-- Recent watches --}}
<div class="table-wrap">
  <div class="table-header">
    <span class="table-title">{{ __('messages.seller.recent_uploads') }}</span>
    <a href="{{ route('seller.watches') }}" class="table-link">
      {{ app()->getLocale() === 'es' ? 'Ver todos →' : 'View all →' }}
    </a>
  </div>

  @if(isset($recentWatches) && $recentWatches->count())
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:64px;">{{ app()->getLocale() === 'es' ? 'Foto' : 'Photo' }}</th>
          <th>{{ app()->getLocale() === 'es' ? 'Reloj' : 'Watch' }}</th>
          <th>{{ app()->getLocale() === 'es' ? 'Precio' : 'Price' }}</th>
          <th>{{ app()->getLocale() === 'es' ? 'Estado' : 'Status' }}</th>
          <th style="width:80px;"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentWatches as $w)
        @php
          $imgs  = is_string($w->images) ? json_decode($w->images, true) : ($w->images ?? []);
          $sc    = ['approved' => 'badge-success', 'rejected' => 'badge-danger', 'pending' => 'badge-warning', 'sold' => 'badge-gold', 'draft' => 'badge-secondary'];
          $class = $sc[$w->status] ?? 'badge-secondary';
          $label = __('messages.seller.status_' . $w->status, [], app()->getLocale()) ?: $w->status;
        @endphp
        <tr>
          <td>
            @if(is_array($imgs) && count($imgs))
              <img src="{{ Storage::url($imgs[0]) }}" style="width:44px;height:44px;object-fit:cover;border-radius:2px;border:1px solid var(--s-border);">
            @else
              <div style="width:44px;height:44px;background:var(--s-bg3);border:1px solid var(--s-border);border-radius:2px;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--s-subtle);">⌚</div>
            @endif
          </td>
          <td class="td-strong">
            <div style="font-weight:500;color:var(--s-text);">{{ $w->brand }}</div>
            <div style="font-size:12px;color:var(--s-muted);margin-top:1px;">{{ $w->model }}</div>
          </td>
          <td>{{ number_format($w->price, 0, ',', '.') }} €</td>
          <td><span class="badge {{ $class }}">{{ $label }}</span></td>
          <td>
            <a href="{{ route('seller.watches.show', $w) }}" class="btn btn--ghost btn--sm">
              {{ app()->getLocale() === 'es' ? 'Ver' : 'View' }}
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="empty-state">
    <div class="empty-state__icon">⌚</div>
    <p class="empty-state__text">{{ __('messages.seller.no_watches') }}</p>
    <a href="{{ route('seller.watches.create') }}" class="btn btn--dark btn--lg">
      + {{ __('messages.seller.upload_first') }}
    </a>
  </div>
  @endif
</div>

@endsection
