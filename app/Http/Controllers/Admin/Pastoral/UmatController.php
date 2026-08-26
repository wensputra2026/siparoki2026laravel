<?php

namespace App\Http\Controllers\Admin\Pastoral;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Kapela;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Umat;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Inertia\Response;

class UmatController extends BaseAdminController
{
    /**
     * Display paginated list of Umat with search and multi-filtering.
     */
    public function index(Request $request): Response
    {
        $query = Umat::with(['kk', 'wilayah', 'kapela', 'kub', 'paroki']);
        $this->applyTenantScope($query, $request);

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nama_baptis', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        // Specific hierarchical filters
        if ($wilayahId = $request->input('wilayah_id')) {
            $query->where('wilayah_id', $wilayahId);
        }
        if ($kapelaId = $request->input('kapela_id')) {
            $query->where('kapela_id', $kapelaId);
        }
        if ($kubId = $request->input('kub_id')) {
            $query->where('kub_id', $kubId);
        }
        if ($gender = $request->input('jenis_kelamin')) {
            $query->where('jenis_kelamin', $gender);
        }
        if ($status = $request->input('status_umat')) {
            $query->where('status_umat', $status);
        }

        $items = $query->orderBy('nama_lengkap')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Data Umat Paroki',
            'moduleKey' => 'umat',
            'items' => $items,
            'filters' => $request->only(['search', 'wilayah_id', 'kapela_id', 'kub_id', 'jenis_kelamin', 'status_umat']),
        ]), $request);
    }

    /**
     * Show form for creating a new Umat.
     */
    public function create(Request $request): Response
    {
        $lookups = $this->getCommonLookups($request);
        return $this->renderInertia('Inertia/UmatForm', array_merge($lookups, [
            'title' => 'Tambah Data Umat Baru',
            'isEdit' => false,
            'umat' => null,
        ]), $request);
    }

    /**
     * Store newly created Umat in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nama_baptis' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:30|unique:umat,nik',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'wilayah_id' => 'nullable|integer',
            'kapela_id' => 'nullable|integer',
            'kub_id' => 'nullable|integer',
            'paroki_id' => 'nullable|integer',
            'kk_id' => 'nullable|integer',
            'hubungan_keluarga' => 'nullable|string|max:50',
            'status_umat' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:30',
        ]);

        if (empty($validated['paroki_id'])) {
            $validated['paroki_id'] = auth()->user()?->paroki_id ?? 1;
        }

        $umat = Umat::create($validated);

        return redirect()->back()->with('success', "Data Umat {$umat->nama_lengkap} berhasil disimpan!");
    }

    /**
     * Show form for editing Umat.
     */
    public function edit(Request $request, int $id): Response
    {
        $umat = Umat::with(['kk', 'wilayah', 'kapela', 'kub'])->findOrFail($id);
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/UmatForm', array_merge($lookups, [
            'title' => "Edit Umat: {$umat->nama_lengkap}",
            'isEdit' => true,
            'umat' => $umat,
        ]), $request);
    }

    /**
     * Update existing Umat in storage.
     */
    public function update(Request $request, int $id)
    {
        $umat = Umat::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nama_baptis' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:30|unique:umat,nik,' . $id . ',id_umat',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'wilayah_id' => 'nullable|integer',
            'kapela_id' => 'nullable|integer',
            'kub_id' => 'nullable|integer',
            'kk_id' => 'nullable|integer',
            'hubungan_keluarga' => 'nullable|string|max:50',
            'status_umat' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:30',
        ]);

        $umat->update($validated);

        return redirect()->back()->with('success', "Data Umat {$umat->nama_lengkap} berhasil diperbarui!");
    }

    /**
     * Remove Umat from storage.
     */
    public function destroy(int $id)
    {
        $umat = Umat::findOrFail($id);
        $name = $umat->nama_lengkap;
        $umat->delete();

        return redirect()->back()->with('success', "Data Umat {$name} telah dihapus.");
    }
}
