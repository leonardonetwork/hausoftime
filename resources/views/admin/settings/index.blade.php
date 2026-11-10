@extends('layouts.admin')
@section('title', __('messages.admin.settings'))

@section('admin-content')
<div class="page-title">{{ __('messages.admin.settings') }}</div>
<p class="page-subtitle">Configuración del sistema, correo e inteligencia artificial</p>

{{-- Tabs --}}
<div class="tabs" id="settingsTabs">
  <button class="tab active" onclick="showTab('general', this)">{{ __('messages.admin.settings_general') }}</button>
  <button class="tab" onclick="showTab('mail', this)">{{ __('messages.admin.settings_mail') }}</button>
  <button class="tab" onclick="showTab('ai', this)">{{ __('messages.admin.settings_ai') }}</button>
  <button class="tab" onclick="showTab('lists', this)">Listas Newsletter</button>
  <button class="tab" onclick="showTab('launch', this)">Lanzamiento</button>
  <button class="tab" onclick="showTab('payments', this)">Pagos</button>
  <button class="tab" onclick="showTab('shop', this)">Tienda</button>
</div>

{{-- ═══ GENERAL ════════════════════════════════════════════════ --}}
<div id="tab-general" class="tab-panel" style="max-width:640px;">
  <div class="card">
    <div class="card__num" style="margin-bottom:20px;">Configuración general</div>
    @if($errors->hasBag('general'))
      <div class="alert alert--error">@foreach($errors->getBag('general')->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif
    <form method="POST" action="{{ route('admin.settings.general') }}">
      @csrf @method('PUT')
      <div class="form-group">
        <label class="form-label">Nombre del sitio</label>
        <input type="text" name="site_name" class="form-control" value="{{ $gen['site_name'] ?? 'Haus of Time' }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email de contacto</label>
        <input type="email" name="contact_email" class="form-control" value="{{ $gen['contact_email'] ?? '' }}" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Comisión estándar (%)</label>
          <input type="number" name="commission_standard" class="form-control" value="{{ $gen['commission_standard'] ?? 7 }}" min="0" max="100" step="0.1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Comisión founding (%)</label>
          <input type="number" name="commission_founding" class="form-control" value="{{ $gen['commission_founding'] ?? 4 }}" min="0" max="100" step="0.1" required>
        </div>
      </div>
      <button type="submit" class="btn btn--primary">{{ __('messages.admin.save_settings') }}</button>
    </form>
  </div>
</div>

{{-- ═══ MAIL ════════════════════════════════════════════════════ --}}
<div id="tab-mail" class="tab-panel" style="display:none;max-width:720px;">
  <div class="card" style="margin-bottom:20px;">
    <div class="card__num" style="margin-bottom:20px;">Configuración de correo</div>

    <form method="POST" action="{{ route('admin.settings.mail') }}" id="mailForm">
      @csrf @method('PUT')

      {{-- Driver selector --}}
      <div class="form-group">
        <label class="form-label">Proveedor</label>
        <div class="radio-group" id="driverGroup">
          <label class="radio-card {{ ($mail['mail_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}" data-value="smtp">
            <input type="radio" name="mail_driver" value="smtp" {{ ($mail['mail_driver'] ?? 'smtp') === 'smtp' ? 'checked' : '' }}>
            <div class="radio-card__dot"></div>
            <div><div class="radio-card__label">SMTP</div><div style="font-size:.75rem;color:var(--text-muted);">Mailgun, SendGrid, etc.</div></div>
          </label>
          <label class="radio-card {{ ($mail['mail_driver'] ?? '') === 'ses' ? 'selected' : '' }}" data-value="ses">
            <input type="radio" name="mail_driver" value="ses" {{ ($mail['mail_driver'] ?? '') === 'ses' ? 'checked' : '' }}>
            <div class="radio-card__dot"></div>
            <div><div class="radio-card__label">Amazon SES</div><div style="font-size:.75rem;color:var(--text-muted);">AWS Simple Email Service</div></div>
          </label>
        </div>
      </div>

      {{-- Common from --}}
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email remitente</label>
          <input type="email" name="mail_from_address" class="form-control" value="{{ $mail['mail_from_address'] ?? '' }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nombre remitente</label>
          <input type="text" name="mail_from_name" class="form-control" value="{{ $mail['mail_from_name'] ?? 'Haus of Time' }}" required>
        </div>
      </div>

      {{-- SMTP fields --}}
      <div id="smtp-fields">
        <div style="height:1px;background:var(--bg-border);margin:20px 0;"></div>
        <p style="font-size:.75rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:16px;">Configuración SMTP</p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Host SMTP</label>
            <input type="text" name="mail_host" class="form-control" value="{{ $mail['mail_host'] ?? '' }}" placeholder="smtp.mailgun.org">
          </div>
          <div class="form-group">
            <label class="form-label">Puerto</label>
            <input type="number" name="mail_port" class="form-control" value="{{ $mail['mail_port'] ?? 587 }}" placeholder="587">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Usuario</label>
            <input type="text" name="mail_username" class="form-control" value="{{ $mail['mail_username'] ?? '' }}" autocomplete="off">
          </div>
          <div class="form-group">
            <label class="form-label">Contraseña</label>
            <input type="password" name="mail_password" class="form-control" placeholder="••••••••" autocomplete="new-password">
            <p style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">Deja en blanco para mantener la actual.</p>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Encriptación</label>
          <select name="mail_encryption" class="form-control form-control--select">
            <option value="tls"  {{ ($mail['mail_encryption'] ?? 'tls') === 'tls'  ? 'selected':'' }}>TLS</option>
            <option value="ssl"  {{ ($mail['mail_encryption'] ?? '') === 'ssl'  ? 'selected':'' }}>SSL</option>
            <option value=""     {{ ($mail['mail_encryption'] ?? '') === ''     ? 'selected':'' }}>Ninguna</option>
          </select>
        </div>
      </div>

      {{-- SES fields --}}
      <div id="ses-fields" style="display:none;">
        <div style="height:1px;background:var(--bg-border);margin:20px 0;"></div>
        <p style="font-size:.75rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:16px;">Amazon SES</p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">AWS Access Key ID</label>
            <input type="text" name="aws_key" class="form-control" value="{{ $mail['aws_key'] ?? '' }}" autocomplete="off">
          </div>
          <div class="form-group">
            <label class="form-label">AWS Secret Access Key</label>
            <input type="password" name="aws_secret" class="form-control" placeholder="••••••••">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Región AWS</label>
          <select name="aws_region" class="form-control form-control--select">
            @foreach(['eu-west-1'=>'EU (Irlanda)','eu-central-1'=>'EU (Frankfurt)','us-east-1'=>'US (N. Virginia)','us-west-2'=>'US (Oregon)'] as $v=>$l)
              <option value="{{ $v }}" {{ ($mail['aws_region'] ?? 'eu-west-1') === $v ? 'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:24px;">
        <button type="submit" class="btn btn--primary">{{ __('messages.admin.save_settings') }}</button>
      </div>
    </form>

    {{-- Test mail --}}
    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--bg-border);">
      <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:12px;">Prueba la configuración enviando un email:</p>
      <form method="POST" action="{{ route('admin.settings.test-mail') }}" style="display:flex;gap:10px;">
        @csrf
        <input type="email" name="test_email" class="form-control" placeholder="{{ auth()->user()->email }}" style="flex:1;" required>
        <button type="submit" class="btn btn--ghost">{{ __('messages.admin.test_mail') }}</button>
      </form>
    </div>
  </div>
</div>

{{-- ═══ AI ═══════════════════════════════════════════════════════ --}}
<div id="tab-ai" class="tab-panel" style="display:none;max-width:640px;">
  <div class="card">
    <div class="card__num" style="margin-bottom:20px;">Inteligencia Artificial</div>
    <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:24px;line-height:1.65;">
      Configura el proveedor de IA para las evaluaciones de relojes. Las claves se almacenan cifradas.
    </p>

    <form method="POST" action="{{ route('admin.settings.ai') }}">
      @csrf @method('PUT')

      <div class="form-group">
        <label class="form-label">Proveedor activo</label>
        <div class="radio-group" id="aiProviderGroup">
          <label class="radio-card {{ ($ai['ai_provider'] ?? 'openai') === 'openai' ? 'selected' : '' }}" data-value="openai">
            <input type="radio" name="ai_provider" value="openai" {{ ($ai['ai_provider'] ?? 'openai') === 'openai' ? 'checked' : '' }}>
            <div class="radio-card__dot"></div>
            <div><div class="radio-card__label">OpenAI</div><div style="font-size:.75rem;color:var(--text-muted);">GPT-4o, GPT-4 Turbo</div></div>
          </label>
          <label class="radio-card {{ ($ai['ai_provider'] ?? '') === 'anthropic' ? 'selected' : '' }}" data-value="anthropic">
            <input type="radio" name="ai_provider" value="anthropic" {{ ($ai['ai_provider'] ?? '') === 'anthropic' ? 'checked' : '' }}>
            <div class="radio-card__dot"></div>
            <div><div class="radio-card__label">Anthropic</div><div style="font-size:.75rem;color:var(--text-muted);">Claude Sonnet, Opus</div></div>
          </label>
        </div>
      </div>

      {{-- OpenAI --}}
      <div id="ai-openai-fields">
        <div style="height:1px;background:var(--bg-border);margin:20px 0;"></div>
        <p style="font-size:.75rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:16px;">OpenAI</p>
        <div class="form-group">
          <label class="form-label">API Key</label>
          <input type="password" name="openai_key" class="form-control" placeholder="sk-••••••••" autocomplete="off">
          <p style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">Deja en blanco para mantener la actual.</p>
        </div>
        <div class="form-group">
          <label class="form-label">Modelo</label>
          <select name="openai_model" class="form-control form-control--select">
            @foreach(\App\Services\AiClient::OPENAI_MODELS as $v=>$l)
              <option value="{{ $v }}" {{ ($ai['openai_model'] ?? \App\Services\AiClient::DEFAULT_OPENAI) === $v ? 'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Anthropic --}}
      <div id="ai-anthropic-fields" style="display:none;">
        <div style="height:1px;background:var(--bg-border);margin:20px 0;"></div>
        <p style="font-size:.75rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:16px;">Anthropic</p>
        <div class="form-group">
          <label class="form-label">API Key</label>
          <input type="password" name="anthropic_key" class="form-control" placeholder="sk-ant-••••••••" autocomplete="off">
          <p style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">Deja en blanco para mantener la actual.</p>
        </div>
        <div class="form-group">
          <label class="form-label">Modelo</label>
          <select name="anthropic_model" class="form-control form-control--select">
            @foreach(\App\Services\AiClient::ANTHROPIC_MODELS as $v=>$l)
              <option value="{{ $v }}" {{ ($ai['anthropic_model'] ?? \App\Services\AiClient::DEFAULT_ANTHROPIC) === $v ? 'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div style="margin-top:24px;">
        <button type="submit" class="btn btn--primary">{{ __('messages.admin.save_settings') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- ═══ LISTAS ══════════════════════════════════════════════════ --}}
<div id="tab-lists" class="tab-panel" style="display:none;max-width:640px;">
  <div class="card">
    <div class="card__num" style="margin-bottom:20px;">Asignación de listas a formularios</div>

    @php
      $availableLists = \App\Models\NewsletterList::orderBy('name')->get();
      $listsConf      = \App\Models\Setting::group('lists');
    @endphp

    @if($availableLists->isEmpty())
      <div class="alert alert--warning" style="margin-bottom:20px;">
        No hay listas creadas todavía.
        <a href="{{ route('admin.newsletter.lists.index') }}" style="color:var(--a-gold);font-weight:600;">
          Crea tu primera lista →
        </a>
      </div>
    @else
      <p style="font-size:12px;color:var(--a-muted);margin-bottom:24px;line-height:1.7;">
        Selecciona qué lista se asigna a cada formulario. Los nuevos suscriptores
        se añadirán automáticamente a la lista elegida.
      </p>
      <form method="POST" action="{{ route('admin.settings.lists') }}">
        @csrf @method('PUT')

        {{-- Evaluación --}}
        <div class="form-group">
          <label class="form-label">Lista — Evaluación de Reloj</label>
          <select name="list_id_evaluacion" class="form-control">
            <option value="">— Sin asignar —</option>
            @foreach($availableLists as $list)
              <option value="{{ $list->slug }}"
                {{ ($listsConf['list_id_evaluacion'] ?? '') === $list->slug ? 'selected' : '' }}>
                {{ $list->name }}
                ({{ $list->activeSubscribers()->count() }} activos)
              </option>
            @endforeach
          </select>
          <p style="font-size:11px;color:var(--a-subtle);margin-top:4px;">
            Formulario: /evaluacion-reloj
          </p>
        </div>

        {{-- Compradores --}}
        <div class="form-group">
          <label class="form-label">Lista — Early Access Compradores</label>
          <select name="list_id_buyer" class="form-control">
            <option value="">— Sin asignar —</option>
            @foreach($availableLists as $list)
              <option value="{{ $list->slug }}"
                {{ ($listsConf['list_id_buyer'] ?? '') === $list->slug ? 'selected' : '' }}>
                {{ $list->name }}
                ({{ $list->activeSubscribers()->count() }} activos)
              </option>
            @endforeach
          </select>
          <p style="font-size:11px;color:var(--a-subtle);margin-top:4px;">
            Formulario: /acceso-anticipado → tipo: comprador
          </p>
        </div>

        {{-- Vendedores --}}
        <div class="form-group">
          <label class="form-label">Lista — Early Access Vendedores</label>
          <select name="list_id_seller" class="form-control">
            <option value="">— Sin asignar —</option>
            @foreach($availableLists as $list)
              <option value="{{ $list->slug }}"
                {{ ($listsConf['list_id_seller'] ?? '') === $list->slug ? 'selected' : '' }}>
                {{ $list->name }}
                ({{ $list->activeSubscribers()->count() }} activos)
              </option>
            @endforeach
          </select>
          <p style="font-size:11px;color:var(--a-subtle);margin-top:4px;">
            Formulario: /acceso-anticipado → tipo: vendedor
          </p>
        </div>

        <button type="submit" class="btn btn--primary">{{ __('messages.admin.save_settings') }}</button>
      </form>
    @endif

    {{-- Quick link --}}
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--a-border);font-size:12px;color:var(--a-muted);">
      ¿Falta alguna lista?
      <a href="{{ route('admin.newsletter.lists.index') }}" style="color:var(--a-gold);">
        Gestionar listas →
      </a>
    </div>
  </div>
</div>


{{-- ═══ LANZAMIENTO ════════════════════════════════════════════ --}}
<div id="tab-launch" class="tab-panel" style="display:none;max-width:760px;">
  <div class="card" style="margin-bottom:20px">
    <div class="card__num" style="margin-bottom:16px">Early Access / Live</div>
    <form method="POST" action="{{ route('admin.settings.launch') }}">
      @csrf @method('PUT')
      <div class="radio-group" style="margin-bottom:22px">
        <label class="radio-card {{ $launch['site_mode'] !== 'live' ? 'selected' : '' }}">
          <input type="radio" name="site_mode" value="early_access" @checked($launch['site_mode'] !== 'live')>
          <div class="radio-card__dot"></div>
          <div><div class="radio-card__label">Early Access</div><div style="font-size:.75rem;color:var(--a-muted)">Landing actual; tienda oculta al público</div></div>
        </label>
        <label class="radio-card {{ $launch['site_mode'] === 'live' ? 'selected' : '' }}">
          <input type="radio" name="site_mode" value="live" @checked($launch['site_mode'] === 'live')>
          <div class="radio-card__dot"></div>
          <div><div class="radio-card__label">Live</div><div style="font-size:.75rem;color:var(--a-muted)">Marketplace público con compras</div></div>
        </label>
      </div>
      <div class="form-group">
        <label class="form-label">Lista blanca · emails (uno por línea)</label>
        <textarea name="live_whitelist_emails" class="form-control" placeholder="cliente@ejemplo.com">{{ $launch['live_whitelist_emails'] }}</textarea>
        <div style="font-size:11px;color:var(--a-subtle);margin-top:6px">Estos usuarios, una vez identificados, ven la versión Live aunque el sitio esté en Early Access. Los administradores siempre pueden verla.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Lista blanca · IP (una por línea, admite CIDR)</label>
        <textarea name="live_whitelist_ips" class="form-control" placeholder="176.107.152.20&#10;10.0.0.0/24" style="min-height:70px">{{ $launch['live_whitelist_ips'] }}</textarea>
        <div style="font-size:11px;color:var(--a-subtle);margin-top:6px">Tu IP actual: {{ request()->ip() }}</div>
      </div>
      <button type="submit" class="btn btn--primary">Guardar lanzamiento</button>
    </form>
  </div>
  <div class="card">
    <div class="card__num" style="margin-bottom:12px">Enlace privado de vista previa</div>
    <p style="font-size:12px;color:var(--a-muted);margin-bottom:12px">Quien abra este enlace verá la versión Live durante 30 días en ese navegador, sin necesidad de cuenta. Compártelo solo con personas de confianza.</p>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <input class="form-control" readonly value="{{ route('preview.enter', $launch['live_preview_token']) }}" onclick="this.select()" style="flex:1;min-width:280px;font-family:monospace;font-size:12px">
      <button type="button" class="btn btn--ghost" onclick="navigator.clipboard.writeText('{{ route('preview.enter', $launch['live_preview_token']) }}');this.textContent='Copiado'">Copiar</button>
      <form method="POST" action="{{ route('admin.settings.preview-token') }}" onsubmit="return confirm('¿Generar un nuevo enlace? El actual dejará de funcionar.')">@csrf<button class="btn btn--ghost">Regenerar</button></form>
    </div>
  </div>
</div>

{{-- ═══ PAGOS (Stripe Connect) ════════════════════════════════════ --}}
<div id="tab-payments" class="tab-panel" style="display:none;max-width:860px;">
  <div class="card" style="margin-bottom:20px">
    <div class="card__num" style="margin-bottom:6px">Stripe Connect</div>
    <p style="font-size:12px;color:var(--a-muted);margin-bottom:18px;line-height:1.7">
      El comprador paga a la cuenta Stripe de HAUS OF TIME. Los fondos quedan retenidos hasta que confirma la recepción o vence el plazo, y entonces se transfieren al vendedor (cuenta Express) menos la comisión.
      Activa <b>Connect</b> en tu Dashboard de Stripe y los métodos de pago (tarjeta, Apple Pay, Google Pay, transferencia…) en Configuración → Métodos de pago.
    </p>
    <form method="POST" action="{{ route('admin.settings.payments') }}" autocomplete="off">
      @csrf @method('PUT')
      <div class="radio-group" style="margin-bottom:22px">
        <label class="radio-card {{ $pay['stripe_mode'] !== 'live' ? 'selected' : '' }}"><input type="radio" name="stripe_mode" value="test" @checked($pay['stripe_mode'] !== 'live')><div class="radio-card__dot"></div><div><div class="radio-card__label">Modo prueba</div><div style="font-size:.75rem;color:var(--a-muted)">Tarjetas de test, sin cargos reales</div></div></label>
        <label class="radio-card {{ $pay['stripe_mode'] === 'live' ? 'selected' : '' }}"><input type="radio" name="stripe_mode" value="live" @checked($pay['stripe_mode'] === 'live')><div class="radio-card__dot"></div><div><div class="radio-card__label">Modo real</div><div style="font-size:.75rem;color:var(--a-muted)">Cobros reales</div></div></label>
      </div>
      @foreach(['test' => 'Claves de prueba', 'live' => 'Claves reales'] as $m => $title)
        <div style="font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--a-gold);margin:10px 0 12px">{{ $title }}</div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Publishable key</label><input name="stripe_{{ $m }}_pk" class="form-control" value="{{ $pay["stripe_{$m}_pk"] }}" placeholder="pk_{{ $m }}_…"></div>
          <div class="form-group"><label class="form-label">Secret key @if($pay["stripe_{$m}_sk_set"])<span class="badge badge-success">{{ $pay["stripe_{$m}_sk_set"] }}</span>@endif</label><input type="password" name="stripe_{{ $m }}_sk" class="form-control" placeholder="{{ $pay["stripe_{$m}_sk_set"] ? 'Guardada · deja vacío para mantener' : 'sk_' . $m . '_…' }}" autocomplete="new-password"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Webhook secret (cuenta) @if($pay["stripe_{$m}_whsec_set"])<span class="badge badge-success">{{ $pay["stripe_{$m}_whsec_set"] }}</span>@endif</label><input type="password" name="stripe_{{ $m }}_whsec" class="form-control" placeholder="whsec_…" autocomplete="new-password"></div>
          <div class="form-group"><label class="form-label">Webhook secret (Connect) @if($pay["stripe_{$m}_whsec_connect_set"])<span class="badge badge-success">{{ $pay["stripe_{$m}_whsec_connect_set"] }}</span>@endif</label><input type="password" name="stripe_{{ $m }}_whsec_connect" class="form-control" placeholder="whsec_…" autocomplete="new-password"></div>
        </div>
      @endforeach
      <button type="submit" class="btn btn--primary">Guardar pagos</button>
    </form>
  </div>
  <div class="card">
    <div class="card__num" style="margin-bottom:10px">Webhooks a crear en Stripe</div>
    <dl class="kv">
      <dt>URL</dt><dd style="font-family:monospace">{{ route('stripe.webhook') }}</dd>
      <dt>Eventos cuenta</dt><dd style="font-size:12px">checkout.session.completed, checkout.session.async_payment_succeeded, checkout.session.async_payment_failed, checkout.session.expired, charge.dispute.created, charge.refunded</dd>
      <dt>Eventos Connect</dt><dd style="font-size:12px">account.updated (endpoint "Connected accounts", misma URL)</dd>
    </dl>
    <form method="POST" action="{{ route('admin.settings.payments.test') }}" style="margin-top:16px">@csrf<button class="btn btn--ghost">Probar conexión con Stripe</button></form>
  </div>
</div>

{{-- ═══ TIENDA ═══════════════════════════════════════════════════ --}}
<div id="tab-shop" class="tab-panel" style="display:none;max-width:640px;">
  <div class="card">
    <div class="card__num" style="margin-bottom:20px">Plazos del marketplace</div>
    <form method="POST" action="{{ route('admin.settings.shop') }}">
      @csrf @method('PUT')
      <div class="form-row">
        <div class="form-group"><label class="form-label">Días laborables para enviar</label><input type="number" name="ship_within_days" class="form-control" min="1" max="15" value="{{ $shop['ship_within_days'] }}" required></div>
        <div class="form-group"><label class="form-label">Liberación automática (días tras el envío)</label><input type="number" name="release_after_days" class="form-control" min="1" max="90" value="{{ $shop['release_after_days'] }}" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Reserva durante el pago (minutos)</label><input type="number" name="reservation_minutes" class="form-control" min="30" max="240" value="{{ $shop['reservation_minutes'] }}" required></div>
        <div class="form-group"><label class="form-label">Email avisos de pedidos</label><input type="email" name="orders_email" class="form-control" value="{{ $shop['orders_email'] }}" placeholder="Por defecto: email de contacto"></div>
      </div>
      <div class="form-group" style="margin-top:8px">
        <label class="form-label">Aprobación de relojes publicados por vendedores</label>
        <div class="radio-group" style="display:grid;grid-template-columns:1fr;gap:10px">
          @foreach([
            'auto'              => ['Aprobados por defecto', 'Se publican al instante tras la foto de verificación, en Early Access y en Live.'],
            'manual'            => ['Todos requieren aprobación', 'Cada reloj queda pendiente hasta que lo apruebes desde Productos.'],
            'auto_early_access' => ['Aprobados solo en Early Access', 'Publicación instantánea mientras el sitio está en Early Access; en Live requieren aprobación.'],
          ] as $v => [$l, $d])
          <label class="radio-card {{ $shop['watch_approval_mode'] === $v ? 'selected' : '' }}"><input type="radio" name="watch_approval_mode" value="{{ $v }}" @checked($shop['watch_approval_mode'] === $v)><div class="radio-card__dot"></div><div><div class="radio-card__label">{{ $l }}</div><div style="font-size:.75rem;color:var(--a-muted)">{{ $d }}</div></div></label>
          @endforeach
        </div>
        <p style="font-size:11px;color:var(--a-subtle);margin-top:8px">Un reloj rechazado y luego modificado vuelve siempre a revisión.</p>
      </div>
      <p style="font-size:11px;color:var(--a-subtle);margin-bottom:16px">Las comisiones (estándar / founding) se configuran en la pestaña General.</p>
      <button type="submit" class="btn btn--primary">Guardar</button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Settings tabs
function showTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('#settingsTabs .tab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).style.display = 'block';
  btn.classList.add('active');
}

// Mail driver toggle
function updateMailDriver() {
  const smtpSelected = document.querySelector('[name="mail_driver"]:checked')?.value === 'smtp';
  document.getElementById('smtp-fields').style.display = smtpSelected ? 'block' : 'none';
  document.getElementById('ses-fields').style.display  = smtpSelected ? 'none'  : 'block';
}
document.querySelectorAll('[name="mail_driver"]').forEach(r => r.addEventListener('change', updateMailDriver));
updateMailDriver();

// AI provider toggle
function updateAiProvider() {
  const openai = document.querySelector('[name="ai_provider"]:checked')?.value === 'openai';
  document.getElementById('ai-openai-fields').style.display     = openai ? 'block' : 'none';
  document.getElementById('ai-anthropic-fields').style.display  = openai ? 'none'  : 'block';
}
document.querySelectorAll('[name="ai_provider"]').forEach(r => r.addEventListener('change', updateAiProvider));
updateAiProvider();

// Riapre la scheda giusta dopo il salvataggio
@if(session('settings_tab'))
  (function(){ var b=[...document.querySelectorAll('#settingsTabs .tab')].find(t=>t.getAttribute('onclick').includes("'{{ session('settings_tab') }}'")); if(b) showTab('{{ session('settings_tab') }}', b); })();
@endif

// Radio cards visual
document.querySelectorAll('.radio-card').forEach(card => {
  card.addEventListener('click', () => {
    const grp = card.closest('.radio-group');
    grp.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
  });
});
</script>
@endpush
