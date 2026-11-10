<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EarlyAccessRequest;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Models\Watch;
use App\Models\WatchEvaluation;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'sellers'         => User::where('role', 'seller')->count(),
            'pending_sellers' => User::where('role', 'seller')->where('status', 'pending')->count(),
            'watches'         => Watch::count(),
            'pending_watches' => Watch::where('status', 'pending')->count(),
            'subscribers'     => NewsletterSubscriber::active()->count(),
            'early_access'    => EarlyAccessRequest::count(),
            'evaluations'     => WatchEvaluation::count(),
        ];

        $recentWatches  = Watch::with('seller')->where('status', 'pending')->latest()->take(5)->get();
        $recentRequests = EarlyAccessRequest::latest()->take(5)->get();

        $siteMode = \App\Models\Setting::get('site_mode', 'early_access');
        $shop = [
            'revenue_month' => \App\Models\Order::whereNotNull('paid_at')->where('paid_at', '>=', now()->startOfMonth())
                                ->whereNotIn('status', ['refunded'])->sum('total'),
            'orders_month'  => \App\Models\Order::whereNotNull('paid_at')->where('paid_at', '>=', now()->startOfMonth())->count(),
            'to_ship'       => \App\Models\OrderItem::where('status', 'awaiting_shipment')->count(),
            'disputed'      => \App\Models\OrderItem::where('status', 'disputed')->count(),
            'published'     => Watch::where('status', 'approved')->count(),
        ];
        $recentOrders = \App\Models\Order::whereNotNull('paid_at')->latest('paid_at')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentWatches', 'recentRequests', 'siteMode', 'shop', 'recentOrders'));
    }
}
