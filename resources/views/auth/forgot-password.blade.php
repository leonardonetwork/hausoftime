@extends('layouts.auth')
@section('title', app()->getLocale() === 'es' ? 'Recuperar contraseña — Haus of Time' : 'Forgot Password — Haus of Time')


@section('content')
<div style="display:flex;align-items:center;justify-content:center;min-height:calc(100vh - 70px);padding:60px 20px;">
  <div style="width:100%;max-width:480px;">
<div style="background:#fff;border:1px solid var(--light-border);padding:52px 48px;">

      <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
        <span style="font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:var(--light-text);white-space:nowrap;">
          {{ app()->getLocale() === 'es' ? 'Recuperar contraseña' : 'Reset password' }}
        </span>
        <div style="flex:1;height:2px;background:linear-gradient(to right,#c8c3bc,transparent);"></div>
      </div>

      <p style="font-family:var(--font-body);font-size:13px;font-weight:300;color:var(--light-muted);line-height:1.75;margin-bottom:32px;">
        {{ app()->getLocale() === 'es'
          ? 'Introduce tu email y te enviaremos un enlace para restablecer tu contraseña.'
          : 'Enter your email and we will send you a link to reset your password.' }}
      </p>

      @if(session('status'))
        <div style="background:#f0faf4;border-left:3px solid #3d7a5e;padding:14px 18px;margin-bottom:24px;font-family:var(--font-body);font-size:13px;color:#2d6a46;line-height:1.7;">
          {{ session('status') }}
        </div>
      @endif
      @if($errors->any())
        <div style="background:#fdf5f5;border-left:3px solid #c0392b;padding:12px 16px;margin-bottom:24px;font-family:var(--font-body);font-size:13px;color:#922b21;line-height:1.7;">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div style="margin-bottom:32px;">
          <label style="display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:10px;">
            Email <span style="background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.1em;padding:2px 9px;border-radius:999px;line-height:1.7;">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
          </label>
          <input type="email" name="email" value="{{ old('email') }}" required autofocus
                 style="width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;"
                 placeholder="tu@email.com"
                 onfocus="this.style.borderBottomColor='#111'" onblur="this.style.borderBottomColor='#ddd'">
        </div>

        <button type="submit"
                style="display:flex;align-items:center;justify-content:center;width:100%;min-height:64px;background:#111;color:#fff;border:none;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;transition:background .25s;margin-bottom:28px;"
                onmouseover="this.style.background='#2a2a28'" onmouseout="this.style.background='#111'">
          {{ app()->getLocale() === 'es' ? 'Enviar enlace' : 'Send reset link' }}
        </button>

        <div style="text-align:center;padding-top:20px;border-top:1px solid var(--light-border);">
          <a href="{{ route('login') }}"
             style="font-family:var(--font-body);font-size:12px;font-weight:300;color:var(--light-subtle);text-decoration:none;">
            ← {{ app()->getLocale() === 'es' ? 'Volver al login' : 'Back to login' }}
          </a>
        </div>
      </form>
    </div>

  </div>
@endsection
