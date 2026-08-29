<template>
  <div class="min-h-screen font-sans pb-9" :class="isDark?'bg-[#0b0e11] text-white':'bg-white text-[#1e2329]'">
    <header class="sticky top-0 z-50 border-b px-6 h-14 flex items-center justify-between" :class="isDark?'bg-[#0b0e11] border-[#1e2329]':'bg-white border-gray-200'">
      <a href="/" class="flex items-center space-x-2">
        <div class="w-7 h-7 bg-[#f0b90b] rounded-sm flex items-center justify-center font-black text-[#1e2329] text-sm">T</div>
        <span class="font-bold text-lg" :class="isDark?'text-[#f0b90b]':'text-[#1e2329]'">TRADE<span class="text-[#f0b90b]">PRO</span></span>
      </a>
      <div class="flex items-center space-x-4 text-[13px]">
        <button @click="isDark=!isDark" class="p-2 rounded-lg" :class="isDark?'text-[#b7bdc6]':'text-[#474d57]'"><span v-if="isDark">☀️</span><span v-else>🌙</span></button>
        <a href="/" class="hover:text-[#f0b90b] transition" :class="isDark?'text-[#b7bdc6]':'text-[#474d57]'">← Back to Home</a>
      </div>
    </header>

    <div class="border-b py-16 px-4 text-center" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
      <div class="max-w-2xl mx-auto space-y-4">
        <div class="text-4xl">🛡️</div>
        <h1 class="text-4xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">Bug Bounty Program</h1>
        <p class="text-[15px] leading-relaxed" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Help us keep TradePro secure. Responsible disclosure of security vulnerabilities is rewarded with USDT bounties up to $10,000.</p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-16 space-y-10">
      <!-- Reward Tiers -->
      <div class="space-y-4">
        <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Reward Tiers</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="tier in tiers" :key="tier.level" class="rounded-xl border p-5 space-y-2" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-white border-gray-200'">
            <div class="flex items-center justify-between">
              <span class="font-bold text-[15px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ tier.level }}</span>
              <span class="font-bold text-[#0ecb81]">{{ tier.reward }}</span>
            </div>
            <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ tier.desc }}</p>
          </div>
        </div>
      </div>

      <!-- In Scope -->
      <div class="rounded-2xl border p-6 space-y-4" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">In Scope</h2>
        <ul class="space-y-2 text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
          <li v-for="s in inScope" :key="s" class="flex items-start space-x-2"><span class="text-[#0ecb81] mt-0.5">✔</span><span>{{ s }}</span></li>
        </ul>
      </div>

      <!-- Out of Scope -->
      <div class="rounded-2xl border p-6 space-y-4" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Out of Scope</h2>
        <ul class="space-y-2 text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
          <li v-for="s in outScope" :key="s" class="flex items-start space-x-2"><span class="text-[#f6465d] mt-0.5">✕</span><span>{{ s }}</span></li>
        </ul>
      </div>

      <!-- Submit -->
      <div class="rounded-2xl border p-8 text-center space-y-3" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <div class="text-2xl">📧</div>
        <div class="font-bold text-[16px]" :class="isDark?'text-white':'text-[#1e2329]'">Submit a Report</div>
        <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Email your findings to <span class="text-[#f0b90b] font-semibold">security@tradepro.io</span> with full reproduction steps, impact assessment, and your BEP20 wallet address for reward payment.</p>
        <p class="text-[11px]" :class="isDark?'text-[#636e80]':'text-[#9ea8b5]'">We aim to acknowledge reports within 24 hours and resolve critical issues within 72 hours.</p>
      </div>
    </div>

    <div class="border-t py-6 text-center text-[12px]" :class="isDark?'border-[#1e2329] text-[#848e9c]':'border-gray-200 text-[#707a8a]'">
      © 2026 TradePro Inc. · <a href="/terms" class="hover:text-[#f0b90b]">Terms</a> · <a href="/privacy" class="hover:text-[#f0b90b]">Privacy</a>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue';
const isDark = ref(true);
const tiers = [
  { level:'Critical', reward:'Up to $10,000 USDT', desc:'Remote code execution, authentication bypass, mass fund exfiltration, or private key exposure.' },
  { level:'High', reward:'Up to $2,500 USDT', desc:'SQL injection, IDOR on financial records, privilege escalation to admin, or session hijacking.' },
  { level:'Medium', reward:'Up to $500 USDT', desc:'Stored XSS, CSRF on sensitive actions, account takeover via predictable tokens.' },
  { level:'Low', reward:'Up to $100 USDT', desc:'Reflected XSS, open redirects, minor information disclosure without financial impact.' },
];
const inScope = [
  'tradepro.io main web application and API endpoints',
  'Authentication and session management logic',
  'Wallet and deposit/withdrawal smart contract integrations',
  'Admin panel access control and privilege separation',
  'Binary options settlement logic and payout calculations',
];
const outScope = [
  'Social engineering attacks on TradePro employees',
  'Physical attacks on infrastructure',
  'Third-party services (Binance, BSCScan, Cloudflare)',
  'Denial of service (DoS/DDoS) attacks',
  'Issues in outdated or unsupported browsers',
];
</script>