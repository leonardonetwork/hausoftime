@extends('layouts.admin')
@section('title', 'Envíos')
@section('admin-content')
<div class="page-title">Envíos</div>
<p class="page-subtitle">Cada reloj viaja por separado desde su vendedor, asegurado. Coste por pieza = tarifa base + seguro (% del valor, con mínimo). Los países se asignan a las zonas en <a href="{{ route('admin.countries.index') }}" style="color:var(--a-gold)">Países</a>.</p>

@foreach($zones as $zone)
<div class="card" style="margin-bottom:20px">
  <form method="POST" action="{{ route('admin.shipping.zones.update', $zone) }}" style="display:grid;grid-template-columns:1.2fr 2fr 2fr auto auto;gap:12px;align-items:end">
    @csrf @method('PUT')
    <div class="form-group" style="margin:0"><label class="form-label">Zona</label><input name="name" class="form-control" value="{{ $zone->name }}" required></div>
    <div class="form-group" style="margin:0"><label class="form-label">Aviso checkout ES</label><input name="notice_es" class="form-control" value="{{ $zone->notice_es }}"></div>
    <div class="form-group" style="margin:0"><label class="form-label">Aviso checkout EN</label><input name="notice_en" class="form-control" value="{{ $zone->notice_en }}"></div>
    <label style="display:flex;gap:6px;align-items:center;font-size:12px;padding-bottom:9px"><input type="checkbox" name="active" value="1" @checked($zone->active)> Activa</label>
    <button class="btn btn--ghost btn--sm" style="margin-bottom:4px">Guardar</button>
  </form>
  <div style="font-size:11px;color:var(--a-subtle);margin:10px 0 14px">{{ $zone->enabled_count }} países habilitados de {{ $zone->countries_count }} asignados
    @if(!$zone->countries_count)
      · <form method="POST" action="{{ route('admin.shipping.zones.destroy', $zone) }}" style="display:inline" onsubmit="return confirm('¿Eliminar zona?')">@csrf @method('DELETE')<button style="background:none;border:0;color:var(--a-danger);cursor:pointer;font-size:11px">Eliminar zona</button></form>
    @endif
  </div>

  <div class="table-wrap"><table class="data-table">
    <thead><tr><th>Tarifa (ES / EN)</th><th>Transp.</th><th>Base €</th><th>Seguro %</th><th>Seguro mín. €</th><th>Gratis desde €</th><th>Valor min–máx €</th><th>Plazo</th><th>Activa</th><th></th></tr></thead>
    <tbody>
    @foreach($zone->rates as $r)
      <tr>
        <form method="POST" action="{{ route('admin.shipping.rates.update', $r) }}" id="rate{{ $r->id }}">@csrf @method('PUT')</form>
        <td><input form="rate{{ $r->id }}" name="name_es" class="form-control" value="{{ $r->name_es }}" required><input form="rate{{ $r->id }}" name="name_en" class="form-control" value="{{ $r->name_en }}" style="margin-top:4px"></td>
        <td><select form="rate{{ $r->id }}" name="carrier" class="form-control" style="width:90px">@foreach(['fedex' => 'FedEx', 'dhl' => 'DHL', 'ups' => 'UPS', 'other' => 'Otro'] as $k => $v)<option value="{{ $k }}" @selected($r->carrier === $k)>{{ $v }}</option>@endforeach</select></td>
        <td><input form="rate{{ $r->id }}" name="base_price" type="number" step="0.01" min="0" class="form-control" style="width:90px" value="{{ $r->base_price }}"></td>
        <td><input form="rate{{ $r->id }}" name="insurance_rate" type="number" step="0.001" min="0" class="form-control" style="width:80px" value="{{ $r->insurance_rate }}"></td>
        <td><input form="rate{{ $r->id }}" name="insurance_min" type="number" step="0.01" min="0" class="form-control" style="width:80px" value="{{ $r->insurance_min }}"></td>
        <td><input form="rate{{ $r->id }}" name="free_over" type="number" step="0.01" min="0" class="form-control" style="width:100px" value="{{ $r->free_over }}"></td>
        <td style="white-space:nowrap"><input form="rate{{ $r->id }}" name="min_value" type="number" step="0.01" min="0" class="form-control" style="width:90px;display:inline-block" value="{{ $r->min_value }}"> <input form="rate{{ $r->id }}" name="max_value" type="number" step="0.01" min="0" class="form-control" style="width:90px;display:inline-block" value="{{ $r->max_value }}"></td>
        <td><input form="rate{{ $r->id }}" name="delivery_estimate" class="form-control" style="width:100px" value="{{ $r->delivery_estimate }}"></td>
        <td><input form="rate{{ $r->id }}" type="checkbox" name="active" value="1" @checked($r->active)></td>
        <td style="white-space:nowrap"><button form="rate{{ $r->id }}" class="btn btn--primary btn--sm">✓</button>
          <form method="POST" action="{{ route('admin.shipping.rates.destroy', $r) }}" style="display:inline" onsubmit="return confirm('¿Eliminar tarifa?')">@csrf @method('DELETE')<button class="btn btn--ghost btn--sm">✕</button></form></td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  <details style="margin-top:12px"><summary class="btn btn--ghost btn--sm">+ Añadir tarifa</summary>
    <form method="POST" action="{{ route('admin.shipping.rates.store', $zone) }}" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:12px">@csrf
      <input name="name_es" class="form-control" placeholder="Nombre ES" required value="FedEx International Priority · asegurado">
      <input name="name_en" class="form-control" placeholder="Nombre EN" value="FedEx International Priority · fully insured">
      <select name="carrier" class="form-control"><option value="fedex">FedEx</option><option value="dhl">DHL</option><option value="ups">UPS</option><option value="other">Otro</option></select>
      <input name="delivery_estimate" class="form-control" placeholder="Plazo (24–72 h)">
      <input name="base_price" type="number" step="0.01" min="0" class="form-control" placeholder="Base €" required>
      <input name="insurance_rate" type="number" step="0.001" min="0" class="form-control" placeholder="Seguro %" required>
      <input name="insurance_min" type="number" step="0.01" min="0" class="form-control" placeholder="Seguro mín. €">
      <input name="free_over" type="number" step="0.01" min="0" class="form-control" placeholder="Gratis desde €">
      <input name="min_value" type="number" step="0.01" min="0" class="form-control" placeholder="Valor mín. €">
      <input name="max_value" type="number" step="0.01" min="0" class="form-control" placeholder="Valor máx. €">
      <label style="display:flex;gap:6px;align-items:center;font-size:12px"><input type="checkbox" name="active" value="1" checked> Activa</label>
      <button class="btn btn--primary btn--sm">Crear tarifa</button>
    </form>
  </details>
</div>
@endforeach

<div class="card">
  <div class="card__num">Nueva zona</div>
  <form method="POST" action="{{ route('admin.shipping.zones.store') }}" style="display:grid;grid-template-columns:1fr 2fr 2fr auto auto;gap:10px;align-items:center">@csrf
    <input name="name" class="form-control" placeholder="Nombre" required>
    <input name="notice_es" class="form-control" placeholder="Aviso ES (opcional)">
    <input name="notice_en" class="form-control" placeholder="Aviso EN (opcional)">
    <label style="display:flex;gap:6px;align-items:center;font-size:12px"><input type="checkbox" name="active" value="1" checked> Activa</label>
    <button class="btn btn--primary btn--sm">Crear</button>
  </form>
</div>
@endsection
