@extends('layouts.admin')
@section('title', 'Cupones')
@section('admin-content')
<div class="panel-header">
  <div><div class="page-title">Cupones</div><p class="page-subtitle" style="margin-bottom:0">Descuentos a cargo de HAUS OF TIME: no reducen lo que cobra el vendedor</p></div>
  <a href="{{ route('admin.coupons.create') }}" class="btn btn--primary">+ Nuevo cupón</a>
</div>
<div class="table-wrap"><table class="data-table">
  <thead><tr><th>Código</th><th>Descuento</th><th>Condiciones</th><th>Validez</th><th>Usos</th><th>Estado</th><th></th></tr></thead>
  <tbody>
  @forelse($coupons as $c)
    <tr>
      <td style="font-family:monospace;letter-spacing:.06em">{{ $c->code }}<div style="font-size:11px;color:var(--a-subtle);font-family:var(--font)">{{ $c->description }}</div></td>
      <td>{{ $c->summary }}</td>
      <td style="font-size:12px">
        @if($c->min_subtotal)Mín. {{ number_format($c->min_subtotal, 0, ',', '.') }} €<br>@endif
        @if($c->max_discount)Máx. {{ number_format($c->max_discount, 0, ',', '.') }} €<br>@endif
        @if($c->first_order_only)Solo primer pedido<br>@endif
        @if($c->brand_ids)Marcas: {{ \App\Models\WatchBrand::whereIn('id', $c->brand_ids)->pluck('name_es')->implode(', ') }}@endif
      </td>
      <td style="font-size:12px">{{ $c->starts_at?->format('d/m/Y') ?? '—' }} → {{ $c->ends_at?->format('d/m/Y') ?? '∞' }}</td>
      <td>{{ $c->used_count }}{{ $c->usage_limit ? ' / ' . $c->usage_limit : '' }}</td>
      <td>@if($c->active && (!$c->ends_at || $c->ends_at->isFuture()))<span class="badge badge-success">Activo</span>@else<span class="badge badge-secondary">Inactivo</span>@endif</td>
      <td style="white-space:nowrap">
        <a href="{{ route('admin.coupons.edit', $c) }}" class="btn btn--ghost btn--sm">Editar</a>
        <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" style="display:inline" onsubmit="return confirm('¿Eliminar el cupón {{ $c->code }}?')">@csrf @method('DELETE')<button class="btn btn--ghost btn--sm">✕</button></form>
      </td>
    </tr>
  @empty
    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--a-subtle)">Todavía no hay cupones</td></tr>
  @endforelse
  </tbody>
</table></div>
<div style="margin-top:18px">{{ $coupons->links() }}</div>
@endsection
