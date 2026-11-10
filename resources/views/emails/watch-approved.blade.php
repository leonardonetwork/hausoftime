{{-- resources/views/emails/watch-approved.blade.php --}}
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
    .watch-box{background:#f9f6f2;border-left:3px solid #b89064;padding:20px 24px;border-radius:4px;margin:24px 0;}
    .watch-box p{margin:4px 0;font-size:14px;}
    .badge{display:inline-block;background:#3d8a5e;color:#fff;padding:4px 14px;border-radius:100px;font-size:13px;font-weight:700;margin-bottom:16px;}
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
      <span class="badge">✓ Approved</span>
      <h1>Your watch has been approved!</h1>
      <p>Great news — your watch listing has been reviewed and approved. It will be visible to buyers on the platform.</p>
      <div class="watch-box">
        <p><strong>{{ $watch->brand }} {{ $watch->model }}</strong></p>
        @if($watch->reference)<p>Ref. {{ $watch->reference }}</p>@endif
        <p>Price: {{ $watch->formatted_price }}</p>
      </div>
      <a href="{{ route('seller.watches') }}" class="btn">View my watches →</a>
    @else
      <span class="badge">✓ Aprobado</span>
      <h1>¡Tu reloj ha sido aprobado!</h1>
      <p>Buenas noticias — tu reloj ha sido revisado y aprobado. Será visible para compradores en la plataforma.</p>
      <div class="watch-box">
        <p><strong>{{ $watch->brand }} {{ $watch->model }}</strong></p>
        @if($watch->reference)<p>Ref. {{ $watch->reference }}</p>@endif
        <p>Precio: {{ $watch->formatted_price }}</p>
      </div>
      <a href="{{ route('seller.watches') }}" class="btn">Ver mis relojes →</a>
    @endif
  </div>
  <div class="ftr">© Haus of Time · <a href="{{ route('privacy') }}">Privacidad</a></div>
</div>
</body>
</html>
