@extends('layouts.admin')
@section('title','Páginas')
@section('admin-content')
<div class="panel-header">
  <div><div class="page-title">Páginas estáticas</div><p class="page-subtitle">{{ $pages->count() }} páginas</p></div>
  <a href="{{ route('admin.pages.create') }}" class="btn btn--primary">+ Nueva página</a>
</div>
@if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>Título ES</th><th>Título EN</th><th>Slug</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
      @forelse($pages as $p)
      <tr>
        <td>{{ $p->title_es }}</td>
        <td style="color:{{ $p->title_en?'var(--a-muted)':'var(--a-danger)' }}">{{ $p->title_en ?: '— sin traducción' }}</td>
        <td><code style="font-size:11px;background:var(--a-bg3);padding:2px 6px;border-radius:2px;color:var(--a-subtle)">/{{ $p->slug }}</code></td>
        <td><span class="badge badge-{{ $p->active?'success':'secondary' }}">{{ $p->active?'Activa':'Inactiva' }}</span></td>
        <td>
          <div style="display:flex;gap:6px">
            <a href="{{ route('admin.pages.edit',$p) }}" class="btn btn--primary btn--sm">Editar</a>
            <a href="{{ url('/'.$p->slug) }}" target="_blank" class="btn btn--ghost btn--sm">Ver</a>
            <form method="POST" action="{{ route('admin.pages.destroy',$p) }}">@csrf @method('DELETE')
              <button type="submit" class="btn btn--ghost btn--sm" style="color:var(--a-danger);border-color:var(--a-danger)" onclick="return confirm('¿Eliminar?')">✕</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--a-muted)">Sin páginas. Crea la primera.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
