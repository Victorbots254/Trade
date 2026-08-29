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
        <h1 class="text-2xl font-bold text-slate-100">Global Privacy Policy & Data Protection Disclosure</h1>
        <p class="text-slate-400">Effective Date: January 1, 2026 | Version 2.4</p>
      </div>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-cyan-400 uppercase tracking-wider">1. Information We Collect</h2>
        <p>
          We collect personal and technical information necessary to maintain account security, execute double-entry ledger settlement, and fulfill regulatory obligations. Collected data includes:
        </p>
        <ul class="list-disc pl-5 space-y-1 text-slate-400">
          <li>Account credentials: Full Name, Email Address, and Hashed Password (BCrypt 12 rounds).</li>
          <li>Compliance logs: Acceptance of Terms timestamp, IP address, and browser user-agent headers.</li>
          <li>Blockchain deposit telemetry: Public BEP-20 Transaction Hashes (TxHash) and optional payment receipt uploads.</li>
        </ul>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-cyan-400 uppercase tracking-wider">2. How Information Is Utilized</h2>
        <p>
          Your data is processed strictly for:
        </p>
        <ul class="list-disc pl-5 space-y-1 text-slate-400">
          <li>Authenticating user sessions and managing double-entry wallet accounts.</li>
          <li>Verifying manual BEP-20 custodial deposits against BscScan public ledger explorers.</li>
          <li>Broadcasting real-time order matching events via Laravel Reverb WebSockets.</li>
          <li>Preventing fraudulent deposit claims and duplicate TxHash submissions.</li>
        </ul>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-cyan-400 uppercase tracking-wider">3. Data Retention & Security Architecture</h2>
        <p>
          All sensitive user data is stored within encrypted SQLite/PostgreSQL databases backed by strict row-level access controls. We do not sell, rent, or trade personal information to third-party advertisers. Data retention adheres to international financial record-keeping standards.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-sm font-bold text-cyan-400 uppercase tracking-wider">4. Cookies & WebSockets Storage</h2>
        <p>
          We utilize essential HTTP-only cookies for session authentication (`laravel_session`, `XSRF-TOKEN`). Temporary local state storage is used exclusively for saving user trading preferences and chart tab selections.
        </p>
      </section>

      <div class="border-t border-slate-800 pt-6 text-slate-500 text-[11px]">
        For privacy inquiries or data subject access requests, contact privacy@tradepro.io.
      </div>
    </main>
  </div>
</template>
