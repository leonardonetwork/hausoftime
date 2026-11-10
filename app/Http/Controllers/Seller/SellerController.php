<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Watch;
use App\Models\WatchBrand;
use App\Models\WatchCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $stats = [
            'watches'  => Watch::where('seller_id', $user->id)->count(),
            'pending'  => Watch::where('seller_id', $user->id)->where('status','pending')->count(),
            'approved' => Watch::where('seller_id', $user->id)->where('status','approved')->count(),
            'sold'     => Watch::where('seller_id', $user->id)->where('status','sold')->count(),
        ];
        $recentWatches = Watch::where('seller_id', $user->id)->latest()->take(5)->get();
        return view('seller.dashboard', compact('stats', 'recentWatches'));
    }

    public function watches()
    {
        $watches = Watch::where('seller_id', auth()->id())->latest()->paginate(15);
        return view('seller.watches', compact('watches'));
    }

    /**
     * Commissione applicabile al venditore corrente.
     * Letta dai Settings (Admin → Configuración → General), mai hardcoded in vista.
     */
    private function commissionFor(): array
    {
        $isFounding = (bool) auth()->user()->is_founding_seller;

        $rate = $isFounding
            ? (float) Setting::get('commission_founding', 4)
            : (float) Setting::get('commission_standard', 7);

        $es = app()->getLocale() === 'es';

        return [
            'rate'  => $rate,
            'label' => $isFounding
                ? ($es ? 'Fundador' : 'Founding')
                : ($es ? 'Estándar' : 'Standard'),
        ];
    }

    public function watchCreate()
    {
        $brands     = WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->get();
        $categories = WatchCategory::active()->orderBy('sort_order')->orderBy('name_es')->get();
        $commission = $this->commissionFor();

        return view('seller.watch-form', [
            'brands'           => $brands,
            'categories'       => $categories,
            'commissionRate'   => $commission['rate'],
            'commissionLabel'  => $commission['label'],
        ]);
    }

    public function watchStore(Request $request)
    {
        $validated = $request->validate([
            'brand'            => 'required|string|max:200',
            'model'            => 'required|string|max:200',
            'reference'        => 'required|string|max:100',
            'manufacture_year' => 'required|integer|min:1900|max:' . date('Y'),
            'condition'        => 'required|string|max:50',
            'category'         => 'required|string|max:200',
            'box_papers'       => 'required|string|max:50',
            'price'            => 'required|numeric|min:0',
            'price_discounted' => 'nullable|numeric|min:0',
            'description'      => 'required|string|max:5000',
            'images'           => 'required|array|min:1|max:10',
            'images.*'         => 'image|max:10240',
            'documentation_files'   => 'nullable|array|max:5',
            'documentation_files.*' => 'file|mimes:pdf,jpeg,jpg,png,webp|max:10240',
        ]);

        // Handle watch images
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('watches', 'public');
                $imagePaths[] = $path;
            }
        }

        // Handle documentation files
        $docPaths = [];
        if ($request->hasFile('documentation_files')) {
            foreach ($request->file('documentation_files') as $doc) {
                $path = $doc->store('watches/docs', 'public');
                $docPaths[] = $path;
            }
        }

        /* Ora casuale che il vendedor dovra' impostare sul reloj per la foto
           di verifica. Generata dal server: e' la prova di possesso. */
        $verifTime = sprintf('%02d:%02d', random_int(1, 12), random_int(0, 59));

        $watch = Watch::create([
            'seller_id'           => auth()->id(),
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
            'images'              => $imagePaths,
            'documentation_files' => $docPaths ?: null,
            /* Nessun reloj nasce approvato: resta in bozza finche' non
               arriva la foto di verifica, poi passa a 'pending'. */
            'status'              => 'draft',
            'approved_at'         => null,
            'verification_time'   => $verifTime,
        ]);

        return redirect()->route('seller.watches.verification', $watch);
    }

    // ── Verificacion: ultimo paso ────────────────────────────
    public function watchVerification(Watch $watch)
    {
        abort_if((int) $watch->seller_id !== (int) auth()->id(), 403);

        if ($watch->status !== 'draft') {
            return redirect()->route('seller.watches.show', $watch);
        }

        if (!$watch->verification_time) {
            $watch->update(['verification_time' => sprintf('%02d:%02d', random_int(1, 12), random_int(0, 59))]);
        }

        return view('seller.watch-verification', ['watch' => $watch]);
    }

    public function watchVerificationStore(Request $request, Watch $watch)
    {
        abort_if((int) $watch->seller_id !== (int) auth()->id(), 403);

        if ($watch->status !== 'draft') {
            return redirect()->route('seller.watches.show', $watch);
        }

        $request->validate([
            'verification_photo' => 'required|image|max:10240',
        ]);

        $path = $request->file('verification_photo')->store('watches/verification', 'public');

        $auto = Watch::autoApproveOnSubmit();
        $watch->update([
            'verification_photo' => $path,
            'verification_at'    => now(),
            'status'             => $auto ? 'approved' : 'pending',
            'approved_at'        => $auto ? now() : null,
            // la foto di verifica del possesso dà diritto al sigillo "certified"
            'is_certified'       => $auto ? true : $watch->is_certified,
        ]);

        return redirect()->route('seller.watches')
            ->with('success', __($auto ? 'messages.seller.watch_published' : 'messages.seller.watch_submitted'));
    }

    public function watchEdit(Watch $watch)
    {
        abort_if((int) $watch->seller_id !== (int) auth()->id(), 403);
        if ($watch->status === 'sold' || $watch->isReserved()) {
            return redirect()->route('seller.watches.show', $watch)->with('error', app()->getLocale() === 'es'
                ? 'Este reloj está vendido o en proceso de compra y no se puede modificar.'
                : 'This watch is sold or being purchased and cannot be edited.');
        }
        $brands     = WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->get();
        $categories = WatchCategory::active()->orderBy('sort_order')->orderBy('name_es')->get();
        $commission = $this->commissionFor();

        return view('seller.watch-form', [
            'watch'            => $watch,
            'brands'           => $brands,
            'categories'       => $categories,
            'commissionRate'   => $commission['rate'],
            'commissionLabel'  => $commission['label'],
        ]);
    }

    public function watchUpdate(Request $request, Watch $watch)
    {
        abort_if((int) $watch->seller_id !== (int) auth()->id(), 403);
        if ($watch->status === 'sold' || $watch->isReserved()) {
            return redirect()->route('seller.watches.show', $watch)->with('error', app()->getLocale() === 'es'
                ? 'Este reloj está vendido o en proceso de compra y no se puede modificar.'
                : 'This watch is sold or being purchased and cannot be edited.');
        }

        $validated = $request->validate([
            'brand'            => 'required|string|max:200',
            'model'            => 'required|string|max:200',
            'reference'        => 'required|string|max:100',
            'manufacture_year' => 'required|integer|min:1900|max:' . date('Y'),
            'condition'        => 'required|string|max:50',
            'category'         => 'required|string|max:200',
            'box_papers'       => 'required|string|max:50',
            'price'            => 'required|numeric|min:0',
            'price_discounted' => 'nullable|numeric|min:0',
            'description'      => 'required|string|max:5000',
            'images'           => 'nullable|array|max:10',
            'images.*'         => 'image|max:10240',
            'documentation_files'   => 'nullable|array|max:5',
            'documentation_files.*' => 'file|mimes:pdf,jpeg,jpg,png,webp|max:10240',
        ]);

        // ── Images: keep existing + add new + delete removed ──────────────
        $existingImages = is_array($watch->images) ? $watch->images : [];
        $keepImages     = array_values(array_intersect(
            $request->input('keep_images', []),
            $existingImages
        ));
        // Delete physically removed images from storage
        foreach (array_diff($existingImages, $keepImages) as $path) {
            Storage::disk('public')->delete($path);
        }
        // Append new uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $keepImages[] = $img->store('watches', 'public');
            }
        }

        // ── Docs: same approach ────────────────────────────────────────────
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
            'images'              => count($keepImages) ? $keepImages : null,
            'documentation_files' => count($keepDocs)   ? $keepDocs   : null,
            /* Nessuna approvazione automatica: una bozza resta bozza finche'
               non arriva la verifica, e un reloj respinto torna in revisione. */
            'status'              => $watch->status === 'rejected' ? 'pending' : $watch->status,
        ]);

        if ($watch->status === 'draft') {
            return redirect()->route('seller.watches.verification', $watch);
        }

        return redirect('/seller/reloj/' . $watch->id)
            ->with('success', __('messages.seller.watch_updated'));
    }


    public function watchShow(Watch $watch)
    {
        abort_if((int) $watch->seller_id !== (int) auth()->id(), 403);
        return view('seller.watches.show', compact('watch'));
    }

        public function watchDestroy(Watch $watch)
    {
        abort_if((int) $watch->seller_id !== (int) auth()->id(), 403);
        $active = \App\Models\OrderItem::where('watch_id', $watch->id)
            ->whereIn('status', ['pending', 'awaiting_shipment', 'shipped', 'received', 'disputed'])->exists();
        if ($active || $watch->status === 'sold' || $watch->isReserved()) {
            return back()->with('error', app()->getLocale() === 'es'
                ? 'Este reloj tiene una venta en curso y no se puede eliminar.'
                : 'This watch has a sale in progress and cannot be deleted.');
        }
        $watch->delete();
        return back()->with('success', 'Reloj eliminado.');
    }

    public function contacts()
    {
        $user = auth()->user();
        return view('seller.contacts', compact('user'));
    }

    public function contactsUpdate(Request $request)
    {
        $validated = $request->validate([
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
        ]);
        auth()->user()->update($validated);
        return back()->with('success', 'Contactos actualizados.');
    }
}
