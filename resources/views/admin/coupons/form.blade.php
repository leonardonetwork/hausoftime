@extends('layouts.admin')
@section('title', $coupon->exists ? 'Editar cupón' : 'Nuevo cupón')
@section('admin-content')
<a href="{{ route('admin.coupons.index') }}" style="font-size:11px;color:var(--a-subtle);letter-spacing:.08em;text-transform:uppercase">← Cupones</a>
<div class="page-title" style="margin-top:12px">{{ $coupon->exists ? 'Editar ' . $coupon->code : 'Nuevo cupón' }}</div>
<p class="page-subtitle">El código se aplica en el checkout; el descuento se reparte entre las piezas a las que aplica</p>
<form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="card" style="max-width:760px">
  @csrf @if($coupon->exists) @method('PUT') @endif
  <div class="form-row">
    <div class="form-group"><label class="form-label">Código</label><input name="code" class="form-control" value="{{ old('code', $coupon->code) }}" required style="text-transform:uppercase;font-family:monospace;letter-spacing:.08em" placeholder="FOUNDERS10"></div>
    <div class="form-group"><label class="form-label">Descripción interna</label><input name="description" class="form-control" value="{{ old('description', $coupon->description) }}"></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Tipo</label>
      <select name="type" class="form-control"><option value="percent" @selected(old('type', $coupon->type) === 'percent')>Porcentaje (%)</option><option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Importe fijo (€)</option></select></div>
    <div class="form-group"><label class="form-label">Valor</label><input name="value" type="number" step="0.01" min="0" class="form-control" value="{{ old('value', $coupon->value) }}" required></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Pedido mínimo (€)</label><input name="min_subtotal" type="number" step="0.01" min="0" class="form-control" value="{{ old('min_subtotal', $coupon->min_subtotal) }}"></div>
    <div class="form-group"><label class="form-label">Descuento máximo (€)</label><input name="max_discount" type="number" step="0.01" min="0" class="form-control" value="{{ old('max_discount', $coupon->max_discount) }}"></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Válido desde</label><input name="starts_at" type="datetime-local" class="form-control" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"></div>
    <div class="form-group"><label class="form-label">Válido hasta</label><input name="ends_at" type="datetime-local" class="form-control" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i')) }}"></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Usos totales</label><input name="usage_limit" type="number" min="1" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Ilimitado"></div>
    <div class="form-group"><label class="form-label">Usos por cliente</label><input name="usage_limit_per_user" type="number" min="1" class="form-control" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user ?? 1) }}" placeholder="Ilimitado"></div>
  </div>
  <div class="form-group"><label class="form-label">Solo para estas marcas (vacío = todas)</label>
    <select name="brand_ids[]" class="form-control" multiple size="5" style="height:auto">
      @foreach($brands as $b)<option value="{{ $b->id }}" @selected(in_array($b->id, old('brand_ids', $coupon->brand_ids ?? [])))>{{ $b->name_es }}</option>@endforeach
    </select></div>
  <div style="display:flex;gap:24px;flex-wrap:wrap;margin:6px 0 22px;font-size:13px">
    <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="first_order_only" value="1" @checked(old('first_order_only', $coupon->first_order_only))> Solo primer pedido</label>
    <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="free_shipping" value="1" @checked(old('free_shipping', $coupon->free_shipping))> Incluye envío gratis</label>
    <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="active" value="1" @checked(old('active', $coupon->active ?? true))> Activo</label>
  </div>
  <button class="btn btn--primary btn--lg">Guardar cupón</button>
</form>
@endsection
