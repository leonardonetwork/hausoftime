@extends('layouts.admin')
@section('title', 'Pedidos')
@php $m = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €'; @endphp
@section('admin-content')
<div class="page-title">Pedidos</div>
<p class="page-subtitle">Ventas del marketplace, envíos, fondos retenidos y liquidaciones a vendedores</p>

<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="stat-card"><div class="stat-card__num">{{ $m($stats['revenue']) }}</div><div class="stat-card__label">Ventas cobradas · {{ $stats['orders'] }} pedidos</div></div>
  <div class="stat-card"><div class="stat-card__num">{{ $m($stats['commission']) }}</div><div class="stat-card__label">Comisiones HOT</div></div>
  <div class="stat-card"><div class="stat-card__num">{{ $m($stats['escrow']) }}</div><div class="stat-card__label">Fondos retenidos (a liquidar)</div></div>
  <div class="stat-card"><div class="stat-card__num" style="{{ $stats['late'] ? 'color:var(--a-danger)' : '' }}">{{ $stats['to_ship'] }}</div><div class="stat-card__label">Por enviar @if($stats['late'])· {{ $stats['late'] }} con retraso @endif @if($stats['disputed'])· {{ $stats['disputed'] }} incidencias @endif</div></div>
</div>

<div class="panel-header">
  <div class="tabs">
    <a class="tab {{ !request('status') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">Todos</a>
    @foreach(['paid' => 'Pagados', 'in_progress' => 'En curso', 'disputed' => 'Disputa', 'completed' => 'Completados', 'pending_payment' => 'Sin pagar', 'refunded' => 'Reembolsados', 'cancelled' => 'Cancelados', 'expired' => 'Caducados'] as $k => $v)
      <a class="tab {{ request('status') === $k ? 'active' : '' }}" href="{{ route('admin.orders.index', ['status' => $k]) }}">{{ $v }}</a>
    @endforeach
  </div>
  <form class="filter-bar" method="GET">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Nº pedido, email, marca…">
    <button class="btn btn--ghost">Buscar</button>
  </form>
</div>

<div class="table-wrap"><table class="data-table">
  <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Piezas</th><th>Total</th><th>Estado</th><th></th></tr></thead>
  <tbody>
  @forelse($orders as $o)
    <tr>
      <td>{{ $o->number }}</td>
      <td>{{ ($o->paid_at ?? $o->created_at)->format('d/m/Y H:i') }}</td>
      <td>{{ $o->email }}<div style="font-size:11px;color:var(--a-subtle)">{{ \App\Support\Countries::name($o->shipping_country, 'es') }}</div></td>
      <td style="font-size:12px">@foreach($o->items as $i)<div>{{ $i->title }} <span class="badge {{ $i->status_class }}">{{ $i->status_label }}</span></div>@endforeach</td>
      <td style="color:var(--a-text);font-weight:500">{{ $m($o->total) }}</td>
      <td><span class="badge {{ $o->status_class }}">{{ $o->status_label }}</span></td>
      <td><a href="{{ route('admin.orders.show', $o) }}" class="btn btn--ghost btn--sm">Ver</a></td>
    </tr>
  @empty
    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--a-subtle)">Sin pedidos</td></tr>
  @endforelse
  </tbody>
</table></div>
<div style="margin-top:18px">{{ $orders->links() }}</div>
@endsection
