@extends('layouts.admin')
@section('title', 'Vendedores')
@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">Vendedores</div>
    <p class="page-subtitle">{{ $counts['all'] }} registrados</p>
  </div>
</div>
@if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif

<div class="tabs">
  <a href="{{ route('admin.sellers.index') }}" class="tab {{ !request('status') ? 'active':'' }}">Todos ({{ $counts['all'] }})</a>
  <a href="{{ route('admin.sellers.index') }}?status=pending"  class="tab {{ request('status')==='pending'  ? 'active':'' }}">Pendientes ({{ $counts['pending'] }})</a>
  <a href="{{ route('admin.sellers.index') }}?status=approved" class="tab {{ request('status')==='approved' ? 'active':'' }}">Aprobados ({{ $counts['approved'] }})</a>
  <a href="{{ route('admin.sellers.index') }}?status=rejected" class="tab {{ request('status')==='rejected' ? 'active':'' }}">Rechazados ({{ $counts['rejected'] }})</a>
</div>

<form method="GET" class="filter-bar" style="margin-bottom:20px;">
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
  <input type="text" name="search" class="form-control" placeholder="Buscar nombre o email..." value="{{ request('search') }}" style="width:280px;">
  <button type="submit" class="btn btn--ghost btn--sm">Buscar</button>
  @if(request('search'))<a href="{{ route('admin.sellers.index') }}{{ request('status') ? '?status='.request('status') : '' }}" class="btn btn--ghost btn--sm">✕</a>@endif
</form>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>País</th>
        <th>Founding</th>
        <th>Estado</th>
        <th>Registro</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($sellers as $s)
      <tr>
        <td>{{ $s->full_name ?: $s->name }}</td>
        <td style="font-size:12px;color:var(--a-muted)">{{ $s->email }}</td>
        <td style="font-size:12px;color:var(--a-muted)">{{ $s->phone_prefix }} {{ $s->phone ?? '—' }}</td>
        <td style="font-size:12px;color:var(--a-muted)">{{ $s->country ?? '—' }}</td>
        <td>@if($s->is_founding_seller)<span class="badge badge-gold">⭐ Founder</span>@else<span style="color:var(--a-subtle);font-size:11px;">—</span>@endif</td>
        <td>
          <span class="badge badge-{{ $s->status==='approved'?'success':($s->status==='rejected'?'danger':'warning') }}">
            {{ ucfirst($s->status) }}
          </span>
        </td>
        <td style="font-size:11px;color:var(--a-subtle)">{{ $s->created_at->format('d/m/Y') }}</td>
        <td>
          <div style="display:flex;gap:6px">
            @if($s->status !== 'approved')
            <form method="POST" action="{{ route('admin.sellers.approve',$s) }}">@csrf<button type="submit" class="btn btn--success btn--sm">✓</button></form>
            @endif
            @if($s->status !== 'rejected')
            <form method="POST" action="{{ route('admin.sellers.reject',$s) }}">@csrf<button type="submit" class="btn btn--ghost btn--sm" style="color:var(--a-danger);border-color:var(--a-danger)" onclick="return confirm('¿Rechazar?')">✕</button></form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--a-muted)">Sin vendedores.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:16px">{{ $sellers->withQueryString()->links() }}</div>
@endsection
