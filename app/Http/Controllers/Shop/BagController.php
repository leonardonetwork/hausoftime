<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use App\Services\Cart;
use Illuminate\Http\Request;

class BagController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function index()
    {
        $watches = $this->cart->watches($removed);
        return view('shop.bag', [
            'watches'  => $watches,
            'removed'  => $removed,
            'subtotal' => $watches->sum('effective_price'),
        ]);
    }

    public function add(Request $request, Watch $watch)
    {
        if (!$watch->isPublished()) {
            return $this->respond($request, false, __('shop.bag.unavailable'));
        }
        if ($request->user() && (int) $request->user()->id === (int) $watch->seller_id) {
            return $this->respond($request, false, __('shop.bag.own_watch'));
        }
        if (!$this->cart->add($watch)) {
            return $this->respond($request, false, __('shop.bag.full'));
        }
        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }
        return $this->respond($request, true, __('shop.bag.added'));
    }

    public function remove(Request $request, Watch $watch)
    {
        $this->cart->remove($watch->id);
        return $this->respond($request, true, __('shop.bag.removed'));
    }

    private function respond(Request $request, bool $ok, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message, 'count' => $this->cart->count()], $ok ? 200 : 422);
        }
        return back()->with($ok ? 'success' : 'error', $message);
    }
}
