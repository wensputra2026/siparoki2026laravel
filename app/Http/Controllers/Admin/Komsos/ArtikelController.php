<?php

namespace App\Http\Controllers\Admin\Komsos;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Artikel;
use App\Models\KategoriKonten;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Response;

class ArtikelController extends BaseAdminController
{
    /**
     * Display list of Artikel & Warta.
     */
    public function index(Request $request): Response
    {
        $query = Artikel::with(['kategori', 'penulis', 'paroki']);
        $this->applyTenantScope($query, $request);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('ringkasan', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        if ($kategoriId = $request->input('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $items = $query->orderByDesc('created_at')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Warta & Artikel Paroki (Komsos)',
            'moduleKey' => 'artikel',
            'items' => $items,
            'filters' => $request->only(['search', 'kategori_id', 'status']),
        ]), $request);
    }

    /**
     * Store new Artikel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori_id' => 'nullable|integer',
            'ringkasan' => 'nullable|string',
            'konten' => 'required|string',
            'status' => 'required|in:Draft,Publikasi,Arsip',
            'gambar' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['judul']) . '-' . time();
        $validated['paroki_id'] = auth()->user()?->paroki_id ?? 1;
        $validated['user_id'] = auth()->id();

        $artikel = Artikel::create($validated);

        return redirect()->back()->with('success', "Artikel '{$artikel->judul}' berhasil disimpan!");
    }

    /**
     * Update Artikel.
     */
    public function update(Request $request, int $id)
    {
        $artikel = Artikel::findOrFail($id);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori_id' => 'nullable|integer',
            'ringkasan' => 'nullable|string',
            'konten' => 'required|string',
            'status' => 'required|in:Draft,Publikasi,Arsip',
            'gambar' => 'nullable|string',
        ]);

        $artikel->update($validated);

        return redirect()->back()->with('success', "Artikel '{$artikel->judul}' berhasil diperbarui!");
    }

    /**
     * Destroy Artikel.
     */
    public function destroy(int $id)
    {
        $artikel = Artikel::findOrFail($id);
        $title = $artikel->judul;
        $artikel->delete();

        return redirect()->back()->with('success', "Artikel '{$title}' telah dihapus.");
    }
}
