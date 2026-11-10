{{-- resources/views/admin/newsletter/preview.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Preview: {{ $campaign->name }}</title>
  <style>
    body { margin: 0; background: #f4f4f4; font-family: 'Helvetica Neue', Arial, sans-serif; }
    .wrap { max-width: 620px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.12); }
    .header { background: #111111; padding: 32px 40px; text-align: center; }
    .header img { height: 30px; }
    .body { padding: 40px; color: #333; font-size: 16px; line-height: 1.65; }
    .footer { background: #f9f9f9; padding: 24px 40px; text-align: center; border-top: 1px solid #eee; font-size: 12px; color: #999; }
    .footer a { color: #b89064; }
    .lang-switch { display: flex; gap: 12px; justify-content: center; margin-bottom: 24px; }
    .lang-btn { padding: 6px 16px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-size: 13px; background: #fff; }
    .lang-btn.active { background: #111; color: #fff; border-color: #111; }
  </style>
</head>
<body>
  <div style="padding: 24px 0; text-align: center;">
    <div class="lang-switch">
      <button class="lang-btn active" onclick="showLang('es', this)">🇪🇸 Español</button>
      <button class="lang-btn" onclick="showLang('en', this)">🇬🇧 English</button>
    </div>
  </div>

  {{-- ES --}}
  <div id="preview-es" class="wrap">
    <div class="header">
      <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
    </div>
    <div class="body">
      <h2 style="color:#111;margin-bottom:20px;">{{ $campaign->subject_es }}</h2>
      {!! $campaign->body_es !!}
    </div>
    <div class="footer">
      <p>© Haus of Time · <a href="#">Política de privacidad</a></p>
      <p><a href="#">Darse de baja</a></p>
    </div>
  </div>

  {{-- EN --}}
  <div id="preview-en" class="wrap" style="display:none;">
    <div class="header">
      <img src="{{ asset('images/logo.png') }}" alt="Haus of Time">
    </div>
    <div class="body">
      <h2 style="color:#111;margin-bottom:20px;">{{ $campaign->subject_en }}</h2>
      {!! $campaign->body_en !!}
    </div>
    <div class="footer">
      <p>© Haus of Time · <a href="#">Privacy Policy</a></p>
      <p><a href="#">Unsubscribe</a></p>
    </div>
  </div>

  <script>
  function showLang(lang, btn) {
    document.getElementById('preview-es').style.display = lang === 'es' ? 'block' : 'none';
    document.getElementById('preview-en').style.display = lang === 'en' ? 'block' : 'none';
    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }
  </script>
</body>
</html>
