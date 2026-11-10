{{-- resources/views/emails/watch-rejected.blade.php --}}
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body{margin:0;padding:0;background:#f4f4f4;font-family:'Helvetica Neue',Arial,sans-serif;}
    .wrap{max-width:600px;margin:40px auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #ddd8ce;}
    .hdr{background:#111;padding:32px 40px;text-align:center;}
    .hdr img{height:28px;}
    .bdy{padding:40px;}
    .bdy h1{color:#111;font-size:20px;margin:0 0 16px;}
    .bdy p{font-size:15px;line-height:1.65;margin:0 0 14px;color:#444;}
    .note-box{background:#fdf6f6;border-left:3px solid #9b3333;padding:20px 24px;border-radius:4px;margin:20px 0;}
    .note-box p{margin:4px 0;font-size:14px;color:#555;}
    .btn{display:inline-block;background:#b89064;color:#fff;text-decoration:none;padding:14px 32px;border-radius:4px;font-weight:600;font-size:14px;}
    .ftr{background:#f9f9f9;padding:20px 40px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;}
    .ftr a{color:#b89064;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="hdr"><img src="{{ asset('images/logo.png') }}" alt="Haus of Time"></div>
  <div class="bdy">
    @php $locale = $watch->seller?->locale ?? 'es'; @endphp
    @if($locale === 'en')
      <h1>Update on your watch listing</h1>
      <p>After reviewing your listing for <strong>{{ $watch->brand }} {{ $watch->model }}</strong>, we were unable to approve it at this time.</p>
      @if($watch->admin_notes)
        <div class="note-box"><p><strong>Reason:</strong> {{ $watch->admin_notes }}</p></div>
      @endif
      <p>You can edit your listing and resubmit it for review. If you have questions, contact our team.</p>
      <a href="{{ route('seller.watches.edit', $watch) }}" class="btn">Edit and resubmit →</a>
    @else
      <h1>Actualización sobre tu reloj</h1>
      <p>Tras revisar tu anuncio de <strong>{{ $watch->brand }} {{ $watch->model }}</strong>, no hemos podido aprobarlo en este momento.</p>
      @if($watch->admin_notes)
        <div class="note-box"><p><strong>Motivo:</strong> {{ $watch->admin_notes }}</p></div>
      @endif
      <p>Puedes editar tu anuncio y volver a enviarlo a revisión. Si tienes dudas, contacta con nuestro equipo.</p>
      <a href="{{ route('seller.watches.edit', $watch) }}" class="btn">Editar y reenviar →</a>
    @endif
  </div>
  <div class="ftr">© Haus of Time · <a href="{{ route('privacy') }}">Privacidad</a></div>
</div>
</body>
</html>
