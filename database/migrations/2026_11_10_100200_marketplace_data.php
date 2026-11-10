<?php

use App\Support\Countries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Dati iniziali del marketplace + correzione dati esistenti:
 *  - watch_brands degli early access salvati con doppio json_encode
 *  - chiavi API / password SMTP salvate in chiaro → cifrate
 *  - slug e ubicazione degli orologi
 *  - paesi, zone e tariffe FedEx di partenza
 *  - impostazioni di lancio (site_mode = early_access)
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── 1. Early access: watch_brands doppiamente codificato ─────────────
        foreach (DB::table('early_access_requests')->whereNotNull('watch_brands')->get(['id', 'watch_brands']) as $r) {
            $v = json_decode($r->watch_brands, true);
            if (is_string($v)) {
                $v = json_decode($v, true);
            }
            if (is_array($v)) {
                DB::table('early_access_requests')->where('id', $r->id)
                    ->update(['watch_brands' => json_encode(array_values($v), JSON_UNESCAPED_UNICODE)]);
            }
        }

        // ── 2. Segreti in chiaro → encrypted ─────────────────────────────────
        foreach (['openai_key', 'anthropic_key', 'mail_password', 'aws_key', 'aws_secret'] as $key) {
            $s = DB::table('settings')->where('key', $key)->first();
            if ($s && $s->value && $s->type !== 'encrypted') {
                DB::table('settings')->where('id', $s->id)->update([
                    'value' => Crypt::encryptString($s->value), 'type' => 'encrypted', 'updated_at' => $now,
                ]);
            }
        }

        // ── 3. Paesi ─────────────────────────────────────────────────────────
        if (DB::table('countries')->count() === 0) {
            $zones = [
                'ES'   => ['España',                       null, null],
                'EU'   => ['Unión Europea',                null, null],
                'EUR+' => ['Reino Unido, Suiza y Noruega',
                           'Los envíos fuera de la UE pueden estar sujetos a aranceles e impuestos de importación, a cargo del comprador.',
                           'Shipments outside the EU may be subject to import duties and taxes, payable by the buyer.'],
                'AM'   => ['Norteamérica y Latinoamérica',
                           'Los aranceles e impuestos de importación corren a cargo del comprador.',
                           'Import duties and taxes are the responsibility of the buyer.'],
                'ROW'  => ['Resto del mundo',
                           'Los aranceles e impuestos de importación corren a cargo del comprador.',
                           'Import duties and taxes are the responsibility of the buyer.'],
            ];
            $zoneIds = [];
            $i = 0;
            foreach ($zones as $k => [$name, $nEs, $nEn]) {
                $zoneIds[$k] = DB::table('shipping_zones')->insertGetId([
                    'name' => $name, 'notice_es' => $nEs, 'notice_en' => $nEn,
                    'active' => true, 'sort_order' => $i++, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            // Tariffe di partenza (FedEx International Priority, assicurato a valore pieno).
            // Valori indicativi: il cliente li rivede da Admin → Envíos.
            $rates = [
                'ES'   => [35, 0.30, 15, '24–48 h'],
                'EU'   => [65, 0.40, 25, '24–72 h'],
                'EUR+' => [95, 0.50, 35, '48–96 h'],
                'AM'   => [140, 0.60, 45, '2–5 días'],
                'ROW'  => [180, 0.70, 60, '3–7 días'],
            ];
            foreach ($rates as $k => [$base, $ins, $insMin, $eta]) {
                DB::table('shipping_rates')->insert([
                    'shipping_zone_id' => $zoneIds[$k],
                    'name_es' => 'FedEx International Priority · asegurado',
                    'name_en' => 'FedEx International Priority · fully insured',
                    'carrier' => 'fedex', 'base_price' => $base, 'insurance_rate' => $ins, 'insurance_min' => $insMin,
                    'delivery_estimate' => $eta, 'active' => true, 'sort_order' => 0,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $enabledByDefault = array_merge(['ES', 'GB', 'CH', 'NO', 'US', 'CA', 'MX', 'PA'], Countries::EU);
            $rows = [];
            foreach (Countries::all() as $code => [$es, $en]) {
                $zone = match (true) {
                    $code === 'ES'                                  => 'ES',
                    in_array($code, Countries::EU, true)            => 'EU',
                    in_array($code, ['GB', 'CH', 'NO', 'IS', 'LI', 'MC', 'AD', 'SM', 'VA'], true) => 'EUR+',
                    in_array($code, ['US', 'CA', 'MX', 'PA', 'CO', 'CL', 'AR', 'BR', 'PE', 'UY', 'CR', 'DO', 'GT', 'EC', 'PR'], true) => 'AM',
                    default                                         => 'ROW',
                };
                $rows[] = [
                    'code' => $code, 'name_es' => $es, 'name_en' => $en,
                    'enabled' => in_array($code, $enabledByDefault, true),
                    'shipping_zone_id' => $zoneIds[$zone],
                    'sort_order' => $code === 'ES' ? 0 : 10,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
            DB::table('countries')->insert($rows);
        }

        // ── 4. Orologi: slug + ubicazione dal venditore ──────────────────────
        $sellers = DB::table('users')->get(['id', 'country'])->keyBy('id');
        foreach (DB::table('watches')->get(['id', 'brand', 'model', 'reference', 'slug', 'seller_id', 'country_code', 'status']) as $w) {
            $upd = [];
            if (!$w->slug) {
                $upd['slug'] = Str::slug(trim("{$w->brand} {$w->model} {$w->reference}")) . '-' . $w->id;
            }
            if (!$w->country_code && isset($sellers[$w->seller_id])) {
                $upd['country_code'] = Countries::codeFromName($sellers[$w->seller_id]->country);
            }
            if ($w->status === 'approved') {
                $upd['is_certified'] = true;
            }
            if ($upd) DB::table('watches')->where('id', $w->id)->update($upd);
        }

        // ── 5. Impostazioni di lancio e negozio ──────────────────────────────
        $defaults = [
            ['site_mode',            'early_access', 'launch', 'string'],
            ['live_whitelist_emails', '',            'launch', 'string'],
            ['live_whitelist_ips',    '',            'launch', 'string'],
            ['live_preview_token',    Str::random(40), 'launch', 'string'],
            ['stripe_mode',          'test',         'payments', 'string'],
            ['ship_within_days',     '2',            'shop', 'string'],
            ['release_after_days',   '30',           'shop', 'string'],
            ['reservation_minutes',  '30',           'shop', 'string'],
            ['shop_min_price',       '0',            'shop', 'string'],
        ];
        foreach ($defaults as [$key, $value, $group, $type]) {
            if (!DB::table('settings')->where('key', $key)->exists()) {
                DB::table('settings')->insert([
                    'key' => $key, 'value' => $value, 'group' => $group, 'type' => $type,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Dati: nessun rollback (le tabelle vengono eliminate dalla migrazione precedente).
    }
};
