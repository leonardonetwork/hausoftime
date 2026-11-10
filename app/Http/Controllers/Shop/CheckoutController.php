<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Country;
use App\Models\Order;
use App\Models\Watch;
use App\Services\Cart;
use App\Services\OrderService;
use App\Services\Pricing;
use App\Services\StripeService;
use App\Support\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private Cart $cart,
        private Pricing $pricing,
        private OrderService $orders,
        private StripeService $stripe,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $watches = $this->cart->watches($removed, $this->pendingOrderId($request));
        if ($watches->isEmpty()) {
            $r = redirect()->route('bag.index');
            return $removed ? $r->with('error', __('shop.bag.items_removed', ['items' => implode(', ', $removed)])) : $r;
        }
        if ($own = $watches->first(fn ($w) => (int) $w->seller_id === (int) $user->id)) {
            $this->cart->remove($own->id);
            return redirect()->route('bag.index')->with('error', __('shop.bag.own_watch'));
        }

        $addresses = $user->addresses;
        $default = $addresses->first();
        $countryCode = old('country_code', $default?->country_code ?? Countries::codeFromName($user->country) ?? 'ES');
        $countries = Country::shippable();
        if (!$countries->contains('code', $countryCode)) $countryCode = $countries->first()?->code;

        $quote = $this->pricing->quote($watches, $countryCode, session('hot_coupon'), $user->email, $user);
        if ($quote['coupon_error'] && session('hot_coupon')) {
            session()->forget('hot_coupon');
        }

        return view('shop.checkout', [
            'quote'          => $quote,
            'addresses'      => $addresses,
            'default'        => $default,
            'countries'      => $countries,
            'countryCode'    => $countryCode,
            'user'           => $user,
            'stripeReady'    => $this->stripe->isConfigured(),
            'removed'        => $removed,
        ]);
    }

    /** Ricalcolo AJAX (cambio paese / coupon). */
    public function quote(Request $request)
    {
        $request->validate(['country_code' => 'nullable|string|size:2', 'coupon' => 'nullable|string|max:60', 'action' => 'nullable|in:apply,remove']);
        $user = $request->user();

        if ($request->input('action') === 'apply') {
            session(['hot_coupon' => strtoupper(trim((string) $request->input('coupon')))]);
        } elseif ($request->input('action') === 'remove') {
            session()->forget('hot_coupon');
        }

        $watches = $this->cart->watches($removed, $this->pendingOrderId($request));
        $quote = $this->pricing->quote($watches, $request->input('country_code'), session('hot_coupon'), $user->email, $user);
        $couponError = $quote['coupon_error'];
        if ($couponError) session()->forget('hot_coupon');

        return response()->json([
            'summary'      => view('shop.partials.summary', ['quote' => $quote])->render(),
            'coupon'       => $quote['coupon']?->code,
            'coupon_error' => $request->input('action') === 'apply' ? $couponError : null,
            'shipping_error' => $quote['shipping_error'],
            'total'        => Watch::money($quote['total'], true),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $countryCodes = Country::shippable()->pluck('code')->all();

        $useSaved = $request->filled('address_id');
        $rules = [
            'address_id'   => 'nullable|integer',
            'accept_terms' => 'accepted',
            'notes'        => 'nullable|string|max:1000',
            'save_address' => 'nullable|boolean',
            'billing_same' => 'nullable|boolean',
        ];
        if (!$useSaved) {
            $rules += [
                'first_name'   => 'required|string|max:100',
                'last_name'    => 'required|string|max:100',
                'company'      => 'nullable|string|max:150',
                'tax_id'       => 'nullable|string|max:40',
                'line1'        => 'required|string|max:200',
                'line2'        => 'nullable|string|max:200',
                'city'         => 'required|string|max:120',
                'postal_code'  => 'required|string|max:20',
                'province'     => 'nullable|string|max:120',
                'country_code' => ['required', 'string', 'size:2', \Illuminate\Validation\Rule::in($countryCodes)],
                'phone_prefix' => 'nullable|string|max:10',
                'phone'        => 'required|string|max:30',
            ];
        }
        $data = $request->validate($rules);

        if ($useSaved) {
            $address = $user->addresses()->findOrFail($data['address_id']);
            if (!in_array($address->country_code, $countryCodes, true)) {
                return back()->withErrors(['address_id' => __('shop.checkout.country_not_available')]);
            }
            $shipping = $address->toSnapshot();
        } else {
            $shipping = collect($data)->only(['first_name', 'last_name', 'company', 'tax_id', 'line1', 'line2', 'city',
                'postal_code', 'province', 'country_code', 'phone_prefix', 'phone'])->all();
            if ($request->boolean('save_address')) {
                $user->addresses()->create($shipping + ['is_default' => $user->addresses()->doesntExist()]);
            }
        }

        if (!$this->stripe->isConfigured()) {
            return back()->withInput()->with('error', __('shop.checkout.payments_unavailable'));
        }

        // Un checkout precedente non pagato dello stesso utente libera i pezzi
        Order::where('user_id', $user->id)->where('status', 'pending_payment')->get()
            ->each(fn (Order $o) => $this->orders->expire($o, 'cancelled'));

        $watches = $this->cart->watches($removed);
        if ($watches->isEmpty() || $removed) {
            return redirect()->route('bag.index')->with('error', $removed ? __('shop.bag.items_removed', ['items' => implode(', ', $removed)]) : __('shop.bag.empty'));
        }
        if ($reserved = $watches->first(fn ($w) => $w->reserved_by_other)) {
            return back()->withInput()->with('error', __('shop.checkout.item_reserved', ['item' => $reserved->title]));
        }

        $quote = $this->pricing->quote($watches, $shipping['country_code'], session('hot_coupon'), $user->email, $user);
        if ($quote['shipping_error']) {
            return back()->withInput()->with('error', $quote['shipping_error']);
        }
        if ($quote['total'] < 0.5) {
            return back()->withInput()->with('error', __('shop.checkout.invalid_total'));
        }

        try {
            $order = $this->orders->createFromQuote($quote, $user, $shipping, null, $data['notes'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        try {
            $session = $this->stripe->createCheckoutSession($order);
        } catch (\Throwable $e) {
            Log::error("Stripe checkout {$order->number}: " . $e->getMessage());
            $this->orders->expire($order, 'cancelled');
            return back()->withInput()->with('error', __('shop.checkout.payment_error'));
        }

        $order->update(['stripe_session_id' => $session->id]);
        $request->session()->put('hot_pending_order', $order->id);

        return redirect()->away($session->url, 303);
    }

    public function success(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        $order = Order::where('stripe_session_id', $sessionId)->where('user_id', $request->user()->id)->firstOrFail();

        if (!$order->paid_at) {
            try {
                $session = $this->stripe->retrieveSession($sessionId);
                if ($session->payment_status === 'paid') {
                    $pi = $session->payment_intent;
                    $this->orders->markPaid($order, $pi?->id ?? null, $pi?->latest_charge ?? null, $pi?->payment_method_types[0] ?? null);
                } elseif ($session->status === 'complete') {
                    // pagamento asincrono (es. bonifico): manteniamo la riserva finché Stripe conferma
                    $order->update(['reserved_until' => now()->addDays(7)]);
                    \App\Models\Watch::where('reserved_order_id', $order->id)->update(['reserved_until' => now()->addDays(7)]);
                    $order->log('awaiting_async_payment', 'Pago pendiente de confirmación (método asíncrono)');
                }
            } catch (\Throwable $e) {
                Log::warning("Checkout success check {$order->number}: " . $e->getMessage());
            }
        }

        $this->cart->set(array_diff($this->cart->ids(), $order->items()->pluck('watch_id')->all()));
        session()->forget(['hot_coupon', 'hot_pending_order']);

        return view('shop.checkout-success', ['order' => $order->fresh('items')]);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);
        if ($order->status === 'pending_payment') {
            $this->orders->expire($order, 'cancelled');
        }
        session()->forget('hot_pending_order');
        return redirect()->route('checkout.index')->with('error', __('shop.checkout.cancelled'));
    }

    private function pendingOrderId(Request $request): ?int
    {
        return $request->session()->get('hot_pending_order');
    }
}
