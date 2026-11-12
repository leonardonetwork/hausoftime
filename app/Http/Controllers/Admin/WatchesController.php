<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use App\Models\WatchBrand;
use App\Models\WatchCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WatchesController extends Controller
{
    public function index(Request $request)
    {
        $query = Watch::with('seller')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }
        if ($request->filled('seller')) {
            $query->whereHas('seller', fn($q) => $q->where('name', 'like', '%' . $request->seller . '%'));
        }

        $watches = $query->paginate(20)->withQueryString();
        $pendingIds = Watch::where('status', 'pending')->orderBy('verification_at')->orderBy('id')->pluck('id');
        return view('admin.products.index', compact('watches', 'pendingIds'));
    }

    public function show(Watch $watch)
    {
        $watch->load('seller');
        return view('admin.products.show', compact('watch'));
    }

    /** Scheda di revisione rapida (frammento HTML per il popup dell'elenco). */
    public function review(Watch $watch)
    {
        $watch->load('seller');
        $next = Watch::where('status', 'pending')->where('id', '!=', $watch->id)
            ->orderBy('verification_at')->orderBy('id')->value('id');
        return view('admin.products.review', compact('watch', 'next'));
    }

    public function edit(Watch $watch)
    {
        $watch->load('seller');
        $brands     = WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->get();
        $categories = WatchCategory::active()->orderBy('sort_order')->orderBy('name_es')->get();
        return view('admin.products.edit', compact('watch', 'brands', 'categories'));
    }

    public function update(Request $request, Watch $watch)
    {
        $validated = $request->validate([
            'brand'            => 'required|string|max:200',
            'model'            => 'required|string|max:200',
            'reference'        => 'nullable|string|max:100',
            'manufacture_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'condition'        => 'required|string|max:50',
            'category'         => 'nullable|string|max:200',
            'box_papers'       => 'nullable|string|max:50',
            'price'            => 'required|numeric|min:0',
            'price_discounted' => 'nullable|numeric|min:0',
            'description'      => 'nullable|string|max:5000',
            'status'           => 'required|in:pending,approved,rejected,sold,draft',
            'admin_notes'      => 'nullable|string|max:1000',
            'images'           => 'nullable|array|max:10',
            'images.*'         => 'image|max:10240',
            'documentation_files'   => 'nullable|array|max:5',
            'documentation_files.*' => 'file|mimes:pdf,jpeg,jpg,png,webp|max:10240',
            'description_en'   => 'nullable|string|max:5000',
            'case_size'        => 'nullable|string|max:40',
            'material'         => 'nullable|string|max:160',
            'country_code'     => 'nullable|string|size:2|exists:countries,code',
            'city'             => 'nullable|string|max:120',
            'sort_order'       => 'nullable|integer',
        ]);

        // ── Images ────────────────────────────────────────────────
        $existingImages = is_array($watch->images) ? $watch->images : [];
        $keepImages     = array_values(array_intersect(
            $request->input('keep_images', []),
            $existingImages
        ));
        foreach (array_diff($existingImages, $keepImages) as $path) {
            Storage::disk('public')->delete($path);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $keepImages[] = $img->store('watches', 'public');
            }
        }

        // ── Docs ──────────────────────────────────────────────────
        $existingDocs = is_array($watch->documentation_files) ? $watch->documentation_files : [];
        $keepDocs     = array_values(array_intersect(
            $request->input('keep_docs', []),
            $existingDocs
        ));
        foreach (array_diff($existingDocs, $keepDocs) as $path) {
            Storage::disk('public')->delete($path);
        }
        if ($request->hasFile('documentation_files')) {
            foreach ($request->file('documentation_files') as $doc) {
                $keepDocs[] = $doc->store('watches/docs', 'public');
            }
        }

        // ── approved_at logic ─────────────────────────────────────
        $approvedAt = $watch->approved_at;
        if ($validated['status'] === 'approved' && !$approvedAt) {
            $approvedAt = now();
        } elseif ($validated['status'] !== 'approved') {
            $approvedAt = null;
        }

        $watch->update([
            'brand'               => $validated['brand'],
            'model'               => $validated['model'],
            'reference'           => $validated['reference'] ?? null,
            'manufacture_year'    => $validated['manufacture_year'] ?? null,
            'condition'           => $validated['condition'],
            'category'            => $validated['category'] ?? null,
            'box_papers'          => $validated['box_papers'] ?? 'none',
            'price'               => $validated['price'],
            'price_discounted'    => $validated['price_discounted'] ?? null,
            'description'         => $validated['description'] ?? null,
            'status'              => $validated['status'],
            'admin_notes'         => $validated['admin_notes'] ?? null,
            'approved_at'         => $approvedAt,
            'images'              => count($keepImages) ? $keepImages : null,
            'documentation_files' => count($keepDocs)   ? $keepDocs   : null,
            'description_en'      => $validated['description_en'] ?? null,
            'case_size'           => $validated['case_size'] ?? null,
            'material'            => $validated['material'] ?? null,
            'country_code'        => $validated['country_code'] ?? null,
            'city'                => $validated['city'] ?? null,
            'sort_order'          => $validated['sort_order'] ?? 0,
            'is_certified'        => $request->boolean('is_certified'),
            'is_featured'         => $request->boolean('is_featured'),
            'sold_at'             => $validated['status'] === 'sold' ? ($watch->sold_at ?? now()) : null,
        ]);

        return redirect('/admin/productos/' . $watch->id)
            ->with('success', 'Reloj actualizado correctamente.');
    }

    public function approve(Watch $watch)
    {
        // La foto di verifica del possesso dà diritto al sigillo "certified"
        $watch->update(['status' => 'approved', 'approved_at' => now(),
                        'is_certified' => $watch->is_certified || (bool) $watch->verification_photo]);
        try {
            \Mail::to($watch->seller->email)->send(new \App\Mail\WatchApprovedMail($watch));
        } catch (\Throwable $e) {
            \Log::error('WatchApprovedMail: ' . $e->getMessage());
        }
        if (request()->expectsJson()) return $this->reviewJson($watch);
        return back()->with('success', __('messages.admin.watch_approved'));
    }

    public function reject(Request $request, Watch $watch)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:1000']);
        $watch->update(['status' => 'rejected', 'admin_notes' => $request->admin_notes]);
        try {
            \Mail::to($watch->seller->email)->send(new \App\Mail\WatchRejectedMail($watch));
        } catch (\Throwable $e) {
            \Log::error('WatchRejectedMail: ' . $e->getMessage());
        }
        if ($request->expectsJson()) return $this->reviewJson($watch);
        return back()->with('success', __('messages.admin.watch_rejected'));
    }

    private function reviewJson(Watch $watch)
    {
        $watch->refresh();
        return response()->json([
            'id'      => $watch->id,
            'status'  => $watch->status,
            'badge'   => $watch->status_badge,
            'pending' => Watch::where('status', 'pending')->count(),
            'next'    => Watch::where('status', 'pending')->orderBy('verification_at')->orderBy('id')->value('id'),
        ]);
    }

    public function destroy(Watch $watch)
    {
        $active = \App\Models\OrderItem::where('watch_id', $watch->id)
            ->whereIn('status', ['pending', 'awaiting_shipment', 'shipped', 'received', 'disputed'])->exists();
        if ($active) {
            return back()->with('error', 'Este reloj tiene un pedido en curso y no se puede eliminar.');
        }
        $watch->delete();
        return redirect()->route('admin.watches.index')->with('success', 'Reloj eliminado.');
    }
}
