{{-- Frammento per il popup di revisione rapida (admin/products/index) --}}
@php
  $imgs = $watch->image_urls;
  $verif = $watch->verification_photo ? asset('storage/' . $watch->verification_photo) : null;
  [$vh, $vm] = $watch->verification_time ? array_map('intval', explode(':', $watch->verification_time)) : [null, null];
  $hAng = $vh !== null ? (($vh % 12) + $vm / 60) * 30 : 0;
  $mAng = $vm !== null ? $vm * 6 : 0;
  $seller = $watch->seller;
  $facts = array_filter([
    'Referencia'    => $watch->reference,
    'Año'           => $watch->manufacture_year,
    'Estado'        => $watch->condition_label,
    'Documentación' => $watch->box_papers_label,
    'Categoría'     => $watch->category,
    'Caja'          => $watch->case_size,
    'Material'      => $watch->material,
    'Ubicación'     => $watch->location_label,
  ]);
@endphp
<div class="rv" data-id="{{ $watch->id }}" data-next="{{ $next }}" data-status="{{ $watch->status }}"
     data-approve="{{ route('admin.watches.approve', $watch) }}" data-reject="{{ route('admin.watches.reject', $watch) }}">

  {{-- Colonna sinistra: verifica di possesso --}}
  <section class="rv__verify">
    <div class="rv__label">Verificación de posesión</div>
    <div class="rv__stage" id="rvStage">
      @if($verif)
        <img src="{{ $verif }}" alt="Foto de verificación" id="rvMain" data-verif="{{ $verif }}">
      @else
        <div class="rv__empty">El vendedor aún no ha subido la foto de verificación</div>
      @endif
      @if($watch->verification_time)
        <div class="rv__time" title="Hora que debe marcar el reloj en la foto">
          <svg viewBox="0 0 100 100" aria-hidden="true">
            <circle cx="50" cy="50" r="46" fill="none" stroke="currentColor" stroke-width="2.5"/>
            @for($i = 0; $i < 12; $i++)
              @php $a = deg2rad($i * 30); @endphp
              <line x1="{{ round(50 + 38 * sin($a), 2) }}" y1="{{ round(50 - 38 * cos($a), 2) }}" x2="{{ round(50 + 43 * sin($a), 2) }}" y2="{{ round(50 - 43 * cos($a), 2) }}" stroke="currentColor" stroke-width="{{ $i % 3 ? 2 : 3.5 }}"/>
            @endfor
            <line x1="50" y1="50" x2="{{ round(50 + 24 * sin(deg2rad($hAng)), 2) }}" y2="{{ round(50 - 24 * cos(deg2rad($hAng)), 2) }}" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
            <line x1="50" y1="50" x2="{{ round(50 + 36 * sin(deg2rad($mAng)), 2) }}" y2="{{ round(50 - 36 * cos(deg2rad($mAng)), 2) }}" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            <circle cx="50" cy="50" r="3.5" fill="currentColor"/>
          </svg>
          <div><small>Hora solicitada</small><strong>{{ $watch->verification_time }}</strong>
            @if($watch->verification_at)<em>Foto subida {{ $watch->verification_at->format('d/m/Y H:i') }}</em>@endif</div>
        </div>
      @endif
    </div>
    @if(count($imgs) || $verif)
      <div class="rv__thumbs" id="rvThumbs">
        @if($verif)<button type="button" class="on is-verif" data-src="{{ $verif }}" title="Foto de verificación"><img src="{{ $verif }}" alt=""><span>Verif.</span></button>@endif
        @foreach($imgs as $u)<button type="button" data-src="{{ $u }}"><img src="{{ $u }}" alt="" loading="lazy"></button>@endforeach
      </div>
    @endif
  </section>

  {{-- Colonna destra: dati e decisione --}}
  <section class="rv__info">
    <div class="rv__head">
      <span class="badge {{ $watch->status_badge['class'] }}" id="rvBadge">{{ $watch->status_badge['label'] }}</span>
      @if($watch->is_certified)<span class="badge badge-gold">Certificado</span>@endif
    </div>
    <div class="rv__brand">{{ $watch->brand }}</div>
    <h2 class="rv__model">{{ $watch->model }}</h2>
    <div class="rv__price">
      @if($watch->price_discounted && $watch->price_discounted < $watch->price)<s>{{ \App\Models\Watch::money($watch->price, true) }}</s>@endif
      {{ \App\Models\Watch::money($watch->effective_price, true) }}
    </div>

    <dl class="rv__facts">
      @foreach($facts as $k => $v)<div><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>@endforeach
    </dl>

    @if($watch->description)<p class="rv__desc">{{ \Illuminate\Support\Str::limit($watch->description, 420) }}</p>@endif

    @if($seller)
      <div class="rv__seller">
        <div class="rv__avatar">{{ mb_strtoupper(mb_substr($seller->name, 0, 1)) }}</div>
        <div><strong>{{ $seller->name }} @if($seller->is_founding_seller)<span class="badge badge-gold" style="margin-left:4px">Founder</span>@endif</strong>
          <span>{{ $seller->email }} · {{ $seller->watches()->where('status', 'approved')->count() }} aprobados</span></div>
      </div>
    @endif

    @if($watch->status === 'rejected' && $watch->admin_notes)
      <div class="rv__note">Motivo del rechazo: {{ $watch->admin_notes }}</div>
    @endif

    <div class="rv__reject" id="rvReject" hidden>
      <label class="form-label" for="rvReason">Motivo (se envía al vendedor)</label>
      <div class="rv__chips">
        @foreach(['La hora del reloj no coincide con la solicitada', 'La foto de verificación no es legible', 'Faltan fotos del reloj', 'El precio no es coherente con el mercado', 'Datos del reloj incompletos'] as $r)
          <button type="button" data-reason="{{ $r }}">{{ $r }}</button>
        @endforeach
      </div>
      <textarea id="rvReason" class="form-control" rows="2" maxlength="1000" placeholder="Escribe o elige un motivo…"></textarea>
      <div class="rv__row">
        <button type="button" class="btn btn--ghost btn--sm" data-act="cancel-reject">Cancelar</button>
        <button type="button" class="btn btn--danger" data-act="confirm-reject">Confirmar rechazo</button>
      </div>
    </div>

    <div class="rv__actions" id="rvActions">
      <button type="button" class="btn btn--success rv__go" data-act="approve" @disabled($watch->status === 'approved' || $watch->status === 'sold')>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg> Aprobar <kbd>A</kbd></button>
      <button type="button" class="btn btn--ghost rv__no" data-act="reject" @disabled($watch->status === 'rejected' || $watch->status === 'sold')>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 6l12 12M18 6L6 18"/></svg> Rechazar <kbd>R</kbd></button>
    </div>
    <div class="rv__foot">
      <a href="{{ route('admin.watches.show', $watch) }}">Ficha completa</a>
      <a href="{{ route('admin.watches.edit', $watch) }}">Editar</a>
      @if($next)<button type="button" data-act="next">Siguiente pendiente <kbd>→</kbd></button>@endif
    </div>
  </section>
</div>
