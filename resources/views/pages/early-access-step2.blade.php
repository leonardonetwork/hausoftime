@extends('layouts.app')
@section('title', app()->getLocale() === 'es' ? 'Acceso Anticipado — Paso 2' : 'Early Access — Step 2')

@section('content')

<div class="page-hero" style="min-height:auto;padding:120px 60px 56px;">
  <div style="max-width:900px;margin:0 auto;width:100%;">
    <div class="page-hero__label">Early Access</div>
    <h1 class="page-hero__title" style="font-size:clamp(28px,3.5vw,48px);">
      {{ app()->getLocale() === 'es' ? 'Solicitar Acceso Anticipado' : 'Request Early Access' }}
    </h1>
    <p class="page-hero__desc">
      {{ app()->getLocale() === 'es'
        ? 'Plataforma curada para la venta segura de relojes de alta gama — Madrid'
        : 'Curated platform for the secure sale of high-end watches — Madrid' }}
    </p>
  </div>
</div>

<div class="ea-page">
  <div class="ea-wrap">

    {{-- Progress --}}
    <div class="ea-progress">
      <span class="ea-progress-label">Early Access</span>
      <div class="ea-progress-track"><div class="ea-progress-fill" style="width:100%"></div></div>
      <span class="ea-progress-step">{{ app()->getLocale() === 'es' ? 'Paso 2 de 2' : 'Step 2 of 2' }} · 100%</span>
    </div>

    <div class="ea-card">

      @if($errors->any())
        <div class="ea-error">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      @php $type = session('early_access_step1.type', 'seller'); @endphp

      <form method="POST" action="{{ route('early-access.seller-step2-submit') }}">
        @csrf

        {{-- ══ INVENTARIO (solo seller + both) ══════════════════ --}}
        @if(in_array($type, ['seller','both']))

        <div class="ea-section">
          <div class="ea-section-head">
            <span class="ea-section-name">{{ app()->getLocale() === 'es' ? 'Tu Inventario' : 'Your Inventory' }}</span>
            <div class="ea-section-line"></div>
          </div>

          {{-- Marcas --}}
          <div class="ea-field" style="margin-bottom:32px;">
            <label class="ea-label">
              {{ app()->getLocale() === 'es' ? '¿Qué marcas tienes?' : 'Which brands do you have?' }}
              <span class="ea-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
            </label>
            @php
            $dbBrands = \App\Models\WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->pluck('name_es')->toArray();
            $defaultBrands = ['Rolex','Patek Philippe','Audemars Piguet','Cartier','Omega','IWC','Breitling','Hublot','Richard Mille','Vacheron Constantin','A. Lange & Söhne','Jaeger-LeCoultre','Panerai','Tudor','Tag Heuer'];
            $brands = count($dbBrands) > 0 ? $dbBrands : $defaultBrands;
            @endphp
            <div class="ea-brands-grid" id="brandsGrid">
              @foreach($brands as $i => $brand)
              <label class="ea-check-item {{ $i >= 8 ? 'ea-brand-hidden' : '' }}">
                <input type="checkbox" name="watch_brands[]" value="{{ $brand }}"
                       {{ is_array(old('watch_brands')) && in_array($brand, old('watch_brands')) ? 'checked' : '' }}>
                <span class="ea-check-box"></span>
                <span class="ea-check-text">{{ $brand }}</span>
              </label>
              @endforeach
            </div>
            @if(count($brands) > 8)
            <button type="button" id="showMoreBrands" class="ea-show-more" onclick="toggleBrands()">
              {{ app()->getLocale() === 'es' ? 'Ver más' : 'Show more' }} —
            </button>
            @endif

            {{-- Otras marcas --}}
            <div class="ea-other" data-other>
              <button type="button" class="ea-other-add" data-other-add>
                + {{ app()->getLocale() === 'es' ? 'Otra marca' : 'Other brand' }}
              </button>
              <div class="ea-other-box" data-other-box>
                <div class="ea-other-row">
                  <input type="text" class="ea-input ea-other-input" data-other-input
                         maxlength="100" autocomplete="off"
                         placeholder="{{ app()->getLocale() === 'es' ? 'Escribe la marca' : 'Type the brand' }}">
                  <button type="button" class="ea-other-ok" data-other-ok>
                    {{ app()->getLocale() === 'es' ? 'Añadir' : 'Add' }}
                  </button>
                </div>
                <p class="ea-other-hint">
                  {{ app()->getLocale() === 'es' ? 'Pulsa Intro para añadir varias marcas.' : 'Press Enter to add several brands.' }}
                </p>
              </div>
              <div class="ea-other-tags" data-other-tags></div>
            </div>
          </div>

          {{-- Cuántos relojes --}}
          <div class="ea-field">
            <label class="ea-label">
              {{ app()->getLocale() === 'es' ? '¿Cuántos relojes planeas listar en el lanzamiento?' : 'How many watches do you plan to list at launch?' }}
              <span class="ea-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
            </label>
            <input type="number" name="watch_count" value="{{ old('watch_count') }}" required
                   class="ea-input" min="1" max="999" placeholder="ej: 5"
                   style="max-width:180px;">
          </div>
        </div>

        @endif

        {{-- ══ COMPRADOR solo: info extra ══════════════════ --}}
        @if($type === 'buyer')
        <div class="ea-section">
          <div class="ea-section-head">
            <span class="ea-section-name">{{ app()->getLocale() === 'es' ? 'Tus Preferencias' : 'Your Preferences' }}</span>
            <div class="ea-section-line"></div>
          </div>
          <div class="ea-field">
            <label class="ea-label">{{ app()->getLocale() === 'es' ? '¿Qué marcas te interesan?' : 'Which brands interest you?' }}</label>
            @php
            $dbBrands = \App\Models\WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->pluck('name_es')->toArray();
            $defaultBrands = ['Rolex','Patek Philippe','Audemars Piguet','Cartier','Omega','IWC','Breitling','Hublot','Richard Mille','Vacheron Constantin','A. Lange & Söhne','Jaeger-LeCoultre','Panerai','Tudor','Tag Heuer'];
            $brands = count($dbBrands) > 0 ? $dbBrands : $defaultBrands;
            @endphp
            <div class="ea-brands-grid">
              @foreach($brands as $brand)
              <label class="ea-check-item">
                <input type="checkbox" name="watch_brands[]" value="{{ $brand }}"
                       {{ is_array(old('watch_brands')) && in_array($brand, old('watch_brands')) ? 'checked' : '' }}>
                <span class="ea-check-box"></span>
                <span class="ea-check-text">{{ $brand }}</span>
              </label>
              @endforeach
            </div>

            {{-- Otras marcas --}}
            <div class="ea-other" data-other>
              <button type="button" class="ea-other-add" data-other-add>
                + {{ app()->getLocale() === 'es' ? 'Otra marca' : 'Other brand' }}
              </button>
              <div class="ea-other-box" data-other-box>
                <div class="ea-other-row">
                  <input type="text" class="ea-input ea-other-input" data-other-input
                         maxlength="100" autocomplete="off"
                         placeholder="{{ app()->getLocale() === 'es' ? 'Escribe la marca' : 'Type the brand' }}">
                  <button type="button" class="ea-other-ok" data-other-ok>
                    {{ app()->getLocale() === 'es' ? 'Añadir' : 'Add' }}
                  </button>
                </div>
                <p class="ea-other-hint">
                  {{ app()->getLocale() === 'es' ? 'Pulsa Intro para añadir varias marcas.' : 'Press Enter to add several brands.' }}
                </p>
              </div>
              <div class="ea-other-tags" data-other-tags></div>
            </div>
          </div>
        </div>
        @endif

        {{-- ══ TU CUENTA (seller + both) ══════════════════ --}}
        @if(in_array($type, ['seller','both']))
        <div class="ea-section">
          <div class="ea-section-head">
            <span class="ea-section-name">{{ app()->getLocale() === 'es' ? 'Tu Cuenta' : 'Your Account' }}</span>
            <div class="ea-section-line"></div>
          </div>
          <p style="font-family:var(--font-body);font-size:13px;font-weight:300;color:var(--light-muted);line-height:1.75;margin-bottom:28px;">
            {{ app()->getLocale() === 'es'
              ? 'Crea tu contraseña para acceder a tu panel de vendedor una vez aprobada tu solicitud.'
              : 'Create your password to access your seller panel once your application is approved.' }}
          </p>
          <div class="ea-row-2">
            <div class="ea-field">
              <label class="ea-label">
                {{ app()->getLocale() === 'es' ? 'Contraseña' : 'Password' }}
                <span class="ea-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
              </label>
              <input type="password" name="password" required class="ea-input"
                     placeholder="••••••••" minlength="8">
            </div>
            <div class="ea-field">
              <label class="ea-label">
                {{ app()->getLocale() === 'es' ? 'Confirmar contraseña' : 'Confirm password' }}
                <span class="ea-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
              </label>
              <input type="password" name="password_confirmation" required class="ea-input"
                     placeholder="••••••••" minlength="8">
            </div>
          </div>
        </div>
        @endif

        {{-- ══ POLÍTICA DE PRIVACIDAD ══════════════════════ --}}
        <div class="ea-section" style="margin-bottom:36px;">
          <div class="ea-section-head">
            <span class="ea-section-name">{{ app()->getLocale() === 'es' ? 'Política de Privacidad' : 'Privacy Policy' }}</span>
            <div class="ea-section-line"></div>
          </div>
          <div style="display:flex;align-items:flex-start;gap:12px;">
            <label class="ea-check-wrap">
              <input type="checkbox" name="accept_terms" required>
              <span class="ea-checkbox"></span>
            </label>
            <span class="ea-terms-text">
              @php
                $termsPage = \App\Models\Page::where('slug','terminos-y-condiciones')->first();
                $privPage  = \App\Models\Page::where('slug','privacidad')->first();
              @endphp
              {{ app()->getLocale() === 'es' ? 'He leído y acepto los' : 'I have read and accept the' }}
              @if($termsPage)
                <a href="{{ url('/terminos-y-condiciones') }}" target="_blank" class="ea-terms-link">
                  {{ app()->getLocale() === 'en' && $termsPage->title_en ? $termsPage->title_en : $termsPage->title_es }}
                </a>
              @endif
              {{ app()->getLocale() === 'es' ? 'y la' : 'and the' }}
              @if($privPage)
                <a href="{{ url('/privacidad') }}" target="_blank" class="ea-terms-link">
                  {{ app()->getLocale() === 'en' && $privPage->title_en ? $privPage->title_en : $privPage->title_es }}
                </a>
              @endif.
            </span>
          </div>
        </div>

        {{-- Buttons --}}
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
          <a href="{{ route('early-access.index') }}"
             style="display:flex;align-items:center;justify-content:center;min-height:64px;border:1px solid var(--light-border);font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.35em;text-transform:uppercase;color:var(--light-muted);text-decoration:none;transition:border-color .25s;">
            ← {{ app()->getLocale() === 'es' ? 'Anterior' : 'Back' }}
          </a>
          <button type="submit" class="ea-btn">
            {{ app()->getLocale() === 'es' ? 'Enviar solicitud' : 'Submit request' }} →
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
.ea-page{background:var(--light-bg);padding:72px 0 110px;}
.ea-wrap{max-width:900px;margin:0 auto;padding:0 40px;}
.ea-progress{display:flex;align-items:center;gap:16px;margin-bottom:44px;}
.ea-progress-label{font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.35em;text-transform:uppercase;color:var(--light-subtle);white-space:nowrap;}
.ea-progress-track{flex:1;height:1px;background:var(--light-border);position:relative;}
.ea-progress-fill{position:absolute;left:0;top:0;height:100%;background:var(--light-text);}
.ea-progress-step{font-family:var(--font-body);font-size:11px;color:var(--light-subtle);white-space:nowrap;}
.ea-card{background:#fff;border:1px solid var(--light-border);padding:60px 64px;}
.ea-section{margin-bottom:44px;}
.ea-section:last-of-type{margin-bottom:36px;}
.ea-section-head{display:flex;align-items:center;gap:14px;margin-bottom:28px;}
.ea-section-name{font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:var(--light-text);white-space:nowrap;flex-shrink:0;}
.ea-section-line{flex:1;height:2px;background:linear-gradient(to right,#c8c3bc,transparent);}
.ea-field{margin-bottom:28px;}
.ea-field:last-child{margin-bottom:0;}
.ea-label{display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:12px;}
.ea-req{background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.1em;padding:2px 9px;border-radius:999px;line-height:1.7;flex-shrink:0;}
.ea-input{width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;border-radius:0;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;}
.ea-input:focus{border-bottom-color:#111;}
.ea-input::placeholder{color:#c0bdb8;}
.ea-row-2{display:grid;grid-template-columns:1fr 1fr;gap:28px;}
.ea-error{background:#fdf5f5;border-left:3px solid #c0392b;padding:14px 20px;margin-bottom:32px;font-family:var(--font-body);font-size:13px;font-weight:300;color:#922b21;line-height:1.7;}
.ea-btn{display:flex;align-items:center;justify-content:center;width:100%;min-height:64px;background:#111;color:#fff;border:none;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.38em;text-transform:uppercase;cursor:pointer;transition:background .25s;}
.ea-btn:hover{background:#2a2a28;}

/* Brands grid */
.ea-brands-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px 20px;margin-bottom:8px;}

/* Otra marca */
.ea-other{margin-top:14px;}
.ea-other-add{background:none;border:none;padding:0;cursor:pointer;font-family:inherit;
  font-size:12px;letter-spacing:.06em;color:#111;text-decoration:underline;text-underline-offset:3px;}
.ea-other-add:hover{opacity:.6;}
.ea-other.is-open .ea-other-add{display:none;}
.ea-other-box{display:none;}
.ea-other.is-open .ea-other-box{display:block;}
.ea-other-row{display:flex;gap:8px;align-items:stretch;max-width:420px;}
.ea-other-input{flex:1;min-width:0;margin:0;}
.ea-other-ok{flex-shrink:0;padding:0 18px;border:1px solid #111;background:#111;color:#fff;
  cursor:pointer;font-family:inherit;font-size:11px;font-weight:500;letter-spacing:.12em;text-transform:uppercase;}
.ea-other-ok:hover{background:#333;border-color:#333;}
.ea-other-hint{font-size:11px;color:#999;margin:6px 0 0;}
.ea-other-tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;}
.ea-other-tag{display:inline-flex;align-items:center;gap:8px;padding:6px 8px 6px 12px;
  border:1px solid #111;font-size:12px;line-height:1;}
.ea-other-tag button{background:none;border:none;cursor:pointer;font-size:15px;line-height:1;
  color:#999;padding:0 2px;}
.ea-other-tag button:hover{color:#9b3333;}
.ea-brand-hidden{display:none;}
.ea-check-item{display:flex;align-items:center;gap:10px;cursor:pointer;font-family:var(--font-body);font-size:14px;font-weight:300;color:var(--light-muted);padding:6px 0;transition:color .2s;}
.ea-check-item:hover{color:var(--light-text);}
.ea-check-item input{display:none;}
.ea-check-box{width:16px;height:16px;border:1px solid #ccc;flex-shrink:0;transition:all .2s;background:#fff;}
.ea-check-item input:checked+.ea-check-box{background:#111;border-color:#111;}
.ea-check-item input:checked+.ea-check-box::after{content:'';display:block;width:4px;height:8px;border:1.5px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);margin:2px auto 0;}
.ea-check-item input:checked~.ea-check-text{color:var(--light-text);font-weight:400;}
.ea-check-text{line-height:1.4;}
.ea-show-more{background:none;border:none;font-family:var(--font-body);font-size:11px;font-weight:600;letter-spacing:.12em;color:var(--light-subtle);cursor:pointer;padding:8px 0;transition:color .2s;}
.ea-show-more:hover{color:var(--light-text);}

/* Privacy checkbox */
.ea-check-wrap{position:relative;cursor:pointer;flex-shrink:0;margin-top:2px;}
.ea-check-wrap input{position:absolute;opacity:0;width:0;height:0;}
.ea-checkbox{display:block;width:16px;height:16px;border:1px solid #ccc;transition:all .2s;}
.ea-check-wrap input:checked+.ea-checkbox{background:#111;border-color:#111;}
.ea-check-wrap input:checked+.ea-checkbox::after{content:'';display:block;width:4px;height:8px;border:1.5px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);margin:2px auto 0;}
.ea-terms-text{font-family:var(--font-body);font-size:12px;font-weight:300;color:var(--light-muted);line-height:1.85;}
.ea-terms-link{color:var(--light-text);font-weight:400;text-decoration:underline;text-underline-offset:3px;}

@media(max-width:640px){
  .ea-wrap{padding:0 20px;}.ea-page{padding:48px 0 72px;}.ea-card{padding:32px 24px;}
  .ea-brands-grid{grid-template-columns:1fr 1fr;}
  .ea-row-2{grid-template-columns:1fr;gap:0;}
  div[style*="grid-template-columns:1fr 2fr"]{grid-template-columns:1fr!important;}
  div[style*="min-height:auto"]{padding:100px 24px 40px!important;}
}
</style>
@endpush

@push('scripts')
<script>
function toggleBrands() {
  const hidden = document.querySelectorAll('.ea-brand-hidden');
  const btn = document.getElementById('showMoreBrands');
  const isHidden = hidden[0]?.style.display !== 'flex';
  hidden.forEach(el => el.style.display = isHidden ? 'flex' : 'none');
  btn.textContent = isHidden
    ? '{{ app()->getLocale() === "es" ? "Ver menos —" : "Show less —" }}'
    : '{{ app()->getLocale() === "es" ? "Ver más —" : "Show more —" }}';
}

/* ── Otras marcas ──────────────────────────────────────────
   Ogni marca aggiunta genera un input hidden watch_brands[],
   quindi arriva al controller insieme alle checkbox. */
(function () {
  var ES = {{ app()->getLocale() === 'es' ? 'true' : 'false' }};

  document.querySelectorAll('[data-other]').forEach(function (wrap) {
    var addBtn = wrap.querySelector('[data-other-add]');
    var input  = wrap.querySelector('[data-other-input]');
    var okBtn  = wrap.querySelector('[data-other-ok]');
    var tags   = wrap.querySelector('[data-other-tags]');
    var field  = wrap.closest('.ea-field');
    var grid   = field ? field.querySelector('.ea-brands-grid') : null;

    function exists(name) {
      var low = name.toLowerCase(), dup = false;
      if (grid) {
        grid.querySelectorAll('input[type="checkbox"]').forEach(function (c) {
          if (c.value.toLowerCase() === low) { c.checked = true; dup = true; }
        });
      }
      tags.querySelectorAll('input[type="hidden"]').forEach(function (h) {
        if (h.value.toLowerCase() === low) dup = true;
      });
      return dup;
    }

    function add() {
      var name = input.value.trim().replace(/\s+/g, ' ');
      if (!name) { input.focus(); return; }
      if (exists(name)) { input.value = ''; input.focus(); return; }

      var tag = document.createElement('span');
      tag.className = 'ea-other-tag';
      tag.appendChild(document.createTextNode(name));

      var hid = document.createElement('input');
      hid.type = 'hidden'; hid.name = 'watch_brands[]'; hid.value = name;
      tag.appendChild(hid);

      var x = document.createElement('button');
      x.type = 'button';
      x.innerHTML = '&times;';
      x.setAttribute('aria-label', ES ? 'Quitar' : 'Remove');
      x.addEventListener('click', function () { tag.remove(); });
      tag.appendChild(x);

      tags.appendChild(tag);
      input.value = '';
      input.focus();
    }

    addBtn.addEventListener('click', function () {
      wrap.classList.add('is-open');
      input.focus();
    });
    okBtn.addEventListener('click', add);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); add(); }
    });
    /* Chi scrive la marca e invia senza premere Añadir non deve perderla */
    var form = wrap.closest('form');
    if (form) form.addEventListener('submit', function () {
      if (input.value.trim()) add();
    });
  });
})();
</script>
@endpush
@endsection
