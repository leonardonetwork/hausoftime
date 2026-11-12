@extends(\App\Support\Look::layout('layouts.auth'))
@section('title', app()->getLocale() === 'es' ? 'Iniciar sesión — Haus of Time' : 'Login — Haus of Time')


@section('content')
<div style="display:flex;align-items:center;justify-content:center;min-height:calc(100vh - 70px);padding:60px 20px;">
  <div style="width:100%;max-width:480px;">

    {{-- Logo --}}
{{-- Card --}}
    <div style="background:#fff;border:1px solid var(--light-border);padding:52px 48px;">

      {{-- Header --}}
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:36px;">
        <span style="font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:var(--light-text);white-space:nowrap;">
          {{ app()->getLocale() === 'es' ? 'Acceso' : 'Login' }}
        </span>
        <div style="flex:1;height:2px;background:linear-gradient(to right,#c8c3bc,transparent);"></div>
      </div>

      @if(session('success'))
        <div style="background:#f0faf4;border-left:3px solid #3d7a5e;padding:12px 16px;margin-bottom:24px;font-family:var(--font-body);font-size:13px;color:#2d6a46;">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div style="background:#fdf5f5;border-left:3px solid #c0392b;padding:12px 16px;margin-bottom:24px;font-family:var(--font-body);font-size:13px;color:#922b21;line-height:1.7;">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div style="margin-bottom:28px;">
          <label style="display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:10px;">
            Email <span style="background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.1em;padding:2px 9px;border-radius:999px;line-height:1.7;">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
          </label>
          <input type="email" name="email" value="{{ old('email') }}" required autofocus
                 style="width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;"
                 placeholder="tu@email.com"
                 onfocus="this.style.borderBottomColor='#111'" onblur="this.style.borderBottomColor='#ddd'">
        </div>

        <div style="margin-bottom:24px;">
          <label style="display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:10px;">
            {{ app()->getLocale() === 'es' ? 'Contraseña' : 'Password' }}
            <span style="background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.1em;padding:2px 9px;border-radius:999px;line-height:1.7;">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
          </label>
          <input type="password" name="password" required
                 style="width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;"
                 placeholder="••••••••"
                 onfocus="this.style.borderBottomColor='#111'" onblur="this.style.borderBottomColor='#ddd'">
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
            <input type="checkbox" name="remember" style="width:14px;height:14px;accent-color:#111;">
            <span style="font-family:var(--font-body);font-size:12px;font-weight:300;color:var(--light-muted);">{{ app()->getLocale() === 'es' ? 'Recuérdame' : 'Remember me' }}</span>
          </label>
          <a href="{{ route('password.request') }}"
             style="font-family:var(--font-body);font-size:12px;font-weight:300;color:var(--light-subtle);text-decoration:none;transition:color .2s;"
             onmouseover="this.style.color='var(--light-text)'" onmouseout="this.style.color='var(--light-subtle)'">
            {{ app()->getLocale() === 'es' ? 'Contraseña olvidada' : 'Forgot password' }}
          </a>
        </div>

        <button type="submit"
                style="display:flex;align-items:center;justify-content:center;width:100%;min-height:64px;background:#111;color:#fff;border:none;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.38em;text-transform:uppercase;cursor:pointer;transition:background .25s;margin-bottom:28px;"
                onmouseover="this.style.background='#2a2a28'" onmouseout="this.style.background='#111'">
          {{ app()->getLocale() === 'es' ? 'Iniciar sesión' : 'Login' }}
        </button>

        <div style="text-align:center;padding-top:20px;border-top:1px solid var(--light-border);">
          <p style="font-family:var(--font-body);font-size:13px;font-weight:300;color:var(--light-muted);">
            {{ app()->getLocale() === 'es' ? '¿No tienes cuenta?' : "Don't have an account?" }}
            <a href="{{ route('early-access.index') }}"
               style="color:var(--light-text);font-weight:400;text-decoration:underline;text-underline-offset:3px;margin-left:4px;">
              {{ \App\Support\Look::t(app()->getLocale() === 'es' ? 'Solicitar acceso anticipado →' : 'Request early access →', app()->getLocale() === 'es' ? 'Crea tu cuenta de vendedor →' : 'Create a seller account →') }}
            </a>
          </p>
        </div>
      </form>
    </div>

  </div>
@endsection
