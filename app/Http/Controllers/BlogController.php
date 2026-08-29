<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    private $articles = [
        "how-to-trade-quick-options" => [
            "title" => "The Ultimate Guide: Mastering Quick Options & Binary Trading",
            "slug" => "how-to-trade-quick-options",
            "category" => "Advanced Trading",
            "read_time" => "12 min read",
            "image" => "https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Discover the fastest way to compound your portfolio. Learn advanced risk management, candlestick pattern recognition, and exactly how to profit up to 90% in 60 seconds.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">Quick Options (often called Binary Options) are one of the most exhilarating and potentially lucrative financial instruments available on the modern cryptocurrency market. Unlike traditional spot trading where you must wait days or weeks for an asset to appreciate, Quick Options allow you to capture substantial gains in a matter of seconds based purely on market direction.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The Fundamental Mechanics</h3>
                    <p>At its core, a Quick Option contract requires you to make a single, binary prediction: <strong>Will the price of an underlying asset be higher or lower than the current price after a strictly defined expiration period?</strong></p>
                    
                    <div class=\"bg-slate-800 p-6 rounded-xl border border-slate-700 my-6\">
                        <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                            <li><strong class=\"text-emerald-400\">CALL (Up):</strong> You predict the price will be strictly greater than the entry price at expiration.</li>
                            <li><strong class=\"text-rose-400\">PUT (Down):</strong> You predict the price will be strictly less than the entry price at expiration.</li>
                        </ul>
                    </div>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Step-by-Step: Executing a Trade</h3>
                    <ol class=\"list-decimal pl-6 space-y-4 text-slate-300\">
                        <li><strong>Select your Asset and Timeframe:</strong> Highly liquid markets like BTC/USDT and ETH/USDT are ideal because they offer rapid volatility. Select a timeframe that matches your analysis (e.g., 60 seconds, 3 minutes, 5 minutes).</li>
                        <li><strong>Analyze the Chart:</strong> Switch your chart to the 1-minute interval. Look for clear support and resistance levels. A common strategy is to wait for a candlestick to touch a known resistance band and immediately purchase a <strong>PUT</strong> option in anticipation of a bounce.</li>
                        <li><strong>Allocate Capital:</strong> Decide your stake. Professional traders strictly abide by the <em>2% Rule</em> - never risk more than 2% of your total account balance on a single 60-second trade.</li>
                        <li><strong>Enter the Market:</strong> Click the respective execution button. The moment your order is filled, a strike line is painted on your chart. This is the exact price you must beat.</li>
                    </ol>

                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Risk Management & Psychology</h3>
                    <p>The speed of Quick Options is a double-edged sword. While it is entirely possible to double your portfolio in an hour, it is equally possible to lose it if you trade emotionally. Always wait for high-probability setups. If you lose a trade, <strong>do not revenge trade</strong> by instantly doubling your next stake. Accept the loss, re-evaluate the chart, and stick to your strict sizing metrics.</p>
                </div>
            "
        ],
        "passive-income-monthly-interests" => [
            "title" => "Wealth Generation: Automating Passive Income with Monthly Yields",
            "slug" => "passive-income-monthly-interests",
            "category" => "Wealth Management",
            "read_time" => "8 min read",
            "image" => "https://images.unsplash.com/photo-1621416894569-0f39ed31d247?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Tired of market volatility? Learn how institutional traders secure guaranteed 5% monthly returns using stablecoin smart liquidity pools.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">One of the most overlooked aspects of building a cryptocurrency portfolio is efficient capital allocation. When the market is chopping sideways or entering a prolonged bear cycle, active trading can lead to unnecessary losses. This is where <strong>Monthly Interest Pools (MMF)</strong> become your greatest asset.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The Problem with Idle Cash</h3>
                    <p>Holding USDT in your spot wallet protects you from downside volatility, but it suffers from inflation and opportunity cost. Every day your capital sits idle is a day it is not compounding. Institutional investors never leave cash idle - they sweep it into overnight repo markets or liquidity pools to generate continuous baseline returns.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">How the Monthly Yield System Works</h3>
                    <p>By migrating your idle USDT into the <strong>Earn</strong> dashboard, you are effectively providing liquidity to our institutional market-making desk. Because we utilize market-neutral arbitrage strategies, we can guarantee a fixed 5% return on your capital every 30 days.</p>
                    
                    <div class=\"bg-amber-900/20 p-6 rounded-xl border border-amber-700/50 my-6\">
                        <h4 class=\"text-amber-400 font-bold mb-2\">The Power of Compounding</h4>
                        <p class=\"text-slate-300\">A 5% monthly yield might not sound astronomical, but the mathematics of compounding are staggering. If you lock 10,000 USDT and reinvest the principal + interest every 30 days:</p>
                        <ul class=\"list-disc pl-5 mt-2 space-y-1 text-slate-300\">
                            <li>Month 1: 10,500 USDT</li>
                            <li>Month 6: 13,400 USDT</li>
                            <li>Month 12: 17,958 USDT (An 79.5% APY)</li>
                        </ul>
                    </div>

                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Security & Mechanics</h3>
                    <p>When you initiate a lockup, the smart ledger instantly deducts the funds from your Available Balance and moves them to your Locked Balance. A countdown timer strictly governs the maturity date. You cannot access these funds during the 30-day period. Exactly at maturity, an automated settlement script unlocks your capital and credits your new profit, allowing you to instantly withdraw it or re-stake it.</p>
                </div>
            "
        ],
        "spot-trading-guide" => [
            "title" => "Mastering Spot Trading: Order Types, Liquidity, and Execution",
            "slug" => "spot-trading-guide",
            "category" => "Market Basics",
            "read_time" => "15 min read",
            "image" => "https://images.unsplash.com/photo-1642104704074-907c0698cbd9?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "A comprehensive deep dive into operating a professional cryptocurrency exchange terminal. Master the order book and execute trades with precision.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">Spot Trading is the foundation of the cryptocurrency market. When you buy on the spot market, you are directly purchasing and taking delivery of the underlying asset. If you buy Bitcoin, you own the actual cryptographic keys to that Bitcoin, enabling you to hold it in your portfolio indefinitely, transfer it, or sell it back to fiat/stablecoins when it appreciates.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Understanding the Order Book</h3>
                    <p>The beating heart of our spot exchange is the Order Book. It is a live ledger of all outstanding buyer and seller intentions.</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li><strong class=\"text-rose-400\">The Asks (Red):</strong> Located at the top of the book, these are sellers offering their assets at specific prices.</li>
                        <li><strong class=\"text-emerald-400\">The Bids (Green):</strong> Located at the bottom, these are buyers willing to purchase at specific prices.</li>
                        <li><strong class=\"text-amber-400\">The Spread:</strong> The gap between the highest bid and lowest ask. A tight spread indicates a highly liquid, healthy market.</li>
                    </ul>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Market Orders vs. Limit Orders</h3>
                    
                    <div class=\"grid md:grid-cols-2 gap-6 my-6\">
                        <div class=\"bg-slate-800 p-5 rounded-lg border border-slate-700\">
                            <h4 class=\"text-lg font-bold text-white mb-2\">Market Order</h4>
                            <p class=\"text-sm text-slate-300\">Executes immediately at the best available current price. Ideal for when you need to enter or exit a position instantly, regardless of minor price slippage.</p>
                        </div>
                        <div class=\"bg-slate-800 p-5 rounded-lg border border-slate-700\">
                            <h4 class=\"text-lg font-bold text-white mb-2\">Limit Order</h4>
                            <p class=\"text-sm text-slate-300\">You specify the exact maximum price you are willing to pay (or minimum you are willing to sell for). The order is placed into the order book and waits passively until the market hits your target.</p>
                        </div>
                    </div>

                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Closing Positions & Realizing Profit</h3>
                    <p>Once you hold an asset, it will appear in your <strong>Holdings & Positions</strong> tab. Our platform calculates your live Unrealized P&L (Profit and Loss) against the live market oracle. When you are ready to secure your gains, simply click the <strong>Close Position</strong> button. Our system will instantly execute a Market Sell order on your behalf, sweeping the asset from your ledger and crediting the equivalent USDT directly to your Available Balance.</p>
                </div>
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
