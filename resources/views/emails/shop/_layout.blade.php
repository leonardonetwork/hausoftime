<!DOCTYPE html>
<html lang="{{ $lang ?? 'es' }}">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{margin:0;padding:0;background:#f3f1ee;font-family:'Helvetica Neue',Arial,sans-serif;color:#222}
    .wrap{max-width:620px;margin:32px auto;background:#fff;border-radius:18px;overflow:hidden;border:1px solid #e7e3dc}
    .hdr{background:#111;padding:30px 40px;text-align:center}
    .hdr img{height:24px}
    .bdy{padding:40px}
    .eyebrow{font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#a07d43;margin:0 0 10px}
    h1{font-size:22px;font-weight:600;margin:0 0 14px;color:#111}
    p{font-size:15px;line-height:1.65;margin:0 0 14px;color:#444}
    .item{display:block;border:1px solid #eee;border-radius:14px;padding:14px;margin:0 0 10px}
    .item td{vertical-align:middle}
    .item img{width:64px;height:78px;object-fit:cover;border-radius:8px;background:#efefef}
    .t{font-size:13px;font-weight:700;text-transform:uppercase;color:#111}
    .s{font-size:12px;color:#888}
    .amt{font-size:14px;font-weight:600;text-align:right;white-space:nowrap}
    table.sum{width:100%;border-collapse:collapse;margin:16px 0}
    table.sum td{padding:6px 0;font-size:14px;color:#444}
    table.sum td:last-child{text-align:right}
    table.sum tr.tot td{border-top:1px solid #eee;padding-top:12px;font-weight:700;font-size:16px;color:#111}
    .box{background:#f8f5ef;border-left:3px solid #b08a4e;padding:16px 18px;margin:20px 0;font-size:14px;line-height:1.6;color:#333;border-radius:4px}
    .addr{white-space:pre-line;font-size:14px;line-height:1.6;color:#333}
    .btn-wrap{text-align:center;margin:28px 0 8px}
    .btn{display:inline-block;background:#111;color:#fff !important;text-decoration:none;padding:16px 36px;font-size:12px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;border-radius:40px}
    .ftr{background:#faf9f7;padding:20px 40px;text-align:center;font-size:11.5px;color:#999;border-top:1px solid #eee;line-height:1.6}
  </style>
</head>
<body>
<div class="wrap">
  <div class="hdr"><img src="{{ rtrim(config('app.url'), '/') }}/images/logo.png" alt="HAUS OF TIME"></div>
  <div class="bdy">@yield('body')</div>
  <div class="ftr">HAUS OF TIME · {{ ($lang ?? 'es') === 'en' ? 'The luxury watch marketplace built on trust' : 'El marketplace de relojes de lujo basado en la confianza' }}<br>
    <a href="{{ rtrim(config('app.url'), '/') }}" style="color:#999">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a></div>
</div>
</body>
</html>
