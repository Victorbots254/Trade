<template>
  <div class="min-h-screen font-sans pb-9" :class="isDark?'bg-[#0b0e11] text-white':'bg-white text-[#1e2329]'">
    <!-- NAV -->
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

    <!-- HERO -->
    <div class="border-b py-16 px-4 text-center" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
      <div class="max-w-3xl mx-auto space-y-4">
        <div class="text-4xl">🏢</div>
        <h1 class="text-4xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">About TradePro</h1>
        <p class="text-[15px] leading-relaxed" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
          We are building the next generation of accessible, institutional-grade trading infrastructure — designed for everyone from first-time traders to professional desks.
        </p>
      </div>
    </div>

    <!-- MISSION -->
    <div class="max-w-4xl mx-auto px-4 py-16 space-y-14">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <div class="space-y-4">
          <div class="text-[11px] font-bold text-[#f0b90b] uppercase tracking-widest">Our Mission</div>
          <h2 class="text-2xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Democratizing Access to Global Markets</h2>
          <p class="text-[14px] leading-relaxed" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
            TradePro was founded with one belief: that every person — regardless of geography or capital — deserves access to the same quality of financial tools that institutions use. We offer spot trading, binary options, and instant USDT settlements via BNB Smart Chain, all in one streamlined platform.
          </p>
        </div>
        <div class="rounded-2xl p-8 border text-center space-y-2" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
          <div class="text-5xl font-extrabold text-[#f0b90b]">185K+</div>
          <div class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Registered traders worldwide</div>
        </div>
      </div>

      <!-- VALUES -->
      <div class="space-y-6">
        <h2 class="text-2xl font-bold text-center" :class="isDark?'text-white':'text-[#1e2329]'">Our Core Values</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div v-for="v in values" :key="v.title" class="rounded-2xl p-6 border space-y-3 text-center" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
            <div class="text-3xl">{{ v.icon }}</div>
            <div class="font-bold text-[15px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ v.title }}</div>
            <div class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ v.desc }}</div>
          </div>
        </div>
      </div>

      <!-- STATS -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center rounded-2xl border p-8" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <div v-for="s in stats" :key="s.label">
          <div class="text-2xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">{{ s.value }}</div>
          <div class="text-[12px] mt-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- FOOTER MINI -->
    <div class="border-t py-6 text-center text-[12px]" :class="isDark?'border-[#1e2329] text-[#848e9c]':'border-gray-200 text-[#707a8a]'">
      © 2026 TradePro Inc. · <a href="/terms" class="hover:text-[#f0b90b]">Terms</a> · <a href="/privacy" class="hover:text-[#f0b90b]">Privacy</a>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue';
const isDark = ref(true);
const values = [
  { icon: '🔐', title: 'Security First', desc: 'Cold wallet custody, double-entry accounting, and encrypted infrastructure protect every user.' },
  { icon: '⚡', title: 'Speed & Reliability', desc: 'Sub-millisecond order matching and 99.9% uptime SLA across all trading pairs.' },
  { icon: '🌍', title: 'Global Access', desc: 'No KYC barriers. Minimum $10 deposit. Open to traders in 140+ countries.' },
];
const stats = [
  { value: '$2.48B+', label: '24h Volume' },
  { value: '185K+', label: 'Traders' },
  { value: '14+', label: 'Markets' },
  { value: '<0.5ms', label: 'Latency' },
];
</script>