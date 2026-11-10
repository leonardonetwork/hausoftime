@extends('layouts.app')
@section('title', app()->getLocale() === 'es' ? 'Baja confirmada' : 'Unsubscribed')
@section('content')
<div class="page-hero" style="min-height:70vh;display:flex;align-items:center;">
  <div class="container" style="text-align:center;">
    <div style="font-size:2.5rem;margin-bottom:20px;">✓</div>
    <h1 class="page-hero__title">
      {{ app()->getLocale() === 'es' ? 'Te has dado de baja correctamente.' : 'You have been unsubscribed.' }}
    </h1>
    <p class="page-hero__desc" style="margin:16px auto 0;max-width:420px;">
      {{ app()->getLocale() === 'es'
        ? 'No recibirás más emails de nuestra newsletter. Si fue un error, puedes volver a suscribirte en cualquier momento.'
        : "You will no longer receive our newsletter emails. If this was a mistake, you can resubscribe at any time." }}
    </p>
    <a href="{{ route('home') }}" class="btn btn--outline" style="margin-top:32px;">
      {{ app()->getLocale() === 'es' ? '← Volver al inicio' : '← Back to home' }}
    </a>
  </div>
</div>
@endsection
