<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
  <meta charset="UTF-8">
  <style>
    body{margin:0;padding:0;background:#f4f4f4;font-family:'Helvetica Neue',Arial,sans-serif;color:#333;}
    .wrap{max-width:600px;margin:40px auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #ddd8ce;}
    .hdr{background:#111;padding:32px 40px;text-align:center;}
    .hdr img{height:28px;}
    .bdy{padding:40px;}
    .bdy h1{color:#111;font-size:22px;margin:0 0 16px;}
    .bdy p{font-size:15px;line-height:1.65;margin:0 0 16px;color:#444;}
    .btn-wrap{text-align:center;margin:32px 0;}
    .btn{display:inline-block;background:#111;color:#fff;text-decoration:none;padding:16px 40px;font-size:13px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;}
    .note{font-size:12px;color:#999;border-top:1px solid #eee;padding-top:16px;margin-top:16px;}
    .url{font-size:11px;color:#b89064;word-break:break-all;}
    .ftr{background:#f9f9f9;padding:20px 40px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="hdr">
      <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
    </div>
    <div class="bdy">
      @if($locale === 'en')
        <h1>Reset your password</h1>
        <p>We received a request to reset the password for your Haus of Time account.</p>
        <p>Click the button below to set a new password. This link is valid for <strong>60 minutes</strong>.</p>
        <div class="btn-wrap">
          <a href="{{ $resetUrl }}" class="btn">Reset password</a>
        </div>
        <div class="note">
          <p>If the button doesn't work, copy and paste this URL into your browser:</p>
          <p class="url">{{ $resetUrl }}</p>
        </div>
        <p style="margin-top:24px;">If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
      @else
        <h1>Recupera tu contraseña</h1>
        <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en Haus of Time.</p>
        <p>Haz clic en el botón para establecer una nueva contraseña. Este enlace es válido durante <strong>60 minutos</strong>.</p>
        <div class="btn-wrap">
          <a href="{{ $resetUrl }}" class="btn">Restablecer contraseña</a>
        </div>
        <div class="note">
          <p>Si el botón no funciona, copia y pega esta URL en tu navegador:</p>
          <p class="url">{{ $resetUrl }}</p>
        </div>
        <p style="margin-top:24px;">Si no solicitaste este cambio, puedes ignorar este email. Tu contraseña no cambiará.</p>
      @endif
    </div>
    <div class="ftr">
      © {{ date('Y') }} HAUSOFTIME.COM — Haus of Time
    </div>
  </div>
</body>
</html>
