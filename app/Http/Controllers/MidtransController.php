<?php

namespace App\Http\Controllers;

use App\Models\PengaturanMidtrans;
use App\Models\TransaksiPembayaran;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Generate Midtrans Snap Token for Checkout
     * POST /midtrans/snap-token
     */
    public function createSnapToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'nullable|string|max:100',
            'gross_amount' => 'required|numeric|min:1000',
            'tipe_transaksi' => 'nullable|string|max:50',
            'referensi_id' => 'nullable|integer',
            'customer_name' => 'nullable|string|max:150',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'item_name' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $config = PengaturanMidtrans::getActiveConfig();
        if (!$config->is_active && ($validated['tipe_transaksi'] ?? '') !== 'uji_coba') {
            return response()->json([
                'success' => false,
                'message' => 'Layanan Payment Gateway Midtrans saat ini sedang dinonaktifkan oleh administrator paroki.',
            ], 400);
        }

        $result = $this->midtransService->createSnapTransaction($validated);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Webhook Notification Callback from Midtrans Server
     * POST /midtrans/callback
     * POST /api/midtrans/webhook
     */
    public function handleCallback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received: ', ['order_id' => $payload['order_id'] ?? 'unknown', 'status' => $payload['transaction_status'] ?? 'unknown']);

        $result = $this->midtransService->handleNotification($payload);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification processed successfully.',
            'data' => $result,
        ], 200);
    }

    /**
     * Check live status of an order
     * GET /midtrans/status/{orderId}
     */
    public function checkStatus(Request $request, string $orderId): JsonResponse
    {
        $localTrx = TransaksiPembayaran::where('order_id', $orderId)->first();
        $remote = $this->midtransService->checkStatus($orderId);

        return response()->json([
            'success' => true,
            'local_transaction' => $localTrx,
            'remote_status' => $remote,
        ]);
    }
}
