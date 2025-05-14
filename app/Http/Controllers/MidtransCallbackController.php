<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // Log awal untuk debugging
        Log::info('Midtrans Callback Payload:', $request->all());

        // Konfigurasi Midtrans
        $serverKey = config('midtrans.server_key');
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        // Verifikasi Signature
        $hashed = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($hashed !== $signatureKey) {
            Log::warning('Signature tidak valid untuk order_id: ' . $orderId);
            return response()->json(['message' => 'Invalid signature'], 403);
        }
        
        // Ambil status transaksi
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');
        
        // Cari data payment
        $payment = Payment::with('order')->where('midtrans_order_id', $orderId)->first();
        
        if (!$payment) {
            Log::error('Payment tidak ditemukan untuk order_id: ' . $orderId);
            return response()->json(['message' => 'Payment not found'], 404);
        }
        
        // Konversi status Midtrans ke internal
        $status = $this->getTransactionStatus($transactionStatus);
        
        // Update payment
        $payment->update([
            'transaction_status' => $status,
            'payment_type' => $request->input('payment_type'),
            'transaction_id' => $request->input('transaction_id'),
            'fraud_status' => $fraudStatus,
            'payload' => json_encode($request->all()),
        ]);

        
        // Update order jika ada
        if ($payment->order) {
            $payment->order->status = $status;
            $payment->order->save();
        }
        
        // Kurangi stok jika pembayaran sukses
        if ($status === 'success') {
            $this->reduceProductStock($payment->order);
        }

        Log::info('Midtrans callback berhasil untuk order_id: ' . $orderId);
        return response()->json(['message' => 'Callback handled'], 200);
    }

    private function getTransactionStatus($transactionStatus)
    {
        switch ($transactionStatus) {
            case 'capture':
                return 'success';
            case 'settlement':
                return 'success';
            case 'pending':
                return 'pending';
            case 'expire':
                return 'failed';
            case 'cancel':
                return 'failed';
            case 'deny':
                return 'failed';
            default:
                return 'failed';
        }
    }

    private function reduceProductStock($order)
    {
        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product;
            if ($product) {
                $product->stock -= $orderItem->quantity;
                $product->save();
            }
        }
    }
}
