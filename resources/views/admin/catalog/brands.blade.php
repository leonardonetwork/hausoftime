@extends('layouts.admin')
@section('title', 'Marcas')
@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">Marcas</div>
    <p class="page-subtitle">{{ $brands->count() }} marcas · traducciones EN automáticas via IA</p>
  </div>
  <a href="{{ route('admin.translations') }}" class="btn btn--ghost btn--sm">🌐 Traducciones</a>
</div>
@if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
<div class="card" style="margin-bottom:24px;">
  <div class="card__num" style="margin-bottom:16px;">Nueva marca</div>
  <form method="POST" action="{{ route('admin.brands.store') }}">
    @csrf
    @if($errors->any())<div class="alert alert--error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:2;min-width:180px;"><label class="form-label">Nombre (ES) *</label><input type="text" name="name_es" class="form-control" value="{{ old('name_es') }}" required placeholder="ej: Rolex"></div>
      <div style="flex:2;min-width:180px;"><label class="form-label">Nombre (EN) — auto si vacío</label><input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="auto-traducido"></div>
      <div style="flex:1;min-width:80px;"><label class="form-label">Orden</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',0) }}" min="0"></div>
      <button type="submit" class="btn btn--primary" style="margin-top:18px;">+ Añadir</button>
    </div>
  </form>
</div>
<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th style="width:50px">Ord.</th><th>Nombre ES</th><th>Nombre EN</th><th>Slug</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
      @forelse($brands as $b)
      <tr>
        <td style="color:var(--a-subtle)">{{ $b->sort_order }}</td>
        <td>@if($b->logo_url)<img src="{{ $b->logo_url }}" style="height:16px;background:#fff;padding:2px;display:inline-block;vertical-align:middle;margin-right:6px">@endif{{ $b->name_es }}</td>
        <td style="color:{{ $b->name_en ? 'var(--a-muted)' : 'var(--a-danger)' }}">{{ $b->name_en ?: '— sin tradución' }}</td>
        <td><code style="font-size:11px;background:var(--a-bg3);padding:2px 6px;border-radius:2px;color:var(--a-subtle)">{{ $b->slug }}</code></td>
        <td><span class="badge badge-{{ $b->active ? 'success':'secondary' }}">{{ $b->active ? 'Activa':'Inactiva' }}</span></td>
        <td>
          <div style="display:flex;gap:6px">
            <button onclick="toggleEdit('b{{ $b->id }}')" class="btn btn--ghost btn--sm">Editar</button>
            <form method="POST" action="{{ route('admin.brands.destroy',$b) }}">@csrf @method('DELETE')<button type="submit" class="btn btn--ghost btn--sm" style="color:var(--a-danger);border-color:var(--a-danger)" onclick="return confirm('¿Eliminar?')">✕</button></form>
          </div>
          <form id="b{{ $b->id }}" method="POST" action="{{ route('admin.brands.update',$b) }}" enctype="multipart/form-data" style="display:none;margin-top:8px">
            @csrf @method('PUT')
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
              <div style="flex:2;min-width:130px"><label class="form-label" style="font-size:9px">ES</label><input type="text" name="name_es" class="form-control" value="{{ $b->name_es }}" required></div>
              <div style="flex:2;min-width:130px"><label class="form-label" style="font-size:9px">EN</label><input type="text" name="name_en" class="form-control" value="{{ $b->name_en }}"></div>
              <div style="width:70px"><label class="form-label" style="font-size:9px">Orden</label><input type="number" name="sort_order" class="form-control" value="{{ $b->sort_order }}"></div>
              <div style="min-width:180px"><label class="form-label" style="font-size:9px">Logo tienda (PNG/WebP transparente)</label>
                @if($b->logo_url)<img src="{{ $b->logo_url }}" style="height:20px;background:#fff;padding:2px;margin-bottom:4px"><label style="font-size:10px;color:var(--a-subtle)"><input type="checkbox" name="remove_logo" value="1"> quitar</label>@endif
                <input type="file" name="logo" accept=".png,.webp" class="form-control" style="padding:5px"></div>
              <div><label class="form-label" style="font-size:9px">Activa</label><select name="active" class="form-control"><option value="1" {{ $b->active?'selected':'' }}>Sí</option><option value="0" {{ !$b->active?'selected':'' }}>No</option></select></div>
              <div style="display:flex;gap:6px;padding-top:18px"><button type="submit" class="btn btn--primary btn--sm">Guardar</button><button type="button" onclick="toggleEdit('b{{ $b->id }}')" class="btn btn--ghost btn--sm">✕</button></div>
            </div>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--a-muted)">Sin marcas. Añade la primera arriba.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<script>function toggleEdit(id){var f=document.getElementById(id);f.style.display=f.style.display==='none'?'block':'none';}</script>
@endsection
