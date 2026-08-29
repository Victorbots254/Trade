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
            "image" => "https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?auto=format&fit=crop&q=80&w=1000",
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
        ],
        "candlestick-charts-beginners" => [
            "title" => "How to Read Candlestick Charts for Beginners",
            "slug" => "candlestick-charts-beginners",
            "category" => "Education",
            "read_time" => "9 min read",
            "image" => "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Candlestick patterns are the foundation of technical analysis. This guide explains the 5 most common patterns every trader needs to know.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">Technical analysis starts with understanding price action. Candlestick charts offer a highly visual representation of market psychology over a given timeframe. Whether you are trading 1-minute binary options or holding spot Bitcoin for a year, reading the candles is a mandatory skill.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The Anatomy of a Candle</h3>
                    <p>Every candlestick has a <strong>Body</strong> and <strong>Wicks (Shadows)</strong>.</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li><strong class=\"text-emerald-400\">Green (Bullish) Candle:</strong> The bottom of the body is the Open price, and the top is the Close price. The wicks show the highest and lowest prices reached during that timeframe.</li>
                        <li><strong class=\"text-rose-400\">Red (Bearish) Candle:</strong> The top of the body is the Open price, and the bottom is the Close price.</li>
                    </ul>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The 3 Most Powerful Patterns</h3>
                    
                    <div class=\"bg-slate-800 p-6 rounded-xl border border-slate-700 my-6 space-y-4\">
                        <div>
                            <h4 class=\"text-lg font-bold text-amber-400\">1. The Hammer (Bullish Reversal)</h4>
                            <p class=\"text-slate-300 text-sm mt-1\">Found at the bottom of a downtrend. It has a small body and a long lower wick. It signals that sellers pushed the price down, but buyers overwhelmed them and drove the price back up.</p>
                        </div>
                        <div class=\"border-t border-slate-700 pt-4\">
                            <h4 class=\"text-lg font-bold text-amber-400\">2. Engulfing Patterns</h4>
                            <p class=\"text-slate-300 text-sm mt-1\">A small candle is immediately followed by a massive candle of the opposite color that completely 'engulfs' the previous body. A green engulfing candle indicates massive buying momentum.</p>
                        </div>
                        <div class=\"border-t border-slate-700 pt-4\">
                            <h4 class=\"text-lg font-bold text-amber-400\">3. The Doji (Indecision)</h4>
                            <p class=\"text-slate-300 text-sm mt-1\">A cross-like candle where the open and close prices are nearly identical. It shows that buyers and sellers are deadlocked. If a Doji appears after a long trend, a reversal is highly probable.</p>
                        </div>
                    </div>
                </div>
            "
        ],
        "usdt-bep20-explained" => [
            "title" => "USDT BEP20 Explained: Why We Chose Binance Smart Chain",
            "slug" => "usdt-bep20-explained",
            "category" => "Platform",
            "read_time" => "6 min read",
            "image" => "https://images.unsplash.com/photo-1621504450181-5d156f0bb225?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "BEP20 USDT offers near-instant finality, sub-cent fees, and universal wallet support. Here is why it is the ideal deposit currency for TradePro.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">At TradePro, our primary base currency is Tether (USDT). However, USDT exists on dozens of different blockchains (Ethereum ERC20, Tron TRC20, Solana, etc.). We exclusively built our infrastructure around <strong>Binance Smart Chain (BEP20)</strong>. Here is why.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The Problem with Ethereum (ERC20)</h3>
                    <p>While Ethereum is the most decentralized smart contract network, it suffers from severe congestion. During a bull market, transferring USDT over ERC20 can cost upwards of $20 to $50 in gas fees, and take up to 15 minutes to confirm. For a high-frequency trading platform, this friction is unacceptable.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The BEP20 Advantage</h3>
                    <p>Binance Smart Chain utilizes a Proof-of-Staked-Authority (PoSA) consensus model. This allows it to achieve remarkable efficiency metrics:</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li><strong class=\"text-emerald-400\">Near-Instant Finality:</strong> Blocks are minted every 3 seconds. Your deposit is confirmed and credited to your trading account almost instantly.</li>
                        <li><strong class=\"text-emerald-400\">Micro-Fees:</strong> Transferring BEP20 USDT typically costs less than $0.05 in BNB gas fees, regardless of the transaction size.</li>
                        <li><strong class=\"text-emerald-400\">Ecosystem Integration:</strong> It is natively supported by Binance, Trust Wallet, MetaMask, and hardware wallets like Ledger.</li>
                    </ul>

                    <p class=\"mt-8\">When you deposit to your TradePro wallet, ensure your sending exchange or wallet has selected the <strong>BSC / BEP20</strong> network. Sending funds via the wrong network will result in permanent loss.</p>
                </div>
            "
        ],
        "cold-storage-security" => [
            "title" => "How TradePro Secures Your Funds: Cold Storage Architecture",
            "slug" => "cold-storage-security",
            "category" => "Security",
            "read_time" => "7 min read",
            "image" => "https://images.unsplash.com/photo-1639762681485-074b7f4ec651?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "We store 98% of user funds in offline cold wallets with multi-signature signing requirements. Here is exactly how your money is protected.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">In the cryptocurrency industry, security is not a feature; it is the entire foundation of trust. TradePro utilizes institutional-grade security architectures to ensure that client assets are immune to hot-wallet breaches, server compromises, and physical attacks.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The 98 / 2 Distribution Rule</h3>
                    <p>TradePro keeps a maximum of <strong>2%</strong> of total platform assets in internet-connected \"Hot Wallets\". These hot wallets are used solely to facilitate automated daily withdrawals and immediate liquidity for users.</p>
                    <p>The remaining <strong>98%</strong> of assets are swept daily into our Air-Gapped Cold Storage system. These wallets reside on specialized hardware that has never, and will never, touch the internet.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Multi-Signature (Multi-Sig) Authentication</h3>
                    <p>Our cold storage addresses are governed by a 3-of-5 Multi-Sig smart contract. This means that to move funds out of cold storage, 3 out of 5 highly secured cryptographic keys must sign the transaction simultaneously.</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li>Keys are distributed across geographically separated secure vaults.</li>
                        <li>No single executive, developer, or hacker can unilaterally access the funds.</li>
                        <li>Physical access to the keys requires biometric authentication and armed security clearance.</li>
                    </ul>

                    <p class=\"mt-6\">Your peace of mind is our priority. Trade confidently knowing your capital is guarded by enterprise-grade cryptographic fortresses.</p>
                </div>
            "
        ],
        "binary-vs-spot" => [
            "title" => "Binary vs Spot: Which Trading Style Suits You?",
            "slug" => "binary-vs-spot",
            "category" => "Education",
            "read_time" => "10 min read",
            "image" => "https://images.unsplash.com/photo-1621416953228-868f08c3475c?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Both have merit — but they suit very different trading styles and risk tolerances. We break down the differences with real examples.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">TradePro offers two distinct avenues for market speculation: traditional Spot Trading and fast-paced Binary Options. Understanding the mechanical and psychological differences between them is crucial to finding your edge as a trader.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Spot Trading: The Investor's Approach</h3>
                    <p>Spot trading involves taking direct ownership of the asset. If Bitcoin is $60,000 and you buy 1 BTC, your portfolio value fluctuates exactly 1:1 with Bitcoin's price.</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li><strong class=\"text-emerald-400\">Pros:</strong> Infinite time horizon. You can never be liquidated or \"expire\" out of a spot trade. It is the safest way to ride long-term macroeconomic trends.</li>
                        <li><strong class=\"text-rose-400\">Cons:</strong> Capital intensive. To make $6,000 profit, you need Bitcoin to move 10%, which might take months.</li>
                    </ul>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Binary Options: The Day Trader's Scalp</h3>
                    <p>Binary options abstract away the asset ownership. You are simply betting on the <strong>direction</strong> of the price over a fixed timeframe (e.g. 60 seconds).</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li><strong class=\"text-emerald-400\">Pros:</strong> Massive ROI velocity. You can earn an 88% payout even if the price of Bitcoin only moves by $1 in your direction. It thrives in stagnant, choppy markets.</li>
                        <li><strong class=\"text-rose-400\">Cons:</strong> Binary outcomes. If you are wrong by a single tick at expiration, the stake is lost. It requires extreme discipline and precision timing.</li>
                    </ul>

                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The Verdict</h3>
                    <p>Successful traders use both. They use <strong>Spot Trading</strong> to securely accumulate wealth over years, and they use a small, dedicated allocation of risk capital for <strong>Binary Options</strong> to generate immediate daily cash flow.</p>
                </div>
            "
        ],
        "global-infrastructure" => [
            "title" => "TradePro Now Serves Traders in 140+ Countries",
            "slug" => "global-infrastructure",
            "category" => "Announcement",
            "read_time" => "4 min read",
            "image" => "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "We have completed our global infrastructure expansion. No KYC barriers, no geographic restrictions. Open to all serious traders.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">We are thrilled to announce that TradePro has successfully deployed decentralized matching engine nodes across 14 global server regions, reducing trade execution latency to sub-10 milliseconds for users across 140+ countries.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Decentralized Access</h3>
                    <p>Our philosophy is simple: financial tools should be globally accessible. By utilizing non-custodial decentralized bridging protocols and Web3 architecture, we have eliminated traditional geographic boundaries.</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li>No discriminatory regional IP bans.</li>
                        <li>No restrictive fiat banking requirements—fund entirely via USDT BEP20.</li>
                        <li>Privacy-first architecture.</li>
                    </ul>
                    
                    <p class=\"mt-6\">Whether you are trading from Tokyo, London, or São Paulo, TradePro guarantees institutional liquidity, fixed-payout binary contracts, and 24/7 uptime.</p>
                </div>
            "
        ],
        "redis-matching-engine" => [
            "title" => "Under the Hood: Our Redis-Powered Order Matching Engine",
            "slug" => "redis-matching-engine",
            "category" => "Tech",
            "read_time" => "11 min read",
            "image" => "https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&q=80&w=1000",
            "excerpt" => "Sub-millisecond matching is not a marketing claim — it is a direct result of our atomic Lua scripts running on Redis sorted sets.",
            "content" => "
                <div class=\"prose prose-invert max-w-none space-y-6\">
                    <p class=\"text-lg leading-relaxed text-slate-300\">When volatility strikes and Bitcoin dumps $2,000 in a minute, traditional database-backed exchanges freeze. Order books lock up, liquidations fail to process, and users are left with API timeouts. At TradePro, we solved this by moving our entire matching engine into in-memory architecture.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">Why Relational Databases Fail at Scale</h3>
                    <p>MySQL and PostgreSQL are brilliant for storing persistent ledger data, but they are absolutely terrible for live order book matching. Reading from a disk, acquiring row-level locks, and calculating spread crosses across millions of active orders causes a bottleneck.</p>
                    
                    <h3 class=\"text-xl font-bold text-emerald-400 mt-8 mb-4\">The Redis Sorted Set Solution</h3>
                    <p>Instead of SQL, our live order book exists entirely in RAM using Redis <code>ZSET</code> (Sorted Sets). When a limit order is placed, it is inserted into the set where the score is the price.</p>
                    <ul class=\"list-disc pl-5 space-y-2 text-slate-300\">
                        <li><strong class=\"text-emerald-400\">O(log(N)) Time Complexity:</strong> Redis can find the highest Bid or lowest Ask in microseconds, regardless of how large the order book gets.</li>
                        <li><strong class=\"text-emerald-400\">Atomic Lua Scripting:</strong> When a Market Order hits the book, a Lua script atomically crosses the spread, deducts balances, and writes the execution log without any race conditions.</li>
                        <li><strong class=\"text-emerald-400\">Asynchronous Persistence:</strong> The Redis engine matches the trade instantly and returns the success to the user. In the background, a queue worker safely persists the finalized ledger state to our primary SQL database.</li>
                    </ul>

                    <p class=\"mt-6\">This hybrid architecture guarantees that you will never miss a fill due to system lag. Your trades execute at the exact speed of your network connection.</p>
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
