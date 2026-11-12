<?php

namespace App\Http\Controllers;

use App\Mail\EarlyAccessAdminMail;
use App\Mail\SellerWelcomeMail;
use App\Mail\WelcomeBuyerMail;
use App\Models\EarlyAccessRequest;
use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EarlyAccessController extends Controller
{
    public function index()
    {
        return view('pages.early-access');
    }

    // ── Step 1: Identidad + Ubicación ───────────────────────
    public function step1(Request $request)
    {
        $validated = $request->validate([
            'type'         => 'required|in:seller,buyer,both',
            'first_name'   => 'required|string|max:200',
            'email'        => 'required|email|max:255',
            'phone_prefix' => 'nullable|string|max:10',
            'phone'        => 'nullable|string|max:30',
            'country'      => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
        ]);

        $validated['email'] = mb_strtolower(trim($validated['email']));

        if (EarlyAccessRequest::where('email', $validated['email'])->exists()
            || ($validated['type'] !== 'buyer' && User::where('email', $validated['email'])->exists())) {
            return back()
                ->withErrors(['email' => app()->getLocale() === 'es'
                    ? 'Este email ya está registrado.'
                    : 'This email is already registered.'])
                ->withInput();
        }

        $request->session()->put('early_access_step1', $validated);

        // Buyer only → direct submit, no step 2
        if ($validated['type'] === 'buyer') {
            return $this->completeBuyer($request, $validated);
        }

        // Seller or Both → step 2
        return redirect()->route('early-access.seller-step2');
    }

    // ── Step 2 view ─────────────────────────────────────────
    public function sellerStep2()
    {
        if (!session()->has('early_access_step1')) {
            return redirect()->route('early-access.index');
        }
        return view('pages.early-access-step2');
    }

    // ── Step 2 submit ────────────────────────────────────────
    public function sellerStep2Submit(Request $request)
    {
        $step1 = $request->session()->get('early_access_step1');
        if (!$step1) return redirect()->route('early-access.index');

        $rules = [
            'watch_brands'   => 'required|array|min:1',
            'watch_brands.*' => 'string|max:100',
            'accept_terms'   => 'accepted',
        ];

        // Password only required for seller/both
        if (in_array($step1['type'], ['seller','both'])) {
            $rules['watch_count'] = 'required|integer|min:1';
            $rules['password']    = ['required', 'confirmed', Password::min(8)];
        }

        $validated = $request->validate($rules);

        /* Le marche libere ("Otra marca") arrivano nello stesso array delle
           checkbox: ripuliamo spazi e duplicati prima di salvare. */
        $brands = collect($validated['watch_brands'])
            ->map(fn ($b) => trim(preg_replace('/\s+/u', ' ', (string) $b)))
            ->filter()
            ->unique(fn ($b) => mb_strtolower($b))
            ->take(30)
            ->values()
            ->all();

        // Un account con questa email potrebbe essere nato nel frattempo (es. registrazione acquirente)
        if (User::where('email', $step1['email'])->exists()) {
            return redirect()->route('early-access.index')->withErrors(['email' => app()->getLocale() === 'es'
                ? 'Este email ya tiene una cuenta. Inicia sesión.'
                : 'This email already has an account. Please sign in.']);
        }

        // Create EarlyAccessRequest
        $ear = EarlyAccessRequest::create([
            'type'         => $step1['type'],
            'first_name'   => $step1['first_name'],
            'last_name'    => '',
            'email'        => $step1['email'],
            'phone_prefix' => $step1['phone_prefix'] ?? null,
            'phone'        => $step1['phone'] ?? null,
            'country'      => $step1['country'] ?? null,
            'city'         => $step1['city'] ?? null,
            'watch_brands' => $brands, // cast 'array': niente json_encode manuale (doppia codifica)
            'watch_count'  => $validated['watch_count'] ?? null,
            'ip_address'   => $request->ip(),
            'locale'       => app()->getLocale(),
            // niente approvazione dell'utente: si approvano solo i relojes
            'status'       => 'approved',
            'approved_at'  => now(),
        ]);

        // Subscribe to seller list
        $listId = $this->getListId('list_id_seller');
        NewsletterSubscriber::subscribe(
            $step1['email'], $step1['first_name'], $listId, 'seller', app()->getLocale()
        );

        // Create User account — founding sellers are auto-approved
        $user = User::create([
            'name'              => $step1['first_name'],
            'first_name'        => $step1['first_name'],
            'last_name'         => '',
            'email'             => $step1['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => 'seller',
            'status'            => 'approved',
            'phone_prefix'      => $step1['phone_prefix'] ?? null,
            'phone'             => $step1['phone'] ?? null,
            'country'           => $step1['country'] ?? null,
            'city'              => $step1['city'] ?? null,
            'locale'            => app()->getLocale(),
            'is_founding_seller'=> true,
        ]);

        $ear->update(['user_id' => $user->id, 'status' => 'approved']);

        // Auto-login
        Auth::login($user);
        $request->session()->regenerate();

        $request->session()->forget('early_access_step1');

        // ── Emails ────────────────────────────────────────────────────────
        $accessType = $step1['type']; // 'seller' or 'both'

        // Welcome email to the new seller
        try {
            Mail::to($user->email)->send(new SellerWelcomeMail($user, $accessType, $ear));
        } catch (\Exception $e) {
            \Log::error('Seller welcome mail failed: ' . $e->getMessage());
        }

        // Admin notification
        try {
            $adminEmail = Setting::get('contact_email', 'support@hausoftime.com');
            Mail::to($adminEmail)->send(new EarlyAccessAdminMail($ear, $user));
        } catch (\Exception $e) {
            \Log::error('Admin notification mail failed: ' . $e->getMessage());
        }

        return redirect()->route('seller.dashboard')->with(
            'success',
            __('messages.seller.welcome_founding')
        );
    }

    // ── Buyer direct flow ─────────────────────────────────────
    private function completeBuyer(Request $request, array $data): \Illuminate\Http\RedirectResponse
    {
        EarlyAccessRequest::create([
            'type'         => 'buyer',
            'first_name'   => $data['first_name'],
            'last_name'    => '',
            'email'        => $data['email'],
            'phone_prefix' => $data['phone_prefix'] ?? null,
            'phone'        => $data['phone'] ?? null,
            'country'      => $data['country'] ?? null,
            'city'         => $data['city'] ?? null,
            'ip_address'   => $request->ip(),
            'locale'       => app()->getLocale(),
            'status'       => 'pending',
        ]);

        $listId = $this->getListId('list_id_buyer');
        NewsletterSubscriber::subscribe(
            $data['email'], $data['first_name'], $listId, 'buyer', app()->getLocale()
        );

        $request->session()->forget('early_access_step1');

        // ── Emails ────────────────────────────────────────────────────────
        try {
            Mail::to($data['email'])->send(new WelcomeBuyerMail(
                $data['first_name'], $data['email'], $data['locale'] ?? app()->getLocale()
            ));
        } catch (\Exception $e) {
            \Log::error('Buyer welcome mail failed: ' . $e->getMessage());
        }

        // Admin notification
        try {
            $adminEmail = Setting::get('contact_email', 'support@hausoftime.com');
            // Get the EarlyAccessRequest we just created
            $earRecord = EarlyAccessRequest::where('email', $data['email'])->latest()->first();
            if ($earRecord) {
                Mail::to($adminEmail)->send(new EarlyAccessAdminMail($earRecord, null));
            }
        } catch (\Exception $e) {
            \Log::error('Admin buyer notification failed: ' . $e->getMessage());
        }

        return redirect()->route('early-access.success')->with([
            'type'  => 'buyer',
            'email' => $data['email'],
            'name'  => $data['first_name'],
        ]);
    }

    public function success()
    {
        return view('pages.early-access-success', [
            'type'  => session('type', 'buyer'),
            'email' => session('email', ''),
            'name'  => session('name', ''),
        ]);
    }

    private function getListId(string $key): ?int
    {
        try {
            $slug = Setting::get($key);
            if (!$slug) return null;
            $list = NewsletterList::where('slug', $slug)->orWhere('id', $slug)->first();
            return $list?->id;
        } catch (\Exception) {
            return null;
        }
    }
}
