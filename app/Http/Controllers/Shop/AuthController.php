<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/** Accesso e registrazione acquirenti con la grafica del negozio. */
class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->filled('next') && str_starts_with((string) $request->query('next'), '/')) {
            $request->session()->put('url.intended', url((string) $request->query('next')));
        }
        return view('shop.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }
        $request->session()->regenerate();
        $user = Auth::user();
        \App\Http\Controllers\Auth\LoginController::afterLogin($user);

        $default = $user->isAdmin() ? route('admin.dashboard') : route('account.orders');
        return redirect()->intended($default);
    }

    public function showRegister()
    {
        return view('shop.auth.register');
    }

    public function register(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email:rfc|max:255|unique:users,email',
            'password'         => ['required', 'confirmed', Password::min(8)],
            'accept_terms'     => 'accepted',
            'marketing_opt_in' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'             => trim($data['first_name'] . ' ' . $data['last_name']),
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'role'             => 'buyer',
            'status'           => 'approved',
            'locale'           => app()->getLocale(),
            'marketing_opt_in' => $request->boolean('marketing_opt_in'),
        ]);

        if ($request->boolean('marketing_opt_in')) {
            try {
                $listSlug = \App\Models\Setting::get('list_id_buyer');
                $listId = $listSlug ? \App\Models\NewsletterList::where('slug', $listSlug)->value('id') : null;
                NewsletterSubscriber::subscribe($user->email, $user->first_name, $listId, 'buyer', app()->getLocale());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();
        \App\Http\Controllers\Auth\LoginController::afterLogin($user);

        return redirect()->intended(route('account.orders'))->with('success', __('shop.auth.welcome', ['name' => $user->first_name]));
    }
}
