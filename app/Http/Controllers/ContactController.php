<?php
namespace App\Http\Controllers;

use App\Mail\ContactSupportMail;
use App\Mail\ContactUserCopyMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:200',
            'email'        => 'required|email',
            'phone_prefix' => 'nullable|string|max:10',
            'phone'        => 'nullable|string|max:30',
            'reason'       => 'required|string',
            'other_reason' => 'nullable|string|max:300',
            'message'      => 'required|string|max:3000',
            'accept_terms' => 'accepted',
        ]);

        $data = [
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'phone_prefix'  => $validated['phone_prefix'] ?? '',
            'phone'         => $validated['phone'] ?? '',
            'reason'        => $validated['reason'],
            'other_reason'  => $validated['other_reason'] ?? '',
            'message'       => $validated['message'],
            'locale'        => app()->getLocale(),
            'authenticated' => auth()->check(),
            'user_id'       => auth()->id(),
        ];

        // ── Email to support ──────────────────────────────────────────────
        try {
            $to = Setting::get('contact_email', 'support@hausoftime.com');
            Mail::to($to)->send(new ContactSupportMail($data));
        } catch (\Exception $e) {
            \Log::error('Contact support mail failed: ' . $e->getMessage());
        }

        // ── Copy to user ──────────────────────────────────────────────────
        try {
            Mail::to($data['email'], $data['name'])->send(new ContactUserCopyMail($data));
        } catch (\Exception $e) {
            \Log::error('Contact user copy mail failed: ' . $e->getMessage());
        }

        $msg = app()->getLocale() === 'es'
            ? '¡Gracias! Hemos recibido tu mensaje. Te responderemos lo antes posible y recibirás una copia en tu email.'
            : 'Thank you! We have received your message. We will get back to you shortly and you will receive a copy by email.';

        return back()->with('success', $msg);
    }
}
