<template>
  <div class="lg:hidden flex flex-col bg-slate-950 border-b border-slate-800/80 select-none">
    <!-- Horizontal Swipeable Markets Strip -->
    <div class="px-2 py-1.5 flex items-center space-x-2 overflow-x-auto no-scrollbar scroll-smooth">
      <!-- "All Markets" Trigger Button -->
      <button @click="showMarketsModal = true" 
              class="shrink-0 bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-[#f0b90b] text-[#f0b90b] px-2.5 py-1 rounded-lg text-xs font-bold flex items-center space-x-1.5 transition shadow-sm">
        <span>⚡</span>
        <span>All Pairs</span>
        <span class="bg-[#f0b90b]/20 text-[#f0b90b] text-[10px] px-1.5 py-0.2 rounded-full font-mono">{{ displayMarkets.length }}</span>
      </button>

      <!-- Market Chips -->
      <button v-for="m in displayMarkets" :key="m.symbol"
              @click="handleSelect(m.symbol)"
              :class="isCurrent(m.symbol) ? 'bg-[#f0b90b]/15 border-[#f0b90b] text-slate-100 shadow-sm shadow-[#f0b90b]/20 ring-1 ring-[#f0b90b]/40' : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700'"
              class="shrink-0 border px-2.5 py-1 rounded-lg flex items-center space-x-2 text-xs font-mono transition">
        <span class="text-sm leading-none">{{ getSymbolIcon(m.symbol) }}</span>
        <div class="flex items-center space-x-1.5">
          <span class="font-bold font-sans" :class="isCurrent(m.symbol) ? 'text-[#f0b90b]' : 'text-slate-200'">
            {{ getShortSymbol(m.symbol) }}
          </span>
          <span class="text-slate-100 font-bold">${{ formatCompactPrice(m.last_price) }}</span>
          <span :class="Number(m.change_24h || 0) >= 0 ? 'text-emerald-400' : 'text-rose-400'" class="text-[10px] font-semibold">
            {{ Number(m.change_24h || 0) >= 0 ? '+' : '' }}{{ Number(m.change_24h || 0).toFixed(1) }}%
          </span>
        </div>
      </button>
    </div>

    <!-- Active Pair Ticker & Quick Stats Sub-Header -->
    <div class="px-3 py-2 bg-slate-900/90 border-t border-slate-800 flex items-center justify-between text-xs">
      <div class="flex items-center space-x-2">
        <button @click="showMarketsModal = true" 
                class="bg-slate-950 hover:bg-slate-850 border border-slate-700 hover:border-[#f0b90b] text-slate-100 px-2.5 py-1 rounded-lg font-bold flex items-center space-x-1.5 transition">
          <span class="text-base leading-none">{{ getSymbolIcon(currentSymbol) }}</span>
          <span class="text-[#f0b90b] font-mono text-sm tracking-wide">{{ currentSymbol }}</span>
          <span class="text-slate-500 text-[10px]">▼</span>
        </button>

        <div class="flex flex-col">
          <span class="font-mono text-emerald-400 font-bold text-sm leading-tight">${{ formatPrice(currentMarket?.last_price) }}</span>
          <span :class="Number(currentMarket?.change_24h || 0) >= 0 ? 'text-emerald-400' : 'text-rose-400'" class="font-mono text-[10px] leading-none">
            {{ Number(currentMarket?.change_24h || 0) >= 0 ? '+' : '' }}{{ Number(currentMarket?.change_24h || 0).toFixed(2) }}%
          </span>
        </div>
      </div>

      <div class="flex items-center space-x-3 text-[10px] font-mono text-slate-400 text-right">
        <div>
          <span class="text-slate-500 block leading-none">24h High</span>
          <span class="text-slate-200 font-semibold leading-tight">${{ formatPrice(currentMarket?.high_24h) }}</span>
        </div>
        <div>
          <span class="text-slate-500 block leading-none">24h Low</span>
          <span class="text-slate-200 font-semibold leading-tight">${{ formatPrice(currentMarket?.low_24h) }}</span>
        </div>
      </div>
    </div>

    <!-- FULLSCREEN / BOTTOM SHEET MARKETS SELECTION MODAL -->
    <div v-if="showMarketsModal" class="fixed inset-0 z-[100] bg-slate-950/85 backdrop-blur-sm flex flex-col justify-end sm:justify-center p-0 sm:p-4">
      <div class="bg-slate-900 border border-slate-800 rounded-t-2xl sm:rounded-2xl max-h-[85vh] w-full max-w-lg mx-auto flex flex-col overflow-hidden shadow-2xl">
        <!-- Modal Top Bar -->
        <div class="p-3.5 border-b border-slate-800 flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span class="text-base font-bold text-slate-100">Select Market</span>
            <span class="bg-[#f0b90b]/15 text-[#f0b90b] text-[11px] font-bold px-2 py-0.5 rounded-full font-mono">
              {{ filteredMarkets.length }} pairs
            </span>
          </div>
          <button @click="showMarketsModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 text-sm font-bold">
            ✕
          </button>
        </div>

        <!-- Search Bar -->
        <div class="p-3 border-b border-slate-800 bg-slate-950/60">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Search pairs (BTC, ETH, GOLD, NVDA...)" 
                   class="w-full bg-slate-900 border border-slate-800 focus:border-[#f0b90b] rounded-xl pl-9 pr-8 py-2 text-slate-100 placeholder-slate-500 text-xs focus:outline-none transition font-sans" />
            <span class="absolute left-3 top-2.5 text-slate-500 text-xs">🔍</span>
            <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2 text-slate-500 hover:text-slate-300 text-xs p-0.5">✕</button>
          </div>

          <!-- Category Filter Pills -->
          <div class="flex items-center space-x-1.5 mt-2.5 overflow-x-auto no-scrollbar">
            <button v-for="cat in categories" :key="cat.id"
                    @click="activeCategory = cat.id"
                    :class="activeCategory === cat.id ? 'bg-[#f0b90b] text-[#1e2329] font-bold shadow-sm' : 'bg-slate-900 text-slate-400 hover:text-slate-200 border border-slate-800'"
                    class="px-2.5 py-1 rounded-lg text-[11px] shrink-0 transition flex items-center space-x-1">
              <span>{{ cat.icon }}</span>
              <span>{{ cat.label }}</span>
            </button>
          </div>
        </div>

        <!-- Table Columns Header -->
        <div class="grid grid-cols-3 px-4 py-2 text-[10px] uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-800/60 bg-slate-950/40">
          <span>Market / Pair</span>
          <span class="text-right">Last Price</span>
          <span class="text-right">24h Change</span>
        </div>

        <!-- Scrollable Pairs List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-800/60">
          <div v-for="m in filteredMarkets" :key="m.symbol"
               @click="handleSelect(m.symbol)"
               :class="isCurrent(m.symbol) ? 'bg-[#f0b90b]/10 border-l-2 border-[#f0b90b]' : 'hover:bg-slate-800/40 active:bg-slate-800/70'"
               class="grid grid-cols-3 px-4 py-3 cursor-pointer transition items-center font-mono text-xs">
            <!-- Pair info -->
            <div class="flex items-center space-x-2">
              <span class="text-base leading-none">{{ getSymbolIcon(m.symbol) }}</span>
              <div class="flex flex-col">
                <span class="font-bold font-sans" :class="isCurrent(m.symbol) ? 'text-[#f0b90b]' : 'text-slate-100'">
                  {{ m.symbol }}
                </span>
                <span class="text-[10px] text-slate-400 font-sans">{{ getPairCategoryName(m.symbol) }}</span>
              </div>
            </div>

            <!-- Price -->
            <div class="text-right">
              <span class="font-bold text-slate-100 text-xs">${{ formatPrice(m.last_price) }}</span>
              <span class="block text-[10px] text-slate-500">Vol: {{ formatCompactPrice(m.volume_24h || 1200) }}</span>
            </div>

            <!-- 24h Change badge -->
            <div class="text-right flex flex-col items-end">
              <span :class="Number(m.change_24h || 0) >= 0 ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/15 text-rose-400 border-rose-500/30'" 
                    class="px-2 py-0.5 rounded text-[11px] font-bold border inline-block">
                {{ Number(m.change_24h || 0) >= 0 ? '+' : '' }}{{ Number(m.change_24h || 0).toFixed(2) }}%
              </span>
              <span class="text-[9px] text-slate-500 mt-0.5">H: ${{ formatCompactPrice(m.high_24h) }}</span>
            </div>
          </div>

          <div v-if="filteredMarkets.length === 0" class="p-8 text-center text-slate-500 text-xs space-y-1">
            <p>No trading pairs found for "{{ searchQuery }}"</p>
            <p class="text-[10px]">Try searching for BTC, ETH, GOLD, or NVDA</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  markets: { type: Array, default: () => [] },
  currentMarket: { type: Object, default: () => ({}) },
  currentSymbol: { type: String, default: 'BTC/USDT' },
});

const emit = defineEmits(['select-market']);

const showMarketsModal = ref(false);
const searchQuery = ref('');
const activeCategory = ref('all');

const categories = [
  { id: 'all', label: 'All Pairs', icon: '🌐' },
  { id: 'crypto', label: 'Crypto', icon: '₿' },
  { id: 'commodities', label: 'Commodities', icon: '🥇' },
  { id: 'stocks', label: 'Stocks & Indices', icon: '📈' },
];

const cryptoSymbols = ['BTC/USDT', 'ETH/USDT', 'BNB/USDT', 'SOL/USDT', 'XRP/USDT', 'DOGE/USDT'];
const commoditySymbols = ['GOLD/USDT', 'SILVER/USDT', 'OIL/USDT'];
const stockSymbols = ['NVDA/USDT', 'AAPL/USDT', 'TSLA/USDT', 'MSFT/USDT', 'SPY/USDT'];

const defaultMarkets = [
  { symbol: 'BTC/USDT', last_price: 80450.00, change_24h: 2.45, high_24h: 81200.00, low_24h: 79500.00 },
  { symbol: 'ETH/USDT', last_price: 3450.00, change_24h: 1.85, high_24h: 3520.00, low_24h: 3410.00 },
  { symbol: 'BNB/USDT', last_price: 580.00, change_24h: 5.40, high_24h: 595.00, low_24h: 565.00 },
  { symbol: 'SOL/USDT', last_price: 148.50, change_24h: 7.80, high_24h: 152.00, low_24h: 139.50 },
  { symbol: 'XRP/USDT', last_price: 0.5850, change_24h: 2.10, high_24h: 0.6100, low_24h: 0.5700 },
  { symbol: 'DOGE/USDT', last_price: 0.1240, change_24h: 4.50, high_24h: 0.1300, low_24h: 0.1180 },
  { symbol: 'GOLD/USDT', last_price: 2512.40, change_24h: 1.12, high_24h: 2525.00, low_24h: 2495.00 },
  { symbol: 'SILVER/USDT', last_price: 29.85, change_24h: 0.85, high_24h: 30.20, low_24h: 29.40 },
  { symbol: 'OIL/USDT', last_price: 75.40, change_24h: -0.65, high_24h: 76.80, low_24h: 74.90 },
  { symbol: 'NVDA/USDT', last_price: 128.50, change_24h: 4.25, high_24h: 131.00, low_24h: 124.80 },
  { symbol: 'AAPL/USDT', last_price: 224.20, change_24h: 0.95, high_24h: 226.50, low_24h: 222.10 },
  { symbol: 'TSLA/USDT', last_price: 210.80, change_24h: -2.40, high_24h: 218.00, low_24h: 208.50 },
  { symbol: 'MSFT/USDT', last_price: 415.60, change_24h: 1.45, high_24h: 419.00, low_24h: 411.20 },
  { symbol: 'SPY/USDT', last_price: 560.20, change_24h: 0.78, high_24h: 562.50, low_24h: 558.00 },
];

const displayMarkets = computed(() => {
  return props.markets && props.markets.length > 0 ? props.markets : defaultMarkets;
});

const filteredMarkets = computed(() => {
  let list = displayMarkets.value;

  if (activeCategory.value === 'crypto') {
    list = list.filter(m => cryptoSymbols.includes(m.symbol));
  } else if (activeCategory.value === 'commodities') {
    list = list.filter(m => commoditySymbols.includes(m.symbol));
  } else if (activeCategory.value === 'stocks') {
    list = list.filter(m => stockSymbols.includes(m.symbol));
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase();
    list = list.filter(m => m.symbol.toLowerCase().includes(q) || getPairCategoryName(m.symbol).toLowerCase().includes(q));
  }

  return list;
});

function isCurrent(symbol) {
  return symbol === props.currentSymbol;
}

function handleSelect(symbol) {
  showMarketsModal.value = false;
  emit('select-market', symbol);
}

function getSymbolIcon(symbol) {
  if (!symbol) return '🪙';
  const sym = symbol.toUpperCase();
  if (sym.startsWith('BTC')) return '₿';
  if (sym.startsWith('ETH')) return 'Ξ';
  if (sym.startsWith('BNB')) return '🟡';
  if (sym.startsWith('SOL')) return '◎';
  if (sym.startsWith('XRP')) return '🪙';
  if (sym.startsWith('DOGE')) return '🐕';
  if (sym.startsWith('GOLD')) return '🥇';
  if (sym.startsWith('SILVER')) return '🥈';
  if (sym.startsWith('OIL')) return '🛢️';
  if (sym.startsWith('NVDA')) return '🟢';
  if (sym.startsWith('AAPL')) return '🍏';
  if (sym.startsWith('TSLA')) return '⚡';
  if (sym.startsWith('MSFT')) return '🪟';
  if (sym.startsWith('SPY')) return '📈';
  return '🪙';
}

function getShortSymbol(symbol) {
  if (!symbol) return '';
  return symbol.split('/')[0];
}

function getPairCategoryName(symbol) {
  if (!symbol) return 'Market';
  if (cryptoSymbols.includes(symbol)) return 'Cryptocurrency';
  if (commoditySymbols.includes(symbol)) return 'Commodity';
  if (stockSymbols.includes(symbol)) return 'Stock Equity';
  return 'Global Market';
}

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatCompactPrice(val) {
  const n = Number(val || 0);
  if (n >= 1000) {
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  } else if (n >= 1) {
    return n.toFixed(2);
  } else {
    return n.toFixed(4);
  }
}
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
