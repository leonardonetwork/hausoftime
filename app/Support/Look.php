<?php

namespace App\Support;

use App\Services\SiteMode;

/**
 * Sceglie la grafica delle pagine pubbliche nate per l'Early Access
 * (valutazione, registrazione venditore, contatti, login, pagine legali).
 * In Early Access restano identiche; quando la home mostra il sito live
 * usano il layout del negozio (stesse funzioni, stesso markup dei form).
 */
class Look
{
    public static function live(): bool
    {
        return app(SiteMode::class)->showsLive();
    }

    public static function layout(string $earlyAccessLayout): string
    {
        return self::live() ? 'layouts.live-page' : $earlyAccessLayout;
    }

    /** Testo diverso solo nella versione live. */
    public static function t(string $ea, string $live): string
    {
        return self::live() ? $live : $ea;
    }
}
