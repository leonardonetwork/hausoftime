<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

/** Zone di spedizione e tariffe FedEx assicurate (per pezzo). */
class ShippingController extends Controller
{
    public function index()
    {
        $zones = ShippingZone::withCount(['countries', 'countries as enabled_count' => fn ($q) => $q->where('enabled', true)])
            ->with('rates')->orderBy('sort_order')->get();
        return view('admin.shipping.index', compact('zones'));
    }

    public function zoneStore(Request $request)
    {
        ShippingZone::create($this->zoneData($request) + ['sort_order' => (int) ShippingZone::max('sort_order') + 1]);
        return back()->with('success', 'Zona creada.');
    }

    public function zoneUpdate(Request $request, ShippingZone $zone)
    {
        $zone->update($this->zoneData($request));
        return back()->with('success', 'Zona actualizada.');
    }

    public function zoneDestroy(ShippingZone $zone)
    {
        if ($zone->countries()->exists()) {
            return back()->with('error', 'La zona tiene países asignados: muévelos antes de eliminarla.');
        }
        $zone->delete();
        return back()->with('success', 'Zona eliminada.');
    }

    public function rateStore(Request $request, ShippingZone $zone)
    {
        $zone->rates()->create($this->rateData($request));
        return back()->with('success', 'Tarifa creada.');
    }

    public function rateUpdate(Request $request, ShippingRate $rate)
    {
        $rate->update($this->rateData($request));
        return back()->with('success', 'Tarifa actualizada.');
    }

    public function rateDestroy(ShippingRate $rate)
    {
        $rate->delete();
        return back()->with('success', 'Tarifa eliminada.');
    }

    private function zoneData(Request $request): array
    {
        $d = $request->validate([
            'name' => 'required|string|max:120', 'notice_es' => 'nullable|string|max:500', 'notice_en' => 'nullable|string|max:500',
        ]);
        $d['active'] = $request->boolean('active');
        return $d;
    }

    private function rateData(Request $request): array
    {
        $d = $request->validate([
            'name_es'           => 'required|string|max:150',
            'name_en'           => 'nullable|string|max:150',
            'carrier'           => 'required|in:fedex,dhl,ups,other',
            'base_price'        => 'required|numeric|min:0',
            'insurance_rate'    => 'required|numeric|min:0|max:20',
            'insurance_min'     => 'nullable|numeric|min:0',
            'free_over'         => 'nullable|numeric|min:0',
            'min_value'         => 'nullable|numeric|min:0',
            'max_value'         => 'nullable|numeric|min:0',
            'delivery_estimate' => 'nullable|string|max:60',
            'sort_order'        => 'nullable|integer',
        ]);
        $d['insurance_min'] = $d['insurance_min'] ?? 0;
        $d['sort_order'] = $d['sort_order'] ?? 0;
        $d['active'] = $request->boolean('active');
        return $d;
    }
}
