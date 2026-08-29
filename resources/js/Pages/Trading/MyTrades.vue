<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col pb-12">
    <ToastNotification ref="toastRef" />

    <TradingHeader 
      :user="$page.props.auth.user"
      :markets="markets"
      :wallets="wallets"
      @account-mode-changed="(mode) => accountMode = mode"
    />

    <!-- Main Workspace Container -->
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
      <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-slate-800 pb-4 gap-2">
        <div>
          <h1 class="text-xl font-bold text-slate-100 flex items-center space-x-2">
            <span>📋 My Trades & Activity Portal</span>
          </h1>
          <p class="text-xs text-slate-400">View and manage all your live open positions, active countdowns, holdings, and historical trade settlements.</p>
        </div>

        <!-- Navigation Action Buttons -->
        <div class="flex items-center space-x-2 font-medium text-xs">
          <Link href="/terminal" class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-3.5 py-2 rounded-lg transition">
            Spot Terminal →
          </Link>
          <Link href="/trade/options/BTC_USDT" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3.5 py-2 rounded-lg transition shadow">
            Quick Options →
          </Link>
        </div>
      </div>

      <!-- Tab Selection Bar -->
      <div class="flex space-x-2 bg-slate-900 p-1.5 rounded-xl border border-slate-800 font-medium text-xs overflow-x-auto">
        <button @click="activeTab = 'active'" 
                :class="activeTab === 'active' ? 'bg-emerald-600 text-white font-bold shadow' : 'text-slate-400 hover:text-slate-200'"
                class="px-4 py-2 rounded-lg transition flex items-center space-x-2 whitespace-nowrap">
          <span>⚡ Active Positions & Open Orders</span>
          <span class="bg-slate-950 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold">{{ totalActiveCount }}</span>
        </button>
        <button @click="activeTab = 'settled_options'" 
                :class="activeTab === 'settled_options' ? 'bg-emerald-600 text-white font-bold shadow' : 'text-slate-400 hover:text-slate-200'"
                class="px-4 py-2 rounded-lg transition flex items-center space-x-2 whitespace-nowrap">
          <span>📊 Settled Options History</span>
          <span class="bg-slate-950 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold">{{ filteredSettledOptions.length }}</span>
        </button>
        <button @click="activeTab = 'spot_history'" 
                :class="activeTab === 'spot_history' ? 'bg-emerald-600 text-white font-bold shadow' : 'text-slate-400 hover:text-slate-200'"
                class="px-4 py-2 rounded-lg transition flex items-center space-x-2 whitespace-nowrap">
          <span>📜 Completed Spot Orders</span>
          <span class="bg-slate-950 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold">{{ filteredOrderHistory.length }}</span>
        </button>
      </div>

      <!-- TAB 1: ACTIVE POSITIONS & OPEN ORDERS -->
      <div v-if="activeTab === 'active'" class="space-y-6">
        <!-- Section A: Active Options Positions (Countdowns & Live P&L) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4 text-xs">
          <div class="flex justify-between items-center border-b border-slate-800 pb-3">
            <h3 class="font-bold text-slate-100 text-sm flex items-center space-x-2">
              <span>Active Options Positions ({{ filteredActiveOptions.length }})</span>
              <span class="text-[10px] text-emerald-400 font-mono bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded font-bold">AUTO-SETTLING</span>
            </h3>
            <span class="text-slate-500 font-mono">Payout: +88% Profit</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono">
              <thead>
                <tr class="text-slate-500 border-b border-slate-800 pb-2">
                  <th>Market</th>
                  <th>Direction</th>
                  <th>Entry Price</th>
                  <th>Current Price</th>
                  <th>Investment</th>
                  <th>Timer</th>
                  <th class="text-right">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="c in filteredActiveOptions" :key="c.id" class="hover:bg-slate-800/30 transition">
                  <td class="py-3 font-bold text-slate-100">{{ c.market?.symbol }}</td>
                  <td class="py-3">
                    <span :class="c.direction === 'higher' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30' : 'text-rose-400 bg-rose-500/10 border-rose-500/30'" class="px-2 py-0.5 rounded border uppercase font-bold text-[10px]">
                      {{ c.direction === 'higher' ? 'HIGHER ⬆' : 'LOWER ⬇' }}
                    </span>
                  </td>
                  <td class="py-3 text-slate-300">${{ formatPrice(c.entry_price) }}</td>
                  <td class="py-3 font-bold text-slate-100">${{ formatPrice(getContractCurrentPrice(c)) }}</td>
                  <td class="py-3 text-emerald-400 font-bold">${{ formatPrice(c.investment_amount) }}</td>
                  <td class="py-3 text-amber-400 font-bold font-mono">
                    {{ formatCountdown(c.remaining_seconds) }}
                  </td>
                  <td class="py-3 text-right font-bold">
                    <span v-if="isContractWinning(c)" class="text-emerald-400">WINNING (+${{ formatPrice(c.payout_amount - c.investment_amount) }})</span>
                    <span v-else class="text-rose-400">OUT OF MONEY</span>
                  </td>
                </tr>
                <tr v-if="filteredActiveOptions.length === 0">
                  <td colspan="7" class="py-6 text-center text-slate-500">No active option contracts running.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Section B: Open Spot Orders -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4 text-xs">
          <div class="flex justify-between items-center border-b border-slate-800 pb-3">
            <h3 class="font-bold text-slate-100 text-sm">Open Spot Limit Orders ({{ filteredOpenOrders.length }})</h3>
            <span class="text-slate-500 font-mono">Unfilled Spot Buy & Sell Orders</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono">
              <thead>
                <tr class="text-slate-500 border-b border-slate-800 pb-2">
                  <th>Market</th>
                  <th>Side / Type</th>
                  <th>Order Price</th>
                  <th>Quantity</th>
                  <th>Filled Amount</th>
                  <th>Created Date</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="o in filteredOpenOrders" :key="o.id" class="hover:bg-slate-800/30 transition">
                  <td class="py-3 font-bold text-slate-100">{{ o.market?.symbol }}</td>
                  <td class="py-3 uppercase font-bold" :class="o.side === 'buy' ? 'text-emerald-400' : 'text-rose-400'">
                    {{ o.side }} / {{ o.type }}
                  </td>
                  <td class="py-3 text-slate-200">${{ formatPrice(o.price) }}</td>
                  <td class="py-3 text-slate-300">{{ Number(o.quantity).toFixed(4) }}</td>
                  <td class="py-3 text-slate-400">{{ Number(o.filled_quantity).toFixed(4) }}</td>
                  <td class="py-3 text-slate-500">{{ formatDate(o.created_at) }}</td>
                  <td class="py-3 text-right">
                    <button @click="cancelOrder(o.id)" :disabled="processingId === o.id"
                            class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 px-3 py-1 rounded-lg font-bold transition flex items-center space-x-1 ml-auto">
                      <svg v-if="processingId === o.id" class="animate-spin h-3 w-3 text-rose-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span>Close Order</span>
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredOpenOrders.length === 0">
                  <td colspan="7" class="py-6 text-center text-slate-500">No open spot orders.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 2: SETTLED OPTIONS HISTORY -->
      <div v-else-if="activeTab === 'settled_options'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h3 class="font-bold text-slate-100 text-sm">Settled Options Contracts History</h3>
          <span class="text-slate-500 font-mono">1m - 1h Completed Contracts</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-slate-500 border-b border-slate-800 pb-2">
                <th>Market</th>
                <th>Direction</th>
                <th>Entry Price</th>
                <th>Strike Price</th>
                <th>Investment</th>
                <th>Payout</th>
                <th class="text-right">Outcome</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="c in filteredSettledOptions" :key="c.id" class="hover:bg-slate-800/30 transition">
                <td class="py-3 font-bold text-slate-100">{{ c.market?.symbol }}</td>
                <td class="py-3">
                  <span :class="c.direction === 'higher' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30' : 'text-rose-400 bg-rose-500/10 border-rose-500/30'" class="px-2 py-0.5 rounded border uppercase font-bold text-[10px]">
                    {{ c.direction === 'higher' ? 'HIGHER ⬆' : 'LOWER ⬇' }}
                  </span>
                </td>
                <td class="py-3 text-slate-300">${{ formatPrice(c.entry_price) }}</td>
                <td class="py-3 font-bold text-slate-100">${{ formatPrice(c.strike_price || c.entry_price) }}</td>
                <td class="py-3 text-slate-200">${{ formatPrice(c.investment_amount) }}</td>
                <td class="py-3 font-bold" :class="c.status === 'win' ? 'text-emerald-400' : 'text-slate-500'">
                  ${{ formatPrice(c.status === 'win' ? c.payout_amount : 0) }}
                </td>
                <td class="py-3 text-right">
                  <span v-if="c.status === 'win'" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2.5 py-1 rounded font-bold">
                    🎉 WIN (+${{ formatPrice(c.payout_amount - c.investment_amount) }})
                  </span>
                  <span v-else class="bg-rose-500/10 text-rose-400 border border-rose-500/30 px-2.5 py-1 rounded font-bold">
                    ❌ LOSS
                  </span>
                </td>
              </tr>
              <tr v-if="filteredSettledOptions.length === 0">
                <td colspan="7" class="py-8 text-center text-slate-500">No settled options contracts history found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: COMPLETED SPOT ORDERS -->
      <div v-else-if="activeTab === 'spot_history'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h3 class="font-bold text-slate-100 text-sm">Completed Spot Orders History</h3>
          <span class="text-slate-500 font-mono">Filled & Cancelled Spot Orders</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-slate-500 border-b border-slate-800 pb-2">
                <th>Market</th>
                <th>Side / Type</th>
                <th>Execution Price</th>
                <th>Quantity</th>
                <th>Total Value</th>
                <th class="text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="o in filteredOrderHistory" :key="o.id" class="hover:bg-slate-800/30 transition">
                <td class="py-3 font-bold text-slate-100">{{ o.market?.symbol }}</td>
                <td class="py-3 uppercase font-bold" :class="o.side === 'buy' ? 'text-emerald-400' : 'text-rose-400'">
                  {{ o.side }} / {{ o.type }}
                </td>
                <td class="py-3 text-slate-200">${{ formatPrice(o.price) }}</td>
                <td class="py-3 text-slate-300">{{ Number(o.filled_quantity || o.quantity).toFixed(4) }}</td>
                <td class="py-3 text-slate-100 font-bold">${{ formatPrice((o.price || 0) * (o.filled_quantity || o.quantity)) }}</td>
                <td class="py-3 text-right">
                  <span :class="o.status === 'filled' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700'"
                        class="px-2.5 py-1 rounded font-bold capitalize">
                    {{ o.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredOrderHistory.length === 0">
                <td colspan="6" class="py-8 text-center text-slate-500">No completed spot order history found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Bottom Live Right-to-Left Ticker Bar -->
    <BottomMarketTicker :markets="markets" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import axios from 'axios';

import TradingHeader from '@/Components/TradingHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import WithdrawalTicker from '@/Components/WithdrawalTicker.vue';
import BottomMarketTicker from '@/Components/BottomMarketTicker.vue';

const props = defineProps({
  openOrders: Array,
  orderHistory: Array,
  activeOptions: Array,
  settledOptions: Array,
  wallets: Array,
  markets: Array,
});

const page = usePage();
const toastRef = ref(null);
const activeTab = ref('active');
const processingId = ref(null);

const accountMode = ref(localStorage.getItem('trade_account_mode') || 'demo');
const isDemoMode = computed(() => accountMode.value === 'demo');

const userTradingMode = ref(page.props.auth.user?.trading_outcome_mode || 'fair_market');
const openOrdersList = ref(props.openOrders || []);
const orderHistoryList = ref(props.orderHistory || []);
const settledOptionsList = ref(props.settledOptions || []);

const activeOptionsList = ref((props.activeOptions || []).map(c => {
  const expires = new Date(c.expires_at).getTime();
  const now = Date.now();
  c.remaining_seconds = Math.max(0, Math.floor((expires - now) / 1000));
  return c;
}));

const filteredOpenOrders = computed(() => {
  return openOrdersList.value.filter(o => (isDemoMode.value ? o.is_demo : !o.is_demo));
});

const filteredOrderHistory = computed(() => {
  return orderHistoryList.value.filter(o => (isDemoMode.value ? o.is_demo : !o.is_demo));
});

const filteredActiveOptions = computed(() => {
  return activeOptionsList.value.filter(o => (isDemoMode.value ? o.is_demo : !o.is_demo));
});

const filteredSettledOptions = computed(() => {
  return settledOptionsList.value.filter(o => (isDemoMode.value ? o.is_demo : !o.is_demo));
});

const totalActiveCount = computed(() => {
  return filteredActiveOptions.value.length + filteredOpenOrders.value.length;
});

let timerInterval = null;
let pollInterval = null;

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString();
}

function formatCountdown(sec) {
  if (sec <= 0) return 'SETTLING...';
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

function getContractCurrentPrice(contract) {
  const mode = userTradingMode.value;
  const market = (props.markets || []).find(m => m.id === contract.market_id);
  const realPrice = market ? parseFloat(market.last_price || contract.entry_price) : parseFloat(contract.entry_price);

  if (mode === 'fair_market') {
    return realPrice;
  }

  const entry = parseFloat(contract.entry_price);
  const delta = Math.max(entry * 0.005, 10.0);
  const noise = (Math.random() * (entry * 0.0005));

  if (mode === 'force_win') {
    if (contract.direction === 'higher') {
      return parseFloat((entry + delta + noise).toFixed(2));
    } else {
      return parseFloat((entry - delta - noise).toFixed(2));
    }
  } else if (mode === 'force_loss') {
    if (contract.direction === 'higher') {
      return parseFloat((entry - delta - noise).toFixed(2));
    } else {
      return parseFloat((entry + delta + noise).toFixed(2));
    }
  }

  return realPrice;
}

function isContractWinning(contract) {
  const mode = userTradingMode.value;
  if (mode === 'force_win') return true;
  if (mode === 'force_loss') return false;

  const curPrice = getContractCurrentPrice(contract);
  const entry = parseFloat(contract.entry_price);
  if (contract.direction === 'higher') {
    return curPrice > entry;
  }
  return curPrice < entry;
}

async function cancelOrder(id) {
  processingId.value = id;
  try {
    const res = await axios.delete(`/api/orders/${id}`);
    toastRef.value?.show(res.data.message || 'Order cancelled successfully.', 'success');
    openOrdersList.value = openOrdersList.value.filter(o => o.id !== id);
  } catch (err) {
    toastRef.value?.show(err.response?.data?.message || 'Failed to cancel order.', 'error');
  } finally {
    processingId.value = null;
  }
}

async function fetchMyTradesData() {
  if (!page.props.auth.user) return;
  try {
    const resUser = await axios.get('/api/user');
    userTradingMode.value = resUser.data.trading_outcome_mode || 'fair_market';
    if (page.props.auth.user) {
      page.props.auth.user.trading_outcome_mode = userTradingMode.value;
    }
  } catch (e) {}
}

async function checkAndSettleContracts() {
  for (let i = activeOptionsList.value.length - 1; i >= 0; i--) {
    const c = activeOptionsList.value[i];
    if (c.remaining_seconds > 0) {
      c.remaining_seconds--;
    } else if (c.status === 'active') {
      c.status = 'settling';
      try {
        const effectiveStrike = getContractCurrentPrice(c);
        const res = await axios.post(`/api/options/${c.id}/settle`, {
          strike_price: effectiveStrike,
        });
        toastRef.value?.show(res.data.message, res.data.contract?.status === 'win' ? 'success' : 'info');
        
        // Move from active options to settled options list
        const settled = res.data.contract;
        if (settled) {
          settledOptionsList.value.unshift(settled);
        }
        activeOptionsList.value.splice(i, 1);
      } catch (e) {}
    }
  }
}

onMounted(() => {
  fetchMyTradesData();
  timerInterval = setInterval(checkAndSettleContracts, 1000);
  pollInterval = setInterval(fetchMyTradesData, 15000);
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  if (pollInterval) clearInterval(pollInterval);
});
</script>
