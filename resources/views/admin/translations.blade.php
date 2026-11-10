@extends('layouts.admin')
@section('title', 'Traducciones')
@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">Traducciones</div>
    <p class="page-subtitle">Gestión de traducciones de Marcas y Categorías vía IA</p>
  </div>
</div>
@if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif

{{-- AI Actions --}}
<div class="card" style="margin-bottom:24px;">
  <div class="card__num" style="margin-bottom:16px;">Acciones con IA</div>
  <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">

    {{-- Translate all missing --}}
    <form method="POST" action="{{ route('admin.translations.translate-all') }}">
      @csrf
      <label class="form-label">Traducir valores faltantes a</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <select name="locale" class="form-control" style="width:140px;">
          @foreach($languages as $code => $name)
            <option value="{{ $code }}">{{ $name }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn--primary"
          onclick="return confirm('¿Traducir automáticamente todos los valores faltantes?')">
          🤖 Traducir faltantes
        </button>
      </div>
    </form>

    {{-- Clear translations --}}
    <form method="POST" action="{{ route('admin.translations.clear') }}">
      @csrf
      <label class="form-label">Eliminar traducciones de</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <select name="locale" class="form-control" style="width:140px;">
          @foreach($languages as $code => $name)
            <option value="{{ $code }}">{{ $name }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn--ghost btn--sm" style="color:var(--a-danger);border-color:var(--a-danger);"
          onclick="return confirm('¿Eliminar TODAS las traducciones para este idioma? Esta acción no se puede deshacer.')">
          🗑 Borrar traducciones
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Brands table --}}
<div style="margin-bottom:8px;font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--a-muted);">Marcas</div>
<div class="table-wrap" style="margin-bottom:28px;">
  <table class="data-table">
    <thead><tr><th>Español (ES)</th><th>English (EN)</th><th>Estado</th></tr></thead>
    <tbody>
      @forelse($brands as $b)
      <tr>
        <td>{{ $b->name_es }}</td>
        <td style="color:{{ $b->name_en ? 'var(--a-muted)':'var(--a-danger)' }}">{{ $b->name_en ?: '— sin traducción' }}</td>
        <td>
          @if($b->name_en)<span class="badge badge-success">✓ Completa</span>
          @else<span class="badge badge-warning">⚠ Falta EN</span>@endif
        </td>
      </tr>
      @empty
      <tr><td colspan="3" style="text-align:center;padding:24px;color:var(--a-muted)">Sin marcas.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Categories table --}}
<div style="margin-bottom:8px;font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--a-muted);">Categorías</div>
<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>Español (ES)</th><th>English (EN)</th><th>Estado</th></tr></thead>
    <tbody>
      @forelse($categories as $c)
      <tr>
        <td>{{ $c->name_es }}</td>
        <td style="color:{{ $c->name_en ? 'var(--a-muted)':'var(--a-danger)' }}">{{ $c->name_en ?: '— sin traducción' }}</td>
        <td>
          @if($c->name_en)<span class="badge badge-success">✓ Completa</span>
          @else<span class="badge badge-warning">⚠ Falta EN</span>@endif
        </td>
      </tr>
      @empty
      <tr><td colspan="3" style="text-align:center;padding:24px;color:var(--a-muted)">Sin categorías.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
