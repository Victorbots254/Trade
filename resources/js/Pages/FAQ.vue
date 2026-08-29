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

    <div class="max-w-3xl mx-auto px-4 py-16 space-y-10">
      <div class="text-center space-y-3">
        <div class="text-4xl">❓</div>
        <h1 class="text-4xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">FAQ Center</h1>
        <p class="text-[14px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Everything you need to know about TradePro. Can't find an answer? <a href="/contact" class="text-[#f0b90b] hover:underline">Contact us →</a></p>
      </div>

      <div v-for="cat in faqCategories" :key="cat.name" class="space-y-4">
        <div class="flex items-center space-x-2">
          <span class="text-xl">{{ cat.icon }}</span>
          <h2 class="text-lg font-bold" :class="isDark?'text-white':'text-[#1e2329]'">{{ cat.name }}</h2>
        </div>
        <div v-for="(faq,i) in cat.faqs" :key="i" class="rounded-xl border overflow-hidden" :class="isDark?'border-[#2b3139]':'border-gray-200'">
          <button @click="toggle(cat.name+i)" class="w-full text-left px-5 py-4 flex items-center justify-between font-semibold text-[14px] transition" :class="isDark?'bg-[#1e2329] text-white hover:bg-[#2b3139]':'bg-gray-50 text-[#1e2329] hover:bg-gray-100'">
            <span>{{ faq.q }}</span>
            <span class="text-[#f0b90b] text-xl leading-none">{{ openKey===cat.name+i?'−':'+' }}</span>
          </button>
          <div v-if="openKey===cat.name+i" class="px-5 py-4 text-[13px] leading-relaxed border-t" :class="isDark?'bg-[#0b0e11] border-[#2b3139] text-[#848e9c]':'bg-white border-gray-200 text-[#707a8a]'">{{ faq.a }}</div>
        </div>
      </div>

      <div class="rounded-2xl border p-8 text-center space-y-3" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <div class="text-2xl">💬</div>
        <div class="font-bold text-[16px]" :class="isDark?'text-white':'text-[#1e2329]'">Still have questions?</div>
        <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Our support team is available 24/7.</p>
        <a href="/contact" class="inline-block bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-6 py-2.5 rounded-lg text-sm transition">Contact Support →</a>
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
const openKey = ref(null);
function toggle(key) { openKey.value = openKey.value === key ? null : key; }
const faqCategories = [
  { icon:'💰', name:'Deposits & Withdrawals', faqs:[
    { q:'What is the minimum deposit?', a:'Minimum deposit is $10 USDT via BNB Smart Chain (BEP20). Credits appear after 1 confirmation (approx. 1-3 minutes).' },
    { q:'How do I deposit USDT?', a:'Go to Payments → Deposit. Copy your unique BEP20 deposit address and send USDT from any BEP20-compatible wallet or exchange (e.g., Binance, Trust Wallet).' },
    { q:'How fast are withdrawals?', a:'Within 10 minutes of admin approval. Funds arrive directly in your BEP20 wallet with a verifiable on-chain transaction hash.' },
    { q:'Are there withdrawal fees?', a:'TradePro charges no platform withdrawal fee. Standard BNB Smart Chain gas fees (typically under $0.01) apply.' },
  ]},
  { icon:'📈', name:'Trading', faqs:[
    { q:'What is Spot Trading?', a:'Spot trading lets you buy and sell asset pairs (e.g., BTC/USDT) at current market prices in real time with full order book depth.' },
    { q:'What are Binary Options?', a:'Binary Options are short-term contracts where you predict whether an asset price will be HIGHER or LOWER at expiry. Correct predictions pay up to 88% ROI. Wrong predictions lose the stake.' },
    { q:'Can I trade without depositing real money?', a:'Yes. Every account has a free $10,000 demo practice balance. Trade Spot and Options risk-free. Reset your demo balance anytime from your Profile page.' },
  ]},
  { icon:'🔐', name:'Account & Security', faqs:[
    { q:'Do I need to verify my identity (KYC)?', a:'No KYC is required to register and trade on TradePro. Simply sign up with an email address.' },
    { q:'How do I reset my password?', a:'On the login page, click "Forgot Password" and follow the email reset link. If you face issues, contact support.' },
    { q:'Is my balance safe?', a:'Yes. Real funds are held in cold wallet custody with strict double-entry accounting. Demo funds are completely separate and do not affect your real balance.' },
  ]},
];
</script>