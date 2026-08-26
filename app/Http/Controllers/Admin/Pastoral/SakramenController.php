<?php

namespace App\Http\Controllers\Admin\Pastoral;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\PengajuanSakramen;
use App\Models\Sakramen;
use App\Models\SakramenUmat;
use App\Models\Umat;
use Illuminate\Http\Request;
use Inertia\Response;

class SakramenController extends BaseAdminController
{
    /**
     * Display list of Sakramen records.
     */
    public function index(Request $request): Response
    {
        $query = Sakramen::with(['umat', 'pastor', 'paroki']);
        $this->applyTenantScope($query, $request);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('jenis_sakramen', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('nama_penerima', 'like', "%{$search}%")
                  ->orWhere('nama_wali_baptis', 'like', "%{$search}%");
            });
        }

        if ($jenis = $request->input('jenis_sakramen')) {
            $query->where('jenis_sakramen', $jenis);
        }

        $items = $query->orderByDesc('tanggal_sakramen')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Buku Induk Administrasi Sakramen Gereja',
            'moduleKey' => 'sakramen',
            'items' => $items,
            'filters' => $request->only(['search', 'jenis_sakramen']),
        ]), $request);
    }

    /**
     * Store new Sakramen.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_sakramen' => 'required|string|max:100',
            'nomor_surat' => 'nullable|string|max:100',
            'umat_id' => 'nullable|integer',
            'nama_penerima' => 'required|string|max:255',
            'tanggal_sakramen' => 'required|date',
            'tempat_sakramen' => 'nullable|string|max:255',
            'nama_pelayan' => 'nullable|string|max:255',
            'nama_wali_baptis' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $validated['paroki_id'] = auth()->user()?->paroki_id ?? 1;
        $sakramen = Sakramen::create($validated);

        return redirect()->back()->with('success', "Pencatatan Sakramen {$sakramen->jenis_sakramen} berhasil disimpan!");
    }

    /**
     * Update Sakramen.
     */
    public function update(Request $request, int $id)
    {
        $sakramen = Sakramen::findOrFail($id);

        $validated = $request->validate([
            'jenis_sakramen' => 'required|string|max:100',
            'nomor_surat' => 'nullable|string|max:100',
            'umat_id' => 'nullable|integer',
            'nama_penerima' => 'required|string|max:255',
            'tanggal_sakramen' => 'required|date',
            'tempat_sakramen' => 'nullable|string|max:255',
            'nama_pelayan' => 'nullable|string|max:255',
            'nama_wali_baptis' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $sakramen->update($validated);

        return redirect()->back()->with('success', "Data Sakramen {$sakramen->jenis_sakramen} berhasil diperbarui!");
    }

    /**
     * Destroy Sakramen.
     */
    public function destroy(int $id)
    {
        $sakramen = Sakramen::findOrFail($id);
        $jenis = $sakramen->jenis_sakramen;
        $sakramen->delete();

        return redirect()->back()->with('success', "Data Sakramen {$jenis} telah dihapus.");
    }
}
