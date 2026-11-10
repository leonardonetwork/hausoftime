{{-- resources/views/admin/products/index.blade.php --}}
@extends('layouts.admin')
@section('title', __('messages.admin.products'))

@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">{{ __('messages.admin.products') }}</div>
    <p class="page-subtitle">{{ $watches->total() }} relojes en total</p>
  </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar" style="margin-bottom:24px;">
  <select name="status" class="form-control form-control--select" style="width:160px;" onchange="this.form.submit()">
    <option value="">Todos los estados</option>
    @foreach(['pending' => 'Pendiente','approved' => 'Aprobado','rejected' => 'Rechazado','sold' => 'Vendido'] as $v => $l)
      <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
    @endforeach
  </select>
  <input type="text" name="brand" class="form-control" placeholder="Marca..." value="{{ request('brand') }}" style="width:160px;">
  <input type="text" name="seller" class="form-control" placeholder="Vendedor..." value="{{ request('seller') }}" style="width:160px;">
  <button type="submit" class="btn btn--ghost btn--sm">Filtrar</button>
  @if(request()->hasAny(['status','brand','seller']))
    <a href="{{ route('admin.watches.index') }}" class="btn btn--ghost btn--sm">✕ Limpiar</a>
  @endif
</form>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Reloj</th>
        <th>Vendedor</th>
        <th>Precio</th>
        <th>Estado</th>
        <th>Condición</th>
        <th>Fecha</th>
        <th>{{ __('messages.admin.actions') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($watches as $watch)
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:12px;">
            @if(!empty($watch->images[0]))
              <img src="{{ asset('storage/'.$watch->images[0]) }}" style="width:44px;height:44px;border-radius:6px;object-fit:cover;">
            @else
              <div style="width:44px;height:44px;border-radius:6px;background:var(--bg-elevated);display:flex;align-items:center;justify-content:center;color:var(--text-muted);">⌚</div>
            @endif
            <div>
              <div>{{ $watch->brand }} {{ $watch->model }}</div>
              <div style="font-size:.75rem;color:var(--text-muted);">{{ $watch->reference }}</div>
            </div>
          </div>
        </td>
        <td style="font-size:.85rem;">{{ $watch->seller?->name }}<div style="font-size:.75rem;color:var(--text-muted);">{{ $watch->seller?->email }}</div></td>
        <td>{{ $watch->formatted_price }}</td>
        <td><span class="badge badge-{{ str_replace('badge-','',$watch->status_badge['class']) }}">{{ $watch->status_badge['label'] }}</span></td>
        <td style="font-size:.82rem;color:var(--text-secondary);">{{ $watch->condition_label }}</td>
        <td style="font-size:.8rem;color:var(--text-muted);">{{ $watch->created_at->format('d/m/Y') }}</td>
        <td>
          <div style="display:flex;gap:6px;">
            <a href="{{ route('admin.watches.show',$watch) }}" class="btn btn--ghost btn--sm">{{ __('messages.admin.view') }}</a>
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Sin resultados</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:20px;">{{ $watches->withQueryString()->links() }}</div>
@endsection
