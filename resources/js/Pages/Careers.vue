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
        <div class="text-4xl">🚀</div>
        <h1 class="text-4xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">Join Our Team</h1>
        <p class="text-[15px] leading-relaxed" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">We are a remote-first team building the future of accessible global trading. If you love high-impact work, we want to hear from you.</p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-16 space-y-10">
      <!-- Perks -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div v-for="p in perks" :key="p.title" class="rounded-xl border p-4 text-center space-y-2" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
          <div class="text-2xl">{{ p.icon }}</div>
          <div class="font-bold text-[13px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ p.title }}</div>
          <div class="text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ p.desc }}</div>
        </div>
      </div>

      <!-- Open Roles -->
      <div class="space-y-4">
        <h2 class="text-xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Open Positions</h2>
        <div v-for="role in roles" :key="role.title" class="rounded-xl border p-5 flex items-center justify-between" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-white border-gray-200'">
          <div class="space-y-1">
            <div class="font-bold text-[15px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ role.title }}</div>
            <div class="flex items-center space-x-3 text-[12px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
              <span>{{ role.dept }}</span><span>·</span><span>{{ role.type }}</span><span>·</span><span>{{ role.location }}</span>
            </div>
          </div>
          <a href="/contact" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-4 py-2 rounded-lg text-[12px] transition flex-shrink-0">Apply Now</a>
        </div>
      </div>

      <div class="rounded-2xl border p-8 text-center space-y-4" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
        <div class="text-2xl">💌</div>
        <div class="font-bold text-[16px]" :class="isDark?'text-white':'text-[#1e2329]'">Don't see your role?</div>
        <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">We are always looking for exceptional talent. Send your CV and a short note to <span class="text-[#f0b90b]">careers@tradepro.io</span></p>
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
const perks = [
  { icon:'🌍', title:'Remote First', desc:'Work from anywhere in the world.' },
  { icon:'💰', title:'Competitive Pay', desc:'Top-of-market salaries + crypto bonuses.' },
  { icon:'📈', title:'Equity', desc:'Early-stage token allocations available.' },
  { icon:'🏖️', title:'Unlimited PTO', desc:'Flexible time off, no approval needed.' },
];
const roles = [
  { title:'Senior Backend Engineer (Laravel / Redis)', dept:'Engineering', type:'Full-time', location:'Remote' },
  { title:'Frontend Engineer (Vue 3 / Inertia.js)', dept:'Engineering', type:'Full-time', location:'Remote' },
  { title:'Blockchain Integration Engineer', dept:'Engineering', type:'Full-time', location:'Remote' },
  { title:'Product Designer (Figma)', dept:'Design', type:'Full-time', location:'Remote' },
  { title:'Community Manager', dept:'Marketing', type:'Part-time', location:'Remote' },
  { title:'Customer Support Lead', dept:'Operations', type:'Full-time', location:'Remote' },
];
</script>