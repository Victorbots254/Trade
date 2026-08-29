<template>
  <div class="bg-slate-900 border-t border-b border-slate-800 py-1.5 px-3 flex items-center space-x-3 text-[11px] overflow-hidden select-none font-mono">
    <div class="flex items-center space-x-1 text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded shrink-0">
      <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
      <span>VERIFIED PAYOUTS (PAST 30 DAYS)</span>
    </div>

    <!-- Right-to-Left Moving Marquee Ticker -->
    <div class="flex-1 overflow-hidden relative">
      <div class="animate-marquee whitespace-nowrap flex items-center space-x-6">
        <span v-for="(w, idx) in withdrawals" :key="idx" class="inline-flex items-center space-x-1.5">
          <span class="text-slate-300 font-bold">{{ w.name }}</span>
          <span class="text-emerald-400 font-bold">+${{ formatPrice(w.amount) }} USDT</span>
          <span class="text-slate-500 text-[10px]">({{ w.network }})</span>
          <span class="text-slate-600 font-sans">•</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
const withdrawals = [
  { name: 'Alexander M.', amount: 14850.00, network: 'BEP20' },
  { name: 'Sophia Chen', amount: 26400.00, network: 'BEP20' },
  { name: 'David K.', amount: 500.00, network: 'BEP20' },
  { name: 'Elena Rostova', amount: 42100.00, network: 'BEP20' },
  { name: 'Marcus Vance', amount: 18500.00, network: 'BEP20' },
  { name: 'Lucas Dubois', amount: 9800.00, network: 'BEP20' },
  { name: 'Chloe Taylor', amount: 31200.00, network: 'BEP20' },
  { name: 'Viktor Popov', amount: 15400.00, network: 'BEP20' },
  { name: 'Amara Okafor', amount: 50000.00, network: 'BEP20' },
  { name: 'Liam O’Connor', amount: 12600.00, network: 'BEP20' },
  { name: 'Isabella Rossi', amount: 21900.00, network: 'BEP20' },
  { name: 'Kenji Sato', amount: 37500.00, network: 'BEP20' },
  { name: 'Hannah Wright', amount: 8400.00, network: 'BEP20' },
  { name: 'Gabriel Silva', amount: 16800.00, network: 'BEP20' },
  { name: 'Nadia El-Sayed', amount: 29300.00, network: 'BEP20' },
  { name: 'Ethan Hunt', amount: 10500.00, network: 'BEP20' },
  { name: 'Zoe Martinez', amount: 45000.00, network: 'BEP20' },
  { name: 'Dmitry Volkov', amount: 11200.00, network: 'BEP20' },
  { name: 'Fatima Al-Hassan', amount: 33600.00, network: 'BEP20' },
  { name: 'Oliver Smith', amount: 19400.00, network: 'BEP20' },
];

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<style scoped>
@keyframes marquee {
  0% { transform: translateX(0%); }
  100% { transform: translateX(-50%); }
}

.animate-marquee {
  display: inline-flex;
  animation: marquee 35s linear infinite;
}

.animate-marquee:hover {
  animation-play-state: paused;
}
</style>
