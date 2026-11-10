<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) return $this->redirectAfterLogin(Auth::user());
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        $validated['email'] = mb_strtolower(trim($validated['email']));
        if (Auth::attempt($validated, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            self::afterLogin($user);
            return $this->redirectAfterLogin($user);
        }
        return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
    }

    private function redirectAfterLogin($user): \Illuminate\Http\RedirectResponse
    {
        if ($user->isAdmin())  return redirect()->intended(route('admin.dashboard'));
        if ($user->isSeller()) return redirect()->intended(route('seller.dashboard'));
        return redirect()->intended(route('home'));
    }

    /** Operazioni comuni dopo ogni login (venditori, admin, acquirenti). */
    public static function afterLogin($user): void
    {
        session(['locale' => $user->locale ?: 'es']);
        app()->setLocale($user->locale ?: 'es');
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        \App\Http\Controllers\Shop\FavoriteController::mergeSessionInto($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // ── Password reset — custom implementation ────────────────────────────────
    // Uses SHA-256 (not bcrypt) stored directly in password_reset_tokens.
    // Bypasses the Password facade to avoid bcrypt/timing issues on shared hosting.

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $locale = app()->getLocale();
        $email  = strtolower(trim($request->email));
        $user   = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // Always show success to prevent email enumeration
        if (!$user) {
            return back()->with('status', $locale === 'es'
                ? 'Si el email está registrado, recibirás el enlace en breve.'
                : 'If the email is registered you will receive a link shortly.');
        }

        // Generate a 64-char hex token
        $token = bin2hex(random_bytes(32)); // 64 hex chars

        // Store SHA-256 hash in DB — simpler and faster than bcrypt
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        // Build reset URL using APP_URL to ensure HTTPS
        $resetUrl = rtrim(config('app.url'), '/') . '/seller/reset-contrasena/' . $token . '?email=' . urlencode($user->email);

        try {
            Mail::to($user->email)->send(
                new PasswordResetMail($resetUrl, $user->email, $user->locale ?? $locale)
            );
        } catch (\Exception $e) {
            \Log::error('Password reset mail failed: ' . $e->getMessage());
        }

        return back()->with('status', $locale === 'es'
            ? 'Te hemos enviado el enlace de recuperación.'
            : 'Reset link sent. Check your inbox.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email ?? '',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $locale = app()->getLocale();

        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $email = strtolower(trim($request->email));

        // Direct DB lookup — no Password facade
        $record = DB::table('password_reset_tokens')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => __('passwords.token')]);
        }

        // Check expiry (60 minutes)
        if ($record->created_at && \Carbon\Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->whereRaw('LOWER(email) = ?', [$email])->delete();
            return back()->withErrors(['email' => __('passwords.token')]);
        }

        // Constant-time SHA-256 comparison
        if (!hash_equals(hash('sha256', $request->token), $record->token)) {
            return back()->withErrors(['email' => __('passwords.token')]);
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$user) {
            return back()->withErrors(['email' => __('passwords.user')]);
        }

        // Update password and delete used token
        $user->forceFill(['password' => Hash::make($request->password)])->save();
        DB::table('password_reset_tokens')->whereRaw('LOWER(email) = ?', [$email])->delete();

        return redirect()->route('login')
            ->with('success', $locale === 'es'
                ? '¡Contraseña actualizada! Inicia sesión con tu nueva contraseña.'
                : 'Password updated! Sign in with your new password.');
    }
}
