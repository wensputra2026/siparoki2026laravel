<?php

namespace App\Services;

use App\Models\Iuran;
use App\Models\PengaturanMidtrans;
use App\Models\TransaksiPembayaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected PengaturanMidtrans $config;

    public function __construct(?PengaturanMidtrans $config = null)
    {
        $this->config = $config ?? PengaturanMidtrans::getActiveConfig();
    }

    public function getConfig(): PengaturanMidtrans
    {
        return $this->config;
    }

    public function isProduction(): bool
    {
        return (bool) $this->config->is_production;
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function getSnapApiUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function getCoreApiUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Create Snap Transaction Token
     *
     * @param array $params [
     *   'order_id' => string,
     *   'gross_amount' => int|float,
     *   'tipe_transaksi' => string (iuran, donasi, kolekte, intensi, lapak, uji_coba),
     *   'referensi_id' => int|null,
     *   'customer_name' => string,
     *   'customer_email' => string|null,
     *   'customer_phone' => string|null,
     *   'item_name' => string|null,
     *   'keterangan' => string|null
     * ]
     */
    public function createSnapTransaction(array $params): array
    {
        TransaksiPembayaran::ensureTableExists();

        $serverKey = trim((string) $this->config->server_key);
        if (empty($serverKey)) {
            return [
                'success' => false,
                'message' => 'Server Key Midtrans belum dikonfigurasi di menu Pengaturan Gateway.',
            ];
        }

        $orderId = $params['order_id'] ?? ('SIPAROKI-' . strtoupper($params['tipe_transaksi'] ?? 'TRX') . '-' . time() . '-' . rand(100, 999));
        $grossAmount = (int) round((float) ($params['gross_amount'] ?? 10000));
        if ($grossAmount < 1000) {
            $grossAmount = 1000;
        }

        $customerName = trim($params['customer_name'] ?? 'Umat SIPAROKI');
        $customerEmail = trim($params['customer_email'] ?? 'umat@siparoki.id');
        $customerPhone = trim($params['customer_phone'] ?? '081234567890');
        $itemName = substr(trim($params['item_name'] ?? 'Pembayaran SIPAROKI'), 0, 50);

        // Filter active payment channels
        $enabledPayments = [];
        if ($this->config->enable_qris) {
            $enabledPayments[] = 'gopay';
            $enabledPayments[] = 'shopeepay';
            $enabledPayments[] = 'qris';
            $enabledPayments[] = 'other_qris';
        }
        if ($this->config->enable_va) {
            $enabledPayments[] = 'bca_va';
            $enabledPayments[] = 'bni_va';
            $enabledPayments[] = 'bri_va';
            $enabledPayments[] = 'echannel'; // Mandiri Bill
            $enabledPayments[] = 'permata_va';
            $enabledPayments[] = 'cimb_va';
            $enabledPayments[] = 'other_va';
        }
        if ($this->config->enable_credit_card) {
            $enabledPayments[] = 'credit_card';
        }
        if ($this->config->enable_cstore) {
            $enabledPayments[] = 'indomaret';
            $enabledPayments[] = 'alfamart';
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ],
            'item_details' => [
                [
                    'id' => $orderId,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => $itemName,
                ]
            ],
            'expiry' => [
                'unit' => 'minute',
                'duration' => max(15, (int) ($this->config->custom_expiry_duration ?: 60)),
            ],
        ];

        if (!empty($enabledPayments)) {
            $payload['enabled_payments'] = array_values(array_unique($enabledPayments));
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->timeout(20)->post($this->getSnapApiUrl(), $payload);

            $result = $response->json();

            if ($response->successful() && !empty($result['token'])) {
                // Record to database
                TransaksiPembayaran::updateOrCreate(
                    ['order_id' => $orderId],
                    [
                        'tipe_transaksi' => $params['tipe_transaksi'] ?? 'umum',
                        'referensi_id' => $params['referensi_id'] ?? null,
                        'nama_pembayar' => $customerName,
                        'email_pembayar' => $customerEmail,
                        'nomor_telepon' => $customerPhone,
                        'jumlah' => $grossAmount,
                        'gross_amount' => $grossAmount,
                        'snap_token' => $result['token'],
                        'snap_redirect_url' => $result['redirect_url'] ?? '',
                        'status_transaksi' => 'pending',
                        'transaction_time' => now(),
                        'raw_response' => $result,
                        'keterangan' => $params['keterangan'] ?? $itemName,
                    ]
                );

                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'snap_token' => $result['token'],
                    'redirect_url' => $result['redirect_url'] ?? '',
                    'client_key' => $this->config->client_key,
                    'is_production' => $this->isProduction(),
                    'snap_js_url' => $this->getSnapJsUrl(),
                ];
            }

            return [
                'success' => false,
                'message' => $result['error_messages'][0] ?? 'Gagal membuat Snap token Midtrans: ' . ($response->body() ?: 'Server Error'),
                'details' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap creation exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi saat memproses Midtrans Snap: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Webhook Callback Notification from Midtrans
     */
    public function handleNotification(array $payload): array
    {
        TransaksiPembayaran::ensureTableExists();

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? '';

        if (empty($orderId)) {
            return ['success' => false, 'message' => 'Order ID tidak ditemukan dalam payload.'];
        }

        // Verify SHA512 signature
        $serverKey = trim((string) $this->config->server_key);
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning("Midtrans Webhook: Invalid signature for Order ID {$orderId}");
            return ['success' => false, 'message' => 'Invalid signature key.'];
        }

        // Determine unified status
        $finalStatus = 'pending';
        if ($transactionStatus === 'capture') {
            $finalStatus = ($fraudStatus === 'accept') ? 'settlement' : 'pending';
        } elseif ($transactionStatus === 'settlement') {
            $finalStatus = 'settlement';
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'failure'])) {
            $finalStatus = $transactionStatus;
        }

        // Extract VA or payment channel details
        $vaNumber = null;
        $paymentChannel = $paymentType;

        if (!empty($payload['va_numbers'][0]['va_number'])) {
            $vaNumber = $payload['va_numbers'][0]['va_number'];
            $paymentChannel = ($payload['va_numbers'][0]['bank'] ?? 'bank') . '_va';
        } elseif (!empty($payload['permata_va_number'])) {
            $vaNumber = $payload['permata_va_number'];
            $paymentChannel = 'permata_va';
        } elseif (!empty($payload['bill_key'])) {
            $vaNumber = $payload['bill_key'];
            $paymentChannel = 'mandiri_bill';
        }

        // Update local transaction log
        $trx = TransaksiPembayaran::where('order_id', $orderId)->first();
        if ($trx) {
            $trx->update([
                'status_transaksi' => $finalStatus,
                'payment_type' => $paymentType,
                'payment_channel' => $paymentChannel,
                'va_number' => $vaNumber ?: $trx->va_number,
                'settlement_time' => ($finalStatus === 'settlement') ? now() : $trx->settlement_time,
                'raw_response' => $payload,
                'signature_key' => $signatureKey,
            ]);

            // Dispatch settlement to related business models
            if ($finalStatus === 'settlement') {
                $this->applySettlementToModule($trx);
            }
        }

        return [
            'success' => true,
            'order_id' => $orderId,
            'status' => $finalStatus,
        ];
    }

    /**
     * Update business model records upon successful payment
     */
    protected function applySettlementToModule(TransaksiPembayaran $trx): void
    {
        try {
            if ($trx->tipe_transaksi === 'iuran' && $trx->referensi_id) {
                Iuran::where('id', $trx->referensi_id)->update([
                    'status_bayar' => 'Lunas',
                    'tanggal_bayar' => now(),
                    'metode_pembayaran' => 'Midtrans (' . strtoupper($trx->payment_channel ?: $trx->payment_type ?: 'Snap') . ')',
                    'keterangan' => ($trx->keterangan ? $trx->keterangan . ' | ' : '') . "Lunas otomatis via Midtrans Snap (Order: {$trx->order_id})",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Failed to apply auto-settlement for Order ID {$trx->order_id}: " . $e->getMessage());
        }
    }

    /**
     * Check transaction status directly from Midtrans Core API
     */
    public function checkStatus(string $orderId): array
    {
        $serverKey = trim((string) $this->config->server_key);
        if (empty($serverKey)) {
            return ['success' => false, 'message' => 'Server Key Midtrans belum dikonfigurasi.'];
        }

        try {
            $url = rtrim($this->getCoreApiUrl(), '/') . '/' . urlencode($orderId) . '/status';
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->timeout(15)->get($url);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'data' => $result,
                    'status' => $result['transaction_status'] ?? 'unknown',
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal mengambil status dari Midtrans: ' . ($response->json()['status_message'] ?? $response->body()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke Midtrans gagal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test API connection with provided credentials
     */
    public function testConnection(?string $serverKey = null, ?bool $isProduction = null): array
    {
        $key = $serverKey !== null ? trim($serverKey) : trim((string) $this->config->server_key);
        $prod = $isProduction !== null ? $isProduction : $this->isProduction();

        if (empty($key)) {
            return [
                'success' => false,
                'message' => 'Server Key masih kosong.',
            ];
        }

        $url = $prod
            ? 'https://api.midtrans.com/v2/token'
            : 'https://api.sandbox.midtrans.com/v2/token';

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($key . ':'),
            ])->timeout(10)->get($url);

            // Midtrans responds with 400/404 on /v2/token for GET but validates authentication header
            $status = $response->status();
            if ($status !== 401 && $status !== 403) {
                return [
                    'success' => true,
                    'message' => 'Koneksi ke Server Midtrans ' . ($prod ? 'Production' : 'Sandbox') . ' BERHASIL! Kredensial valid.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Autentikasi gagal (HTTP ' . $status . '). Pastikan Server Key ' . ($prod ? 'Production' : 'Sandbox') . ' sudah benar.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server Midtrans: ' . $e->getMessage(),
            ];
        }
    }
}
