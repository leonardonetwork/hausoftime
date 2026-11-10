<!DOCTYPE html>
<html lang="{{ $locale ?? 'es' }}">
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
    .tag{display:inline-block;background:#111;color:#fff;font-size:10px;font-weight:700;letter-spacing:.12em;padding:4px 12px;border-radius:100px;text-transform:uppercase;margin-bottom:20px;}
    .highlight{background:#f9f6f2;border-left:3px solid #b89064;padding:18px 22px;margin:24px 0;}
    .highlight p{margin:6px 0;font-size:14px;color:#444;}
    .ftr{background:#f9f9f9;padding:20px 40px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
  </div>
  <div class="bdy">
    @if(($locale ?? 'es') === 'en')
      <div class="tag">Buyer — Waiting List</div>
      <h1>You're on the list, {{ $firstName }}!</h1>
      <p>Thank you for your interest in Haus of Time — the first authenticated marketplace with escrow payments for pre-owned luxury watches in Southern Europe.</p>
      <p>We have added you to our <strong>priority buyer list</strong>. You will be among the first to know when the platform opens to buyers.</p>
      <div class="highlight">
        <p>✓ You're on the priority buyer list</p>
        <p>✓ We'll notify you before the public launch</p>
        <p>✓ Early access to verified, authenticated watches</p>
      </div>
      <p style="font-size:13px;color:#888;">In the meantime, if you have any questions please contact us at <a href="mailto:support@hausoftime.com" style="color:#b89064;">support@hausoftime.com</a></p>
    @else
      <div class="tag">Comprador — Lista de Espera</div>
      <h1>¡Ya estás en la lista, {{ $firstName }}!</h1>
      <p>Gracias por tu interés en Haus of Time — el primer marketplace autenticado con pagos en escrow para relojes de lujo de segunda mano en el sur de Europa.</p>
      <p>Te hemos añadido a nuestra <strong>lista de compradores prioritarios</strong>. Serás de los primeros en saber cuando la plataforma abra al público.</p>
      <div class="highlight">
        <p>✓ Estás en la lista de compradores prioritarios</p>
        <p>✓ Te notificaremos antes del lanzamiento público</p>
        <p>✓ Acceso anticipado a relojes verificados y autenticados</p>
      </div>
      <p style="font-size:13px;color:#888;">Mientras tanto, si tienes alguna pregunta contáctanos en <a href="mailto:support@hausoftime.com" style="color:#b89064;">support@hausoftime.com</a></p>
    @endif
  </div>
  <div class="ftr">© {{ date('Y') }} HAUSOFTIME.COM — Haus of Time</div>
</div>
</body>
</html>
