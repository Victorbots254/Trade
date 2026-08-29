<template>
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-3 flex flex-col h-full text-xs select-none">
    <ToastNotification ref="toastRef" />

    <div class="mb-3 p-2 rounded-md font-mono text-[11px] flex justify-between items-center"
         :class="activeAccountMode === 'demo' ? 'bg-amber-100 dark:bg-[#f0b90b]/10 border border-amber-300 dark:border-[#f0b90b]/30 text-amber-700 dark:text-[#f0b90b]' : 'bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-300 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300'">
      <div class="flex items-center space-x-1.5 font-bold">
        <span v-if="activeAccountMode === 'demo'">🎮 DEMO DASHBOARD</span>
        <span v-else class="flex items-center space-x-1">
          <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
          <span>🟢 LIVE REAL TRADING</span>
        </span>
      </div>
      <span class="text-[10px] opacity-80">{{ activeAccountMode === 'demo' ? 'Virtual Funds' : 'Real Capital' }}</span>
    </div>

    <!-- Side Toggle: BUY vs SELL -->
    <div class="grid grid-cols-2 gap-1 bg-slate-950 p-1 rounded-md border border-slate-800 mb-3">
      <button @click="side = 'buy'" 
              :class="side === 'buy' ? 'bg-emerald-600 text-white font-bold shadow shadow-emerald-900/50' : 'text-slate-400 hover:text-slate-200'"
              class="py-1.5 rounded transition text-center uppercase tracking-wide">
        Buy {{ market?.base_currency }}
      </button>
      <button @click="side = 'sell'" 
              :class="side === 'sell' ? 'bg-rose-600 text-white font-bold shadow shadow-rose-900/50' : 'text-slate-400 hover:text-slate-200'"
              class="py-1.5 rounded transition text-center uppercase tracking-wide">
        Sell {{ market?.base_currency }}
      </button>
    </div>

    <!-- Order Type: Limit vs Market -->
    <div class="flex items-center justify-between mb-3 border-b border-slate-800 pb-2 text-[11px]">
      <div class="flex space-x-3">
        <button @click="type = 'limit'" :class="type === 'limit' ? 'text-emerald-400 font-bold border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-slate-200'">Limit Order</button>
        <button @click="type = 'market'" :class="type === 'market' ? 'text-emerald-400 font-bold border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-slate-200'">Market Order</button>
      </div>

      <button v-if="side === 'sell' && (baseWallet?.available_balance || 0) > 0"
              @click="quickSellAllToFunds" type="button" :disabled="loading"
              class="text-[10px] text-amber-400 bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/20 px-2 py-0.5 rounded font-mono font-bold flex items-center space-x-1">
        <svg v-if="quickLiquidating" class="animate-spin h-3 w-3 text-amber-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Liquidate All to USDT</span>
      </button>
    </div>

    <!-- Available Balance Indicator -->
    <div class="flex justify-between items-center text-slate-400 mb-2 font-mono text-[11px]">
      <span>Avail Balance:</span>
      <span class="text-slate-200 font-semibold">
        {{ side === 'buy' ? `${formatBalance(quoteWallet?.available_balance)} ${market?.quote_currency}` : `${formatBalance(baseWallet?.available_balance)} ${market?.base_currency}` }}
      </span>
    </div>

    <!-- Inputs Form -->
    <form @submit.prevent="submitOrder" class="space-y-3 flex-1 flex flex-col justify-between">
      <div class="space-y-2.5">
          <!-- Price Input -->
          <div v-if="type === 'limit'">
            <label class="block text-slate-500 dark:text-slate-400 text-[11px] mb-1">Price ({{ market?.quote_currency }})</label>
            <div class="relative">
              <input v-model="price" @input="userEditedPrice = true" type="number" step="any" min="0.000001" required
                     class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded px-2.5 py-1.5 font-mono text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:border-emerald-500" />
              <span class="absolute right-2.5 top-2 text-slate-400 dark:text-slate-500 font-mono text-[11px]">{{ market?.quote_currency }}</span>
            </div>
          </div>

          <div v-else class="bg-slate-100 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-800 rounded p-2 text-slate-500 dark:text-slate-400 text-center font-mono">
            Market Price (Best Execution)
          </div>

          <!-- Amount Input -->
          <div>
            <label class="block text-slate-500 dark:text-slate-400 text-[11px] mb-1">
              Amount ({{ side === 'buy' ? 'USDT' : market?.base_currency }})
            </label>
            <div class="relative">
              <input v-model="orderAmount" type="number" step="any" min="0.000001" required
                     class="w-full bg-white dark:bg-[#0b0e11] border border-slate-300 dark:border-[#2b3139] rounded-lg px-2.5 py-1.5 font-mono text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:border-emerald-500 dark:focus:border-[#f0b90b] transition" />
              <span class="absolute right-2.5 top-2 text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                {{ side === 'buy' ? 'USDT' : market?.base_currency }}
              </span>
            </div>
          </div>

        <!-- Calculated Receive/Sell View -->
        <div class="bg-slate-100 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-800 p-2.5 rounded font-mono text-[11px] flex justify-between items-center">
          <span class="text-slate-600 dark:text-slate-400">
            Est. {{ side === 'buy' ? market?.base_currency : 'USDT' }} to Receive:
          </span>
          <span class="text-emerald-700 dark:text-emerald-400 font-bold">
            {{ side === 'buy' ? calculatedQuantity.toFixed(6) + ' ' + market?.base_currency : totalCost + ' USDT' }}
          </span>
        </div>

        <!-- Percentage Slider Buttons -->
        <div class="grid grid-cols-4 gap-1 pt-1">
          <button v-for="pct in [25, 50, 75, 100]" :key="pct" type="button" @click="setPercentage(pct)"
                  class="bg-slate-950 hover:bg-slate-800 text-slate-400 border border-slate-800 py-1 rounded text-[10px] font-mono transition">
            {{ pct === 100 ? '100% (MAX)' : `${pct}%` }}
          </button>
        </div>

        <!-- Order Total Calculation -->
        <div class="flex justify-between items-center pt-2 text-slate-400 font-mono">
          <span>Est. Total:</span>
          <span class="text-slate-100 font-bold text-sm">${{ totalCost }}</span>
        </div>
      </div>

      <div>
        <button type="submit" :disabled="loading"
                :class="side === 'buy' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-rose-600 hover:bg-rose-500'"
                class="w-full py-2.5 rounded font-bold text-white uppercase tracking-wider text-xs transition shadow-md disabled:opacity-50 flex items-center justify-center space-x-2">
          <svg v-if="loading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-if="loading" class="text-white font-bold">Processing Order...</span>
          <span v-else class="text-white font-bold">{{ side }} {{ market?.base_currency }}</span>
        </button>

        <div class="mt-3 bg-amber-500/10 border border-amber-500/30 rounded p-2 text-[10px] text-amber-400 font-medium flex items-start space-x-2">
          <span class="text-amber-400 mt-0.5">⚠️</span>
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
