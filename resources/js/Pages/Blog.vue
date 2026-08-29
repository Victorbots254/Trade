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

    <div class="max-w-4xl mx-auto px-4 py-16 space-y-10">
      <div class="text-center space-y-3">
        <div class="text-4xl">📰</div>
        <h1 class="text-4xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">Blog & News</h1>
        <p class="text-[14px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Trading insights, platform updates, and market analysis from the TradePro team.</p>
      </div>

      <!-- Featured -->
      <div class="rounded-2xl border overflow-hidden" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <div class="p-6 space-y-3">
          <div class="flex items-center space-x-2 text-[11px]">
            <span class="bg-[#f0b90b] text-[#1e2329] font-bold px-2 py-0.5 rounded">FEATURED</span>
            <span :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Aug 27, 2026</span>
          </div>
          <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Binary Options Now Live: Trade BTC, ETH & GOLD with Up to 88% ROI</h2>
          <p class="text-[13px] leading-relaxed" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">We are excited to announce the full launch of Binary Options on TradePro. Predict UP or DOWN on your favourite assets with transparent, fixed-payout contracts from 1 minute to 1 hour expiry.</p>
          <a href="/trade/BTC_USDT" class="inline-block bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-5 py-2 rounded-lg text-sm transition">Start Trading →</a>
        </div>
      </div>

      <!-- Posts Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div v-for="post in posts" :key="post.title" class="rounded-2xl border p-5 space-y-3 hover:border-[#f0b90b]/40 transition" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-white border-gray-200'">
          <div class="text-2xl">{{ post.icon }}</div>
          <div class="text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ post.date }} · {{ post.category }}</div>
          <h3 class="font-bold text-[15px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ post.title }}</h3>
          <p class="text-[13px] leading-relaxed" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ post.excerpt }}</p>
          <a href="#" class="text-[#f0b90b] text-[12px] font-semibold hover:underline">Read More →</a>
        </div>
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
const posts = [
  { icon:'📈', title:'How to Read Candlestick Charts for Beginners', date:'Aug 20, 2026', category:'Education', excerpt:'Candlestick patterns are the foundation of technical analysis. This guide explains the 5 most common patterns every trader needs to know.' },
  { icon:'💰', title:'USDT BEP20 Explained: Why We Chose Binance Smart Chain', date:'Aug 14, 2026', category:'Platform', excerpt:'BEP20 USDT offers near-instant finality, sub-cent fees, and universal wallet support. Here is why it is the ideal deposit currency for TradePro.' },
  { icon:'🔐', title:'How TradePro Secures Your Funds: Cold Storage Architecture', date:'Aug 8, 2026', category:'Security', excerpt:'We store 98% of user funds in offline cold wallets with multi-signature signing requirements. Here is exactly how your money is protected.' },
  { icon:'🎯', title:'Binary vs Spot: Which Trading Style Suits You?', date:'Jul 30, 2026', category:'Education', excerpt:'Both have merit — but they suit very different trading styles and risk tolerances. We break down the differences with real examples.' },
  { icon:'🌍', title:'TradePro Now Serves Traders in 140+ Countries', date:'Jul 22, 2026', category:'Announcement', excerpt:'We have completed our global infrastructure expansion. No KYC barriers, no geographic restrictions. Open to all serious traders.' },
  { icon:'⚡', title:'Under the Hood: Our Redis-Powered Order Matching Engine', date:'Jul 10, 2026', category:'Tech', excerpt:'Sub-millisecond matching is not a marketing claim — it is a direct result of our atomic Lua scripts running on Redis sorted sets.' },
];
</script>