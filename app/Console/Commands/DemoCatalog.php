<?php

namespace App\Console\Commands;

use App\Models\Favorite;
use App\Models\Setting;
use App\Models\User;
use App\Models\Watch;
use App\Models\WatchBrand;
use App\Support\Countries;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Catalogo DIMOSTRATIVO: 10 venditori finti con 3–5 orologi ciascuno (dati da database/demo/catalog.json,
 * immagini renderizzate in database/demo/images). Solo per vedere il sito: da rimuovere prima del lancio.
 *
 *   php artisan hot:demo            carica (rilanciabile: prima rimuove la demo precedente)
 *   php artisan hot:demo --remove   elimina venditori, orologi, immagini e marche create dalla demo
 */
class DemoCatalog extends Command
{
    protected $signature = 'hot:demo {--remove : Elimina il catalogo dimostrativo} {--seed=2026 : Seme per la distribuzione casuale}';
    protected $description = 'Carica o elimina il catalogo dimostrativo (venditori e orologi finti)';

    public const DOMAIN = 'demo.hausoftime.test';

    private const SELLERS = [
        ['Lucía', 'Fernández Ortega', 'Madrid', 'ES'],
        ['Javier', 'Montero Ruiz', 'Barcelona', 'ES'],
        ['Carmen', 'Vidal Soler', 'Marbella', 'ES'],
        ['Álvaro', 'Navarro Gil', 'Valencia', 'ES'],
        ['Itziar', 'Etxeberria', 'Bilbao', 'ES'],
        ['Duarte', 'Almeida', 'Lisboa', 'PT'],
        ['Giulia', 'Ferraresi', 'Milano', 'IT'],
        ['Antoine', 'Delacroix', 'París', 'FR'],
        ['Matthias', 'Keller', 'Ginebra', 'CH'],
        ['Oliver', 'Hastings', 'Londres', 'GB'],
    ];

    public function handle(): int
    {
        $this->remove();
        if ($this->option('remove')) {
            $this->info('Catalogo dimostrativo eliminato.');
            return self::SUCCESS;
        }

        $catalog = json_decode(file_get_contents(database_path('demo/catalog.json')), true);
        mt_srand((int) $this->option('seed'));
        shuffle($catalog);

        $createdBrands = [];
        $total = 0;
        $pos = 0;

        DB::transaction(function () use ($catalog, &$createdBrands, &$total, &$pos) {
            foreach (self::SELLERS as $i => [$first, $last, $city, $cc]) {
                $seller = User::create([
                    'name'               => "$first $last",
                    'first_name'         => $first,
                    'last_name'          => $last,
                    'email'              => Str::slug("$first.$last", '.') . '@' . self::DOMAIN,
                    'password'           => Hash::make(Str::random(32)),
                    'role'               => 'seller',
                    'status'             => 'approved',
                    'country'            => Countries::name($cc, 'es'),
                    'city'               => $city,
                    'locale'             => in_array($cc, ['ES'], true) ? 'es' : 'en',
                    'is_founding_seller' => mt_rand(0, 1) === 1,
                ]);

                $n = mt_rand(3, 5);
                for ($k = 0; $k < $n && $pos < count($catalog); $k++, $pos++) {
                    $it = $catalog[$pos];
                    $brand = WatchBrand::where('name_es', $it['brand'])->orWhere('name_en', $it['brand'])->first();
                    if (!$brand) {
                        $brand = WatchBrand::create(['name_es' => $it['brand'], 'name_en' => $it['brand'], 'slug' => Str::slug($it['brand']), 'active' => true]);
                        $createdBrands[] = $brand->id;
                    }

                    $images = [];
                    foreach ([1, 2, 3] as $v) {
                        $src = database_path("demo/images/{$it['key']}-$v.webp");
                        if (is_file($src)) {
                            $dest = "watches/demo/{$it['key']}-$v.webp";
                            Storage::disk('public')->put($dest, file_get_contents($src));
                            $images[] = $dest;
                        }
                    }

                    $price = (float) $it['price'];
                    $discount = mt_rand(1, 6) === 1 ? round($price * 0.94 / 100) * 100 : null;

                    $w = Watch::create([
                        'seller_id'        => $seller->id,
                        'brand'            => $brand->name_en ?: $brand->name_es,
                        'model'            => $it['model'],
                        'reference'        => $it['reference'],
                        'case_size'        => $it['case_size'],
                        'material'         => $it['material_es'],
                        'manufacture_year' => $it['year'],
                        'condition'        => $it['condition'],
                        'category'         => $it['category'],
                        'description'      => $it['description_es'],
                        'description_en'   => $it['description_en'],
                        'price'            => $price,
                        'price_discounted' => $discount,
                        'currency'         => 'EUR',
                        'box_papers'       => $it['box_papers'],
                        'images'           => $images,
                        'status'           => 'approved',
                        'is_certified'     => mt_rand(1, 10) <= 7,
                        'is_featured'      => mt_rand(1, 10) === 1,
                        'country_code'     => $cc,
                        'city'             => $city,
                        'approved_at'      => now()->subDays(mt_rand(1, 60))->subMinutes(mt_rand(0, 1440)),
                        'verification_at'  => now()->subDays(61),
                    ]);
                    $total++;
                }
                $this->line("  {$seller->name} ({$city}) · {$n} relojes");
            }
        });

        Setting::set('demo_brand_ids', json_encode($createdBrands), 'demo', 'string');
        $this->info("Catalogo dimostrativo caricato: " . count(self::SELLERS) . " venditori, {$total} orologi.");
        return self::SUCCESS;
    }

    private function remove(): void
    {
        $sellers = User::where('email', 'like', '%@' . self::DOMAIN)->pluck('id');
        $watchIds = Watch::withTrashed()->whereIn('seller_id', $sellers)->pluck('id');

        Favorite::whereIn('watch_id', $watchIds)->delete();
        DB::table('order_items')->whereIn('watch_id', $watchIds)->update(['watch_id' => null]);
        Watch::withTrashed()->whereIn('id', $watchIds)->forceDelete();
        User::whereIn('id', $sellers)->delete();
        Storage::disk('public')->deleteDirectory('watches/demo');

        $brandIds = json_decode((string) Setting::get('demo_brand_ids', '[]'), true) ?: [];
        foreach (WatchBrand::whereIn('id', $brandIds)->get() as $b) {
            $inUse = Watch::withTrashed()->where(fn ($q) => $q->where('brand', $b->name_es)->orWhere('brand', $b->name_en))->exists();
            if (!$inUse) $b->delete();
        }
        Setting::set('demo_brand_ids', '[]', 'demo', 'string');

        if ($sellers->count()) {
            $this->line("  Rimossi {$sellers->count()} venditori demo e {$watchIds->count()} orologi.");
        }
    }
}
