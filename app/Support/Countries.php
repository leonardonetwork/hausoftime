<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Elenco ISO dei paesi (nomi ES/EN) e conversione nome → codice.
 * I dati storici (early access, venditori) hanno il paese scritto per esteso.
 */
class Countries
{
    private static ?array $all = null;

    public static function all(): array
    {
        return self::$all ??= require database_path('data/countries.php');
    }

    public static function name(?string $code, ?string $locale = null): ?string
    {
        if (!$code) return null;
        $row = self::all()[strtoupper($code)] ?? null;
        if (!$row) return $code;
        return ($locale ?? app()->getLocale()) === 'en' ? $row[1] : $row[0];
    }

    public static function codeFromName(?string $name): ?string
    {
        if (!$name) return null;
        $name = trim($name);
        if (strlen($name) === 2 && isset(self::all()[strtoupper($name)])) {
            return strtoupper($name);
        }
        $needle = self::normalize($name);
        $aliases = ['espana' => 'ES', 'spain' => 'ES', 'usa' => 'US', 'eeuu' => 'US', 'uk' => 'GB', 'inglaterra' => 'GB',
                    'england' => 'GB', 'holanda' => 'NL', 'mexico' => 'MX', 'panama' => 'PA', 'suiza' => 'CH'];
        if (isset($aliases[$needle])) return $aliases[$needle];
        foreach (self::all() as $code => [$es, $en]) {
            if (self::normalize($es) === $needle || self::normalize($en) === $needle) {
                return $code;
            }
        }
        return null;
    }

    private static function normalize(string $s): string
    {
        return Str::of(Str::ascii($s))->lower()->replaceMatches('/[^a-z]/', '')->toString();
    }

    /** Stati UE (per zone di default). */
    public const EU = ['AT','BE','BG','CY','CZ','DE','DK','EE','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK'];
}
