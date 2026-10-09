<?php

namespace App\Services;

use App\Models\P2POrder;
use App\Models\P2PMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class P2PNotificationService
{
    /**
     * Notify seller when a new P2P trade order is created.
     */
    public static function notifyOrderCreated(P2POrder $order): void
    {
        try {
            $seller = $order->seller;
            $buyer = $order->buyer;
            if (!$seller || !$seller->email) {
                return;
            }

            $orderUrl = url("/p2p/orders/{$order->id}");
            $fiatCurrency = $order->ad ? $order->ad->fiat : 'USDT';
            $formattedFiat = number_format((float) $order->fiat_amount, 2) . " {$fiatCurrency}";
            $formattedCrypto = number_format((float) $order->crypto_amount, 4) . " USDT";
            $subject = "⚡ New P2P Trade Order #{$order->order_number} Received";

            $html = self::buildEmailLayout(
                title: "New P2P Trade Order Initiated",
                badge: "Action Required",
                badgeColor: "#f0b90b",
                recipientName: $seller->name,
                introText: "A buyer has just initiated a trade for your P2P advertisement. Your escrowed funds are safely locked by TradeCo.",
                details: [
                    "Order Number" => $order->order_number,
                    "Buyer Name" => $buyer->name ?? 'Trader',
                    "Trade Amount" => "{$formattedFiat} ({$formattedCrypto})",
                    "Payment Method" => strtoupper($order->payment_method),
                    "Status" => "Waiting for Buyer Payment",
                ],
                buttonText: "Open Trade Room & Chat",
                buttonUrl: $orderUrl,
                note: "Please keep this tab open or check your notifications. Once the buyer sends payment, you will be notified to confirm and release the crypto."
            );

            Mail::html($html, function ($msg) use ($seller, $subject) {
                $msg->to($seller->email, $seller->name)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning("P2P notifyOrderCreated email error: " . $e->getMessage());
        }
    }

    /**
     * Notify seller when buyer marks payment as sent.
     */
    public static function notifyPaymentSent(P2POrder $order): void
    {
        try {
            $seller = $order->seller;
            $buyer = $order->buyer;
            if (!$seller || !$seller->email) {
                return;
            }

            $orderUrl = url("/p2p/orders/{$order->id}");
            $fiatCurrency = $order->ad ? $order->ad->fiat : 'USDT';
            $formattedFiat = number_format((float) $order->fiat_amount, 2) . " {$fiatCurrency}";
            $subject = "💸 Payment Sent for P2P Order #{$order->order_number}";

            $html = self::buildEmailLayout(
                title: "Buyer Marked Payment Sent",
                badge: "Release Crypto",
                badgeColor: "#0ecb81",
                recipientName: $seller->name,
                introText: "The buyer ({$buyer->name}) has confirmed sending payment of {$formattedFiat} for your P2P trade.",
                details: [
                    "Order Number" => $order->order_number,
                    "Buyer Name" => $buyer->name ?? 'Trader',
                    "Amount Sent" => $formattedFiat,
                    "Crypto to Release" => number_format((float) $order->crypto_amount, 4) . " USDT",
                    "Status" => "Pending Your Confirmation",
                ],
                buttonText: "Verify Payment & Release USDT",
                buttonUrl: $orderUrl,
                note: "IMPORTANT: Always verify funds directly inside your mobile banking or M-Pesa app before clicking Release. Escrow releases are irreversible."
            );

            Mail::html($html, function ($msg) use ($seller, $subject) {
                $msg->to($seller->email, $seller->name)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning("P2P notifyPaymentSent email error: " . $e->getMessage());
        }
    }

    /**
     * Notify the other party when a new chat message arrives.
     */
    public static function notifyNewMessage(P2POrder $order, P2PMessage $message, User $sender): void
    {
        try {
            // Determine recipient: the other participant in the trade
            $recipient = ($order->buyer_id === $sender->id) ? $order->seller : $order->buyer;
            if (!$recipient || !$recipient->email) {
                return;
            }

            $orderUrl = url("/p2p/orders/{$order->id}");
            $subject = "💬 New Message on Trade #{$order->order_number} from {$sender->name}";
            $messagePreview = $message->message ? '"' . mb_substr($message->message, 0, 150) . '"' : '[Receipt attachment uploaded]';

            $html = self::buildEmailLayout(
                title: "New Message in Trade Chat",
                badge: "Live Chat",
                badgeColor: "#38bdf8",
                recipientName: $recipient->name,
                introText: "{$sender->name} just sent a message regarding your active P2P trade order #{$order->order_number}:",
                details: [
                    "Order Number" => $order->order_number,
                    "Sender" => $sender->name,
                    "Message Preview" => $messagePreview,
                ],
                buttonText: "Reply in Trade Room",
                buttonUrl: $orderUrl,
                note: "Respond promptly to ensure a smooth trade experience and avoid order timeouts."
            );

            Mail::html($html, function ($msg) use ($recipient, $subject) {
                $msg->to($recipient->email, $recipient->name)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning("P2P notifyNewMessage email error: " . $e->getMessage());
        }
    }

    /**
     * Notify buyer when crypto has been released to their wallet.
     */
    public static function notifyCryptoReleased(P2POrder $order): void
    {
        try {
            $buyer = $order->buyer;
            if (!$buyer || !$buyer->email) {
                return;
            }

            $orderUrl = url("/p2p/orders/{$order->id}");
            $escrowFee = (float) ($order->escrow_fee ?? 0);
            $netCrypto = max(0, round($order->crypto_amount - $escrowFee, 4));
            $subject = "🎉 Crypto Released for P2P Order #{$order->order_number}";

            $html = self::buildEmailLayout(
                title: "Trade Completed - Crypto Released",
                badge: "Completed",
                badgeColor: "#0ecb81",
                recipientName: $buyer->name,
                introText: "The seller has confirmed your payment and released the crypto. Your funds are now available in your TradeCo wallet!",
                details: [
                    "Order Number" => $order->order_number,
                    "Crypto Received" => number_format($netCrypto, 4) . " USDT",
                    "Escrow Fee" => $escrowFee > 0 ? number_format($escrowFee, 4) . " USDT" : "FREE ($0.00)",
                    "Status" => "Completed",
                ],
                buttonText: "View Wallet & Order",
                buttonUrl: $orderUrl,
                note: "Thank you for trading on TradeCo P2P Escrow!"
            );

            Mail::html($html, function ($msg) use ($buyer, $subject) {
                $msg->to($buyer->email, $buyer->name)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning("P2P notifyCryptoReleased email error: " . $e->getMessage());
        }
    }

    /**
     * Clean, responsive dark-mode HTML email template matching TradeCo branding.
     */
    private static function buildEmailLayout(
        string $title,
        string $badge,
        string $badgeColor,
        string $recipientName,
        string $introText,
        array $details,
        string $buttonText,
        string $buttonUrl,
        string $note = ''
    ): string {
        $detailsRows = '';
        foreach ($details as $label => $val) {
            $detailsRows .= "
            <tr>
                <td style='padding: 8px 12px; color: #848e9c; font-size: 13px; border-bottom: 1px solid #2b3139;'>{$label}</td>
                <td style='padding: 8px 12px; color: #ffffff; font-size: 13px; font-weight: bold; text-align: right; border-bottom: 1px solid #2b3139;'>{$val}</td>
            </tr>";
        }

        $noteSection = $note ? "
        <div style='margin-top: 20px; padding: 12px; background-color: rgba(240, 185, 11, 0.1); border-left: 3px solid #f0b90b; border-radius: 6px;'>
            <p style='margin: 0; color: #e2e8f0; font-size: 12px; line-height: 1.5;'>{$note}</p>
        </div>" : '';

        return "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
</head>
<body style='margin: 0; padding: 0; background-color: #0b0e11; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #eaecef;'>
    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color: #0b0e11; padding: 30px 10px;'>
        <tr>
            <td align='center'>
                <table role='presentation' width='100%' style='max-width: 560px; background-color: #181a20; border: 1px solid #2b3139; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);'>
                    <!-- Header -->
                    <tr>
                        <td style='padding: 24px 28px; background-color: #14161a; border-bottom: 1px solid #2b3139;'>
                            <table width='100%' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td>
                                        <table cellspacing='0' cellpadding='0'>
                                            <tr>
                                                <td style='width: 32px; height: 32px; background-color: #f0b90b; border-radius: 8px; text-align: center; vertical-align: middle; font-weight: 900; color: #1e2329; font-size: 16px; line-height: 32px;'>
                                                    T
                                                </td>
                                                <td style='padding-left: 10px;'>
                                                    <span style='font-size: 18px; font-weight: 900; color: #ffffff; letter-spacing: 0.5px;'>TRADE<span style='color: #f0b90b;'>CO</span></span>
                                                    <span style='display: block; font-size: 10px; color: #848e9c; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;'>P2P Escrow</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align='right'>
                                        <span style='background-color: {$badgeColor}22; color: {$badgeColor}; border: 1px solid {$badgeColor}55; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; text-transform: uppercase;'>{$badge}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 28px;'>
                            <h2 style='margin: 0 0 10px 0; color: #ffffff; font-size: 18px; font-weight: 700;'>{$title}</h2>
                            <p style='margin: 0 0 16px 0; color: #94a3b8; font-size: 14px; line-height: 1.5;'>Hello <strong style='color: #ffffff;'>{$recipientName}</strong>,</p>
                            <p style='margin: 0 0 20px 0; color: #cbd5e1; font-size: 14px; line-height: 1.5;'>{$introText}</p>

                            <!-- Details Table -->
                            <table width='100%' cellspacing='0' cellpadding='0' style='background-color: #0b0e11; border: 1px solid #2b3139; border-radius: 10px; margin-bottom: 24px; border-collapse: collapse;'>
                                {$detailsRows}
                            </table>

                            <!-- Call to Action Button -->
                            <div style='text-align: center; margin: 26px 0;'>
                                <a href='{$buttonUrl}' style='display: inline-block; background-color: #0ecb81; color: #1e2329; font-size: 14px; font-weight: 800; text-decoration: none; padding: 14px 32px; border-radius: 12px; box-shadow: 0 4px 15px rgba(14,203,129,0.3); text-transform: uppercase; letter-spacing: 0.5px;'>
                                    {$buttonText} &rarr;
                                </a>
                            </div>

                            {$noteSection}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='padding: 18px 28px; background-color: #14161a; border-top: 1px solid #2b3139; text-align: center;'>
                            <p style='margin: 0; color: #64748b; font-size: 11px;'>TradeCo Peer-to-Peer Escrow Protection. All escrow deposits are cryptographically held.</p>
                            <p style='margin: 6px 0 0 0; color: #64748b; font-size: 11px;'>Direct Order Link: <a href='{$buttonUrl}' style='color: #f0b90b; text-decoration: underline;'>{$buttonUrl}</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
    }
}
