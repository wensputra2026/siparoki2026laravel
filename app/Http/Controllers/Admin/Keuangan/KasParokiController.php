<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Keuangan;
use App\Models\MasterCoa;
use Illuminate\Http\Request;
use Inertia\Response;

class KasParokiController extends BaseAdminController
{
    /**
     * Display list of financial transactions.
     */
    public function index(Request $request): Response
    {
        $query = Keuangan::with(['coa', 'paroki']);
        $this->applyTenantScope($query, $request);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('uraian', 'like', "%{$search}%")
                  ->orWhere('nomor_bukti', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($jenis = $request->input('jenis_transaksi')) {
            $query->where('jenis_transaksi', $jenis);
        }
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $items = $query->orderByDesc('tanggal')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Buku Kas & Transaksi Keuangan Paroki',
            'moduleKey' => 'keuangan',
            'items' => $items,
            'filters' => $request->only(['search', 'jenis_transaksi', 'start_date', 'end_date']),
        ]), $request);
    }

    /**
     * Store new transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis_transaksi' => 'required|in:Pemasukan,Pengeluaran',
            'uraian' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'coa_id' => 'nullable|integer',
            'nomor_bukti' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $validated['paroki_id'] = auth()->user()?->paroki_id ?? 1;
        $trx = Keuangan::create($validated);

        return redirect()->back()->with('success', "Transaksi Kas sebesar Rp " . number_format($trx->nominal, 0, ',', '.') . " berhasil dicatat!");
    }

    /**
     * Update transaction.
     */
    public function update(Request $request, int $id)
    {
        $trx = Keuangan::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis_transaksi' => 'required|in:Pemasukan,Pengeluaran',
            'uraian' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'coa_id' => 'nullable|integer',
            'nomor_bukti' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $trx->update($validated);

        return redirect()->back()->with('success', "Transaksi Kas berhasil diperbarui!");
    }

    /**
     * Destroy transaction.
     */
    public function destroy(int $id)
    {
        $trx = Keuangan::findOrFail($id);
        $trx->delete();

        return redirect()->back()->with('success', "Transaksi Kas telah dihapus.");
    }
}
