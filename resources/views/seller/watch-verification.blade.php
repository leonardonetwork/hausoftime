@extends('layouts.seller')
@section('title', app()->getLocale()==='es' ? 'Verificación de posesión' : 'Ownership verification')

@php
  $es = app()->getLocale() === 'es';

  [$vh, $vm] = array_map('intval', explode(':', $watch->verification_time));

  /* Angoli delle lancette: 0° = ore 12, si gira in senso orario */
  $minAngle  = $vm * 6;
  $hourAngle = (($vh % 12) * 30) + ($vm * 0.5);

  $hand = function (float $angle, float $len) {
      $rad = deg2rad($angle);
      return [110 + $len * sin($rad), 110 - $len * cos($rad)];
  };
  [$mx, $my] = $hand($minAngle, 62);
  [$hx, $hy] = $hand($hourAngle, 42);
@endphp

@section('seller-content')

<div class="wz-shell">

<div class="wz-top">
  <div>
    <h1 class="page-title" style="margin-bottom:2px;">
      {{ $es ? 'Verificación de posesión' : 'Ownership verification' }}
    </h1>
    <p class="page-subtitle" style="margin-bottom:0;">{{ $watch->brand }} · {{ $watch->model }}</p>
  </div>
  <span class="vf-flag">{{ $es ? 'Último paso' : 'Final step' }}</span>
</div>

<div class="wz-split" style="margin-top:26px;">

  {{-- ── Pannello: l'ora da impostare ─────────────────── --}}
  <aside class="wz-aside">
    <div class="wz-aside-inner">
      <div class="wz-art is-active">
        <div class="wz-art-svg">
          <svg viewBox="0 0 220 220" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
               aria-label="{{ $es ? 'Reloj marcando las' : 'Clock showing' }} {{ $watch->verification_time }}">
            {{-- Cassa --}}
            <path d="M78 42V26a10 10 0 0 1 10-10h44a10 10 0 0 1 10 10v16M78 178v16a10 10 0 0 0 10 10h44a10 10 0 0 0 10-10v-16"
                  stroke="#f5f3ee" stroke-opacity=".22" stroke-width="1.6" stroke-linecap="round"/>
            <circle cx="110" cy="110" r="86" stroke="#f5f3ee" stroke-opacity=".16" stroke-width="1.4"/>
            <circle cx="110" cy="110" r="78" stroke="#c69c6d" stroke-width="1.8"/>
            <path d="M196 100h10v20h-10" stroke="#f5f3ee" stroke-opacity=".22" stroke-width="1.6" stroke-linejoin="round"/>

            {{-- Indici --}}
            @for($i = 0; $i < 12; $i++)
              @php
                $a = deg2rad($i * 30);
                $r1 = $i % 3 === 0 ? 60 : 66; $r2 = 70;
              @endphp
              <line x1="{{ 110 + $r1 * sin($a) }}" y1="{{ 110 - $r1 * cos($a) }}"
                    x2="{{ 110 + $r2 * sin($a) }}" y2="{{ 110 - $r2 * cos($a) }}"
                    stroke="#f5f3ee" stroke-opacity="{{ $i % 3 === 0 ? '.55' : '.28' }}"
                    stroke-width="{{ $i % 3 === 0 ? 2.4 : 1.4 }}" stroke-linecap="round"/>
            @endfor

            {{-- Lancette all'ora richiesta --}}
            <line x1="110" y1="110" x2="{{ round($hx, 2) }}" y2="{{ round($hy, 2) }}"
                  stroke="#c69c6d" stroke-width="4.6" stroke-linecap="round"/>
            <line x1="110" y1="110" x2="{{ round($mx, 2) }}" y2="{{ round($my, 2) }}"
                  stroke="#c69c6d" stroke-width="3" stroke-linecap="round"/>
            <circle cx="110" cy="110" r="4.6" fill="#c69c6d"/>
          </svg>
        </div>

        <div class="wz-art-eyebrow">{{ $es ? 'Pon el reloj en esta hora' : 'Set the watch to this time' }}</div>
        <div class="vf-time">{{ $watch->verification_time }}</div>
        <p class="wz-art-sub">
          {{ $es
            ? 'Cada reloj recibe una hora distinta y aleatoria. Así verificamos que la pieza está realmente en tus manos.'
            : 'Each watch gets its own random time. This is how we verify the piece is really in your hands.' }}
        </p>
      </div>
    </div>
  </aside>

  {{-- ── Istruzioni e caricamento ─────────────────────── --}}
  <div class="wz-main">
    <form method="POST" action="{{ route('seller.watches.verification.store', $watch) }}"
          enctype="multipart/form-data" id="vfForm" novalidate>
      @csrf

      <div class="card">
        <div class="wf-sh">
          <span class="wf-sl">{{ $es ? 'Cómo hacerlo' : 'How to do it' }}</span>
          <div class="wf-sline"></div>
        </div>

        <ol class="vf-steps">
          <li>
            <span class="vf-n">1</span>
            <span>{{ $es
              ? 'Ajusta la hora del reloj hasta que marque exactamente '
              : 'Set the watch until it reads exactly ' }}<b>{{ $watch->verification_time }}</b>.</span>
          </li>
          <li>
            <span class="vf-n">2</span>
            <span>{{ $es
              ? 'Haz una foto nítida de la esfera completa, con buena luz y sin reflejos.'
              : 'Take a sharp photo of the whole dial, well lit and without reflections.' }}</span>
          </li>
          <li>
            <span class="vf-n">3</span>
            <span>{{ $es
              ? 'Súbela aquí abajo. La revisará el equipo de Haus of Time; no se publicará en el anuncio.'
              : 'Upload it below. The Haus of Time team will review it; it will not appear in the listing.' }}</span>
          </li>
        </ol>
      </div>

      <div class="card" style="margin-top:16px;">
        <div class="wf-sh">
          <span class="wf-sl">{{ $es ? 'Foto de verificación' : 'Verification photo' }}</span>
          <div class="wf-sline"></div>
        </div>

        <input type="file" name="verification_photo" id="vfInput"
               accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none">

        <div class="vf-drop" id="vfDrop">
          <div class="vf-drop-empty" id="vfEmpty">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 7h3l1.5-2h7L17 7h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z"/>
              <circle cx="12" cy="13" r="3.6"/>
            </svg>
            <span class="vf-drop-t">{{ $es ? 'Haz clic o arrastra la foto' : 'Click or drag the photo here' }}</span>
            <span class="vf-drop-s">JPG, PNG {{ $es ? 'o' : 'or' }} WEBP · 10 MB</span>
          </div>
          <img id="vfPreview" alt="">
          <button type="button" class="vf-x" id="vfRemove" aria-label="{{ $es ? 'Quitar' : 'Remove' }}">&times;</button>
        </div>

        @error('verification_photo')<p class="field-error" style="margin-top:10px;">{{ $message }}</p>@enderror
        <p class="wz-err" id="vfError">{{ $es ? 'Sube la foto de verificación para terminar' : 'Upload the verification photo to finish' }}</p>
      </div>

      <div class="wz-nav">
        <a href="{{ route('seller.watches') }}" class="btn btn--ghost">
          {{ $es ? 'Terminar más tarde' : 'Finish later' }}
        </a>
        <div style="flex:1"></div>
        <button type="submit" class="btn btn--dark btn--lg" id="vfSubmit">
          {{ $es ? 'Enviar y finalizar' : 'Submit and finish' }}
        </button>
      </div>

      <p class="vf-note">
        {{ $es
          ? 'Hasta que envíes esta foto el reloj queda como borrador y no entra en revisión.'
          : 'Until you send this photo the watch stays as a draft and does not enter review.' }}
      </p>
    </form>
  </div>
</div>
</div>

@push('styles')
<style>
.wz-shell{position:relative;z-index:1;max-width:1120px;margin:0 auto;}
.wz-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;}
.wz-split{display:grid;grid-template-columns:330px minmax(0,1fr);gap:28px;align-items:start;}
.wz-main{min-width:0;}
.wz-aside{position:sticky;top:24px;border-radius:5px;overflow:hidden;background:#0f0e0b;border:1px solid rgba(198,156,109,.20);}
.wz-aside::before{content:'';position:absolute;inset:0;background:radial-gradient(115% 75% at 50% 16%,rgba(198,156,109,.18),rgba(198,156,109,.05) 46%,transparent 72%);}
.wz-aside::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.04),transparent 52%);}
.wz-aside-inner{position:relative;z-index:1;padding:34px 30px 34px;}
.wz-art-svg{margin:0 auto 22px;padding-bottom:24px;border-bottom:1px solid rgba(198,156,109,.16);}
.wz-art-svg svg{width:100%;max-width:230px;height:auto;display:block;margin:0 auto;}
.wz-art-eyebrow{font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--s-gold);margin-bottom:8px;}
.wz-art-sub{font-size:12px;line-height:1.6;color:rgba(245,243,238,.62);margin:0;}
.vf-time{font-size:46px;font-weight:300;letter-spacing:.04em;color:#f5f3ee;line-height:1;margin-bottom:14px;}

.vf-flag{align-self:center;font-size:9px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;
  color:var(--s-gold);border:1px solid rgba(198,156,109,.4);border-radius:999px;padding:7px 14px;white-space:nowrap;}

.wf-sh{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
.wf-sl{font-size:10px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--s-text);white-space:nowrap;flex-shrink:0;}
.wf-sline{flex:1;height:1px;background:linear-gradient(to right,var(--s-border),transparent);}

.vf-steps{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:15px;}
.vf-steps li{display:flex;gap:14px;align-items:flex-start;font-size:13px;line-height:1.6;color:var(--s-muted);}
.vf-steps b{color:var(--s-gold);font-weight:600;letter-spacing:.04em;}
.vf-n{flex-shrink:0;width:23px;height:23px;border-radius:50%;border:1px solid var(--s-border);
  display:flex;align-items:center;justify-content:center;font-size:11px;color:var(--s-subtle);margin-top:1px;}

.vf-drop{position:relative;border:1.5px dashed var(--s-border);border-radius:4px;background:rgba(255,255,255,.025);
  min-height:230px;display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden;transition:border-color .2s;}
.vf-drop:hover,.vf-drop.is-over{border-color:var(--s-gold);}
.vf-drop.is-filled{border-style:solid;cursor:default;}
.vf-drop-empty{display:flex;flex-direction:column;align-items:center;gap:9px;color:var(--s-subtle);padding:26px;text-align:center;}
.vf-drop.is-filled .vf-drop-empty{display:none;}
.vf-drop-t{font-size:12.5px;}
.vf-drop-s{font-size:10px;letter-spacing:.1em;text-transform:uppercase;}
#vfPreview{display:none;width:100%;max-height:440px;object-fit:contain;}
.vf-drop.is-filled #vfPreview{display:block;}
.vf-x{display:none;position:absolute;top:9px;right:9px;width:28px;height:28px;border-radius:50%;
  background:rgba(10,9,7,.8);border:1px solid rgba(245,243,238,.25);color:#f5f3ee;font-size:17px;line-height:1;
  cursor:pointer;align-items:center;justify-content:center;}
.vf-drop.is-filled .vf-x{display:flex;}
.vf-x:hover{background:var(--s-danger);border-color:var(--s-danger);}

.wz-err{display:none;font-size:11.5px;color:var(--s-danger);margin-top:12px;}
.wz-err.is-on{display:block;}
.wz-nav{display:flex;gap:10px;align-items:center;margin-top:24px;flex-wrap:wrap;}
.vf-note{font-size:11px;color:var(--s-subtle);line-height:1.6;margin-top:16px;}

@media(max-width:1000px){
  .wz-split{grid-template-columns:1fr;gap:20px;}
  .wz-aside{position:static;}
  .wz-art-svg{max-width:200px;}
}
@media(max-width:640px){
  .vf-time{font-size:38px;}
  .wz-nav .btn{flex:1;min-width:130px;justify-content:center;}
}
</style>
@endpush

@push('scripts')
<script>
(function () {
  var ES     = {{ $es ? 'true' : 'false' }};
  var input  = document.getElementById('vfInput');
  var drop   = document.getElementById('vfDrop');
  var prev   = document.getElementById('vfPreview');
  var rm     = document.getElementById('vfRemove');
  var err    = document.getElementById('vfError');
  var form   = document.getElementById('vfForm');
  var submit = document.getElementById('vfSubmit');

  function show(file) {
    if (!file || !/^image\//.test(file.type)) return;
    prev.src = URL.createObjectURL(file);
    prev.onload = function () { URL.revokeObjectURL(this.src); };
    drop.classList.add('is-filled');
    err.classList.remove('is-on');
  }

  function clear() {
    input.value = '';
    prev.removeAttribute('src');
    drop.classList.remove('is-filled');
  }

  drop.addEventListener('click', function () {
    if (!drop.classList.contains('is-filled')) input.click();
  });
  input.addEventListener('change', function () { show(input.files[0]); });
  rm.addEventListener('click', function (e) { e.stopPropagation(); clear(); });

  ['dragenter', 'dragover'].forEach(function (ev) {
    drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); });
  });
  drop.addEventListener('drop', function (e) {
    if (!e.dataTransfer || !e.dataTransfer.files.length) return;
    if (typeof DataTransfer !== 'undefined') {
      var dt = new DataTransfer();
      dt.items.add(e.dataTransfer.files[0]);
      input.files = dt.files;
    }
    show(e.dataTransfer.files[0]);
  });

  form.addEventListener('submit', function (e) {
    if (!input.files || !input.files.length) {
      e.preventDefault();
      err.classList.add('is-on');
      drop.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    submit.disabled = true;
    submit.textContent = ES ? 'Enviando…' : 'Sending…';
  });
})();
</script>
@endpush

@endsection
