@extends(\App\Support\Look::layout('layouts.auth'))
@section('title', app()->getLocale() === 'es' ? 'Nueva contraseña — Haus of Time' : 'New Password — Haus of Time')


@section('content')
<div style="display:flex;align-items:center;justify-content:center;min-height:calc(100vh - 70px);padding:60px 20px;">
  <div style="width:100%;max-width:480px;">
<div style="background:#fff;border:1px solid var(--light-border);padding:52px 48px;">

      <div style="display:flex;align-items:center;gap:14px;margin-bottom:32px;">
        <span style="font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:var(--light-text);white-space:nowrap;">
          {{ app()->getLocale() === 'es' ? 'Nueva contraseña' : 'New password' }}
        </span>
        <div style="flex:1;height:2px;background:linear-gradient(to right,#c8c3bc,transparent);"></div>
      </div>

      @if($errors->any())
        <div style="background:#fdf5f5;border-left:3px solid #c0392b;padding:12px 16px;margin-bottom:24px;font-family:var(--font-body);font-size:13px;color:#922b21;line-height:1.7;">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        @foreach(['email'=>'Email','password'=>app()->getLocale()==="es"?"Nueva contraseña":"New password",'password_confirmation'=>app()->getLocale()==="es"?"Confirmar contraseña":"Confirm password"] as $field => $label)
        <div style="margin-bottom:28px;">
          <label style="display:flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--light-muted);margin-bottom:10px;">
            {{ $label }} <span style="background:#111;color:#fff;font-size:8px;font-weight:600;letter-spacing:.1em;padding:2px 9px;border-radius:999px;line-height:1.7;">{{ app()->getLocale() === 'es' ? 'REQUERIDO' : 'REQUIRED' }}</span>
          </label>
          <input type="{{ $field === 'email' ? 'email' : 'password' }}"
                 name="{{ $field }}"
                 value="{{ $field === 'email' ? old('email', $email ?? '') : '' }}"
                 required {{ $field !== 'email' ? 'minlength=8' : '' }}
                 style="width:100%;background:transparent;border:none;border-bottom:1px solid #ddd;padding:.65rem 0;font-family:var(--font-body);font-size:15px;font-weight:300;color:var(--light-text);outline:none;transition:border-color .25s;"
                 placeholder="{{ $field === 'email' ? 'tu@email.com' : '••••••••' }}"
                 onfocus="this.style.borderBottomColor='#111'" onblur="this.style.borderBottomColor='#ddd'">
        </div>
        @endforeach

        <button type="submit"
                style="display:flex;align-items:center;justify-content:center;width:100%;min-height:64px;background:#111;color:#fff;border:none;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;transition:background .25s;"
                onmouseover="this.style.background='#2a2a28'" onmouseout="this.style.background='#111'">
          {{ app()->getLocale() === 'es' ? 'Guardar nueva contraseña' : 'Save new password' }}
        </button>
      </form>
    </div>

  </div>
@endsection
