@extends('layouts.admin')
@section('title', 'Países')
@section('admin-content')
<div class="page-title">Países habilitados</div>
<p class="page-subtitle">{{ $enabled }} países con envío activo. Solo los países habilitados y con zona activa aparecen en el checkout.</p>

<div class="panel-header">
  <form class="filter-bar" method="GET">
    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar país o código">
    <select name="estado" class="form-control"><option value="">Todos</option><option value="activos" @selected(request('estado') === 'activos')>Habilitados</option><option value="inactivos" @selected(request('estado') === 'inactivos')>Deshabilitados</option></select>
    <select name="zona" class="form-control"><option value="">Todas las zonas</option>@foreach($zones as $z)<option value="{{ $z->id }}" @selected(request('zona') == $z->id)>{{ $z->name }}</option>@endforeach</select>
    <button class="btn btn--ghost">Filtrar</button>
  </form>
</div>

<form method="POST" action="{{ route('admin.countries.bulk') }}" id="bulkForm">@csrf
  <div class="card" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;padding:14px 18px">
    <span style="font-size:12px;color:var(--a-muted)">Con los seleccionados:</span>
    <button name="action" value="enable" class="btn btn--success btn--sm">Habilitar</button>
    <button name="action" value="disable" class="btn btn--ghost btn--sm">Deshabilitar</button>
    <select name="shipping_zone_id" class="form-control" style="width:auto"><option value="">— zona —</option>@foreach($zones as $z)<option value="{{ $z->id }}">{{ $z->name }}</option>@endforeach</select>
    <button name="action" value="zone" class="btn btn--ghost btn--sm">Mover a zona</button>
  </div>
</form>

<div class="table-wrap"><table class="data-table">
  <thead><tr><th style="width:30px"><input type="checkbox" onclick="document.querySelectorAll('.cc').forEach(c=>c.checked=this.checked)"></th><th>País</th><th>Código</th><th>Zona</th><th>Envío habilitado</th></tr></thead>
  <tbody>
  @foreach($countries as $c)
    <tr>
      <td><input type="checkbox" class="cc" name="ids[]" value="{{ $c->id }}" form="bulkForm"></td>
      <td>{{ $c->name_es }} <span style="color:var(--a-subtle);font-size:11px">{{ $c->name_en }}</span></td>
      <td style="font-family:monospace">{{ $c->code }}</td>
      <td>
        <select class="form-control" style="width:auto" data-country="{{ route('admin.countries.update', $c) }}" data-field="zone">
          <option value="">—</option>@foreach($zones as $z)<option value="{{ $z->id }}" @selected($c->shipping_zone_id === $z->id)>{{ $z->name }}</option>@endforeach
        </select>
      </td>
      <td><label class="switch"><input type="checkbox" data-country="{{ route('admin.countries.update', $c) }}" data-field="enabled" @checked($c->enabled)><span></span></label></td>
    </tr>
  @endforeach
  </tbody>
</table></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-country]').forEach(function (el) {
  el.addEventListener('change', function () {
    var row = el.closest('tr');
    var zone = row.querySelector('[data-field=zone]').value, enabled = row.querySelector('[data-field=enabled]').checked;
    fetch(el.dataset.country, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
      body: JSON.stringify({ shipping_zone_id: zone || null, enabled: enabled }) })
      .then(function (r) { row.style.transition = 'background .6s'; row.style.background = r.ok ? 'rgba(61,138,94,.15)' : 'rgba(155,51,51,.2)'; setTimeout(function(){ row.style.background=''; }, 700); });
  });
});
</script>
@endpush
