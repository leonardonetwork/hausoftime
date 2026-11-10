<!DOCTYPE html>
<html lang="{{ $user->locale ?? 'es' }}">
<head>
  <meta charset="UTF-8">
  <style>
    body{margin:0;padding:0;background:#f4f4f4;font-family:'Helvetica Neue',Arial,sans-serif;color:#333;}
    .wrap{max-width:600px;margin:40px auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #ddd8ce;}
    .hdr{background:#111;padding:32px 40px;text-align:center;}
    .hdr img{height:28px;}
    .bdy{padding:44px 40px;}
    h1{color:#111;font-size:22px;font-weight:600;margin:0 0 16px;}
    p{font-size:15px;line-height:1.7;margin:0 0 16px;color:#444;}
    .tag{display:inline-block;background:#b89064;color:#fff;font-size:10px;font-weight:700;letter-spacing:.12em;padding:4px 12px;border-radius:100px;text-transform:uppercase;margin-bottom:20px;}
    .highlight{background:#f9f6f2;border-left:3px solid #b89064;padding:18px 22px;margin:24px 0;}
    .highlight p{margin:4px 0;font-size:14px;color:#444;}
    .info-table{width:100%;border-collapse:collapse;margin:20px 0;}
    .info-table td{padding:9px 0;border-bottom:1px solid #eee;font-size:14px;vertical-align:top;}
    .info-table td:first-child{color:#999;width:40%;font-size:12px;text-transform:uppercase;letter-spacing:.05em;}
    .info-table td:last-child{color:#222;font-weight:500;}
    .btn-wrap{text-align:center;margin:32px 0;}
    .btn{display:inline-block;background:#111;color:#fff !important;text-decoration:none;padding:18px 44px;font-size:12px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;}
    .divider{border:none;border-top:1px solid #eee;margin:28px 0;}
    .note{font-size:12px;color:#999;line-height:1.7;}
    .ftr{background:#f9f9f9;padding:20px 40px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
  </div>
  <div class="bdy">
    @if(($user->locale ?? 'es') === 'en')

      @if($accessType === 'both')
        <div class="tag">Seller & Buyer Account</div>
      @else
        <div class="tag">Founding Seller</div>
      @endif

      <h1>Welcome to Haus of Time, {{ $user->first_name ?? $user->name }}!</h1>

      <p>Your
        @if($accessType === 'both') seller & buyer @else seller @endif
        account has been created and is <strong>active</strong>. As a Founding Seller, you have immediate access to all platform features.</p>

      <table class="info-table">
        <tr><td>Email</td><td>{{ $user->email }}</td></tr>
        <tr><td>Access</td><td>{{ $accessType === 'both' ? 'Seller + Buyer' : 'Seller' }}</td></tr>
        <tr><td>Status</td><td><span style="color:#3d7a5e;font-weight:700;">✓ Active</span></td></tr>
        @if($user->is_founding_seller)
        <tr><td>Commission</td><td>⭐ 4% Founding (vs 7% standard)</td></tr>
        @endif
      </table>

      <div class="btn-wrap">
        <a href="{{ rtrim(config('app.url'),'/') }}/seller/sube-tu-reloj" class="btn">
          Upload your first watch →
        </a>
      </div>

      <hr class="divider">

      <div class="highlight">
        <p><strong>Your Founding Seller benefits:</strong></p>
        <p>· 4% commission on your first batch (vs 7% standard)</p>
        <p>· Priority visibility from launch day</p>
        <p>· Direct onboarding support from our team</p>
        <p>· Founding Seller badge on your profile</p>
      </div>

      <p class="note">If you did not create this account, please contact us at <a href="mailto:support@hausoftime.com" style="color:#b89064;">support@hausoftime.com</a></p>

    @else

      @if($accessType === 'both')
        <div class="tag">Cuenta Vendedor y Comprador</div>
      @else
        <div class="tag">Founding Seller</div>
      @endif

      <h1>Bienvenido a Haus of Time, {{ $user->first_name ?? $user->name }}!</h1>

      <p>Tu cuenta de
        @if($accessType === 'both') vendedor y comprador @else vendedor @endif
        ha sido creada y está <strong>activa</strong>. Como Founding Seller, tienes acceso inmediato a todas las funcionalidades de la plataforma.</p>

      <table class="info-table">
        <tr><td>Email</td><td>{{ $user->email }}</td></tr>
        <tr><td>Acceso</td><td>{{ $accessType === 'both' ? 'Vendedor + Comprador' : 'Vendedor' }}</td></tr>
        <tr><td>Estado</td><td><span style="color:#3d7a5e;font-weight:700;">✓ Activo</span></td></tr>
        @if($user->is_founding_seller)
        <tr><td>Comisión</td><td>⭐ 4% Founding (vs 7% estándar)</td></tr>
        @endif
      </table>

      <div class="btn-wrap">
        <a href="{{ rtrim(config('app.url'),'/') }}/seller/sube-tu-reloj" class="btn">
          Sube tu primer reloj →
        </a>
      </div>

      <hr class="divider">

      <div class="highlight">
        <p><strong>Tus ventajas como Founding Seller:</strong></p>
        <p>· Comisión 4% en el primer batch (vs 7% estándar)</p>
        <p>· Visibilidad prioritaria desde el día de apertura</p>
        <p>· Onboarding directo con nuestro equipo</p>
        <p>· Badge de Founding Seller en tu perfil</p>
      </div>

      <p class="note">Si no has solicitado esta cuenta, contáctanos en <a href="mailto:support@hausoftime.com" style="color:#b89064;">support@hausoftime.com</a></p>

    @endif
  </div>
  <div class="ftr">© {{ date('Y') }} HAUSOFTIME.COM — Haus of Time</div>
</div>
</body>
</html>
