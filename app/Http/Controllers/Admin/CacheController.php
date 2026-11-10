<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CacheController extends Controller
{
    public function clear()
    {
        $results = [];

        // Laravel caches
        try { \Artisan::call('view:clear');   $results[] = '✓ Views'; } catch(\Exception $e) { $results[] = '✗ Views: '.$e->getMessage(); }
        try { \Artisan::call('cache:clear');  $results[] = '✓ Cache'; } catch(\Exception $e) { $results[] = '✗ Cache: '.$e->getMessage(); }
        try { \Artisan::call('config:clear'); $results[] = '✓ Config'; } catch(\Exception $e) { $results[] = '✗ Config: '.$e->getMessage(); }
        try { \Artisan::call('route:clear');  $results[] = '✓ Routes'; } catch(\Exception $e) { $results[] = '✗ Routes: '.$e->getMessage(); }

        // OPcache
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $results[] = '✓ OPcache';
        } else {
            $results[] = '⚠ OPcache: non disponibile via CLI (normale)';
        }

        $msg = implode(' · ', $results);
        return back()->with('success', '🧹 Cache pulita: ' . $msg);
    }
}
