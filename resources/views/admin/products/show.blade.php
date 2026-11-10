@extends('layouts.admin')
@section('title', $watch->brand . ' ' . $watch->model)

@section('admin-content')
<div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;">
  <a href="{{ route('admin.watches.index') }}" class="btn btn--ghost btn--sm">← Volver</a>
  <div>
    <div class="page-title">{{ $watch->brand }} {{ $watch->model }}</div>
    <p class="page-subtitle">Ref. {{ $watch->reference ?? '—' }} · Vendedor: {{ $watch->seller?->name }}</p>
  </div>
  @php $b = $watch->status_badge; @endphp
  <span class="badge badge-{{ str_replace('badge-','',$b['class']) }}" style="margin-left:auto;font-size:.85rem;padding:6px 16px;">{{ $b['label'] }}</span>
  <a href="{{ route('admin.watches.edit', $watch) }}" class="btn btn--primary btn--sm">
    ✎ Editar
  </a>
</div>

<div class="grid-2" style="gap:28px;align-items:start;">

  {{-- Left: watch details --}}
  <div>
    {{-- Images --}}
    @if(!empty($watch->images))
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:24px;">
      @foreach($watch->images as $img)
      <div style="aspect-ratio:1;border-radius:8px;overflow:hidden;border:1px solid var(--bg-border);">
        <img src="{{ asset('storage/'.$img) }}" style="width:100%;height:100%;object-fit:cover;">
      </div>
      @endforeach
    </div>
    @endif

    {{-- Info card --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="card__num" style="margin-bottom:16px;">Detalles del reloj</div>
      <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
        @php
          $bpLabels = ['none'=>'Sin caja ni papeles','box_only'=>'Solo caja','papers_only'=>'Solo papeles','full_set'=>'Caja y papeles completos'];
          $bpLabel  = $bpLabels[$watch->box_papers ?? 'none'] ?? ($watch->box_papers ?? '—');
        @endphp
        @foreach([
          ['Marca', $watch->brand],
          ['Modelo', $watch->model],
          ['Referencia', $watch->reference ?? '—'],
          ['Año', $watch->manufacture_year ?? '—'],
          ['Condición', $watch->condition_label],
          ['Categoría', $watch->category ?? '—'],
          ['Precio', $watch->formatted_price],
          ['Caja/Papeles', $bpLabel],
        ] as [$label, $val])
        <tr>
          <td style="padding:8px 0;color:var(--text-muted);width:140px;">{{ $label }}</td>
          <td style="padding:8px 0;color:var(--text-primary);font-weight:500;">{{ $val }}</td>
        </tr>
        @endforeach
      </table>
      @if($watch->description)
      <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border);">
        <div style="font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">Descripción</div>
        <p style="font-size:.9rem;color:var(--text-secondary);line-height:1.65;">{{ $watch->description }}</p>
      </div>
      @endif
    </div>

    {{-- Documentation --}}
    @if(!empty($watch->documentation_files))
    <div class="card">
      <div class="card__num" style="margin-bottom:12px;">Documentación</div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($watch->documentation_files as $doc)
        <a href="{{ asset('storage/'.$doc) }}" target="_blank"
           style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--bg-elevated);border:1px solid var(--bg-border);border-radius:6px;font-size:.85rem;color:var(--text-secondary);transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold-dark)'" onmouseout="this.style.borderColor='var(--bg-border)'">
          <span>📄</span> {{ basename($doc) }}
          <span style="margin-left:auto;font-size:.7rem;color:var(--text-muted);">Abrir →</span>
        </a>
        @endforeach
      </div>
    </div>
    @endif
  </div>

  {{-- Right: seller info + actions --}}
  <div>
    {{-- Seller info --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="card__num" style="margin-bottom:16px;">Vendedor</div>
      @if($watch->seller)
      <div style="display:flex;flex-direction:column;gap:8px;font-size:.875rem;">
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--text-muted);">Nombre</span>
          <span style="font-weight:500;">{{ $watch->seller->full_name }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--text-muted);">Email</span>
          <span>{{ $watch->seller->email }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--text-muted);">Teléfono</span>
          <span>{{ $watch->seller->phone_prefix }} {{ $watch->seller->phone ?? '—' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--text-muted);">Estado cuenta</span>
          <span class="badge badge-{{ $watch->seller->status === 'approved' ? 'success' : 'warning' }}">{{ ucfirst($watch->seller->status) }}</span>
        </div>
        @if($watch->seller->is_founding_seller)
          <div style="margin-top:8px;"><span class="badge badge-gold">⭐ Founding Seller</span></div>
        @endif
      </div>
      @endif
    </div>

    {{-- Verificación de posesión --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="card__num" style="margin-bottom:16px;">Verificación de posesión</div>

      @if($watch->verification_photo)
        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:6px;">Hora solicitada</p>
        <div style="font-size:2rem;font-weight:300;letter-spacing:.04em;margin-bottom:14px;">
          {{ $watch->verification_time ?? '—' }}
        </div>
        <a href="{{ asset('storage/'.$watch->verification_photo) }}" target="_blank" rel="noopener"
           style="display:block;border-radius:8px;overflow:hidden;border:1px solid var(--bg-border);margin-bottom:10px;">
          <img src="{{ asset('storage/'.$watch->verification_photo) }}" style="width:100%;display:block;">
        </a>
        <p style="font-size:.78rem;color:var(--text-muted);line-height:1.6;">
          Enviada el {{ $watch->verification_at?->format('d/m/Y H:i') ?? '—' }}.<br>
          Comprueba que la esfera marca la hora solicitada antes de aprobar.
        </p>
      @else
        <p style="font-size:.85rem;color:var(--warning,#d4a24a);margin-bottom:6px;">Pendiente de envío</p>
        <p style="font-size:.78rem;color:var(--text-muted);line-height:1.6;">
          Hora asignada: <strong>{{ $watch->verification_time ?? '—' }}</strong>.<br>
          El vendedor aún no ha subido la foto de verificación.
        </p>
      @endif
    </div>

    {{-- Actions --}}
    @if($watch->status === 'pending')
    <div class="card">
      <div class="card__num" style="margin-bottom:16px;">Decisión</div>

      {{-- Approve --}}
      <form method="POST" action="{{ route('admin.watches.approve', $watch) }}" style="margin-bottom:12px;">
        @csrf
        <button type="submit" class="btn btn--success" style="width:100%;"
          onclick="return confirm('¿Aprobar este reloj?')">
          ✓ {{ __('messages.admin.approve') }}
        </button>
      </form>

      {{-- Reject --}}
      <form method="POST" action="{{ route('admin.watches.reject', $watch) }}" id="rejectForm">
        @csrf
        <div class="form-group" style="margin-bottom:12px;">
          <label class="form-label">{{ __('messages.admin.reject_note') }}</label>
          <textarea name="admin_notes" class="form-control" rows="3" placeholder="Motivo del rechazo..."></textarea>
        </div>
        <button type="submit" class="btn btn--danger" style="width:100%;"
          onclick="return confirm('¿Rechazar este reloj?')">
          ✕ {{ __('messages.admin.reject') }}
        </button>
      </form>
    </div>
    @elseif($watch->status === 'approved')
    <div class="card">
      <div style="color:var(--success);font-weight:600;margin-bottom:8px;">✓ Aprobado</div>
      <p style="font-size:.85rem;color:var(--text-muted);">{{ $watch->approved_at?->format('d/m/Y H:i') }}</p>
    </div>
    @elseif($watch->status === 'rejected')
    <div class="card">
      <div style="color:var(--danger);font-weight:600;margin-bottom:8px;">✕ Rechazado</div>
      @if($watch->admin_notes)
        <p style="font-size:.85rem;color:var(--text-secondary);">{{ $watch->admin_notes }}</p>
      @endif
    </div>
    @endif

    {{-- Metadata --}}
    <div class="card" style="margin-top:20px;">
      <div class="card__num" style="margin-bottom:12px;">Información</div>
      <div style="display:flex;flex-direction:column;gap:6px;font-size:.82rem;color:var(--text-muted);">
        <div>Creado: <span style="color:var(--text-secondary);">{{ $watch->created_at->format('d/m/Y H:i') }}</span></div>
        <div>Actualizado: <span style="color:var(--text-secondary);">{{ $watch->updated_at->format('d/m/Y H:i') }}</span></div>
        <div>ID: <span style="color:var(--text-secondary);">#{{ $watch->id }}</span></div>
      </div>
    </div>
  </div>
</div>
@endsection
