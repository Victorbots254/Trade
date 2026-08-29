<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    private $articles = [
        "how-to-trade-quick-options" => [
            "title" => "How to Earn up to 90% Profit in 60 Seconds with Quick Options",
            "slug" => "how-to-trade-quick-options",
            "category" => "Trading Guide",
            "read_time" => "4 min read",
            "image" => "https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Learn the basics of Quick Options (Binary Trading) and how you can capitalize on short-term market volatility.",
            "content" => "
                <h3>What are Quick Options?</h3>
                <p>Quick Options allow you to predict whether the price of an asset (like Bitcoin) will go <strong>Up</strong> or <strong>Down</strong> within a very short time frame - usually 1 to 5 minutes.</p>
                <br>
                <h3>How to Place Your First Trade</h3>
                <ol>
                    <li><strong>Select your asset:</strong> Choose a highly liquid pair like BTC/USDT.</li>
                    <li><strong>Analyze the trend:</strong> Use the live Depth Chart and order book to see where the market pressure is leaning.</li>
                    <li><strong>Select your time limit:</strong> Choose 60 seconds for fast action.</li>
                    <li><strong>Predict:</strong> Click <strong>CALL (High)</strong> if you believe the price will rise, or <strong>PUT (Low)</strong> if you believe it will fall.</li>
                </ol>
                <br>
                <p>If your prediction is correct at the exact moment the timer expires, you instantly earn a fixed payout, often up to 90% of your initial stake. It is fast, thrilling, and highly rewarding.</p>
            "
        ],
        "passive-income-monthly-interests" => [
            "title" => "Generate Passive Income with Monthly Interests",
            "slug" => "passive-income-monthly-interests",
            "category" => "Wealth Management",
            "read_time" => "3 min read",
            "image" => "https://images.unsplash.com/photo-1621416894569-0f39ed31d247?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Do not let your USDT sit idle. Discover how locking your funds in our Monthly Interests pool can generate reliable passive yields.",
            "content" => "
                <h3>Why Leave Your Crypto Idle?</h3>
                <p>Many traders keep a large portion of their portfolio in stablecoins like USDT waiting for the perfect trading opportunity. Instead of letting those funds sit idle, you can put them to work.</p>
                <br>
                <h3>How the Monthly Interests System Works</h3>
                <p>Our platform offers a high-yield staking dashboard called <strong>Monthly Interests</strong>. Here is how you can use it:</p>
                <ul>
                    <li>Navigate to the <strong>Earn</strong> tab on the main navigation bar.</li>
                    <li>Enter the amount of USDT you wish to lock into the smart pool.</li>
                    <li>Your funds will be locked securely for exactly 30 days.</li>
                    <li>Upon maturity, your original capital plus a <strong>5% fixed monthly yield</strong> is automatically deposited back into your available live balance.</li>
                </ul>
                <br>
                <p>It is the safest and most reliable way to grow your portfolio automatically while you sleep.</p>
            "
        ],
        "spot-trading-guide" => [
            "title" => "The Beginners Guide to Spot Trading Crypto",
            "slug" => "spot-trading-guide",
            "category" => "Market Basics",
            "read_time" => "5 min read",
            "image" => "https://images.unsplash.com/photo-1642104704074-907c0698cbd9?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Master the fundamentals of buying and holding real cryptocurrencies on our Spot Trading Terminal.",
            "content" => "
                <h3>Spot Trading vs. Quick Options</h3>
                <p>Unlike Quick Options where you trade on time limits, <strong>Spot Trading</strong> means you are buying the actual underlying asset. If you buy 1 BTC, you own 1 BTC indefinitely until you decide to sell it.</p>
                <br>
                <h3>How to Execute a Spot Trade</h3>
                <p>Our spot terminal connects directly to global liquidity pools to give you the best prices instantly.</p>
                <ol>
                    <li>Head over to the <strong>Spot Trade</strong> terminal from the top menu.</li>
                    <li>Use the <strong>TradingView</strong> chart to perform technical analysis.</li>
                    <li>Enter the amount of USDT you wish to spend, or the amount of the asset you wish to buy.</li>
                    <li>Click <strong>Buy</strong>. The asset is instantly credited to your portfolio.</li>
                </ol>
                <br>
                <p>To close your position and take profit, simply navigate to your <strong>Past Trades & Orders</strong> page or use the Terminal to issue a Market Sell. Your asset will be swapped back to USDT immediately.</p>
            "
        ]
    ];

    public function index(Request $request)
    {
        return Inertia::render("Blog/Index", [
            "user" => $request->user(),
            "articles" => array_values($this->articles)
        ]);
    }

    public function show(Request $request, $slug)
    {
        if (!isset($this->articles[$slug])) {
            abort(404);
        }

        return Inertia::render("Blog/Show", [
            "user" => $request->user(),
            "article" => $this->articles[$slug],
            "moreArticles" => collect($this->articles)->except($slug)->take(2)->values()
        ]);
    }
}
