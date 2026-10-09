<template>
  <div class="bg-white dark:bg-[#181a20] border border-slate-300 dark:border-[#2b3139] rounded-xl p-3.5 flex flex-col h-full text-xs select-none shadow-lg transition-colors">
    <ToastNotification ref="toastRef" />

    <!-- Top Account Mode Pill (Demo vs Live) -->
    <div class="mb-3 p-2.5 rounded-xl font-mono text-[11px] flex justify-between items-center transition-colors"
         :class="activeAccountMode === 'demo' ? 'bg-amber-100 dark:bg-[#f0b90b]/15 border border-amber-300 dark:border-[#f0b90b]/40 text-amber-900 dark:text-[#f0b90b]' : 'bg-emerald-100 dark:bg-[#0ecb81]/15 border border-emerald-300 dark:border-[#0ecb81]/40 text-emerald-900 dark:text-[#0ecb81]'">
      <div class="flex items-center space-x-1.5 font-black">
        <span v-if="activeAccountMode === 'demo'">🎮 DEMO DASHBOARD</span>
        <span v-else class="flex items-center space-x-1.5">
          <span class="w-2 h-2 bg-[#0ecb81] rounded-full animate-ping"></span>
          <span>🟢 LIVE REAL TRADING</span>
        </span>
      </div>
      <span class="text-[10px] font-bold uppercase tracking-wider opacity-90">{{ activeAccountMode === 'demo' ? 'Virtual Funds' : 'Real Capital' }}</span>
    </div>

    <!-- Side Toggle: BUY vs SELL -->
    <div class="grid grid-cols-2 gap-1 bg-slate-100 dark:bg-[#0b0e11] p-1 rounded-xl border border-slate-300 dark:border-[#2b3139] mb-3">
      <button @click="side = 'buy'" 
              :class="side === 'buy' ? 'bg-[#0ecb81] text-[#1e2329] font-black shadow-md' : 'text-slate-600 dark:text-[#848e9c] hover:text-slate-900 dark:hover:text-white font-bold'"
              class="py-2 rounded-lg transition text-center uppercase tracking-wider text-xs">
        Buy {{ market?.base_currency }}
      </button>
      <button @click="side = 'sell'" 
              :class="side === 'sell' ? 'bg-[#f6465d] text-white font-black shadow-md' : 'text-slate-600 dark:text-[#848e9c] hover:text-slate-900 dark:hover:text-white font-bold'"
              class="py-2 rounded-lg transition text-center uppercase tracking-wider text-xs">
        Sell {{ market?.base_currency }}
      </button>
    </div>

    <!-- Order Type: Limit vs Market -->
    <div class="flex items-center justify-between mb-3 border-b border-slate-200 dark:border-[#2b3139] pb-2.5 text-xs">
      <div class="flex space-x-4">
        <button @click="type = 'limit'" 
                :class="type === 'limit' ? 'text-[#0ecb81] font-black border-b-2 border-[#0ecb81] pb-1' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'">
          Limit Order
        </button>
        <button @click="type = 'market'" 
                :class="type === 'market' ? 'text-[#0ecb81] font-black border-b-2 border-[#0ecb81] pb-1' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'">
          Market Order
        </button>
      </div>

      <button v-if="side === 'sell' && (baseWallet?.available_balance || 0) > 0"
              @click="quickSellAllToFunds" type="button" :disabled="loading"
              class="text-[10px] text-amber-700 dark:text-amber-300 bg-amber-500/15 border border-amber-500/40 hover:bg-amber-500/25 px-2.5 py-1 rounded-lg font-mono font-bold flex items-center space-x-1 transition">
        <svg v-if="quickLiquidating" class="animate-spin h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Liquidate All to USDT</span>
      </button>
    </div>

    <!-- Available Balance Indicator (High Contrast) -->
    <div class="flex justify-between items-center mb-3 font-mono text-xs p-2 rounded-lg bg-slate-50 dark:bg-[#0b0e11] border border-slate-200 dark:border-[#2b3139]">
      <span class="text-slate-600 dark:text-slate-300 font-bold">Avail Balance:</span>
      <span class="text-slate-900 dark:text-white font-black text-xs sm:text-[13px]">
        {{ side === 'buy' ? `${formatBalance(quoteWallet?.available_balance)} ${market?.quote_currency}` : `${formatBalance(baseWallet?.available_balance)} ${market?.base_currency}` }}
      </span>
    </div>

    <!-- Inputs Form -->
    <form @submit.prevent="submitOrder" class="space-y-3.5 flex-1 flex flex-col justify-between">
      <div class="space-y-3">
        <!-- Price Input -->
        <div v-if="type === 'limit'">
          <label class="block text-slate-700 dark:text-slate-200 font-bold text-xs mb-1.5">
            Price ({{ market?.quote_currency }})
          </label>
          <div class="relative">
            <input v-model="price" @input="userEditedPrice = true" type="number" step="any" min="0.000001" required
                   class="w-full bg-slate-50 dark:bg-[#0b0e11] border border-slate-300 dark:border-[#2b3139] rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono font-bold text-sm focus:border-[#0ecb81] focus:outline-none transition" />
            <span class="absolute right-3 top-2.5 text-slate-500 dark:text-slate-400 font-mono font-bold text-xs">
              {{ market?.quote_currency }}
            </span>
          </div>
        </div>

        <div v-else class="bg-slate-100 dark:bg-[#0b0e11] border border-slate-300 dark:border-[#2b3139] rounded-xl p-2.5 text-slate-700 dark:text-slate-300 text-center font-mono font-bold text-xs">
          Market Price (Instant Best Execution)
        </div>

        <!-- Amount Input -->
        <div>
          <label class="block text-slate-700 dark:text-slate-200 font-bold text-xs mb-1.5">
            Amount ({{ side === 'buy' ? 'USDT' : market?.base_currency }})
          </label>
          <div class="relative">
            <input v-model="orderAmount" type="number" step="any" min="0.000001" required
                   class="w-full bg-slate-50 dark:bg-[#0b0e11] border border-slate-300 dark:border-[#2b3139] rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono font-bold text-sm focus:border-[#0ecb81] focus:outline-none transition" />
            <span class="absolute right-3 top-2.5 text-slate-500 dark:text-slate-400 font-mono font-bold text-xs">
              {{ side === 'buy' ? 'USDT' : market?.base_currency }}
            </span>
          </div>
        </div>

        <!-- Calculated Receive/Sell View -->
        <div class="bg-slate-100 dark:bg-[#0b0e11] border border-slate-300 dark:border-[#2b3139] p-3 rounded-xl font-mono text-xs flex justify-between items-center">
          <span class="text-slate-700 dark:text-slate-300 font-bold">
            Est. {{ side === 'buy' ? market?.base_currency : 'USDT' }} to Receive:
          </span>
          <span class="text-emerald-700 dark:text-[#0ecb81] font-black text-xs sm:text-sm">
            {{ side === 'buy' ? calculatedQuantity.toFixed(6) + ' ' + market?.base_currency : totalCost + ' USDT' }}
          </span>
        </div>

        <!-- Percentage Slider Buttons (High Contrast) -->
        <div class="grid grid-cols-4 gap-1.5 pt-0.5">
          <button v-for="pct in [25, 50, 75, 100]" :key="pct" type="button" @click="setPercentage(pct)"
                  class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold font-mono border border-slate-300 dark:border-slate-700 py-1.5 rounded-lg text-[11px] transition shadow-sm">
            {{ pct === 100 ? '100% (MAX)' : `${pct}%` }}
          </button>
        </div>

        <!-- Order Total Calculation (High Contrast) -->
        <div class="flex justify-between items-center pt-2 font-mono border-t border-slate-200 dark:border-[#2b3139]">
          <span class="text-slate-700 dark:text-slate-300 font-bold text-xs">Est. Total:</span>
          <span class="text-slate-900 dark:text-white font-black text-base sm:text-lg">${{ totalCost }}</span>
        </div>
      </div>

      <!-- Execution Submit Button -->
      <div>
        <button type="submit" :disabled="loading"
                :class="side === 'buy' ? 'bg-[#0ecb81] hover:bg-[#0bb573] text-[#1e2329]' : 'bg-[#f6465d] hover:bg-[#e0384f] text-white'"
                class="w-full py-3 rounded-xl font-black uppercase tracking-wider text-sm transition shadow-lg disabled:opacity-50 flex items-center justify-center space-x-2">
          <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-if="loading">Processing Order...</span>
          <span v-else>{{ side === 'buy' ? 'BUY' : 'SELL' }} {{ market?.base_currency }}</span>
        </button>

        <div class="mt-3 bg-amber-100 dark:bg-amber-500/10 border border-amber-300 dark:border-amber-500/30 rounded-xl p-2.5 text-[11px] text-amber-900 dark:text-amber-300 font-medium flex items-start space-x-2 leading-relaxed">
          <span class="text-amber-600 dark:text-amber-400 shrink-0 mt-0.5">⚠️</span>
          <span>
            <strong class="font-bold">Trading Notice:</strong> Selling returns assets directly to your USDT funds balance via double-entry ledger settlement.
          </span>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
  market: Object,
  wallets: Array,
  selectedPrice: [Number, String],
  accountMode: String,
});

const emit = defineEmits(['order-placed']);

const toastRef = ref(null);
const activeAccountMode = computed(() => props.accountMode || localStorage.getItem('trade_account_mode') || 'demo');
const side = ref('buy');
const type = ref('limit');
const price = ref(props.market?.last_price || 0);
const userEditedPrice = ref(false);
const orderAmount = ref('50');
const loading = ref(false);
const quickLiquidating = ref(false);

const calculatedQuantity = computed(() => {
  const u = parseFloat(orderAmount.value) || 0;
  if (side.value === 'buy') {
    const p = type.value === 'market' ? (props.market?.last_price || 1) : (parseFloat(price.value) || 1);
    if (p <= 0) return 0;
    return u / p;
  } else {
    return u; // For sell, the input IS the base quantity!
  }
});

watch(() => props.market?.id, () => {
  userEditedPrice.value = false;
  price.value = props.market?.last_price;
});

watch(() => props.market?.last_price, (newVal) => {
  if (!userEditedPrice.value || type.value === 'market') {
    price.value = newVal;
  }
});

watch(() => props.selectedPrice, (newVal) => {
  if (newVal) {
    userEditedPrice.value = true;
    price.value = newVal;
    type.value = 'limit';
  }
});

const isDemoMode = computed(() => activeAccountMode.value === 'demo');

const baseWallet = computed(() => (props.wallets || []).find(w => w.currency === props.market?.base_currency && (isDemoMode.value ? w.is_demo : !w.is_demo)));
const quoteWallet = computed(() => (props.wallets || []).find(w => w.currency === props.market?.quote_currency && (isDemoMode.value ? w.is_demo : !w.is_demo)));

const totalCost = computed(() => {
  const u = parseFloat(orderAmount.value) || 0;
  if (side.value === 'buy') {
    return u.toFixed(2);
  } else {
    const p = type.value === 'market' ? (props.market?.last_price || 1) : (parseFloat(price.value) || 1);
    return (u * p).toFixed(2);
  }
});

function setPercentage(pct) {
  if (side.value === 'buy') {
    const availUSDT = quoteWallet.value?.available_balance || 0;
    orderAmount.value = (availUSDT * (pct / 100)).toFixed(2);
  } else {
    const availBase = baseWallet.value?.available_balance || 0;
    orderAmount.value = (availBase * (pct / 100)).toFixed(6);
  }
}

async function quickSellAllToFunds() {
  const availBase = baseWallet.value?.available_balance || 0;
  if (availBase <= 0) return;

  quickLiquidating.value = true;
  loading.value = true;

  try {
    const response = await axios.post('/api/orders', {
      market_id: props.market.id,
      side: 'sell',
      type: 'market',
      strike_price: props.market?.last_price,
      quantity: availBase,
      is_demo: isDemoMode.value,
    });
    toastRef.value?.show(`Sold ${availBase} ${props.market?.base_currency} to USDT funds balance.`, 'success');
    orderAmount.value = '';
    emit('order-placed');
  } catch (err) {
    toastRef.value?.show(err.response?.data?.message || 'Failed to liquidate asset.', 'error');
  } finally {
    loading.value = false;
    quickLiquidating.value = false;
  }
}

function formatBalance(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
}

async function submitOrder() {
  const q = calculatedQuantity.value;
  if (q <= 0) {
    toastRef.value?.show('Please enter a valid amount.', 'error');
    return;
  }
  loading.value = true;
  try {
    const response = await axios.post('/api/orders', {
      market_id: props.market.id,
      side: side.value,
      type: type.value,
      price: type.value === 'limit' ? price.value : null,
      strike_price: props.market?.last_price,
      quantity: q.toFixed(6),
      is_demo: isDemoMode.value,
    });
    toastRef.value?.show(response.data.message, 'success');
    orderAmount.value = '';
    emit('order-placed');
  } catch (err) {
    toastRef.value?.show(err.response?.data?.message || 'Failed to submit order.', 'error');
  } finally {
    loading.value = false;
  }
}
</script>
