<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request)
    {
        $orders = $request->user()->orders()->where('status', '!=', 'expired')
            ->with('items')->paginate(10);
        return view('shop.account.orders', compact('orders'));
    }

    public function order(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $order->load(['items.seller', 'events' => fn ($q) => $q->whereIn('type', ['paid', 'shipped', 'received', 'released', 'refunded', 'dispute_opened', 'dispute_closed'])]);
        return view('shop.account.order', compact('order'));
    }

    public function confirmReceived(Request $request, Order $order, OrderItem $item)
    {
        $this->authorizeOrder($request, $order);
        abort_unless((int) $item->order_id === (int) $order->id, 404);
        try {
            $this->orders->confirmReceived($item);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', __('shop.account.received_ok'));
    }

    public function dispute(Request $request, Order $order, OrderItem $item)
    {
        $this->authorizeOrder($request, $order);
        abort_unless((int) $item->order_id === (int) $order->id, 404);
        $data = $request->validate(['reason' => 'required|string|min:10|max:2000']);
        try {
            $this->orders->openDispute($item, $data['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', __('shop.account.dispute_ok'));
    }

    // ── Indirizzi ─────────────────────────────────────────────────────────
    public function addresses(Request $request)
    {
        return view('shop.account.addresses', [
            'addresses' => $request->user()->addresses,
            'countries' => Country::shippable(),
        ]);
    }

    public function addressStore(Request $request)
    {
        $data = $this->validateAddress($request);
        $user = $request->user();
        if ($request->boolean('is_default') || $user->addresses()->doesntExist()) {
            $user->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }
        $user->addresses()->create($data);
        return back()->with('success', __('shop.account.address_saved'));
    }

    public function addressUpdate(Request $request, Address $address)
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);
        $data = $this->validateAddress($request);
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }
        $address->update($data);
        return back()->with('success', __('shop.account.address_saved'));
    }

    public function addressDestroy(Request $request, Address $address)
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);
        $address->delete();
        return back()->with('success', __('shop.account.address_deleted'));
    }

    // ── Dati personali ────────────────────────────────────────────────────
    public function profile(Request $request)
    {
        return view('shop.account.profile', ['user' => $request->user()]);
    }

    public function profileUpdate(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'nullable|string|max:100',
            'email'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_prefix'     => 'nullable|string|max:10',
            'phone'            => 'nullable|string|max:30',
            'marketing_opt_in' => 'nullable|boolean',
            'current_password' => 'nullable|required_with:password|current_password',
            'password'         => ['nullable', 'confirmed', Password::min(8)],
        ]);
        $user->fill([
            'first_name' => $data['first_name'], 'last_name' => $data['last_name'] ?? '',
            'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
            'email' => mb_strtolower($data['email']), 'phone_prefix' => $data['phone_prefix'] ?? null,
            'phone' => $data['phone'] ?? null, 'marketing_opt_in' => $request->boolean('marketing_opt_in'),
        ]);
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return back()->with('success', __('shop.account.profile_saved'));
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label'        => 'nullable|string|max:60',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'company'      => 'nullable|string|max:150',
            'tax_id'       => 'nullable|string|max:40',
            'line1'        => 'required|string|max:200',
            'line2'        => 'nullable|string|max:200',
            'city'         => 'required|string|max:120',
            'postal_code'  => 'required|string|max:20',
            'province'     => 'nullable|string|max:120',
            'country_code' => ['required', 'string', 'size:2', Rule::exists('countries', 'code')],
            'phone_prefix' => 'nullable|string|max:10',
            'phone'        => 'required|string|max:30',
        ]);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);
    }
}
