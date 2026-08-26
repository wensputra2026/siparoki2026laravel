<?php

namespace App\Http\Controllers\Admin\Pastoral;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\KkKatolik;
use App\Models\Umat;
use Illuminate\Http\Request;
use Inertia\Response;

class KartuKeluargaController extends BaseAdminController
{
    /**
     * Display list of Kartu Keluarga Katolik (KKK).
     */
    public function index(Request $request): Response
    {
        $query = KkKatolik::with(['kepalaKeluarga', 'wilayah', 'kapela', 'kub', 'anggotas']);
        $this->applyTenantScope($query, $request);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kk', 'like', "%{$search}%")
                  ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($wilayahId = $request->input('wilayah_id')) {
            $query->where('wilayah_id', $wilayahId);
        }
        if ($kubId = $request->input('kub_id')) {
            $query->where('kub_id', $kubId);
        }

        $items = $query->orderBy('nama_kepala_keluarga')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Buku Induk Kartu Keluarga Katolik (KKK)',
            'moduleKey' => 'kk-katolik',
            'items' => $items,
            'filters' => $request->only(['search', 'wilayah_id', 'kub_id']),
        ]), $request);
    }

    /**
     * Store new KK.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_kk' => 'required|string|max:50|unique:kk_katolik,nomor_kk',
            'nama_kepala_keluarga' => 'required|string|max:255',
            'wilayah_id' => 'nullable|integer',
            'kapela_id' => 'nullable|integer',
            'kub_id' => 'nullable|integer',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:30',
        ]);

        $validated['paroki_id'] = auth()->user()?->paroki_id ?? 1;
        $kk = KkKatolik::create($validated);

        return redirect()->back()->with('success', "Kartu Keluarga No. {$kk->nomor_kk} berhasil disimpan!");
    }

    /**
     * Update KK.
     */
    public function update(Request $request, int $id)
    {
        $kk = KkKatolik::findOrFail($id);

        $validated = $request->validate([
            'nomor_kk' => 'required|string|max:50|unique:kk_katolik,nomor_kk,' . $id . ',id_kk',
            'nama_kepala_keluarga' => 'required|string|max:255',
            'wilayah_id' => 'nullable|integer',
            'kapela_id' => 'nullable|integer',
            'kub_id' => 'nullable|integer',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:30',
        ]);

        $kk->update($validated);

        return redirect()->back()->with('success', "Kartu Keluarga No. {$kk->nomor_kk} berhasil diperbarui!");
    }

    /**
     * Destroy KK.
     */
    public function destroy(int $id)
    {
        $kk = KkKatolik::findOrFail($id);
        $no = $kk->nomor_kk;
        $kk->delete();

        return redirect()->back()->with('success', "Kartu Keluarga No. {$no} telah dihapus.");
    }
}
