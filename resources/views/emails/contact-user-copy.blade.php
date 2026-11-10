<!DOCTYPE html>
<html lang="{{ $data['locale'] ?? 'es' }}">
<head>
  <meta charset="UTF-8">
  <style>
    body{margin:0;padding:40px 0;background:#f4f4f4;font-family:'Helvetica Neue',Arial,sans-serif;color:#333;}
    .wrap{max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #ddd8ce;}
    .hdr{background:#111;padding:32px 40px;text-align:center;}
    .hdr img{height:28px;}
    .bdy{padding:44px 40px;}
    h1{color:#111;font-size:22px;font-weight:600;margin:0 0 16px;}
    p{font-size:15px;line-height:1.7;margin:0 0 16px;color:#444;}
    .section-title{font-size:10px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#999;border-bottom:1px solid #eee;padding-bottom:8px;margin:28px 0 14px;}
    .info-table{width:100%;border-collapse:collapse;margin-bottom:8px;}
    .info-table td{padding:8px 0;border-bottom:1px solid #eee;font-size:14px;vertical-align:top;}
    .info-table td:first-child{color:#999;width:35%;font-size:12px;text-transform:uppercase;letter-spacing:.05em;padding-right:12px;}
    .info-table td:last-child{color:#222;font-weight:500;}
    .msg-box{background:#f5f5f5;border-radius:4px;padding:18px 20px;margin:14px 0;font-size:14px;line-height:1.75;color:#333;white-space:pre-wrap;word-break:break-word;}
    .note{font-size:12px;color:#999;border-top:1px solid #eee;padding-top:16px;margin-top:20px;line-height:1.7;}
    .ftr{background:#f9f9f9;padding:20px 40px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
  </div>
  <div class="bdy">
    @php
      $d = $data;
      $es = ($d['locale'] ?? 'es') !== 'en';

      $reasonLabelsEs = [
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
      $reasonLabelsEn = [
        'early_access' => 'Early Access App',
        'buy'          => 'Buy a watch',
        'sell'         => 'Sell a watch',
        'tech'         => 'Technical issue',
        'account'      => 'Account or access',
        'press'        => 'Press and media',
        'partners'     => 'Partnerships',
        'general'      => 'General enquiry',
        'other'        => 'Other',
      ];
      $labels = $es ? $reasonLabelsEs : $reasonLabelsEn;
      $reasonLabel = $labels[$d['reason'] ?? ''] ?? ($d['reason'] ?? '—');
      if (!empty($d['other_reason'])) $reasonLabel .= ' — ' . $d['other_reason'];
    @endphp

    @if($es)
      <h1>Hemos recibido tu mensaje, {{ $d['name'] }}</h1>
      <p>Gracias por contactar con Haus of Time. Hemos recibido tu solicitud y te responderemos lo antes posible, normalmente en menos de 24 horas.</p>

      <div class="section-title">Resumen de tu solicitud</div>
      <table class="info-table">
        <tr><td>Motivo</td><td><strong>{{ $reasonLabel }}</strong></td></tr>
      </table>

      <div class="section-title">Tu mensaje</div>
      <div class="msg-box">{{ $d['message'] }}</div>

      <p class="note">
        Este es un email de confirmación. Para hacer seguimiento de tu solicitud, responde directamente a este email o contáctanos en
        <a href="mailto:support@hausoftime.com" style="color:#b89064;">support@hausoftime.com</a>
      </p>
    @else
      <h1>We received your message, {{ $d['name'] }}</h1>
      <p>Thank you for contacting Haus of Time. We have received your request and will get back to you as soon as possible, usually within 24 hours.</p>

      <div class="section-title">Your request summary</div>
      <table class="info-table">
        <tr><td>Reason</td><td><strong>{{ $reasonLabel }}</strong></td></tr>
      </table>

      <div class="section-title">Your message</div>
      <div class="msg-box">{{ $d['message'] }}</div>

      <p class="note">
        This is a confirmation email. To follow up on your request, reply directly to this email or contact us at
        <a href="mailto:support@hausoftime.com" style="color:#b89064;">support@hausoftime.com</a>
      </p>
    @endif
  </div>
  <div class="ftr">© {{ date('Y') }} HAUSOFTIME.COM — Haus of Time</div>
</div>
</body>
</html>
