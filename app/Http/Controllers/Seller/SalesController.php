<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;

/** Vendite del venditore: dati di spedizione, tracking FedEx, stato della liquidazione. */
class SalesController extends Controller
{
    public function index(Request $request)
    {
        $sales = OrderItem::with('order')->where('seller_id', $request->user()->id)
            ->where('status', '!=', 'pending')->where('status', '!=', 'cancelled')
            ->latest()->paginate(20);

        $base = OrderItem::where('seller_id', $request->user()->id);
        $stats = [
            'to_ship'  => (clone $base)->where('status', 'awaiting_shipment')->count(),
            'in_transit' => (clone $base)->whereIn('status', ['shipped', 'received'])->count(),
            'pending_payout' => (clone $base)->whereIn('status', ['awaiting_shipment', 'shipped', 'received', 'disputed'])->sum('seller_payout'),
            'paid_out' => (clone $base)->where('status', 'released')->sum('seller_payout'),
        ];
        return view('seller.sales.index', compact('sales', 'stats'));
    }

    public function show(Request $request, OrderItem $item)
    {
        abort_unless((int) $item->seller_id === (int) $request->user()->id && $item->status !== 'pending', 404);
        $item->load('order');
        return view('seller.sales.show', compact('item'));
    }

    public function ship(Request $request, OrderItem $item, OrderService $orders)
    {
        abort_unless((int) $item->seller_id === (int) $request->user()->id, 404);
        $data = $request->validate([
            'carrier'         => 'required|in:fedex,dhl,ups,other',
            'tracking_number' => 'required|string|min:6|max:80',
            'tracking_url'    => 'nullable|url|max:255',
            'confirm_insured' => 'accepted',
        ]);
        try {
            $orders->ship($item, $data['carrier'], $data['tracking_number'], $data['tracking_url'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', __('shop.seller.shipped_ok'));
    }
}
