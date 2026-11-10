@extends('layouts.seller')
@section('title', isset($watch) ? (app()->getLocale()==='es' ? 'Editar reloj' : 'Edit watch') : __('messages.seller.watch_form_title'))

@php
  $isEdit = isset($watch);
  $es     = app()->getLocale() === 'es';
  $TOTAL  = 5;

  /* Commissione: arriva dal controller (Settings). Fallback prudente se il patch
     del controller non e' stato applicato, cosi' la vista non si rompe mai. */
  $commRate  = $commissionRate  ?? (auth()->user()->is_founding_seller ? 4 : 7);
  $commLabel = $commissionLabel ?? (auth()->user()->is_founding_seller
                  ? ($es ? 'Fundador' : 'Founding')
                  : ($es ? 'Estándar' : 'Standard'));

  /* Media gia' caricati (solo edit) */
  $existImgs = [];
  $existDocs = [];
  if ($isEdit) {
      $existImgs = is_array($watch->images) ? $watch->images : (json_decode($watch->images ?? '[]', true) ?: []);
      $existDocs = is_array($watch->documentation_files) ? $watch->documentation_files : (json_decode($watch->documentation_files ?? '[]', true) ?: []);
  }

  /* Se il server rimanda errori, apriamo il wizard sul primo passo che li contiene.
     In creazione si parte dal passo 1; in modifica dal riepilogo (passo 5). */
  $stepFields = [
      1 => ['brand', 'model', 'reference', 'manufacture_year', 'category'],
      2 => ['condition', 'description'],
      3 => ['box_papers', 'documentation_files', 'documentation_files.*'],
      4 => ['images', 'images.*'],
      5 => ['price', 'price_discounted'],
  ];
  $startStep = $isEdit ? 5 : 1;
  foreach ($stepFields as $sIdx => $sFields) {
      foreach ($sFields as $sf) {
          if ($errors->has($sf)) { $startStep = $sIdx; break 2; }
      }
  }

  $savedBrand  = old('brand', $watch->brand ?? '');
  $brandInList = $brands->contains(fn($b) => $b->name_es === $savedBrand);
  $curCond     = old('condition', $watch->condition ?? '');
  $curBox      = old('box_papers', $watch->box_papers ?? 'none');
  $curCat      = old('category', $watch->category ?? '');

  /* Etichetta + descrizione: diventano di fatto lo standard di valutazione della piattaforma */
  $conditions = $es ? [
      'mint'      => ['Nunca usado', 'Sin señales de uso, protecciones originales intactas'],
      'excellent' => ['Excelente',   'Micromarcas mínimas, sólo visibles de cerca'],
      'very_good' => ['Muy bueno',   'Señales de uso leves y homogéneas'],
      'good'      => ['Bueno',       'Marcas visibles, mecánica en perfecto estado'],
      'fair'      => ['Aceptable',   'Uso evidente o piezas que requieren servicio'],
  ] : [
      'mint'      => ['Unworn',    'No signs of wear, original protective films intact'],
      'excellent' => ['Excellent', 'Minimal micro-scratches, only visible up close'],
      'very_good' => ['Very good', 'Light, even signs of wear'],
      'good'      => ['Good',      'Visible marks, movement in perfect working order'],
      'fair'      => ['Fair',      'Evident wear or parts in need of service'],
  ];

  $boxOpts = $es ? [
      'none'        => ['Sin caja ni papeles', 'Sólo el reloj'],
      'box_only'    => ['Solo caja',           'Estuche original, sin documentación'],
      'papers_only' => ['Solo papeles',        'Certificado o factura, sin estuche'],
      'full_set'    => ['Caja y papeles',      'Estuche, certificado y documentación completa'],
  ] : [
      'none'        => ['No box or papers', 'The watch only'],
      'box_only'    => ['Box only',         'Original case, no documentation'],
      'papers_only' => ['Papers only',      'Certificate or invoice, no case'],
      'full_set'    => ['Full set',         'Case, certificate and complete documentation'],
  ];

  $photoHints = $es
      ? ['Frontal','Perfil','Trasera','Cierre','Caja','Papeles']
      : ['Front','Profile','Back','Clasp','Box','Papers'];

  $stepShort = $es
      ? [1=>'Reloj', 2=>'Estado', 3=>'Caja', 4=>'Fotos', 5=>'Precio']
      : [1=>'Watch', 2=>'Condition', 3=>'Box', 4=>'Photos', 5=>'Price'];

  $stepIntro = $es ? [
      1 => ['¿Qué reloj vas a vender?',      'Empieza por la referencia: identifica la pieza de forma inequívoca.'],
      2 => ['¿En qué estado está el reloj?', 'Sé honesto: una descripción precisa acelera la venta y evita devoluciones.'],
      3 => ['¿Qué incluye la venta?',        'El estuche y la documentación original pueden aumentar el valor de forma notable.'],
      4 => ['Muestra el reloj',              'Las fotos son lo primero que mira un comprador. La primera será la portada.'],
      5 => ['Precio y publicación',          'Revisa la ficha. Después te pediremos una foto de verificación.'],
  ] : [
      1 => ['Which watch are you selling?', 'Start with the reference: it identifies the piece unambiguously.'],
      2 => ['What condition is it in?',     'Be honest: an accurate description speeds up the sale and prevents returns.'],
      3 => ['What does the sale include?',  'The original case and papers can raise the value significantly.'],
      4 => ['Show the watch',               'Photos are the first thing a buyer looks at. The first one is the cover.'],
      5 => ['Price and publication',        'Review the listing. Next we will ask for a verification photo.'],
  ];
@endphp

@section('seller-content')

<div class="wz-shell">

{{-- ══════════════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════════════ --}}
<div class="wz-top">
  <div>
    <h1 class="page-title" style="margin-bottom:2px;">
      {{ $isEdit ? ($es ? 'Editar reloj' : 'Edit watch') : __('messages.seller.watch_form_title') }}
    </h1>
    <p class="page-subtitle" style="margin-bottom:0;">
      {{ $isEdit ? $watch->brand.' · '.$watch->model : __('messages.seller.watch_form_sub') }}
    </p>
  </div>
  <a href="{{ $isEdit ? route('seller.watches.show', $watch) : route('seller.watches') }}" class="btn btn--ghost">
    ← {{ $isEdit ? ($es ? 'Volver al reloj' : 'Back to watch') : __('messages.seller.my_watches') }}
  </a>
</div>

{{-- ══════════════════════════════════════════════════════
     STEPPER
══════════════════════════════════════════════════════ --}}
<div class="wz-steps">
  @foreach($stepShort as $i => $label)
    <div class="wz-step" data-step="{{ $i }}" role="button" tabindex="0" title="{{ $stepIntro[$i][0] }}">
      <div class="wz-circle"><span class="wz-num">{{ $i }}</span><svg class="wz-tick" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="wz-name">{{ $label }}</div>
    </div>
    @if($i < $TOTAL)<div class="wz-bar"><span></span></div>@endif
  @endforeach
</div>

<div class="wz-split">

{{-- ══════════════════════════════════════════════════════
     PANNELLO ILLUSTRATO — una tavola per ogni passo
══════════════════════════════════════════════════════ --}}
<aside class="wz-aside">
  <div class="wz-aside-inner">
    @foreach($stepIntro as $i => $intro)
      <div class="wz-art" data-art="{{ $i }}">
        <div class="wz-art-svg">
          @if($i === 1)
            {{-- Identificación: lupa sobre la referencia --}}
            <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M28 176a92 92 0 0 1 184 0z" fill="#b89064" fill-opacity=".08"/>
              <path d="M78 66V44a8 8 0 0 1 8-8h30a8 8 0 0 1 8 8v22M78 138v22a8 8 0 0 0 8 8h30a8 8 0 0 0 8-8v-22" stroke="#f5f3ee" stroke-opacity=".28" stroke-width="1.4" stroke-linecap="round"/>
              <circle cx="101" cy="102" r="44" stroke="#f5f3ee" stroke-opacity=".3" stroke-width="1.4"/>
              <circle cx="101" cy="102" r="35" stroke="#b89064" stroke-width="1.6"/>
              <path d="M145 96h9v12h-9" stroke="#f5f3ee" stroke-opacity=".28" stroke-width="1.4" stroke-linejoin="round"/>
              <path d="M101 102V80M101 102l15 9" stroke="#b89064" stroke-width="1.8" stroke-linecap="round"/>
              <circle cx="101" cy="102" r="2.6" fill="#b89064"/>
              <circle cx="158" cy="132" r="31" fill="#14130f" fill-opacity=".92" stroke="#b89064" stroke-width="1.8"/>
              <path d="M180 154l17 17" stroke="#b89064" stroke-width="3.4" stroke-linecap="round"/>
              <path d="M144 125h29M144 136h20" stroke="#f5f3ee" stroke-opacity=".55" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
          @elseif($i === 2)
            {{-- Estado: escala de valoración --}}
            <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M28 176a92 92 0 0 1 184 0z" fill="#b89064" fill-opacity=".08"/>
              <circle cx="96" cy="88" r="41" stroke="#f5f3ee" stroke-opacity=".28" stroke-width="1.4"/>
              <circle cx="96" cy="88" r="32" stroke="#b89064" stroke-width="1.6"/>
              <path d="M96 88V68M96 88l14 8" stroke="#b89064" stroke-width="1.8" stroke-linecap="round"/>
              <circle cx="96" cy="88" r="2.6" fill="#b89064"/>
              <path d="M164 40l4.6 11.4L180 56l-11.4 4.6L164 72l-4.6-11.4L148 56l11.4-4.6z" stroke="#b89064" stroke-width="1.5" stroke-linejoin="round"/>
              <path d="M44 162h152" stroke="#f5f3ee" stroke-opacity=".22" stroke-width="1.4" stroke-linecap="round"/>
              <circle cx="44" cy="162" r="6" stroke="#f5f3ee" stroke-opacity=".35" stroke-width="1.4"/>
              <circle cx="82" cy="162" r="9.5" fill="#b89064"/>
              <circle cx="120" cy="162" r="6" stroke="#f5f3ee" stroke-opacity=".35" stroke-width="1.4"/>
              <circle cx="158" cy="162" r="6" stroke="#f5f3ee" stroke-opacity=".35" stroke-width="1.4"/>
              <circle cx="196" cy="162" r="6" stroke="#f5f3ee" stroke-opacity=".35" stroke-width="1.4"/>
            </svg>
          @elseif($i === 3)
            {{-- Caja y papeles --}}
            <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M28 176a92 92 0 0 1 184 0z" fill="#b89064" fill-opacity=".08"/>
              <rect x="36" y="40" width="112" height="26" rx="4" transform="rotate(-9 92 53)" stroke="#f5f3ee" stroke-opacity=".3" stroke-width="1.4"/>
              <rect x="38" y="92" width="112" height="66" rx="5" stroke="#b89064" stroke-width="1.6"/>
              <path d="M38 112h112" stroke="#f5f3ee" stroke-opacity=".2" stroke-width="1.4"/>
              <circle cx="94" cy="130" r="19" stroke="#f5f3ee" stroke-opacity=".45" stroke-width="1.4"/>
              <path d="M94 130v-11M94 130l8 5" stroke="#f5f3ee" stroke-opacity=".45" stroke-width="1.5" stroke-linecap="round"/>
              <rect x="150" y="62" width="60" height="82" rx="4" fill="#14130f" fill-opacity=".92" stroke="#f5f3ee" stroke-opacity=".38" stroke-width="1.4"/>
              <path d="M162 82h36M162 95h36M162 108h24" stroke="#f5f3ee" stroke-opacity=".4" stroke-width="1.5" stroke-linecap="round"/>
              <circle cx="174" cy="126" r="10" stroke="#b89064" stroke-width="1.6"/>
              <path d="M174 136l-5 10 5-3 5 3z" stroke="#b89064" stroke-width="1.4" stroke-linejoin="round"/>
            </svg>
          @elseif($i === 4)
            {{-- Fotografías --}}
            <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M28 176a92 92 0 0 1 184 0z" fill="#b89064" fill-opacity=".08"/>
              <rect x="74" y="18" width="104" height="164" rx="16" stroke="#f5f3ee" stroke-opacity=".3" stroke-width="1.4"/>
              <path d="M112 32h28" stroke="#f5f3ee" stroke-opacity=".3" stroke-width="3" stroke-linecap="round"/>
              <path d="M92 62v-8h10M160 62v-8h-10M92 122v8h10M160 122v8h-10" stroke="#b89064" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="126" cy="92" r="27" stroke="#b89064" stroke-width="1.6"/>
              <circle cx="126" cy="92" r="19" stroke="#f5f3ee" stroke-opacity=".4" stroke-width="1.4"/>
              <path d="M126 92V79M126 92l10 6" stroke="#f5f3ee" stroke-opacity=".5" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M111 66V56a5 5 0 0 1 5-5h20a5 5 0 0 1 5 5v10M111 118v10a5 5 0 0 0 5 5h20a5 5 0 0 0 5-5v-10" stroke="#f5f3ee" stroke-opacity=".22" stroke-width="1.4" stroke-linecap="round"/>
              <circle cx="126" cy="160" r="11" stroke="#b89064" stroke-width="1.8"/>
              <circle cx="126" cy="160" r="5" fill="#b89064"/>
            </svg>
          @else
            {{-- Precio --}}
            <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M28 176a92 92 0 0 1 184 0z" fill="#b89064" fill-opacity=".08"/>
              <g transform="rotate(-16 128 92)">
                <path d="M96 46h84a10 10 0 0 1 10 10v56a10 10 0 0 1-10 10h-84l-30-38z" fill="#14130f" fill-opacity=".92" stroke="#b89064" stroke-width="1.8" stroke-linejoin="round"/>
                <circle cx="90" cy="84" r="7" stroke="#f5f3ee" stroke-opacity=".5" stroke-width="1.5"/>
                <path d="M158 66c-5-5-14-5-19 1-6 7-6 22 0 29 5 6 14 6 19 1M126 78h22M126 90h22" stroke="#f5f3ee" stroke-opacity=".75" stroke-width="2" stroke-linecap="round"/>
              </g>
              <path d="M46 168l34-20 26 11 42-32" stroke="#b89064" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M134 127h14v14" stroke="#b89064" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          @endif
        </div>
        <div class="wz-art-eyebrow">{{ $es ? 'Paso' : 'Step' }} {{ $i }} {{ $es ? 'de' : 'of' }} {{ $TOTAL }}</div>
        <h2 class="wz-art-title">{{ $intro[0] }}</h2>
        <p class="wz-art-sub">{{ $intro[1] }}</p>
      </div>
    @endforeach

    <button type="button" class="wz-help" id="wzHelpBtn">
      <span class="wz-help-ico">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
      </span>
      <span style="flex:1;min-width:0;">
        <span class="wz-help-t">{{ $es ? '¿Tienes dudas?' : 'Need a hand?' }}</span>
        <span class="wz-help-s">{{ $es ? 'Mira el vídeo' : 'Watch the video' }}</span>
      </span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="opacity:.5;flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  </div>
</aside>

<div class="wz-main">

<form method="POST"
      action="{{ $isEdit ? route('seller.watches.update', $watch) : route('seller.watches.store') }}"
      enctype="multipart/form-data" id="watchForm" novalidate>
  @csrf
  @if($isEdit) @method('PUT') @endif

  {{-- ══════════════════════════════════════════════════
       PASO 1 — EL RELOJ
  ══════════════════════════════════════════════════ --}}
  <div class="wz-pane" data-pane="1">
    <div class="card wz-hero">
      <svg class="wz-hero-ico" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.2" y2="16.2"/></svg>
      <div style="flex:1;min-width:0;">
        <label class="form-label" style="margin-bottom:4px;">{{ __('messages.seller.reference') }} *</label>
        <p class="wz-hint">
          {{ $es
            ? 'La encontrarás en el fondo de la caja, entre las asas o en la factura original.'
            : 'You will find it on the case back, between the lugs or on the original invoice.' }}
        </p>
        <input type="text" name="reference" class="form-control wz-hero-input" data-req="1"
               value="{{ old('reference', $watch->reference ?? '') }}" placeholder="126610LN" autocomplete="off">
        @error('reference')<p class="field-error">{{ $message }}</p>@enderror
      </div>
    </div>

    <div class="card">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">{{ __('messages.seller.brand') }} *</label>
          <select name="brand" class="form-control" id="brandSelect" data-req="1" onchange="toggleBrand(this)">
            <option value="">— {{ $es ? 'Seleccionar marca' : 'Select brand' }} —</option>
            @foreach($brands as $b)
              <option value="{{ $b->name_es }}" {{ $savedBrand === $b->name_es ? 'selected' : '' }}>
                {{ !$es && $b->name_en ? $b->name_en : $b->name_es }}
              </option>
            @endforeach
            <option value="__custom__" {{ $savedBrand && !$brandInList ? 'selected' : '' }}>
              + {{ $es ? 'Otra marca' : 'Other brand' }}
            </option>
          </select>
          <input type="text" id="brandCustomInput" class="form-control" style="margin-top:8px;display:none;"
                 placeholder="{{ $es ? 'Escribe la marca' : 'Type the brand' }}"
                 value="{{ $savedBrand && !$brandInList ? $savedBrand : '' }}">
          @error('brand')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
          <label class="form-label">{{ __('messages.seller.model') }} *</label>
          <input type="text" name="model" class="form-control" data-req="1"
                 value="{{ old('model', $watch->model ?? '') }}" placeholder="Submariner, Nautilus…">
          @error('model')<p class="field-error">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="form-row" style="margin-bottom:0;">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">{{ __('messages.seller.year') }} *</label>
          <input type="number" name="manufacture_year" class="form-control" data-req="1"
                 value="{{ old('manufacture_year', $watch->manufacture_year ?? '') }}"
                 min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y')-3 }}">
          @error('manufacture_year')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">{{ __('messages.seller.category') }} *</label>
          <select name="category" class="form-control" data-req="1">
            <option value="">— {{ $es ? 'Seleccionar' : 'Select' }} —</option>
            @foreach($categories as $c)
              <option value="{{ $c->name_es }}" {{ $curCat === $c->name_es ? 'selected' : '' }}>
                {{ !$es && $c->name_en ? $c->name_en : $c->name_es }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════
       PASO 2 — ESTADO
  ══════════════════════════════════════════════════ --}}
  <div class="wz-pane" data-pane="2">
    <div class="wz-rows">
      @foreach($conditions as $v => $meta)
        <label class="wz-row {{ $curCond === $v ? 'is-on' : '' }}">
          <input type="radio" name="condition" value="{{ $v }}" {{ $curCond === $v ? 'checked' : '' }}>
          <span class="wz-row-ico">
            @if($v === 'mint')
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 4.6 4.9.4-3.7 3.2 1.1 4.8L12 13.5 7.8 16l1.1-4.8L5.2 8l4.9-.4z"/></svg>
            @elseif($v === 'excellent')
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l3 6-9 12L3 9z"/><path d="M3 9h18"/><path d="M9 3l3 6 3-6"/></svg>
            @elseif($v === 'very_good')
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="8.5 12.2 11 14.7 15.8 9.6"/></svg>
            @elseif($v === 'good')
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M7 21V10l5-7 1.2.8a2 2 0 0 1 .7 2.2L13 10h5.5a2 2 0 0 1 2 2.4l-1.3 6.4a2 2 0 0 1-2 1.6z"/><rect x="3" y="10" width="4" height="11" rx="1"/></svg>
            @else
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
            @endif
          </span>
          <span class="wz-row-txt">
            <span class="wz-row-t">{{ $meta[0] }}</span>
            <span class="wz-row-d">{{ $meta[1] }}</span>
          </span>
          <span class="wz-dot"></span>
        </label>
      @endforeach
    </div>
    @error('condition')<p class="field-error" style="margin-top:10px;">{{ $message }}</p>@enderror
    <p class="wz-err" id="condError">{{ $es ? 'Selecciona el estado del reloj' : 'Select the watch condition' }}</p>

    <div class="card" style="margin-top:16px;">
      <label class="form-label">{{ __('messages.seller.description') }} *</label>
      <textarea name="description" class="form-control" data-req="1" style="min-height:130px;"
                placeholder="{{ $es ? 'Historia del reloj, servicios realizados, detalles especiales…' : 'Watch history, services performed, special details…' }}">{{ old('description', $watch->description ?? '') }}</textarea>
      @error('description')<p class="field-error">{{ $message }}</p>@enderror
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════
       PASO 3 — CAJA Y PAPELES
  ══════════════════════════════════════════════════ --}}
  <div class="wz-pane" data-pane="3">
    <div class="wz-rows">
      @foreach($boxOpts as $v => $meta)
        <label class="wz-row {{ $curBox === $v ? 'is-on' : '' }}">
          <input type="radio" name="box_papers" value="{{ $v }}" {{ $curBox === $v ? 'checked' : '' }} onchange="toggleDocs('{{ $v }}')">
          <span class="wz-row-ico">
            @if($v === 'none')
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3.3 7.5 12 3l8.7 4.5v9L12 21l-8.7-4.5z"/><line x1="4" y1="4" x2="20" y2="20"/></svg>
            @elseif($v === 'box_only')
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3.3 7.5 12 3l8.7 4.5v9L12 21l-8.7-4.5z"/><path d="M3.3 7.5 12 12l8.7-4.5M12 12v9"/></svg>
            @elseif($v === 'papers_only')
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><polyline points="14 3 14 8 19 8"/><line x1="8.5" y1="13" x2="15.5" y2="13"/><line x1="8.5" y1="17" x2="13" y2="17"/></svg>
            @else
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="2.5 13 6.5 17 11 11.5"/><polyline points="12 13 16 17 21.5 9.5"/></svg>
            @endif
          </span>
          <span class="wz-row-txt">
            <span class="wz-row-t">{{ $meta[0] }}</span>
            <span class="wz-row-d">{{ $meta[1] }}</span>
          </span>
          <span class="wz-dot"></span>
        </label>
      @endforeach
    </div>

    <div id="docsSection" class="card" style="display:none;margin-top:16px;">
      <label class="form-label">{{ $es ? 'Documentación' : 'Documentation' }}</label>
      <p class="wz-hint">{{ $es ? 'Certificado, factura o tarjeta de garantía. Sólo la verá el equipo de Haus of Time.' : 'Certificate, invoice or warranty card. Only the Haus of Time team will see it.' }}</p>

      @if(count($existDocs))
        <div style="margin-bottom:10px;display:flex;flex-direction:column;gap:6px;">
          @foreach($existDocs as $di => $doc)
            <div class="wf-doc-item" id="docItem{{ $di }}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" style="color:var(--s-subtle);flex-shrink:0;"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><polyline points="14 3 14 8 19 8"/></svg>
              <span style="font-size:12px;flex:1;color:var(--s-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ basename($doc) }}</span>
              <input type="hidden" name="keep_docs[]" value="{{ $doc }}" id="keepDoc{{ $di }}">
              <button type="button" class="wf-del-btn" onclick="toggleMedia('docItem{{ $di }}','keepDoc{{ $di }}')" title="{{ $es ? 'Eliminar' : 'Delete' }}">×</button>
            </div>
          @endforeach
        </div>
      @endif

      <label class="wz-drop">
        <input type="file" name="documentation_files[]" multiple accept=".pdf,image/*" style="display:none" onchange="previewDocs(this)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="opacity:.5;"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><polyline points="14 3 14 8 19 8"/></svg>
        <span style="font-size:12px;color:var(--s-muted);">{{ $es ? 'Añadir PDF o imágenes' : 'Add PDF or images' }}</span>
      </label>
      <div id="newDocsList" style="margin-top:8px;display:flex;flex-direction:column;gap:3px;"></div>
      @error('documentation_files.*')<p class="field-error">{{ $message }}</p>@enderror
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════
       PASO 4 — FOTOGRAFÍAS
  ══════════════════════════════════════════════════ --}}
  <div class="wz-pane" data-pane="4">
    @if(count($existImgs))
      <div class="card" style="margin-bottom:16px;">
        <label class="form-label">{{ $es ? 'Fotos actuales' : 'Current photos' }}</label>
        <div class="wz-grid">
          @foreach($existImgs as $ii => $img)
            <div class="wf-img-item" id="imgItem{{ $ii }}">
              <img src="{{ Storage::url($img) }}" alt="">
              @if($ii === 0)<span class="wz-cover">{{ $es ? 'Portada' : 'Cover' }}</span>@endif
              <input type="hidden" name="keep_images[]" value="{{ $img }}" id="keepImg{{ $ii }}">
              <button type="button" class="wf-del-btn wf-del-img" onclick="toggleMedia('imgItem{{ $ii }}','keepImg{{ $ii }}')" title="{{ $es ? 'Eliminar' : 'Delete' }}">×</button>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <input type="file" name="images[]" id="imgInput" multiple accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none">

    <div class="wz-grid" id="photoSlots"></div>

    <p class="wz-hint" style="margin-top:14px;">
      {{ $es ? 'Máx. 10 fotos · JPG, PNG o WEBP · 10 MB cada una. Haz clic o arrastra las imágenes sobre las casillas.' : 'Max. 10 photos · JPG, PNG or WEBP · 10 MB each. Click or drag images onto the slots.' }}
    </p>
    <p class="wz-err" id="photoError">{{ $es ? 'Añade al menos una fotografía' : 'Add at least one photo' }}</p>
    @error('images')<p class="field-error">{{ $message }}</p>@enderror
    @error('images.*')<p class="field-error">{{ $message }}</p>@enderror
  </div>

  {{-- ══════════════════════════════════════════════════
       PASO 5 — PRECIO Y RESUMEN
  ══════════════════════════════════════════════════ --}}
  <div class="wz-pane" data-pane="5">
    <div class="card">
      <div class="form-row">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">{{ __('messages.seller.price_sale') }} *</label>
          <div style="position:relative;">
            <span class="wz-cur">€</span>
            <input type="number" name="price" id="priceInput" class="form-control" style="padding-left:28px;font-size:15px;font-weight:500;"
                   data-req="1" value="{{ old('price', $isEdit ? (int)$watch->price : '') }}" min="0" step="1" placeholder="15000">
          </div>
          @error('price')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">{{ __('messages.seller.price_discounted') }}</label>
          <div style="position:relative;">
            <span class="wz-cur">€</span>
            <input type="number" name="price_discounted" id="priceDiscInput" class="form-control" style="padding-left:28px;"
                   value="{{ old('price_discounted', $isEdit && $watch->price_discounted ? (int)$watch->price_discounted : '') }}"
                   min="0" step="1" placeholder="{{ $es ? 'Opcional' : 'Optional' }}">
          </div>
          @error('price_discounted')<p class="field-error">{{ $message }}</p>@enderror
        </div>
      </div>
      <p class="wz-hint" style="margin-top:8px;">{{ __('messages.seller.price_discounted_hint') }}</p>

      <div class="wz-settle">
        <div class="wz-settle-h">{{ $es ? 'Tu liquidación' : 'Your payout' }}</div>
        <div class="wz-settle-r"><span>{{ $es ? 'Precio' : 'Price' }}</span><span id="stPrice">—</span></div>
        <div class="wz-settle-r wz-settle-r--b">
          <span>{{ __('messages.seller.price_commission') }} {{ $commLabel }} {{ rtrim(rtrim(number_format($commRate, 2, ',', ''), '0'), ',') }}%</span>
          <span id="stComm">—</span>
        </div>
        <div class="wz-settle-tot"><span>{{ $es ? 'Recibes' : 'You receive' }}</span><span id="stNet">—</span></div>
        <p class="wz-settle-n" id="stNote">{{ $es ? 'Cálculo estimado sobre el precio de venta.' : 'Estimated on the sale price.' }}</p>
      </div>
    </div>

    {{-- Resumen --}}
    <div class="card" style="margin-top:16px;">
      <div class="wf-sh"><span class="wf-sl">{{ $es ? 'Resumen' : 'Summary' }}</span><div class="wf-sline"></div></div>
      <div id="summaryBox"></div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════
       NAVEGACIÓN
  ══════════════════════════════════════════════════ --}}
  <div class="wz-nav">
    <button type="button" class="btn btn--ghost" id="wzBack">← {{ $es ? 'Atrás' : 'Back' }}</button>
    <div style="flex:1"></div>
    @if($isEdit)
      <button type="button" class="btn btn--ghost" id="wzDelete" style="color:var(--s-danger);border-color:rgba(155,51,51,.3);">
        {{ $es ? 'Eliminar' : 'Delete' }}
      </button>
    @endif
    <button type="button" class="btn btn--dark btn--lg" id="wzNext">{{ $es ? 'Continuar' : 'Continue' }} →</button>
    <button type="submit" class="btn btn--dark btn--lg" id="wzSubmit" style="display:none;">
      {{ $isEdit ? __('messages.seller.save_changes_btn') : ($es ? 'Continuar a verificación' : 'Continue to verification') }}
    </button>
  </div>

</form>

</div>{{-- /wz-main --}}
</div>{{-- /wz-split --}}

</div>{{-- /wz-shell --}}

@if($isEdit)
<form method="POST" action="{{ route('seller.watches.destroy', $watch) }}" id="deleteWatchForm">
  @csrf @method('DELETE')
</form>
@endif

{{-- ══════════════════════════════════════════════════════
     POPUP VIDEO TUTORIAL
══════════════════════════════════════════════════════ --}}
<div id="wzModal" role="dialog" aria-modal="true" aria-label="{{ $es ? 'Vídeo tutorial' : 'Tutorial video' }}">
  <div class="wz-modal-bd" id="wzModalBd"></div>
  <div class="wz-modal-box">
    <button type="button" class="wz-modal-x" id="wzModalX" aria-label="{{ $es ? 'Cerrar' : 'Close' }}">&times;</button>
    <video id="wzVideo" playsinline controls preload="metadata" poster="/videos/tutorial-poster.jpg">
      <source src="/videos/tutorial-subir-reloj.mp4" type="video/mp4">
    </video>
    <button type="button" class="wz-unmute" id="wzUnmute">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H3v6h3l5 4z"/><path d="M16 9.5c1 1 1 4 0 5"/><path d="M19 7c2.2 2.2 2.2 7.8 0 10"/></svg>
      {{ $es ? 'Toca para activar el sonido' : 'Tap to turn on sound' }}
    </button>
  </div>
</div>

@push('styles')
<style>
/* ── Shell ───────────────────────────────────────────── */
.wz-shell{position:relative;z-index:1;max-width:1120px;margin:0 auto;}
.wz-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:4px;}

/* ── Due colonne: tavola illustrata + form ───────────── */
.wz-split{display:grid;grid-template-columns:330px minmax(0,1fr);gap:28px;align-items:start;}
.wz-main{min-width:0;}

.wz-aside{position:sticky;top:24px;border-radius:5px;overflow:hidden;background:#0f0e0b;border:1px solid rgba(198,156,109,.20);}
.wz-aside::before{content:'';position:absolute;inset:0;background:radial-gradient(115% 75% at 50% 16%,rgba(198,156,109,.18),rgba(198,156,109,.05) 46%,transparent 72%);}
.wz-aside::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.04),transparent 52%);}
.wz-aside-inner{position:relative;z-index:1;padding:38px 30px 34px;}
.wz-art{display:none;}
.wz-art.is-active{display:block;animation:wzArt .4s ease;}
@keyframes wzArt{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.wz-art-svg{margin:0 auto 24px;padding-bottom:26px;border-bottom:1px solid rgba(198,156,109,.16);}
.wz-art-svg svg{width:100%;max-width:250px;height:auto;display:block;margin:0 auto;}
.wz-art-eyebrow{font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--s-gold);margin-bottom:9px;}
.wz-art-title{font-size:22px;font-weight:400;line-height:1.25;letter-spacing:-.01em;color:#f5f3ee;margin-bottom:10px;}
.wz-art-sub{font-size:12.5px;line-height:1.65;color:rgba(245,243,238,.68);margin:0;}

/* ── Pulsante video ──────────────────────────────────── */
.wz-help{position:relative;z-index:1;display:flex;align-items:center;gap:13px;width:100%;
  margin-top:26px;padding-top:24px;border-top:1px solid rgba(198,156,109,.16);
  background:none;border-left:none;border-right:none;border-bottom:none;
  font-family:var(--font);color:#f5f3ee;cursor:pointer;text-align:left;}
.wz-help-ico{width:32px;height:32px;border-radius:50%;border:1px solid var(--s-gold);color:var(--s-gold);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;padding-left:2px;transition:all .2s;}
.wz-help:hover .wz-help-ico{background:var(--s-gold);color:#141310;}
.wz-help-t{display:block;font-size:12.5px;font-weight:500;line-height:1.35;}
.wz-help-s{display:block;font-size:10px;font-weight:500;letter-spacing:.16em;text-transform:uppercase;color:var(--s-gold);}

/* ── Popup video ─────────────────────────────────────── */
#wzModal{position:fixed;inset:0;z-index:900;display:none;align-items:center;justify-content:center;padding:24px;}
#wzModal.is-open{display:flex;}
.wz-modal-bd{position:absolute;inset:0;background:rgba(8,7,5,.88);}
.wz-modal-box{position:relative;z-index:1;width:min(92vw,calc(84vh * 9 / 16));aspect-ratio:9/16;
  border-radius:6px;overflow:hidden;background:#0f0e0b;border:1px solid rgba(198,156,109,.25);}
.wz-modal-box video{width:100%;height:100%;object-fit:contain;display:block;background:#0f0e0b;}
.wz-modal-x{position:absolute;top:10px;right:10px;z-index:2;width:34px;height:34px;border-radius:50%;
  background:rgba(8,7,5,.7);border:1px solid rgba(245,243,238,.25);color:#f5f3ee;font-size:21px;line-height:1;
  cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s;}
.wz-modal-x:hover{background:var(--s-danger);border-color:var(--s-danger);}
.wz-unmute{position:absolute;z-index:2;left:50%;bottom:74px;transform:translateX(-50%);display:none;
  align-items:center;gap:8px;padding:10px 18px;border-radius:999px;white-space:nowrap;
  background:rgba(198,156,109,.94);color:#141310;border:none;cursor:pointer;
  font-family:var(--font);font-size:11.5px;font-weight:500;letter-spacing:.04em;}
.wz-unmute.is-on{display:flex;}
.wz-unmute:hover{background:var(--s-gold);}

/* ── Stepper ─────────────────────────────────────────── */
.wz-steps{display:flex;align-items:flex-start;margin:26px 6px 32px;}
.wz-step{display:flex;flex-direction:column;align-items:center;cursor:pointer;outline:none;}
.wz-circle{width:30px;height:30px;border-radius:50%;border:1px solid var(--s-border);background:var(--s-bg2);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:500;color:var(--s-subtle);transition:all .25s;}
.wz-step:focus-visible .wz-circle{border-color:var(--s-gold);}
.wz-tick{display:none;}
.wz-step.is-on .wz-circle{border-color:var(--s-gold);background:var(--s-gold);color:#141310;}
.wz-step.is-done .wz-circle{border-color:var(--s-gold);background:rgba(198,156,109,.14);color:var(--s-gold);}
.wz-step.is-done .wz-num{display:none;}
.wz-step.is-done .wz-tick{display:block;}
.wz-name{font-size:8.5px;letter-spacing:.15em;text-transform:uppercase;color:var(--s-subtle);margin-top:8px;white-space:nowrap;transition:color .25s;}
.wz-step.is-on .wz-name{color:var(--s-text);font-weight:500;}
.wz-step.is-done .wz-name{color:var(--s-muted);}
.wz-bar{flex:1;height:1px;background:var(--s-border);margin:0 8px;position:relative;top:14px;}
.wz-bar span{display:block;height:100%;width:0;background:var(--s-gold);transition:width .35s ease;}

/* ── Panes ───────────────────────────────────────────── */
.wz-pane{display:none;animation:wzIn .28s ease;}
.wz-pane.is-active{display:block;}
@keyframes wzIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.wz-hint{font-size:11.5px;color:var(--s-subtle);line-height:1.5;margin:0 0 12px;}

/* ── Righe di scelta ─────────────────────────────────── */
.wz-rows{display:flex;flex-direction:column;gap:9px;}
.wz-row{display:flex;align-items:center;gap:15px;background:var(--s-bg2);border:1px solid var(--s-border);border-radius:4px;padding:15px 17px;cursor:pointer;transition:border-color .18s,background .18s;}
.wz-row:hover{border-color:var(--s-gold);}
.wz-row input{position:absolute;opacity:0;pointer-events:none;}
.wz-row.is-on{border-color:var(--s-gold);background:rgba(198,156,109,.10);}
.wz-row-ico{flex-shrink:0;color:var(--s-subtle);transition:color .18s;display:flex;}
.wz-row-ico svg{width:22px;height:22px;}
.wz-row.is-on .wz-row-ico{color:var(--s-gold);}
.wz-row-txt{flex:1;min-width:0;}
.wz-row-t{display:block;font-size:13px;font-weight:500;color:var(--s-text);}
.wz-row-d{display:block;font-size:11.5px;color:var(--s-muted);line-height:1.45;}
.wz-dot{width:15px;height:15px;border-radius:50%;border:1px solid var(--s-border);flex-shrink:0;transition:all .18s;}
.wz-row.is-on .wz-dot{border-color:var(--s-gold);background:var(--s-gold);box-shadow:inset 0 0 0 3px var(--s-bg2);}

/* ── Hero referencia ─────────────────────────────────── */
.wz-hero{display:flex;gap:16px;align-items:flex-start;margin-bottom:16px;}
.wz-hero-ico{color:var(--s-gold);flex-shrink:0;margin-top:2px;}
.wz-hero-input{font-size:15px;letter-spacing:.06em;padding:12px 14px;max-width:320px;}

/* ── Section heading ─────────────────────────────────── */
.wf-sh{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
.wf-sl{font-size:10px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--s-text);white-space:nowrap;flex-shrink:0;}
.wf-sline{flex:1;height:1px;background:linear-gradient(to right,var(--s-border),transparent);}

/* ── Foto ────────────────────────────────────────────── */
.wz-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.wz-slot{aspect-ratio:1;border:1.5px dashed var(--s-border);border-radius:3px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;background:rgba(255,255,255,.025);color:var(--s-subtle);cursor:pointer;transition:border-color .18s,color .18s;overflow:hidden;position:relative;text-align:center;padding:8px;}
.wz-slot svg{width:24px;height:24px;}
.wz-slot span{font-size:9px;letter-spacing:.12em;text-transform:uppercase;line-height:1.2;}
.wz-slot:hover,.wz-slot.is-over{border-color:var(--s-gold);color:var(--s-gold);}
.wz-slot.is-filled{border-style:solid;border-color:var(--s-border);padding:0;cursor:default;}
.wz-slot.is-filled svg,.wz-slot.is-filled span:not(.wz-cover){display:none;}
.wz-slot img{width:100%;height:100%;object-fit:cover;display:block;}
.wz-slot-x{position:absolute;top:6px;right:6px;background:rgba(10,9,7,.82);color:#fff;border:none;width:21px;height:21px;border-radius:50%;font-size:13px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.wz-slot-x:hover{background:var(--s-danger);}
.wz-cover{position:absolute;left:6px;bottom:6px;background:rgba(10,9,7,.85);color:#fff;font-size:8px;letter-spacing:.12em;text-transform:uppercase;padding:2px 7px;border-radius:2px;}
.wf-img-item{position:relative;border-radius:3px;}
.wf-img-item img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:3px;border:1px solid var(--s-border);display:block;}

/* ── Dropzone documenti ──────────────────────────────── */
.wz-drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;border:1.5px dashed var(--s-border);border-radius:3px;padding:18px;cursor:pointer;background:var(--s-bg);transition:border-color .2s;width:100%;color:var(--s-muted);}
.wz-drop:hover{border-color:var(--s-gold);}

/* ── Liquidazione ────────────────────────────────────── */
.wz-cur{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--s-subtle);font-size:13px;pointer-events:none;}
.wz-settle{background:var(--s-bg);border-radius:4px;padding:17px 19px;margin-top:18px;}
.wz-settle-h{font-size:9px;letter-spacing:.16em;text-transform:uppercase;color:var(--s-subtle);margin-bottom:13px;}
.wz-settle-r{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:var(--s-muted);margin-bottom:7px;}
.wz-settle-r--b{padding-bottom:11px;border-bottom:1px solid var(--s-border);margin-bottom:0;}
.wz-settle-tot{display:flex;justify-content:space-between;align-items:baseline;gap:10px;margin-top:12px;}
.wz-settle-tot span:first-child{font-size:11px;font-weight:500;}
.wz-settle-tot span:last-child{font-size:20px;font-weight:600;color:var(--s-gold);}
.wz-settle-n{font-size:10px;color:var(--s-subtle);margin-top:10px;line-height:1.5;}

/* ── Resumen ─────────────────────────────────────────── */
.wz-sum{display:flex;justify-content:space-between;align-items:baseline;gap:14px;padding:9px 0;border-bottom:1px solid var(--s-border2);font-size:12.5px;}
.wz-sum:last-child{border-bottom:none;}
.wz-sum-k{color:var(--s-muted);flex-shrink:0;}
.wz-sum-v{font-weight:500;text-align:right;}
.wz-sum-v.is-missing{color:var(--s-danger);font-weight:400;cursor:pointer;text-decoration:underline;text-underline-offset:2px;}

/* ── Misc ────────────────────────────────────────────── */
.wz-err{display:none;font-size:11.5px;color:var(--s-danger);margin-top:12px;}
.wz-err.is-on{display:block;}
.form-control.is-invalid{border-color:var(--s-danger);}
.wz-rows.is-invalid .wz-row{border-color:rgba(155,51,51,.4);}
.wz-nav{display:flex;gap:10px;align-items:center;margin-top:26px;flex-wrap:wrap;}
.wf-doc-item{display:flex;align-items:center;gap:8px;padding:8px 12px;background:var(--s-bg3);border:1px solid var(--s-border);border-radius:3px;}
.wf-del-btn{background:rgba(155,51,51,.85);color:#fff;border:none;cursor:pointer;border-radius:50%;width:22px;height:22px;font-size:14px;line-height:1;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s;}
.wf-del-btn:hover{background:var(--s-danger);}
.wf-del-btn.wf-del-img{position:absolute;top:-6px;right:-6px;z-index:2;box-shadow:0 1px 4px rgba(0,0,0,.25);}
.wf-del-btn.is-restore{background:rgba(61,122,94,.85);}
.wf-img-item.is-deleted img{opacity:.25;filter:grayscale(1);}
.wf-doc-item.is-deleted{opacity:.35;text-decoration:line-through;}

/* ── Responsive ──────────────────────────────────────── */
@media(max-width:1250px){
  .wz-split{grid-template-columns:290px minmax(0,1fr);gap:22px;}
  .wz-aside-inner{padding:26px 24px 28px;}
  .wz-art-title{font-size:20px;}
}
@media(max-width:1000px){
  .wz-split{grid-template-columns:1fr;gap:20px;}
  .wz-aside{position:static;}
  .wz-art.is-active{display:grid;grid-template-columns:150px minmax(0,1fr);gap:24px;align-items:center;}
  .wz-art-svg{margin:0;padding:0;border-bottom:none;border-right:1px solid rgba(198,156,109,.16);padding-right:24px;}
  .wz-art-svg svg{max-width:150px;}
  .wz-art-eyebrow{grid-column:2;margin-bottom:6px;}
  .wz-art-title{grid-column:2;margin-bottom:6px;}
  .wz-art-sub{grid-column:2;}
  .wz-art-svg{grid-row:1/span 3;}
}
@media(max-width:640px){
  .wz-art.is-active{grid-template-columns:1fr;gap:16px;}
  .wz-art-svg{grid-row:auto;border-right:none;padding-right:0;padding-bottom:18px;border-bottom:1px solid rgba(198,156,109,.16);}
  .wz-art-svg svg{max-width:120px;}
  .wz-art-eyebrow,.wz-art-title,.wz-art-sub{grid-column:1;}
  .wz-art-title{font-size:18px;}
  .wz-name{display:none;}
  .wz-bar{margin:0 5px;top:14px;}
  .wz-steps{margin:20px 0 26px;}
  .wz-grid{grid-template-columns:repeat(2,1fr);}
  .wz-hero{flex-direction:column;gap:10px;}
  .wz-hero-input{max-width:100%;}
  .wz-row{padding:13px 14px;gap:12px;}
  .wz-nav .btn{flex:1;min-width:120px;justify-content:center;}
}
</style>
@endpush

@push('scripts')
<script>
(function () {
  var TOTAL    = {{ $TOTAL }};
  var LOC_ES   = {{ $es ? 'true' : 'false' }};
  var IS_EDIT  = {{ $isEdit ? 'true' : 'false' }};
  var HAS_IMGS = {{ count($existImgs) }};
  var MAX_IMGS = 10;
  var RATE     = parseFloat('{{ $commRate }}') || 0;
  var HINTS    = @json($photoHints);

  var step = {{ $startStep }};

  var form    = document.getElementById('watchForm');
  var panes   = document.querySelectorAll('.wz-pane');
  var arts    = document.querySelectorAll('.wz-art');
  var steps   = document.querySelectorAll('.wz-step');
  var bars    = document.querySelectorAll('.wz-bar span');
  var btnNext = document.getElementById('wzNext');
  var btnBack = document.getElementById('wzBack');
  var btnSub  = document.getElementById('wzSubmit');

  var T = {
    missing: LOC_ES ? 'Falta — completar' : 'Missing — complete',
    none:    LOC_ES ? 'No indicado' : 'Not specified',
    watch:   LOC_ES ? 'Reloj' : 'Watch',
    year:    LOC_ES ? 'Año' : 'Year',
    cat:     LOC_ES ? 'Categoría' : 'Category',
    cond:    LOC_ES ? 'Estado' : 'Condition',
    box:     LOC_ES ? 'Caja y papeles' : 'Box and papers',
    photos:  LOC_ES ? 'Fotografías' : 'Photos',
    nPhotos: LOC_ES ? ' subidas' : ' uploaded'
  };

  /* ── Navigazione ────────────────────────────────── */
  function render() {
    panes.forEach(function (p) {
      p.classList.toggle('is-active', parseInt(p.dataset.pane, 10) === step);
    });
    arts.forEach(function (a) {
      a.classList.toggle('is-active', parseInt(a.dataset.art, 10) === step);
    });
    steps.forEach(function (s) {
      var i = parseInt(s.dataset.step, 10);
      s.classList.toggle('is-on', i === step);
      s.classList.toggle('is-done', i < step);
    });
    bars.forEach(function (b, idx) { b.style.width = (step > idx + 1) ? '100%' : '0'; });

    btnBack.style.visibility = step === 1 ? 'hidden' : 'visible';
    btnNext.style.display    = step === TOTAL ? 'none' : 'inline-flex';
    /* In modifica il salvataggio e' sempre a portata di mano */
    btnSub.style.display     = (step === TOTAL || IS_EDIT) ? 'inline-flex' : 'none';

    if (step === TOTAL) buildSummary();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function focusFirstInvalid() {
    var el = document.querySelector('.wz-pane.is-active .is-invalid');
    if (el && el.focus) { try { el.focus({ preventScroll: true }); } catch (err) { el.focus(); } }
  }

  function goTo(target) {
    /* In modifica si salta liberamente: i dati esistono gia' */
    if (!IS_EDIT && target > step) {
      for (var i = step; i < target; i++) {
        if (!validate(i)) { step = i; render(); focusFirstInvalid(); return; }
      }
    }
    step = target;
    render();
  }

  btnNext.addEventListener('click', function () { if (step < TOTAL) goTo(step + 1); });
  btnBack.addEventListener('click', function () { if (step > 1) goTo(step - 1); });

  steps.forEach(function (s) {
    function jump() { goTo(parseInt(s.dataset.step, 10)); }
    s.addEventListener('click', jump);
    s.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); jump(); } });
  });

  /* ── Validazione ────────────────────────────────── */
  function markInvalid(el, on) { if (el) el.classList.toggle('is-invalid', !!on); }

  function validate(n) {
    var ok = true;
    var pane = document.querySelector('.wz-pane[data-pane="' + n + '"]');
    if (!pane) return true;

    pane.querySelectorAll('[data-req]').forEach(function (el) {
      if (el.id === 'brandSelect' && el.value === '__custom__') {
        var ci = document.getElementById('brandCustomInput');
        var noCustom = !ci || !ci.value.trim();
        markInvalid(ci, noCustom);
        markInvalid(el, false);
        if (noCustom) ok = false;
        return;
      }
      var empty = !el.value || !String(el.value).trim();
      markInvalid(el, empty);
      if (empty) ok = false;
    });

    if (n === 2) {
      var cond = form.querySelector('input[name="condition"]:checked');
      document.getElementById('condError').classList.toggle('is-on', !cond);
      pane.querySelector('.wz-rows').classList.toggle('is-invalid', !cond);
      if (!cond) ok = false;
    }

    if (n === 4 && !IS_EDIT) {
      var input = document.getElementById('imgInput');
      var none  = (!input.files || input.files.length === 0) && HAS_IMGS === 0;
      document.getElementById('photoError').classList.toggle('is-on', none);
      if (none) ok = false;
    }
    return ok;
  }

  form.addEventListener('submit', function (e) {
    for (var i = 1; i <= TOTAL; i++) {
      if (!validate(i)) { e.preventDefault(); step = i; render(); focusFirstInvalid(); return; }
    }
    btnSub.disabled = true;
    btnSub.textContent = LOC_ES ? 'Enviando…' : 'Sending…';
  });

  form.addEventListener('input', function (e) {
    if (e.target.classList) e.target.classList.remove('is-invalid');
  });

  /* ── Righe di scelta ────────────────────────────── */
  document.querySelectorAll('.wz-row input[type="radio"]').forEach(function (r) {
    r.addEventListener('change', function () {
      document.querySelectorAll('.wz-row input[name="' + r.name + '"]').forEach(function (o) {
        o.closest('.wz-row').classList.toggle('is-on', o.checked);
      });
      if (r.name === 'condition') {
        document.getElementById('condError').classList.remove('is-on');
        var rows = r.closest('.wz-rows'); if (rows) rows.classList.remove('is-invalid');
      }
    });
  });

  /* ── Marca custom ───────────────────────────────── */
  window.toggleBrand = function (sel) {
    var c = document.getElementById('brandCustomInput');
    if (!c) return;
    if (sel.value === '__custom__') {
      c.style.display = 'block'; c.name = 'brand'; sel.removeAttribute('name');
    } else {
      c.style.display = 'none'; c.removeAttribute('name'); sel.name = 'brand';
    }
  };

  /* ── Documentazione condizionale ────────────────── */
  window.toggleDocs = function (val) {
    var s = document.getElementById('docsSection');
    if (s) s.style.display = (val === 'papers_only' || val === 'full_set') ? 'block' : 'none';
  };

  /* ── Elimina / ripristina media esistenti ───────── */
  window.toggleMedia = function (itemId, inputId) {
    var item = document.getElementById(itemId);
    var inp  = document.getElementById(inputId);
    if (!item || !inp) return;
    var btn = item.querySelector('.wf-del-btn');
    var del = item.classList.toggle('is-deleted');
    inp.disabled = del;
    if (btn) {
      btn.classList.toggle('is-restore', del);
      btn.textContent = del ? '↩' : '×';
      btn.title = del ? (LOC_ES ? 'Restaurar' : 'Restore') : (LOC_ES ? 'Eliminar' : 'Delete');
    }
  };

  window.previewDocs = function (input) {
    var p = document.getElementById('newDocsList');
    if (!p) return;
    p.innerHTML = '';
    Array.prototype.slice.call(input.files).forEach(function (f) {
      var d = document.createElement('div');
      d.style.cssText = 'font-size:11px;color:var(--s-muted);';
      d.textContent = '· ' + f.name;
      p.appendChild(d);
    });
  };

  /* ── Foto ───────────────────────────────────────── */
  var imgInput = document.getElementById('imgInput');
  var slotWrap = document.getElementById('photoSlots');
  var picked   = [];
  var supportsDT = (typeof DataTransfer !== 'undefined');

  function syncInput() {
    if (!supportsDT) return;
    var dt = new DataTransfer();
    picked.forEach(function (f) { dt.items.add(f); });
    imgInput.files = dt.files;
  }

  function renderSlots() {
    slotWrap.innerHTML = '';
    var total = Math.max(HINTS.length, picked.length);
    for (var i = 0; i < total && i < MAX_IMGS; i++) {
      var slot = document.createElement('div');
      slot.className = 'wz-slot';
      if (picked[i]) {
        slot.classList.add('is-filled');
        var img = document.createElement('img');
        img.src = URL.createObjectURL(picked[i]);
        img.onload = function () { URL.revokeObjectURL(this.src); };
        slot.appendChild(img);
        if (i === 0 && HAS_IMGS === 0) {
          var cov = document.createElement('span');
          cov.className = 'wz-cover';
          cov.textContent = LOC_ES ? 'Portada' : 'Cover';
          slot.appendChild(cov);
        }
        if (supportsDT) {
          var x = document.createElement('button');
          x.type = 'button'; x.className = 'wz-slot-x'; x.textContent = '×';
          x.setAttribute('aria-label', LOC_ES ? 'Quitar foto' : 'Remove photo');
          (function (idx) {
            x.addEventListener('click', function (ev) {
              ev.stopPropagation();
              picked.splice(idx, 1); syncInput(); renderSlots();
            });
          })(i);
          slot.appendChild(x);
        }
      } else {
        slot.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="7" x2="12" y2="17"/><line x1="7" y1="12" x2="17" y2="12"/></svg><span>' + (HINTS[i] || '') + '</span>';
        slot.addEventListener('click', function () { imgInput.click(); });
      }
      slotWrap.appendChild(slot);
    }
    document.getElementById('photoError').classList.remove('is-on');
  }

  function addFiles(list) {
    var arr = Array.prototype.slice.call(list).filter(function (f) { return /^image\//.test(f.type); });
    if (!supportsDT) { picked = arr.slice(0, MAX_IMGS); renderSlots(); return; }
    arr.forEach(function (f) { if (picked.length < MAX_IMGS) picked.push(f); });
    syncInput();
    renderSlots();
  }

  imgInput.addEventListener('change', function () {
    if (!supportsDT) { picked = Array.prototype.slice.call(imgInput.files); renderSlots(); return; }
    addFiles(imgInput.files);
  });

  ['dragenter', 'dragover'].forEach(function (ev) {
    slotWrap.addEventListener(ev, function (e) {
      e.preventDefault();
      slotWrap.querySelectorAll('.wz-slot:not(.is-filled)').forEach(function (s) { s.classList.add('is-over'); });
    });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    slotWrap.addEventListener(ev, function (e) {
      e.preventDefault();
      slotWrap.querySelectorAll('.wz-slot').forEach(function (s) { s.classList.remove('is-over'); });
    });
  });
  slotWrap.addEventListener('drop', function (e) {
    if (e.dataTransfer && e.dataTransfer.files) addFiles(e.dataTransfer.files);
  });

  /* ── Liquidazione ───────────────────────────────── */
  var fmt = new Intl.NumberFormat(LOC_ES ? 'es-ES' : 'en-GB', { maximumFractionDigits: 0 });
  var priceInput = document.getElementById('priceInput');
  var discInput  = document.getElementById('priceDiscInput');

  function settle() {
    var base = parseFloat(discInput.value) || parseFloat(priceInput.value) || 0;
    var comm = Math.round(base * RATE / 100);
    document.getElementById('stPrice').textContent = base ? fmt.format(base) + ' €' : '—';
    document.getElementById('stComm').textContent  = base ? '−' + fmt.format(comm) + ' €' : '—';
    document.getElementById('stNet').textContent   = base ? fmt.format(base - comm) + ' €' : '—';
    document.getElementById('stNote').textContent  = (discInput.value && parseFloat(discInput.value) > 0)
      ? (LOC_ES ? 'Cálculo sobre el precio con descuento.' : 'Calculated on the discounted price.')
      : (LOC_ES ? 'Cálculo estimado sobre el precio de venta.' : 'Estimated on the sale price.');
  }
  priceInput.addEventListener('input', settle);
  discInput.addEventListener('input', settle);

  /* ── Resumen ────────────────────────────────────── */
  function brandValue() {
    var sel = document.getElementById('brandSelect');
    if (sel && sel.value === '__custom__') {
      var ci = document.getElementById('brandCustomInput');
      return ci ? ci.value.trim() : '';
    }
    return sel ? sel.value.trim() : '';
  }

  function keptImages() {
    var n = 0;
    document.querySelectorAll('input[name="keep_images[]"]').forEach(function (i) { if (!i.disabled) n++; });
    return n;
  }

  function line(key, value, targetStep) {
    var row = document.createElement('div');
    row.className = 'wz-sum';
    var k = document.createElement('span'); k.className = 'wz-sum-k'; k.textContent = key;
    var v = document.createElement('span'); v.className = 'wz-sum-v';
    if (value) {
      v.textContent = value;
    } else {
      v.textContent = T.missing;
      v.className += ' is-missing';
      v.addEventListener('click', function () { goTo(targetStep); });
    }
    row.appendChild(k); row.appendChild(v);
    return row;
  }

  function buildSummary() {
    var box = document.getElementById('summaryBox');
    if (!box) return;
    box.innerHTML = '';

    var brand = brandValue();
    var model = form.querySelector('[name="model"]').value.trim();
    var ref   = form.querySelector('[name="reference"]').value.trim();
    var watch = (brand || model) ? (brand + ' ' + model).trim() + (ref ? ' · ' + ref : '') : '';

    var year = form.querySelector('[name="manufacture_year"]').value.trim();
    var cat  = form.querySelector('[name="category"]');
    var catT = cat && cat.value ? cat.options[cat.selectedIndex].text : '';

    var cond = form.querySelector('input[name="condition"]:checked');
    var condT = cond ? cond.closest('.wz-row').querySelector('.wz-row-t').textContent : '';

    var boxr = form.querySelector('input[name="box_papers"]:checked');
    var boxT = boxr ? boxr.closest('.wz-row').querySelector('.wz-row-t').textContent : '';

    var nPhotos = picked.length + keptImages();

    box.appendChild(line(T.watch,  watch, 1));
    box.appendChild(line(T.year,   year || T.none, 1));
    box.appendChild(line(T.cat,    catT || T.none, 1));
    box.appendChild(line(T.cond,   condT, 2));
    box.appendChild(line(T.box,    boxT, 3));
    box.appendChild(line(T.photos, nPhotos ? (nPhotos + T.nPhotos) : '', 4));
  }

  /* ── Init ───────────────────────────────────────── */
  var bs = document.getElementById('brandSelect'); if (bs) toggleBrand(bs);
  var bp = form.querySelector('input[name="box_papers"]:checked'); if (bp) toggleDocs(bp.value);
  renderSlots();
  settle();
  render();
  document.getElementById('photoError').classList.remove('is-on');
  document.getElementById('condError').classList.remove('is-on');

  /* ── Popup video ────────────────────────────────── */
  var modal  = document.getElementById('wzModal');
  var video  = document.getElementById('wzVideo');
  var unmute = document.getElementById('wzUnmute');

  function openVideo() {
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    unmute.classList.remove('is-on');
    try {
      video.currentTime = 0;
      /* Il popup si apre da un click, quindi l'audio e' consentito.
         Se un browser lo blocca comunque, si riparte muti e si offre
         un tasto per riattivare il suono. */
      video.muted  = false;
      video.volume = 1;
      var p = video.play();
      if (p && p.catch) p.catch(function () {
        video.muted = true;
        unmute.classList.add('is-on');
        var q = video.play();
        if (q && q.catch) q.catch(function () {});
      });
    } catch (err) {}
  }

  function closeVideo() {
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
    unmute.classList.remove('is-on');
    try { video.pause(); video.currentTime = 0; } catch (err) {}
  }

  unmute.addEventListener('click', function () {
    video.muted = false;
    video.volume = 1;
    unmute.classList.remove('is-on');
    var p = video.play();
    if (p && p.catch) p.catch(function () {});
  });

  document.getElementById('wzHelpBtn').addEventListener('click', openVideo);
  document.getElementById('wzModalX').addEventListener('click', closeVideo);
  document.getElementById('wzModalBd').addEventListener('click', closeVideo);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) closeVideo();
  });

  var del = document.getElementById('wzDelete');
  if (del) {
    del.addEventListener('click', function () {
      var msg = LOC_ES ? '¿Eliminar este reloj definitivamente?' : 'Delete this watch permanently?';
      if (confirm(msg)) document.getElementById('deleteWatchForm').submit();
    });
  }
})();
</script>
@endpush

@endsection
