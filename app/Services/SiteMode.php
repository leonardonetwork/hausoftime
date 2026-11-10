<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Modalità del sito: "early_access" (landing attuale) oppure "live" (marketplace).
 *
 * In early access il negozio è visibile solo a:
 *  - amministratori
 *  - email in whitelist (utente autenticato)
 *  - IP in whitelist (anche CIDR, es. 176.107.152.0/24)
 *  - chi ha aperto il link di anteprima (/preview/{token}) → cookie firmato
 *
 * Chi ha accesso può alternare la vista con /vista/live e /vista/early-access.
 */
class SiteMode
{
    public const COOKIE = 'hot_preview';

    private ?bool $privileged = null;

    public function __construct(private Request $request) {}

    public function globalMode(): string
    {
        return Setting::get('site_mode', 'early_access') === 'live' ? 'live' : 'early_access';
    }

    public function isLive(): bool
    {
        return $this->globalMode() === 'live';
    }

    /** Può vedere il negozio anche in early access. */
    public function isPrivileged(): bool
    {
        if ($this->privileged !== null) return $this->privileged;

        $user = $this->request->user();
        if ($user && $user->isAdmin()) return $this->privileged = true;

        if ($user) {
            $emails = self::lines(Setting::get('live_whitelist_emails', ''));
            if (in_array(mb_strtolower($user->email), array_map('mb_strtolower', $emails), true)) {
                return $this->privileged = true;
            }
        }

        $ips = self::lines(Setting::get('live_whitelist_ips', ''));
        if ($ips && IpUtils::checkIp((string) $this->request->ip(), $ips)) {
            return $this->privileged = true;
        }

        $token = (string) Setting::get('live_preview_token', '');
        $cookie = (string) $this->request->cookie(self::COOKIE, '');
        if ($token !== '' && $cookie !== '' && hash_equals($token, $cookie)) {
            return $this->privileged = true;
        }

        return $this->privileged = false;
    }

    /** Il negozio (catalogo, scheda, carrello, checkout, account) è raggiungibile. */
    public function shopOpen(): bool
    {
        return $this->isLive() || $this->isPrivileged();
    }

    /** Cosa mostra la home per questa richiesta. */
    public function showsLive(): bool
    {
        $pref = $this->request->hasSession() ? $this->request->session()->get('hot_view_mode') : null;

        if ($this->isLive()) {
            // Solo chi ha accesso privilegiato può tornare a vedere la landing early access.
            return !($pref === 'early_access' && $this->isPrivileged());
        }
        if (!$this->isPrivileged()) return false;

        $user = $this->request->user();
        $default = ($user && $user->isAdmin()) ? 'early_access' : 'live';
        return ($pref ?? $default) === 'live';
    }

    public static function lines(?string $value): array
    {
        return collect(preg_split('/[\s,;]+/', (string) $value))
            ->map(fn ($l) => trim($l))->filter()->values()->all();
    }
}
