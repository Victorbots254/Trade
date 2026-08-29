<?php

namespace App\Jobs;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSupportDepositNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Deposit $deposit) {}

    public function handle(): void
    {
        $deposit = $this->deposit->load('user');
        $bscScanUrl = "https://bscscan.com/tx/{$deposit->tx_hash}";

        $message = "🚨 *NEW BEP-20 DEPOSIT PENDING APPROVAL*\n\n" .
                   "👤 *User:* {$deposit->user->name} ({$deposit->user->email})\n" .
                   "💰 *Amount:* {$deposit->amount} {$deposit->currency}\n" .
                   "🔗 *TxHash:* `{$deposit->tx_hash}`\n" .
                   "🌐 *BscScan Link:* {$bscScanUrl}\n\n" .
                   "Please review and approve via Admin Dashboard.";

        // Send Telegram Webhook if configured
        $telegramBotToken = config('services.telegram.bot_token');
        $telegramChatId   = config('services.telegram.chat_id');
        if ($telegramBotToken && $telegramChatId) {
            try {
                Http::post("https://api.telegram.org/bot{$telegramBotToken}/sendMessage", [
                    'chat_id' => $telegramChatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Exception $e) {
                Log::error("Telegram notification failed: " . $e->getMessage());
            }
        }

        // Send Discord Webhook if configured
        $discordWebhookUrl = config('services.discord.webhook_url');
        if ($discordWebhookUrl) {
            try {
                Http::post($discordWebhookUrl, [
                    'content' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error("Discord notification failed: " . $e->getMessage());
            }
        }
    }
}
