<template>
  <div class="min-h-screen bg-[#0b0e11] text-slate-300 font-sans selection:bg-emerald-500/30 flex flex-col">
    <!-- Header -->
    <TradingHeader :user="$page.props.auth.user" />
    <ToastNotification ref="toastRef" />

    <div class="flex-1 max-w-[1400px] w-full mx-auto px-4 py-8 space-y-6 overflow-y-auto pb-24">
      
      <!-- Page Title Area -->
      <div class="flex items-center justify-between border-b border-[#2b3139] pb-4">
        <div>
          <h1 class="text-2xl font-black text-white tracking-tight flex items-center space-x-3">
            <svg class="w-7 h-7 text-[#f0b90b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span>My Trades & Portfolio</span>
          </h1>
          <p class="text-sm text-[#848e9c] mt-1">Manage live spot holdings, active options, and view execution history.</p>
        </div>
        <div class="flex space-x-3">
          <Link href="/terminal" class="bg-[#2b3139] hover:bg-[#323942] text-white px-4 py-2 rounded-lg text-sm font-bold transition">Spot Terminal &rarr;</Link>
          <Link href="/trade/options/BTC_USDT" class="bg-[#f0b90b] hover:bg-[#b07e00] text-[#1e2329] px-4 py-2 rounded-lg text-sm font-bold transition">Quick Options &rarr;</Link>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex space-x-2 border-b border-[#2b3139]">
        <button @click="activeTab = 'active'" 
                :class="activeTab === 'active' ? 'border-[#f0b90b] text-[#f0b90b]' : 'border-transparent text-[#848e9c] hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold transition flex items-center space-x-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
          <span>Active Positions & Holdings</span>
          <span v-if="totalActiveCount > 0" class="bg-[#f0b90b] text-[#1e2329] text-[10px] px-1.5 py-0.5 rounded ml-2">{{ totalActiveCount }}</span>
        </button>
        <button @click="activeTab = 'history'" 
                :class="activeTab === 'history' ? 'border-[#f0b90b] text-[#f0b90b]' : 'border-transparent text-[#848e9c] hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold transition flex items-center space-x-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          <span>Trade History</span>
        </button>
      </div>

      <!-- ACTIVE TAB -->
      <div v-if="activeTab === 'active'" class="space-y-6">
        
        <!-- Spot Holdings -->
        <div class="bg-[#1e2329] rounded-xl border border-[#2b3139] overflow-hidden shadow-xl">
          <div class="px-5 py-4 border-b border-[#2b3139] flex justify-between items-center bg-[#181a20]">
            <h3 class="font-bold text-white flex items-center space-x-2">
              <span>Spot Asset Holdings ({{ activeHoldings.length }})</span>
            </h3>
            <span class="text-[11px] text-[#848e9c] uppercase tracking-widest font-bold">Unrealized P&L Tracker</span>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm font-mono whitespace-nowrap">
              <thead class="bg-[#181a20]">
                <tr class="text-[#848e9c] text-[11px] uppercase tracking-wider">
                  <th class="px-5 py-3 font-medium">Asset</th>
                  <th class="px-5 py-3 font-medium">Quantity</th>
                  <th class="px-5 py-3 font-medium">Avg Buy Price</th>
                  <th class="px-5 py-3 font-medium">Current Price</th>
                  <th class="px-5 py-3 font-medium">Market Value</th>
                  <th class="px-5 py-3 font-medium">Unrealized P&L</th>
                  <th class="px-5 py-3 text-right font-medium">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#2b3139]">
                <tr v-for="h in activeHoldings" :key="h.currency" class="hover:bg-[#2b3139]/30 transition group">
                  <td class="px-5 py-4 font-bold text-white flex items-center space-x-2">
                    <img :src="`/images/crypto/${h.currency.toLowerCase()}.png`" @error="$event.target.src='https://cryptologos.cc/logos/bitcoin-btc-logo.svg?v=024'" class="w-5 h-5 rounded-full" />
                    <span>{{ h.currency }}/USDT</span>
                  </td>
                  <td class="px-5 py-4 text-white">{{ h.amount.toFixed(4) }} <span class="text-[#848e9c] text-xs">{{ h.currency }}</span></td>
                  <td class="px-5 py-4 text-slate-300">${{ formatPrice(h.avgBuyPrice) }}</td>
                  <td class="px-5 py-4 text-white">${{ formatPrice(h.currentPrice) }}</td>
                  <td class="px-5 py-4 font-bold text-white">${{ formatPrice(h.marketValue) }}</td>
                  <td class="px-5 py-4 font-bold" :class="h.pnlAmount >= 0 ? 'text-[#0ecb81]' : 'text-[#f6465d]'">
                    {{ h.pnlAmount >= 0 ? '+' : '' }}${{ formatPrice(h.pnlAmount) }} ({{ h.pnlAmount >= 0 ? '+' : '' }}{{ h.pnlPercent.toFixed(2) }}%)
                  </td>
                  <td class="px-5 py-4 text-right">
                    <button @click="closePosition(h)" :disabled="processingId === h.currency"
                            class="bg-[#f6465d] hover:bg-[#f6465d]/80 text-white font-bold px-3 py-1.5 rounded text-xs transition shadow flex items-center justify-end space-x-1 ml-auto disabled:opacity-50">
                      <svg v-if="processingId === h.currency" class="animate-spin h-3 w-3 text-white mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span>Market Sell</span>
                    </button>
                  </td>
                </tr>
                <tr v-if="activeHoldings.length === 0">
                  <td colspan="7" class="py-10 text-center text-[#848e9c]">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                      <span>No active spot holdings found. Purchase assets on the Spot Terminal.</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Active Options Positions -->
        <div class="bg-[#1e2329] rounded-xl border border-[#2b3139] overflow-hidden shadow-xl">
          <div class="px-5 py-4 border-b border-[#2b3139] flex justify-between items-center bg-[#181a20]">
            <h3 class="font-bold text-white flex items-center space-x-2">
              <span>Active Options Contracts ({{ filteredActiveOptions.length }})</span>
              <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded border border-emerald-500/30 tracking-wider">AUTO-SETTLING</span>
            </h3>
            <span class="text-[11px] text-[#848e9c] font-mono">Payout: +88% Profit</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono whitespace-nowrap">
              <thead class="bg-[#181a20]">
                <tr class="text-[#848e9c] text-[11px] uppercase tracking-wider">
                  <th class="px-5 py-3 font-medium">Market</th>
                  <th class="px-5 py-3 font-medium">Direction</th>
                  <th class="px-5 py-3 font-medium">Entry Price</th>
                  <th class="px-5 py-3 font-medium">Live Oracle</th>
                  <th class="px-5 py-3 font-medium">Investment</th>
                  <th class="px-5 py-3 font-medium">Countdown</th>
                  <th class="px-5 py-3 text-right font-medium">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#2b3139]">
                <tr v-for="o in filteredActiveOptions" :key="o.id" class="hover:bg-[#2b3139]/30 transition">
                  <td class="px-5 py-4 font-bold text-white">{{ (markets.find(m => m.id === o.market_id) || {}).symbol || 'Unknown' }}</td>
                  <td class="px-5 py-4">
                    <span :class="o.direction === 'higher' ? 'bg-[#0ecb81]/10 text-[#0ecb81]' : 'bg-[#f6465d]/10 text-[#f6465d]'"
                          class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wider flex items-center w-fit space-x-1">
                      <svg v-if="o.direction === 'higher'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                      <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                      <span>{{ o.direction === 'higher' ? 'CALL' : 'PUT' }}</span>
                    </span>
                  </td>
                  <td class="px-5 py-4 text-slate-300">${{ formatPrice(o.entry_price) }}</td>
                  <td class="px-5 py-4 font-bold" :class="isContractWinning(o) ? 'text-[#0ecb81]' : 'text-[#f6465d]'">
                    ${{ formatPrice(getContractCurrentPrice(o)) }}
                  </td>
                  <td class="px-5 py-4 text-white font-bold">${{ formatPrice(o.amount) }}</td>
                  <td class="px-5 py-4">
                    <span class="bg-[#2b3139] text-[#f0b90b] px-2 py-1 rounded font-bold shadow-inner">
                      {{ formatCountdown(o.remaining_seconds) }}
                    </span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <span v-if="isContractWinning(o)" class="text-[#0ecb81] font-bold text-xs bg-[#0ecb81]/10 px-2 py-1 rounded animate-pulse">Winning</span>
                    <span v-else class="text-[#f6465d] font-bold text-xs bg-[#f6465d]/10 px-2 py-1 rounded">Losing</span>
                  </td>
                </tr>
                <tr v-if="filteredActiveOptions.length === 0">
                  <td colspan="7" class="py-10 text-center text-[#848e9c]">No active binary options running.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Open Limit Orders -->
        <div class="bg-[#1e2329] rounded-xl border border-[#2b3139] overflow-hidden shadow-xl">
          <div class="px-5 py-4 border-b border-[#2b3139] flex justify-between items-center bg-[#181a20]">
            <h3 class="font-bold text-white flex items-center space-x-2">
              <span>Open Spot Limit Orders ({{ filteredOpenOrders.length }})</span>
            </h3>
            <span class="text-[11px] text-[#848e9c] font-mono">Unfilled Spot Buy & Sell Orders</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono whitespace-nowrap">
              <thead class="bg-[#181a20]">
                <tr class="text-[#848e9c] text-[11px] uppercase tracking-wider">
                  <th class="px-5 py-3 font-medium">Market</th>
                  <th class="px-5 py-3 font-medium">Side / Type</th>
                  <th class="px-5 py-3 font-medium">Order Price</th>
                  <th class="px-5 py-3 font-medium">Quantity</th>
                  <th class="px-5 py-3 font-medium">Filled Amount</th>
                  <th class="px-5 py-3 font-medium">Created Date</th>
                  <th class="px-5 py-3 text-right font-medium">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#2b3139]">
                <tr v-for="o in filteredOpenOrders" :key="o.id" class="hover:bg-[#2b3139]/30 transition">
                  <td class="px-5 py-4 font-bold text-white">{{ o.market?.symbol }}</td>
                  <td class="px-5 py-4 uppercase font-bold" :class="o.side === 'buy' ? 'text-[#0ecb81]' : 'text-[#f6465d]'">
                    {{ o.side }} / {{ o.type }}
                  </td>
                  <td class="px-5 py-4 text-slate-300">${{ formatPrice(o.price) }}</td>
                  <td class="px-5 py-4 text-white">{{ Number(o.quantity).toFixed(4) }}</td>
                  <td class="px-5 py-4 text-white">{{ Number(o.filled_quantity || 0).toFixed(4) }}</td>
                  <td class="px-5 py-4 text-[#848e9c] text-sm">{{ formatDate(o.created_at) }}</td>
                  <td class="px-5 py-4 text-right">
                    <button @click="cancelOrder(o.id)" :disabled="processingId === o.id"
                            class="bg-[#2b3139] hover:bg-[#323942] text-white px-3 py-1 rounded text-xs transition border border-[#848e9c]/30 disabled:opacity-50">
                      Cancel
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredOpenOrders.length === 0">
                  <td colspan="7" class="py-10 text-center text-[#848e9c]">No open spot orders waiting to be filled.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- HISTORY TAB -->
      <div v-if="activeTab === 'history'" class="space-y-6">
        
        <!-- Settled Options -->
        <div class="bg-[#1e2329] rounded-xl border border-[#2b3139] overflow-hidden shadow-xl">
          <div class="px-5 py-4 border-b border-[#2b3139] flex justify-between items-center bg-[#181a20]">
            <h3 class="font-bold text-white">Settled Options History ({{ filteredSettledOptions.length }})</h3>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono whitespace-nowrap">
              <thead class="bg-[#181a20]">
                <tr class="text-[#848e9c] text-[11px] uppercase tracking-wider">
                  <th class="px-5 py-3 font-medium">Market</th>
                  <th class="px-5 py-3 font-medium">Direction</th>
                  <th class="px-5 py-3 font-medium">Entry -> Exit Price</th>
                  <th class="px-5 py-3 font-medium">Investment</th>
                  <th class="px-5 py-3 font-medium">Payout</th>
                  <th class="px-5 py-3 font-medium">Settled Time</th>
                  <th class="px-5 py-3 text-right font-medium">Result</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#2b3139]">
                <tr v-for="o in filteredSettledOptions" :key="o.id" class="hover:bg-[#2b3139]/30 transition">
                  <td class="px-5 py-4 font-bold text-white">{{ (markets.find(m => m.id === o.market_id) || {}).symbol || 'Unknown' }}</td>
                  <td class="px-5 py-4">
                    <span :class="o.direction === 'higher' ? 'text-[#0ecb81]' : 'text-[#f6465d]'" class="font-bold uppercase">
                      {{ o.direction === 'higher' ? 'CALL' : 'PUT' }}
                    </span>
                  </td>
                  <td class="px-5 py-4 text-slate-300">
                    <span class="line-through opacity-70">${{ formatPrice(o.entry_price) }}</span> &rarr; 
                    <span class="font-bold">${{ formatPrice(o.settle_price) }}</span>
                  </td>
                  <td class="px-5 py-4 text-white">${{ formatPrice(o.amount) }}</td>
                  <td class="px-5 py-4 font-bold text-white">${{ formatPrice(o.payout || 0) }}</td>
                  <td class="px-5 py-4 text-[#848e9c] text-sm">{{ formatDate(o.settled_at) }}</td>
                  <td class="px-5 py-4 text-right">
                    <span :class="o.status === 'win' ? 'bg-[#0ecb81]/10 text-[#0ecb81]' : 'bg-[#f6465d]/10 text-[#f6465d]'"
                          class="px-2.5 py-1 rounded font-bold uppercase tracking-wider text-xs">
                      {{ o.status }}
                    </span>
                  </td>
                </tr>
                <tr v-if="filteredSettledOptions.length === 0">
                  <td colspan="7" class="py-10 text-center text-[#848e9c]">No settled options history.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Completed Spot Orders -->
        <div class="bg-[#1e2329] rounded-xl border border-[#2b3139] overflow-hidden shadow-xl">
          <div class="px-5 py-4 border-b border-[#2b3139] flex justify-between items-center bg-[#181a20]">
            <h3 class="font-bold text-white">Completed Spot Orders ({{ filteredOrderHistory.length }})</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono whitespace-nowrap">
              <thead class="bg-[#181a20]">
                <tr class="text-[#848e9c] text-[11px] uppercase tracking-wider">
                  <th class="px-5 py-3 font-medium">Market</th>
                  <th class="px-5 py-3 font-medium">Side / Type</th>
                  <th class="px-5 py-3 font-medium">Execution Price</th>
                  <th class="px-5 py-3 font-medium">Quantity</th>
                  <th class="px-5 py-3 font-medium">Total Value</th>
                  <th class="px-5 py-3 text-right font-medium">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#2b3139]">
                <tr v-for="o in filteredOrderHistory" :key="o.id" class="hover:bg-[#2b3139]/30 transition">
                  <td class="px-5 py-4 font-bold text-white">{{ o.market?.symbol }}</td>
                  <td class="px-5 py-4 uppercase font-bold" :class="o.side === 'buy' ? 'text-[#0ecb81]' : 'text-[#f6465d]'">
                    {{ o.side }} / {{ o.type }}
                  </td>
                  <td class="px-5 py-4 text-slate-300">${{ formatPrice(o.price) }}</td>
                  <td class="px-5 py-4 text-white">{{ Number(o.filled_quantity || o.quantity).toFixed(4) }}</td>
                  <td class="px-5 py-4 text-white font-bold">${{ formatPrice((o.price || 0) * (o.filled_quantity || o.quantity)) }}</td>
                  <td class="px-5 py-4 text-right">
                    <span :class="o.status === 'filled' ? 'bg-[#0ecb81]/10 text-[#0ecb81]' : 'bg-[#2b3139] text-[#848e9c]'"
                          class="px-2.5 py-1 rounded font-bold capitalize text-xs">
                      {{ o.status }}
                    </span>
                  </td>
                </tr>
                <tr v-if="filteredOrderHistory.length === 0">
                  <td colspan="6" class="py-10 text-center text-[#848e9c]">No completed spot order history found.</td>
                </tr>
              </tbody>
            </table>
          </div>
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

const filteredWallets = computed(() => {
  return (props.wallets || []).filter(w => isDemoMode.value ? w.is_demo : !w.is_demo);
});

// Calculate Spot Holdings
const activeHoldings = computed(() => {
  const holdings = [];

  filteredWallets.value.forEach(w => {
    const qty = parseFloat(w.available_balance || 0);
    if (w.currency === 'USDT' || qty <= 0) return;

    const market = (props.markets || []).find(m => m.base_currency === w.currency);
    if (!market) return;

    const currentPrice = parseFloat(market.last_price || 0);
    const marketValue = qty * currentPrice;

    const buyOrders = orderHistoryList.value.filter(o => o.market_id === market.id && o.side === 'buy' && o.status === 'filled' && (isDemoMode.value ? o.is_demo : !o.is_demo));
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
      marketId: market.id,
      amount: qty,
      avgBuyPrice,
      currentPrice,
      marketValue,
      pnlAmount,
      pnlPercent
    });
  });

  return holdings;
});

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
  return filteredActiveOptions.value.length + filteredOpenOrders.value.length + activeHoldings.value.length;
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

  // Inject manipulation only in the last 5 seconds of the trade
  if (contract.remaining_seconds > 5) {
    return realPrice;
  }

  const entry = parseFloat(contract.entry_price);
  
  // Maintain within a few cents of the entry price
  let delta = 0.05;
  let noise = Math.random() * 0.04;
  
  if (entry < 1) {
    delta = entry * 0.005;
    noise = delta * Math.random();
  }

  if (mode === 'force_win') {
    if (contract.direction === 'higher') {
      return parseFloat((entry + delta + noise).toFixed(6));
    } else {
      return parseFloat((entry - delta - noise).toFixed(6));
    }
  } else if (mode === 'force_loss') {
    if (contract.direction === 'higher') {
      return parseFloat((entry - delta - noise).toFixed(6));
    } else {
      return parseFloat((entry + delta + noise).toFixed(6));
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

async function closePosition(holding) {
  processingId.value = holding.currency;
  try {
    await axios.post('/api/orders', {
      market_id: holding.marketId,
      side: 'sell',
      type: 'market',
      quantity: holding.amount,
      is_demo: isDemoMode.value,
    });

    const gainLossText = holding.pnlAmount >= 0 
      ? `+$${formatPrice(holding.pnlAmount)} PROFIT`
      : `-$${formatPrice(Math.abs(holding.pnlAmount))} LOSS`;

    toastRef.value?.show(`Closed ${holding.currency} position! Credited $${formatPrice(holding.marketValue)} USDT (${gainLossText}).`, 'success');
    
    // Remove from active holdings visually until reload
    const wIndex = props.wallets.findIndex(w => w.currency === holding.currency && w.is_demo === isDemoMode.value);
    if (wIndex >= 0) props.wallets[wIndex].available_balance = 0;

  } catch (err) {
    toastRef.value?.show(err.response?.data?.message || 'Error closing position.', 'error');
  } finally {
    processingId.value = null;
  }
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
