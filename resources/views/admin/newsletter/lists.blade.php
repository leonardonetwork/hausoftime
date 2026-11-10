@extends('layouts.admin')
@section('title', 'Listas Newsletter')

@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">Listas de suscripción</div>
    <p class="page-subtitle">Gestiona tus listas de suscriptores</p>
  </div>
  <a href="{{ route('admin.newsletter.index') }}" class="btn btn--ghost btn--sm">← Campañas</a>
</div>

@if(session('success'))
  <div class="alert alert--success">{{ session('success') }}</div>
@endif

{{-- Create form --}}
<div class="card" style="margin-bottom:28px;">
  <div class="card__num" style="margin-bottom:16px;">Nueva lista</div>
  <form method="POST" action="{{ route('admin.newsletter.lists.store') }}">
    @csrf
    @if($errors->any())
      <div class="alert alert--error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Nombre de la lista</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}"
               placeholder="ej: Evaluación, Early Access Sellers..." required>
      </div>
      <div class="form-group">
        <label class="form-label">Descripción (opcional)</label>
        <input type="text" name="description" class="form-control" value="{{ old('description') }}"
               placeholder="Para qué sirve esta lista">
      </div>
    </div>
    <button type="submit" class="btn btn--primary">+ Crear lista</button>
  </form>
</div>

{{-- Lists table --}}
<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Slug</th>
        <th>Descripción</th>
        <th>Activos</th>
        <th>Total</th>
        <th>Creada</th>
        <th>{{ __('messages.admin.actions') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($lists as $list)
      <tr>
        <td>
          <strong>{{ $list->name }}</strong>
        </td>
        <td>
          <code style="background:var(--a-bg3);padding:2px 8px;border-radius:3px;font-size:11px;color:var(--a-gold);">
            {{ $list->slug }}
          </code>
        </td>
        <td style="font-size:12px;color:var(--a-muted);">{{ $list->description ?? '—' }}</td>
        <td>
          <span style="font-weight:600;color:var(--a-gold);">{{ $list->active_count }}</span>
        </td>
        <td style="color:var(--a-muted);">{{ $list->total_count }}</td>
        <td style="font-size:11px;color:var(--a-subtle);">{{ $list->created_at->format('d/m/Y') }}</td>
        <td>
          <div style="display:flex;gap:6px;align-items:center;">
            <a href="{{ route('admin.newsletter.lists.subscribers', $list) }}"
               class="btn btn--ghost btn--sm">Ver iscritti</a>

            {{-- Edit --}}
            <button onclick="toggleEdit({{ $list->id }})"
                    class="btn btn--ghost btn--sm">Editar</button>

            {{-- Delete --}}
            <form method="POST" action="{{ route('admin.newsletter.lists.destroy', $list) }}">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn--ghost btn--sm"
                style="color:var(--a-danger);border-color:var(--a-danger);"
                onclick="return confirm('¿Eliminar lista {{ $list->name }}? Los suscriptores no se borrarán.')">
                ✕
              </button>
            </form>
          </div>

          {{-- Edit form (hidden) --}}
          <form id="edit-{{ $list->id }}" method="POST"
                action="{{ route('admin.newsletter.lists.update', $list) }}"
                style="display:none;margin-top:10px;">
            @csrf @method('PUT')
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <input type="text" name="name" class="form-control" value="{{ $list->name }}"
                     style="flex:1;min-width:160px;" required>
              <input type="text" name="description" class="form-control" value="{{ $list->description }}"
                     style="flex:1;min-width:200px;" placeholder="Descripción">
              <button type="submit" class="btn btn--primary btn--sm">Guardar</button>
              <button type="button" onclick="toggleEdit({{ $list->id }})"
                      class="btn btn--ghost btn--sm">Cancelar</button>
            </div>
          </form>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="text-align:center;padding:40px;color:var(--a-muted);">
          No hay listas todavía. Crea tu primera lista arriba.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Info box --}}
<div class="card" style="margin-top:24px;border-color:var(--a-border2);">
  <div style="font-size:11px;color:var(--a-muted);line-height:1.8;">
    <strong style="color:var(--a-text);display:block;margin-bottom:8px;">¿Cómo asignar una lista a un formulario?</strong>
    Una vez creada la lista, ve a <strong style="color:var(--a-gold);">Configuración → General</strong>
    e introduce el <strong>ID</strong> o <strong>slug</strong> de cada lista en los campos correspondientes
    (Evaluación, Early Access Buyer, Early Access Seller).
    Los nuevos suscriptores se asignarán automáticamente.
  </div>
</div>

<script>
function toggleEdit(id) {
  const form = document.getElementById('edit-' + id);
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
