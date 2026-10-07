<?php

namespace App\Services;

use App\Events\DepositApprovedEvent;
use App\Models\Deposit;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MegaPayService
{
    /**
     * Format phone number to standard Safaricom / Kenyan format: 254XXXXXXXXX (12 digits, no plus)
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // 1. Strip all non-numeric characters (spaces, +, -, parentheses, etc.)
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // 2. If it starts with 2540 (e.g. 2540712345678), fix to 254712345678
        if (str_starts_with($clean, '2540')) {
            $clean = '254' . substr($clean, 4);
        }
        // 3. If it starts with 0 (e.g. 0712345678 or 0112345678), replace 0 with 254
        elseif (str_starts_with($clean, '0')) {
            $clean = '254' . substr($clean, 1);
        }
        // 4. If it starts with 7 or 1 (e.g. 712345678 or 112345678 - 9 digits), prepend 254
        elseif (str_starts_with($clean, '7') || str_starts_with($clean, '1')) {
            $clean = '254' . $clean;
        }

        // Return pure digits without any plus: 2547XXXXXXXX
        return $clean;
    }

    /**
     * Get the configured KES to USDT exchange rate (e.g. 130 KES = 1 USDT).
     */
    public static function getExchangeRate(): float
    {
        $rate = (float) config('services.megapay.exchange_rate', 130.0);
        return $rate > 0 ? $rate : 130.0;
    }

    /**
     * Convert KES amount to USDT.
     */
    public static function convertKesToUsdt(float $kesAmount): float
    {
        $rate = self::getExchangeRate();
        return round($kesAmount / $rate, 4);
    }

    /**
     * Convert USDT amount to KES.
     */
    public static function convertUsdtToKes(float $usdtAmount): float
    {
        $rate = self::getExchangeRate();
        return round($usdtAmount * $rate, 2);
    }

    /**
     * Initiate an M-Pesa STK Push via MegaPay API.
     */
    public static function initiateStkPush(string $phone, float $kesAmount, string $reference): array
    {
        $apiKey = config('services.megapay.api_key');
        $accountEmail = config('services.megapay.email');
        $baseUrl = rtrim(config('services.megapay.base_url', 'https://megapay.co.ke/backend/v1'), '/');

        if (empty($apiKey) || empty($accountEmail)) {
            Log::warning('MegaPay credentials missing in .env (MEGAPAY_API_KEY or MEGAPAY_EMAIL)');
            return [
                'success' => false,
                'message' => 'MegaPay API credentials are not configured. Please set MEGAPAY_API_KEY and MEGAPAY_EMAIL in .env.',
            ];
        }

        $formattedPhone = self::formatPhoneNumber($phone);
        $roundedAmount = (int) round($kesAmount);

        $payload = [
            'api_key' => $apiKey,
            'email' => $accountEmail, // Always use merchant's MegaPay login email
            'amount' => (string) $roundedAmount,
            'msisdn' => $formattedPhone,
            'reference' => (string) $reference,
        ];

        Log::info('MegaPay STK Push initiating', [
            'url' => "{$baseUrl}/initiatestk",
            'msisdn' => $formattedPhone,
            'amount' => $roundedAmount,
            'reference' => $reference,
            'email' => $accountEmail,
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(20)->post("{$baseUrl}/initiatestk", $payload);

            $data = $response->json();
            Log::info('MegaPay STK Push response', ['status' => $response->status(), 'data' => $data]);

            if (!$response->successful() && empty($data)) {
                return [
                    'success' => false,
                    'message' => 'MegaPay server connection failed. Status: ' . $response->status(),
                ];
            }

            // Real MegaPay success format:
            // {"ResultCode":"0","ResponseCode":"0","success":true,"message":"Please enter your MPESA PIN...","transaction_request_id":"PFXID..."}
            // Error format:
            // {"ResultCode":"102","errorMessage":"Email is not registered in MegaPay"}
            $resultCode = $data['ResultCode'] ?? $data['ResponseCode'] ?? null;
            $txReqId = $data['transaction_request_id'] ?? $data['TransactionID'] ?? null;
            $errorMessage = $data['errorMessage'] ?? $data['message'] ?? $data['ResponseDescription'] ?? null;

            $isSuccess = false;
            if ($resultCode === '0' || $resultCode === 0) {
                $isSuccess = true;
            } elseif (($data['success'] ?? false) === true || ($data['success'] ?? '') == '200') {
                $isSuccess = true;
            }

            if ($isSuccess && !empty($txReqId)) {
                return [
                    'success' => true,
                    'message' => $data['message'] ?? 'Please enter your MPESA PIN on your phone to complete payment.',
                    'transaction_request_id' => $txReqId,
                    'merchant_request_id' => $data['MerchantRequestID'] ?? null,
                    'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
                    'phone' => $formattedPhone,
                    'amount_kes' => $roundedAmount,
                    'raw' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $errorMessage ?: 'MegaPay rejected STK push request.',
                'raw' => $data,
            ];
        } catch (Exception $e) {
            Log::error('MegaPay STK Push exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => 'Error contacting MegaPay service: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Query transaction status from MegaPay API.
     */
    public static function checkTransactionStatus(string $transactionRequestId): array
    {
        $apiKey = config('services.megapay.api_key');
        $accountEmail = config('services.megapay.email');
        $baseUrl = rtrim(config('services.megapay.base_url', 'https://megapay.co.ke/backend/v1'), '/');

        if (empty($apiKey) || empty($accountEmail)) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'MegaPay credentials missing',
            ];
        }

        $payload = [
            'api_key' => $apiKey,
            'email' => $accountEmail,
            'transaction_request_id' => $transactionRequestId,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(15)->post("{$baseUrl}/transactionstatus", $payload);

            $data = $response->json();
            Log::info('MegaPay transaction status response', ['tx_id' => $transactionRequestId, 'data' => $data]);

            // Expected response:
            // { "ResultCode": "200", "ResultDesc": "Success...", "TransactionStatus": "Completed", "TransactionCode": "0", "TransactionReceipt": "..." }
            $status = $data['TransactionStatus'] ?? null;
            $code = $data['TransactionCode'] ?? null;
            $receipt = $data['TransactionReceipt'] ?? null;

            if (strtolower($status ?? '') === 'completed' || $code === '0' || $code === 0) {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'receipt' => $receipt,
                    'amount' => $data['TransactionAmount'] ?? null,
                    'data' => $data,
                ];
            }

            if (strtolower($status ?? '') === 'failed' || ($code !== null && $code !== '0' && $code !== 0)) {
                return [
                    'success' => false,
                    'status' => 'failed',
                    'reason' => $data['ResultDesc'] ?? 'Transaction was not completed.',
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'status' => 'pending',
                'message' => $data['ResultDesc'] ?? 'Transaction is still processing.',
                'data' => $data,
            ];
        } catch (Exception $e) {
            Log::error('MegaPay checkTransactionStatus error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'pending',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Atomically credit user's USDT wallet and mark deposit as approved upon successful payment.
     */
    public static function processDepositSuccess(Deposit $deposit, string $receipt, ?string $transactionId = null): Deposit
    {
        return DB::transaction(function () use ($deposit, $receipt, $transactionId) {
            $lockedDeposit = Deposit::where('id', $deposit->id)->lockForUpdate()->first();

            if ($lockedDeposit->status === 'approved') {
                return $lockedDeposit;
            }

            // Target wallet is USDT Live Wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $lockedDeposit->user_id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 0.00, 'locked_balance' => 0.00]
            );

            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            // Record double-entry credit
            LedgerService::recordDepositCredit($wallet, $lockedDeposit->amount, $lockedDeposit->id);

            $lockedDeposit->update([
                'status' => 'approved',
                'mpesa_receipt' => $receipt,
                'transaction_request_id' => $transactionId ?: $lockedDeposit->transaction_request_id,
                'approved_at' => now(),
            ]);

            // Broadcast real-time balance update to frontend (safely guarded)
            try {
                broadcast(new DepositApprovedEvent($lockedDeposit, $wallet));
            } catch (\Throwable $e) {
                Log::warning('Could not broadcast DepositApprovedEvent: ' . $e->getMessage());
            }

            Log::info("MegaPay deposit #{$lockedDeposit->id} approved and credited: {$lockedDeposit->amount} USDT (Receipt: {$receipt})");

            return $lockedDeposit;
        });
    }

    /**
     * Mark pending deposit as rejected upon failed M-Pesa transaction.
     */
    public static function processDepositFailed(Deposit $deposit, string $reason): Deposit
    {
        return DB::transaction(function () use ($deposit, $reason) {
            $lockedDeposit = Deposit::where('id', $deposit->id)->lockForUpdate()->first();

            if ($lockedDeposit->status === 'pending') {
                $lockedDeposit->update([
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                ]);

                Log::info("MegaPay deposit #{$lockedDeposit->id} marked rejected: {$reason}");
            }

            return $lockedDeposit;
        });
    }
}
