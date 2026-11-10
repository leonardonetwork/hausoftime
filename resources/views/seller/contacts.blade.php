@extends('layouts.seller')
@section('title', app()->getLocale() === 'es' ? 'Contacto' : 'Contact')

@section('seller-content')

<div class="panel-header" style="margin-bottom:28px;">
  <div>
    <h1 class="page-title">{{ app()->getLocale() === 'es' ? 'Contacto' : 'Contact' }}</h1>
    <p class="page-subtitle" style="margin-bottom:0;">
      {{ app()->getLocale() === 'es'
        ? '¿Tienes alguna duda? Escríbenos y te responderemos lo antes posible.'
        : 'Have a question? Write to us and we will respond as soon as possible.' }}
    </p>
  </div>
</div>

@if(session('success'))
  <div class="alert alert--success">{{ session('success') }}</div>
@endif

<div style="max-width:680px;">
  <div class="card">

    <form method="POST" action="{{ route('contact.send') }}">
      @csrf

      @if($errors->any())
        <div class="alert alert--error" style="margin-bottom:20px;">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      {{-- Nombre --}}
      <div class="form-group">
        <label class="form-label">
          {{ app()->getLocale() === 'es' ? 'Nombre completo' : 'Full name' }}
          <span class="req-badge">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
        </label>
        @php $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name; @endphp
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $fullName) }}" required
               placeholder="{{ app()->getLocale() === 'es' ? 'Tu nombre completo' : 'Your full name' }}">
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      {{-- Email --}}
      <div class="form-group">
        <label class="form-label">{{ app()->getLocale() === 'es' ? 'Correo electrónico' : 'Email' }}</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email', $user->email) }}" required>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      {{-- Prefijo + Teléfono --}}
      <div class="form-row" style="grid-template-columns:140px 1fr;margin-bottom:0;">
        <div class="form-group">
          <label class="form-label">{{ app()->getLocale() === 'es' ? 'Prefijo' : 'Prefix' }}</label>
          @include('components.phone-prefix', [
            'name'     => 'phone_prefix',
            'selected' => old('phone_prefix', $user->phone_prefix ?? '+34'),
          ])
        </div>
        <div class="form-group">
          <label class="form-label">{{ app()->getLocale() === 'es' ? 'Teléfono' : 'Phone' }}</label>
          <input type="tel" name="phone" class="form-control"
                 value="{{ old('phone', $user->phone ?? '') }}"
                 placeholder="600 000 000">
        </div>
      </div>

      {{-- Motivo --}}
      <div class="form-group">
        <label class="form-label">
          {{ app()->getLocale() === 'es' ? 'Motivo del contacto' : 'Reason for contact' }}
          <span class="req-badge">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
        </label>
        @php
        $reasons = app()->getLocale() === 'es' ? [
          'sell'         => 'Vender un reloj',
          'account'      => 'Cuenta o acceso',
          'tech'         => 'Problema técnico',
          'buy'          => 'Comprar un reloj',
          'early_access' => 'Early Access App',
          'press'        => 'Prensa y medios',
          'partners'     => 'Colaboraciones',
          'general'      => 'Consulta general',
          'other'        => 'Otro',
        ] : [
          'sell'         => 'Sell a watch',
          'account'      => 'Account or access',
          'tech'         => 'Technical issue',
          'buy'          => 'Buy a watch',
          'early_access' => 'Early Access App',
          'press'        => 'Press and media',
          'partners'     => 'Partnerships',
          'general'      => 'General enquiry',
          'other'        => 'Other',
        ];
        @endphp
        <div class="reasons-grid">
          @foreach($reasons as $val => $label)
          <label class="reason-item {{ old('reason') === $val ? 'is-active' : '' }}">
            <input type="radio" name="reason" value="{{ $val }}"
                   {{ old('reason') === $val ? 'checked' : '' }}>
            <span class="reason-dot"></span>
            <span class="reason-text">{{ $label }}</span>
          </label>
          @endforeach
        </div>
        @error('reason')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      {{-- Otro --}}
      <div id="other-wrap" style="display:{{ old('reason')==='other' ? 'block' : 'none' }};margin-bottom:18px;">
        <input type="text" name="other_reason" value="{{ old('other_reason') }}" class="form-control"
               placeholder="{{ app()->getLocale() === 'es' ? 'Especifica el motivo' : 'Specify the reason' }}">
      </div>

      {{-- Mensaje --}}
      <div class="form-group">
        <label class="form-label">
          {{ app()->getLocale() === 'es' ? 'Mensaje' : 'Message' }}
          <span class="req-badge">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
        </label>
        <textarea name="message" required rows="5" class="form-control"
                  placeholder="{{ app()->getLocale() === 'es' ? 'Cuantos más detalles, más rápido podremos ayudarte.' : 'The more details, the faster we can help.' }}">{{ old('message') }}</textarea>
        @error('message')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      {{-- Términos --}}
      <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:24px;">
        <label style="position:relative;cursor:pointer;flex-shrink:0;margin-top:3px;">
          <input type="checkbox" name="accept_terms" required style="position:absolute;opacity:0;width:0;height:0;">
          <span class="custom-checkbox"></span>
        </label>
        <span style="font-size:12px;color:var(--s-muted);line-height:1.85;">
          @php
            $termsPage = \App\Models\Page::where('slug','terminos-y-condiciones')->first();
            $privPage  = \App\Models\Page::where('slug','privacidad')->first();
          @endphp
          {{ app()->getLocale() === 'es' ? 'He leído y acepto los' : 'I have read and accept the' }}
          @if($termsPage)
            <a href="{{ url('/terminos-y-condiciones') }}" target="_blank" style="color:var(--s-text);text-decoration:underline;text-underline-offset:3px;">
              {{ app()->getLocale() === 'en' && $termsPage->title_en ? $termsPage->title_en : $termsPage->title_es }}
            </a>
          @endif
          {{ app()->getLocale() === 'es' ? 'y la' : 'and the' }}
          @if($privPage)
            <a href="{{ url('/privacidad') }}" target="_blank" style="color:var(--s-text);text-decoration:underline;text-underline-offset:3px;">
              {{ app()->getLocale() === 'en' && $privPage->title_en ? $privPage->title_en : $privPage->title_es }}
            </a>
          @endif.
        </span>
      </div>

      <button type="submit" class="btn btn--dark btn--lg" style="width:100%;justify-content:center;">
        {{ app()->getLocale() === 'es' ? 'Enviar mensaje' : 'Send message' }}
      </button>

    </form>
  </div>
</div>

@push('styles')
<style>
.req-badge{background:var(--s-gold);color:var(--s-on-gold);font-size:8px;font-weight:600;letter-spacing:.1em;padding:2px 8px;border-radius:999px;line-height:1.6;flex-shrink:0;margin-left:6px;vertical-align:middle;}
.reasons-grid{display:grid;grid-template-columns:1fr 1fr;gap:0;}
.reason-item{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--s-border2);cursor:pointer;font-size:13px;color:var(--s-muted);transition:color .2s;}
.reason-item input{position:absolute;opacity:0;pointer-events:none;}
.reason-dot{width:10px;height:10px;border-radius:50%;border:1px solid var(--s-border);flex-shrink:0;transition:all .2s;}
.reason-item.is-active .reason-dot,.reason-item:has(input:checked) .reason-dot{border-color:var(--s-gold);background:var(--s-gold);}
.reason-item.is-active,.reason-item:has(input:checked){color:var(--s-text);}
.custom-checkbox{display:block;width:16px;height:16px;border:1px solid var(--s-border);background:var(--s-bg3);transition:all .2s;}
input:checked+.custom-checkbox{background:var(--s-gold);border-color:var(--s-gold);}
input:checked+.custom-checkbox::after{content:'';display:block;width:4px;height:8px;border:1.5px solid var(--s-on-gold);border-top:none;border-left:none;transform:rotate(45deg);margin:2px auto 0;}
@media(max-width:600px){.reasons-grid{grid-template-columns:1fr;}}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.reason-item').forEach(function(item) {
  item.addEventListener('click', function() {
    document.querySelectorAll('.reason-item').forEach(function(i) { i.classList.remove('is-active'); });
    item.classList.add('is-active');
    var inp = item.querySelector('input');
    inp.checked = true;
    document.getElementById('other-wrap').style.display = inp.value === 'other' ? 'block' : 'none';
  });
});
</script>
@endpush

@endsection
