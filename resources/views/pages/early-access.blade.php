@extends(\App\Support\Look::layout('layouts.app'))
@section('title', app()->getLocale() === 'es' ? 'Acceso Anticipado — Haus of Time' : 'Early Access — Haus of Time')

@section('content')

{{-- Hero compatto --}}
<div class="page-hero" style="min-height:auto;padding:120px 60px 56px;">
  <div style="max-width:900px;margin:0 auto;width:100%;">
    <div class="page-hero__label">{{ \App\Support\Look::t('Early Access', app()->getLocale() === 'es' ? 'Vende tu reloj' : 'Sell your watch') }}</div>
    <h1 class="page-hero__title" style="font-size:clamp(28px,3.5vw,48px);">
      {{ \App\Support\Look::t(app()->getLocale() === 'es' ? 'Solicitar Acceso Anticipado' : 'Request Early Access', app()->getLocale() === 'es' ? 'Crea tu cuenta de vendedor' : 'Create your seller account') }}
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
      <div class="ea-progress-track"><div class="ea-progress-fill" style="width:50%"></div></div>
      <span class="ea-progress-step">{{ app()->getLocale() === 'es' ? 'Paso 1 de 2' : 'Step 1 of 2' }} · 50%</span>
    </div>

    {{-- Card --}}
    <div class="ea-card">

      @if(session('error'))
        <div class="ea-error">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="ea-error">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('early-access.step1') }}">
        @csrf

        {{-- ── TIPO DE ACCESO ──────────────────────────── --}}
        <div class="ea-section">
          <div class="ea-section-head">
            <span class="ea-section-name">{{ app()->getLocale() === 'es' ? 'Tipo de Acceso' : 'Access Type' }}</span>
            <div class="ea-section-line"></div>
          </div>

          <div class="ea-type-grid ea-type-grid--3">
            <label class="ea-type-card {{ old('type','seller') === 'seller' ? 'is-active' : '' }}">
              <input type="radio" name="type" value="seller"
                     {{ old('type','seller') === 'seller' ? 'checked' : '' }}>
              <div class="ea-type-dot"></div>
              <div>
                <div class="ea-type-title">{{ app()->getLocale() === 'es' ? 'Vender relojes' : 'Sell watches' }}</div>
                <div class="ea-type-sub">Founding Seller · 4%</div>
              </div>
            </label>
            <label class="ea-type-card {{ old('type') === 'buyer' ? 'is-active' : '' }}">
              <input type="radio" name="type" value="buyer"
                     {{ old('type') === 'buyer' ? 'checked' : '' }}>
              <div class="ea-type-dot"></div>
              <div>
                <div class="ea-type-title">{{ app()->getLocale() === 'es' ? 'Comprar relojes' : 'Buy watches' }}</div>
              </div>
            </label>
            <label class="ea-type-card {{ old('type') === 'both' ? 'is-active' : '' }}">
              <input type="radio" name="type" value="both"
                     {{ old('type') === 'both' ? 'checked' : '' }}>
              <div class="ea-type-dot"></div>
              <div>
                <div class="ea-type-title">{{ app()->getLocale() === 'es' ? 'Ambos' : 'Both' }}</div>
                <div class="ea-type-sub">{{ app()->getLocale() === 'es' ? 'Vender y comprar' : 'Sell and buy' }}</div>
              </div>
            </label>
          </div>
        </div>

        {{-- ── IDENTIDAD ───────────────────────────────── --}}
        <div class="ea-section">
          <div class="ea-section-head">
            <span class="ea-section-name">{{ app()->getLocale() === 'es' ? 'Identidad' : 'Identity' }}</span>
            <div class="ea-section-line"></div>
          </div>

          {{-- Nombre --}}
          <div class="ea-field">
            <label class="ea-label">
              {{ app()->getLocale() === 'es' ? 'Nombre y Apellido' : 'Full Name' }}
              <span class="ea-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
            </label>
            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                   class="ea-input" placeholder="{{ app()->getLocale() === 'es' ? 'Nombre Apellido' : 'First Last' }}">
          </div>

          {{-- Email + Prefijo + Teléfono --}}
          <div class="ea-row-3">
            <div class="ea-field ea-field--email">
              <label class="ea-label">
                Email
                <span class="ea-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
              </label>
              <input type="email" name="email" value="{{ old('email') }}" required
                     class="ea-input" placeholder="tu@email.com">
            </div>
            <div style="display:flex;gap:16px;align-items:flex-start;">
              <div class="ea-field ea-field--prefix" style="flex:0 0 35%;">
                <label class="ea-label">{{ app()->getLocale() === 'es' ? 'Prefijo' : 'Prefix' }}</label>
                <div class="ea-select-line">
                  @include('partials._phone_prefix', ['name' => 'phone_prefix'])
                </div>
              </div>
              <div class="ea-field ea-field--phone" style="flex:1;">
                <label class="ea-label">{{ app()->getLocale() === 'es' ? 'Teléfono' : 'Phone' }}</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="ea-input" placeholder="600 000 000">
              </div>
            </div>
          </div>

          {{-- Confirm email (hidden, anti-bot) --}}
          <input type="email" name="confirm_email" value="{{ old('confirm_email') }}"
                 style="display:none" tabindex="-1" autocomplete="off">
        </div>

        {{-- ── UBICACIÓN ───────────────────────────────── --}}
        <div class="ea-section">
          <div class="ea-section-head">
            <span class="ea-section-name">{{ app()->getLocale() === 'es' ? 'Ubicación' : 'Location' }}</span>
            <div class="ea-section-line"></div>
          </div>

          <div class="ea-row-2">
            <div class="ea-field">
              <label class="ea-label">{{ app()->getLocale() === 'es' ? 'País' : 'Country' }}</label>
              <div class="ea-select-line">
                @include('partials._countries', ['name' => 'country'])
              </div>
            </div>
            <div class="ea-field">
              <label class="ea-label">{{ app()->getLocale() === 'es' ? 'Ciudad' : 'City' }}</label>
              <input type="text" name="city" value="{{ old('city') }}"
                     class="ea-input" placeholder="{{ app()->getLocale() === 'es' ? 'Madrid' : 'City' }}">
            </div>
          </div>
        </div>

        <button type="submit" class="ea-btn">
          {{ app()->getLocale() === 'es' ? 'Continuar' : 'Continue' }} &nbsp;→
        </button>

      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
.ea-page{background:var(--light-bg);padding:72px 0 110px;}
.ea-wrap{max-width:900px;margin:0 auto;padding:0 40px;}

/* Progress */
.ea-progress{display:flex;align-items:center;gap:16px;margin-bottom:44px;}
.ea-progress-label{font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.35em;text-transform:uppercase;color:var(--light-subtle);white-space:nowrap;}
.ea-progress-track{flex:1;height:1px;background:var(--light-border);position:relative;}
.ea-progress-fill{position:absolute;left:0;top:0;height:100%;background:var(--light-text);}
.ea-progress-step{font-family:var(--font-body);font-size:11px;color:var(--light-subtle);white-space:nowrap;}

/* Card */
.ea-card{background:#fff;border:1px solid var(--light-border);padding:60px 64px;}

/* Section */
.ea-section{margin-bottom:40px;}
.ea-section:last-of-type{margin-bottom:36px;}
.ea-section-head{display:flex;align-items:center;gap:14px;margin-bottom:24px;}
.ea-section-name{font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:var(--light-text);white-space:nowrap;flex-shrink:0;}
.ea-section-line{flex:1;height:2px;background:linear-gradient(to right,#c8c3bc,transparent);}

/* Type cards */
.ea-type-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.ea-type-grid--3{grid-template-columns:1fr 1fr 1fr;margin-bottom:0;}
.ea-type-card{display:flex;align-items:flex-start;gap:14px;padding:20px;border:1px solid var(--light-border);border-radius:8px;cursor:pointer;transition:all .25s;background:#fff;}
.ea-type-card input{display:none;}
.ea-type-dot{width:16px;height:16px;border-radius:50%;border:1px solid #ccc;flex-shrink:0;margin-top:2px;transition:all .2s;}
.ea-type-card.is-active{border-color:#111;background:#111;}
.ea-type-card.is-active .ea-type-dot{border-color:#fff;background:#fff;}
.ea-type-card.is-active .ea-type-title{color:#fff;}
.ea-type-card.is-active .ea-type-sub{color:rgba(255,255,255,.55);}
.ea-type-title{font-family:var(--font-body);font-size:14px;font-weight:400;color:var(--light-text);margin-bottom:4px;transition:color .25s;}
.ea-type-sub{font-family:var(--font-body);font-size:11px;font-weight:300;color:var(--light-subtle);transition:color .25s;}

/* Fields */
.ea-field{margin-bottom:20px;}
.ea-field:last-child{margin-bottom:0;}
.ea-label{display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:10px;}
.ea-req{background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.1em;padding:2px 9px;border-radius:999px;line-height:1.7;flex-shrink:0;}
.ea-input{width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;border-radius:0;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;}
.ea-input:focus{border-bottom-color:#111;}
.ea-input::placeholder{color:#c0bdb8;}

/* Rows */
.ea-row-3{display:flex;gap:28px;align-items:flex-start;margin-bottom:0;}
.ea-row-3 .ea-field{margin-bottom:0;}
.ea-row-3>div[style]{margin-bottom:0;}
.ea-field--email{flex:2.2;}
.ea-field--prefix{flex:0 0 110px;}
.ea-field--phone{flex:1.4;}
.ea-row-3 .ea-input,.ea-row-3 .ea-select-line select{margin-top:auto;}
.ea-row-2{display:grid;grid-template-columns:1fr 1fr;gap:28px;}

/* Select */
.ea-select-line select{width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;border-radius:0;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;-webkit-appearance:none;appearance:none;cursor:pointer;}
.ea-select-line select:focus{border-bottom-color:#111;}

/* Error */
.ea-error{background:#fdf5f5;border-left:3px solid #c0392b;padding:14px 20px;margin-bottom:32px;font-family:var(--font-body);font-size:13px;font-weight:300;color:#922b21;line-height:1.7;}

/* Button */
.ea-btn{display:flex;align-items:center;justify-content:center;width:100%;min-height:66px;background:#111;color:#fff;border:none;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.38em;text-transform:uppercase;cursor:pointer;transition:background .25s;margin-top:8px;}
.ea-btn:hover{background:#2a2a28;}

/* Mobile */
@media(max-width:640px){
  .ea-row-3>div[style*="display:flex"]{width:100%!important;box-sizing:border-box;}
  .ea-wrap{padding:0 20px;}.ea-page{padding:48px 0 72px;}.ea-card{padding:32px 24px;}
  .ea-type-grid{grid-template-columns:1fr;}.ea-type-grid--3{grid-template-columns:1fr;}.ea-row-2{grid-template-columns:1fr;gap:0;}
  .ea-row-3{flex-direction:column;gap:0;}
  .ea-field--email{flex:none;width:100%;}
  .ea-phone-row{display:contents;}
  .ea-phone-row .ea-field--prefix{flex:0 0 30%!important;min-width:0;}
  .ea-phone-row .ea-field--phone{flex:1!important;}
  div[style*="min-height:auto"]{padding:100px 24px 40px!important;}
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.ea-type-card').forEach(card => {
  card.addEventListener('click', () => {
    document.querySelectorAll('.ea-type-card').forEach(c => c.classList.remove('is-active'));
    card.classList.add('is-active');
    card.querySelector('input').checked = true;
  });
});
</script>
@endpush
@endsection
