<template>
  <!-- SUNKEN / COLLAPSED STATE BY DEFAULT -->
  <div v-if="isCollapsed" 
       class="bg-slate-900 border border-slate-800 rounded-lg flex flex-col items-center py-3 h-full overflow-hidden text-xs select-none space-y-4">
    <!-- Expand Toggle Button -->
    <button @click="toggleCollapse" 
            title="Expand Market Pairs List"
            class="bg-slate-950 hover:bg-slate-800 text-emerald-400 border border-slate-800 p-2 rounded-lg transition shadow-md flex items-center justify-center space-x-1 group">
      <span class="font-bold text-[11px] group-hover:translate-x-0.5 transition-transform">▶</span>
    </button>

    <!-- Sunken Active Symbol Badge -->
    <div class="writing-mode-vertical text-[11px] font-bold text-slate-300 font-mono tracking-wider bg-slate-950 px-2 py-3 rounded-lg border border-slate-800 flex items-center space-x-2">
      <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping mb-2"></span>
      <span class="text-emerald-400">{{ currentSymbol }}</span>
    </div>

    <!-- Quick Category Icons -->
    <div class="flex-1 flex flex-col items-center space-y-3 pt-2 text-slate-500 font-mono text-[10px]">
      <button @click="toggleCollapse" title="Crypto Markets" class="hover:text-emerald-400 transition">₿</button>
      <button @click="toggleCollapse" title="Commodities Markets" class="hover:text-emerald-400 transition">🥇</button>
      <button @click="toggleCollapse" title="Stocks Markets" class="hover:text-emerald-400 transition">📈</button>
    </div>
  </div>

  <!-- EXPANDED STATE (WHEN CLICKED BY USER) -->
  <div v-else class="bg-slate-900 border border-slate-800 rounded-lg flex flex-col h-full overflow-hidden text-xs select-none">
    <!-- Header with Collapse Action Button -->
    <div class="p-2 border-b border-slate-800 flex items-center justify-between space-x-2">
      <div class="flex items-center space-x-2 flex-1">
        <input v-model="search" type="text" placeholder="Search Pairs (BTC, GOLD, NVDA...)" 
               class="w-full bg-slate-950 border border-slate-800 rounded px-2.5 py-1 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500/50 text-[11px]" />
        <span class="flex items-center space-x-1 text-[10px] text-emerald-400 font-bold font-mono bg-emerald-500/10 border border-emerald-500/30 px-1.5 py-0.5 rounded whitespace-nowrap">
          <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
          <span>LIVE</span>
        </span>
      </div>

      <!-- Sink / Collapse Button -->
      <button @click="toggleCollapse" title="Sink / Collapse to side" 
              class="bg-slate-950 hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-slate-800 px-2 py-1 rounded text-[11px] transition font-bold">
        ◀
      </button>
    </div>

    <!-- Category Tabs -->
    <div class="flex border-b border-slate-800 bg-slate-950 text-[10px] p-0.5 justify-around font-medium">
      <button v-for="cat in ['all', 'crypto', 'commodities', 'stocks']" :key="cat"
              @click="activeCategory = cat"
              :class="activeCategory === cat ? 'bg-slate-800 text-emerald-400 font-bold' : 'text-slate-400 hover:text-slate-200'"
              class="px-2 py-1 rounded capitalize transition">
        {{ cat }}
      </button>
    </div>

    <!-- Table Header -->
    <div class="grid grid-cols-3 px-3 py-1.5 text-[11px] text-slate-500 font-medium border-b border-slate-800/60">
      <span>Pair</span>
      <span class="text-right">Price</span>
      <span class="text-right">24h</span>
    </div>

    <!-- Markets List -->
    <div class="flex-1 overflow-y-auto divide-y divide-slate-900/60 font-mono">
      <div v-for="m in filteredMarkets" :key="m.symbol"
           @click="handleSelect(m.symbol)"
           :class="m.symbol === currentSymbol ? 'bg-slate-800/80 text-emerald-400 font-bold' : 'hover:bg-slate-800/40 text-slate-300'"
           class="grid grid-cols-3 px-3 py-2 cursor-pointer transition items-center">
        <span class="font-semibold text-slate-200 flex items-center space-x-1">
          <svg v-if="loadingSymbol === m.symbol" class="animate-spin h-3 w-3 text-emerald-400 mr-1" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ m.symbol }}</span>
        </span>
        <span class="text-right">${{ formatPrice(m.last_price) }}</span>
        <span :class="m.change_24h >= 0 ? 'text-emerald-400' : 'text-rose-400'" class="text-right font-medium">
          {{ m.change_24h >= 0 ? '+' : '' }}{{ m.change_24h }}%
        </span>
      </div>

      <div v-if="filteredMarkets.length === 0" class="p-4 text-center text-slate-500 text-[11px]">
        No matching markets found.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  markets: Array,
  currentSymbol: String,
});

const emit = defineEmits(['select-market', 'toggle-collapse']);

// DEFAULT STATE: SUNKEN / COLLAPSED TO SIDE
const isCollapsed = ref(true);
const search = ref('');
const activeCategory = ref('all');
const loadingSymbol = ref(null);

const cryptoSymbols = ['BTC/USDT', 'ETH/USDT', 'BNB/USDT', 'SOL/USDT', 'XRP/USDT', 'DOGE/USDT'];
const commoditySymbols = ['GOLD/USDT', 'SILVER/USDT', 'OIL/USDT'];
const stockSymbols = ['NVDA/USDT', 'AAPL/USDT', 'TSLA/USDT', 'MSFT/USDT', 'SPY/USDT'];

function toggleCollapse() {
  isCollapsed.value = !isCollapsed.value;
  emit('toggle-collapse', isCollapsed.value);
}

const filteredMarkets = computed(() => {
  let list = props.markets || [];

  if (activeCategory.value === 'crypto') {
    list = list.filter(m => cryptoSymbols.includes(m.symbol));
  } else if (activeCategory.value === 'commodities') {
    list = list.filter(m => commoditySymbols.includes(m.symbol));
  } else if (activeCategory.value === 'stocks') {
    list = list.filter(m => stockSymbols.includes(m.symbol));
  }

  if (search.value) {
    list = list.filter(m => m.symbol.toLowerCase().includes(search.value.toLowerCase()));
  }

  return list;
});

function handleSelect(symbol) {
  loadingSymbol.value = symbol;
  emit('select-market', symbol);
}

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<style scoped>
.writing-mode-vertical {
  writing-mode: vertical-rl;
  transform: rotate(180deg);
}
</style>
