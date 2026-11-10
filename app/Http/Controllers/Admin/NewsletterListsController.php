<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterListsController extends Controller
{
    public function index()
    {
        $lists = NewsletterList::withCount([
            'subscribers as total_count',
            'activeSubscribers as active_count',
        ])->latest()->get();

        return view('admin.newsletter.lists', compact('lists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Ensure unique slug
        $base = $validated['slug'];
        $i = 1;
        while (NewsletterList::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        NewsletterList::create($validated);

        return back()->with('success', 'Lista creada correctamente.');
    }

    public function update(Request $request, NewsletterList $newsletterList)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
        ]);

        $newsletterList->update($validated);

        return back()->with('success', 'Lista actualizada.');
    }

    public function destroy(NewsletterList $newsletterList)
    {
        // Dissocia iscritti prima di eliminare
        NewsletterSubscriber::where('list_id', $newsletterList->id)
            ->update(['list_id' => null]);

        $newsletterList->delete();

        return back()->with('success', 'Lista eliminada.');
    }

    public function subscribers(NewsletterList $newsletterList)
    {
        $subscribers = $newsletterList->subscribers()
            ->latest()->paginate(25);

        return view('admin.newsletter.list-subscribers',
            compact('newsletterList', 'subscribers'));
    }
}
