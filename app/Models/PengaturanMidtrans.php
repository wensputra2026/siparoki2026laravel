<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PengaturanMidtrans extends Model
{
    protected $table = 'pengaturan_midtrans';

    protected $fillable = [
        'is_active',
        'is_production',
        'merchant_id',
        'client_key',
        'server_key',
        'enable_qris',
        'enable_va',
        'enable_gopay',
        'enable_credit_card',
        'enable_cstore',
        'custom_expiry_duration',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_production' => 'boolean',
            'enable_qris' => 'boolean',
            'enable_va' => 'boolean',
            'enable_gopay' => 'boolean',
            'enable_credit_card' => 'boolean',
            'enable_cstore' => 'boolean',
            'custom_expiry_duration' => 'integer',
        ];
    }

    /**
     * Helper to get default or first active configuration.
     */
    public static function getActiveConfig(): self
    {
        self::ensureTableExists();

        $config = self::first();
        if (!$config) {
            $config = self::create([
                'is_active' => false,
                'is_production' => false,
                'merchant_id' => '',
                'client_key' => '',
                'server_key' => '',
                'enable_qris' => true,
                'enable_va' => true,
                'enable_gopay' => true,
                'enable_credit_card' => false,
                'enable_cstore' => false,
                'custom_expiry_duration' => 60,
                'keterangan' => 'Konfigurasi Midtrans Snap SIPAROKI',
            ]);
        }

        return $config;
    }

    /**
     * Self-healing table check
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('pengaturan_midtrans')) {
                Schema::create('pengaturan_midtrans', function ($table) {
                    $table->increments('id');
                    $table->boolean('is_active')->default(false);
                    $table->boolean('is_production')->default(false);
                    $table->string('merchant_id', 100)->nullable();
                    $table->string('client_key', 255)->nullable();
                    $table->string('server_key', 255)->nullable();
                    $table->boolean('enable_qris')->default(true);
                    $table->boolean('enable_va')->default(true);
                    $table->boolean('enable_gopay')->default(true);
                    $table->boolean('enable_credit_card')->default(false);
                    $table->boolean('enable_cstore')->default(false);
                    $table->integer('custom_expiry_duration')->default(60);
                    $table->text('keterangan')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }
}
