<?php

namespace App\Livewire;

use App\Models\PengajuanSakramen;
use Livewire\Component;

class FormSakramen extends Component
{
    public $step = 1;
    public $totalSteps = 3;

    // Step 1: Data Diri
    public $nama_lengkap = '';
    public $whatsapp = '';
    public $email = '';
    public $alamat = '';

    // Step 2: Data Sakramen
    public $tipe_sakramen = '';
    public $tanggal_pelaksanaan = '';
    public $keterangan = '';

    // Step 3: Konfirmasi
    public $agree = false;

    public $submitted = false;
    public $errors_list = [];

    protected $rules = [
        'nama_lengkap' => 'required|min:3',
        'whatsapp' => 'required|min:10',
        'tipe_sakramen' => 'required',
    ];

    public function nextStep()
    {
        $this->validateStep();
        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function validateStep()
    {
        $this->errors_list = [];

        if ($this->step === 1) {
            if (empty($this->nama_lengkap) || strlen($this->nama_lengkap) < 3) {
                $this->errors_list[] = 'Nama lengkap wajib diisi (minimal 3 karakter).';
            }
            if (empty($this->whatsapp) || strlen($this->whatsapp) < 10) {
                $this->errors_list[] = 'Nomor WhatsApp wajib diisi (minimal 10 digit).';
            }
        }

        if ($this->step === 2) {
            if (empty($this->tipe_sakramen)) {
                $this->errors_list[] = 'Tipe sakramen wajib dipilih.';
            }
        }

        return empty($this->errors_list);
    }

    public function submit()
    {
        $this->validate([
            'nama_lengkap' => 'required|min:3',
            'whatsapp' => 'required|min:10',
            'tipe_sakramen' => 'required',
            'agree' => 'accepted',
        ]);

        PengajuanSakramen::create([
            'nama_lengkap' => $this->nama_lengkap,
            'whatsapp' => $this->whatsapp,
            'tipe_sakramen' => $this->tipe_sakramen,
            'tanggal_pelaksanaan' => $this->tanggal_pelaksanaan ?: null,
            'keterangan' => $this->keterangan,
            'status_pengajuan' => 'Pending',
            'status_pembayaran' => 'Menunggu Verifikasi',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.form-sakramen');
    }
}
