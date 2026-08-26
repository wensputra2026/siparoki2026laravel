<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TransaksiPembayaran extends Model
{
    protected $table = 'transaksi_pembayaran';

    protected $fillable = [
        'order_id',
        'tipe_transaksi',
        'referensi_id',
        'nama_pembayar',
        'email_pembayar',
        'nomor_telepon',
        'jumlah',
        'gross_amount',
        'snap_token',
        'snap_redirect_url',
        'payment_type',
        'payment_channel',
        'va_number',
        'qr_code_url',
        'status_transaksi',
        'transaction_time',
        'settlement_time',
        'raw_response',
        'signature_key',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'transaction_time' => 'datetime',
            'settlement_time' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    /**
     * Self-healing table check
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('transaksi_pembayaran')) {
                Schema::create('transaksi_pembayaran', function ($table) {
                    $table->increments('id');
                    $table->string('order_id', 100)->unique();
                    $table->string('tipe_transaksi', 50)->default('iuran'); // iuran, donasi, kolekte, intensi, lapak, uji_coba
                    $table->unsignedBigInteger('referensi_id')->nullable();
                    $table->string('nama_pembayar', 150);
                    $table->string('email_pembayar', 150)->nullable();
                    $table->string('nomor_telepon', 30)->nullable();
                    $table->decimal('jumlah', 15, 2)->default(0);
                    $table->decimal('gross_amount', 15, 2)->default(0);
                    $table->string('snap_token', 255)->nullable();
                    $table->string('snap_redirect_url', 500)->nullable();
                    $table->string('payment_type', 50)->nullable();
                    $table->string('payment_channel', 50)->nullable();
                    $table->string('va_number', 100)->nullable();
                    $table->string('qr_code_url', 500)->nullable();
                    $table->string('status_transaksi', 30)->default('pending'); // pending, settlement, capture, deny, cancel, expire, failure
                    $table->timestamp('transaction_time')->nullable();
                    $table->timestamp('settlement_time')->nullable();
                    $table->json('raw_response')->nullable();
                    $table->string('signature_key', 255)->nullable();
                    $table->text('keterangan')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }
}
