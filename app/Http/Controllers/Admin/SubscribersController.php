<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EarlyAccessRequest;
use Illuminate\Http\Request;

class SubscribersController extends Controller
{
    public function index(Request $request)
    {
        $query = EarlyAccessRequest::latest();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('email', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
            );
        }

        $requests = $query->paginate(25)->withQueryString();
        $counts = [
            'all'     => EarlyAccessRequest::count(),
            'sellers' => EarlyAccessRequest::where('type', 'seller')->count(),
            'buyers'  => EarlyAccessRequest::where('type', 'buyer')->count(),
        ];

        return view('admin.subscribers.index', compact('requests', 'counts'));
    }

    public function approveSeller(EarlyAccessRequest $earlyAccessRequest)
    {
        $earlyAccessRequest->update(['status' => 'approved', 'approved_at' => now()]);
        if ($earlyAccessRequest->user_id) {
            $earlyAccessRequest->user->update(['status' => 'approved']);
        }
        return back()->with('success', __('messages.admin.seller_approved'));
    }

    public function reject(EarlyAccessRequest $earlyAccessRequest)
    {
        $earlyAccessRequest->update(['status' => 'rejected']);
        if ($earlyAccessRequest->user_id) {
            $earlyAccessRequest->user->update(['status' => 'rejected']);
        }
        return back()->with('success', __('messages.admin.seller_rejected'));
    }
}
