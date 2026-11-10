@extends('layouts.app')
@section('title', __('messages.evaluation.title'))

@section('content')

{{-- Hero --}}
<div class="page-hero">
  <div class="page-hero__inner" style="max-width:var(--max-width);margin:0 auto;width:100%;padding:0 60px;">
    <div class="page-hero__label">{{ __('messages.evaluation.subtitle') }}</div>
    <h1 class="page-hero__title">{{ __('messages.evaluation.title') }}</h1>
  </div>
</div>

<div class="ev-page">
  <div class="ev-wrap">

    {{-- Progress --}}
    @php $fromPanel = session('evaluation_step1.from_panel', false); @endphp
    <div class="ev-progress">
      <span class="ev-progress-label">Instant Watch Evaluation</span>
      <div class="ev-progress-track"><div class="ev-progress-fill" style="width:100%"></div></div>
      @if($fromPanel)
        <span class="ev-progress-step">{{ app()->getLocale() === 'es' ? 'Datos del reloj' : 'Watch details' }}</span>
      @else
        <span class="ev-progress-step">2 / 2</span>
      @endif
    </div>

    {{-- Card --}}
    <div class="ev-card">

      @if($errors->any())
        <div class="ev-errors">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('evaluation.step2') }}">
        @csrf

        {{-- ── TU RELOJ ─────────────────────────────────── --}}
        <div class="ev-section">
          <div class="ev-section-head">
            <span class="ev-section-name">{{ app()->getLocale() === 'es' ? 'Tu Reloj' : 'Your Watch' }}</span>
            <div class="ev-section-line"></div>
          </div>

          {{-- Marca + Modelo --}}
          <div class="ev-row-2">
            <div class="ev-field">
              <label class="ev-label">
                {{ app()->getLocale() === 'es' ? 'Marca' : 'Brand' }}
                <span class="ev-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
              </label>
              <input type="text" name="brand" value="{{ old('brand') }}" required class="ev-input"
                     placeholder="{{ app()->getLocale() === 'es' ? 'Rolex, Patek Philippe...' : 'Rolex, Patek Philippe...' }}">
            </div>
            <div class="ev-field">
              <label class="ev-label">
                {{ app()->getLocale() === 'es' ? 'Modelo' : 'Model' }}
                <span class="ev-req">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
              </label>
              <input type="text" name="model" value="{{ old('model') }}" required class="ev-input"
                     placeholder="{{ app()->getLocale() === 'es' ? 'Submariner, Nautilus...' : 'Submariner, Nautilus...' }}">
            </div>
          </div>

          {{-- Referencia + Año --}}
          <div class="ev-row-2">
            <div class="ev-field">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'Referencia' : 'Reference' }}</label>
              <input type="text" name="reference" value="{{ old('reference') }}" class="ev-input"
                     placeholder="126610LN">
            </div>
            <div class="ev-field">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'Año de fabricación' : 'Year of manufacture' }}</label>
              <input type="number" name="manufacture_year" value="{{ old('manufacture_year') }}" class="ev-input"
                     min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') - 3 }}">
            </div>
          </div>

          {{-- Estado + Caja --}}
          <div class="ev-row-2">
            <div class="ev-field">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'Estado' : 'Condition' }}</label>
              <div class="ev-select-line">
                <select name="condition">
                  <option value="">— {{ app()->getLocale() === 'es' ? 'Seleccionar' : 'Select' }} —</option>
                  @php $conditions = app()->getLocale() === 'es'
                    ? ['mint'=>'Perfecto (sin uso)','excellent'=>'Excelente','very_good'=>'Muy bueno','good'=>'Bueno','fair'=>'Aceptable']
                    : ['mint'=>'Mint (unworn)','excellent'=>'Excellent','very_good'=>'Very Good','good'=>'Good','fair'=>'Fair'];
                  @endphp
                  @foreach($conditions as $val => $label)
                    <option value="{{ $val }}" {{ old('condition') === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="ev-field">
              <label class="ev-label">{{ app()->getLocale() === 'es' ? 'Caja y documentación' : 'Box & Papers' }}</label>
              <div class="ev-select-line">
                <select name="box_papers">
                  <option value="">— {{ app()->getLocale() === 'es' ? 'Seleccionar' : 'Select' }} —</option>
                  @php $boxOptions = app()->getLocale() === 'es'
                    ? ['none'=>'Sin caja ni papeles','box_only'=>'Solo caja','papers_only'=>'Solo papeles','full_set'=>'Caja y papeles completos']
                    : ['none'=>'Neither box nor papers','box_only'=>'Box only','papers_only'=>'Papers only','full_set'=>'Full set'];
                  @endphp
                  @foreach($boxOptions as $val => $label)
                    <option value="{{ $val }}" {{ old('box_papers') === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>

        {{-- Nota AI --}}
        <div style="border-left:2px solid var(--light-border);padding:14px 20px;margin-bottom:36px;">
          <p style="font-family:var(--font-body);font-size:12px;font-weight:300;color:var(--light-subtle);line-height:1.7;">
            {{ app()->getLocale() === 'es'
              ? 'Nuestro sistema analizará tu reloj y te dará una estimación de valor basada en el mercado actual. El resultado es orientativo.'
              : 'Our system will analyze your watch and provide a market value estimate. The result is indicative only.' }}
          </p>
        </div>

        {{-- ── POLÍTICA DE PRIVACIDAD ─────────────────── --}}
        <div class="ev-section" style="margin-bottom:32px;">
          <div class="ev-section-head">
            <span class="ev-section-name">{{ app()->getLocale() === 'es' ? 'Política de Privacidad' : 'Privacy Policy' }}</span>
            <div class="ev-section-line"></div>
          </div>

          <div style="display:flex;align-items:flex-start;gap:12px;">
            <label class="ev-check-wrap">
              <input type="checkbox" name="accept_terms" required>
              <span class="ev-checkbox"></span>
            </label>
            <span class="ev-terms-text">
              @php
                $termsPage = \App\Models\Page::where('slug','terminos-y-condiciones')->first();
                $privPage  = \App\Models\Page::where('slug','privacidad')->first();
              @endphp
              {{ app()->getLocale() === 'es' ? 'He leído y acepto los' : 'I have read and accept the' }}
              @if($termsPage)
                <a href="{{ url('/terminos-y-condiciones') }}" target="_blank" class="ev-terms-link">
                  {{ app()->getLocale() === 'en' && $termsPage->title_en ? $termsPage->title_en : $termsPage->title_es }}
                </a>
              @endif
              {{ app()->getLocale() === 'es' ? 'y la' : 'and the' }}
              @if($privPage)
                <a href="{{ url('/privacidad') }}" target="_blank" class="ev-terms-link">
                  {{ app()->getLocale() === 'en' && $privPage->title_en ? $privPage->title_en : $privPage->title_es }}
                </a>
              @endif.
            </span>
          </div>
        </div>

        {{-- Buttons --}}
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
          <a href="{{ route('evaluation.index') }}"
             style="display:flex;align-items:center;justify-content:center;min-height:64px;background:transparent;border:1px solid var(--light-border);font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.35em;text-transform:uppercase;color:var(--light-muted);transition:border-color .25s;text-decoration:none;">
            ← {{ app()->getLocale() === 'es' ? 'Volver' : 'Back' }}
          </a>
          <button type="submit" class="ev-btn">
            {{ app()->getLocale() === 'es' ? 'Obtener valoración' : 'Get valuation' }} →
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
.ev-page{background:var(--light-bg);padding:80px 0 110px;}
.ev-wrap{max-width:800px;margin:0 auto;padding:0 40px;}
.ev-progress{display:flex;align-items:center;gap:16px;margin-bottom:44px;}
.ev-progress-label{font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.35em;text-transform:uppercase;color:var(--light-subtle);white-space:nowrap;}
.ev-progress-track{flex:1;height:1px;background:var(--light-border);position:relative;}
.ev-progress-fill{position:absolute;left:0;top:0;height:100%;background:var(--light-text);transition:width .4s;}
.ev-progress-step{font-family:var(--font-body);font-size:11px;color:var(--light-subtle);white-space:nowrap;}
.ev-card{background:#fff;border:1px solid var(--light-border);padding:60px 64px;}
.ev-section{margin-bottom:40px;}
.ev-section-head{display:flex;align-items:center;gap:14px;margin-bottom:32px;}
.ev-section-name{font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:var(--light-text);white-space:nowrap;flex-shrink:0;}
.ev-section-line{flex:1;height:2px;background:linear-gradient(to right,#c8c3bc,transparent);}
.ev-field{margin-bottom:28px;}
.ev-field:last-child{margin-bottom:0;}
.ev-label{display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:10px;}
.ev-req{background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:2px 9px;border-radius:999px;line-height:1.7;flex-shrink:0;}
.ev-input{width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;border-radius:0;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;}
.ev-input:focus{border-bottom-color:#111;}
.ev-input::placeholder{color:#c0bdb8;}
.ev-select-line select{width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;border-radius:0;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;-webkit-appearance:none;appearance:none;cursor:pointer;}
.ev-select-line select:focus{border-bottom-color:#111;}
.ev-row-2{display:grid;grid-template-columns:1fr 1fr;gap:28px;}
.ev-errors{background:#fdf5f5;border-left:3px solid #c0392b;padding:14px 20px;margin-bottom:32px;font-family:var(--font-body);font-size:13px;font-weight:300;color:#922b21;line-height:1.7;}
.ev-btn{display:flex;align-items:center;justify-content:center;width:100%;min-height:64px;background:#111;color:#fff;border:none;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.38em;text-transform:uppercase;cursor:pointer;transition:background .25s;}
.ev-btn:hover{background:#2a2a28;}
.ev-check-wrap{position:relative;cursor:pointer;flex-shrink:0;margin-top:2px;}
.ev-check-wrap input{position:absolute;opacity:0;width:0;height:0;}
.ev-checkbox{display:block;width:16px;height:16px;border:1px solid #ccc;transition:all .2s;background:#fff;}
.ev-check-wrap input:checked+.ev-checkbox{background:#111;border-color:#111;}
.ev-check-wrap input:checked+.ev-checkbox::after{content:'';display:block;width:4px;height:8px;border:1.5px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);margin:2px auto 0;}
.ev-terms-text{font-family:var(--font-body);font-size:12px;font-weight:300;color:var(--light-muted);line-height:1.85;}
.ev-terms-link{color:var(--light-text);font-weight:400;text-decoration:underline;text-underline-offset:3px;}
@media(max-width:640px){
  .ev-wrap{padding:0 20px;}.ev-page{padding:48px 0 72px;}.ev-card{padding:32px 24px;}
  .ev-row-2{grid-template-columns:1fr;gap:0;}
  div[style*="grid-template-columns:1fr 2fr"]{grid-template-columns:1fr!important;}
}
</style>
@endpush
@endsection
