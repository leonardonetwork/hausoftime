@extends('layouts.seller')
@section('title', __('messages.seller.my_watches'))

@section('seller-content')

<div class="panel-header" style="margin-bottom:28px;">
  <div>
    <h1 class="page-title">{{ __('messages.seller.my_watches') }}</h1>
    <p class="page-subtitle" style="margin-bottom:0;">
      {{ $watches->total() }} {{ app()->getLocale() === 'es' ? ($watches->total() === 1 ? 'reloj' : 'relojes') : ($watches->total() === 1 ? 'watch' : 'watches') }}
    </p>
  </div>
  <a href="{{ route('seller.watches.create') }}" class="btn btn--dark btn--lg">
    + {{ __('messages.seller.upload_watch') }}
  </a>
</div>

@if($watches->count())
<div class="table-wrap">
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:72px;">{{ app()->getLocale() === 'es' ? 'Foto' : 'Photo' }}</th>
          <th>{{ app()->getLocale() === 'es' ? 'Reloj' : 'Watch' }}</th>
          <th>Ref.</th>
          <th>{{ app()->getLocale() === 'es' ? 'Precio' : 'Price' }}</th>
          <th>{{ app()->getLocale() === 'es' ? 'Estado' : 'Status' }}</th>
          <th>{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }}</th>
          <th style="width:140px;"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($watches as $w)
        @php
          $imgs = is_array($w->images) ? $w->images : (json_decode($w->images, true) ?? []);
          $sc   = ['approved'=>'badge-success','rejected'=>'badge-danger','pending'=>'badge-warning','sold'=>'badge-gold','draft'=>'badge-secondary'];
          $label = __('messages.seller.status_' . $w->status);
          $needsVerif = $w->status === 'draft';
          if ($needsVerif) {
              $label = app()->getLocale() === 'es' ? 'Verificación pendiente' : 'Verification pending';
          }
        @endphp
        <tr>
          <td>
            @if(count($imgs))
              <img src="{{ Storage::url($imgs[0]) }}"
                   style="width:52px;height:52px;object-fit:cover;border-radius:2px;border:1px solid var(--s-border);">
            @else
              <div style="width:52px;height:52px;background:var(--s-bg3);border:1px solid var(--s-border);border-radius:2px;display:flex;align-items:center;justify-content:center;color:var(--s-subtle);font-size:18px;">⌚</div>
            @endif
          </td>
          <td class="td-strong">
            <div>{{ $w->brand }}</div>
            <div style="font-size:12px;color:var(--s-muted);font-weight:400;margin-top:1px;">{{ $w->model }}</div>
          </td>
          <td style="font-size:12px;">{{ $w->reference ?? '—' }}</td>
          <td style="white-space:nowrap;font-weight:500;color:var(--s-text);">{{ number_format($w->price, 0, ',', '.') }} €</td>
          <td><span class="badge {{ $needsVerif ? 'badge-gold' : ($sc[$w->status] ?? 'badge-secondary') }}">{{ $label }}</span></td>
          <td style="font-size:11px;">{{ $w->created_at->format('d/m/Y') }}</td>
          <td>
            <div style="display:flex;gap:6px;justify-content:flex-end;">
              @if($needsVerif)
              <a href="{{ route('seller.watches.verification', $w) }}" class="btn btn--dark btn--sm">
                {{ app()->getLocale() === 'es' ? 'Completar' : 'Complete' }}
              </a>
              @endif
              <a href="{{ route('seller.watches.show', $w) }}" class="btn btn--ghost btn--sm">
                {{ app()->getLocale() === 'es' ? 'Ver' : 'View' }}
              </a>
              <a href="{{ route('seller.watches.edit', $w) }}" class="btn btn--ghost btn--sm">
                {{ app()->getLocale() === 'es' ? 'Editar' : 'Edit' }}
              </a>
              <form method="POST" action="{{ route('seller.watches.destroy', $w) }}" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit"
                        onclick="return confirm('{{ app()->getLocale() === 'es' ? '¿Eliminar este reloj?' : 'Delete this watch?' }}')"
                        class="btn btn--ghost btn--sm" style="color:var(--s-danger);border-color:rgba(155,51,51,.25);">
                  ✕
                </button>
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div style="padding:16px 20px;border-top:1px solid var(--s-border);">
    {{ $watches->links() }}
  </div>
</div>

@else
<div class="table-wrap">
  <div class="empty-state">
    <div class="empty-state__icon">⌚</div>
    <p class="empty-state__text">{{ __('messages.seller.no_watches') }}</p>
    <a href="{{ route('seller.watches.create') }}" class="btn btn--dark btn--lg">
      + {{ __('messages.seller.upload_first') }}
    </a>
  </div>
</div>
@endif

@endsection
