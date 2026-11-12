@extends(\App\Support\Look::layout('layouts.app'))
@section('title', app()->getLocale() === 'es' ? 'Contacto — Haus of Time' : 'Contact — Haus of Time')

@section('content')

<div class="page-hero">
  <div class="page-hero__inner" style="max-width:1300px;margin:0 auto;width:100%;padding:0 60px;">
    <div class="page-hero__label">{{ app()->getLocale() === 'es' ? 'Contacto' : 'Contact' }}</div>
    <h1 class="page-hero__title">
      {{ app()->getLocale() === 'es' ? '¿En qué podemos ayudarte?' : 'How can we help you?' }}
    </h1>
  </div>
</div>

<div style="background:var(--light-bg);padding:80px 0 100px;">
  <div style="max-width:1300px;margin:0 auto;padding:0 60px;">
    <div class="contact-grid">

      {{-- ── LEFT ─────────────────────────────────────────── --}}
      <div class="contact-left">
        <p class="c-eyebrow">{{ app()->getLocale() === 'es' ? 'Escríbenos' : 'Write to us' }}</p>
        <h2 class="c-heading">
          {{ app()->getLocale() === 'es'
            ? 'Estamos aquí para resolver cualquier duda.'
            : 'We are here to answer any questions.' }}
        </h2>
        <p class="c-body">
          {{ app()->getLocale() === 'es'
            ? 'Escríbenos y te responderemos en el menor tiempo posible. Para solicitudes urgentes, incluye tu número de teléfono.'
            : 'Write to us and we will respond as soon as possible. For urgent requests, include your phone number.' }}
        </p>
        @php $privPage = \App\Models\Page::where('slug','privacidad')->first(); @endphp
        <div class="c-note">
          <p>{{ app()->getLocale() === 'es'
            ? 'Tu información es estrictamente privada. No compartimos tus datos con terceros.'
            : 'Your information is strictly private. We do not share your data.' }}
            @if($privPage)
              {{ app()->getLocale() === 'es' ? 'Ver' : 'See' }}
              <a href="{{ url('/privacidad') }}" class="c-note-link">
                {{ app()->getLocale() === 'en' && $privPage->title_en ? $privPage->title_en : $privPage->title_es }}
              </a>.
            @endif
          </p>
        </div>
      </div>

      {{-- ── RIGHT — Form ─────────────────────────────────── --}}
      <div>
        @if(session('success'))
          <div class="c-success">{{ session('success') }}</div>
        @endif

        <div class="c-card">
          <div class="c-card-header">
            {{ app()->getLocale() === 'es' ? 'Contáctenos' : 'Contact us' }}
          </div>

          <form method="POST" action="{{ route('contact.send') }}">
            @csrf

            @if($errors->any())
              <div class="c-errors">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
              </div>
            @endif

            {{-- Nombre — full width --}}
            <div class="c-field">
              <label class="c-label">
                {{ app()->getLocale() === 'es' ? 'Nombre' : 'Name' }}
                <span class="c-req">REQUERIDO</span>
              </label>
              <input type="text" name="name" value="{{ old('name') }}" required class="c-input"
                     placeholder="{{ app()->getLocale() === 'es' ? 'Tu nombre completo' : 'Your full name' }}">
            </div>

            {{-- Correo electrónico — full width --}}
            <div class="c-field">
              <label class="c-label">
                {{ app()->getLocale() === 'es' ? 'Correo electrónico' : 'Email' }}
                <span class="c-req">REQUERIDO</span>
              </label>
              <input type="email" name="email" value="{{ old('email') }}" required class="c-input"
                     placeholder="tu@email.com">
            </div>

            {{-- Prefijo + Teléfono — always on same row --}}
            <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:28px;">
              <div class="c-field" style="flex:0 0 35%;margin-bottom:0;">
                <label class="c-label">{{ app()->getLocale() === 'es' ? 'Prefijo' : 'Prefix' }}</label>
                <div class="c-prefix-wrap" style="width:100%;">
                  @include('partials._phone_prefix', ['name' => 'phone_prefix'])
                </div>
              </div>
              <div class="c-field" style="flex:1;margin-bottom:0;">
                <label class="c-label">{{ app()->getLocale() === 'es' ? 'Teléfono' : 'Phone' }}</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" class="c-input"
                       placeholder="600 000 000">
              </div>
            </div>

            {{-- Motivo --}}
            <div class="c-field">
              <label class="c-label">
                {{ app()->getLocale() === 'es' ? 'Motivo del contacto' : 'Reason for contact' }}
                <span class="c-req">REQUERIDO</span>
              </label>
              @php
              $reasons = app()->getLocale() === 'es' ? [
                'early_access' => 'Early Access App',
                'buy'          => 'Comprar un reloj / disponibilidad',
                'sell'         => 'Vender un reloj',
                'tech'         => 'Problema técnico',
                'account'      => 'Cuenta o acceso',
                'press'        => 'Prensa y medios',
                'partners'     => 'Colaboraciones',
                'general'      => 'Consulta general',
                'other'        => 'Otro',
              ] : [
                'early_access' => 'Early Access App',
                'buy'          => 'Buy a watch / availability',
                'sell'         => 'Sell a watch',
                'tech'         => 'Technical issue',
                'account'      => 'Account or access',
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
            </div>

            {{-- Otro --}}
            <div id="other-wrap" style="display:{{ old('reason')==='other'?'block':'none' }};margin-bottom:20px;">
              <input type="text" name="other_reason" value="{{ old('other_reason') }}" class="c-input"
                     placeholder="{{ app()->getLocale() === 'es' ? 'Especifica el motivo' : 'Specify the reason' }}">
            </div>

            {{-- Mensaje --}}
            <div class="c-field">
              <label class="c-label">
                {{ app()->getLocale() === 'es' ? 'Mensaje' : 'Message' }}
                <span class="c-req">REQUERIDO</span>
              </label>
              <textarea name="message" required rows="5" class="c-input c-textarea"
                        placeholder="{{ app()->getLocale() === 'es' ? 'Cuantos más detalles, más rápido podremos ayudarle.' : 'The more details, the faster we can help.' }}">{{ old('message', request('reloj') ? (app()->getLocale() === 'es' ? 'Consulta sobre: ' : 'Enquiry about: ') . \Illuminate\Support\Str::limit((string) request('reloj'), 150, '') . "\n\n" : '') }}</textarea>
            </div>

            {{-- Términos --}}
            <div class="c-terms">
              <label class="c-check-wrap">
                <input type="checkbox" name="accept_terms" required>
                <span class="c-checkbox"></span>
              </label>
              <span class="c-terms-text">
                @php
                  $termsPage = \App\Models\Page::where('slug','terminos-y-condiciones')->first();
                  $privPageF = \App\Models\Page::where('slug','privacidad')->first();
                @endphp
                {{ app()->getLocale() === 'es' ? 'He leído y acepto los' : 'I have read and accept the' }}
                @if($termsPage)
                  <a href="{{ url('/terminos-y-condiciones') }}" target="_blank" class="c-terms-link">
                    {{ app()->getLocale() === 'en' && $termsPage->title_en ? $termsPage->title_en : $termsPage->title_es }}
                  </a>
                @endif
                {{ app()->getLocale() === 'es' ? 'y la' : 'and the' }}
                @if($privPageF)
                  <a href="{{ url('/privacidad') }}" target="_blank" class="c-terms-link">
                    {{ app()->getLocale() === 'en' && $privPageF->title_en ? $privPageF->title_en : $privPageF->title_es }}
                  </a>
                @endif.
              </span>
            </div>

            <button type="submit" class="c-submit">
              {{ app()->getLocale() === 'es' ? 'Enviar solicitud' : 'Send request' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
/* ── Layout ─────────────────────────────────────────── */
.contact-grid{display:grid;grid-template-columns:1fr 1.65fr;gap:100px;align-items:start;}
.contact-left{position:sticky;top:120px;}

/* ── Left typography ─────────────────────────────────── */
.c-eyebrow{font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.45em;text-transform:uppercase;color:var(--light-subtle);margin-bottom:20px;}
.c-heading{font-family:var(--font-display);font-size:clamp(36px,3.5vw,54px);font-weight:300;line-height:1.05;color:var(--light-text);margin-bottom:28px;letter-spacing:-.01em;}
.c-body{font-family:var(--font-body);font-size:15px;font-weight:300;line-height:1.85;color:var(--light-muted);margin-bottom:48px;}
.c-note{border-left:1px solid var(--light-border);padding:16px 20px;}
.c-note p{font-family:var(--font-body);font-size:12px;font-weight:300;line-height:1.85;color:var(--light-subtle);}
.c-note-link{color:var(--light-text);text-decoration:underline;text-underline-offset:3px;}

/* ── Card ────────────────────────────────────────────── */
.c-card{background:#fff;border:1px solid var(--light-border);padding:52px 48px;}
.c-card-header{font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.4em;text-transform:uppercase;color:var(--light-subtle);padding-bottom:24px;border-bottom:1px solid var(--light-border);margin-bottom:36px;}
.c-success{background:#f0faf4;border-left:3px solid #3d8a5e;padding:20px 24px;margin-bottom:28px;font-family:var(--font-body);font-size:14px;font-weight:300;color:#2d6a46;line-height:1.7;}
.c-errors{background:#fdf5f5;border-left:3px solid #c0392b;padding:14px 18px;margin-bottom:24px;font-family:var(--font-body);font-size:13px;color:#922b21;line-height:1.7;}

/* ── Fields ──────────────────────────────────────────── */
.c-field{margin-bottom:28px;}
.c-label{display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:8px;}

/* REQUERIDO badge */
.c-req{background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;padding:2px 9px;border-radius:999px;line-height:1.6;flex-shrink:0;}

/* Inputs underline style */
.c-input{width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;border-radius:0;padding:.6rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;}
.c-input:focus{border-bottom-color:#111;}
.c-input::placeholder{color:#bbb;font-weight:300;}
.c-textarea{border:1px solid #ddd!important;border-radius:4px!important;padding:.75rem 1rem!important;resize:vertical;min-height:120px;line-height:1.7;}
.c-textarea:focus{border-color:#111!important;}

/* ── Phone row ───────────────────────────────────────── */
.c-row-3{display:flex;gap:20px;align-items:flex-end;margin-bottom:0;}
.c-prefix-wrap{width:120px;flex-shrink:0;}
.c-prefix-wrap select{width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;border-radius:0;padding:.6rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;-webkit-appearance:none;appearance:none;cursor:pointer;}
.c-prefix-wrap select:focus{border-bottom-color:#111;}

/* ── Reasons ─────────────────────────────────────────── */
.reasons-grid{display:grid;grid-template-columns:1fr 1fr;column-gap:40px;}
.reason-item{display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid #f0ede8;cursor:pointer;font-family:var(--font-body);font-size:13px;font-weight:300;color:var(--light-muted);transition:color .2s;}
.reason-item input{position:absolute;opacity:0;pointer-events:none;}
.reason-dot{width:10px;height:10px;border-radius:50%;border:1px solid #ccc;flex-shrink:0;transition:all .2s;}
.reason-item.is-active .reason-dot,.reason-item:has(input:checked) .reason-dot{border-color:#111;background:#111;}
.reason-item.is-active,.reason-item:has(input:checked){color:var(--light-text);}
.reason-text{line-height:1.4;}

/* ── Checkbox custom ─────────────────────────────────── */
.c-terms{display:flex;align-items:flex-start;gap:12px;margin-bottom:32px;}
.c-check-wrap{position:relative;cursor:pointer;flex-shrink:0;margin-top:3px;}
.c-check-wrap input{position:absolute;opacity:0;width:0;height:0;}
.c-checkbox{display:block;width:16px;height:16px;border:1px solid #ccc;transition:all .2s;background:#fff;}
.c-check-wrap input:checked+.c-checkbox{background:#111;border-color:#111;}
.c-check-wrap input:checked+.c-checkbox::after{content:'';display:block;width:4px;height:8px;border:1.5px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);margin:2px auto 0;}
.c-terms-text{font-family:var(--font-body);font-size:12px;font-weight:300;color:var(--light-muted);line-height:1.85;}
.c-terms-link{color:var(--light-text);font-weight:400;text-decoration:underline;text-underline-offset:3px;}

/* ── Submit ──────────────────────────────────────────── */
.c-submit{display:flex;align-items:center;justify-content:center;width:100%;min-height:64px;background:#111;color:#fff;border:none;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.38em;text-transform:uppercase;transition:background .25s;cursor:pointer;}
.c-submit:hover{background:#2a2a28;}

/* ── Mobile ──────────────────────────────────────────── */
@media(max-width:900px){
  .contact-grid{grid-template-columns:1fr;gap:48px;}
  .contact-left{position:static;text-align:center;}
  .c-note{text-align:left;}
  .c-card{padding:32px 24px;}
}
@media(max-width:600px){
  .reasons-grid{grid-template-columns:1fr;}
  .contact-grid{padding:0;}
  div[style*="max-width:1300px"]{padding:0 24px!important;}
  .c-card{padding:24px 20px;}
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.reason-item').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('.reason-item').forEach(i => i.classList.remove('is-active'));
    item.classList.add('is-active');
    const inp = item.querySelector('input');
    inp.checked = true;
    document.getElementById('other-wrap').style.display =
      inp.value === 'other' ? 'block' : 'none';
  });
});
</script>
@endpush
@endsection
