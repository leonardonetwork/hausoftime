@extends(\App\Support\Look::layout('layouts.app'))
@section('title', __('messages.evaluation.title'))

@section('content')

{{-- Hero --}}
<div class="page-hero">
  <div class="page-hero__inner" style="max-width:var(--max-width);margin:0 auto;width:100%;padding:0 60px;">
    <div class="page-hero__label">{{ __('messages.evaluation.subtitle') }}</div>
    <h1 class="page-hero__title">{{ __('messages.evaluation.title') }}</h1>
    <p class="page-hero__desc">{{ __('messages.evaluation.hero_desc') }}</p>
  </div>
</div>

<div class="ev-page">
  <div class="ev-wrap">

    {{-- Progress --}}
    <div class="ev-progress">
      <span class="ev-progress-label">Instant Watch Evaluation</span>
      <div class="ev-progress-track"><div class="ev-progress-fill" style="width:50%"></div></div>
      <span class="ev-progress-step">1 / 2</span>
    </div>

    {{-- Card --}}
    <div class="ev-card">

      @if($errors->any())
        <div class="ev-errors">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('evaluation.step1') }}">
        @csrf

        {{-- ── IDENTIDAD ──────────────────────────────── --}}
        <div class="ev-section">
          <div class="ev-section-head">
            <span class="ev-section-name">{{ app()->getLocale() === 'es' ? 'Identidad' : 'Identity' }}</span>
            <div class="ev-section-line"></div>
          </div>

          <div class="ev-field">
            <label class="ev-label">
              {{ app()->getLocale() === 'es' ? 'Nombre y Apellido' : 'Full Name' }}
              <span class="ev-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required class="ev-input"
                   placeholder="{{ app()->getLocale() === 'es' ? 'Nombre Apellido' : 'First Last' }}">
          </div>

          {{-- Email full width --}}
          <div class="ev-field">
            <label class="ev-label">
              Email
              <span class="ev-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
            </label>
            <input type="email" name="email" value="{{ old('email') }}" required class="ev-input"
                   placeholder="tu@email.com">
          </div>

          {{-- Prefijo + Teléfono — always same row --}}
          <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:0;">
            <div class="ev-field" style="flex:0 0 35%;margin-bottom:0;">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'Prefijo' : 'Prefix' }}</label>
              <div class="ev-select-line">
                @include('partials._phone_prefix', ['name' => 'phone_prefix'])
              </div>
            </div>
            <div class="ev-field" style="flex:1;margin-bottom:0;">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'Teléfono' : 'Phone' }}</label>
              <input type="tel" name="phone" value="{{ old('phone') }}" class="ev-input"
                     placeholder="600 000 000">
            </div>
          </div>
        </div>

        {{-- ── UBICACIÓN ──────────────────────────────── --}}
        <div class="ev-section">
          <div class="ev-section-head">
            <span class="ev-section-name">{{ app()->getLocale() === 'es' ? 'Ubicación' : 'Location' }}</span>
            <div class="ev-section-line"></div>
          </div>

          <div class="ev-row-2">
            <div class="ev-field">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'País' : 'Country' }}</label>
              <div class="ev-select-line">
                @include('partials._countries', ['name' => 'country'])
              </div>
            </div>
            <div class="ev-field">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'Ciudad' : 'City' }}</label>
              <input type="text" name="city" value="{{ old('city') }}" class="ev-input"
                     placeholder="{{ app()->getLocale() === 'es' ? 'Madrid' : 'City' }}">
            </div>
          </div>
        </div>

        <button type="submit" class="ev-btn">
          {{ app()->getLocale() === 'es' ? 'Continuar' : 'Continue' }} &nbsp;→
        </button>

      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
/* ── Page wrapper ──────────────────────────────────────── */
.ev-page {
  background: var(--light-bg);
  padding: 80px 0 110px;
}
.ev-wrap {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 40px;
}

/* ── Progress ──────────────────────────────────────────── */
.ev-progress {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 44px;
}
.ev-progress-label {
  font-family: var(--font-body);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: .35em;
  text-transform: uppercase;
  color: var(--light-subtle);
  white-space: nowrap;
}
.ev-progress-track {
  flex: 1;
  height: 1px;
  background: var(--light-border);
  position: relative;
}
.ev-progress-fill {
  position: absolute;
  left: 0; top: 0;
  height: 100%;
  background: var(--light-text);
  transition: width .4s;
}
.ev-progress-step {
  font-family: var(--font-body);
  font-size: 11px;
  color: var(--light-subtle);
  white-space: nowrap;
}

/* ── Card ──────────────────────────────────────────────── */
.ev-card {
  background: #fff;
  border: 1px solid var(--light-border);
  padding: 60px 64px;
}

/* ── Section ───────────────────────────────────────────── */
.ev-section {
  margin-bottom: 44px;
}
.ev-section:last-of-type {
  margin-bottom: 36px;
}

/* Title con linea orizzontale a destra */
.ev-section-head {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 32px;
}
.ev-section-name {
  font-family: var(--font-body);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .28em;
  text-transform: uppercase;
  color: var(--light-text);
  white-space: nowrap;
  flex-shrink: 0;
}
.ev-section-line {
  flex: 1;
  height: 2px;
  background: linear-gradient(to right, #c8c3bc, transparent);
}

/* ── Fields ────────────────────────────────────────────── */
.ev-field {
  margin-bottom: 28px;
}
.ev-field:last-child { margin-bottom: 0; }

.ev-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-body);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: .2em;
  text-transform: uppercase;
  color: var(--light-muted);
  margin-bottom: 10px;
}

.ev-req {
  background: #111;
  color: #fff;
  font-size: 8px;
  font-weight: 600;
  letter-spacing: .1em;
  text-transform: uppercase;
  padding: 2px 9px;
  border-radius: 999px;
  line-height: 1.7;
  flex-shrink: 0;
}

/* Underline inputs */
.ev-input {
  width: 100%;
  background: transparent;
  border: none;
  border-bottom: 1px solid #ddd;
  border-radius: 0;
  padding: .65rem 0;
  font-family: var(--font-body);
  font-size: 15px;
  font-weight: 300;
  color: var(--light-text);
  outline: none;
  transition: border-color .25s;
}
.ev-input:focus { border-bottom-color: #111; }
.ev-input::placeholder { color: #c0bdb8; }

/* Select underline */
.ev-select-line select {
  width: 100%;
  background: transparent;
  border: none;
  border-bottom: 1px solid #ddd;
  border-radius: 0;
  padding: .65rem 0;
  font-family: var(--font-body);
  font-size: 15px;
  font-weight: 300;
  color: var(--light-text);
  outline: none;
  transition: border-color .25s;
  -webkit-appearance: none;
  appearance: none;
  cursor: pointer;
}
.ev-select-line select:focus { border-bottom-color: #111; }

/* Row layouts */
.ev-row-3 {
  display: flex;
  gap: 28px;
  align-items: flex-start;
}
.ev-row-3 .ev-field {
  display: flex;
  flex-direction: column;
}
.ev-row-3 .ev-input,
.ev-row-3 .ev-select-line select {
  margin-top: auto;
}
.ev-field--email  { flex: 2.2; }
.ev-field--prefix { flex: 0 0 110px; }
.ev-field--phone  { flex: 1.4; }

.ev-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px;
}

/* Errors */
.ev-errors {
  background: #fdf5f5;
  border-left: 3px solid #c0392b;
  padding: 14px 20px;
  margin-bottom: 32px;
  font-family: var(--font-body);
  font-size: 13px;
  font-weight: 300;
  color: #922b21;
  line-height: 1.7;
}

/* Submit */
.ev-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  min-height: 66px;
  background: #111;
  color: #fff;
  border: none;
  font-family: var(--font-body);
  font-size: 11px;
  font-weight: 400;
  letter-spacing: .38em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background .25s;
  margin-top: 8px;
}
.ev-btn:hover { background: #2a2a28; }

/* ── Mobile ────────────────────────────────────────────── */
@media (max-width: 640px) {
  .ev-wrap       { padding: 0 20px; }
  .ev-page       { padding: 48px 0 72px; }
  .ev-card       { padding: 32px 24px; }
  .ev-row-2      { grid-template-columns: 1fr; gap: 0; }
  .ev-section    { margin-bottom: 32px; }
  .ev-progress   { flex-wrap: wrap; gap: 10px; }
  .ev-progress-track { min-width: 100%; order: 3; }
}
</style>
@endpush
@endsection
