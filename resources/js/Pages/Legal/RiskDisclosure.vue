<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans select-none">
    <!-- Header -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
      <a href="/" class="flex items-center space-x-2 font-bold text-lg text-emerald-400 tracking-wider">
        <span>TRADE<span class="text-slate-400 font-normal">PRO</span></span>
      </a>
      <a href="/" class="text-xs text-slate-400 hover:text-slate-200 transition">← Back to Home</a>
    </header>

    <!-- Content -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-6 md:p-12 space-y-8 text-xs text-slate-300 leading-relaxed">
      <div class="border-b border-slate-800 pb-6 space-y-2">
        <h1 class="text-2xl font-bold text-slate-100">Institutional Financial Risk Disclosure Statement</h1>
        <p class="text-slate-400">Effective Date: January 1, 2026 | Mandatory Review Required</p>
      </div>

      <div class="bg-amber-500/10 border border-amber-500/30 text-amber-300 p-4 rounded-xl text-xs leading-relaxed font-mono">
        ⚠️ <strong>IMPORTANT NOTICE:</strong> Trading spot digital assets, precious metals (Gold/Silver), and global equity instruments involves extreme financial risk. You should only trade funds you can afford to lose completely.
      </div>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-amber-400 uppercase tracking-wider">1. High Volatility & Price Slippage Risk</h2>
        <p>
          Spot crypto assets, commodities (Gold, Silver, Crude Oil), and equities experience rapid price fluctuations. High market volatility may cause order executions to slip from your expected limit price or fill partially during fast market movements.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-amber-400 uppercase tracking-wider">2. Non-Reversible Order Executions</h2>
        <p>
          Once an order is matched by the in-memory matching engine and committed to the double-entry accounting ledger, the trade execution is final, non-reversible, and cannot be cancelled or refunded under any circumstances.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-amber-400 uppercase tracking-wider">3. Blockchain Network & BEP-20 Custodial Risks</h2>
        <p>
          Transfers made over the BNB Smart Chain (BEP-20) network are subject to blockchain network congestion, gas fee fluctuations, and user address entry accuracy. Transferring funds to incorrect addresses or non-supported chain standards results in permanent loss of funds.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-amber-400 uppercase tracking-wider">4. Systemic Latency & Technology Dependencies</h2>
        <p>
          Electronic trading relies on complex internet infrastructure, public WebSockets streams, and database connectivity. Unforeseen network outages or latency spikes may impair your ability to place or cancel orders in real-time.
        </p>
      </section>

      <div class="border-t border-slate-800 pt-6 text-slate-500 text-[11px]">
        By proceeding to trade on TradePro, you explicitly accept all inherent financial and technological risks.
      </div>
    </main>
  </div>
</template>
