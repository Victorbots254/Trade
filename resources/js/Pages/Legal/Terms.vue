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
        <h1 class="text-2xl font-bold text-slate-100">Terms of Service & User Operating Agreement</h1>
        <p class="text-slate-400">Effective Date: January 1, 2026 | Version 2.4</p>
      </div>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider">1. Acceptance of Terms</h2>
        <p>
          By creating an account, accessing the trading terminal, or utilizing the custodial BEP-20 deposit protocol on TradePro ("Platform"), you acknowledge that you have read, understood, and agreed to be legally bound by these Terms of Service ("Terms"). If you do not agree, you must cease all access to the Platform immediately.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider">2. Account Registration & Eligibility</h2>
        <p>
          You must be at least 18 years of age (or the legal age of majority in your jurisdiction) to register an account. You agree to provide accurate, complete information during registration and maintain strict confidentiality of your login credentials. You are strictly responsible for all actions conducted through your account.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider">3. Custodial BEP-20 Deposits & Settlement Protocol</h2>
        <p>
          The Platform operates a manual verification deposit framework utilizing designated BNB Smart Chain (BEP-20) custodial addresses. When submitting a deposit proof:
        </p>
        <ul class="list-disc pl-5 space-y-1 text-slate-400">
          <li>You must transfer tokens exclusively on the BEP-20 (BNB Smart Chain) network. Transfers via non-supported chains (e.g. ERC-20, TRC-20, Solana) will result in non-recoverable token loss.</li>
          <li>Each Transaction Hash (TxHash / TxID) must be unique. Duplicate submissions are automatically blocked by unique database constraints.</li>
          <li>Deposits are verified manually by support staff before being credited to your double-entry ledger account.</li>
        </ul>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider">4. Order Matching & Double-Entry Accounting Ledger Integrity</h2>
        <p>
          All limit and market orders are matched via an in-memory Redis sorted set matching engine under price-time priority rules. Every executed order generates immutable double-entry ledger entries (`debit` and `credit`) backed by atomic database row-level locking (`lockForUpdate()`). Executed trades are final and non-reversible.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider">5. Systemic Latency & Service Availability</h2>
        <p>
          While TradePro strives for 99.99% system availability, you acknowledge that electronic trading services may experience periodic delays, WebSocket reconnections, or API outages due to market volatility or network congestion. TradePro shall not be held liable for losses incurred due to systemic latency or connectivity failures.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider">6. Governing Law & Dispute Resolution</h2>
        <p>
          These Terms shall be governed by and construed in accordance with international commercial arbitration standards. Any dispute arising out of or relating to these Terms shall be resolved exclusively through binding confidential arbitration.
        </p>
      </section>

      <div class="border-t border-slate-800 pt-6 text-slate-500 text-[11px]">
        For legal inquiries or compliance verification, contact legal@tradepro.io.
      </div>
    </main>
  </div>
</template>
