<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Wallet;
use App\Services\MegaPayService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MegaPayController extends Controller
{
    /**
     * Get M-Pesa configuration & live exchange rate.
     */
    public function getSettings()
    {
        $rate = MegaPayService::getExchangeRate();
        $minKes = (int) ceil(5.0 * $rate);

        return response()->json([
            'exchange_rate' => $rate,
            'min_kes' => $minKes,
            'min_usdt' => 5.0,
            'currency' => 'KES',
            'is_configured' => !empty(config('services.megapay.api_key')) && !empty(config('services.megapay.email')),
        ]);
    }

    /**
     * Initiate an M-Pesa STK push via MegaPay.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'amount_type' => ['nullable', 'string', 'in:kes,usdt'],
        ]);

        $rawPhone = (string) $request->input('phone', '');
        $formattedPhone = MegaPayService::formatPhoneNumber($rawPhone);

        // Valid Kenyan phone must be 12 digits: 254XXXXXXXXX (starts with 2547 or 2541)
        if (strlen($formattedPhone) !== 12 || (!str_starts_with($formattedPhone, '2547') && !str_starts_with($formattedPhone, '2541'))) {
            return response()->json([
                'message' => 'Please enter a valid Safaricom / M-Pesa phone number (e.g. 0712345678 or 254712345678). Number resolved to: ' . $formattedPhone,
            ], 422);
        }

        $exchangeRate = MegaPayService::getExchangeRate();
        $amountType = $request->input('amount_type', 'kes');

        if ($amountType === 'usdt') {
            $usdtAmount = round((float) $request->amount, 4);
            $kesAmount = round($usdtAmount * $exchangeRate, 2);
        } else {
            $kesAmount = round((float) $request->amount, 2);
            $usdtAmount = round($kesAmount / $exchangeRate, 4);
        }

        $minKes = (int) ceil(5.0 * $exchangeRate);
        if ($usdtAmount < 5.0 || $kesAmount < $minKes) {
            return response()->json([
                'message' => "Minimum deposit amount is $5.00 USDT (approx. {$minKes} KES).",
            ], 422);
        }

        $user = $request->user();
        $reference = 'MP' . time() . strtoupper(Str::random(5));

        // Call MegaPay STK Push using merchant's configured MegaPay account email
        $stkResult = MegaPayService::initiateStkPush(
            $formattedPhone,
            $kesAmount,
            $reference
        );

        if (!$stkResult['success']) {
            return response()->json([
                'message' => $stkResult['message'] ?? 'Could not initiate M-Pesa payment prompt. Please try again.',
                'error' => $stkResult,
            ], 400);
        }

        $txReqId = $stkResult['transaction_request_id'] ?? $reference;

        // Create pending Deposit record
        $deposit = Deposit::create([
            'user_id' => $user->id,
            'payment_method' => 'mpesa',
            'phone_number' => $stkResult['phone'] ?? $formattedPhone,
            'currency' => 'USDT',
            'amount' => $usdtAmount,
            'kes_amount' => $kesAmount,
            'exchange_rate' => $exchangeRate,
            'tx_hash' => $txReqId,
            'transaction_request_id' => $txReqId,
            'merchant_request_id' => $stkResult['merchant_request_id'] ?? null,
            'checkout_request_id' => $stkResult['checkout_request_id'] ?? null,
            'reference' => $reference,
            'status' => 'pending',
        ]);

        // Ensure user's USDT wallet exists
        Wallet::firstOrCreate(
            ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => false],
            ['available_balance' => 0.00, 'locked_balance' => 0.00]
        );

        return response()->json([
            'success' => true,
            'message' => $stkResult['message'] ?? 'STK Push sent to your phone! Please enter your M-Pesa PIN.',
            'deposit_id' => $deposit->id,
            'deposit' => $deposit,
            'transaction_request_id' => $txReqId,
            'reference' => $reference,
            'kes_amount' => $kesAmount,
            'usdt_amount' => $usdtAmount,
            'phone' => $stkResult['phone'] ?? $formattedPhone,
        ]);
    }

    /**
     * Check status of an M-Pesa deposit (used by frontend polling).
     */
    public function checkStatus(Request $request, Deposit $deposit)
    {
        $user = $request->user();

        // Authorize: user owns deposit or is admin
        if ($deposit->user_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized access to deposit status.');
        }

        // If already approved, return immediately
        if ($deposit->status === 'approved') {
            return response()->json([
                'status' => 'approved',
                'deposit' => $deposit,
                'message' => 'Payment approved! Your balance has been credited.',
            ]);
        }

        // If already rejected
        if ($deposit->status === 'rejected') {
            return response()->json([
                'status' => 'rejected',
                'deposit' => $deposit,
                'message' => $deposit->rejection_reason ?: 'M-Pesa payment failed or was cancelled.',
            ]);
        }

        // If pending and has transaction_request_id, query MegaPay status API
        if ($deposit->transaction_request_id) {
            $statusCheck = MegaPayService::checkTransactionStatus(
                $deposit->transaction_request_id
            );

            if (($statusCheck['status'] ?? null) === 'completed') {
                $receipt = $statusCheck['receipt'] ?? ('MPESA-' . time());
                $updatedDeposit = MegaPayService::processDepositSuccess($deposit, $receipt);

                return response()->json([
                    'status' => 'approved',
                    'deposit' => $updatedDeposit,
                    'message' => 'Payment received! Your USDT balance has been credited.',
                ]);
            }

            if (($statusCheck['status'] ?? null) === 'failed') {
                $reason = $statusCheck['reason'] ?? 'Payment was declined or cancelled.';
                $updatedDeposit = MegaPayService::processDepositFailed($deposit, $reason);

                return response()->json([
                    'status' => 'rejected',
                    'deposit' => $updatedDeposit,
                    'message' => $reason,
                ]);
            }
        }

        return response()->json([
            'status' => 'pending',
            'deposit' => $deposit,
            'message' => 'Waiting for M-Pesa payment confirmation...',
        ]);
    }

    /**
     * MegaPay Webhook Callback Handler.
     */
    public function webhook(Request $request)
    {
        $raw = $request->getContent();
        $payload = $request->all();

        Log::info('MegaPay Webhook Received', [
            'payload' => $payload,
            'raw' => $raw,
        ]);

        if (empty($payload)) {
            $json = json_decode($raw, true);
            if ($json) {
                $payload = $json;
            }
        }

        $responseCode = isset($payload['ResponseCode']) ? (int) $payload['ResponseCode'] : null;
        $txId = $payload['TransactionID'] ?? null;
        $receipt = $payload['TransactionReceipt'] ?? null;
        $reference = $payload['TransactionReference'] ?? null;
        $description = $payload['ResponseDescription'] ?? 'No description provided';
        $checkoutId = $payload['CheckoutRequestID'] ?? null;
        $merchantId = $payload['MerchantRequestID'] ?? null;

        if ($responseCode === null && empty($txId) && empty($reference)) {
            Log::warning('MegaPay Webhook invalid payload', ['data' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook payload'], 400);
        }

        // Find the deposit record
        $deposit = Deposit::where(function ($query) use ($txId, $reference) {
            if (!empty($txId)) {
                $query->where('transaction_request_id', $txId)->orWhere('tx_hash', $txId);
            }
            if (!empty($reference)) {
                $query->orWhere('reference', $reference)->orWhere('tx_hash', $reference);
            }
        })->first();

        if (!$deposit) {
            Log::warning('MegaPay Webhook: Corresponding deposit not found', [
                'tx_id' => $txId,
                'reference' => $reference,
            ]);

            return response()->json([
                'status' => 'received',
                'message' => 'Deposit record not found, logged for review',
            ], 200);
        }

        // Update tracking IDs
        $deposit->update(array_filter([
            'merchant_request_id' => $merchantId,
            'checkout_request_id' => $checkoutId,
        ]));

        if ($responseCode === 0) {
            // Payment success!
            $receiptNumber = $receipt ?: ($txId ?: ('MPESA-' . time()));
            MegaPayService::processDepositSuccess($deposit, $receiptNumber, $txId);

            return response()->json([
                'status' => 'success',
                'message' => 'Deposit approved and credited successfully',
            ], 200);
        } else {
            // Payment failed or cancelled by user
            $failureReason = "M-Pesa payment failed ({$responseCode}): {$description}";
            MegaPayService::processDepositFailed($deposit, $failureReason);

            return response()->json([
                'status' => 'received',
                'message' => 'Failed transaction logged',
            ], 200);
        }
    }
}
