<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TradingEmailService
{
    /**
     * Send clean, crypto-trading style Welcome Email upon registration.
     */
    public static function sendWelcomeEmail(User $user, ?string $verifyUrl = null): void
    {
        try {
            if (!$user->email) return;

            $terminalUrl = url('/terminal');
            $verificationUrl = $verifyUrl ?: url('/profile');

            $html = self::buildEmailTemplate([
                'badge' => 'NEW TRADER',
                'badgeColor' => '#f0b90b',
                'title' => 'Welcome to TradeCo',
                'subtitle' => 'Your Professional Crypto Trading Account is Ready',
                'recipientName' => $user->name,
                'intro' => "Welcome to TradeCo! Your cryptocurrency trading account has been registered successfully. You now have instant access to spot markets, binary options, P2P trading, and our 18% monthly yield MMF vault.",
                'details' => [
                    'Trader Name' => $user->name,
                    'Account UID' => '#' . $user->id,
                    'Registered Email' => $user->email,
                    'Practice Demo Balance' => '$10,000.00 USDT (Ready)',
                    'Live Wallet Status' => 'Active & Ready for Deposit',
                    'Escrow Security' => 'BEP-20 Smart Contract Protected',
                ],
                'buttonText' => 'Start Trading on Terminal',
                'buttonUrl' => $terminalUrl,
                'secondaryButtonText' => 'Verify & Manage Profile',
                'secondaryButtonUrl' => $verificationUrl,
                'note' => '🛡️ <strong>TradeCo Security Advisory:</strong> TradeCo staff will never ask you for your password, withdrawal PINs, or private keys. Always ensure you are on the official TradeCo domain.',
            ]);

            Mail::html($html, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('🚀 Welcome to TradeCo — Your Crypto Trading Account is Ready!');
            });

            Log::info("Welcome email successfully sent to {$user->email}");
        } catch (\Throwable $e) {
            Log::warning("Welcome email sending failed for {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Send Email Verification notification.
     */
    public static function sendVerificationEmail(User $user, string $verifyUrl): void
    {
        try {
            if (!$user->email) return;

            $html = self::buildEmailTemplate([
                'badge' => 'SECURITY VERIFY',
                'badgeColor' => '#0ecb81',
                'title' => 'Verify Your Trader Account',
                'subtitle' => 'Confirm Email Address for Account Security',
                'recipientName' => $user->name,
                'intro' => "Please verify your email address to confirm your TradeCo trader account. Verifying your email protects your funds and enables automated withdrawals and P2P trading privileges.",
                'details' => [
                    'Trader Account' => $user->name,
                    'Account UID' => '#' . $user->id,
                    'Email to Verify' => $user->email,
                    'Status' => 'Pending Email Verification',
                    'Link Validity' => '24 Hours',
                ],
                'buttonText' => 'Verify My Email Address',
                'buttonUrl' => $verifyUrl,
                'note' => 'If you did not create an account on TradeCo, please disregard this email. Your email address will not be activated without clicking the link.',
            ]);

            Mail::html($html, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('🔒 Verify Your TradeCo Trader Account');
            });

            Log::info("Verification email sent to {$user->email}");
        } catch (\Throwable $e) {
            Log::warning("Verification email failed for {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Send clean Password Reset Email.
     */
    public static function sendPasswordResetEmail(User $user, string $resetUrl): void
    {
        try {
            if (!$user->email) return;

            $html = self::buildEmailTemplate([
                'badge' => 'PASSWORD RESET',
                'badgeColor' => '#f59e0b',
                'title' => 'Reset Your Password',
                'subtitle' => 'Secure Password Recovery Request',
                'recipientName' => $user->name,
                'intro' => "We received a request to reset the password for your TradeCo account. Click the button below to choose a new secure password. This link is valid for 60 minutes.",
                'details' => [
                    'Account Name' => $user->name,
                    'Account Email' => $user->email,
                    'Link Expiry' => '60 Minutes',
                    'Action' => 'Password Reset',
                ],
                'buttonText' => 'Reset Password Now',
                'buttonUrl' => $resetUrl,
                'note' => '🔒 <strong>Security Notice:</strong> If you did not request a password reset, no further action is required. Your password will remain unchanged. If you suspect unauthorized access, please change your credentials immediately.',
            ]);

            Mail::html($html, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('🔑 Reset Your TradeCo Password');
            });

            Log::info("Password reset email sent to {$user->email}");
        } catch (\Throwable $e) {
            Log::warning("Password reset email failed for {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Send Deposit Approved / Credited Email.
     */
    public static function sendDepositApprovedEmail(
        User $user,
        float $amount,
        string $currency = 'USDT',
        string $reference = '',
        string $method = 'M-Pesa'
    ): void {
        try {
            if (!$user->email) return;

            $formattedAmount = number_format($amount, 2);
            $terminalUrl = url('/terminal');

            $html = self::buildEmailTemplate([
                'badge' => 'FUNDS CREDITED',
                'badgeColor' => '#0ecb81',
                'title' => "Deposit Approved: +{$formattedAmount} {$currency}",
                'subtitle' => 'Live Balance Credited Successfully',
                'recipientName' => $user->name,
                'intro' => "Your deposit of <strong>{$formattedAmount} {$currency}</strong> has been verified and credited directly to your live TradeCo balance. You can now execute live spot trades, options contracts, or allocate to the 18% MMF yield vault.",
                'details' => [
                    'Amount Credited' => "+{$formattedAmount} {$currency}",
                    'Payment Channel' => strtoupper($method),
                    'Reference / TxID' => $reference ?: 'PROCESSED',
                    'Status' => 'Approved & Available',
                ],
                'buttonText' => 'Trade Spot & Options',
                'buttonUrl' => $terminalUrl,
                'secondaryButtonText' => 'Earn 18% Monthly Yield',
                'secondaryButtonUrl' => url('/monthly-interests'),
                'note' => 'Your funds are held securely in your TradeCo live wallet. You can withdraw your balance at any time.',
            ]);

            Mail::html($html, function ($message) use ($user, $formattedAmount, $currency) {
                $message->to($user->email, $user->name)
                    ->subject("✅ Deposit Confirmed: +{$formattedAmount} {$currency} Credited");
            });

            Log::info("Deposit approved email sent to {$user->email} for {$amount} {$currency}");
        } catch (\Throwable $e) {
            Log::warning("Deposit approved email failed for {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Send Withdrawal Notification Email.
     */
    public static function sendWithdrawalNotification(
        User $user,
        float $amount,
        string $currency,
        string $address,
        string $status = 'Processing'
    ): void {
        try {
            if (!$user->email) return;

            $formattedAmount = number_format($amount, 2);

            $html = self::buildEmailTemplate([
                'badge' => 'WITHDRAWAL',
                'badgeColor' => '#38bdf8',
                'title' => "Withdrawal Request: {$formattedAmount} {$currency}",
                'subtitle' => 'BEP-20 Blockchain Payout Processing',
                'recipientName' => $user->name,
                'intro' => "Your withdrawal request of <strong>{$formattedAmount} {$currency}</strong> has been registered and is being processed over the Binance Smart Chain (BEP-20) network.",
                'details' => [
                    'Withdrawal Amount' => "{$formattedAmount} {$currency}",
                    'Destination Address' => $address,
                    'Network' => 'BNB Smart Chain (BEP-20)',
                    'Status' => $status,
                ],
                'buttonText' => 'View Account Hub',
                'buttonUrl' => url('/profile'),
                'note' => 'Blockchain transactions typically settle within 5–15 minutes depending on network congestion.',
            ]);

            Mail::html($html, function ($message) use ($user, $formattedAmount, $currency) {
                $message->to($user->email, $user->name)
                    ->subject("💸 Withdrawal Update: {$formattedAmount} {$currency} {$status}");
            });

            Log::info("Withdrawal email sent to {$user->email}");
        } catch (\Throwable $e) {
            Log::warning("Withdrawal email failed for {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Master Responsive Trading Email Layout (Dark Mode, Binance/TradeCo Theme).
     */
    public static function buildEmailTemplate(array $params): string
    {
        $badge = $params['badge'] ?? 'NOTICE';
        $badgeColor = $params['badgeColor'] ?? '#f0b90b';
        $title = $params['title'] ?? 'TradeCo Notification';
        $subtitle = $params['subtitle'] ?? '';
        $recipientName = $params['recipientName'] ?? 'Trader';
        $intro = $params['intro'] ?? '';
        $details = $params['details'] ?? [];
        $buttonText = $params['buttonText'] ?? '';
        $buttonUrl = $params['buttonUrl'] ?? '';
        $secondaryButtonText = $params['secondaryButtonText'] ?? '';
        $secondaryButtonUrl = $params['secondaryButtonUrl'] ?? '';
        $note = $params['note'] ?? '';

        // Build details table rows
        $detailsRows = '';
        foreach ($details as $key => $val) {
            $detailsRows .= "
            <tr>
                <td style='padding: 10px 14px; color: #848e9c; font-size: 13px; border-bottom: 1px solid #2b3139;'>{$key}</td>
                <td style='padding: 10px 14px; color: #ffffff; font-size: 13px; font-weight: 700; text-align: right; border-bottom: 1px solid #2b3139; font-family: monospace;'>{$val}</td>
            </tr>";
        }

        $secondaryButtonHtml = '';
        if ($secondaryButtonText && $secondaryButtonUrl) {
            $secondaryButtonHtml = "
            <div style='margin-top: 10px;'>
                <a href='{$secondaryButtonUrl}' style='display: inline-block; color: #848e9c; font-size: 12px; font-weight: 600; text-decoration: underline;'>
                    {$secondaryButtonText} &rarr;
                </a>
            </div>";
        }

        $noteSection = '';
        if ($note) {
            $noteSection = "
            <div style='margin-top: 24px; padding: 14px 16px; background-color: rgba(240, 185, 11, 0.08); border-left: 3px solid #f0b90b; border-radius: 8px;'>
                <p style='margin: 0; color: #cbd5e1; font-size: 12px; line-height: 1.6;'>{$note}</p>
            </div>";
        }

        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
</head>
<body style='margin: 0; padding: 0; background-color: #0b0e11; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #eaecef;'>
    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color: #0b0e11; padding: 36px 12px;'>
        <tr>
            <td align='center'>
                <!-- Main Container -->
                <table role='presentation' width='100%' style='max-width: 540px; background-color: #181a20; border: 1px solid #2b3139; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 35px rgba(0,0,0,0.6);'>
                    
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
                                                    <span style='display: block; font-size: 10px; color: #848e9c; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;'>Crypto Exchange</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align='right'>
                                        <span style='background-color: {$badgeColor}22; color: {$badgeColor}; border: 1px solid {$badgeColor}55; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;'>
                                            {$badge}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 32px 28px;'>
                            <h1 style='margin: 0 0 6px 0; color: #ffffff; font-size: 20px; font-weight: 800; letter-spacing: -0.3px;'>{$title}</h1>
                            " . ($subtitle ? "<p style='margin: 0 0 18px 0; color: #848e9c; font-size: 13px; font-weight: 500;'>{$subtitle}</p>" : '') . "
                            
                            <p style='margin: 0 0 16px 0; color: #94a3b8; font-size: 14px;'>Hello <strong style='color: #ffffff;'>{$recipientName}</strong>,</p>
                            
                            <p style='margin: 0 0 22px 0; color: #cbd5e1; font-size: 14px; line-height: 1.6;'>{$intro}</p>

                            <!-- Structured Details Table -->
                            " . ($detailsRows ? "
                            <table width='100%' cellspacing='0' cellpadding='0' style='background-color: #0b0e11; border: 1px solid #2b3139; border-radius: 12px; margin-bottom: 26px; border-collapse: collapse; overflow: hidden;'>
                                {$detailsRows}
                            </table>" : '') . "

                            <!-- Call to Action Button -->
                            " . ($buttonText && $buttonUrl ? "
                            <div style='text-align: center; margin: 28px 0 10px 0;'>
                                <a href='{$buttonUrl}' style='display: inline-block; background-color: #0ecb81; color: #1e2329; font-size: 14px; font-weight: 800; text-decoration: none; padding: 14px 34px; border-radius: 12px; box-shadow: 0 4px 16px rgba(14,203,129,0.35); text-transform: uppercase; letter-spacing: 0.5px;'>
                                    {$buttonText} &rarr;
                                </a>
                                {$secondaryButtonHtml}
                            </div>" : '') . "

                            {$noteSection}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='padding: 22px 28px; background-color: #14161a; border-top: 1px solid #2b3139; text-align: center;'>
                            <p style='margin: 0; color: #64748b; font-size: 11px; line-height: 1.5;'>
                                This is an automated message from <strong>TradeCo Global Exchange</strong>.
                            </p>
                            <p style='margin: 6px 0 0 0; color: #64748b; font-size: 11px;'>
                                Support: <a href='mailto:support@tradeco.io' style='color: #f0b90b; text-decoration: none;'>support@tradeco.io</a> &bull; 
                                <a href='" . url('/privacy') . "' style='color: #848e9c; text-decoration: none;'>Privacy Policy</a> &bull; 
                                <a href='" . url('/terms') . "' style='color: #848e9c; text-decoration: none;'>Terms of Service</a>
                            </p>
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
