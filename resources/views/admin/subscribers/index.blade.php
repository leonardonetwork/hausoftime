@extends('layouts.admin')
@section('title', __('messages.admin.subscribers'))

@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">{{ __('messages.admin.subscribers') }}</div>
    <p class="page-subtitle">Solicitudes de acceso anticipado</p>
  </div>
  <a href="{{ route('admin.newsletter.subscribers') }}" class="btn btn--ghost btn--sm">✉ Lista newsletter</a>
</div>

{{-- Tabs --}}
<div class="tabs">
  <a href="{{ route('admin.subscribers.index') }}" class="tab {{ !request('type') ? 'active' : '' }}">
    {{ __('messages.admin.filter_all') }} ({{ $counts['all'] }})
  </a>
  <a href="{{ route('admin.subscribers.index') }}?type=seller" class="tab {{ request('type') === 'seller' ? 'active' : '' }}">
    {{ __('messages.admin.filter_sellers') }} ({{ $counts['sellers'] }})
  </a>
  <a href="{{ route('admin.subscribers.index') }}?type=buyer" class="tab {{ request('type') === 'buyer' ? 'active' : '' }}">
    {{ __('messages.admin.filter_buyers') }} ({{ $counts['buyers'] }})
  </a>
</div>

{{-- Search + status filter --}}
<form method="GET" class="filter-bar" style="margin-bottom:20px;">
  @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
  <input type="text" name="search" class="form-control" placeholder="{{ __('messages.admin.search') }}"
         value="{{ request('search') }}" style="width:240px;">
  <select name="status" class="form-control form-control--select" style="width:160px;" onchange="this.form.submit()">
    <option value="">Todos los estados</option>
    <option value="pending"  {{ request('status') === 'pending'  ? 'selected':'' }}>Pendiente</option>
    <option value="approved" {{ request('status') === 'approved' ? 'selected':'' }}>Aprobado</option>
    <option value="rejected" {{ request('status') === 'rejected' ? 'selected':'' }}>Rechazado</option>
  </select>
  <button type="submit" class="btn btn--ghost btn--sm">Filtrar</button>
  @if(request()->hasAny(['search','status']))
    <a href="{{ route('admin.subscribers.index') }}{{ request('type') ? '?type='.request('type') : '' }}" class="btn btn--ghost btn--sm">✕</a>
  @endif
</form>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Tipo</th>
        <th>País</th>
        <th>Inventario</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>{{ __('messages.admin.actions') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($requests as $r)
      <tr>
        <td>{{ $r->full_name }}</td>
        <td style="font-size:.85rem;">{{ $r->email }}<br>
          @if($r->phone)<span style="color:var(--text-muted);font-size:.75rem;">{{ $r->phone_prefix }} {{ $r->phone }}</span>@endif
        </td>
        <td>
          <span class="badge badge-{{ $r->type === 'seller' ? 'gold' : 'info' }}">
            {{ $r->type === 'seller' ? 'Seller' : 'Buyer' }}
          </span>
        </td>
        <td style="font-size:.82rem;color:var(--text-muted);">{{ $r->country ?? '—' }}</td>
        <td style="font-size:.8rem;color:var(--text-muted);">
          @if($r->type === 'seller')
            {{ $r->watch_count ? $r->watch_count.' relojes' : '—' }}<br>
            {{ $r->estimated_value ?? '' }}
          @else —
          @endif
        </td>
        <td>
          <span class="badge badge-{{ $r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'warning') }}">
            {{ ucfirst($r->status) }}
          </span>
        </td>
        <td style="font-size:.8rem;color:var(--text-muted);">{{ $r->created_at->format('d/m/Y') }}</td>
        <td>
          @if($r->user_id)
            <a href="#" class="btn btn--ghost btn--sm" style="font-size:.72rem;">Ver cuenta</a>
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:40px;">Sin solicitudes</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:20px;">{{ $requests->withQueryString()->links() }}</div>
@endsection
