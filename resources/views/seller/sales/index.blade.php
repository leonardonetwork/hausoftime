@extends('layouts.seller')
@php $es = app()->getLocale() === 'es'; $m = fn ($v) => \App\Models\Watch::money($v, true); @endphp
@section('title', __('shop.seller.sales'))
@section('seller-content')
<div class="panel-header" style="margin-bottom:28px;">
  <div>
    <h1 class="page-title">{{ __('shop.seller.sales') }}</h1>
    <p class="page-subtitle" style="margin-bottom:0;">{{ $es ? 'Pedidos pagados de tus relojes, envíos y liquidaciones' : 'Paid orders for your watches, shipments and payouts' }}</p>
  </div>
  @if(!auth()->user()->hasPayoutsReady())
    <a href="{{ route('seller.payouts') }}" class="btn btn--dark btn--lg">{{ $es ? 'Activar cobros' : 'Activate payouts' }}</a>
  @endif
</div>

<div class="stats-grid" style="margin-bottom:28px">
  <div class="stat-card"><div class="stat-card__num stat-card__num--warning">{{ $stats['to_ship'] }}</div><div class="stat-card__label">{{ $es ? 'Pendientes de envío' : 'To ship' }}</div></div>
  <div class="stat-card"><div class="stat-card__num">{{ $stats['in_transit'] }}</div><div class="stat-card__label">{{ $es ? 'En tránsito' : 'In transit' }}</div></div>
  <div class="stat-card"><div class="stat-card__num stat-card__num--gold">{{ $m($stats['pending_payout']) }}</div><div class="stat-card__label">{{ $es ? 'Pendiente de liberar' : 'Pending release' }}</div></div>
  <div class="stat-card"><div class="stat-card__num stat-card__num--success">{{ $m($stats['paid_out']) }}</div><div class="stat-card__label">{{ $es ? 'Liquidado' : 'Paid out' }}</div></div>
</div>

@if($sales->count())
<div class="table-wrap"><div style="overflow-x:auto">
  <table class="data-table">
    <thead><tr><th>{{ $es ? 'Pedido' : 'Order' }}</th><th>{{ $es ? 'Reloj' : 'Watch' }}</th><th>{{ $es ? 'Recibirás' : 'Payout' }}</th><th>{{ $es ? 'Estado' : 'Status' }}</th><th>{{ $es ? 'Plazo de envío' : 'Ship by' }}</th><th></th></tr></thead>
    <tbody>
      @foreach($sales as $s)
      <tr>
        <td class="td-strong">{{ $s->order->number }}<div style="font-size:11px;color:var(--s-subtle)">{{ $s->order->paid_at?->format('d/m/Y H:i') }}</div></td>
        <td class="td-strong">{{ $s->title }}<div style="font-size:11px;color:var(--s-subtle)">{{ $s->reference }}</div></td>
        <td>{{ $m($s->seller_payout) }}</td>
        <td><span class="badge {{ $s->status_class }}">{{ $s->status_label }}</span></td>
        <td>@if($s->status === 'awaiting_shipment' && $s->ship_by)<span style="color:{{ $s->ship_by->isPast() ? 'var(--s-danger)' : 'inherit' }}">{{ $s->ship_by->format('d/m/Y') }}</span>@else — @endif</td>
        <td><a href="{{ route('seller.sales.show', $s) }}" class="btn btn--{{ $s->status === 'awaiting_shipment' ? 'dark' : 'ghost' }} btn--sm">{{ $s->status === 'awaiting_shipment' ? ($es ? 'Registrar envío' : 'Add shipment') : ($es ? 'Ver' : 'View') }}</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div></div>
<div style="margin-top:18px">{{ $sales->links() }}</div>
@else
<div class="card" style="text-align:center;padding:48px 24px;color:var(--s-muted)">{{ $es ? 'Todavía no tienes ventas. Cuando un comprador pague uno de tus relojes aparecerá aquí.' : 'No sales yet. When a buyer pays for one of your watches it will appear here.' }}</div>
@endif
@endsection
