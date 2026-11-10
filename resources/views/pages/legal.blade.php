{{-- resources/views/pages/legal.blade.php --}}
@extends('layouts.app')
@section('title', __('messages.footer.legal'))
@section('content')
<div class="page-hero"><div class="container">
  <div class="page-hero__label">Legal</div>
  <h1 class="page-hero__title">{{ __('messages.footer.legal') }}</h1>
</div></div>
<div class="section"><div class="container container--narrow">
  <div class="card" style="line-height:1.8;color:var(--text-secondary);font-size:.95rem;">
    <h3 style="margin:0 0 16px;color:var(--text-primary);">Información legal</h3>
    <p><strong style="color:var(--text-primary);">Razón social:</strong> Leonardo Network S.L.</p>
    <p><strong style="color:var(--text-primary);">CIF:</strong> B-XXXXXXXX</p>
    <p><strong style="color:var(--text-primary);">Domicilio social:</strong> Madrid, España</p>
    <p><strong style="color:var(--text-primary);">Email:</strong> legal@hausoftime.com</p>
    <p><strong style="color:var(--text-primary);">Web:</strong> hausoftime.com</p>
    <p style="margin-top:24px;">El acceso y uso de este sitio web implica la aceptación plena y sin reservas de todas las condiciones incluidas en el Aviso Legal. La empresa se reserva el derecho a modificar cualquier tipo de información que pudiera aparecer en el sitio web, sin que exista obligación de preavisar.</p>
    <h3 style="margin:24px 0 10px;color:var(--text-primary);">Propiedad intelectual</h3>
    <p>Todos los contenidos del sitio web, incluyendo textos, fotografías, gráficos, imágenes, iconos, tecnología, software, así como su diseño gráfico y códigos fuente, constituyen una obra cuya propiedad pertenece a Leonardo Network S.L.</p>
  </div>
</div></div>
@endsection
