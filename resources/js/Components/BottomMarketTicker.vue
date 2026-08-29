<template>
  <div class="bottom-ticker-bar fixed bottom-0 left-0 right-0 z-40 bg-slate-950/95 border-t border-slate-800 backdrop-blur-md py-1.5 px-4 text-[11px] font-mono select-none overflow-hidden flex items-center justify-between shadow-2xl transition-colors duration-200">
    <div class="ticker-badge flex items-center space-x-1.5 text-emerald-400 font-bold shrink-0 bg-slate-900 border border-slate-800 px-2.5 py-0.5 rounded text-[10px]">
      <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
      <span>LIVE MARKETS</span>
    </div>

    <!-- Moving Right-to-Left Ticker Row -->
    <div class="flex-1 overflow-hidden relative ml-3">
      <div class="animate-bottom-ticker whitespace-nowrap flex items-center space-x-6"
           :style="{ animationDuration: tickerDuration + 's' }">
        <div v-for="m in displayMarkets" :key="m.symbol" class="inline-flex items-center space-x-1.5">
          <span class="font-bold text-slate-200 ticker-symbol">{{ m.symbol }}</span>
          <span class="text-slate-100 font-bold ticker-price">${{ formatPrice(m.last_price) }}</span>
          <span :class="m.change_24h >= 0 ? 'text-emerald-400' : 'text-rose-400'" class="font-semibold text-[10px]">
            {{ m.change_24h >= 0 ? '+' : '' }}{{ m.change_24h }}%
          </span>
          <span class="text-slate-700 font-sans ml-2 ticker-dot">•</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  markets: Array,
});

const defaultMarkets = [
  { symbol: 'BTC/USDT', last_price: 80450.00, change_24h: 2.45 },
  { symbol: 'ETH/USDT', last_price: 3450.00, change_24h: 1.85 },
  { symbol: 'BNB/USDT', last_price: 580.00, change_24h: 5.40 },
  { symbol: 'SOL/USDT', last_price: 148.50, change_24h: 7.80 },
  { symbol: 'GOLD/USDT', last_price: 2512.40, change_24h: 1.12 },
  { symbol: 'SILVER/USDT', last_price: 29.85, change_24h: 0.85 },
  { symbol: 'OIL/USDT', last_price: 75.40, change_24h: -0.65 },
  { symbol: 'NVDA/USDT', last_price: 128.50, change_24h: 4.25 },
  { symbol: 'AAPL/USDT', last_price: 224.20, change_24h: 0.95 },
  { symbol: 'TSLA/USDT', last_price: 210.80, change_24h: -2.40 },
  { symbol: 'MSFT/USDT', last_price: 415.60, change_24h: 1.45 },
  { symbol: 'SPY/USDT', last_price: 560.20, change_24h: 0.78 },
];
const tickerDuration = computed(() => {
  const count = props.markets && props.markets.length > 0 ? props.markets.length : defaultMarkets.length;
  return count * 4; // 4 seconds per item for consistent readability speed
});

const displayMarkets = computed(() => {
  const list = (props.markets && props.markets.length > 0) ? props.markets : defaultMarkets;
  // Duplicate for smooth seamless infinite loop
  return [...list, ...list];
});

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<style scoped>
@keyframes bottomTicker {
  0% { transform: translateX(0%); }
  100% { transform: translateX(-50%); }
}

.animate-bottom-ticker {
  display: inline-flex;
  animation: bottomTicker 40s linear infinite;
}

.animate-bottom-ticker:hover {
  animation-play-state: paused;
}
</style>
