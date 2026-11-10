<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\StripeService;
use App\Support\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Attivazione dei cobros con Stripe Connect (account Express). */
class PayoutsController extends Controller
{
    public function __construct(private StripeService $stripe) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->stripe_account_id && $this->stripe->isConfigured()) {
            try {
                $this->stripe->syncAccount($user);
            } catch (\Throwable $e) {
                Log::warning('Stripe sync ' . $user->id . ': ' . $e->getMessage());
            }
        }
        return view('seller.payouts', [
            'user'        => $user->fresh(),
            'configured'  => $this->stripe->isConfigured(),
            'countries'   => Country::orderBy(app()->getLocale() === 'en' ? 'name_en' : 'name_es')->get(),
            'countryCode' => Countries::codeFromName($user->country) ?? 'ES',
        ]);
    }

    public function connect(Request $request)
    {
        $data = $request->validate(['country' => 'nullable|string|size:2|exists:countries,code']);
        $user = $request->user();
        try {
            $this->stripe->ensureAccount($user, $data['country'] ?? null);
            return redirect()->away($this->stripe->onboardingLink($user->fresh()));
        } catch (\Throwable $e) {
            Log::error('Stripe connect ' . $user->id . ': ' . $e->getMessage());
            return back()->with('error', __('shop.seller.stripe_error') . ' (' . $e->getMessage() . ')');
        }
    }

    /** Il link di onboarding è scaduto: se ne genera uno nuovo. */
    public function refresh(Request $request)
    {
        $user = $request->user();
        if (!$user->stripe_account_id) return redirect()->route('seller.payouts');
        try {
            return redirect()->away($this->stripe->onboardingLink($user));
        } catch (\Throwable $e) {
            return redirect()->route('seller.payouts')->with('error', __('shop.seller.stripe_error'));
        }
    }

    public function return(Request $request)
    {
        return redirect()->route('seller.payouts')->with('success', __('shop.seller.stripe_return'));
    }

    public function dashboard(Request $request)
    {
        try {
            return redirect()->away($this->stripe->dashboardLink($request->user()));
        } catch (\Throwable $e) {
            return back()->with('error', __('shop.seller.stripe_error'));
        }
    }
}
