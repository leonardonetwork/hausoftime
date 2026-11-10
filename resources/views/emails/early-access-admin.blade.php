<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body{margin:0;padding:0;background:#f4f4f4;font-family:'Helvetica Neue',Arial,sans-serif;color:#333;}
    .wrap{max-width:600px;margin:40px auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #ddd8ce;}
    .hdr{background:#111;padding:24px 40px;text-align:center;}
    .hdr img{height:24px;}
    .hdr-sub{color:#888;font-size:11px;letter-spacing:.15em;text-transform:uppercase;margin-top:8px;}
    .bdy{padding:40px;}
    h1{color:#111;font-size:20px;font-weight:600;margin:0 0 6px;}
    .subtitle{font-size:13px;color:#888;margin:0 0 28px;}
    p{font-size:15px;line-height:1.7;margin:0 0 14px;color:#444;}
    .type-badge{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.1em;padding:4px 14px;border-radius:100px;text-transform:uppercase;margin-bottom:24px;}
    .type-seller{background:#111;color:#fff;}
    .type-buyer{background:#3d7a5e;color:#fff;}
    .type-both{background:#b89064;color:#fff;}
    .section-title{font-size:10px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#999;border-bottom:2px solid #111;padding-bottom:8px;margin:28px 0 14px;}
    .info-table{width:100%;border-collapse:collapse;margin-bottom:8px;}
    .info-table td{padding:9px 0;border-bottom:1px solid #eee;font-size:14px;vertical-align:top;}
    .info-table td:first-child{color:#999;width:40%;font-size:12px;text-transform:uppercase;letter-spacing:.05em;padding-right:12px;}
    .info-table td:last-child{color:#222;font-weight:500;}
    .btn-wrap{text-align:center;margin:28px 0 0;}
    .btn{display:inline-block;background:#111;color:#fff !important;text-decoration:none;padding:14px 36px;font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;}
    .ftr{background:#f9f9f9;padding:16px 40px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
    <div class="hdr-sub">Admin Notification — Early Access</div>
  </div>
  <div class="bdy">

    @php
      $type = $ear->type ?? 'unknown';
      $brands = is_string($ear->watch_brands ?? null)
        ? implode(', ', json_decode($ear->watch_brands, true) ?? [])
        : (implode(', ', $ear->watch_brands ?? []));
    @endphp

    <span class="type-badge {{ $type === 'buyer' ? 'type-buyer' : ($type === 'both' ? 'type-both' : 'type-seller') }}">
      {{ $type === 'seller' ? 'Nuevo Vendedor' : ($type === 'buyer' ? 'Nuevo Comprador' : 'Nuevo Seller + Buyer') }}
    </span>

    <h1>{{ $ear->first_name }} {{ $ear->last_name }}</h1>
    <p class="subtitle">Registro vía Early Access · {{ now()->format('d/m/Y H:i') }}</p>

    <div class="section-title">Datos de contacto</div>
    <table class="info-table">
      <tr><td>Nombre</td><td>{{ $ear->first_name }} {{ $ear->last_name }}</td></tr>
      <tr><td>Email</td><td><a href="mailto:{{ $ear->email }}" style="color:#b89064;">{{ $ear->email }}</a></td></tr>
      @if($ear->phone)
      <tr><td>Teléfono</td><td>{{ $ear->phone_prefix }} {{ $ear->phone }}</td></tr>
      @endif
      @if($ear->country)
      <tr><td>País</td><td>{{ $ear->country }}</td></tr>
      @endif
      @if($ear->city)
      <tr><td>Ciudad</td><td>{{ $ear->city }}</td></tr>
      @endif
      <tr><td>Tipo de acceso</td><td>{{ strtoupper($type) }}</td></tr>
      <tr><td>Idioma</td><td>{{ strtoupper($ear->locale ?? 'ES') }}</td></tr>
    </table>

    @if($type !== 'buyer' && ($ear->watch_brands || $ear->watch_count))
    <div class="section-title">Inventario declarado</div>
    <table class="info-table">
      @if($brands)
      <tr><td>Marcas</td><td>{{ $brands }}</td></tr>
      @endif
      @if($ear->watch_count)
      <tr><td>Nº relojes aprox.</td><td>{{ $ear->watch_count }}</td></tr>
      @endif
      @if($ear->estimated_value ?? null)
      <tr><td>Valor estimado</td><td>{{ $ear->estimated_value }}</td></tr>
      @endif
    </table>
    @endif

    @if($user)
    <div class="section-title">Cuenta creada</div>
    <table class="info-table">
      <tr><td>User ID</td><td>#{{ $user->id }}</td></tr>
      <tr><td>Estado</td><td><span style="color:#3d7a5e;font-weight:700;">✓ {{ strtoupper($user->status) }}</span></td></tr>
      <tr><td>Founding Seller</td><td>{{ $user->is_founding_seller ? 'Sí ⭐' : 'No' }}</td></tr>
    </table>
    <div class="btn-wrap">
      <a href="{{ rtrim(config('app.url'),'/') }}/admin/suscriptores" class="btn">Ver en admin →</a>
    </div>
    @endif

  </div>
  <div class="ftr">Haus of Time · Notificación interna · {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
