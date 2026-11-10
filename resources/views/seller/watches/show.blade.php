@extends('layouts.seller')
@section('title', $watch->brand . ' ' . $watch->model)

@section('seller-content')

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
  <div style="display:flex;align-items:center;gap:14px;">
    <a href="{{ route('seller.watches') }}" class="btn btn--ghost btn--sm">←</a>
    <div>
      <h1 class="page-title" style="margin-bottom:2px;">{{ $watch->brand }} {{ $watch->model }}</h1>
      <p class="page-subtitle" style="margin-bottom:0;">
        {{ $watch->reference ? 'Ref. '.$watch->reference.' · ' : '' }}
        {{ app()->getLocale() === 'es' ? 'Publicado' : 'Listed' }} {{ $watch->created_at->format('d/m/Y') }}
      </p>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px;">
    @php $sc = ['approved'=>'badge-success','rejected'=>'badge-danger','pending'=>'badge-warning','sold'=>'badge-gold','draft'=>'badge-secondary']; @endphp
    <span class="badge {{ $sc[$watch->status] ?? 'badge-secondary' }}" style="font-size:10px;padding:4px 12px;">
      {{ __('messages.seller.status_' . $watch->status) }}
    </span>
    <a href="{{ route('seller.watches.edit', $watch) }}" class="btn btn--dark btn--sm">
      ✎ {{ app()->getLocale() === 'es' ? 'Editar' : 'Edit' }}
    </a>
  </div>
</div>

{{-- Alerts --}}
@if($watch->admin_notes && $watch->status === 'rejected')
<div class="alert alert--error" style="margin-bottom:20px;">
  <strong>{{ app()->getLocale() === 'es' ? 'Motivo del rechazo:' : 'Rejection reason:' }}</strong>
  {{ $watch->admin_notes }}
</div>
@endif

<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:20px;align-items:start;">

  {{-- LEFT: images + docs --}}
  <div>
    {{-- Images --}}
    @php $imgs = is_array($watch->images) ? $watch->images : (json_decode($watch->images ?? '[]', true) ?? []); @endphp
    @if(count($imgs))
    <div class="card" style="margin-bottom:16px;padding:16px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        @foreach($imgs as $i => $img)
          <div style="{{ $i === 0 ? 'grid-column:1/-1;aspect-ratio:16/9;' : 'aspect-ratio:1;' }}border-radius:3px;overflow:hidden;border:1px solid var(--s-border);">
            <img src="{{ Storage::url($img) }}" style="width:100%;height:100%;object-fit:cover;">
          </div>
        @endforeach
      </div>
    </div>
    @else
    <div class="card" style="text-align:center;padding:48px 24px;margin-bottom:16px;">
      <div style="font-size:2.5rem;opacity:.3;margin-bottom:8px;">⌚</div>
      <p style="font-size:13px;color:var(--s-subtle);">{{ app()->getLocale() === 'es' ? 'Sin fotografías' : 'No photos' }}</p>
    </div>
    @endif

    {{-- Docs --}}
    @php $docs = is_array($watch->documentation_files) ? $watch->documentation_files : (json_decode($watch->documentation_files ?? '[]', true) ?? []); @endphp
    @if(count($docs))
    <div class="card">
      <div class="table-title" style="margin-bottom:14px;">{{ __('messages.seller.doc_files') }}</div>
      @foreach($docs as $doc)
        <a href="{{ Storage::url($doc) }}" target="_blank"
           style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--s-bg3);border:1px solid var(--s-border);border-radius:3px;font-size:12px;color:var(--s-muted);margin-bottom:6px;text-decoration:none;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--s-gold)'" onmouseout="this.style.borderColor='var(--s-border)'">
          📄 {{ basename($doc) }}
          <span style="margin-left:auto;font-size:10px;color:var(--s-subtle);">{{ app()->getLocale() === 'es' ? 'Abrir' : 'Open' }} →</span>
        </a>
      @endforeach
    </div>
    @endif
  </div>

  {{-- RIGHT: details --}}
  <div>
    <div class="card" style="margin-bottom:16px;">
      <div class="table-title" style="margin-bottom:16px;">{{ app()->getLocale() === 'es' ? 'Detalles' : 'Details' }}</div>
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        @php
          $bpLabels = app()->getLocale()==='en'
            ? ['none'=>'No box or papers','box_only'=>'Box only','papers_only'=>'Papers only','full_set'=>'Full set (box + papers)']
            : ['none'=>'Sin caja ni papeles','box_only'=>'Solo caja','papers_only'=>'Solo papeles','full_set'=>'Caja y papeles completos'];

          $rows = [
            [__('messages.seller.brand'),     $watch->brand],
            [__('messages.seller.model'),     $watch->model],
            [__('messages.seller.reference'), $watch->reference ?? '—'],
            [__('messages.seller.year'),      $watch->manufacture_year ?? '—'],
            [__('messages.seller.condition'), $watch->condition_label],
            [__('messages.seller.category'),  $watch->category ?? '—'],
            [__('messages.seller.price_sale'),$watch->formatted_price],
          ];
          if ($watch->price_discounted)
            $rows[] = [__('messages.seller.price_discounted'), number_format($watch->price_discounted,0,',','.').' €'];
          $rows[] = [__('messages.seller.box_papers'), $bpLabels[$watch->box_papers ?? 'none'] ?? ($watch->box_papers ?? '—')];
        @endphp
        @foreach($rows as [$label, $val])
        <tr>
          <td style="padding:8px 0;color:var(--s-subtle);width:130px;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;border-bottom:1px solid var(--s-border2);">{{ $label }}</td>
          <td style="padding:8px 0;color:var(--s-text);font-weight:500;border-bottom:1px solid var(--s-border2);">{{ $val }}</td>
        </tr>
        @endforeach
      </table>

      @if($watch->description)
      <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--s-border);">
        <p style="font-size:13px;color:var(--s-muted);line-height:1.7;">{{ $watch->description }}</p>
      </div>
      @endif
    </div>

    {{-- Timestamps --}}
    <div class="card">
      <div style="display:flex;flex-direction:column;gap:6px;font-size:11px;color:var(--s-subtle);">
        <div>{{ app()->getLocale() === 'es' ? 'Publicado:' : 'Listed:' }}
          <span style="color:var(--s-muted);">{{ $watch->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div>{{ app()->getLocale() === 'es' ? 'Actualizado:' : 'Updated:' }}
          <span style="color:var(--s-muted);">{{ $watch->updated_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($watch->approved_at)
        <div>{{ app()->getLocale() === 'es' ? 'Aprobado:' : 'Approved:' }}
          <span style="color:var(--s-success);">{{ $watch->approved_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
      </div>
    </div>
  </div>

</div>

@push('styles')
<style>
@media(max-width:768px){
  div[style*="grid-template-columns:1.3fr"]{grid-template-columns:1fr!important;}
}
</style>
@endpush

@endsection
