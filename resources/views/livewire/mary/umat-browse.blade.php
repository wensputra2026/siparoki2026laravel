<div>
    <x-mary-card>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-bold">Data Umat</h3>
                <p class="text-sm text-base-content/60">Tampilan bertahap (MaryUI) — migrasi dari Filament dimulai dari sini.</p>
            </div>
            <x-mary-input icon="o-magnifying-glass" placeholder="Cari nama / NIU..." wire:model.live="search" class="md:w-72" />
        </div>

        <x-mary-table :headers="$headers" :rows="$rows" with-pagination sticky>
            @scope('cell.status_aktif', $row)
                @if($row->status_aktif)
                    <x-mary-badge value="Ya" class="badge-success" />
                @else
                    <x-mary-badge value="Tidak" class="badge-warning" />
                @endif
            @endscope

            @scope('cell.status_umat', $row)
                <x-mary-badge :value="$row->status_umat" class="{{ $row->status_umat == 'Aktif' ? 'badge-success' : 'badge-warning' }}" />
            @endscope

            @scope('cell.jenis_kelamin', $row)
                <span class="badge {{ $row->jenis_kelamin == 'Laki-Laki' ? 'badge-info' : 'badge-secondary' }} badge-sm">
                    {{ $row->jenis_kelamin }}
                </span>
            @endscope

            <x-slot:empty>
                <div class="text-center py-8 text-base-content/50">Tidak ada data umat.</div>
            </x-slot:empty>
        </x-mary-table>
    </x-mary-card>
</div>
