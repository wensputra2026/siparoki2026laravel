<?php

namespace App\Http\Controllers\Admin\Teritorial;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Paroki;
use App\Models\ProfilParoki;
use Illuminate\Http\Request;
use Inertia\Response;

class ParokiController extends BaseAdminController
{
    /**
     * Display list of Paroki.
     */
    public function index(Request $request): Response
    {
        $query = Paroki::with(['keuskupan', 'dekenat', 'provinsi', 'kabupaten', 'kecamatan', 'desa']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_paroki', 'like', "%{$search}%")
                  ->orWhere('pelindung_paroki', 'like', "%{$search}%")
                  ->orWhere('nama_pastor_paroki_aktif', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($keuskupanId = $request->input('keuskupan_id')) {
            $query->where('keuskupan_id', $keuskupanId);
        }
        if ($dekenatId = $request->input('dekenat_id')) {
            $query->where('dekenat_id', $dekenatId);
        }

        $items = $query->orderBy('nama_paroki')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Master Data Paroki',
            'moduleKey' => 'paroki',
            'items' => $items,
            'filters' => $request->only(['search', 'keuskupan_id', 'dekenat_id']),
        ]), $request);
    }

    /**
     * Store new Paroki.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_paroki' => 'required|string|max:255',
            'pelindung_paroki' => 'nullable|string|max:255',
            'keuskupan_id' => 'required|integer',
            'dekenat_id' => 'nullable|integer',
            'nama_pastor_paroki_aktif' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
        ]);

        $paroki = Paroki::create($validated);

        return redirect()->back()->with('success', "Paroki {$paroki->nama_paroki} berhasil disimpan!");
    }

    /**
     * Update Paroki.
     */
    public function update(Request $request, int $id)
    {
        $paroki = Paroki::findOrFail($id);

        $validated = $request->validate([
            'nama_paroki' => 'required|string|max:255',
            'pelindung_paroki' => 'nullable|string|max:255',
            'keuskupan_id' => 'required|integer',
            'dekenat_id' => 'nullable|integer',
            'nama_pastor_paroki_aktif' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
        ]);

        $paroki->update($validated);

        return redirect()->back()->with('success', "Paroki {$paroki->nama_paroki} berhasil diperbarui!");
    }

    /**
     * Destroy Paroki.
     */
    public function destroy(int $id)
    {
        $paroki = Paroki::findOrFail($id);
        $name = $paroki->nama_paroki;
        $paroki->delete();

        return redirect()->back()->with('success', "Paroki {$name} telah dihapus.");
    }
}
