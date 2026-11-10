@extends('layouts.admin')
@section('title', 'Iscritti — ' . $newsletterList->name)

@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">{{ $newsletterList->name }}</div>
    <p class="page-subtitle">
      {{ $subscribers->total() }} iscritti totali ·
      <code style="font-size:11px;background:var(--a-bg3);padding:1px 8px;border-radius:3px;color:var(--a-gold);">{{ $newsletterList->slug }}</code>
    </p>
  </div>
  <a href="{{ route('admin.newsletter.lists.index') }}" class="btn btn--ghost btn--sm">← Liste</a>
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
        <td style="color:var(--a-muted);">{{ $s->name ?? '—' }}</td>
        <td>
          <span class="badge badge-{{ $s->type === 'seller' ? 'gold' : ($s->type === 'buyer' ? 'info' : 'secondary') }}">
            {{ ucfirst($s->type) }}
          </span>
        </td>
        <td style="font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:var(--a-subtle);">{{ $s->locale }}</td>
        <td>
          @if($s->unsubscribed_at)
            <span class="badge badge-danger">Baja</span>
          @else
            <span class="badge badge-success">Activo</span>
          @endif
        </td>
        <td style="font-size:11px;color:var(--a-subtle);">{{ $s->subscribed_at?->format('d/m/Y H:i') ?? '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--a-muted);">Sin suscriptores en esta lista.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:16px;">{{ $subscribers->links() }}</div>
@endsection
