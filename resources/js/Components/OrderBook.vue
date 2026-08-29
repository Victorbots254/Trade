<template>
  <div class="bg-slate-900 border border-slate-800 rounded-lg flex flex-col h-full overflow-hidden text-xs">
    <div class="px-3 py-2 border-b border-slate-800 font-semibold text-slate-300 flex justify-between items-center">
      <span>Order Book</span>
      <span class="text-[11px] text-slate-500 font-mono">Precision: {{ precision }}</span>
    </div>

    <!-- Column Headers -->
    <div class="grid grid-cols-3 px-3 py-1.5 text-[11px] text-slate-500 font-medium border-b border-slate-800/60">
      <span>Price (USDT)</span>
      <span class="text-right">Size</span>
      <span class="text-right">Total</span>
    </div>

    <!-- Asks List (Red - Lowest Asks at bottom) -->
    <div class="flex-1 overflow-y-auto divide-y divide-slate-900/30 flex flex-col-reverse justify-end">
      <div v-for="(ask, idx) in formattedAsks" :key="'ask-'+idx"
           @click="$emit('select-price', ask.price)"
           class="grid grid-cols-3 px-3 py-0.5 cursor-pointer relative hover:bg-rose-950/40 transition items-center font-mono">
        <!-- Depth Bar -->
        <div class="absolute right-0 top-0 bottom-0 bg-rose-500/15 pointer-events-none transition-all duration-300"
             :style="{ width: getDepthPercent(ask.quantity, maxQty) + '%' }"></div>
        <span class="text-rose-400 font-semibold relative z-10">${{ formatPrice(ask.price) }}</span>
        <span class="text-slate-300 text-right relative z-10">{{ ask.quantity.toFixed(4) }}</span>
        <span class="text-slate-400 text-right relative z-10">${{ (ask.price * ask.quantity).toFixed(2) }}</span>
      </div>
    </div>

    <!-- Last Spread Indicator -->
    <div class="px-3 py-2 bg-slate-950 border-y border-slate-800 flex items-center justify-between font-mono">
      <div class="flex items-center space-x-2">
        <span class="text-sm font-bold text-emerald-400">${{ formatPrice(lastPrice) }}</span>
        <span class="text-[11px] text-slate-500">↑ Spread: {{ spread }}</span>
      </div>
    </div>

    <!-- Bids List (Green - Highest Bids at top) -->
    <div class="flex-1 overflow-y-auto divide-y divide-slate-900/30">
      <div v-for="(bid, idx) in formattedBids" :key="'bid-'+idx"
           @click="$emit('select-price', bid.price)"
           class="grid grid-cols-3 px-3 py-0.5 cursor-pointer relative hover:bg-emerald-950/40 transition items-center font-mono">
        <!-- Depth Bar -->
        <div class="absolute right-0 top-0 bottom-0 bg-emerald-500/15 pointer-events-none transition-all duration-300"
             :style="{ width: getDepthPercent(bid.quantity, maxQty) + '%' }"></div>
        <span class="text-emerald-400 font-semibold relative z-10">${{ formatPrice(bid.price) }}</span>
        <span class="text-slate-300 text-right relative z-10">{{ bid.quantity.toFixed(4) }}</span>
        <span class="text-slate-400 text-right relative z-10">${{ (bid.price * bid.quantity).toFixed(2) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  bids: Array,
  asks: Array,
  lastPrice: [Number, String],
  precision: { type: Number, default: 2 },
});

defineEmits(['select-price']);

const formattedBids = computed(() => (props.bids || []).slice(0, 15));
const formattedAsks = computed(() => (props.asks || []).slice(0, 15));

const maxQty = computed(() => {
  const all = [...formattedBids.value, ...formattedAsks.value];
  if (all.length === 0) return 1;
  return Math.max(...all.map(i => i.quantity || 0));
});

const spread = computed(() => {
  const topBid = formattedBids.value[0]?.price || 0;
  const topAsk = formattedAsks.value[0]?.price || 0;
  if (!topBid || !topAsk) return '0.00';
  return Math.abs(topAsk - topBid).toFixed(2);
});

function getDepthPercent(qty, max) {
  if (!max || max === 0) return 0;
  return Math.min(100, Math.round((qty / max) * 100));
}

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
