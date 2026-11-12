{{-- resources/views/pages/privacy.blade.php --}}
@extends(\App\Support\Look::layout('layouts.app'))
@section('title', __('messages.footer.privacy'))
@section('content')
<div class="page-hero"><div class="container">
  <div class="page-hero__label">Legal</div>
  <h1 class="page-hero__title">{{ __('messages.footer.privacy') }}</h1>
</div></div>
<div class="section"><div class="container container--narrow">
  <div class="card" style="line-height:1.8;color:var(--text-secondary);font-size:.95rem;">
    <p>En Haus of Time tomamos muy en serio la privacidad de tus datos personales. Esta política explica cómo recopilamos, usamos y protegemos tu información.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">1. Responsable del tratamiento</h3>
    <p>Leonardo Network S.L., con domicilio en España. Email de contacto: privacy@hausoftime.com</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">2. Datos recopilados</h3>
    <p>Recopilamos únicamente los datos necesarios para la prestación del servicio: nombre, email, teléfono, país y datos de los relojes listados. No vendemos ni compartimos datos con terceros sin consentimiento explícito.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">3. Finalidad</h3>
    <p>Los datos se usan para gestionar tu cuenta, procesar transacciones, verificar identidad y enviarte comunicaciones relacionadas con el servicio.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">4. Tus derechos</h3>
    <p>Tienes derecho a acceder, rectificar, suprimir y portar tus datos. Puedes ejercerlos escribiendo a privacy@hausoftime.com.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">5. Cookies</h3>
    <p>Usamos cookies de sesión estrictamente necesarias para el funcionamiento de la plataforma. No usamos cookies de publicidad ni de seguimiento.</p>
    <p style="margin-top:24px;font-size:.8rem;color:var(--text-muted);">Última actualización: Enero 2025</p>
  </div>
</div></div>
@endsection
