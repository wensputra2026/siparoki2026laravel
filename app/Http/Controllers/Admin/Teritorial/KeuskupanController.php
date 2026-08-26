<?php

namespace App\Http\Controllers\Admin\Teritorial;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Keuskupan;
use Illuminate\Http\Request;
use Inertia\Response;

class KeuskupanController extends BaseAdminController
{
    /**
     * Display list of Keuskupan across Indonesia.
     */
    public function index(Request $request): Response
    {
        $query = Keuskupan::withCount(['parokis', 'dekenats']);

        if ($search = $request->input('search')) {
            $query->where('nama_keuskupan', 'like', "%{$search}%")
                  ->orWhere('regio', 'like', "%{$search}%");
        }

        if ($regio = $request->input('regio')) {
            $query->where('regio', $regio);
        }

        $items = $query->orderBy('nama_keuskupan')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Master Data 39 Keuskupan se-Indonesia (KWI)',
            'moduleKey' => 'keuskupan',
            'items' => $items,
            'filters' => $request->only(['search', 'regio']),
        ]), $request);
    }

    /**
     * Store new Keuskupan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_keuskupan' => 'required|string|max:150|unique:keuskupan,nama_keuskupan',
            'nama_uskup' => 'nullable|string|max:150',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
        ]);

        $keuskupan = Keuskupan::create($validated);

        return redirect()->back()->with('success', "Keuskupan {$keuskupan->nama_keuskupan} berhasil disimpan!");
    }

    /**
     * Update Keuskupan.
     */
    public function update(Request $request, int $id)
    {
        $keuskupan = Keuskupan::findOrFail($id);

        $validated = $request->validate([
            'nama_keuskupan' => 'required|string|max:150|unique:keuskupan,nama_keuskupan,' . $id . ',id_keuskupan',
            'nama_uskup' => 'nullable|string|max:150',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
        ]);

        $keuskupan->update($validated);

        return redirect()->back()->with('success', "Keuskupan {$keuskupan->nama_keuskupan} berhasil diperbarui!");
    }

    /**
     * Destroy Keuskupan.
     */
    public function destroy(int $id)
    {
        $keuskupan = Keuskupan::findOrFail($id);
        $name = $keuskupan->nama_keuskupan;
        $keuskupan->delete();

        return redirect()->back()->with('success', "Keuskupan {$name} telah dihapus.");
    }
}
