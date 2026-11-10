{{-- resources/views/pages/terms.blade.php --}}
@extends('layouts.app')
@section('title', __('messages.footer.terms'))
@section('content')
<div class="page-hero"><div class="container">
  <div class="page-hero__label">Legal</div>
  <h1 class="page-hero__title">{{ __('messages.footer.terms') }}</h1>
</div></div>
<div class="section"><div class="container container--narrow">
  <div class="card" style="line-height:1.8;color:var(--text-secondary);font-size:.95rem;">
    <p>Los presentes términos y condiciones regulan el acceso y uso de la plataforma Haus of Time (en adelante, "la plataforma"), operada por Leonardo Network S.L.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">1. Objeto</h3>
    <p>La plataforma facilita la compraventa de relojes de lujo de segunda mano entre vendedores verificados y compradores registrados, con un sistema de custodia de fondos (escrow) y autenticación independiente.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">2. Condiciones de uso</h3>
    <p>El acceso a la plataforma está restringido a usuarios mayores de 18 años que hayan completado el proceso de registro. El vendedor es responsable de la veracidad de la información proporcionada sobre los relojes listados.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">3. Comisiones</h3>
    <p>La plataforma aplica una comisión del 7% sobre el precio de venta (4% para vendedores fundadores) una vez completada la transacción y liberados los fondos del escrow.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">4. Autenticación</h3>
    <p>Todos los relojes pasan por un proceso de autenticación física y documental por un partner certificado. El coste está incluido en la comisión de la plataforma.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">5. Modificaciones</h3>
    <p>Haus of Time se reserva el derecho a modificar estos términos con previo aviso de 15 días a los usuarios registrados.</p>
    <p style="margin-top:24px;font-size:.8rem;color:var(--text-muted);">Última actualización: Enero 2025</p>
  </div>
</div></div>
@endsection
