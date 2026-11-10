<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NewsletterController extends Controller
{
    public function __construct(private NewsletterService $newsletter) {}

    public function index()
    {
        $campaigns   = NewsletterCampaign::with('creator')->latest()->paginate(15);
        $subscribers = NewsletterSubscriber::active()->count();
        return view('admin.newsletter.index', compact('campaigns', 'subscribers'));
    }

    public function create()
    {
        return view('admin.newsletter.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'subject_es'     => 'required|string|max:255',
            'subject_en'     => 'required|string|max:255',
            'body_es'        => 'required|string',
            'body_en'        => 'required|string',
            'recipient_type' => ['required', Rule::in(['all', 'buyers', 'sellers'])],
        ]);

        NewsletterCampaign::create([
            ...$validated,
            'status'     => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.newsletter.index')
            ->with('success', __('messages.admin.campaign_created'));
    }

    public function edit(NewsletterCampaign $campaign)
    {
        return view('admin.newsletter.edit', compact('campaign'));
    }

    public function update(Request $request, NewsletterCampaign $campaign)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'subject_es'     => 'required|string|max:255',
            'subject_en'     => 'required|string|max:255',
            'body_es'        => 'required|string',
            'body_en'        => 'required|string',
            'recipient_type' => ['required', Rule::in(['all', 'buyers', 'sellers'])],
        ]);
        $campaign->update($validated);
        return redirect()->route('admin.newsletter.index')
            ->with('success', __('messages.admin.campaign_updated'));
    }

    public function send(NewsletterCampaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return back()->with('error', __('messages.admin.campaign_already_sent'));
        }
        $this->newsletter->send($campaign);
        return redirect()->route('admin.newsletter.index')
            ->with('success', __('messages.admin.campaign_sent'));
    }

    public function preview(NewsletterCampaign $campaign)
    {
        return view('admin.newsletter.preview', compact('campaign'));
    }

    public function subscribersList()
    {
        $subscribers = NewsletterSubscriber::latest()->paginate(25);
        return view('admin.newsletter.subscribers', compact('subscribers'));
    }
}
