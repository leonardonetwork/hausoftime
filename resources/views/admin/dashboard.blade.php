{{-- ============================================================
     resources/views/admin/dashboard.blade.php
============================================================ --}}
@extends('layouts.admin')
@section('title','Panel')

@section('admin-content')
<div class="page-title">Panel</div>
<p class="page-subtitle">{{ ucfirst(now()->translatedFormat('l, j \d\e F \d\e Y')) }}</p>

{{-- ── Modo del sitio ─────────────────────────────── --}}
<div class="site-mode {{ $siteMode === 'live' ? 'is-live' : '' }}">
  <div style="display:flex;align-items:center;gap:14px">
    <span class="site-mode__dot"></span>
    <div>
      <div style="font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--a-subtle)">Modo del sitio</div>
      <div style="font-size:1.05rem;font-weight:600">{{ $siteMode === 'live' ? 'LIVE · marketplace público' : 'EARLY ACCESS · landing de lanzamiento' }}</div>
      <div style="font-size:12px;color:var(--a-muted)">{{ $siteMode === 'live' ? 'Cualquier visitante ve el catálogo y puede comprar.' : 'El público ve la landing actual. La tienda solo es visible para administradores, la lista blanca y el enlace de vista previa.' }}</div>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <a href="{{ route('view.switch', 'live') }}" class="btn btn--ghost btn--sm">Ver versión Live</a>
    <a href="{{ route('view.switch', 'early-access') }}" class="btn btn--ghost btn--sm">Ver Early Access</a>
    <form method="POST" action="{{ route('admin.site-mode') }}" onsubmit="return confirm('{{ $siteMode === 'live' ? '¿Volver a Early Access? La tienda dejará de ser pública.' : '¿Publicar el marketplace? Todos los visitantes verán la tienda y podrán comprar.' }}')">
      @csrf
      <input type="hidden" name="site_mode" value="{{ $siteMode === 'live' ? 'early_access' : 'live' }}">
      <button type="submit" class="btn {{ $siteMode === 'live' ? 'btn--ghost' : 'btn--success' }}">{{ $siteMode === 'live' ? 'Volver a Early Access' : 'Publicar LIVE' }}</button>
    </form>
  </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(5,1fr);">
  <div class="stat-card"><div class="stat-card__num">{{ number_format($shop['revenue_month'], 0, ',', '.') }} €</div><div class="stat-card__label">Ventas este mes</div></div>
  <div class="stat-card"><div class="stat-card__num">{{ $shop['orders_month'] }}</div><div class="stat-card__label">Pedidos este mes</div></div>
  <div class="stat-card"><div class="stat-card__num">{{ $shop['published'] }}</div><div class="stat-card__label">Relojes publicados</div></div>
  <a class="stat-card" href="{{ route('admin.orders.index') }}"><div class="stat-card__num" style="{{ $shop['to_ship'] ? 'color:var(--a-warning)' : '' }}">{{ $shop['to_ship'] }}</div><div class="stat-card__label">Pendientes de envío</div></a>
  <a class="stat-card" href="{{ route('admin.orders.index', ['status' => 'disputed']) }}"><div class="stat-card__num" style="{{ $shop['disputed'] ? 'color:var(--a-danger)' : '' }}">{{ $shop['disputed'] }}</div><div class="stat-card__label">Incidencias</div></a>
</div>

@if($recentOrders->count())
<div style="margin-bottom:28px">
  <div class="panel-header"><h3 style="font-size:1rem;font-weight:600;">Últimos pedidos</h3><a href="{{ route('admin.orders.index') }}" class="btn btn--ghost btn--sm">Ver todos</a></div>
  <div class="table-wrap"><table class="data-table">
    <thead><tr><th>Pedido</th><th>Cliente</th><th>Total</th><th>Estado</th><th></th></tr></thead>
    <tbody>@foreach($recentOrders as $o)
      <tr><td>{{ $o->number }}</td><td>{{ $o->email }}</td><td>{{ number_format($o->total, 2, ',', '.') }} €</td>
        <td><span class="badge {{ $o->status_class }}">{{ $o->status_label }}</span></td>
        <td><a href="{{ route('admin.orders.show', $o) }}" class="btn btn--ghost btn--sm">Ver</a></td></tr>
    @endforeach</tbody></table></div>
</div>
@endif

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-card">
    <div class="stat-card__num">{{ $stats['sellers'] }}</div>
    <div class="stat-card__label">{{ __('messages.admin.total_sellers') }}</div>
    @if($stats['pending_sellers'])<div style="font-size:.75rem;color:var(--warning);margin-top:4px;">{{ $stats['pending_sellers'] }} {{ __('messages.admin.pending_sellers') }}</div>@endif
  </div>
  <div class="stat-card">
    <div class="stat-card__num">{{ $stats['watches'] }}</div>
    <div class="stat-card__label">{{ __('messages.admin.total_watches') }}</div>
    @if($stats['pending_watches'])<div style="font-size:.75rem;color:var(--warning);margin-top:4px;">{{ $stats['pending_watches'] }} pendientes</div>@endif
  </div>
  <div class="stat-card">
    <div class="stat-card__num">{{ $stats['subscribers'] }}</div>
    <div class="stat-card__label">{{ __('messages.admin.total_subscribers') }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-card__num">{{ $stats['early_access'] }}</div>
    <div class="stat-card__label">{{ __('messages.admin.total_early_access') }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-card__num">{{ $stats['evaluations'] }}</div>
    <div class="stat-card__label">Evaluaciones AI</div>
  </div>
</div>

<div class="grid-2" style="gap:28px;margin-top:8px;">
  {{-- Pending watches --}}
  <div>
    <div class="panel-header"><h3 style="font-size:1rem;font-weight:600;">Relojes pendientes</h3>
      <a href="{{ route('admin.watches.index') }}?status=pending" class="btn btn--ghost btn--sm">Ver todos</a></div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Marca</th><th>Modelo</th><th>Vendedor</th><th>€</th><th></th></tr></thead>
        <tbody>
          @forelse($recentWatches as $w)
          <tr>
            <td>{{ $w->brand }}</td>
            <td>{{ $w->model }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $w->seller?->name }}</td>
            <td>{{ $w->formatted_price }}</td>
            <td><a href="{{ route('admin.watches.show',$w) }}" class="btn btn--ghost btn--sm">Ver</a></td>
          </tr>
          @empty
          <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px;">Sin relojes pendientes</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Recent early access --}}
  <div>
    <div class="panel-header"><h3 style="font-size:1rem;font-weight:600;">Últimas solicitudes</h3>
      <a href="{{ route('admin.subscribers.index') }}" class="btn btn--ghost btn--sm">Ver todas</a></div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Estado</th></tr></thead>
        <tbody>
          @forelse($recentRequests as $r)
          <tr>
            <td>{{ $r->full_name }}<div style="font-size:.75rem;color:var(--text-muted);">{{ $r->email }}</div></td>
            <td><span class="badge badge-{{ $r->type === 'seller' ? 'gold' : 'info' }}">{{ ucfirst($r->type) }}</span></td>
            <td><span class="badge badge-{{ $r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($r->status) }}</span></td>
          </tr>
          @empty
          <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:24px;">Sin solicitudes</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
