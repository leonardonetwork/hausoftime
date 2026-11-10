{{-- resources/views/admin/newsletter/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Newsletter')

@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">Newsletter</div>
    <p class="page-subtitle">{{ $subscribers }} suscriptores activos</p>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="{{ route('admin.newsletter.subscribers') }}" class="btn btn--ghost btn--sm">Lista suscriptores</a>
    <a href="{{ route('admin.newsletter.create') }}" class="btn btn--primary btn--sm">+ Nueva campaña</a>
  </div>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Destinatarios</th>
        <th>Estado</th>
        <th>Enviados</th>
        <th>Fecha</th>
        <th>{{ __('messages.admin.actions') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($campaigns as $c)
      <tr>
        <td>{{ $c->name }}<div style="font-size:.75rem;color:var(--text-muted);">{{ Str::limit($c->subject_es, 60) }}</div></td>
        <td style="font-size:.82rem;">
          @php $typeMap = ['all'=>'Todos','buyers'=>'Compradores','sellers'=>'Vendedores']; @endphp
          {{ $typeMap[$c->recipient_type] ?? $c->recipient_type }}
        </td>
        <td>
          @php $sc = ['draft'=>'secondary','scheduled'=>'info','sending'=>'warning','sent'=>'success']; @endphp
          <span class="badge badge-{{ $sc[$c->status] ?? 'secondary' }}">{{ ucfirst($c->status) }}</span>
        </td>
        <td style="font-size:.85rem;color:var(--text-muted);">
          {{ $c->sent_count }} / {{ $c->recipient_count }}
        </td>
        <td style="font-size:.8rem;color:var(--text-muted);">
          {{ $c->sent_at ? $c->sent_at->format('d/m/Y H:i') : ($c->created_at->format('d/m/Y')) }}
        </td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            @if($c->status !== 'sent')
              <a href="{{ route('admin.newsletter.edit', $c) }}" class="btn btn--ghost btn--sm">{{ __('messages.admin.edit') }}</a>
            @endif
            <a href="{{ route('admin.newsletter.preview', $c) }}" target="_blank" class="btn btn--ghost btn--sm">{{ __('messages.admin.preview') }}</a>
            @if(in_array($c->status, ['draft','scheduled']))
            <form method="POST" action="{{ route('admin.newsletter.send', $c) }}">
              @csrf
              <button type="submit" class="btn btn--primary btn--sm"
                onclick="return confirm('¿Enviar esta campaña a {{ $c->recipient_count ?: '?' }} personas?')">
                {{ __('messages.admin.send') }}
              </button>
            </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:40px;">Sin campañas todavía.<br>
        <a href="{{ route('admin.newsletter.create') }}" class="btn btn--primary btn--sm" style="margin-top:16px;">Crear primera campaña</a>
      </td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:20px;">{{ $campaigns->links() }}</div>
@endsection
