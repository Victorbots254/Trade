<template>
  <div class="bg-slate-900 border border-slate-800 rounded-lg flex flex-col h-full overflow-hidden text-xs">
    <div class="px-3 py-2 border-b border-slate-800 font-semibold text-slate-300 flex justify-between items-center">
      <span>Recent Market Trades ({{ (trades || []).length }})</span>
      <span class="flex items-center space-x-1 text-[10px] text-emerald-400 font-bold font-mono bg-emerald-500/10 border border-emerald-500/30 px-1.5 py-0.5 rounded">
        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
        <span>STREAMING</span>
      </span>
    </div>

    <div class="grid grid-cols-3 px-3 py-1.5 text-[11px] text-slate-500 font-medium border-b border-slate-800/60">
      <span>Price (USDT)</span>
      <span class="text-right">Size</span>
      <span class="text-right">Time</span>
    </div>

    <div class="flex-1 overflow-y-auto divide-y divide-slate-900/40 font-mono">
      <div v-for="t in trades" :key="t.id"
           class="grid grid-cols-3 px-3 py-1.5 items-center hover:bg-slate-800/30 transition">
        <span :class="t.side === 'buy' ? 'text-emerald-400' : 'text-rose-400'" class="font-semibold">
          ${{ formatPrice(t.price) }}
        </span>
        <span class="text-slate-300 text-right">{{ Number(t.quantity).toFixed(4) }}</span>
        <span class="text-slate-500 text-right text-[11px]">{{ formatTime(t.timestamp || t.created_at) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  trades: Array,
});

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatTime(ts) {
  if (!ts) return '';
  const d = new Date(ts);
  return d.toTimeString().split(' ')[0];
}
</script>
