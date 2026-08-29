<template>
  <div class="bg-slate-900 border border-slate-800 rounded-lg flex flex-col h-full overflow-hidden text-xs select-none">
    <ToastNotification ref="toastRef" />

    <div class="flex items-center justify-between px-3 py-2 border-b border-slate-800">
      <div class="flex space-x-4 font-medium text-[11px]">
        <button @click="activeTab = 'open'" :class="activeTab === 'open' ? 'text-emerald-400 font-bold border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-slate-200'">
          Open Orders ({{ filteredOrders.length }})
        </button>

        <button @click="activeTab = 'positions'" :class="activeTab === 'positions' ? 'text-emerald-400 font-bold border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-slate-200'" class="flex items-center space-x-1">
          <span>Holdings & Positions ({{ activeHoldings.length }})</span>
          <span v-if="activeHoldings.length > 0" class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
        </button>

        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'text-emerald-400 font-bold border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-slate-200'">
          Order History
        </button>
      </div>

      <span v-if="activeTab === 'positions'" class="text-[10px] text-slate-400 font-mono">
        Live Market Value P&L Settlement
      </span>
    </div>

    <!-- TAB 1: OPEN ORDERS & TAB 3: ORDER HISTORY -->
    <template v-if="activeTab === 'open' || activeTab === 'history'">
      <!-- Table Header -->
      <div class="grid grid-cols-6 px-3 py-1.5 text-[11px] text-slate-500 font-medium border-b border-slate-800/60">
        <span>Market</span>
        <span>Side / Type</span>
        <span>Price</span>
        <span>Quantity</span>
        <span>Filled</span>
        <span class="text-right">Action</span>
      </div>

      <div class="flex-1 overflow-y-auto divide-y divide-slate-900/40 font-mono">
        <div v-for="o in (activeTab === 'open' ? filteredOrders : filteredHistory)" :key="o.id"
             class="grid grid-cols-6 px-3 py-2 items-center hover:bg-slate-800/30 transition">
          <span class="font-semibold text-slate-200">{{ o.market?.symbol }}</span>
          <span :class="o.side === 'buy' ? 'text-emerald-400' : 'text-rose-400'" class="uppercase text-[11px] font-bold">
            {{ o.side }} / {{ o.type }}
          </span>
          <span class="text-slate-200">${{ formatPrice(o.price) }}</span>
          <span class="text-slate-300">{{ Number(o.quantity).toFixed(4) }}</span>
          <span class="text-slate-400">{{ Number(o.filled_quantity).toFixed(4) }}</span>
          <div class="text-right">
            <button v-if="o.status === 'open' || o.status === 'partially_filled'"
                    @click="cancelOrder(o.id)" :disabled="processingId === o.id"
                    class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 px-2.5 py-1 rounded text-[10px] font-bold transition inline-flex items-center space-x-1">
              <svg v-if="processingId === o.id" class="animate-spin h-3 w-3 text-rose-400 mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Close Order</span>
            </button>
            <span v-else class="text-slate-500 text-[10px] capitalize">{{ o.status }}</span>
          </div>
        </div>

        <div v-if="(activeTab === 'open' ? orders : history).length === 0" class="p-6 text-center text-slate-500">
          No {{ activeTab }} orders found.
        </div>
      </div>
    </template>

    <!-- TAB 2: HOLDINGS & ACTIVE POSITIONS (REAL-TIME P&L GAIN/LOSS TRACKING) -->
    <template v-else-if="activeTab === 'positions'">
      <div class="grid grid-cols-7 px-3 py-1.5 text-[11px] text-slate-500 font-medium border-b border-slate-800/60">
        <span>Asset</span>
        <span>Holdings Qty</span>
        <span>Avg Buy Price</span>
        <span>Live Price</span>
        <span>Market Value</span>
        <span>Unrealized P&L</span>
        <span class="text-right">Action</span>
      </div>

      <div class="flex-1 overflow-y-auto divide-y divide-slate-900/40 font-mono">
        <div v-for="h in activeHoldings" :key="h.currency"
             class="grid grid-cols-7 px-3 py-2 items-center hover:bg-slate-800/30 transition">
          <span class="font-bold text-slate-100">{{ h.currency }}/USDT</span>
          <span class="text-slate-300 font-bold">{{ formatBalance(h.amount) }} {{ h.currency }}</span>
          <span class="text-slate-400">${{ formatPrice(h.avgBuyPrice) }}</span>
          <span class="text-slate-100 font-bold">${{ formatPrice(h.currentPrice) }}</span>
          <span class="text-slate-100 font-bold">${{ formatPrice(h.marketValue) }}</span>
          
          <!-- Live P&L Gain/Loss Indicator -->
          <div class="font-bold text-[11px]">
            <span :class="h.pnlAmount >= 0 ? 'text-emerald-400' : 'text-rose-400'">
              {{ h.pnlAmount >= 0 ? '+' : '' }}${{ formatPrice(h.pnlAmount) }}
              ({{ h.pnlAmount >= 0 ? '+' : '' }}{{ h.pnlPercent.toFixed(2) }}%)
            </span>
          </div>

          <!-- Close Position (Sell at Market to USDT Funds) -->
          <div class="text-right">
            <button @click="closePosition(h)" :disabled="processingId === h.currency"
                    class="bg-rose-600 hover:bg-rose-500 text-white font-bold px-2.5 py-1 rounded text-[10px] transition shadow flex items-center justify-end space-x-1 ml-auto">
              <svg v-if="processingId === h.currency" class="animate-spin h-3 w-3 text-white mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Close Position</span>
            </button>
          </div>
        </div>

        <div v-if="activeHoldings.length === 0" class="p-6 text-center text-slate-500">
          No open positions or asset holdings. Purchase crypto/stocks to track live P&L gain/loss.
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
  orders: { type: Array, default: () => [] },
  history: { type: Array, default: () => [] },
  wallets: { type: Array, default: () => [] },
  markets: { type: Array, default: () => [] },
  accountMode: String,
});

const emit = defineEmits(['order-cancelled']);

const toastRef = ref(null);
const activeTab = ref('open');
const processingId = ref(null);

const activeAccountMode = computed(() => props.accountMode || localStorage.getItem('trade_account_mode') || 'demo');
const isDemoMode = computed(() => activeAccountMode.value === 'demo');

const filteredOrders = computed(() => {
  return (props.orders || []).filter(o => (isDemoMode.value ? o.is_demo : !o.is_demo));
});

const filteredHistory = computed(() => {
  return (props.history || []).filter(o => (isDemoMode.value ? o.is_demo : !o.is_demo));
});

const filteredWallets = computed(() => {
  return (props.wallets || []).filter(w => (isDemoMode.value ? w.is_demo : !w.is_demo));
});

const activeHoldings = computed(() => {
  const holdings = [];

  (filteredWallets.value || []).forEach(w => {
    const qty = parseFloat(w.available_balance || 0);
    if (w.currency === 'USDT' || qty <= 0) return;

    const market = (props.markets || []).find(m => m.base_currency === w.currency);
    if (!market) return;

    const currentPrice = parseFloat(market.last_price || 0);
    const marketValue = qty * currentPrice;

    const buyOrders = (props.history || []).filter(o => o.market_id === market.id && o.side === 'buy' && o.status === 'filled');
    let avgBuyPrice = currentPrice;
    if (buyOrders.length > 0) {
      const totalCost = buyOrders.reduce((acc, o) => acc + (parseFloat(o.price) * parseFloat(o.filled_quantity)), 0);
      const totalQty = buyOrders.reduce((acc, o) => acc + parseFloat(o.filled_quantity), 0);
      if (totalQty > 0) {
        avgBuyPrice = totalCost / totalQty;
      }
    }

    const pnlAmount = (currentPrice - avgBuyPrice) * qty;
    const pnlPercent = avgBuyPrice > 0 ? ((currentPrice - avgBuyPrice) / avgBuyPrice) * 100 : 0;

    holdings.push({
      currency: w.currency,
      amount: qty,
      marketId: market.id,
      symbol: market.symbol,
      currentPrice,
      avgBuyPrice,
      marketValue,
      pnlAmount,
      pnlPercent,
    });
  });

  return holdings;
});

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatBalance(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
}

async function cancelOrder(id) {
  processingId.value = id;
  try {
    const res = await axios.delete(`/api/orders/${id}`);
    toastRef.value?.show(res.data.message || 'Order closed successfully.', 'success');
    emit('order-cancelled');
  } catch (err) {
    toastRef.value?.show(err.response?.data?.message || 'Error closing order.', 'error');
  } finally {
    processingId.value = null;
  }
}

async function closePosition(holding) {
  processingId.value = holding.currency;
  try {
    await axios.post('/api/orders', {
      market_id: holding.marketId,
      side: 'sell',
      type: 'market',
      quantity: holding.amount,
    });

    const gainLossText = holding.pnlAmount >= 0 
      ? `+$${formatPrice(holding.pnlAmount)} PROFIT`
      : `-$${formatPrice(Math.abs(holding.pnlAmount))} LOSS`;

    toastRef.value?.show(`Closed ${holding.currency} position! Credited $${formatPrice(holding.marketValue)} USDT (${gainLossText}).`, 'success');
    emit('order-cancelled');
  } catch (err) {
    toastRef.value?.show(err.response?.data?.message || 'Error closing position.', 'error');
  } finally {
    processingId.value = null;
  }
}
</script>
