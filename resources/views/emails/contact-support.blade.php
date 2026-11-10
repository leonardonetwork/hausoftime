<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body{margin:0;padding:40px 0;background:#f4f4f4;font-family:'Helvetica Neue',Arial,sans-serif;color:#333;}
    .wrap{max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #ddd8ce;}
    .hdr{background:#111;padding:24px 40px;text-align:center;}
    .hdr img{height:24px;}
    .hdr-sub{color:#888;font-size:11px;letter-spacing:.15em;text-transform:uppercase;margin-top:8px;}
    .bdy{padding:40px;}
    h1{color:#111;font-size:20px;font-weight:600;margin:0 0 4px;}
    .subtitle{font-size:13px;color:#888;margin:0 0 28px;}
    .section-title{font-size:10px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#999;border-bottom:2px solid #111;padding-bottom:8px;margin:28px 0 14px;}
    .info-table{width:100%;border-collapse:collapse;margin-bottom:8px;}
    .info-table td{padding:9px 0;border-bottom:1px solid #eee;font-size:14px;vertical-align:top;}
    .info-table td:first-child{color:#999;width:35%;font-size:12px;text-transform:uppercase;letter-spacing:.05em;padding-right:12px;}
    .info-table td:last-child{color:#222;font-weight:500;}
    .msg-box{background:#f5f5f5;border-radius:4px;padding:18px 20px;margin:14px 0;font-size:14px;line-height:1.75;color:#333;white-space:pre-wrap;word-break:break-word;}
    .ftr{background:#f9f9f9;padding:16px 40px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
    <div class="hdr-sub">Nuevo mensaje de contacto</div>
  </div>
  <div class="bdy">
    @php
      $d = $data;
      $isAuthenticated = $d['authenticated'] ?? false;

      $reasonLabels = [
        'early_access' => 'Early Access App',
        'buy'          => 'Comprar un reloj',
        'sell'         => 'Vender un reloj',
        'tech'         => 'Problema técnico',
        'account'      => 'Cuenta o acceso',
        'press'        => 'Prensa y medios',
        'partners'     => 'Colaboraciones',
        'general'      => 'Consulta general',
        'other'        => 'Otro',
      ];
      $reasonLabel = $reasonLabels[$d['reason'] ?? ''] ?? ($d['reason'] ?? '—');
      if (!empty($d['other_reason'])) $reasonLabel .= ' — ' . $d['other_reason'];
    @endphp

    <h1>{{ $d['name'] }}</h1>
    <p class="subtitle">
      {{ $isAuthenticated ? 'Mensaje desde área de vendedor' : 'Mensaje desde formulario público' }}
      · {{ now()->format('d/m/Y H:i') }}
    </p>

    <div class="section-title">Datos del remitente</div>
    <table class="info-table">
      <tr><td>Nombre</td><td>{{ $d['name'] }}</td></tr>
      <tr><td>Email</td><td><a href="mailto:{{ $d['email'] }}" style="color:#b89064;">{{ $d['email'] }}</a></td></tr>
      @if(!empty($d['phone']))
      <tr><td>Teléfono</td><td>{{ ($d['phone_prefix'] ?? '') }} {{ $d['phone'] }}</td></tr>
      @endif
      <tr><td>Motivo</td><td><strong>{{ $reasonLabel }}</strong></td></tr>
    </table>

    <div class="section-title">Mensaje</div>
    <div class="msg-box">{{ $d['message'] }}</div>

    <p style="font-size:12px;color:#999;margin-top:20px;">
      Responde directamente a este email para contestar al usuario.
    </p>
  </div>
  <div class="ftr">Haus of Time · Formulario de contacto · {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
