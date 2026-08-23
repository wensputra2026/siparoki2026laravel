<div>
    @if($submitted)
    <div class="bg-white rounded-xl shadow-md p-8 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Pengajuan Berhasil!</h2>
        <p class="text-gray-600 mb-6">Pengajuan sakramen Anda telah diterima. Tim kami akan menghubungi Anda via WhatsApp untuk proses selanjutnya.</p>
        <a href="/" wire:navigate class="inline-block bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
            Kembali ke Beranda
        </a>
    </div>
    @else
    {{-- Progress Steps --}}
    <div class="flex items-center justify-center mb-8">
        @for($i = 1; $i <= $totalSteps; $i++)
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition {{ $step >= $i ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                {{ $i }}
            </div>
            @if($i < $totalSteps)
                <div class="w-16 h-1 transition {{ $step > $i ? 'bg-amber-600' : 'bg-gray-200' }}"></div>
            @endif
        </div>
        @endfor
    </div>

    <div class="bg-white rounded-xl shadow-md p-8">
        <form wire:submit.prevent="{{ $step === $totalSteps ? 'submit' : 'nextStep' }}">
            {{-- Error Messages --}}
            @if(!empty($errors_list))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                    @foreach($errors_list as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Step 1: Data Diri --}}
            @if($step === 1)
            <h3 class="text-lg font-bold text-gray-900 mb-6">Langkah 1: Data Diri</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nama_lengkap" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" placeholder="Masukkan nama lengkap">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" wire:model="whatsapp" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" placeholder="08xxxxxxxxxx">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="email" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" placeholder="email@contoh.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea wire:model="alamat" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" placeholder="Alamat lengkap"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-amber-700 transition">
                    Selanjutnya &rarr;
                </button>
            </div>
            @endif

            {{-- Step 2: Data Sakramen --}}
            @if($step === 2)
            <h3 class="text-lg font-bold text-gray-900 mb-6">Langkah 2: Jenis Sakramen</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Sakramen <span class="text-red-500">*</span></label>
                    <select wire:model="tipe_sakramen" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                        <option value="">-- Pilih Sakramen --</option>
                        <option value="Baptis">Baptis</option>
                        <option value="Baptis Dewasa">Baptis Dewasa</option>
                        <option value="Komuni Pertama">Komuni Pertama</option>
                        <option value="Krisma">Krisma</option>
                        <option value="Pernikahan">Pernikahan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pelaksanaan (Opsional)</label>
                    <input type="date" wire:model="tanggal_pelaksanaan" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Catatan</label>
                    <textarea wire:model="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" placeholder="Tambahkan keterangan jika diperlukan"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" wire:click="prevStep" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-50 transition">
                    &larr; Kembali
                </button>
                <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-amber-700 transition">
                    Selanjutnya &rarr;
                </button>
            </div>
            @endif

            {{-- Step 3: Konfirmasi --}}
            @if($step === 3)
            <h3 class="text-lg font-bold text-gray-900 mb-6">Langkah 3: Konfirmasi</h3>

            <div class="bg-gray-50 rounded-lg p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Nama:</span>
                    <span class="font-medium">{{ $nama_lengkap }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">WhatsApp:</span>
                    <span class="font-medium">{{ $whatsapp }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Sakramen:</span>
                    <span class="font-medium">{{ $tipe_sakramen }}</span>
                </div>
                @if($tanggal_pelaksanaan)
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal:</span>
                    <span class="font-medium">{{ \Carbon\Carbon::parse($tanggal_pelaksanaan)->format('d M Y') }}</span>
                </div>
                @endif
                @if($keterangan)
                <div>
                    <span class="text-gray-600">Keterangan:</span>
                    <p class="font-medium mt-1">{{ $keterangan }}</p>
                </div>
                @endif
            </div>

            <div class="mt-6">
                <label class="flex items-start space-x-3">
                    <input type="checkbox" wire:model="agree" class="mt-1 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    <span class="text-sm text-gray-600">Saya menyatakan bahwa data yang diisi adalah benar dan bersedia mengikuti prosedur yang berlaku di paroki.</span>
                </label>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" wire:click="prevStep" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-50 transition">
                    &larr; Kembali
                </button>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
                    Kirim Pengajuan
                </button>
            </div>
            @endif
        </form>
    </div>
    @endif
</div>
