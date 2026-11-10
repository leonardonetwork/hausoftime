<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

/** Paesi abilitati alla vendita/spedizione e zona di appartenenza. */
class CountriesController extends Controller
{
    public function index(Request $request)
    {
        $q = Country::with('zone')->orderByDesc('enabled')->orderBy('name_es');
        if ($s = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('name_es', 'like', "%$s%")->orWhere('name_en', 'like', "%$s%")->orWhere('code', $s));
        }
        if ($request->query('estado') === 'activos') $q->where('enabled', true);
        if ($request->query('estado') === 'inactivos') $q->where('enabled', false);
        if ($z = $request->query('zona')) $q->where('shipping_zone_id', $z);

        return view('admin.countries.index', [
            'countries' => $q->get(),
            'zones'     => ShippingZone::orderBy('sort_order')->get(),
            'enabled'   => Country::where('enabled', true)->count(),
        ]);
    }

    public function update(Request $request, Country $country)
    {
        $data = $request->validate(['shipping_zone_id' => 'nullable|exists:shipping_zones,id']);
        $country->update(['enabled' => $request->boolean('enabled'), 'shipping_zone_id' => $data['shipping_zone_id'] ?? null]);
        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return back()->with('success', "{$country->name_es} actualizado.");
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids'              => 'required|array|min:1',
            'ids.*'            => 'integer|exists:countries,id',
            'action'           => 'required|in:enable,disable,zone',
            'shipping_zone_id' => 'nullable|required_if:action,zone|exists:shipping_zones,id',
        ]);
        $q = Country::whereIn('id', $data['ids']);
        match ($data['action']) {
            'enable'  => $q->update(['enabled' => true]),
            'disable' => $q->update(['enabled' => false]),
            'zone'    => $q->update(['shipping_zone_id' => $data['shipping_zone_id']]),
        };
        return back()->with('success', count($data['ids']) . ' países actualizados.');
    }
}
