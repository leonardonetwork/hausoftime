<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index()
    {
        $mail = Setting::group('mail');
        $ai   = Setting::group('ai');
        $gen  = Setting::group('general');
        $launch = [
            'site_mode'             => Setting::get('site_mode', 'early_access'),
            'live_whitelist_emails' => Setting::get('live_whitelist_emails', ''),
            'live_whitelist_ips'    => Setting::get('live_whitelist_ips', ''),
            'live_preview_token'    => Setting::get('live_preview_token', ''),
        ];
        $pay = ['stripe_mode' => Setting::get('stripe_mode', 'test')];
        foreach (['test', 'live'] as $m) {
            $pay["stripe_{$m}_pk"] = Setting::get("stripe_{$m}_pk", '');
            foreach (['sk', 'whsec', 'whsec_connect'] as $k) {
                $v = (string) Setting::get("stripe_{$m}_{$k}", '');
                $pay["stripe_{$m}_{$k}_set"] = $v !== '' ? '••••' . substr($v, -4) : null;
            }
        }
        $shop = [];
        foreach (['ship_within_days' => 2, 'release_after_days' => 30, 'reservation_minutes' => 30, 'orders_email' => ''] as $k => $d) {
            $shop[$k] = Setting::get($k, $d);
        }
        return view('admin.settings.index', compact('mail', 'ai', 'gen', 'launch', 'pay', 'shop'));
    }

    // ── Lanzamiento: early access / live ─────────────────────────────────
    public function updateLaunch(Request $request)
    {
        $data = $request->validate([
            'site_mode'             => ['required', Rule::in(['early_access', 'live'])],
            'live_whitelist_emails' => 'nullable|string|max:5000',
            'live_whitelist_ips'    => 'nullable|string|max:2000',
        ]);
        foreach (\App\Services\SiteMode::lines($data['live_whitelist_ips'] ?? '') as $ip) {
            $plain = explode('/', $ip)[0];
            if (!filter_var($plain, FILTER_VALIDATE_IP)) {
                return back()->with('error', "IP no válida: {$ip}")->with('settings_tab', 'launch');
            }
        }
        $emails = \App\Services\SiteMode::lines($data['live_whitelist_emails'] ?? '');
        foreach ($emails as $e) {
            if (!filter_var($e, FILTER_VALIDATE_EMAIL)) return back()->with('error', "Email no válido: {$e}")->with('settings_tab', 'launch');
        }
        Setting::set('site_mode', $data['site_mode'], 'launch');
        Setting::set('live_whitelist_emails', implode("\n", $emails), 'launch');
        Setting::set('live_whitelist_ips', implode("\n", \App\Services\SiteMode::lines($data['live_whitelist_ips'] ?? '')), 'launch');
        return back()->with('success', $data['site_mode'] === 'live' ? 'El sitio está ahora en modo LIVE.' : 'El sitio está en modo Early Access.')->with('settings_tab', 'launch');
    }

    public function toggleSiteMode(Request $request)
    {
        $mode = $request->validate(['site_mode' => ['required', Rule::in(['early_access', 'live'])]])['site_mode'];
        Setting::set('site_mode', $mode, 'launch');
        $request->session()->forget('hot_view_mode');
        return back()->with('success', $mode === 'live' ? 'Sitio publicado en modo LIVE.' : 'Sitio devuelto a Early Access.');
    }

    public function regeneratePreviewToken()
    {
        Setting::set('live_preview_token', \Illuminate\Support\Str::random(40), 'launch');
        return back()->with('success', 'Nuevo enlace de vista previa generado (el anterior deja de funcionar).')->with('settings_tab', 'launch');
    }

    // ── Pagos: Stripe Connect ────────────────────────────────────────────
    public function updatePayments(Request $request)
    {
        $data = $request->validate([
            'stripe_mode'                => ['required', Rule::in(['test', 'live'])],
            'stripe_test_pk'             => ['nullable', 'string', 'starts_with:pk_test_'],
            'stripe_test_sk'             => ['nullable', 'string', 'regex:/^(sk|rk)_test_/'],
            'stripe_test_whsec'          => ['nullable', 'string', 'starts_with:whsec_'],
            'stripe_test_whsec_connect'  => ['nullable', 'string', 'starts_with:whsec_'],
            'stripe_live_pk'             => ['nullable', 'string', 'starts_with:pk_live_'],
            'stripe_live_sk'             => ['nullable', 'string', 'regex:/^(sk|rk)_live_/'],
            'stripe_live_whsec'          => ['nullable', 'string', 'starts_with:whsec_'],
            'stripe_live_whsec_connect'  => ['nullable', 'string', 'starts_with:whsec_'],
        ]);
        Setting::set('stripe_mode', $data['stripe_mode'], 'payments');
        foreach (['test', 'live'] as $m) {
            if (isset($data["stripe_{$m}_pk"])) Setting::set("stripe_{$m}_pk", trim($data["stripe_{$m}_pk"]), 'payments');
            foreach (['sk', 'whsec', 'whsec_connect'] as $k) {
                $key = "stripe_{$m}_{$k}";
                if (!empty($data[$key])) {
                    Setting::set($key, \Illuminate\Support\Facades\Crypt::encryptString(trim($data[$key])), 'payments', 'encrypted');
                }
                if ($request->boolean("clear_{$key}")) {
                    Setting::set($key, '', 'payments', 'encrypted');
                }
            }
        }
        return back()->with('success', 'Configuración de pagos guardada.')->with('settings_tab', 'payments');
    }

    public function testStripe(\App\Services\StripeService $stripe)
    {
        try {
            $acct = $stripe->client()->accounts->retrieve();
            $name = $acct->settings->dashboard->display_name ?? $acct->business_profile->name ?? $acct->id;
            return back()->with('success', "Conexión correcta con Stripe ({$stripe->mode()}): {$name} · país {$acct->country}.")->with('settings_tab', 'payments');
        } catch (\Throwable $e) {
            return back()->with('error', 'Stripe: ' . $e->getMessage())->with('settings_tab', 'payments');
        }
    }

    // ── Tienda: plazos ───────────────────────────────────────────────────
    public function updateShop(Request $request)
    {
        $data = $request->validate([
            'ship_within_days'    => 'required|integer|min:1|max:15',
            'release_after_days'  => 'required|integer|min:1|max:90',
            'reservation_minutes' => 'required|integer|min:30|max:240',
            'orders_email'        => 'nullable|email',
        ]);
        foreach ($data as $k => $v) {
            Setting::set($k, (string) ($v ?? ''), 'shop');
        }
        return back()->with('success', __('messages.admin.settings_saved'))->with('settings_tab', 'shop');
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'site_name'           => 'required|string|max:100',
            'contact_email'       => 'required|email',
            'commission_standard' => 'required|numeric|min:0|max:100',
            'commission_founding' => 'required|numeric|min:0|max:100',
        ]);
        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'general', 'string');
        }
        return back()->with('success', __('messages.admin.settings_saved'));
    }

    public function updateMail(Request $request)
    {
        $validated = $request->validate([
            'mail_driver'       => ['required', Rule::in(['smtp', 'ses'])],
            'mail_host'         => 'nullable|string',
            'mail_port'         => 'nullable|string',
            'mail_username'     => 'nullable|string',
            'mail_password'     => 'nullable|string',
            'mail_encryption'   => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name'    => 'required|string',
            'aws_key'           => 'nullable|string',
            'aws_secret'        => 'nullable|string',
            'aws_region'        => 'nullable|string',
        ]);

        // Save all, skip empty passwords (keep existing)
        $skipIfEmpty = ['mail_password', 'aws_key', 'aws_secret'];
        foreach ($validated as $key => $value) {
            if (in_array($key, $skipIfEmpty) && empty($value)) continue;
            if (in_array($key, $skipIfEmpty, true)) {
                Setting::set($key, \Illuminate\Support\Facades\Crypt::encryptString($value), 'mail', 'encrypted');
                continue;
            }
            Setting::set($key, $value, 'mail', 'string');
        }
        return back()->with('success', __('messages.admin.settings_saved'));
    }

    public function updateAi(Request $request)
    {
        $validated = $request->validate([
            'ai_provider'     => ['required', Rule::in(['openai', 'anthropic'])],
            'openai_key'      => 'nullable|string',
            'openai_model'    => 'nullable|string',
            'anthropic_key'   => 'nullable|string',
            'anthropic_model' => 'nullable|string',
        ]);

        // Always save provider and models
        Setting::set('ai_provider',     $validated['ai_provider'],     'ai', 'string');
        Setting::set('openai_model',    $validated['openai_model'] ?? 'gpt-4o', 'ai', 'string');
        Setting::set('anthropic_model', $validated['anthropic_model'] ?? 'claude-sonnet-4-6', 'ai', 'string');

        // Only update keys if provided (not empty)
        if (!empty($validated['openai_key'])) {
            Setting::set('openai_key', \Illuminate\Support\Facades\Crypt::encryptString($validated['openai_key']), 'ai', 'encrypted');
        }
        if (!empty($validated['anthropic_key'])) {
            Setting::set('anthropic_key', \Illuminate\Support\Facades\Crypt::encryptString($validated['anthropic_key']), 'ai', 'encrypted');
        }

        return back()->with('success', __('messages.admin.settings_saved'));
    }


    public function updateLists(Request $request)
    {
        $validated = $request->validate([
            'list_id_evaluacion' => 'nullable|string|max:100',
            'list_id_buyer'      => 'nullable|string|max:100',
            'list_id_seller'     => 'nullable|string|max:100',
        ]);
        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '', 'lists', 'string');
        }
        return back()->with('success', __('messages.admin.settings_saved'));
    }

    public function testMail(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);
        try {
            \Mail::raw('Test email from Haus of Time admin panel.', function ($m) use ($request) {
                $m->to($request->test_email)->subject('HOT Mail Test');
            });
            return back()->with('success', __('messages.admin.mail_test_sent'));
        } catch (\Exception $e) {
            return back()->with('error', 'Mail error: ' . $e->getMessage());
        }
    }
}
