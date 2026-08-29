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

    <div class="max-w-4xl mx-auto px-4 py-16 space-y-12">
      <div class="text-center space-y-3">
        <div class="text-4xl">🎨</div>
        <h1 class="text-4xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">Media Kit</h1>
        <p class="text-[14px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Official TradePro brand assets, logos, color palettes, and usage guidelines for press and partners.</p>
      </div>

      <!-- Logo section -->
      <div class="space-y-4">
        <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Logo</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="rounded-2xl p-8 border flex items-center justify-center" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
            <div class="flex items-center space-x-2">
              <div class="w-10 h-10 bg-[#f0b90b] rounded-lg flex items-center justify-center font-black text-[#1e2329] text-xl">T</div>
              <span class="font-bold text-2xl" :class="isDark?'text-[#f0b90b]':'text-[#1e2329]'">TRADE<span class="text-[#f0b90b]">PRO</span></span>
            </div>
          </div>
          <div class="rounded-2xl p-8 border flex items-center justify-center bg-[#1e2329]">
            <div class="flex items-center space-x-2">
              <div class="w-10 h-10 bg-[#f0b90b] rounded-lg flex items-center justify-center font-black text-[#1e2329] text-xl">T</div>
              <span class="font-bold text-2xl text-white">TRADE<span class="text-[#f0b90b]">PRO</span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Colors -->
      <div class="space-y-4">
        <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Brand Colors</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div v-for="c in colors" :key="c.name" class="rounded-xl border overflow-hidden" :class="isDark?'border-[#2b3139]':'border-gray-200'">
            <div class="h-16" :style="`background:${c.hex}`"></div>
            <div class="p-3 space-y-0.5" :class="isDark?'bg-[#1e2329]':'bg-gray-50'">
              <div class="font-bold text-[13px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ c.name }}</div>
              <div class="font-mono text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ c.hex }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Guidelines -->
      <div class="rounded-2xl border p-6 space-y-4" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Usage Guidelines</h2>
        <ul class="space-y-2 text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
          <li>✔ Use the official logo on white, dark, or yellow backgrounds only.</li>
          <li>✔ Maintain a clear space of at least 16px around the logo on all sides.</li>
          <li>✕ Do not rotate, distort, recolor, or add effects to the logo.</li>
          <li>✕ Do not use the TradePro name or logo to imply endorsement without written permission.</li>
        </ul>
        <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">For press inquiries and asset requests, contact: <span class="text-[#f0b90b] font-semibold">press@tradepro.io</span></p>
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
const colors = [
  { name:'Brand Gold', hex:'#f0b90b' },
  { name:'Dark BG', hex:'#0b0e11' },
  { name:'Card BG', hex:'#1e2329' },
  { name:'Green', hex:'#0ecb81' },
];
</script>