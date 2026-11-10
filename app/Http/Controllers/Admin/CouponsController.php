<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\WatchBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponsController extends Controller
{
    public function index()
    {
        return view('admin.coupons.index', ['coupons' => Coupon::latest()->paginate(30)]);
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => new Coupon(['type' => 'percent', 'active' => true]), 'brands' => WatchBrand::orderBy('name_es')->get()]);
    }

    public function store(Request $request)
    {
        Coupon::create($this->validated($request));
        return redirect()->route('admin.coupons.index')->with('success', 'Cupón creado.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', ['coupon' => $coupon, 'brands' => WatchBrand::orderBy('name_es')->get()]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request, $coupon));
        return redirect()->route('admin.coupons.index')->with('success', 'Cupón actualizado.');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->redemptions()->exists()) {
            $coupon->update(['active' => false]);
            return back()->with('success', 'El cupón ya se ha usado: se ha desactivado en lugar de eliminarlo.');
        }
        $coupon->delete();
        return back()->with('success', 'Cupón eliminado.');
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $data = $request->validate([
            'code'                 => ['required', 'string', 'max:60', 'regex:/^[A-Z0-9\-_]+$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'description'          => 'nullable|string|max:255',
            'type'                 => 'required|in:percent,fixed',
            'value'                => ['required', 'numeric', 'min:0', $request->input('type') === 'percent' ? 'max:100' : 'max:1000000'],
            'min_subtotal'         => 'nullable|numeric|min:0',
            'max_discount'         => 'nullable|numeric|min:0',
            'brand_ids'            => 'nullable|array',
            'brand_ids.*'          => 'integer|exists:watch_brands,id',
            'starts_at'            => 'nullable|date',
            'ends_at'              => 'nullable|date|after_or_equal:starts_at',
            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
        ], ['code.regex' => 'El código solo puede contener letras, números, guiones y guiones bajos.']);

        $data['brand_ids'] = !empty($data['brand_ids']) ? array_map('intval', $data['brand_ids']) : null;
        $data['first_order_only'] = $request->boolean('first_order_only');
        $data['free_shipping'] = $request->boolean('free_shipping');
        $data['active'] = $request->boolean('active');
        return $data;
    }
}
