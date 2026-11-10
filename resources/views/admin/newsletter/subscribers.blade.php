@extends('layouts.admin')
@section('title', 'Suscriptores newsletter')

@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">Suscriptores newsletter</div>
    <p class="page-subtitle">{{ $subscribers->total() }} en total · {{ $subscribers->where('unsubscribed_at', null)->count() }} activos</p>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="{{ route('admin.newsletter.lists.index') }}" class="btn btn--primary btn--sm">◈ Listas</a>
    <a href="{{ route('admin.newsletter.index') }}" class="btn btn--ghost btn--sm">← Campañas</a>
  </div>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Email</th>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Idioma</th>
        <th>Estado</th>
        <th>Suscrito</th>
      </tr>
    </thead>
    <tbody>
      @forelse($subscribers as $s)
      <tr>
        <td>{{ $s->email }}</td>
        <td style="color:var(--text-secondary);">{{ $s->name ?? '—' }}</td>
        <td>
          <span class="badge badge-{{ $s->type === 'seller' ? 'gold' : ($s->type === 'buyer' ? 'info' : 'secondary') }}">
            {{ ucfirst($s->type) }}
          </span>
        </td>
        <td style="font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);">{{ $s->locale }}</td>
        <td>
          @if($s->unsubscribed_at)
            <span class="badge badge-danger">Baja</span>
          @else
            <span class="badge badge-success">Activo</span>
          @endif
        </td>
        <td style="font-size:.8rem;color:var(--text-muted);">{{ $s->subscribed_at?->format('d/m/Y') ?? '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:40px;">Sin suscriptores</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:20px;">{{ $subscribers->links() }}</div>
@endsection
